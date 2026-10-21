@php
    $at = $activity['at'];
    $atollsLit = count(array_filter($activity['counts']));
    $top = $activity['top_event'];
@endphp
<x-layouts.app :title="$snapshot ? 'The Maldives at '.$at->mvt()->format('H:00, j M') : null">
    <section class="mb-8">
        <p class="eyebrow">{{ $snapshot ? 'Snapshot' : 'The Maldives right now' }} · {{ $at->mvt()->format('l j F Y, H:i') }} MVT</p>
        <h1 class="mt-3 max-w-3xl text-2xl font-semibold leading-tight sm:text-3xl">
            {{ number_format($activity['events']) }} {{ Str::plural('event', $activity['events']) }} across {{ $atollsLit }} {{ Str::plural('atoll', $atollsLit) }} in the last {{ $activity['hours'] }} hours.
        </h1>
        @if ($top)
            <p class="mt-3 max-w-3xl text-ink-300">
                The biggest: <a class="link text-ink-100" href="{{ route('events.show', $top) }}" @if ($top->isRtl()) lang="dv" dir="rtl" @endif>{{ $top->title }}</a>
                <span class="font-mono text-sm text-ink-400">({{ $top->source_count }} {{ Str::plural('source', $top->source_count) }})</span>
            </p>
        @endif
        <div class="mt-5 flex flex-wrap gap-x-8 gap-y-2 text-lg">
            <span class="stat">{{ number_format($activity['events']) }}<small>events</small></span>
            <span class="stat">{{ $atollsLit }}<small>atolls</small></span>
            <span class="stat">{{ number_format($activity['articles']) }}<small>articles tracked</small></span>
        </div>
    </section>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,420px)_1fr]">
        <section class="panel p-4">
            <x-atoll-map :map="$map" />
            <p class="mt-3 text-xs text-ink-400">Each atoll is coloured by how many events in the last {{ $activity['hours'] }} hours are about it. Refreshes every ten minutes; every hour gets a permanent link.</p>
            @if ($snapshots->isNotEmpty())
                <div class="mt-4">
                    <p class="eyebrow mb-2">Earlier hours</p>
                    <div class="flex flex-wrap gap-1.5 font-mono text-[11px]">
                        @foreach ($snapshots as $s)
                            <a href="{{ $s->permalink() }}" @class(['rounded border px-1.5 py-0.5 no-underline', 'border-ember-400 text-ember-300' => $snapshot?->is($s), 'border-ink-700 text-ink-300 hover:border-ink-400' => ! $snapshot?->is($s)])>{{ $s->hour->mvt()->format('H:00') }}</a>
                        @endforeach
                        @if ($snapshot)
                            <a href="{{ route('home') }}" class="rounded border border-ink-700 px-1.5 py-0.5 text-lagoon no-underline">live</a>
                        @endif
                    </div>
                </div>
            @endif
        </section>

        <div class="min-w-0 space-y-8">
            <section>
                <div class="flex items-baseline justify-between">
                    <h2 class="eyebrow">Most covered · last {{ $activity['hours'] }}h</h2>
                    <a href="{{ route('events.index') }}" class="text-xs text-ink-400 no-underline hover:text-ink-100">All events →</a>
                </div>
                @if ($events->isEmpty())
                    <p class="mt-4 text-ink-400">Nothing yet. Run <code class="font-mono text-ink-300">php artisan news:fetch</code> to read the feeds.</p>
                @else
                    <ul class="mt-2 divide-y divide-ink-800">
                        @foreach ($events as $event)
                            <x-event-row :event="$event" />
                        @endforeach
                    </ul>
                @endif
            </section>

            @if ($situations->isNotEmpty())
                <section>
                    <h2 class="eyebrow">Developing situations</h2>
                    <ul class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($situations as $situation)
                            <li>
                                <a href="{{ route('situations.show', $situation) }}" class="panel block h-full p-4 no-underline hover:border-ink-600">
                                    <div class="font-medium leading-snug">{{ $situation->title }}</div>
                                    <div class="mt-2 font-mono text-[11px] text-ink-400">{{ $situation->event_count }} events · {{ $situation->first_seen_at->mvt()->format('j M') }} – {{ $situation->last_seen_at->mvt()->format('j M') }}</div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</x-layouts.app>
