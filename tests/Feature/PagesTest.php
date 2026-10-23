<?php

use App\Models\Event;
use App\Models\MapSnapshot;
use App\Models\Situation;
use Database\Seeders\DemoNewsSeeder;

beforeEach(fn () => $this->seed(DemoNewsSeeder::class));

it('renders every page', function () {
    $situation = Situation::first();
    $snapshot = MapSnapshot::latest('hour')->first();

    foreach (['/', '/events', '/events?tier=major', '/events/'.Event::first()->id, '/situations', '/situations/'.$situation->slug, '/atolls/S', '/observatory', '/developers', $snapshot->permalink()] as $url) {
        $this->get($url)->assertOk();
    }

    $this->get('/atolls/NOPE')->assertNotFound();
    $this->get('/map/2001-01-01T00')->assertNotFound();
});

it('serves the JSON API', function () {
    $this->getJson('/api/v1/events?atoll=S')->assertOk()->assertJsonPath('data.0.atolls', fn ($a) => in_array('S', $a));
    $this->getJson('/api/v1/events/'.Event::first()->id)->assertOk()->assertJsonStructure(['data' => ['articles' => [['title', 'url', 'source']]]]);
    $this->getJson('/api/v1/situations/'.Situation::first()->slug)->assertOk()->assertJsonStructure(['data' => ['timeline']]);
    $this->getJson('/api/v1/map')->assertOk()->assertJsonCount(count(config('places')), 'data.atolls');
    $this->getJson('/api/v1/stats')->assertOk()->assertJsonPath('data.articles', 41);
    $this->getJson('/api/v1/sources')->assertOk();
    $this->getJson('/api/v1/events?tier=bogus')->assertUnprocessable();
});
