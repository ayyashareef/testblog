<?php

namespace App\Services\News;

use Carbon\CarbonImmutable;
use SimpleXMLElement;

/**
 * Parses RSS 2.0 and Atom feeds into plain item arrays.
 */
class FeedParser
{
    /**
     * @return list<array{guid: string, url: string, title: string, summary: string, published_at: CarbonImmutable}>
     */
    public function parse(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $feed = simplexml_load_string(trim($xml), SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($feed === false) {
            throw new \InvalidArgumentException('Response is not valid XML.');
        }

        $items = match (true) {
            isset($feed->channel->item) => $this->rssItems($feed->channel->item),
            isset($feed->item) => $this->rssItems($feed->item), // RSS 1.0 / RDF
            isset($feed->entry) => $this->atomEntries($feed->entry),
            default => [],
        };

        return array_values(array_filter($items, fn ($item) => $item['title'] !== '' && $item['url'] !== ''));
    }

    private function rssItems(SimpleXMLElement $items): array
    {
        $parsed = [];

        foreach ($items as $item) {
            $content = $item->children('http://purl.org/rss/1.0/modules/content/');
            $dc = $item->children('http://purl.org/dc/elements/1.1/');

            $url = trim((string) $item->link);
            $guid = trim((string) $item->guid) ?: $url;

            $parsed[] = $this->item(
                guid: $guid,
                url: $url,
                title: (string) $item->title,
                summary: (string) ($item->description ?: $content->encoded),
                date: (string) ($item->pubDate ?: $dc->date),
            );
        }

        return $parsed;
    }

    private function atomEntries(SimpleXMLElement $entries): array
    {
        $parsed = [];

        foreach ($entries as $entry) {
            $url = '';
            foreach ($entry->link as $link) {
                $rel = (string) $link['rel'];
                if ($rel === '' || $rel === 'alternate') {
                    $url = (string) $link['href'];
                    break;
                }
            }

            $parsed[] = $this->item(
                guid: trim((string) $entry->id) ?: $url,
                url: $url,
                title: (string) $entry->title,
                summary: (string) ($entry->summary ?: $entry->content),
                date: (string) ($entry->published ?: $entry->updated),
            );
        }

        return $parsed;
    }

    private function item(string $guid, string $url, string $title, string $summary, string $date): array
    {
        try {
            $published = $date !== '' ? CarbonImmutable::parse($date)->utc() : CarbonImmutable::now();
        } catch (\Throwable) {
            $published = CarbonImmutable::now();
        }

        // Feeds sometimes carry future-dated items; never trust a clock ahead of ours.
        if ($published->isFuture()) {
            $published = CarbonImmutable::now();
        }

        return [
            'guid' => hash('sha256', $guid),
            'url' => trim($url),
            'title' => $this->clean($title),
            'summary' => mb_substr($this->clean($summary), 0, 1000),
            'published_at' => $published,
        ];
    }

    private function clean(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
