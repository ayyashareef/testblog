<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $fillable = ['slug', 'name', 'url', 'feed_url', 'lang', 'last_fetched_at', 'last_error'];

    protected function casts(): array
    {
        return ['last_fetched_at' => 'datetime'];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Create or update source rows from config/news.php.
     */
    public static function syncFromConfig(): void
    {
        foreach (config('news.sources') as $source) {
            static::updateOrCreate(['slug' => $source['slug']], [
                'name' => $source['name'],
                'url' => $source['url'],
                'feed_url' => $source['feed'],
                'lang' => $source['lang'],
            ]);
        }
    }
}
