<?php

namespace App\Http\Controllers;

use App\Models\MetaToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MetaAuthController extends Controller
{
    /**
     * Redireciona o usuário para o diálogo de autorização da Meta.
     */
    public function redirect(Request $request)
    {
        $state = Str::random(40);
        session(['meta_oauth_state' => $state]);

        $params = http_build_query([
            'client_id'     => config('services.meta.app_id'),
            'redirect_uri'  => config('services.meta.redirect_uri'),
            'scope'         => implode(',', [
                'pages_manage_posts',
                'pages_read_engagement',
                'instagram_basic',
                'instagram_content_publish',
                'pages_show_list',
                'business_management',
            ]),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return redirect('https://www.facebook.com/dialog/oauth?' . $params);
    }

    /**
     * Recebe o callback OAuth da Meta, troca o code pelo token de longa duração
     * e redireciona para a seleção de página.
     */
    public function callback(Request $request)
    {
        if ($request->state !== session('meta_oauth_state')) {
            return redirect()->route('meta.social.settings')
                ->with('error', 'Falha na verificação de segurança OAuth (state inválido).');
        }

        if ($request->has('error')) {
            return redirect()->route('meta.social.settings')
                ->with('error', 'Autorização cancelada: ' . $request->error_description);
        }

        // Trocar code por short-lived token
        $tokenResp = Http::get('https://graph.facebook.com/oauth/access_token', [
            'client_id'     => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'redirect_uri'  => config('services.meta.redirect_uri'),
            'code'          => $request->code,
        ]);

        if ($tokenResp->failed() || ! isset($tokenResp->json()['access_token'])) {
            return redirect()->route('meta.social.settings')
                ->with('error', 'Erro ao obter token de acesso da Meta.');
        }

        $shortToken = $tokenResp->json()['access_token'];

        // Trocar por long-lived token (válido ~60 dias para User Token)
        $llResp = Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.meta.app_id'),
            'client_secret'     => config('services.meta.app_secret'),
            'fb_exchange_token' => $shortToken,
        ]);

        $llData    = $llResp->json();
        $longToken = $llData['access_token'];
        $expiresIn = $llData['expires_in'] ?? 5184000;

        // Buscar dados do usuário Meta
        $meResp = Http::get('https://graph.facebook.com/me', [
            'access_token' => $longToken,
            'fields'       => 'id,name',
        ]);
        $metaUserId = $meResp->json()['id'] ?? null;

        // Buscar páginas do usuário com dados do Instagram vinculado
        $pagesResp = Http::get('https://graph.facebook.com/me/accounts', [
            'access_token' => $longToken,
            'fields'       => 'id,name,access_token,instagram_business_account',
        ]);

        $pages = $pagesResp->json()['data'] ?? [];

        if (empty($pages)) {
            return redirect()->route('meta.social.settings')
                ->with('error', 'Nenhuma Página do Facebook encontrada. Verifique se o perfil possui uma Página vinculada.');
        }

        session([
            'meta_long_token' => $longToken,
            'meta_expires_in' => $expiresIn,
            'meta_user_id'    => $metaUserId,
            'meta_pages'      => $pages,
        ]);

        return redirect()->route('meta.select-page');
    }

    /**
     * Exibe a tela para o cliente selecionar qual página usar.
     */
    public function selectPage(Request $request)
    {
        $pages = session('meta_pages', []);

        if (empty($pages)) {
            return redirect()->route('meta.social.settings')
                ->with('error', 'Sessão expirada. Refaça a conexão.');
        }

        return view('tenant.social.select-page', compact('pages'));
    }

    /**
     * Salva o token da página selecionada pelo cliente.
     */
    public function savePage(Request $request)
    {
        $request->validate([
            'page_id' => 'required|string',
        ]);

        $pages    = session('meta_pages', []);
        $selected = collect($pages)->firstWhere('id', $request->page_id);

        if (! $selected) {
            return back()->with('error', 'Página não encontrada na sessão.');
        }

        $tenantId  = auth()->user()->tenant_id;
        $expiresIn = session('meta_expires_in', 5184000);

        MetaToken::updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'access_token'          => $selected['access_token'],
                'meta_user_id'          => session('meta_user_id'),
                'page_id'               => $selected['id'],
                'page_name'             => $selected['name'],
                'instagram_account_id'  => $selected['instagram_business_account']['id'] ?? null,
                'expires_at'            => now()->addSeconds($expiresIn),
            ]
        );

        session()->forget(['meta_long_token', 'meta_expires_in', 'meta_user_id', 'meta_pages', 'meta_oauth_state']);

        return redirect()->route('meta.social.settings')
            ->with('success', 'Conta conectada com sucesso! Página: ' . $selected['name']);
    }

    /**
     * Remove o token do tenant (desconecta a conta).
     */
    public function disconnect(Request $request)
    {
        MetaToken::where('tenant_id', auth()->user()->tenant_id)->delete();

        return redirect()->route('meta.social.settings')
            ->with('success', 'Conta Meta desconectada com sucesso.');
    }
}
