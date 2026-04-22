<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $row) {
            $row->id();
            $row->string('key')->unique();
            $row->longText('value')->nullable();
            $row->string('type')->default('string'); // string, json, image
            $row->timestamps();
        });

        // Seed default values
        DB::table('settings')->insert([
            ['key' => 'app_name', 'value' => 'Electrofabiptv', 'type' => 'string'],
            ['key' => 'app_logo', 'value' => null, 'type' => 'image'],
            ['key' => 'app_version', 'value' => '1.0.0', 'type' => 'string'],
            ['key' => 'app_apk_url', 'value' => null, 'type' => 'string'],
            ['key' => 'primary_color', 'value' => '#ff3333', 'type' => 'string'],
            ['key' => 'secondary_color', 'value' => '#00aaff', 'type' => 'string'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
