<?php

namespace App\Services\News;

use App\Models\Article;
use App\Models\Event;
use App\Models\EventPlace;
use App\Models\Situation;
use App\Models\Source;
use Illuminate\Support\Facades\DB;

/**
 * Whole-archive statistics for the observatory page and the stats API.
 */
class Observatory
{
    public function stats(): array
    {
        $articles = Article::count();
        $events = Event::count();
        $first = Article::min('published_at');
        $days = $first ? (int) floor(now()->diffInDays($first, true)) + 1 : 0;

        // Only judge events old enough to have had a chance at a second day.
        $settled = Event::where('first_seen_at', '<', now()->subDay());
        $settledCount = (clone $settled)->count();
        $oneDay = (clone $settled)->whereRaw('date(last_seen_at) = date(first_seen_at)')->count();

        $tiers = Event::selectRaw('tier, count(*) as n')->groupBy('tier')->pluck('n', 'tier');

        $places = EventPlace::selectRaw('place_code, count(*) as n')->groupBy('place_code')->orderByDesc('n')->get()
            ->map(fn ($row) => [
                'code' => $row->place_code,
                'name' => config("places.{$row->place_code}.name", $row->place_code),
                'events' => (int) $row->n,
                'per_day' => $days ? round($row->n / $days, 1) : 0,
            ]);

        $bySource = Source::withCount('articles')->orderByDesc('articles_count')->get()
            ->map(fn ($s) => ['slug' => $s->slug, 'name' => $s->name, 'articles' => $s->articles_count, 'last_error' => $s->last_error]);

        $perDay = Article::selectRaw('date(published_at) as day, count(*) as n')
            ->where('published_at', '>=', now()->subDays(30)->startOfDay())
            ->groupBy('day')->orderBy('day')->pluck('n', 'day')->map(fn ($n) => (int) $n);

        return [
            'days' => $days,
            'since' => $first,
            'articles' => $articles,
            'events' => $events,
            'situations' => Situation::count(),
            'sources' => Source::count(),
            'articles_per_event' => $events ? round($articles / $events, 1) : 0,
            'single_day_share' => $settledCount ? round($oneDay / $settledCount, 3) : null,
            'tiers' => collect(Event::TIERS)->mapWithKeys(fn ($t) => [$t => (int) ($tiers[$t] ?? 0)])->all(),
            'exceptional_share' => $events ? round(($tiers['exceptional'] ?? 0) / $events, 4) : 0,
            'biggest_event' => Event::orderByDesc('source_count')->orderByDesc('article_count')->first(),
            'longest_situation' => Situation::orderByDesc('event_count')->first(),
            'places' => $places->all(),
            'sources_breakdown' => $bySource->all(),
            'articles_per_day' => $perDay->all(),
            'languages' => Article::select('lang', DB::raw('count(*) as n'))->groupBy('lang')->pluck('n', 'lang')->map(fn ($n) => (int) $n)->all(),
        ];
    }
}
