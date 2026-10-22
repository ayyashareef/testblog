<x-layouts.app :title="$event->displayTitle()">
    <p class="eyebrow">{{ __('Event') }} · {{ $event->first_seen_at->mvt()->translatedFormat('j F Y, H:i') }} MVT</p>
    <h1 lang="{{ $event->isRtl() ? 'dv' : 'en' }}" dir="{{ $event->isRtl() ? 'rtl' : 'ltr' }}" class="mt-3 max-w-3xl text-2xl font-semibold leading-tight sm:text-3xl">{{ $event->displayTitle() }}</h1>

    <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2">
        <x-tier :tier="$event->tier" />
        <span class="stat">{{ $event->source_count }}<small>{{ trans_choice('source|sources', $event->source_count) }}</small></span>
        <span class="stat">{{ $event->article_count }}<small>{{ trans_choice('article|articles', $event->article_count) }}</small></span>
        <span class="stat">{{ $event->first_seen_at->diffForHumans($event->last_seen_at, ['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'short' => true]) }}<small>{{ __('of coverage') }}</small></span>
    </div>

    @if ($event->places->isNotEmpty())
        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            @foreach ($event->places as $place)
                <a href="{{ route('atolls.show', $place->place_code) }}" class="rounded-full border border-lagoon/30 px-3 py-0.5 text-lagoon no-underline hover:border-lagoon">{{ $place->name() }}</a>
            @endforeach
        </div>
    @endif

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_320px]">
        <section>
            <h2 class="eyebrow">{{ __('Coverage, oldest first') }}</h2>
            <ol class="mt-3 space-y-3">
                @foreach ($event->articles as $article)
                    <li class="panel p-4">
                        <div class="flex items-center justify-between gap-3 font-mono text-[11px] text-ink-400">
                            <span class="text-ink-300">{{ $article->source->name }}</span>
                            <span>{{ $article->published_at->mvt()->translatedFormat('j M H:i') }}</span>
                        </div>
                        <a href="{{ $article->url }}" rel="noopener nofollow" target="_blank" lang="{{ $article->lang }}" dir="{{ $article->lang === 'dv' ? 'rtl' : 'ltr' }}" class="link mt-1 block font-medium">{{ $article->title }}</a>
                        @if ($article->summary)
                            <p lang="{{ $article->lang }}" dir="{{ $article->lang === 'dv' ? 'rtl' : 'ltr' }}" class="mt-1 line-clamp-3 text-sm text-ink-400">{{ $article->summary }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </section>

        @if ($event->situation)
            <aside>
                <h2 class="eyebrow">{{ __('Part of a situation') }}</h2>
                <a href="{{ route('situations.show', $event->situation) }}" class="link mt-2 block font-medium">{{ $event->situation->displayTitle() }}</a>
                <ol class="mt-4 border-s border-ink-700 ps-4">
                    @foreach ($event->situation->events as $step)
                        <li class="relative pb-4">
                            <span @class(['absolute -start-[21px] top-1.5 h-2 w-2 rounded-full', 'bg-ember-400' => $step->is($event), 'bg-ink-600' => ! $step->is($event)])></span>
                            <div class="font-mono text-[11px] text-ink-400">{{ $step->first_seen_at->mvt()->translatedFormat('j M') }}</div>
                            @if ($step->is($event))
                                <div class="text-sm text-ember-300">{{ $step->displayTitle() }}</div>
                            @else
                                <a href="{{ route('events.show', $step) }}" class="text-sm text-ink-300 no-underline hover:text-ink-100">{{ $step->displayTitle() }}</a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </aside>
        @endif
    </div>
</x-layouts.app>
