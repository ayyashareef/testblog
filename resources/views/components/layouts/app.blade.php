@props(['title' => null, 'wide' => false])
@php
    $dv = app()->getLocale() === 'dv';
    $sections = [
        'Overview' => ['home' => 'Now', 'events.index' => 'Events', 'situations.index' => 'Situations'],
        'Data' => ['observatory' => 'Observatory', 'developers' => 'API'],
    ];
    $nav = function () use ($sections, $dv) {
        $html = '';
        foreach ($sections as $heading => $links) {
            $html .= '<p class="sidebar-heading">'.e(__($heading)).'</p>';
            foreach ($links as $route => $label) {
                $current = request()->routeIs($route) ? ' aria-current="page"' : '';
                $html .= '<a class="sidebar-link" href="'.e(route($route)).'"'.$current.'>'.e(__($label)).'</a>';
            }
        }
        $html .= '<p class="sidebar-heading">'.e(__('Atolls')).'</p>';
        foreach (config('places') as $code => $place) {
            $current = request()->routeIs('atolls.show') && request()->route('code') === $code ? ' aria-current="page"' : '';
            $html .= '<a class="sidebar-link py-1" href="'.e(route('atolls.show', $code)).'"'.$current.'>'.e($dv ? $place['name_dv'] : $place['name']).'</a>';
        }

        return $html;
    };
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dv ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}{{ __('Raajje Watch') }}</title>
    <meta name="description" content="{{ __('Every Maldivian news story, read from every outlet, grouped into events and mapped by atoll.') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="sticky top-0 z-10 border-b border-line bg-page/95 backdrop-blur">
        <div class="mx-auto flex h-14 max-w-7xl items-center gap-4 px-4">
            <a href="{{ route('home') }}" class="font-semibold text-ink no-underline hover:no-underline">{{ __('Raajje Watch') }}</a>
            <span class="hidden text-sm text-muted sm:inline">{{ __('Maldives news, grouped by event') }}</span>
            <a href="{{ request()->fullUrlWithQuery(['lang' => $dv ? 'en' : 'dv']) }}" @unless ($dv) lang="dv" @endunless class="ms-auto text-sm">{{ $dv ? 'English' : 'ދިވެހި' }}</a>
        </div>
    </header>

    <div class="mx-auto max-w-7xl px-4 lg:grid lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-10">
        <details class="border-b border-line py-3 lg:hidden">
            <summary class="cursor-pointer text-sm font-medium">{{ __('Menu') }}</summary>
            <nav class="mt-2 pb-2">{!! $nav() !!}</nav>
        </details>

        <aside class="hidden lg:block">
            <nav class="sticky top-14 max-h-[calc(100vh-3.5rem)] overflow-y-auto py-8 pe-2">{!! $nav() !!}</nav>
        </aside>

        <main @class(['min-w-0 py-8', 'max-w-prose' => ! $wide])>
            {{ $slot }}

            <footer class="mt-16 border-t border-line pt-6 text-sm text-muted">
                {{ __('Raajje Watch reads Maldivian news feeds, groups articles about the same happening into events, and links related events into situations. Headlines link to the original publishers.') }}
            </footer>
        </main>
    </div>
</body>
</html>
