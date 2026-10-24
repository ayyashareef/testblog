@props(['events', 'empty' => null])
@if ($events->isEmpty())
    <p class="callout">{{ $empty ?? __('No events yet.') }}</p>
@else
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('Event') }}</th>
                    <th class="num">{{ __('Sources') }}</th>
                    <th class="hidden sm:table-cell">{{ __('Level') }}</th>
                    <th class="hidden md:table-cell">{{ __('Where') }}</th>
                    <th class="num">{{ __('Updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($events as $event)
                    <tr>
                        <td>
                            <a href="{{ route('events.show', $event) }}" lang="{{ $event->isRtl() ? 'dv' : 'en' }}" dir="{{ $event->isRtl() ? 'rtl' : 'ltr' }}" class="font-medium text-ink">{{ $event->displayTitle() }}</a>
                        </td>
                        <td class="num">{{ $event->source_count }}</td>
                        <td class="hidden sm:table-cell"><x-tier :tier="$event->tier" /></td>
                        <td class="hidden text-muted md:table-cell">{{ $event->places->take(2)->map->name()->join(', ') ?: '—' }}</td>
                        <td class="num whitespace-nowrap text-muted" title="{{ $event->last_seen_at->mvt()->translatedFormat('j F Y H:i') }} MVT">{{ $event->last_seen_at->diffForHumans(short: true) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
