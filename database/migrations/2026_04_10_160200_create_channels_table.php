<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            $table->foreignId('channel_group_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('stream_url');
            $table->string('stream_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('epg_id')->nullable();
            $table->boolean('is_adult')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
