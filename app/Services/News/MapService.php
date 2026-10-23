<?php

namespace App\Services\News;

use App\Models\Article;
use App\Models\Event;
use App\Models\EventPlace;
use App\Models\MapSnapshot;
use Carbon\CarbonInterface;

/**
 * Counts events per atoll and draws the Maldives as a grid of dots, each
 * atoll a ring of dots coloured by how much news touched it.
 */
class MapService
{
    // Projection bounds and SVG geometry. Longitude is stretched a little so
    // the narrow chain of atolls leaves room for labels.
    private const LON_MIN = 72.55;

    private const LON_MAX = 73.75;

    private const LAT_MIN = -0.8;

    private const LAT_MAX = 7.3;

    private const MAP_X = 16;

    private const MAP_W = 190;

    private const MAP_Y = 16;

    private const MAP_H = 860;

    private const DOT_STEP_LON = 0.024;

    private const DOT_STEP_LAT = 0.034;

    public const WIDTH = 420;

    public const HEIGHT = 892;

    /**
     * Activity for the window ending at $at.
     *
     * @return array{at: CarbonInterface, hours: int, counts: array<string, int>, events: int, articles: int, top_event: ?Event}
     */
    public function activity(CarbonInterface $at, ?int $hours = null): array
    {
        $hours ??= config('news.map_window_hours');
        $from = $at->copy()->subHours($hours);

        $events = Event::where('last_seen_at', '>=', $from)->where('first_seen_at', '<=', $at);

        $counts = EventPlace::query()
            ->whereIn('event_id', (clone $events)->select('id'))
            ->selectRaw('place_code, count(*) as n')
            ->groupBy('place_code')
            ->pluck('n', 'place_code')
            ->map(fn ($n) => (int) $n)
            ->all();

        return [
            'at' => $at,
            'hours' => $hours,
            'counts' => $counts,
            'events' => (clone $events)->count(),
            'articles' => Article::whereBetween('published_at', [$from, $at])->count(),
            'top_event' => (clone $events)->orderByDesc('source_count')->orderByDesc('article_count')->first(),
        ];
    }

    public function snapshot(CarbonInterface $hour): MapSnapshot
    {
        $hour = $hour->copy()->startOfHour();
        $activity = $this->activity($hour);

        return MapSnapshot::updateOrCreate(['hour' => $hour], [
            'window_hours' => $activity['hours'],
            'counts' => $activity['counts'],
            'event_total' => $activity['events'],
            'article_total' => $activity['articles'],
            'top_event_id' => $activity['top_event']?->id,
        ]);
    }

    /**
     * @param  array<string, int>  $counts
     * @return array{dots: list<array>, labels: list<array>, width: int, height: int}
     */
    public function render(array $counts): array
    {
        $places = config('places');
        $max = max([1, ...array_values($counts)]);

        $dots = [];
        for ($lat = self::LAT_MAX; $lat >= self::LAT_MIN; $lat -= self::DOT_STEP_LAT) {
            for ($lon = self::LON_MIN; $lon <= self::LON_MAX; $lon += self::DOT_STEP_LON) {
                [$code, $d] = $this->nearest($places, $lat, $lon);
                if ($code === null) {
                    continue;
                }

                $small = $places[$code]['rx'] < 0.05;
                $onRing = $small || $d >= 0.55;
                $intensity = $this->intensity($counts[$code] ?? 0, $max);

                [$x, $y] = $this->project($lat, $lon);
                $dots[] = [
                    'x' => round($x, 1),
                    'y' => round($y, 1),
                    'code' => $code,
                    'r' => $onRing ? 1.7 : 1.1,
                    'fill' => $onRing ? $this->color($intensity) : $this->lagoon($intensity),
                ];
            }
        }

        return [
            'dots' => $dots,
            'labels' => $this->labels($places, $counts, $max),
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
        ];
    }

