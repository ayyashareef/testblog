<?php

use App\Models\Event;
use Database\Seeders\DemoNewsSeeder;
use Illuminate\Support\Facades\File;

beforeEach(fn () => $this->seed(DemoNewsSeeder::class));

it('switches to Dhivehi and remembers the choice', function () {
    $this->get('/?lang=dv')
        ->assertOk()
        ->assertSee('dir="rtl"', false)
        ->assertSee('ރާއްޖެ މިހާރު')
        ->assertSee('އައްޑު');

    $this->get('/observatory')->assertSee('lang="dv"', false)->assertSee('ތަފާސްހިސާބު');

    $this->get('/?lang=en')->assertSee('dir="ltr"', false)->assertSee('The Maldives right now');
});

it('renders every page in Dhivehi', function () {
    $this->get('/?lang=dv');

    foreach (['/', '/events', '/events/'.Event::first()->id, '/situations', '/atolls/S', '/observatory', '/developers'] as $url) {
        $this->get($url)->assertOk()->assertSee('dir="rtl"', false);
    }
});

it('shows the Dhivehi headline of an event when there is one', function () {
    $event = Event::whereNotNull('title_dv')->firstOrFail();

    $this->get('/events/'.$event->id.'?lang=dv')->assertSee($event->title_dv);
});

it('has a Dhivehi translation for every interface string', function () {
    $translations = json_decode(File::get(lang_path('dv.json')), true);

    $missing = collect(File::allFiles(resource_path('views')))
        ->flatMap(fn ($file) => preg_match_all("/(?:__|trans_choice)\\('((?:[^'\\\\]|\\\\.)*)'/", $file->getContents(), $m) ? $m[1] : [])
        ->map(fn ($key) => str_replace("\\'", "'", $key))
        ->unique()
        ->reject(fn ($key) => isset($translations[$key]))
        ->values()
        ->all();

    expect($missing)->toBe([]);
});
