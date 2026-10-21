<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Situation;
use App\Models\Source;
use App\Services\News\MapService;
use App\Services\News\Observatory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NewsApiController extends Controller
{
    public function events(Request $request)
    {
        $request->validate([
            'tier' => 'nullable|in:'.implode(',', Event::TIERS),
            'atoll' => 'nullable|string|max:8',
            'since' => 'nullable|date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $events = Event::with('places')
            ->when($request->tier, fn ($q, $tier) => $q->where('tier', $tier))
            ->when($request->atoll, fn ($q, $code) => $q->whereHas('places', fn ($p) => $p->where('place_code', $code)))
            ->when($request->since, fn ($q, $since) => $q->where('last_seen_at', '>=', Carbon::parse($since)))
            ->orderByDesc('last_seen_at')
            ->paginate($request->integer('per_page', 25))->withQueryString();

        return $events->through(fn (Event $e) => $this->eventJson($e));
    }

    public function event(Event $event)
    {
        $event->load(['articles.source', 'places', 'situation']);

        return ['data' => $this->eventJson($event, withArticles: true)];
    }

    public function situations()
    {
        return Situation::orderByDesc('last_seen_at')->paginate(25)
            ->through(fn (Situation $s) => $this->situationJson($s));
    }

    public function situation(Situation $situation)
    {
        $situation->load('events.places');

        return ['data' => $this->situationJson($situation) + [
            'timeline' => $situation->events->map(fn ($e) => $this->eventJson($e))->values(),
        ]];
    }

    public function map(Request $request, MapService $map)
    {
        $request->validate(['at' => 'nullable|date', 'hours' => 'nullable|integer|min:1|max:168']);

        $at = $request->at ? Carbon::parse($request->at) : now();
        $activity = $map->activity($at, $request->integer('hours') ?: null);

        return ['data' => [
            'at' => $activity['at']->toIso8601String(),
            'window_hours' => $activity['hours'],
            'events' => $activity['events'],
            'articles' => $activity['articles'],
            'top_event' => $activity['top_event'] ? $this->eventJson($activity['top_event']) : null,
            'atolls' => collect(config('places'))->map(fn ($p, $code) => [
                'code' => $code,
                'name' => $p['name'],
                'name_dv' => $p['name_dv'],
                'lat' => $p['lat'],
                'lng' => $p['lng'],
                'events' => $activity['counts'][$code] ?? 0,
            ])->values(),
        ]];
    }

    public function stats(Observatory $observatory)
    {
        $stats = $observatory->stats();
        $stats['biggest_event'] = $stats['biggest_event'] ? $this->eventJson($stats['biggest_event']) : null;
        $stats['longest_situation'] = $stats['longest_situation'] ? $this->situationJson($stats['longest_situation']) : null;
        $stats['sources_breakdown'] = array_map(fn ($s) => array_diff_key($s, ['last_error' => 1]), $stats['sources_breakdown']);

        return ['data' => $stats];
    }

    public function sources()
    {
        return ['data' => Source::withCount('articles')->orderBy('name')->get()->map(fn ($s) => [
            'slug' => $s->slug,
            'name' => $s->name,
            'url' => $s->url,
            'lang' => $s->lang,
            'articles' => $s->articles_count,
            'last_fetched_at' => $s->last_fetched_at?->toIso8601String(),
        ])];
    }

    private function eventJson(Event $event, bool $withArticles = false): array
    {
        $json = [
            'id' => $event->id,
            'title' => $event->title,
            'lang' => $event->lang,
            'tier' => $event->tier,
            'first_seen_at' => $event->first_seen_at->toIso8601String(),
            'last_seen_at' => $event->last_seen_at->toIso8601String(),
            'article_count' => $event->article_count,
            'source_count' => $event->source_count,
            'situation_id' => $event->situation_id,
            'atolls' => $event->places->pluck('place_code')->values(),
            'url' => route('events.show', $event),
        ];

        if ($withArticles) {
            $json['articles'] = $event->articles->map(fn ($a) => [
                'title' => $a->title,
                'url' => $a->url,
                'source' => $a->source->name,
                'lang' => $a->lang,
                'published_at' => $a->published_at->toIso8601String(),
            ])->values();
        }

        return $json;
    }

    private function situationJson(Situation $situation): array
    {
        return [
            'id' => $situation->id,
            'slug' => $situation->slug,
            'title' => $situation->title,
            'first_seen_at' => $situation->first_seen_at->toIso8601String(),
            'last_seen_at' => $situation->last_seen_at->toIso8601String(),
            'event_count' => $situation->event_count,
            'article_count' => $situation->article_count,
            'url' => route('situations.show', $situation),
        ];
    }
}
