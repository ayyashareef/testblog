@props(['event'])
<li class="group py-3">
    <a href="{{ route('events.show', $event) }}" class="block no-underline">
        <div @if ($event->isRtl()) lang="dv" dir="rtl" @endif class="font-medium leading-snug text-ink-100 group-hover:text-ember-300">{{ $event->title }}</div>
        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[11px] text-ink-400">
            <x-tier :tier="$event->tier" />
            <span>{{ $event->source_count }} {{ Str::plural('source', $event->source_count) }}</span>
            <span>{{ $event->article_count }} {{ Str::plural('article', $event->article_count) }}</span>
            <span title="{{ $event->last_seen_at->mvt()->format('j M Y H:i') }} MVT">{{ $event->last_seen_at->diffForHumans(short: true) }}</span>
            @foreach ($event->places->take(3) as $place)
                <span class="text-lagoon/80">{{ $place->name() }}</span>
            @endforeach
        </div>
    </a>
</li>
