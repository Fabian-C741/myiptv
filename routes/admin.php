<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserAdminController;

/*
|--------------------------------------------------------------------------
| Admin Web Routes (Panel de Administración)
|--------------------------------------------------------------------------
*/

// Auth - Públicas
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [AdminAuthController::class, 'login']);
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Dashboard y Gestión - Protegidas por Guard Admin
Route::middleware('auth:admin')->group(function () {
    
    // Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Gestión de Usuarios (Vistas Web)
    /*
    | Estas rutas usan redirecciones en lugar de JSON
    */
    // Gestión de Usuarios
    Route::get('/users', [UserAdminController::class, 'indexWeb'])->name('admin.users');
    Route::get('/users/create', [UserAdminController::class, 'create'])->name('admin.users.create');
    Route::post('/users', [UserAdminController::class, 'storeWeb'])->name('admin.users.store');
    Route::get('/users/{id}/edit', [UserAdminController::class, 'edit'])->name('admin.users.edit');
    Route::patch('/users/{id}', [UserAdminController::class, 'updateWeb'])->name('admin.users.update');
    Route::patch('/users/{id}/status', [UserAdminController::class, 'updateStatusWeb'])->name('admin.users.status');

    // Gestión de Stremio Addons
    Route::get('/stremio', [\App\Http\Controllers\Admin\StremioAddonController::class, 'index'])->name('admin.stremio.index');
    Route::post('/stremio', [\App\Http\Controllers\Admin\StremioAddonController::class, 'store'])->name('admin.stremio.store');
    Route::delete('/stremio/{stremioAddon}', [\App\Http\Controllers\Admin\StremioAddonController::class, 'destroy'])->name('admin.stremio.destroy');

    // Sincronización Legal
    Route::post('/legal-channels/sync', [\App\Http\Controllers\Admin\LegalChannelController::class, 'sync'])->name('admin.legal.sync');
});
