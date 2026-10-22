<x-layouts.app :title="__('Situations')">
    <p class="eyebrow">{{ __('Stories over time') }}</p>
    <h1 class="mt-2 text-2xl font-semibold">{{ __('Situations') }}</h1>
    <p class="mt-2 max-w-2xl text-ink-400">{{ __('Related events chained together, so you can follow how a story developed over days or weeks instead of re-reading the same headline.') }}</p>

    <ul class="mt-6 grid gap-3 sm:grid-cols-2">
        @forelse ($situations as $situation)
            <li>
                <a href="{{ route('situations.show', $situation) }}" class="panel block h-full p-4 no-underline hover:border-ink-600">
                    <div class="font-medium leading-snug" lang="{{ $situation->isRtl() ? 'dv' : 'en' }}" dir="{{ $situation->isRtl() ? 'rtl' : 'ltr' }}">{{ $situation->displayTitle() }}</div>
                    <div class="meta mt-2 font-mono text-[11px] text-ink-400">{{ trans_choice(':count event|:count events', $situation->event_count) }} · {{ trans_choice(':count article|:count articles', $situation->article_count) }} · {{ $situation->first_seen_at->mvt()->translatedFormat('j M') }} – {{ $situation->last_seen_at->mvt()->translatedFormat('j M') }}</div>
                </a>
            </li>
        @empty
            <li class="text-ink-400">{{ __('No situations yet. They appear once related events have been reported on more than one occasion.') }}</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $situations->links() }}</div>
</x-layouts.app>
