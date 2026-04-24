<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\SessionController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\ChannelController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\EpgController;
use App\Http\Controllers\Api\ExternalSourceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\DeviceAdminController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\ConfigController;

/*
|--------------------------------------------------------------------------
| API Routes — Plataforma OTT
|--------------------------------------------------------------------------
*/

// ── Público: solo login con rate limit anti-fuerza bruta ─────────────────────
Route::get('/login', function () {
    return response()->json([
        'message' => 'El método GET no está soportado para esta ruta. Por favor, use POST con sus credenciales.',
        'code' => 'METHOD_NOT_ALLOWED'
    ], 405);
});
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login');
Route::get('/app/config', [ConfigController::class, 'show']);

// ── Rutas autenticadas con Sanctum ───────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn(Request $r) => $r->user());

    // Perfiles tipo Netflix
    Route::prefix('profiles')->group(function () {
        Route::get('/',                   [ProfileController::class, 'index']);
        Route::post('/',                  [ProfileController::class, 'store']);
        Route::put('/{id}',               [ProfileController::class, 'update']);
        Route::delete('/{id}',            [ProfileController::class, 'destroy']);
        Route::post('/{id}/verify-pin',   [ProfileController::class, 'verifyPin']);
        Route::post('/{id}/avatar',       [ProfileController::class, 'uploadAvatar']); // Foto desde galería
    });

    // Fuentes Externas y VOD (Stremio / MXL / M3U / URL directa)
    Route::prefix('vod')->group(function () {
        Route::get('/stremio/catalogs',           [\App\Http\Controllers\Api\AppVODController::class, 'getCatalogs']);
        Route::get('/stremio/items',              [\App\Http\Controllers\Api\AppVODController::class, 'getCatalogItems']);
        Route::get('/stremio/meta/{type}/{id}',   [\App\Http\Controllers\Api\AppVODController::class, 'getMeta']);
        Route::get('/stremio/stream/{type}/{id}', [\App\Http\Controllers\Api\AppVODController::class, 'getStream']);
    });

    Route::prefix('external-sources')->group(function () {
        Route::get('/',              [ExternalSourceController::class, 'index']);
        Route::post('/',             [ExternalSourceController::class, 'store']);
        Route::put('/{id}',          [ExternalSourceController::class, 'update']);
        Route::delete('/{id}',       [ExternalSourceController::class, 'destroy']);
        Route::post('/{id}/sync',    [ExternalSourceController::class, 'sync']);
        Route::post('/validate-url', [ExternalSourceController::class, 'validate_url']);
    });

    // Dispositivos del usuario
    Route::get('/devices',                 [DeviceController::class, 'index']);
    Route::delete('/devices/{id}',         [DeviceController::class, 'destroy']);
    Route::delete('/devices',              [DeviceController::class, 'destroyAll']);

    // Sesiones del usuario
    Route::get('/sessions',                [SessionController::class, 'index']);
    Route::delete('/sessions',             [SessionController::class, 'destroyAll']);

    // IPTV — Playlists
    Route::get('/playlists',               [PlaylistController::class, 'index']);
    Route::post('/playlists/{id}/sync',    [PlaylistController::class, 'sync']);

    // IPTV — Canales (Temporalmente público para verificación en navegador)
    Route::get('/channels',            [ChannelController::class, 'index']);
    Route::get('/channels/groups',     [ChannelController::class, 'groups']);
    Route::get('/channels/groups/{id}',[ChannelController::class, 'byGroup']);
    Route::get('/series/{id}',         [ChannelController::class, 'seriesDetails']);

    // IPTV — EPG (Sigue protegido)
    Route::middleware('parental')->group(function () {
        Route::get('/channels/{id}/epg',   [EpgController::class, 'currentAndNext']);
    });

    // Favoritos por perfil
    Route::get('/favorites',               [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle',       [FavoriteController::class, 'toggle']);

    // ── Panel de Administración ───────────────────────────────────────────────
    Route::prefix('admin')->middleware('admin.role:superadmin,soporte')->group(function () {

        // Dashboard
        Route::get('/dashboard',           [DashboardController::class, 'stats']);

        // Usuarios
        Route::get('/users',               [UserAdminController::class, 'index']);
        Route::post('/users',              [UserAdminController::class, 'store']);
        Route::patch('/users/{id}/status', [UserAdminController::class, 'updateStatus']);
        Route::delete('/users/{id}/sessions', [UserAdminController::class, 'closeSessions']); // Cerrar sesiones de un user

        // Dispositivos
        Route::get('/devices',                          [DeviceAdminController::class, 'index']);
        Route::get('/devices/user/{userId}',            [DeviceAdminController::class, 'byUser']);
        Route::post('/devices/{id}/close',              [DeviceAdminController::class, 'closeSession']);
        Route::post('/devices/{id}/block',              [DeviceAdminController::class, 'block']);
        Route::delete('/users/{userId}/sessions/all',   [DeviceAdminController::class, 'closeAllUserSessions']);

        // Seguridad
        Route::get('/security/alerts',         [SecurityController::class, 'alerts']);
        Route::patch('/security/alerts/{id}',  [SecurityController::class, 'resolve']);
        Route::get('/security/suspicious-ips', [SecurityController::class, 'suspiciousIps']);
        Route::get('/security/multi-country',  [SecurityController::class, 'multiCountryUsers']);

        // Auditoría / Logs (solo superadmin y auditor)
        Route::middleware('admin.role:superadmin,auditor')->group(function () {
            Route::get('/audit',         [AuditController::class, 'index']);
            Route::get('/audit/summary', [AuditController::class, 'summary']);
        });

        // Configuración global
        Route::patch('/config', [ConfigController::class, 'update']);
    });
});
