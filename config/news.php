<?php

return [

    /*
    |--------------------------------------------------------------------------
    | News sources
    |--------------------------------------------------------------------------
    |
    | Feeds read by `php artisan news:fetch`. Any RSS 2.0 or Atom feed works.
    | Feed URLs change from time to time; a failing feed is logged and skipped
    | so one broken source never stops the rest of the run.
    |
    */

    'sources' => [
        ['slug' => 'sun', 'name' => 'Sun Online', 'url' => 'https://en.sun.mv', 'feed' => 'https://en.sun.mv/feed', 'lang' => 'en'],
        ['slug' => 'edition', 'name' => 'Edition.mv', 'url' => 'https://edition.mv', 'feed' => 'https://edition.mv/feed', 'lang' => 'en'],
        ['slug' => 'atolltimes', 'name' => 'Atoll Times', 'url' => 'https://atolltimes.mv', 'feed' => 'https://atolltimes.mv/feed', 'lang' => 'en'],
        ['slug' => 'psm', 'name' => 'PSM News', 'url' => 'https://psmnews.mv/en', 'feed' => 'https://psmnews.mv/en/feed', 'lang' => 'en'],
        ['slug' => 'corporatemaldives', 'name' => 'Corporate Maldives', 'url' => 'https://corporatemaldives.com', 'feed' => 'https://corporatemaldives.com/feed/', 'lang' => 'en'],
        ['slug' => 'maldivesindependent', 'name' => 'Maldives Independent', 'url' => 'https://maldivesindependent.com', 'feed' => 'https://maldivesindependent.com/feed', 'lang' => 'en'],
        ['slug' => 'avas', 'name' => 'Avas', 'url' => 'https://avas.mv', 'feed' => 'https://avas.mv/feed', 'lang' => 'dv'],
        ['slug' => 'mihaaru', 'name' => 'Mihaaru', 'url' => 'https://mihaaru.com', 'feed' => 'https://mihaaru.com/rss', 'lang' => 'dv'],
        ['slug' => 'sun-dv', 'name' => 'Sun (Dhivehi)', 'url' => 'https://sun.mv', 'feed' => 'https://sun.mv/feed', 'lang' => 'dv'],
        ['slug' => 'gnews', 'name' => 'Google News: Maldives', 'url' => 'https://news.google.com', 'feed' => 'https://news.google.com/rss/search?q=Maldives&hl=en&gl=MV&ceid=MV:en', 'lang' => 'en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Clustering
    |--------------------------------------------------------------------------
    |
    | event_threshold:     similarity needed for an article to join an event
    | event_window_hours:  how far back an event can still absorb new articles
    | situation_threshold: similarity needed to chain two events into a situation
    | situation_window_days: how far back a situation stays open
    |
    */

    'clustering' => [
        'event_threshold' => (float) env('NEWS_EVENT_THRESHOLD', 0.32),
        'event_window_hours' => (int) env('NEWS_EVENT_WINDOW_HOURS', 48),
        'situation_threshold' => (float) env('NEWS_SITUATION_THRESHOLD', 0.18),
        'situation_window_days' => (int) env('NEWS_SITUATION_WINDOW_DAYS', 21),
    ],

    // The map colours each atoll by events touching it in this many hours.
    'map_window_hours' => (int) env('NEWS_MAP_WINDOW_HOURS', 24),

    'http_timeout' => 15,
    'user_agent' => 'RaajjeWatch/1.0 (+https://github.com/ayyashareef/testblog)',
];
