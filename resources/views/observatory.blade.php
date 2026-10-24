@php
    $s = $stats;
    $maxDay = max([1, ...array_values($s['articles_per_day'])]);
@endphp
<x-layouts.app :title="__('Observatory')">
    <h1 class="text-3xl">{{ __('Observatory') }}</h1>
    <p class="mt-3 text-lg text-muted">
        {{ __(':articles over :days, from :sources.', [
            'articles' => trans_choice(':count article|:count articles', $s['articles'], ['count' => number_format($s['articles'])]),
            'days' => trans_choice(':count day|:count days', $s['days']),
            'sources' => trans_choice(':count source|:count sources', $s['sources']),
        ]) }}
        {{ __('What the Maldivian press writes about, how much, and for how long.') }}
    </p>

    <h2 class="mt-10 text-xl">{{ __('Key numbers') }}</h2>
    <table class="data-table mt-3">
        <tbody>
            <tr>
                <td class="num w-24 text-base font-semibold" dir="ltr">{{ $s['articles_per_event'] }}:1</td>
                <td>{{ __('articles written per thing that actually happened.') }}</td>
            </tr>
            <tr>
                <td class="num text-base font-semibold">{{ $s['single_day_share'] === null ? '—' : round($s['single_day_share'] * 100).'%' }}</td>
                <td>{{ __('of events never get a second day of coverage.') }}</td>
            </tr>
            <tr>
                <td class="num text-base font-semibold">{{ $s['places'] ? $s['places'][0]['per_day'] : 0 }}</td>
                <td>{{ __('new events a day about :place, the most covered place.', ['place' => $s['places'][0]['name'] ?? __('the busiest atoll')]) }}</td>
            </tr>
            <tr>
                <td class="num text-base font-semibold">{{ round($s['exceptional_share'] * 100, 2) }}%</td>
                <td>{{ __('of events rate as exceptional. The routine middle is where most news lives.') }}</td>
            </tr>
        </tbody>
    </table>

    @if ($s['biggest_event'])
        <div class="callout mt-6">
            {{ __('Biggest single event so far') }}:
            <a href="{{ route('events.show', $s['biggest_event']) }}" lang="{{ $s['biggest_event']->isRtl() ? 'dv' : 'en' }}" class="font-medium">{{ $s['biggest_event']->displayTitle() }}</a>
            ({{ trans_choice(':count source|:count sources', $s['biggest_event']->source_count) }}, {{ trans_choice(':count article|:count articles', $s['biggest_event']->article_count) }})
        </div>
    @endif

    <h2 class="mt-10 text-xl">{{ __('Articles per day · last 30 days') }}</h2>
    {{-- The chart reads left to right (oldest to newest) in both languages. --}}
    <svg viewBox="0 0 300 80" class="mt-3 h-28 w-full text-accent" preserveAspectRatio="none" role="img" aria-label="{{ __('Articles per day · last 30 days') }}" direction="ltr">
        @php $n = max(1, count($s['articles_per_day'])); $w = 300 / $n; @endphp
        @foreach (array_values($s['articles_per_day']) as $i => $count)
            <rect x="{{ $i * $w + 0.5 }}" y="{{ 80 - $count / $maxDay * 78 }}" width="{{ max(1, $w - 1) }}" height="{{ $count / $maxDay * 78 }}" fill="currentColor"><title>{{ array_keys($s['articles_per_day'])[$i] }}: {{ $count }}</title></rect>
        @endforeach
    </svg>

    <h2 class="mt-10 text-xl">{{ __('Event tiers') }}</h2>
    <table class="data-table mt-3">
        <thead><tr><th>{{ __('Level') }}</th><th>{{ __('Rule') }}</th><th class="num">{{ __('Events') }}</th></tr></thead>
        <tbody>
            @foreach ($s['tiers'] as $tier => $count)
                <tr>
                    <td><x-tier :tier="$tier" /></td>
                    <td class="text-muted">{{ __(['routine' => '1 outlet', 'notable' => '2+ outlets', 'major' => '4+ outlets', 'exceptional' => '6+ outlets and 10+ articles'][$tier]) }}</td>
                    <td class="num">{{ number_format($count) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-10 text-xl">{{ __('Where the news is') }}</h2>
    @if ($s['places'])
        <table class="data-table mt-3">
            <thead><tr><th>{{ __('Atoll') }}</th><th class="num">{{ __('Events') }}</th><th class="num">{{ __('Per day') }}</th></tr></thead>
            <tbody>
                @foreach ($s['places'] as $place)
                    <tr>
                        <td><a href="{{ route('atolls.show', $place['code']) }}">{{ $place['name'] }}</a></td>
                        <td class="num">{{ $place['events'] }}</td>
                        <td class="num text-muted">{{ $place['per_day'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="callout mt-3">{{ __('No places tagged yet.') }}</p>
    @endif

    <h2 class="mt-10 text-xl">{{ __('Sources') }}</h2>
    <table class="data-table mt-3">
        <thead><tr><th>{{ __('Outlet') }}</th><th>{{ __('Status') }}</th><th class="num">{{ __('Articles') }}</th></tr></thead>
        <tbody>
            @foreach ($s['sources_breakdown'] as $source)
                <tr>
                    <td>{{ $source['name'] }}</td>
                    <td>
                        @if ($source['last_error'])
                            <span class="text-red-600 dark:text-red-400" title="{{ $source['last_error'] }}">{{ __('Last fetch failed') }}</span>
                        @else
                            <span class="text-muted">{{ __('OK') }}</span>
                        @endif
                    </td>
                    <td class="num">{{ number_format($source['articles']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-10 text-xl">{{ __('Methodology') }}</h2>
    <div class="mt-3 space-y-3 leading-relaxed">
        <p>{{ __('Every ten minutes Raajje Watch reads the RSS feeds of Maldivian outlets in English and Dhivehi. Each headline and summary becomes a bag of words weighted by TF-IDF, so words every story uses count for little and distinctive ones count for a lot.') }}</p>
        <p>{{ __('A new article joins the most similar event seen in the last :hours hours when their cosine similarity is at least :threshold; otherwise it starts a new event. Events that share at least three distinctive words and look loosely alike (at least :loose) within :days days are chained into a situation.', [
            'hours' => config('news.clustering.event_window_hours'),
            'threshold' => config('news.clustering.event_threshold'),
            'loose' => config('news.clustering.situation_threshold'),
            'days' => config('news.clustering.situation_window_days'),
        ]) }}</p>
        <p>{{ __('Atolls are found by matching a gazetteer of atoll and island names, including Dhivehi spellings, against each article. Clustering works within a language: a Dhivehi and an English report of the same event stay separate events for now.') }}</p>
    </div>
</x-layouts.app>
