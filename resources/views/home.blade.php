@php
    $at = $activity['at'];
    $atollsLit = count(array_filter($activity['counts']));
    $top = $activity['top_event'];
@endphp
<x-layouts.app :wide="true" :title="$snapshot ? __('The Maldives at :time', ['time' => $at->mvt()->translatedFormat('H:00, j F')]) : null">
    <div class="max-w-prose">
        @if ($snapshot)
            <p class="breadcrumb"><a href="{{ route('home') }}">{{ __('Now') }}</a> <span>/</span> <span>{{ __('Snapshot') }}</span></p>
        @endif
        <h1 class="text-3xl">{{ $snapshot ? __('The Maldives at :time', ['time' => $at->mvt()->translatedFormat('H:00, j F')]) : __('The Maldives right now') }}</h1>
        <p class="mt-3 text-lg text-muted">
            {{ __(':events across :atolls in the last :hours hours.', [
                'events' => trans_choice(':count event|:count events', $activity['events'], ['count' => number_format($activity['events'])]),
                'atolls' => trans_choice(':count atoll|:count atolls', $atollsLit),
                'hours' => $activity['hours'],
            ]) }}
            @if ($top)
                {{ __('The biggest:') }} <a href="{{ route('events.show', $top) }}" lang="{{ $top->isRtl() ? 'dv' : 'en' }}">{{ $top->displayTitle() }}</a>
                ({{ trans_choice(':count source|:count sources', $top->source_count) }}).
            @endif
        </p>
        <p class="meta mt-2">{{ __('Updated :time MVT · :articles articles read', ['time' => $at->mvt()->translatedFormat('j F Y, H:i'), 'articles' => number_format($activity['articles'])]) }}</p>
    </div>

    <div class="mt-10 grid gap-10 xl:grid-cols-[22rem_minmax(0,1fr)]">
        <section>
            <h2 class="text-lg">{{ __('Map') }}</h2>
            <p class="meta mt-1">{{ __('Each atoll is coloured by how many events in the last :hours hours are about it. Refreshes every ten minutes; every hour gets a permanent link.', ['hours' => $activity['hours']]) }}</p>
            <x-atoll-map :map="$map" class="mt-4 max-w-md" />

            @if ($snapshots->isNotEmpty())
                <h3 class="mt-6 text-sm">{{ __('Earlier hours') }}</h3>
                <p class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-sm tabular-nums">
                    @foreach ($snapshots as $s)
                        @if ($snapshot?->is($s))
                            <span class="font-medium text-ink">{{ $s->hour->mvt()->format('H:00') }}</span>
                        @else
                            <a href="{{ $s->permalink() }}">{{ $s->hour->mvt()->format('H:00') }}</a>
                        @endif
                    @endforeach
                    @if ($snapshot)
                        <a href="{{ route('home') }}">{{ __('live') }}</a>
                    @endif
                </p>
            @endif
        </section>

        <div class="min-w-0 space-y-10">
            <section>
                <div class="flex items-baseline justify-between gap-4">
                    <h2 class="text-lg">{{ __('Most covered in the last :hours hours', ['hours' => $activity['hours']]) }}</h2>
                    <a href="{{ route('events.index') }}" class="text-sm">{{ __('All events') }}</a>
                </div>
                <div class="mt-3">
                    <x-event-table :events="$events" :empty="__('No news yet. The feeds are read every ten minutes, so check back in a minute.')" />
                </div>
            </section>

            @if ($situations->isNotEmpty())
                <section>
                    <div class="flex items-baseline justify-between gap-4">
                        <h2 class="text-lg">{{ __('Developing situations') }}</h2>
                        <a href="{{ route('situations.index') }}" class="text-sm">{{ __('All situations') }}</a>
                    </div>
                    <ul class="mt-3 divide-y divide-line border-y border-line">
                        @foreach ($situations as $situation)
                            <li class="py-3">
                                <a href="{{ route('situations.show', $situation) }}" lang="{{ $situation->isRtl() ? 'dv' : 'en' }}" class="font-medium text-ink">{{ $situation->displayTitle() }}</a>
                                <p class="meta mt-0.5">{{ trans_choice(':count event|:count events', $situation->event_count) }} · {{ $situation->first_seen_at->mvt()->translatedFormat('j M') }} – {{ $situation->last_seen_at->mvt()->translatedFormat('j M') }}</p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
