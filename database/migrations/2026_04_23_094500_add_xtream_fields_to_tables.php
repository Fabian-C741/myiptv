<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('external_sources', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('external_sources', 'username')) {
                $blueprint.string('username')->nullable();
                $blueprint.string('password')->nullable();
            }
        });

        Schema::table('channel_groups', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('channel_groups', 'external_id')) {
                $blueprint.string('external_id')->nullable();
                $blueprint.unsignedBigInteger('source_id')->nullable();
            }
        });
        
        Schema::table('channels', function (Blueprint $blueprint) {
            if (!Schema::hasColumn('channels', 'source_id')) {
                $blueprint.unsignedBigInteger('source_id')->nullable();
            }
        });
    }

    public function down()
    {
        // No necesario de momento
    }
};
