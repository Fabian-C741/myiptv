<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('type');                // 'brute_force', 'suspicious_ip', 'multiple_countries'
            $table->string('ip_address')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestamps();

            $table->index('type');
            $table->index('resolved');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_alerts');
    }
};
