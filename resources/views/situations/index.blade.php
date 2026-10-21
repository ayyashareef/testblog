<x-layouts.app title="Situations">
    <p class="eyebrow">Stories over time</p>
    <h1 class="mt-2 text-2xl font-semibold">Situations</h1>
    <p class="mt-2 max-w-2xl text-ink-400">Related events chained together, so you can follow how a story developed over days or weeks instead of re-reading the same headline.</p>

    <ul class="mt-6 grid gap-3 sm:grid-cols-2">
        @forelse ($situations as $situation)
            <li>
                <a href="{{ route('situations.show', $situation) }}" class="panel block h-full p-4 no-underline hover:border-ink-600">
                    <div class="font-medium leading-snug">{{ $situation->title }}</div>
                    <div class="mt-2 font-mono text-[11px] text-ink-400">{{ $situation->event_count }} events · {{ $situation->article_count }} articles · {{ $situation->first_seen_at->mvt()->format('j M') }} – {{ $situation->last_seen_at->mvt()->format('j M') }}</div>
                </a>
            </li>
        @empty
            <li class="text-ink-400">No situations yet. They appear once related events have been reported on more than one occasion.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $situations->links() }}</div>
</x-layouts.app>
