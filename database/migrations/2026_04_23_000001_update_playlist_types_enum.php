<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // En MariaDB/MySQL no se puede modificar un ENUM directamente con Blueprint de forma fácil sin Doctrine.
        // Usamos una consulta directa que es más segura.
        DB::statement("ALTER TABLE playlists MODIFY COLUMN type ENUM('m3u', 'xtream', 'stremio', 'mxl', 'direct') DEFAULT 'm3u'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE playlists MODIFY COLUMN type ENUM('m3u', 'xtream') DEFAULT 'm3u'");
    }
};
