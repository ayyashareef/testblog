<?php

use App\Models\Event;
use App\Models\Source;
use App\Services\News\Clusterer;
use App\Services\News\Ingestor;

function article(string $source, string $title, string $summary, int $hoursAgo): void
{
    Source::syncFromConfig();

    app(Ingestor::class)->store(Source::where('slug', $source)->first(), [[
        'guid' => hash('sha256', $source.$title),
        'url' => 'https://example.com/'.md5($title),
        'title' => $title,
        'summary' => $summary,
        'published_at' => now()->subHours($hoursAgo),
    ]]);
}

it('groups reports of the same happening into one event', function () {
    article('sun', 'Heavy rain floods homes in Addu City', 'Homes in Hithadhoo flooded overnight.', 5);
    article('edition', 'Addu City flooding: homes inundated after heavy rain', 'Flood water entered homes in Hithadhoo.', 4);
    article('psm', 'STO raises diesel and petrol prices', 'Fuel prices up by 50 laari.', 3);

    app(Clusterer::class)->run();

    expect(Event::count())->toBe(2);

    $flood = Event::where('title', 'like', '%Addu%')->first();
    expect($flood->article_count)->toBe(2)
        ->and($flood->source_count)->toBe(2)
        ->and($flood->tier)->toBe('notable')
        ->and($flood->places->pluck('place_code')->all())->toBe(['S']);
});

it('chains a follow-up story into a situation', function () {
    article('sun', 'Heavy rain floods homes in Addu City', 'Homes in Hithadhoo flooded after heavy rain.', 150);
    article('edition', 'Flooding in Addu City after heavy rain', 'Hithadhoo homes flooded.', 149);
    article('sun', 'Addu City council seeks drainage upgrade after flooding', 'Hithadhoo homes flooded last week; council wants drainage.', 10);

    app(Clusterer::class)->run();

    $events = Event::orderBy('first_seen_at')->get();
    expect($events)->toHaveCount(2)
        ->and($events[0]->situation_id)->not->toBeNull()
        ->and($events[0]->situation_id)->toBe($events[1]->situation_id);
});

it('keeps unrelated stories in different atolls apart', function () {
    article('sun', 'Record whale shark sightings at Maamigili this season', 'Divers report record sightings.', 50);
    article('atolltimes', 'Fuvahmulah reports record tiger shark dives this season', 'Divers report record bookings.', 10);

    app(Clusterer::class)->run();

    expect(Event::whereNotNull('situation_id')->count())->toBe(0);
});
