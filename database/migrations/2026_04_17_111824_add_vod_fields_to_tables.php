<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('channel_groups', function (Blueprint $table) {
            $table->string('type')->default('live')->after('name'); // live, movie, series
            $table->string('ext_id')->nullable()->after('type'); // Xtream category_id
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->string('type')->default('live')->after('name'); // live, movie
            $table->text('description')->nullable()->after('type');
            $table->string('release_date')->nullable()->after('description');
            $table->string('rating')->nullable()->after('release_date');
            $table->string('duration')->nullable()->after('rating');
            $table->string('backdrop')->nullable()->after('duration');
            $table->unsignedBigInteger('tmdb_id')->nullable()->after('backdrop');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('channel_groups', function (Blueprint $table) {
            $table->dropColumn(['type', 'ext_id']);
        });

        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'release_date', 'rating', 'duration', 'backdrop', 'tmdb_id']);
        });
    }
};
