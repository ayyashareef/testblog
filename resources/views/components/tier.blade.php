@props(['tier'])
<span {{ $attributes->class(['whitespace-nowrap', 'font-medium text-accent-ink' => in_array($tier, ['major', 'exceptional']), 'text-muted' => ! in_array($tier, ['major', 'exceptional'])]) }}>{{ __(ucfirst($tier)) }}</span>
