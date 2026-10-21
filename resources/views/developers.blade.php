@php
    $base = url('/api/v1');
    $endpoints = [
        ['GET', '/events', 'Latest events. Filters: tier, atoll (code, e.g. S for Addu), since (ISO date), per_page (max 100).'],
        ['GET', '/events/{id}', 'One event with every article that covered it.'],
        ['GET', '/situations', 'Chains of related events, newest first.'],
        ['GET', '/situations/{slug}', 'One situation with its dated timeline of events.'],
        ['GET', '/map', 'Events per atoll. Optional at (ISO time) and hours (1–168).'],
        ['GET', '/stats', 'Observatory numbers for the whole archive.'],
        ['GET', '/sources', 'Every outlet read and how many articles each contributed.'],
    ];
@endphp
<x-layouts.app title="API">
    <p class="eyebrow">Developers</p>
    <h1 class="mt-2 text-2xl font-semibold">A free JSON API</h1>
    <p class="mt-2 max-w-2xl text-ink-400">Everything on this site is available as JSON. No key needed; please stay under 60 requests a minute.</p>

    <div class="panel mt-6 divide-y divide-ink-800">
        @foreach ($endpoints as [$method, $path, $about])
            <div class="grid gap-1 p-4 sm:grid-cols-[220px_1fr] sm:gap-4">
                <code class="font-mono text-sm"><span class="text-lagoon">{{ $method }}</span> {{ $path }}</code>
                <p class="text-sm text-ink-300">{{ $about }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="eyebrow mt-8">Example</h2>
    <pre class="panel mt-3 overflow-x-auto p-4 font-mono text-xs leading-relaxed text-ink-300">curl {{ $base }}/events?atoll=S&amp;tier=major

{
  "data": [
    {
      "id": 412,
      "title": "Flooding reported across Addu City after overnight rain",
      "tier": "major",
      "source_count": 5,
      "article_count": 9,
      "atolls": ["S"],
      "first_seen_at": "2026-10-20T03:12:00+00:00",
      ...
    }
  ],
  "current_page": 1,
  ...
}</pre>

    <h2 class="eyebrow mt-8">Atoll codes</h2>
    <div class="mt-3 grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-4">
        @foreach (config('places') as $code => $place)
            <div><code class="font-mono text-ember-400">{{ $code }}</code> <span class="text-ink-300">{{ $place['name'] }}</span></div>
        @endforeach
    </div>
</x-layouts.app>
