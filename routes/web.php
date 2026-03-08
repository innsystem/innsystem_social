<?php

use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MetaAuthController;
use App\Http\Controllers\MetaPublicAuthController;
use App\Http\Controllers\MetaWebhookController;
use App\Http\Controllers\Tenant\PostHistoryController;
use App\Http\Controllers\Tenant\SocialSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas Institucionais
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/institucional/politica-privacidade', 'institucional.politica-privacidade');
Route::view('/institucional/termos-servicos', 'institucional.termos-servicos');
Route::view('/institucional/exclusao-dados-usuario', 'institucional.exclusao-dados-usuario');

/*
|--------------------------------------------------------------------------
| Autenticação Web
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Webhook da Meta
|--------------------------------------------------------------------------
*/
Route::get('/meta/webhook', [MetaWebhookController::class, 'verify'])->name('meta.webhook.verify');
Route::post('/meta/webhook', [MetaWebhookController::class, 'handle'])->name('meta.webhook.handle');

/*
|--------------------------------------------------------------------------
| OAuth Público via Link Assinado (OpenCart)
|--------------------------------------------------------------------------
*/
Route::get('/connect/meta/callback', [MetaPublicAuthController::class, 'callback'])->name('meta.public.callback');
Route::get('/connect/meta/select-page', [MetaPublicAuthController::class, 'selectPage'])->name('meta.public.select-page');
Route::post('/connect/meta/select-page', [MetaPublicAuthController::class, 'savePage'])->name('meta.public.save-page');
Route::get('/connect/meta/done', [MetaPublicAuthController::class, 'done'])->name('meta.public.done');
Route::get('/connect/meta/{tenant}', [MetaPublicAuthController::class, 'redirect'])
    ->whereNumber('tenant')
    ->middleware('signed')
    ->name('meta.public.redirect');

/*
|--------------------------------------------------------------------------
| Painel Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/tenants', [TenantManagementController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [TenantManagementController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantManagementController::class, 'store'])->name('tenants.store');
    Route::post('/tenants/{tenant}/regenerate-credentials', [TenantManagementController::class, 'regenerateCredentials'])
        ->name('tenants.regenerate-credentials');
});

/*
|--------------------------------------------------------------------------
| Painel Tenant (apenas configurações e histórico)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'tenant'])->group(function () {
    // ── OAuth Meta ────────────────────────────────────────────────────────
    Route::get('/auth/meta/redirect', [MetaAuthController::class, 'redirect'])
        ->name('meta.redirect');

    Route::get('/auth/meta/callback', [MetaAuthController::class, 'callback'])
        ->name('meta.callback')
        ->withoutMiddleware('tenant'); // callback pode chegar antes do tenant ser configurado

    Route::get('/auth/meta/select-page', [MetaAuthController::class, 'selectPage'])
        ->name('meta.select-page');

    Route::post('/auth/meta/save-page', [MetaAuthController::class, 'savePage'])
        ->name('meta.save-page');

    Route::delete('/auth/meta/disconnect', [MetaAuthController::class, 'disconnect'])
        ->name('meta.disconnect');

    // ── Redes Sociais ─────────────────────────────────────────────────────
    Route::get('/social/settings', [SocialSettingsController::class, 'index'])
        ->name('meta.social.settings');
    Route::post('/social/manual-publish', [SocialSettingsController::class, 'manualPublish'])
        ->name('meta.social.manual-publish');

    // ── Histórico de Posts ────────────────────────────────────────────────
    Route::get('/posts/history', [PostHistoryController::class, 'index'])
        ->name('tenant.posts.history');
});
