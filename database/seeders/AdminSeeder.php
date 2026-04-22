<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Crea el primer administrador del sistema OTT.
     *
     * Ejecutar con:
     *   php artisan db:seed --class=AdminSeeder
     *
     * IMPORTANTE: Cambiar las credenciales por defecto antes de
     * subir a producción o compartir el proyecto.
     */
    public function run(): void
    {
        // Superadmin principal — cambia email y contraseña en .env o aquí
        Admin::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@apktv.com')],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme123!')),
                'role'     => 'superadmin',
            ]
        );

        $this->command->info('✅ Admin creado: ' . env('ADMIN_EMAIL', 'admin@apktv.com'));
        $this->command->warn('⚠️  Cambia la contraseña por defecto antes de subir a producción.');
    }
}
