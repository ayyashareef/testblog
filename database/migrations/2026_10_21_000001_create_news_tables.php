<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('url');
            $table->string('feed_url');
            $table->string('lang', 5)->default('en');
            $table->timestamp('last_fetched_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('situations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->timestamp('first_seen_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->unsignedInteger('event_count')->default(0);
            $table->unsignedInteger('article_count')->default(0);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('situation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('lang', 5)->default('en');
            $table->timestamp('first_seen_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->unsignedInteger('article_count')->default(0);
            $table->unsignedInteger('source_count')->default(0);
            $table->string('tier', 16)->default('routine')->index();
            $table->json('terms');
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guid', 64);
            $table->text('url');
            $table->text('title');
            $table->text('summary')->nullable();
            $table->string('lang', 5)->default('en');
            $table->timestamp('published_at')->index();
            $table->json('tokens');
            $table->timestamps();

            $table->unique(['source_id', 'guid']);
        });

        Schema::create('event_places', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('place_code', 8)->index();
            $table->unsignedInteger('mentions')->default(1);
            $table->primary(['event_id', 'place_code']);
        });

        Schema::create('map_snapshots', function (Blueprint $table) {
            $table->id();
            $table->timestamp('hour')->unique();
            $table->unsignedInteger('window_hours');
            $table->json('counts');
            $table->unsignedInteger('event_total');
            $table->unsignedInteger('article_total');
            $table->foreignId('top_event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('map_snapshots');
        Schema::dropIfExists('event_places');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('events');
        Schema::dropIfExists('situations');
        Schema::dropIfExists('sources');
    }
};
