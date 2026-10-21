<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPlace extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = ['event_id', 'place_code', 'mentions'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function name(): string
    {
        return config("places.{$this->place_code}.name", $this->place_code);
    }
}