    /**
     * 0 for no news, then a log scale up to 1 for the busiest atoll.
     */
    public function intensity(int $count, int $max): float
    {
        return $count > 0 ? 0.15 + 0.85 * log(1 + $count) / log(1 + $max) : 0.0;
    }

    public function color(float $t): string
    {
        if ($t <= 0) {
            return '#1e2a3a';
        }

        // dark rust -> orange -> pale gold, like embers
        $stops = [[0.0, [124, 45, 18]], [0.55, [234, 116, 26]], [1.0, [253, 230, 138]]];

        return $this->interpolate($stops, $t);
    }

    private function lagoon(float $t): string
    {
        return $t <= 0 ? '#142030' : $this->interpolate([[0.0, [40, 30, 28]], [1.0, [110, 70, 30]]], $t);
    }

    private function interpolate(array $stops, float $t): string
    {
        for ($i = 1; $i < count($stops); $i++) {
            [$t1, $c1] = $stops[$i];
            [$t0, $c0] = $stops[$i - 1];
            if ($t <= $t1) {
                $f = ($t - $t0) / ($t1 - $t0);

                return sprintf('#%02x%02x%02x', ...array_map(fn ($a, $b) => (int) round($a + ($b - $a) * $f), $c0, $c1));
            }
        }

        return sprintf('#%02x%02x%02x', ...end($stops)[1]);
    }

    /**
     * The atoll whose ellipse contains the point, by normalised distance.
     */
    private function nearest(array $places, float $lat, float $lon): array
    {
        $best = null;
        $bestD = 1.0;

        foreach ($places as $code => $p) {
            $dx = ($lon - $p['lng']) / $p['rx'];
            $dy = ($lat - $p['lat']) / $p['ry'];
            $d = $dx * $dx + $dy * $dy;

            // Malé sits inside Kaafu's ring; a single-island place always wins.
            if ($d <= 1.0 && $p['rx'] < 0.05) {
                return [$code, $d];
            }
            if ($d <= $bestD) {
                [$best, $bestD] = [$code, $d];
            }
        }

        return [$best, $bestD];
    }

    private function project(float $lat, float $lon): array
    {
        return [
            self::MAP_X + ($lon - self::LON_MIN) / (self::LON_MAX - self::LON_MIN) * self::MAP_W,
            self::MAP_Y + (self::LAT_MAX - $lat) / (self::LAT_MAX - self::LAT_MIN) * self::MAP_H,
        ];
    }

    /**
     * Labels down the right-hand side, nudged apart so they never overlap,
     * each with a leader line back to its atoll.
     */
    private function labels(array $places, array $counts, int $max): array
    {
        $labels = [];
        foreach ($places as $code => $p) {
            [$x, $y] = $this->project($p['lat'], $p['lng'] + $p['rx']);
            $labels[] = [
                'code' => $code,
                'name' => EventPlace::label($code),
                'count' => $counts[$code] ?? 0,
                'anchor_x' => round($x + 3, 1),
                'anchor_y' => round($y, 1),
                'y' => $y,
                'color' => $this->color($this->intensity($counts[$code] ?? 0, $max)),
            ];
        }

        usort($labels, fn ($a, $b) => $a['y'] <=> $b['y']);

        $minGap = 22;
        for ($i = 1; $i < count($labels); $i++) {
            $labels[$i]['y'] = max($labels[$i]['y'], $labels[$i - 1]['y'] + $minGap);
        }
        // If the stack ran off the bottom, push it back up.
        $overflow = end($labels)['y'] - (self::HEIGHT - 10);
        if ($overflow > 0) {
            for ($i = count($labels) - 1; $i >= 0; $i--) {
                $limit = $i === count($labels) - 1 ? self::HEIGHT - 10 : $labels[$i + 1]['y'] - $minGap;
                $labels[$i]['y'] = min($labels[$i]['y'], $limit);
            }
        }

        foreach ($labels as &$label) {
            $label['y'] = round($label['y'], 1);
            $label['x'] = 250;
        }

        return $labels;
    }
}
