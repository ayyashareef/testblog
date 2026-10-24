@props(['map'])
@php
    $groups = collect($map['dots'])->groupBy(fn ($d) => $d['level'].($d['ring'] ? 'r' : 'l'));
@endphp
<svg viewBox="0 0 {{ $map['width'] }} {{ $map['height'] }}" role="img" aria-label="{{ __('Dot map of the Maldives, each atoll coloured by how many news events mention it') }}" {{ $attributes->class('h-auto w-full') }} direction="ltr">
    @foreach ($groups as $key => $dots)
        <g class="heat-{{ (int) $key }}" @if (str_ends_with($key, 'l')) opacity="0.4" @endif>
            @foreach ($dots as $dot)
                <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="{{ $dot['ring'] ? 1.7 : 1.1 }}"/>
            @endforeach
        </g>
    @endforeach
    <g font-size="15">
        @foreach ($map['labels'] as $label)
            <a href="{{ route('atolls.show', $label['code']) }}" @class(['text-ink' => $label['count'], 'text-muted' => ! $label['count']])>
                <path d="M{{ $label['anchor_x'] }} {{ $label['anchor_y'] }} L{{ $label['x'] - 30 }} {{ $label['y'] }} H{{ $label['x'] - 6 }}" fill="none" stroke="currentColor" stroke-opacity="0.35" stroke-width="0.75"/>
                <circle cx="{{ $label['x'] - 2 }}" cy="{{ $label['y'] }}" r="3" class="heat-{{ $label['level'] }}"/>
                <text x="{{ $label['x'] + 6 }}" y="{{ $label['y'] + 5 }}" fill="currentColor" @class(['font-medium' => $label['count']])>{{ $label['name'] }}</text>
                <text x="{{ $map['width'] - 4 }}" y="{{ $label['y'] + 5 }}" text-anchor="end" fill="currentColor" class="tabular-nums">{{ $label['count'] ?: '' }}</text>
                <title>{{ $label['name'] }}: {{ trans_choice(':count event|:count events', $label['count']) }}</title>
            </a>
        @endforeach
    </g>
</svg>
