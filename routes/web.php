<?php

use App\Http\Controllers\MetaAuthController;
use App\Http\Controllers\MetaWebhookController;
use App\Http\Controllers\ProductPublishController;
use App\Http\Controllers\Tenant\PostHistoryController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\SocialSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

// Páginas institucionais
Route::view('/institucional/politica-privacidade', 'institucional.politica-privacidade');
Route::view('/institucional/termos-servicos', 'institucional.termos-servicos');
Route::view('/institucional/exclusao-dados-usuario', 'institucional.exclusao-dados-usuario');

/*
|--------------------------------------------------------------------------
| Webhook da Meta (sem autenticação — validado por verify_token)
|--------------------------------------------------------------------------
*/
Route::get('/meta/webhook', [MetaWebhookController::class, 'verify'])->name('meta.webhook.verify');
Route::post('/meta/webhook', [MetaWebhookController::class, 'handle'])->name('meta.webhook.handle');

/*
|--------------------------------------------------------------------------
| Rotas do Painel (requer autenticação + tenant)
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

    // ── Produtos ──────────────────────────────────────────────────────────
    Route::get('/products', [ProductController::class, 'index'])
        ->name('tenant.products.index');

    Route::post('/products/{product}/publish', [ProductPublishController::class, 'publish'])
        ->name('tenant.products.publish');

    Route::get('/products/publish-history', [ProductPublishController::class, 'history'])
        ->name('tenant.products.publish-history');

    // ── Histórico de Posts ────────────────────────────────────────────────
    Route::get('/posts/history', [PostHistoryController::class, 'index'])
        ->name('tenant.posts.history');

    // ── Logout ────────────────────────────────────────────────────────────
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
