@php
    $dv = app()->getLocale() === 'dv';
@endphp
<x-layouts.app :wide="true" :title="isset($place) ? ($dv ? $place['name_dv'] : $place['name']) : __('Events')">
    <div class="max-w-prose">
        @isset($place)
            <p class="breadcrumb"><span>{{ __('Atolls') }}</span> <span>/</span></p>
            <h1 class="text-3xl">{{ $dv ? $place['name_dv'] : $place['name'] }}
                <span lang="{{ $dv ? 'en' : 'dv' }}" class="ms-2 text-xl font-normal text-muted">{{ $dv ? $place['name'] : $place['name_dv'] }}</span>
            </h1>
            <p class="mt-3 text-lg text-muted">{{ __('Events that mention :name or one of its islands.', ['name' => $dv ? $place['name_dv'] : $place['name']]) }}</p>
        @else
            <h1 class="text-3xl">{{ __('Events') }}</h1>
            <p class="mt-3 text-lg text-muted">{{ __('Every event, newest first. An event is one happening, however many outlets reported it.') }}</p>
            <p class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-sm">
                <span class="text-muted">{{ __('Level:') }}</span>
                @foreach ([null, ...\App\Models\Event::TIERS] as $t)
                    @if ($tier === $t)
                        <span class="font-medium text-ink">{{ $t ? __(ucfirst($t)) : __('All') }}</span>
                    @else
                        <a href="{{ route('events.index', $t ? ['tier' => $t] : []) }}">{{ $t ? __(ucfirst($t)) : __('All') }}</a>
                    @endif
                @endforeach
            </p>
        @endisset
    </div>

    <div class="mt-8">
        <x-event-table :events="$events" />
    </div>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layouts.app>
