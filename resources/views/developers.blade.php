@php
    $base = url('/api/v1');
    $dv = app()->getLocale() === 'dv';
    $endpoints = [
        ['/events', 'Latest events. Filters: tier, atoll (code, e.g. S for Addu), since (ISO date), per_page (max 100).'],
        ['/events/{id}', 'One event with every article that covered it.'],
        ['/situations', 'Chains of related events, newest first.'],
        ['/situations/{slug}', 'One situation with its dated timeline of events.'],
        ['/map', 'Events per atoll. Optional at (ISO time) and hours (1–168).'],
        ['/stats', 'Observatory numbers for the whole archive.'],
        ['/sources', 'Every outlet read and how many articles each contributed.'],
    ];
@endphp
<x-layouts.app :title="__('API')">
    <h1 class="text-3xl">{{ __('API') }}</h1>
    <p class="mt-3 text-lg text-muted">{{ __('Everything on this site is available as JSON. No key needed; please stay under 60 requests a minute.') }}</p>

    <h2 class="mt-10 text-xl">{{ __('Base URL') }}</h2>
    <pre dir="ltr" class="mt-3 overflow-x-auto rounded-md border border-line bg-sunken p-4 text-start font-mono text-sm">{{ $base }}</pre>

    <h2 class="mt-10 text-xl">{{ __('Endpoints') }}</h2>
    <table class="data-table mt-3">
        <thead><tr><th>{{ __('Request') }}</th><th>{{ __('Returns') }}</th></tr></thead>
        <tbody>
            @foreach ($endpoints as [$path, $about])
                <tr>
                    <td class="whitespace-nowrap" dir="ltr"><code>GET {{ $path }}</code></td>
                    <td>{{ __($about) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 class="mt-10 text-xl">{{ __('Example') }}</h2>
    <pre dir="ltr" class="mt-3 overflow-x-auto rounded-md border border-line bg-sunken p-4 text-start font-mono text-sm leading-relaxed">curl "{{ $base }}/events?atoll=S&amp;tier=major"

{
  "data": [
    {
      "id": 412,
      "title": "Flooding reported across Addu City after overnight rain",
      "title_dv": "އައްޑޫގައި ބޮޑު ވިއްސާރަ، ގެތަކަށް ފެން ވަދެއްޖެ",
      "tier": "major",
      "source_count": 5,
      "article_count": 9,
      "atolls": ["S"],
      "first_seen_at": "2026-10-20T03:12:00+00:00"
    }
  ],
  "current_page": 1
}</pre>

    <h2 class="mt-10 text-xl">{{ __('Atoll codes') }}</h2>
    <table class="data-table mt-3">
        <thead><tr><th>{{ __('Code') }}</th><th>{{ __('Atoll') }}</th></tr></thead>
        <tbody>
            @foreach (config('places') as $code => $place)
                <tr><td dir="ltr"><code>{{ $code }}</code></td><td>{{ $dv ? $place['name_dv'] : $place['name'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</x-layouts.app>
