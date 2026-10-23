@props(['tier'])
@php
    $styles = [
        'exceptional' => 'border-ember-300/60 bg-ember-300/10 text-ember-300',
        'major' => 'border-ember-500/50 bg-ember-500/10 text-ember-400',
        'notable' => 'border-ink-600 text-ink-300',
        'routine' => 'border-ink-700 text-ink-400',
    ];
@endphp
<span {{ $attributes->class(['tier inline-block rounded-full border px-2 py-px font-mono text-[10px] uppercase tracking-wider', $styles[$tier] ?? $styles['routine']]) }}>{{ __($tier) }}</span>
