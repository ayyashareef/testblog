<?php

use App\Services\News\Gazetteer;

it('prefers the longest matching place name', function () {
    $found = app(Gazetteer::class)->find('New school opens in Haa Dhaalu');

    expect($found)->toBe(['HDh' => 1]);
});

it('maps islands to their atoll', function () {
    expect(app(Gazetteer::class)->find('Fishermen in Fonadhoo and Thinadhoo'))
        ->toHaveKeys(['L', 'GDh']);
});

it('does not read the word male as Malé', function () {
    expect(app(Gazetteer::class)->find('A male tourist was rescued'))->toBe([]);
});

it('finds Dhivehi place names with case endings', function () {
    expect(app(Gazetteer::class)->find('އައްޑޫގައި ބޮޑު ވިއްސާރަ'))->toBe(['S' => 1]);
});
