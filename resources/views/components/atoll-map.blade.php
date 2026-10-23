@props(['map'])
@php
    $dv = app()->getLocale() === 'dv';
@endphp
<svg viewBox="0 0 {{ $map['width'] }} {{ $map['height'] }}" role="img" aria-label="{{ __('Dot map of the Maldives, each atoll coloured by how many news events mention it') }}" {{ $attributes->class('h-auto w-full') }} direction="ltr">
    <g>
        @foreach ($map['dots'] as $dot)
            <circle cx="{{ $dot['x'] }}" cy="{{ $dot['y'] }}" r="{{ $dot['r'] }}" fill="{{ $dot['fill'] }}"/>
        @endforeach
    </g>
    <g font-family="{{ $dv ? 'Noto Sans Thaana, Faruma, sans-serif' : 'JetBrains Mono, monospace' }}" font-size="{{ $dv ? 12 : 11 }}">
        @foreach ($map['labels'] as $label)
            <a href="{{ route('atolls.show', $label['code']) }}">
                <path d="M{{ $label['anchor_x'] }} {{ $label['anchor_y'] }} L{{ $label['x'] - 30 }} {{ $label['y'] }} H{{ $label['x'] - 6 }}" fill="none" stroke="#2a3a57" stroke-width="0.75"/>
                <circle cx="{{ $label['x'] - 2 }}" cy="{{ $label['y'] }}" r="3" fill="{{ $label['color'] }}"/>
                <text x="{{ $label['x'] + 6 }}" y="{{ $label['y'] + 4 }}" fill="{{ $label['count'] ? '#e6ebf3' : '#7d8ba6' }}">{{ $label['name'] }}</text>
                <text x="{{ $map['width'] - 4 }}" y="{{ $label['y'] + 4 }}" text-anchor="end" font-family="JetBrains Mono, monospace" font-size="11" fill="{{ $label['count'] ? '#f8b24a' : '#2a3a57' }}">{{ $label['count'] }}</text>
                <title>{{ $label['name'] }}: {{ trans_choice(':count event|:count events', $label['count']) }}</title>
            </a>
        @endforeach
    </g>
</svg>
