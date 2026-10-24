<x-layouts.app :title="$event->displayTitle()">
    <p class="breadcrumb"><a href="{{ route('events.index') }}">{{ __('Events') }}</a> <span>/</span></p>
    <h1 lang="{{ $event->isRtl() ? 'dv' : 'en' }}" dir="{{ $event->isRtl() ? 'rtl' : 'ltr' }}" class="text-3xl leading-tight">{{ $event->displayTitle() }}</h1>

    <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-3 border-y border-line py-4 text-sm sm:grid-cols-4">
        <div><dt class="text-muted">{{ __('Level') }}</dt><dd class="mt-0.5"><x-tier :tier="$event->tier" /></dd></div>
        <div><dt class="text-muted">{{ __('Sources') }}</dt><dd class="mt-0.5 tabular-nums">{{ $event->source_count }}</dd></div>
        <div><dt class="text-muted">{{ __('Articles') }}</dt><dd class="mt-0.5 tabular-nums">{{ $event->article_count }}</dd></div>
        <div><dt class="text-muted">{{ __('First seen') }}</dt><dd class="mt-0.5">{{ $event->first_seen_at->mvt()->translatedFormat('j M, H:i') }}</dd></div>
    </dl>

    @if ($event->places->isNotEmpty())
        <p class="mt-4 text-sm">
            <span class="text-muted">{{ __('Where:') }}</span>
            @foreach ($event->places as $place)
                <a href="{{ route('atolls.show', $place->place_code) }}">{{ $place->name() }}</a>@unless ($loop->last), @endunless
            @endforeach
        </p>
    @endif

    @if ($event->situation)
        <div class="callout mt-6">
            {{ __('Part of a situation') }}:
            <a href="{{ route('situations.show', $event->situation) }}" class="font-medium">{{ $event->situation->displayTitle() }}</a>
            ({{ trans_choice(':count event|:count events', $event->situation->event_count) }})
        </div>
    @endif

    <h2 class="mt-10 text-xl">{{ __('Coverage, oldest first') }}</h2>
    <ol class="mt-3 divide-y divide-line border-y border-line">
        @foreach ($event->articles as $article)
            <li class="py-4">
                <p class="meta">{{ $article->source->name }} · {{ $article->published_at->mvt()->translatedFormat('j M, H:i') }}</p>
                <a href="{{ $article->url }}" rel="noopener nofollow" target="_blank" lang="{{ $article->lang }}" dir="{{ $article->lang === 'dv' ? 'rtl' : 'ltr' }}" class="mt-1 block font-medium">{{ $article->title }}</a>
                @if ($article->summary)
                    <p lang="{{ $article->lang }}" dir="{{ $article->lang === 'dv' ? 'rtl' : 'ltr' }}" class="mt-1 line-clamp-3 text-sm text-muted">{{ $article->summary }}</p>
                @endif
            </li>
        @endforeach
    </ol>
</x-layouts.app>
