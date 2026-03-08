<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MetaToken;
use App\Models\SocialPost;
use App\Services\MetaFacebookService;
use App\Services\MetaInstagramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SocialSettingsController extends Controller
{
    public function __construct(
        private MetaInstagramService $instagramService,
        private MetaFacebookService $facebookService,
    ) {
    }

    /**
     * Painel de configurações de redes sociais do tenant.
     */
    public function index(Request $request)
    {
        $tenantId  = auth()->user()->tenant_id;
        $metaToken = MetaToken::where('tenant_id', $tenantId)->first();
        $pageProfile = null;
        $instagramProfile = null;

        if ($metaToken) {
            [$pageProfile, $instagramProfile] = $this->fetchConnectedProfiles($metaToken);
        }

        $recentPosts = SocialPost::where('tenant_id', $tenantId)
            ->latest()
            ->take(10)
            ->get();

        $dailyInstagram = SocialPost::dailyCountForTenant($tenantId, 'instagram');
        $dailyFacebook  = SocialPost::dailyCountForTenant($tenantId, 'facebook');

        return view('tenant.social.settings', compact(
            'metaToken',
            'recentPosts',
            'dailyInstagram',
            'dailyFacebook',
            'pageProfile',
            'instagramProfile'
        ));
    }

    public function manualPublish(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $metaToken = MetaToken::where('tenant_id', $tenantId)->first();

        if (! $metaToken) {
            return back()->with('error', 'Conecte sua conta Meta antes de publicar.');
        }

        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:2200'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:instagram,facebook'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $path = $request->file('image')->store('social-posts/tenant-' . $tenantId, 'public');
        $publicImageUrl = asset('storage/' . $path);
        $caption = $data['caption'] ?: 'Publicação enviada pelo painel InnSystem Social.';

        $payload = [
            'external_product_id' => 'manual-upload-' . now()->format('YmdHis'),
            'source_domain' => $request->getHost(),
            'caption' => $caption,
            'image_url' => $publicImageUrl,
        ];

        $results = [];

        if (in_array('instagram', $data['platforms'], true)) {
            $results['instagram'] = $this->instagramService->publishProduct($tenantId, $payload);
        }

        if (in_array('facebook', $data['platforms'], true)) {
            $results['facebook'] = $this->facebookService->publishProduct($tenantId, $payload);
        }

        Log::info('Manual publish request finished', [
            'tenant_id' => $tenantId,
            'image_url' => $publicImageUrl,
            'platforms' => $data['platforms'],
            'results' => $results,
        ]);

        $allSuccess = collect($results)->every(fn ($result) => ($result['success'] ?? false) === true);

        if ($allSuccess) {
            return back()->with('success', 'Publicação enviada com sucesso para as plataformas selecionadas.');
        }

        return back()->with('error', 'Publicação concluída com falhas em uma ou mais plataformas. Verifique o histórico.');
    }

    private function fetchConnectedProfiles(MetaToken $metaToken): array
    {
        $baseUrl = 'https://graph.facebook.com/' . config('services.meta.api_version', 'v25.0');
        $pageProfile = null;
        $instagramProfile = null;

        if (! empty($metaToken->page_id)) {
            $pageResp = Http::get("{$baseUrl}/{$metaToken->page_id}", [
                'fields' => 'id,name,picture{url},fan_count,followers_count,link,verification_status',
                'access_token' => $metaToken->access_token,
            ]);

            $pageData = $pageResp->json() ?? [];
            Log::info('Meta page profile fetched', [
                'tenant_id' => $metaToken->tenant_id,
                'success' => $pageResp->successful(),
                'payload' => $pageData,
            ]);

            if ($pageResp->successful()) {
                $pageProfile = $pageData;
            }
        }

        if (! empty($metaToken->instagram_account_id)) {
            $igResp = Http::get("{$baseUrl}/{$metaToken->instagram_account_id}", [
                'fields' => 'id,username,name,profile_picture_url,followers_count,follows_count,media_count,biography,website',
                'access_token' => $metaToken->access_token,
            ]);

            $igData = $igResp->json() ?? [];
            Log::info('Meta instagram profile fetched', [
                'tenant_id' => $metaToken->tenant_id,
                'success' => $igResp->successful(),
                'payload' => $igData,
            ]);

            if ($igResp->successful()) {
                $instagramProfile = $igData;

                if (! empty($igData['username']) && $metaToken->instagram_username !== $igData['username']) {
                    $metaToken->update(['instagram_username' => $igData['username']]);
                }
            }
        }

        return [$pageProfile, $instagramProfile];
    }
}
