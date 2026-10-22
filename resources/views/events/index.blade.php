@php
    $dv = app()->getLocale() === 'dv';
@endphp
<x-layouts.app :title="isset($place) ? ($dv ? $place['name_dv'] : $place['name']) : __('Events')">
    @isset($place)
        <p class="eyebrow">{{ __('Atoll') }}</p>
        <h1 class="mt-2 text-2xl font-semibold">
            {{ $dv ? $place['name_dv'] : $place['name'] }}
            <span lang="{{ $dv ? 'en' : 'dv' }}" class="ms-2 text-xl text-ink-400">{{ $dv ? $place['name'] : $place['name_dv'] }}</span>
        </h1>
        <p class="mt-2 text-ink-400">{{ __('Events that mention :name or one of its islands.', ['name' => $dv ? $place['name_dv'] : $place['name']]) }}</p>
    @else
        <p class="eyebrow">{{ __('Archive') }}</p>
        <h1 class="mt-2 text-2xl font-semibold">{{ __('Events') }}</h1>
        <div class="mt-4 flex flex-wrap gap-2 font-mono text-xs">
            <a href="{{ route('events.index') }}" @class(['tier rounded-full border px-3 py-1 no-underline', 'border-ember-400 text-ember-300' => ! $tier, 'border-ink-700 text-ink-300' => $tier])>{{ __('all') }}</a>
            @foreach (\App\Models\Event::TIERS as $t)
                <a href="{{ route('events.index', ['tier' => $t]) }}" @class(['tier rounded-full border px-3 py-1 no-underline', 'border-ember-400 text-ember-300' => $tier === $t, 'border-ink-700 text-ink-300' => $tier !== $t])>{{ __($t) }}</a>
            @endforeach
        </div>
    @endisset

    <ul class="mt-6 divide-y divide-ink-800">
        @forelse ($events as $event)
            <x-event-row :event="$event" />
        @empty
            <li class="py-6 text-ink-400">{{ __('No events yet.') }}</li>
        @endforelse
    </ul>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.app>
