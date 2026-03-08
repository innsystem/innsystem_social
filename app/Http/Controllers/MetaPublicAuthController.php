<?php

namespace App\Http\Controllers;

use App\Models\MetaToken;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MetaPublicAuthController extends Controller
{
    public function redirect(Request $request, Tenant $tenant): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(401, 'Link expirado ou inválido.');
        }

        $state = Str::random(40);

        session([
            'meta_public_state' => $state,
            'meta_public_tenant_id' => $tenant->id,
        ]);

        $params = http_build_query([
            'client_id' => config('services.meta.app_id'),
            'redirect_uri' => route('meta.public.callback'),
            'scope' => implode(',', [
                'pages_read_engagement',
                'instagram_basic',
                'instagram_content_publish',
                'pages_show_list',
                'business_management',
            ]),
            'response_type' => 'code',
            'state' => $state,
        ]);

        return redirect('https://www.facebook.com/dialog/oauth?' . $params);
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            return redirect()->route('meta.public.done')->with('error', 'Autorização cancelada/bloqueada pela Meta.');
        }

        if (! $request->filled('state') || ! session()->has('meta_public_state')) {
            return redirect()->route('meta.public.done')->with('error', 'Fluxo OAuth sem state válido. Gere um novo link de conexão.');
        }

        if ($request->state !== session('meta_public_state')) {
            return redirect()->route('meta.public.done')->with('error', 'State inválido no OAuth.');
        }

        $tenantId = session('meta_public_tenant_id');
        abort_unless($tenantId, 403, 'Tenant não identificado.');

        $tokenResp = Http::get('https://graph.facebook.com/oauth/access_token', [
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'redirect_uri' => route('meta.public.callback'),
            'code' => $request->code,
        ]);
        $this->logGraphPayload('public.callback.short_token', $tokenResp->json() ?? [], ['access_token']);

        if ($tokenResp->failed() || ! isset($tokenResp->json()['access_token'])) {
            return redirect()->route('meta.public.done')->with('error', 'Falha ao obter token.');
        }

        $shortToken = $tokenResp->json()['access_token'];

        $llResp = Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'fb_exchange_token' => $shortToken,
        ]);
        $this->logGraphPayload('public.callback.long_token', $llResp->json() ?? [], ['access_token', 'expires_in']);

        $longToken = $llResp->json()['access_token'] ?? null;
        $expiresIn = $llResp->json()['expires_in'] ?? 5184000;

        if (! $longToken) {
            return redirect()->route('meta.public.done')->with('error', 'Falha ao gerar long-lived token.');
        }

        $meResp = Http::get('https://graph.facebook.com/me', [
            'access_token' => $longToken,
            'fields' => 'id',
        ]);
        $this->logGraphPayload('public.callback.me', $meResp->json() ?? [], ['id']);
        $metaUserId = $meResp->json()['id'] ?? null;

        $pagesResp = Http::get('https://graph.facebook.com/me/accounts', [
            'access_token' => $longToken,
            'fields' => 'id,name,access_token,instagram_business_account',
        ]);
        $this->logGraphPayload('public.callback.pages', $pagesResp->json() ?? [], ['data']);
        $pages = $pagesResp->json()['data'] ?? [];

        if (empty($pages)) {
            return redirect()->route('meta.public.done')->with('error', 'Nenhuma página encontrada.');
        }

        session([
            'meta_public_pages' => $pages,
            'meta_public_expires_in' => $expiresIn,
            'meta_public_user_id' => $metaUserId,
        ]);

        return redirect()->route('meta.public.select-page');
    }

    public function selectPage(): View|RedirectResponse
    {
        $pages = session('meta_public_pages', []);
        $tenantId = session('meta_public_tenant_id');

        if (empty($pages) || ! $tenantId) {
            return redirect()->route('meta.public.done')->with('error', 'Sessão expirada.');
        }

        $tenant = Tenant::find($tenantId);

        return view('public.meta-select-page', compact('pages', 'tenant'));
    }

    public function savePage(Request $request): RedirectResponse
    {
        $request->validate([
            'page_id' => ['required', 'string'],
        ]);

        $tenantId = session('meta_public_tenant_id');
        $pages = session('meta_public_pages', []);
        $selected = collect($pages)->firstWhere('id', $request->input('page_id'));

        if (! $tenantId || ! $selected) {
            return redirect()->route('meta.public.done')->with('error', 'Sessão inválida para salvar página.');
        }

        MetaToken::updateOrCreate(
            ['tenant_id' => $tenantId],
            [
                'access_token' => $selected['access_token'],
                'meta_user_id' => session('meta_public_user_id'),
                'page_id' => $selected['id'],
                'page_name' => $selected['name'],
                'instagram_account_id' => $selected['instagram_business_account']['id'] ?? null,
                'expires_at' => now()->addSeconds(session('meta_public_expires_in', 5184000)),
            ]
        );
        $this->logGraphPayload('public.save_page.selected_page', $selected, ['id', 'name', 'access_token']);

        session()->forget([
            'meta_public_state',
            'meta_public_tenant_id',
            'meta_public_pages',
            'meta_public_expires_in',
            'meta_public_user_id',
        ]);

        return redirect()->route('meta.public.done')->with('success', 'Conta conectada com sucesso. Agora você pode fechar esta janela.');
    }

    public function done(): View
    {
        return view('public.meta-done');
    }

    private function logGraphPayload(string $stage, array $payload, array $expectedFields = []): void
    {
        $missing = [];

        foreach ($expectedFields as $field) {
            if (! array_key_exists($field, $payload)) {
                $missing[] = $field;
            }
        }

        Log::info('Meta Graph payload', [
            'stage' => $stage,
            'missing_fields' => $missing,
            'payload' => $this->sanitizeSensitive($payload),
        ]);
    }

    private function sanitizeSensitive(array $payload): array
    {
        $sensitiveKeys = [
            'access_token',
            'client_secret',
            'app_secret',
            'token',
        ];

        array_walk_recursive($payload, function (&$value, $key) use ($sensitiveKeys): void {
            if (in_array(strtolower((string) $key), $sensitiveKeys, true) && is_string($value)) {
                $value = substr($value, 0, 8) . '...';
            }
        });

        return $payload;
    }
}
