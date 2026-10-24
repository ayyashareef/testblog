<?php

namespace App\Services\News;

use App\Models\Article;
use App\Models\Source;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Downloads a source's feed and stores any articles not seen before.
 */
class Ingestor
{
    public function __construct(
        private FeedParser $parser,
        private Tokenizer $tokenizer,
    ) {}

    /**
     * @return int number of new articles stored
     */
    public function fetch(Source $source): int
    {
        try {
            $response = Http::withHeaders(['User-Agent' => config('news.user_agent')])
                ->timeout(config('news.http_timeout'))
                ->get($source->feed_url)
                ->throw();

            $items = $this->parser->parse($response->body());
        } catch (\Throwable $e) {
            Log::warning("Feed {$source->slug} failed: {$e->getMessage()}");
            $source->update(['last_fetched_at' => now(), 'last_error' => mb_substr($e->getMessage(), 0, 500)]);

            return 0;
        }

        $stored = $this->store($source, $items);
        $source->update(['last_fetched_at' => now(), 'last_error' => null]);

        return $stored;
    }

    /**
     * @param  list<array{guid: string, url: string, title: string, summary: string, published_at: \DateTimeInterface}>  $items
     */
    public function store(Source $source, array $items): int
    {
        $known = Article::where('source_id', $source->id)
            ->whereIn('guid', array_column($items, 'guid'))
            ->pluck('guid')
            ->flip();

        $stored = 0;
        foreach ($items as $item) {
            if (isset($known[$item['guid']])) {
                continue;
            }

            $title = $this->stripAggregatorSuffix($source, $item['title']);

            try {
                Article::create([
                    'source_id' => $source->id,
                    'guid' => $item['guid'],
                    'url' => $item['url'],
                    'title' => $title,
                    'summary' => $item['summary'],
                    'lang' => Tokenizer::thaanaRatio($title) > 0.3 ? 'dv' : 'en',
                    'published_at' => $item['published_at'],
                    'tokens' => $this->tokenizer->terms($title, $item['summary']),
                ]);
            } catch (UniqueConstraintViolationException) {
                // An overlapping run stored it first.
                continue;
            }

            $known[$item['guid']] = true;
            $stored++;
        }

        return $stored;
    }

    /**
     * Google News titles end in " - Publisher"; that suffix would make every
     * article from one publisher look alike.
     */
    private function stripAggregatorSuffix(Source $source, string $title): string
    {
        if (! str_contains($source->feed_url, 'news.google.')) {
            return $title;
        }

        return preg_replace('/\s+[-–|]\s+[^-–|]{2,60}$/u', '', $title);
    }
}
