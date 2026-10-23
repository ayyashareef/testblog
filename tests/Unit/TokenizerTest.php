<?php

use App\Services\News\Tokenizer;

it('drops stop words and weights title words double', function () {
    $terms = (new Tokenizer)->terms('Flooding in Addu City', 'Addu homes flooded');

    expect($terms)->toHaveKey('addu')
        ->and($terms['addu'])->toBe(3)
        ->and($terms)->not->toHaveKey('in');
});

it('stems simple English endings', function () {
    expect((new Tokenizer)->words('floods flooding prices'))->toBe(['flood', 'flood', 'price']);
});

it('strips Dhivehi case endings', function () {
    $words = (new Tokenizer)->words('ހިތަދޫގައި ވިއްސާރަ');

    expect($words)->toContain('ހިތަދޫ');
});

it('measures the share of Thaana letters', function () {
    expect(Tokenizer::thaanaRatio('އައްޑޫގައި ބޮޑު ވިއްސާރަ'))->toBeGreaterThan(0.9)
        ->and(Tokenizer::thaanaRatio('Addu City'))->toBe(0.0);
});
