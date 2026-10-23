<?php

use App\Services\News\FeedParser;

it('parses RSS items', function () {
    $items = (new FeedParser)->parse(<<<'XML'
        <?xml version="1.0"?>
        <rss version="2.0"><channel>
          <item>
            <title>Heavy rain floods Addu City &amp; Fuvahmulah</title>
            <link>https://example.com/a</link>
            <description><![CDATA[<p>Roads under water.</p>]]></description>
            <pubDate>Mon, 19 Oct 2026 10:00:00 +0500</pubDate>
          </item>
          <item><title></title><link>https://example.com/empty</link></item>
        </channel></rss>
        XML);

    expect($items)->toHaveCount(1)
        ->and($items[0]['title'])->toBe('Heavy rain floods Addu City & Fuvahmulah')
        ->and($items[0]['summary'])->toBe('Roads under water.')
        ->and($items[0]['published_at']->toIso8601String())->toBe('2026-10-19T05:00:00+00:00');
});

it('parses Atom entries', function () {
    $items = (new FeedParser)->parse(<<<'XML'
        <?xml version="1.0"?>
        <feed xmlns="http://www.w3.org/2005/Atom">
          <entry>
            <id>tag:example.com,2026:1</id>
            <title>Thinadhoo harbour contract signed</title>
            <link rel="alternate" href="https://example.com/b"/>
            <summary>Gaafu Dhaalu harbour.</summary>
            <updated>2026-10-19T08:00:00Z</updated>
          </entry>
        </feed>
        XML);

    expect($items)->toHaveCount(1)
        ->and($items[0]['url'])->toBe('https://example.com/b');
});

it('rejects a response that is not XML', function () {
    (new FeedParser)->parse('<html><body>Not found');
})->throws(InvalidArgumentException::class);
