<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    protected $fillable = ['source_id', 'event_id', 'guid', 'url', 'title', 'summary', 'lang', 'published_at', 'tokens'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'tokens' => 'array',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
