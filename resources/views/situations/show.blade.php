<x-layouts.app :title="$situation->displayTitle()">
    <p class="eyebrow">{{ __('Situation') }} · {{ $situation->first_seen_at->mvt()->translatedFormat('j M') }} – {{ $situation->last_seen_at->mvt()->translatedFormat('j M Y') }}</p>
    <h1 lang="{{ $situation->isRtl() ? 'dv' : 'en' }}" dir="{{ $situation->isRtl() ? 'rtl' : 'ltr' }}" class="mt-3 max-w-3xl text-2xl font-semibold leading-tight sm:text-3xl">{{ $situation->displayTitle() }}</h1>
    <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
        <span class="stat">{{ $situation->event_count }}<small>{{ __('events') }}</small></span>
        <span class="stat">{{ $situation->article_count }}<small>{{ __('articles') }}</small></span>
        <span class="stat">{{ (int) $situation->first_seen_at->diffInDays($situation->last_seen_at, true) + 1 }}<small>{{ __('days') }}</small></span>
    </div>

    <ol class="mt-8 max-w-3xl border-s border-ink-700 ps-6">
        @foreach ($situation->events as $event)
            <li class="relative pb-8">
                <span class="absolute -start-[29px] top-1 h-2.5 w-2.5 rounded-full bg-ember-500 ring-4 ring-ink-950"></span>
                <time class="font-mono text-xs text-ink-400">{{ $event->first_seen_at->mvt()->translatedFormat('l j F Y') }}</time>
                <a href="{{ route('events.show', $event) }}" lang="{{ $event->isRtl() ? 'dv' : 'en' }}" dir="{{ $event->isRtl() ? 'rtl' : 'ltr' }}" class="mt-1 block text-lg font-medium leading-snug no-underline hover:text-ember-300">{{ $event->displayTitle() }}</a>
                <div class="meta mt-1 flex flex-wrap items-center gap-x-3 font-mono text-[11px] text-ink-400">
                    <x-tier :tier="$event->tier" />
                    <span>{{ trans_choice(':count source|:count sources', $event->source_count) }}</span>
                    <span>{{ $event->articles->pluck('source.name')->unique()->join(', ') }}</span>
                </div>
            </li>
        @endforeach
    </ol>
</x-layouts.app>
