<?php

namespace App\Services\News;

/**
 * Turns headline and summary text into weighted term counts.
 *
 * English words are lower-cased, lightly stemmed and filtered against a stop
 * list. Thaana (Dhivehi) words are kept whole apart from common case
 * suffixes, which is crude but enough for same-language clustering.
 */
class Tokenizer
{
    private const STOPWORDS = [
        'the', 'a', 'an', 'and', 'or', 'but', 'of', 'to', 'in', 'on', 'at', 'for', 'from', 'by', 'with', 'as', 'is', 'are',
        'was', 'were', 'be', 'been', 'being', 'has', 'have', 'had', 'it', 'its', 'this', 'that', 'these', 'those', 'will',
        'would', 'can', 'could', 'should', 'may', 'might', 'not', 'no', 'over', 'after', 'before', 'into', 'about', 'than',
        'more', 'most', 'new', 'says', 'said', 'say', 'also', 'up', 'out', 'who', 'what', 'when', 'where', 'which', 'how',
        'his', 'her', 'their', 'they', 'he', 'she', 'we', 'you', 'our', 'all', 'some', 'any', 'two', 'one', 'three', 'first',
        'last', 'year', 'years', 'day', 'days', 'today', 'yesterday', 'week', 'now', 'just', 'amid', 'via', 'per', 'under',
        'maldives', 'maldivian', 'maldivians', 'news', 'read', 'more', 'continue', 'reading', 'photo', 'video', 'update',
        'during', 'while', 'against', 'between', 'there', 'here', 'been', 'being', 'such', 'only', 'other', 'very',
        // Dhivehi function words
        'އަދި', 'މި', 'އެ', 'ވަނީ', 'ކަމަށް', 'ކުރި', 'ކޮށް', 'ގޮތުން', 'ކަމުގައި', 'ނަމަވެސް', 'އެވެ', 'އާއި', 'ވެސް', 'ދިވެހި', 'ރާއްޖެ', 'ރާއްޖޭގެ',
    ];

    // Case endings stripped from the end of Thaana words (longest first).
    private const DV_SUFFIXES = ['ގައިވެސް', 'ގައި', 'އަށް', 'ތަކުގެ', 'ތަކަށް', 'ތައް', 'ގެ', 'ން', 'ށް'];

    private const TITLE_WEIGHT = 2;

    private array $stop;

    public function __construct()
    {
        $this->stop = array_flip(self::STOPWORDS);
    }

    /**
     * @return array<string, int> term => weighted count
     */
    public function terms(string $title, ?string $summary = null): array
    {
        $counts = [];

        foreach ($this->words($title) as $word) {
            $counts[$word] = ($counts[$word] ?? 0) + self::TITLE_WEIGHT;
        }

        foreach ($this->words((string) $summary) as $word) {
            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }

        arsort($counts);

        return $counts;
    }

    /**
     * @return list<string>
     */
    public function words(string $text): array
    {
        $text = mb_strtolower(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace("/[’']s\b/u", '', $text);
        // preg_split returns false on malformed UTF-8; treat that as no words.
        $parts = preg_split('/[^\p{L}\p{M}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $words = [];
        foreach ($parts as $part) {
            $word = self::isThaana($part) ? $this->stemDhivehi($part) : $this->stemEnglish($part);

            if ($word === '' || isset($this->stop[$word]) || isset($this->stop[$part])) {
                continue;
            }
            if (! self::isThaana($word) && (mb_strlen($word) < 3 || ctype_digit($word) && strlen($word) < 4)) {
                continue;
            }

            $words[] = $word;
        }

        return $words;
    }

    public static function isThaana(string $text): bool
    {
        return (bool) preg_match('/\p{Thaana}/u', $text);
    }

    /**
     * Share of letters in the text that are Thaana.
     */
    public static function thaanaRatio(string $text): float
    {
        $letters = preg_match_all('/\p{L}/u', $text);

        return $letters ? preg_match_all('/\p{Thaana}/u', $text) / $letters : 0.0;
    }

    private function stemEnglish(string $word): string
    {
        foreach (['ies' => 'y', 'ing' => '', 'ed' => '', 's' => ''] as $suffix => $replacement) {
            if (str_ends_with($word, $suffix) && mb_strlen($word) - strlen($suffix) >= 4 && ! str_ends_with($word, 'ss')) {
                return substr($word, 0, -strlen($suffix)).$replacement;
            }
        }

        return $word;
    }

    private function stemDhivehi(string $word): string
    {
        foreach (self::DV_SUFFIXES as $suffix) {
            if (str_ends_with($word, $suffix) && mb_strlen($word) - mb_strlen($suffix) >= 3) {
                return mb_substr($word, 0, -mb_strlen($suffix));
            }
        }

        return $word;
    }
}
