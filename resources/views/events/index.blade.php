<x-layouts.app :title="isset($place) ? $place['name'] : 'Events'">
    @isset($place)
        <p class="eyebrow">Atoll</p>
        <h1 class="mt-2 text-2xl font-semibold">{{ $place['name'] }} <span lang="dv" class="ml-2 text-xl text-ink-400">{{ $place['name_dv'] }}</span></h1>
        <p class="mt-2 text-ink-400">Events that mention {{ $place['name'] }} or one of its islands.</p>
    @else
        <p class="eyebrow">Archive</p>
        <h1 class="mt-2 text-2xl font-semibold">Events</h1>
        <div class="mt-4 flex flex-wrap gap-2 font-mono text-xs">
            <a href="{{ route('events.index') }}" @class(['rounded-full border px-3 py-1 no-underline', 'border-ember-400 text-ember-300' => ! $tier, 'border-ink-700 text-ink-300' => $tier])>all</a>
            @foreach (\App\Models\Event::TIERS as $t)
                <a href="{{ route('events.index', ['tier' => $t]) }}" @class(['rounded-full border px-3 py-1 no-underline', 'border-ember-400 text-ember-300' => $tier === $t, 'border-ink-700 text-ink-300' => $tier !== $t])>{{ $t }}</a>
            @endforeach
        </div>
    @endisset

    <ul class="mt-6 divide-y divide-ink-800">
        @forelse ($events as $event)
            <x-event-row :event="$event" />
        @empty
            <li class="py-6 text-ink-400">No events yet.</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.app>
