# Raajje Watch

Every Maldivian news story, read from every outlet, grouped into events and mapped by atoll.

Raajje Watch reads the RSS feeds of Maldivian outlets (English and Dhivehi), works out which articles cover the same happening, and squashes them into one **event**. Related events are chained into **situations** with dated timelines, so you can follow how a story developed over weeks instead of re-reading the same headline. A dot map of the atolls shows where the news is right now, and every hour gets a permanent link.

## Pages

| URL | What |
| --- | --- |
| `/` | The Maldives right now: atoll map plus the most covered events of the last 24 hours |
| `/map/2026-10-21T14` | The map as it was at that hour (UTC) |
| `/events`, `/events/{id}` | Event archive, filterable by tier; one event with all its coverage |
| `/situations`, `/situations/{slug}` | Stories over time |
| `/atolls/{code}` | Events for one atoll (`S` = Addu, `MLE` = Malé, ...) |
| `/observatory` | Stats: articles per event, single-day share, busiest atolls, sources |
| `/developers` | The free JSON API under `/api/v1` |

## Running it

```bash
composer install && npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate

php artisan db:seed          # optional: made-up demo stories
php artisan news:fetch       # read the real feeds
php artisan serve
```

In production, run the scheduler (`* * * * * php artisan schedule:run`). It fetches every ten minutes and snapshots the map every hour.

## How it works

- **Sources** live in `config/news.php`. Any RSS or Atom feed works; a broken feed is logged and skipped.
- **Clustering** (`app/Services/News/Clusterer.php`): headline and summary become TF-IDF weighted words. An article joins the most similar event from the last 48 hours if cosine similarity is at least 0.32, or starts a new one. Events that share at least three words and are loosely similar (at least 0.18) within 21 days become a situation. Events set in different atolls need the stricter 0.32. All thresholds can be set in `.env` (`NEWS_EVENT_THRESHOLD`, ...).
- **Places** (`config/places.php`): a gazetteer of all 20 atolls plus Malé, with island names and Dhivehi spellings.
- **Tiers**: notable = 2+ outlets, major = 4+, exceptional = 6+ outlets and 10+ articles.

## Known limits / next steps

- Dhivehi and English reports of the same event are not merged yet. Multilingual embeddings or an LLM pass would fix that.
- Feed URLs in `config/news.php` are best guesses; check each one with `php artisan news:fetch --source=sun` and adjust.
- Tiers are rule-based; an LLM judge could rate significance instead.
- An MCP server over the same data would make it usable from AI assistants.

## Tests

```bash
vendor/bin/pest
```
