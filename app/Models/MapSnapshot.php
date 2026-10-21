<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapSnapshot extends Model
{
    protected $fillable = ['hour', 'window_hours', 'counts', 'event_total', 'article_total', 'top_event_id'];

    protected function casts(): array
    {
        return [
            'hour' => 'datetime',
            'counts' => 'array',
        ];
    }

    public function topEvent(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'top_event_id');
    }

    public function permalink(): string
    {
        return route('map.hour', $this->hour->format('Y-m-d\TH'));
    }
}
