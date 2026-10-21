<x-layouts.app :title="$situation->title">
    <p class="eyebrow">Situation · {{ $situation->first_seen_at->mvt()->format('j M') }} – {{ $situation->last_seen_at->mvt()->format('j M Y') }}</p>
    <h1 class="mt-3 max-w-3xl text-2xl font-semibold leading-tight sm:text-3xl">{{ $situation->title }}</h1>
    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
        <span class="stat">{{ $situation->event_count }}<small>events</small></span>
        <span class="stat">{{ $situation->article_count }}<small>articles</small></span>
        <span class="stat">{{ (int) $situation->first_seen_at->diffInDays($situation->last_seen_at, true) + 1 }}<small>days</small></span>
    </div>

    <ol class="mt-8 max-w-3xl border-l border-ink-700 pl-6">
        @foreach ($situation->events as $event)
            <li class="relative pb-8">
                <span class="absolute -left-[29px] top-1 h-2.5 w-2.5 rounded-full bg-ember-500 ring-4 ring-ink-950"></span>
                <time class="font-mono text-xs text-ink-400">{{ $event->first_seen_at->mvt()->format('D j M Y') }}</time>
                <a href="{{ route('events.show', $event) }}" @if ($event->isRtl()) lang="dv" dir="rtl" @endif class="mt-1 block text-lg font-medium leading-snug no-underline hover:text-ember-300">{{ $event->title }}</a>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 font-mono text-[11px] text-ink-400">
                    <x-tier :tier="$event->tier" />
                    <span>{{ $event->source_count }} sources</span>
                    <span>{{ $event->articles->pluck('source.name')->unique()->join(', ') }}</span>
                </div>
            </li>
        @endforeach
    </ol>
</x-layouts.app>
