<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\IPTVAdminController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\SecurityController;

Route::get('/', function () {
    return view('welcome');
});

// Panel Administrativo Web
Route::prefix('admin')->group(function () {
    // Auth
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Rutas Protegidas
    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        // Perfil y Credenciales del Admin
        Route::get('/profile', [AdminAuthController::class, 'showProfile'])->name('admin.profile');
        Route::patch('/profile', [AdminAuthController::class, 'updateProfile'])->name('admin.profile.update');
        
        // Gestión de Clientes
        Route::get('/users', [UserAdminController::class, 'indexWeb'])->name('admin.users');
        Route::get('/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
        Route::get('/users/{id}/edit', [UserAdminController::class, 'edit'])->name('admin.users.edit');
        Route::post('/users', [UserAdminController::class, 'storeWeb'])->name('admin.users.store');
        Route::patch('/users/{id}', [UserAdminController::class, 'updateWeb'])->name('admin.users.update');
        Route::patch('/users/{id}/status', [UserAdminController::class, 'updateStatusWeb'])->name('admin.users.status');

        // Seguridad
        Route::get('/security', [SecurityController::class, 'indexWeb'])->name('admin.security');
        Route::patch('/security/alerts/{id}/resolve', [SecurityController::class, 'resolve'])->name('admin.security.resolve');

        // Gestión de Contenido (M3U / Xtream)
        Route::get('/content', [IPTVAdminController::class, 'index'])->name('admin.content');
        Route::get('/content/setup', [IPTVAdminController::class, 'setup'])->name('admin.content.setup');
        Route::get('/content/{id}/edit', [IPTVAdminController::class, 'edit'])->name('admin.content.edit');
        Route::post('/content/setup', [IPTVAdminController::class, 'store'])->name('admin.content.store');
        Route::patch('/content/{id}', [IPTVAdminController::class, 'update'])->name('admin.content.update');
        Route::delete('/content/{id}', [IPTVAdminController::class, 'destroy'])->name('admin.content.destroy');
        Route::post('/content/sync/{id}', [IPTVAdminController::class, 'sync'])->name('admin.content.sync');

        // Configuración de Marca y App
        Route::get('/config', [ConfigController::class, 'index'])->name('admin.config');
        Route::patch('/config', [ConfigController::class, 'update'])->name('admin.config.update');
    });
});
