<?php

namespace App\Services\News;

/**
 * Finds Maldivian atolls, islands and Malé in text (config/places.php).
 *
 * Longer aliases win: "Haa Dhaalu" is consumed before "Dhaalu" can match it,
 * and "Thinadhoo, Vaavu" before "Thinadhoo".
 */
class Gazetteer
{
    /** @var list<array{pattern: string, code: string}> */
    private array $patterns = [];

    public function __construct(private array $places = [])
    {
        $this->places = $places ?: config('places');

        $aliases = [];
        foreach ($this->places as $code => $place) {
            foreach ($place['aliases'] as $alias) {
                $aliases[] = [$alias, $code];
            }
        }

        usort($aliases, fn ($a, $b) => mb_strlen(rtrim($b[0], '*')) <=> mb_strlen(rtrim($a[0], '*')));

        foreach ($aliases as [$alias, $code]) {
            $this->patterns[] = ['pattern' => $this->compile($alias), 'code' => $code];
        }
    }

    /**
     * @return array<string, int> place code => mentions
     */
    public function find(string $text): array
    {
        $found = [];

        foreach ($this->patterns as ['pattern' => $pattern, 'code' => $code]) {
            $text = preg_replace_callback($pattern, function ($m) use (&$found, $code) {
                $found[$code] = ($found[$code] ?? 0) + 1;

                return str_repeat(' ', strlen($m[0]));
            }, $text);
        }

        arsort($found);

        return $found;
    }

    public function places(): array
    {
        return $this->places;
    }

    private function compile(string $alias): string
    {
        $prefix = str_ends_with($alias, '*');
        $quoted = preg_quote(rtrim($alias, '*'), '/');
        $quoted = preg_replace('/\s+/', '\\s+', $quoted);

        // \b is unreliable around Thaana combining marks and around "Male'", so
        // spell out the boundaries as "not a letter or mark".
        $before = '(?<![\p{L}\p{M}])';
        $after = $prefix ? '[\p{L}\p{M}]*' : (str_ends_with($alias, "'") || str_ends_with($alias, '.') ? '' : '(?![\p{L}\p{M}])');

        return '/'.$before.$quoted.$after.'/u';
    }
}
