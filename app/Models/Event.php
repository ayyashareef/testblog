<?php

namespace App\Models;

use App\Services\News\Tokenizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public const TIERS = ['routine', 'notable', 'major', 'exceptional'];

    protected $fillable = ['situation_id', 'title', 'title_dv', 'lang', 'first_seen_at', 'last_seen_at', 'article_count', 'source_count', 'tier', 'terms'];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'terms' => 'array',
        ];
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)->orderBy('published_at');
    }

    public function situation(): BelongsTo
    {
        return $this->belongsTo(Situation::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(EventPlace::class)->orderByDesc('mentions');
    }

    /**
     * How much attention an event got. With a dozen or so Maldivian outlets,
     * six independent sources on one story is already rare.
     */
    public static function tierFor(int $sources, int $articles): string
    {
        return match (true) {
            $sources >= 6 && $articles >= 10 => 'exceptional',
            $sources >= 4 => 'major',
            $sources >= 2 => 'notable',
            default => 'routine',
        };
    }

    /**
     * The Dhivehi headline when reading in Dhivehi and one exists.
     */
    public function displayTitle(): string
    {
        return app()->getLocale() === 'dv' && $this->title_dv ? $this->title_dv : $this->title;
    }

    public function isRtl(): bool
    {
        return Tokenizer::isThaana($this->displayTitle());
    }
}
