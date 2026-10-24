<?php

namespace App\Console\Commands;

use App\Models\Source;
use App\Services\News\Clusterer;
use App\Services\News\Ingestor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FetchNews extends Command
{
    protected $signature = 'news:fetch {--source=* : Only fetch these source slugs} {--no-cluster : Store articles without clustering them}';

    protected $description = 'Read every news feed, store new articles and cluster them into events';

    public function handle(Ingestor $ingestor, Clusterer $clusterer): int
    {
        // The container's startup fetch and the scheduler can overlap; two
        // runs at once would race to insert the same articles.
        $lock = Cache::lock('news:fetch', 900);
        if (! $lock->get()) {
            $this->components->info('Another fetch is already running.');

            return self::SUCCESS;
        }

        try {
            $this->fetchAll($ingestor, $clusterer);
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }

    private function fetchAll(Ingestor $ingestor, Clusterer $clusterer): void
    {
        Source::syncFromConfig();

        $sources = Source::query()
            ->when($this->option('source'), fn ($q, $slugs) => $q->whereIn('slug', $slugs))
            ->get();

        $total = 0;
        $events = 0;
        foreach ($sources as $source) {
            try {
                $count = $ingestor->fetch($source);
            } catch (\Throwable $e) {
                // One broken source must not stop the others or the clustering.
                report($e);
                $source->update(['last_fetched_at' => now(), 'last_error' => mb_substr($e->getMessage(), 0, 500)]);
                $this->components->warn("{$source->name}: {$e->getMessage()}");

                continue;
            }

            $total += $count;
            $source->last_error
                ? $this->components->warn("{$source->name}: {$source->last_error}")
                : $this->components->twoColumnDetail($source->name, "{$count} new");

            // Cluster after each source rather than at the end, so events show
            // up while slow or unreachable feeds are still being waited on.
            if ($count && ! $this->option('no-cluster')) {
                $events += $clusterer->run()['new_events'];
            }
        }

        $this->components->info("{$total} new articles, {$events} new events.");
    }
}
