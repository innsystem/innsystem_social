<?php

namespace App\Services;

use App\Models\MetaToken;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaFacebookService
{
    private string $baseUrl;

    public function __construct()
    {
        $version       = config('services.meta.api_version', 'v25.0');
        $this->baseUrl = 'https://graph.facebook.com/' . $version;
    }

    /**
     * Publica um produto na Página do Facebook do tenant.
     */
    public function publishProduct(int $tenantId, array $product): array
    {
        $token = MetaToken::where('tenant_id', $tenantId)->first();

        if (! $token) {
            return ['success' => false, 'error' => 'Conta Meta não conectada.'];
        }

        if (! $token->hasFacebook()) {
            return ['success' => false, 'error' => 'Página do Facebook não está conectada.'];
        }

        // Verificar rate limit
        if (SocialPost::dailyCountForTenant($tenantId, 'facebook') >= 50) {
            return ['success' => false, 'error' => 'Limite diário de 50 publicações no Facebook atingido.'];
        }

        $log = SocialPost::create([
            'tenant_id'  => $tenantId,
            'product_id' => $product['product_id'] ?? null,
            'platform'   => 'facebook',
            'caption'    => $product['caption'],
            'image_url'  => $product['image_url'],
            'status'     => 'pending',
        ]);

        $response = Http::post("{$this->baseUrl}/{$token->page_id}/photos", [
            'url'          => $product['image_url'],
            'message'      => $product['caption'],
            'access_token' => $token->access_token,
        ]);

        $result = $response->json();

        if (isset($result['id'])) {
            $log->update([
                'status'       => 'published',
                'meta_post_id' => $result['id'],
                'published_at' => now(),
            ]);
            return ['success' => true, 'post_id' => $result['id']];
        }

        $errorMsg = $result['error']['message'] ?? 'Erro desconhecido na API do Facebook.';
        Log::error('Facebook publishProduct error', $result ?? []);

        $log->update(['status' => 'failed', 'error_message' => $errorMsg]);
        return ['success' => false, 'error' => $errorMsg];
    }
}
