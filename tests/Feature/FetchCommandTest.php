<?php

use App\Models\Article;
use App\Models\Event;
use App\Models\Source;
use Illuminate\Support\Facades\Http;

it('fetches feeds, skips broken ones and clusters the result', function () {
    $rss = fn (string $title) => '<?xml version="1.0"?><rss version="2.0"><channel><item><title>'.$title.'</title><link>https://example.com/'.md5($title).'</link><description>Hithadhoo homes flooded.</description><pubDate>'.now()->subHour()->toRssString().'</pubDate></item></channel></rss>';

    Http::fake([
        'en.sun.mv/*' => Http::response($rss('Heavy rain floods homes in Addu City')),
        'edition.mv/*' => Http::response($rss('Addu City homes flooded after heavy rain')),
        '*' => Http::response('down', 503),
    ]);

    $this->artisan('news:fetch')->assertSuccessful();
    $this->artisan('news:fetch')->assertSuccessful(); // second run stores nothing new

    expect(Article::count())->toBe(2)
        ->and(Event::count())->toBe(1)
        ->and(Source::where('slug', 'avas')->value('last_error'))->not->toBeNull();
});
