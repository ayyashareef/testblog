<?php

namespace App\Services\News;

use App\Models\Article;
use App\Models\Event;
use App\Models\EventPlace;
use App\Models\Situation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Groups articles about the same happening into events, then chains related
 * events into longer-running situations.
 *
 * Each article and event is a bag of words weighted by TF-IDF. An article
 * joins the most similar event still open (seen within the event window) if
 * the cosine similarity clears the event threshold; otherwise it starts a new
 * event. Events that resemble each other more loosely, over a longer window,
 * are linked into a situation.
 */
class Clusterer
{
    private const MAX_EVENT_TERMS = 60;

    private const MIN_SHARED_TERMS = 3;

    /** @var array<string, float> */
    private array $idf = [];

    private int $docs = 0;

    public function __construct(private Gazetteer $gazetteer) {}

    /**
     * @return array{articles: int, new_events: int, touched_events: int, situations: int}
     */
    public function run(): array
    {
        $pending = Article::whereNull('event_id')->orderBy('published_at')->get();

        if ($pending->isEmpty()) {
            return ['articles' => 0, 'new_events' => 0, 'touched_events' => 0, 'situations' => 0];
        }

        $this->idf = $this->buildIdf($pending->min('published_at'));

        $windowHours = config('news.clustering.event_window_hours');
        $threshold = config('news.clustering.event_threshold');

        /** @var Collection<int, Event> $open */
        $open = Event::where('last_seen_at', '>=', $pending->min('published_at')->copy()->subHours($windowHours))->get()->keyBy('id');
        $touched = [];
        $created = 0;

        DB::transaction(function () use ($pending, $open, $windowHours, $threshold, &$touched, &$created) {
            foreach ($pending as $article) {
                [$best, $score] = $this->bestEvent($article, $open, $windowHours);

                if ($best && $score >= $threshold) {
                    $best->terms = $this->mergeTerms($best->terms, $article->tokens);
                    $best->last_seen_at = $article->published_at->max($best->last_seen_at);
                } else {
                    $best = Event::create([
                        'title' => $article->title,
                        'lang' => $article->lang,
                        'first_seen_at' => $article->published_at,
                        'last_seen_at' => $article->published_at,
                        'terms' => $this->mergeTerms([], $article->tokens),
                    ]);
                    $open[$best->id] = $best;
                    $created++;
                }

                $article->update(['event_id' => $best->id]);
                $touched[$best->id] = $best;
            }

            foreach ($touched as $event) {
                $this->refreshEvent($event);
            }
        });

        $situations = $this->chainSituations(collect($touched));

        return [
            'articles' => $pending->count(),
            'new_events' => $created,
            'touched_events' => count($touched),
            'situations' => $situations,
        ];
    }

