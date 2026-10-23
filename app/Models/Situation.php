<?php

namespace App\Models;

use App\Services\News\Tokenizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Situation extends Model
{
    protected $fillable = ['slug', 'title', 'title_dv', 'first_seen_at', 'last_seen_at', 'event_count', 'article_count'];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class)->orderBy('first_seen_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function displayTitle(): string
    {
        return app()->getLocale() === 'dv' && $this->title_dv ? $this->title_dv : $this->title;
    }

    public function isRtl(): bool
    {
        return Tokenizer::isThaana($this->displayTitle());
    }

    public function refreshStats(): void
    {
        $events = $this->events()->get();
        $lead = $events->sortByDesc('source_count')->first();

        $this->fill([
            'title' => $lead?->title ?? $this->title,
            'title_dv' => $events->sortByDesc('source_count')->firstWhere('title_dv')?->title_dv,
            'first_seen_at' => $events->min('first_seen_at'),
            'last_seen_at' => $events->max('last_seen_at'),
            'event_count' => $events->count(),
            'article_count' => $events->sum('article_count'),
        ])->save();
    }
}
