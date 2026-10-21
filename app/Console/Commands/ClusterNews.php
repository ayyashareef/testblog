<?php

namespace App\Console\Commands;

use App\Services\News\Clusterer;
use Illuminate\Console\Command;

class ClusterNews extends Command
{
    protected $signature = 'news:cluster';

    protected $description = 'Group unclustered articles into events and chain events into situations';

    public function handle(Clusterer $clusterer): int
    {
        $result = $clusterer->run();

        $this->components->info(sprintf(
            '%d articles clustered: %d new events, %d events updated, %d new situation links.',
            $result['articles'], $result['new_events'], $result['touched_events'], $result['situations'],
        ));

        return self::SUCCESS;
    }
}
