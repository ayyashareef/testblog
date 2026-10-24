<x-layouts.app :title="__('Situations')">
    <h1 class="text-3xl">{{ __('Situations') }}</h1>
    <p class="mt-3 text-lg text-muted">{{ __('Related events chained together, so you can follow how a story developed over days or weeks instead of re-reading the same headline.') }}</p>

    @if ($situations->isEmpty())
        <p class="callout mt-8">{{ __('No situations yet. They appear once related events have been reported on more than one occasion.') }}</p>
    @else
        <table class="data-table mt-8">
            <thead>
                <tr>
                    <th>{{ __('Situation') }}</th>
                    <th class="num">{{ __('Events') }}</th>
                    <th class="num">{{ __('Dates') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($situations as $situation)
                    <tr>
                        <td><a href="{{ route('situations.show', $situation) }}" lang="{{ $situation->isRtl() ? 'dv' : 'en' }}" class="font-medium text-ink">{{ $situation->displayTitle() }}</a></td>
                        <td class="num">{{ $situation->event_count }}</td>
                        <td class="num whitespace-nowrap text-muted">{{ $situation->first_seen_at->mvt()->translatedFormat('j M') }} – {{ $situation->last_seen_at->mvt()->translatedFormat('j M') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="mt-6">{{ $situations->links() }}</div>
</x-layouts.app>
