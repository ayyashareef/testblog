<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MapSnapshot;
use App\Models\Situation;
use App\Services\News\MapService;
use App\Services\News\Observatory;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NewsController extends Controller
{
    public function home(MapService $map)
    {
        $activity = $map->activity(now());

        $events = Event::with('places')
            ->where('last_seen_at', '>=', now()->subHours($activity['hours']))
            ->orderByDesc('source_count')->orderByDesc('last_seen_at')
            ->limit(30)->get();

        return view('home', [
            'activity' => $activity,
            'map' => $map->render($activity['counts']),
            'events' => $events,
            'situations' => Situation::orderByDesc('last_seen_at')->limit(6)->get(),
            'snapshots' => MapSnapshot::orderByDesc('hour')->limit(24)->get(),
            'snapshot' => null,
        ]);
    }

    public function mapHour(string $hour, MapService $map)
    {
        $at = Carbon::createFromFormat('Y-m-d\TH', $hour, 'UTC')->startOfHour();
        $snapshot = MapSnapshot::with('topEvent')->where('hour', $at)->firstOrFail();

        $events = Event::with('places')
            ->where('last_seen_at', '>=', $at->copy()->subHours($snapshot->window_hours))
            ->where('first_seen_at', '<=', $at)
            ->orderByDesc('source_count')->limit(30)->get();

        return view('home', [
            'activity' => [
                'at' => $snapshot->hour,
                'hours' => $snapshot->window_hours,
                'counts' => $snapshot->counts,
                'events' => $snapshot->event_total,
                'articles' => $snapshot->article_total,
                'top_event' => $snapshot->topEvent,
            ],
            'map' => $map->render($snapshot->counts),
            'events' => $events,
            'situations' => collect(),
            'snapshots' => MapSnapshot::where('hour', '<=', $at)->orderByDesc('hour')->limit(24)->get(),
            'snapshot' => $snapshot,
        ]);
    }

    public function events(Request $request)
    {
        $events = Event::with('places')
            ->when($request->query('tier'), fn ($q, $tier) => $q->where('tier', $tier))
            ->orderByDesc('last_seen_at')
            ->paginate(40)->withQueryString();

        return view('events.index', ['events' => $events, 'tier' => $request->query('tier')]);
    }

    public function event(Event $event)
    {
        $event->load(['articles.source', 'places', 'situation.events']);

        return view('events.show', ['event' => $event]);
    }

    public function situations()
    {
        return view('situations.index', [
            'situations' => Situation::orderByDesc('last_seen_at')->paginate(30),
        ]);
    }

    public function situation(Situation $situation)
    {
        $situation->load(['events.places', 'events.articles.source']);

        return view('situations.show', ['situation' => $situation]);
    }

    public function atoll(string $code)
    {
        $place = config("places.{$code}") ?? abort(404);

        $events = Event::with('places')
            ->whereHas('places', fn ($q) => $q->where('place_code', $code))
            ->orderByDesc('last_seen_at')
            ->paginate(40);

        return view('events.index', ['events' => $events, 'tier' => null, 'place' => $place + ['code' => $code]]);
    }

    public function observatory(Observatory $observatory)
    {
        return view('observatory', ['stats' => $observatory->stats()]);
    }

    public function developers()
    {
        return view('developers');
    }
}
