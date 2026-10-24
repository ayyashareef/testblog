<x-layouts.app :title="$situation->displayTitle()">
    <p class="breadcrumb"><a href="{{ route('situations.index') }}">{{ __('Situations') }}</a> <span>/</span></p>
    <h1 lang="{{ $situation->isRtl() ? 'dv' : 'en' }}" dir="{{ $situation->isRtl() ? 'rtl' : 'ltr' }}" class="text-3xl leading-tight">{{ $situation->displayTitle() }}</h1>
    <p class="meta mt-3">
        {{ trans_choice(':count event|:count events', $situation->event_count) }} ·
        {{ trans_choice(':count article|:count articles', $situation->article_count) }} ·
        {{ $situation->first_seen_at->mvt()->translatedFormat('j M') }} – {{ $situation->last_seen_at->mvt()->translatedFormat('j M Y') }}
    </p>

    <h2 class="mt-10 text-xl">{{ __('Timeline') }}</h2>
    <ol class="mt-4 border-s border-line ps-6">
        @foreach ($situation->events as $event)
            <li class="relative pb-8 last:pb-0">
                <span class="absolute -start-[1.72rem] top-1.5 h-2 w-2 rounded-full bg-accent"></span>
                <time class="meta">{{ $event->first_seen_at->mvt()->translatedFormat('l j F Y') }}</time>
                <a href="{{ route('events.show', $event) }}" lang="{{ $event->isRtl() ? 'dv' : 'en' }}" dir="{{ $event->isRtl() ? 'rtl' : 'ltr' }}" class="mt-1 block text-lg font-medium leading-snug">{{ $event->displayTitle() }}</a>
                <p class="meta mt-1">
                    <x-tier :tier="$event->tier" /> ·
                    {{ trans_choice(':count source|:count sources', $event->source_count) }}:
                    {{ $event->articles->pluck('source.name')->unique()->join(', ') }}
                </p>
            </li>
        @endforeach
    </ol>
</x-layouts.app>
