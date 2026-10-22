<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->text('title_dv')->nullable()->after('title');
        });

        Schema::table('situations', function (Blueprint $table) {
            $table->text('title_dv')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn('title_dv'));
        Schema::table('situations', fn (Blueprint $table) => $table->dropColumn('title_dv'));
    }
};
