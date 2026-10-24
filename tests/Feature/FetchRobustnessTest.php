<?php

use App\Models\Article;
use App\Models\Event;
use App\Models\Source;
use App\Services\News\Ingestor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

function feedWith(string $title): string
{
    return '<?xml version="1.0"?><rss version="2.0"><channel><item><title>'.$title.'</title><link>https://example.com/'.md5($title).'</link><description>Hithadhoo homes flooded.</description><pubDate>'.now()->subHour()->toRssString().'</pubDate></item></channel></rss>';
}

it('skips an article another run saved a moment earlier', function () {
    Source::syncFromConfig();
    $source = Source::where('slug', 'sun')->first();
    $item = ['guid' => hash('sha256', 'x'), 'url' => 'https://example.com/x', 'title' => 'Heavy rain floods Addu City', 'summary' => '', 'published_at' => now()];

    // A second, overlapping run inserts the same article between our
    // "already known?" check and our insert.
    Article::creating(function (Article $article) {
        static $raced = false;
        if (! $raced) {
            $raced = true;
            DB::table('articles')->insert($article->getAttributes() + ['created_at' => now(), 'updated_at' => now()]);
        }
    });

    expect(app(Ingestor::class)->store($source, [$item]))->toBe(0)
        ->and(Article::count())->toBe(1);
});

it('does not start while another fetch is running', function () {
    Http::fake();
    $lock = Cache::lock('news:fetch', 60);
    $lock->get();

    $this->artisan('news:fetch')->assertSuccessful();

    Http::assertNothingSent();
    $lock->release();
});

it('still clusters when one source fails unexpectedly', function () {
    Http::fake([
        'en.sun.mv/*' => Http::response(feedWith('Heavy rain floods homes in Addu City')),
        '*' => Http::response('down', 503),
    ]);

    $real = app(Ingestor::class);
    $this->mock(Ingestor::class, function ($mock) use ($real) {
        $mock->shouldReceive('fetch')->andReturnUsing(function (Source $source) use ($real) {
            if ($source->slug === 'edition') {
                throw new RuntimeException('database is locked');
            }

            return $real->fetch($source);
        });
    });

    $this->artisan('news:fetch')->assertSuccessful();

    expect(Event::count())->toBe(1)
        ->and(Source::where('slug', 'edition')->value('last_error'))->toContain('database is locked');
});
