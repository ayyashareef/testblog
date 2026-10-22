@php
    $s = $stats;
    $maxDay = max([1, ...array_values($s['articles_per_day'])]);
    $maxSource = max([1, ...array_column($s['sources_breakdown'], 'articles')]);
    $maxPlace = max([1, ...array_column($s['places'], 'events')]);
@endphp
<x-layouts.app :title="__('Observatory')">
    <p class="eyebrow">{{ __('Observatory') }}</p>
    <h1 class="mt-2 max-w-3xl text-2xl font-semibold leading-tight sm:text-3xl">
        {{ __(':articles over :days, from :sources.', [
            'articles' => trans_choice(':count article|:count articles', $s['articles'], ['count' => number_format($s['articles'])]),
            'days' => trans_choice(':count day|:count days', $s['days']),
            'sources' => trans_choice(':count source|:count sources', $s['sources']),
        ]) }}
    </h1>
    <p class="mt-2 text-ink-400">{{ __('What the Maldivian press writes about, how much, and for how long.') }}</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="panel p-5">
            <div class="stat text-3xl" dir="ltr">{{ $s['articles_per_event'] }}:1</div>
            <p class="mt-2 text-sm text-ink-400">{{ __('articles written per thing that actually happened.') }}</p>
        </div>
        <div class="panel p-5">
            <div class="stat text-3xl">{{ $s['single_day_share'] === null ? '—' : round($s['single_day_share'] * 100).'%' }}</div>
            <p class="mt-2 text-sm text-ink-400">{{ __('of events never get a second day of coverage.') }}</p>
        </div>
        <div class="panel p-5">
            <div class="stat text-3xl">{{ $s['places'] ? $s['places'][0]['per_day'] : 0 }}</div>
            <p class="mt-2 text-sm text-ink-400">{{ __('new events a day about :place, the most covered place.', ['place' => $s['places'][0]['name'] ?? __('the busiest atoll')]) }}</p>
        </div>
        <div class="panel p-5">
            <div class="stat text-3xl">{{ round($s['exceptional_share'] * 100, 2) }}%</div>
            <p class="mt-2 text-sm text-ink-400">{{ __('of events rate as exceptional. The routine middle is where most news lives.') }}</p>
        </div>
    </div>

    @if ($s['biggest_event'])
        <section class="panel mt-4 p-5">
            <p class="eyebrow">{{ __('Biggest single event so far') }}</p>
            <a href="{{ route('events.show', $s['biggest_event']) }}" lang="{{ $s['biggest_event']->isRtl() ? 'dv' : 'en' }}" dir="{{ $s['biggest_event']->isRtl() ? 'rtl' : 'ltr' }}" class="link mt-2 block text-lg font-medium">{{ $s['biggest_event']->displayTitle() }}</a>
            <p class="meta mt-1 font-mono text-sm text-ink-400">{{ trans_choice(':count source|:count sources', $s['biggest_event']->source_count) }} · {{ trans_choice(':count article|:count articles', $s['biggest_event']->article_count) }}</p>
        </section>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section>
            <h2 class="eyebrow">{{ __('Articles per day · last 30 days') }}</h2>
            {{-- The chart reads left to right (oldest to newest) in both languages. --}}
            <svg viewBox="0 0 300 100" class="mt-3 h-32 w-full" preserveAspectRatio="none" role="img" aria-label="{{ __('Articles per day · last 30 days') }}">
                @php $n = max(1, count($s['articles_per_day'])); $w = 300 / $n; @endphp
                @foreach (array_values($s['articles_per_day']) as $i => $count)
                    <rect x="{{ $i * $w + 0.5 }}" y="{{ 100 - $count / $maxDay * 96 }}" width="{{ max(1, $w - 1) }}" height="{{ $count / $maxDay * 96 }}" fill="#ea741a"><title>{{ array_keys($s['articles_per_day'])[$i] }}: {{ $count }}</title></rect>
                @endforeach
            </svg>
        </section>

        <section>
            <h2 class="eyebrow">{{ __('Event tiers') }}</h2>
            <dl class="mt-3 grid grid-cols-4 gap-2 text-center">
                @foreach ($s['tiers'] as $tier => $count)
                    <div class="panel p-3"><dt><x-tier :tier="$tier" /></dt><dd class="stat mt-2 text-xl">{{ number_format($count) }}</dd></div>
                @endforeach
            </dl>
            <p class="mt-2 text-xs text-ink-400">{{ __('Notable: 2+ outlets. Major: 4+. Exceptional: 6+ outlets and 10+ articles.') }}</p>
        </section>

        <section>
            <h2 class="eyebrow">{{ __('Where the news is') }}</h2>
            <ul class="mt-3 space-y-1.5">
                @forelse ($s['places'] as $place)
                    <li class="grid grid-cols-[110px_1fr_48px] items-center gap-3 text-sm">
                        <a href="{{ route('atolls.show', $place['code']) }}" class="truncate text-ink-300 no-underline hover:text-ink-100">{{ $place['name'] }}</a>
                        <span class="h-2 rounded-full bg-lagoon/70" style="width: {{ max(2, $place['events'] / $maxPlace * 100) }}%"></span>
                        <span class="text-end font-mono text-ink-400">{{ $place['events'] }}</span>
                    </li>
                @empty
                    <li class="text-ink-400">{{ __('No places tagged yet.') }}</li>
                @endforelse
            </ul>
        </section>

        <section>
            <h2 class="eyebrow">{{ __('Sources') }}</h2>
            <ul class="mt-3 space-y-1.5">
                @foreach ($s['sources_breakdown'] as $source)
                    <li class="grid grid-cols-[150px_1fr_48px] items-center gap-3 text-sm">
                        <span class="truncate text-ink-300" @if ($source['last_error']) title="{{ __('Last fetch failed: :error', ['error' => $source['last_error']]) }}" @endif>{{ $source['name'] }}@if ($source['last_error']) <span class="text-ember-500">!</span>@endif</span>
                        <span class="h-2 rounded-full bg-ember-500/80" style="width: {{ max(2, $source['articles'] / $maxSource * 100) }}%"></span>
                        <span class="text-end font-mono text-ink-400">{{ number_format($source['articles']) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <section class="mt-10 max-w-3xl text-sm leading-relaxed text-ink-300">
        <h2 class="eyebrow mb-3">{{ __('Methodology') }}</h2>
        <p>{{ __('Every ten minutes Raajje Watch reads the RSS feeds of Maldivian outlets in English and Dhivehi. Each headline and summary becomes a bag of words weighted by TF-IDF, so words every story uses count for little and distinctive ones count for a lot.') }}</p>
        <p class="mt-3">{{ __('A new article joins the most similar event seen in the last :hours hours when their cosine similarity is at least :threshold; otherwise it starts a new event. Events that share at least three distinctive words and look loosely alike (at least :loose) within :days days are chained into a situation.', [
            'hours' => config('news.clustering.event_window_hours'),
            'threshold' => config('news.clustering.event_threshold'),
            'loose' => config('news.clustering.situation_threshold'),
            'days' => config('news.clustering.situation_window_days'),
        ]) }}</p>
        <p class="mt-3">{{ __('Atolls are found by matching a gazetteer of atoll and island names, including Dhivehi spellings, against each article. Clustering works within a language: a Dhivehi and an English report of the same event stay separate events for now.') }}</p>
    </section>
</x-layouts.app>
