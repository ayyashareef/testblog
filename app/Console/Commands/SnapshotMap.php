<?php

namespace App\Console\Commands;

use App\Services\News\MapService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SnapshotMap extends Command
{
    protected $signature = 'news:snapshot {--at= : Hour to snapshot (default: the current hour)}';

    protected $description = 'Store the atoll map for an hour so it keeps a permanent link';

    public function handle(MapService $map): int
    {
        $at = $this->option('at') ? Carbon::parse($this->option('at')) : now();
        $snapshot = $map->snapshot($at);

        $this->components->info("Snapshot {$snapshot->hour->format('Y-m-d H:00')} UTC: {$snapshot->event_total} events. {$snapshot->permalink()}");

        return self::SUCCESS;
    }
}
