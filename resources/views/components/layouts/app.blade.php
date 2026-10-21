<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title.' · ' : '' }}Raajje Watch</title>
    <meta name="description" content="Every Maldivian news story, read from every outlet, grouped into events and mapped by atoll.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&family=Noto+Sans+Thaana:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">
    <header class="border-b border-ink-800/80">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 no-underline">
                <svg width="22" height="22" viewBox="0 0 22 22" aria-hidden="true">
                    <ellipse cx="11" cy="11" rx="7" ry="9" fill="none" stroke="#ea741a" stroke-width="2" stroke-dasharray="2 2.4"/>
                    <circle cx="11" cy="11" r="2" fill="#fde68a"/>
                </svg>
                <span class="font-mono text-sm font-semibold tracking-[0.2em] text-ink-100">RAAJJE<span class="text-ember-400">.</span>WATCH</span>
            </a>
            <nav class="flex flex-wrap gap-x-5 gap-y-1 text-sm text-ink-300">
                @foreach (['home' => 'Now', 'events.index' => 'Events', 'situations.index' => 'Situations', 'observatory' => 'Observatory', 'developers' => 'API'] as $route => $label)
                    <a href="{{ route($route) }}" @class(['no-underline hover:text-ink-100', 'text-ember-300' => request()->routeIs($route)])>{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-6xl px-4 pb-10 pt-4 text-xs text-ink-400">
        Raajje Watch reads Maldivian news feeds, groups articles about the same happening into events, and links related events into situations. Headlines link to the original publishers.
    </footer>
</body>
</html>
