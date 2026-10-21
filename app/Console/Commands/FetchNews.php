<?php

namespace App\Console\Commands;

use App\Models\Source;
use App\Services\News\Clusterer;
use App\Services\News\Ingestor;
use Illuminate\Console\Command;

class FetchNews extends Command
{
    protected $signature = 'news:fetch {--source=* : Only fetch these source slugs} {--no-cluster : Store articles without clustering them}';

    protected $description = 'Read every news feed, store new articles and cluster them into events';

    public function handle(Ingestor $ingestor, Clusterer $clusterer): int
    {
        Source::syncFromConfig();

        $sources = Source::query()
            ->when($this->option('source'), fn ($q, $slugs) => $q->whereIn('slug', $slugs))
            ->get();

        $total = 0;
        foreach ($sources as $source) {
            $count = $ingestor->fetch($source);
            $total += $count;

            $source->last_error
                ? $this->components->warn("{$source->name}: {$source->last_error}")
                : $this->components->twoColumnDetail($source->name, "{$count} new");
        }

        $this->components->info("{$total} new articles.");

        if (! $this->option('no-cluster')) {
            $this->call('news:cluster');
        }

        return self::SUCCESS;
    }
}