    /**
     * Cosine similarity between two term-count bags using the current IDF.
     */
    public function similarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $term => $count) {
            $w = $count * $this->idfFor($term);
            $normA += $w * $w;
            if (isset($b[$term])) {
                $dot += $w * $b[$term] * $this->idfFor($term);
            }
        }
        foreach ($b as $term => $count) {
            $w = $count * $this->idfFor($term);
            $normB += $w * $w;
        }

        return $normA && $normB ? $dot / (sqrt($normA) * sqrt($normB)) : 0.0;
    }

    private function bestEvent(Article $article, Collection $open, int $windowHours): array
    {
        $best = null;
        $bestScore = 0.0;

        foreach ($open as $event) {
            if ($event->lang !== $article->lang && ! $this->langCompatible($event, $article)) {
                continue;
            }
            if (abs($article->published_at->diffInHours($event->last_seen_at)) > $windowHours) {
                continue;
            }

            $score = $this->similarity($article->tokens, $event->terms);
            if ($score > $bestScore) {
                [$best, $bestScore] = [$event, $score];
            }
        }

        return [$best, $bestScore];
    }

    /**
     * Words carry across languages only when both texts share script, so an
     * English article can only meet a Dhivehi event if that event already
     * picked up English words (e.g. through a bilingual outlet).
     */
    private function langCompatible(Event $event, Article $article): bool
    {
        foreach (array_keys($article->tokens) as $term) {
            if (isset($event->terms[$term])) {
                return true;
            }
        }

        return false;
    }

    private function refreshEvent(Event $event): void
    {
        $articles = $event->articles()->with('source')->get();
        $sources = $articles->pluck('source_id')->unique()->count();

        // Prefer an English headline for the event title; most readers of the
        // index read English, and Dhivehi titles still show on the event page.
        $lead = $articles->firstWhere('lang', 'en') ?? $articles->first();

        $event->fill([
            'title' => $lead->title,
            'lang' => $lead->lang,
            'first_seen_at' => $articles->min('published_at'),
            'last_seen_at' => $articles->max('published_at'),
            'article_count' => $articles->count(),
            'source_count' => $sources,
            'tier' => Event::tierFor($sources, $articles->count()),
        ])->save();

        $mentions = [];
        foreach ($articles as $article) {
            foreach ($this->gazetteer->find($article->title.' '.$article->summary) as $code => $n) {
                $mentions[$code] = ($mentions[$code] ?? 0) + $n;
            }
        }

        EventPlace::where('event_id', $event->id)->delete();
        foreach ($mentions as $code => $n) {
            EventPlace::create(['event_id' => $event->id, 'place_code' => $code, 'mentions' => $n]);
        }
    }

    /**
     * @param  Collection<int, Event>  $events
     */
    private function chainSituations(Collection $events): int
    {
        $threshold = config('news.clustering.situation_threshold');
        $windowDays = config('news.clustering.situation_window_days');
        $chained = 0;

        foreach ($events->whereNull('situation_id') as $event) {
            $event->refresh();
            if ($event->situation_id) {
                continue;
            }

            $best = null;
            $bestScore = 0.0;

            $candidates = Event::where('id', '!=', $event->id)
                ->where('last_seen_at', '>=', $event->first_seen_at->copy()->subDays($windowDays))
                ->where('first_seen_at', '<=', $event->last_seen_at->copy()->addDays($windowDays))
                ->get();

            $places = $event->places()->pluck('place_code')->all();
            $candidatePlaces = EventPlace::whereIn('event_id', $candidates->pluck('id'))->get()->groupBy('event_id');

            foreach ($candidates as $candidate) {
                if (count(array_intersect_key($event->terms, $candidate->terms)) < self::MIN_SHARED_TERMS) {
                    continue;
                }

                // Two stories set in different atolls need to look much more
                // alike before we call them one situation.
                $theirs = $candidatePlaces->get($candidate->id)?->pluck('place_code')->all() ?? [];
                $needed = $places && $theirs && ! array_intersect($places, $theirs)
                    ? config('news.clustering.event_threshold')
                    : $threshold;

                $score = $this->similarity($event->terms, $candidate->terms);
                if ($score >= $needed && $score > $bestScore) {
                    [$best, $bestScore] = [$candidate, $score];
                }
            }

            if (! $best) {
                continue;
            }

            // Name a new situation after whichever story came first.
            $origin = $best->first_seen_at->lte($event->first_seen_at) ? $best : $event;
            $situation = $best->situation ?? Situation::create([
                'slug' => Str::slug(Str::limit($origin->title, 60, '')).'-'.Str::lower(Str::random(5)),
                'title' => $origin->title,
                'first_seen_at' => $origin->first_seen_at,
                'last_seen_at' => $origin->last_seen_at,
            ]);

            $best->update(['situation_id' => $situation->id]);
            $event->update(['situation_id' => $situation->id]);
            $situation->refreshStats();
            $chained++;
        }

        // Keep counts right for situations whose events grew this run.
        Situation::whereIn('id', $events->pluck('situation_id')->filter()->unique())->get()->each->refreshStats();

        return $chained;
    }

    private function mergeTerms(array $terms, array $tokens): array
    {
        foreach ($tokens as $term => $count) {
            $terms[$term] = ($terms[$term] ?? 0) + $count;
        }

        arsort($terms);

        return array_slice($terms, 0, self::MAX_EVENT_TERMS, true);
    }

    /**
     * Document frequency over the last week of articles (and anything newer
     * than the oldest pending one), so common words count for little.
     *
     * @return array<string, float>
     */
    private function buildIdf(CarbonInterface $oldestPending): array
    {
        $from = $oldestPending->copy()->subDays(7)->min(now()->subDays(7));
        $df = [];
        $docs = 0;

        Article::where('published_at', '>=', $from)->select('tokens')->chunk(1000, function ($chunk) use (&$df, &$docs) {
            foreach ($chunk as $article) {
                $docs++;
                foreach (array_keys($article->tokens) as $term) {
                    $df[$term] = ($df[$term] ?? 0) + 1;
                }
            }
        });

        $this->docs = $docs;

        return array_map(fn ($n) => log(($docs + 1) / ($n + 1)) + 1, $df);
    }

    private function idfFor(string $term): float
    {
        return $this->idf[$term] ?? log($this->docs + 1) + 1;
    }
}
