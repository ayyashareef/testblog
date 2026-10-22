@props(['event'])
<li class="group py-3">
    <a href="{{ route('events.show', $event) }}" class="block no-underline">
        <div lang="{{ $event->isRtl() ? 'dv' : 'en' }}" dir="{{ $event->isRtl() ? 'rtl' : 'ltr' }}" class="font-medium leading-snug text-ink-100 group-hover:text-ember-300">{{ $event->displayTitle() }}</div>
        <div class="meta mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[11px] text-ink-400">
            <x-tier :tier="$event->tier" />
            <span>{{ trans_choice(':count source|:count sources', $event->source_count) }}</span>
            <span>{{ trans_choice(':count article|:count articles', $event->article_count) }}</span>
            <span title="{{ $event->last_seen_at->mvt()->translatedFormat('j F Y H:i') }} MVT">{{ $event->last_seen_at->diffForHumans() }}</span>
            @foreach ($event->places->take(3) as $place)
                <span class="text-lagoon/80">{{ $place->name() }}</span>
            @endforeach
        </div>
    </a>
</li>
