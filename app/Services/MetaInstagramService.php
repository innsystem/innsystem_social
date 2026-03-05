<?php

namespace App\Services;

use App\Models\MetaToken;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaInstagramService
{
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiVersion = config('services.meta.api_version', 'v25.0');
        $this->baseUrl    = 'https://graph.facebook.com/' . $this->apiVersion;
    }

    /**
     * Publica um produto no Instagram do tenant.
     */
    public function publishProduct(int $tenantId, array $product): array
    {
        $token = MetaToken::where('tenant_id', $tenantId)->first();

        if (! $token) {
            return ['success' => false, 'error' => 'Conta Meta não conectada.'];
        }

        if (! $token->hasInstagram()) {
            return ['success' => false, 'error' => 'Conta do Instagram não está conectada.'];
        }

        // Verificar rate limit (50 posts/dia por usuário)
        if (SocialPost::dailyCountForTenant($tenantId, 'instagram') >= 50) {
            return ['success' => false, 'error' => 'Limite diário de 50 publicações no Instagram atingido.'];
        }

        $log = SocialPost::create([
            'tenant_id'   => $tenantId,
            'product_id'  => $product['product_id'] ?? null,
            'platform'    => 'instagram',
            'caption'     => $product['caption'],
            'image_url'   => $product['image_url'],
            'status'      => 'pending',
        ]);

        // Etapa 1: criar container de mídia
        $containerId = $this->createMediaContainer(
            $token->instagram_account_id,
            $token->access_token,
            $product['image_url'],
            $product['caption']
        );

        if (! $containerId) {
            $log->update(['status' => 'failed', 'error_message' => 'Falha ao criar container de mídia.']);
            return ['success' => false, 'error' => 'Falha ao criar container de mídia.'];
        }

        // Etapa 2: aguardar processamento e publicar
        $ready = $this->waitForContainer($containerId, $token->access_token);

        if (! $ready) {
            $log->update(['status' => 'failed', 'error_message' => 'Timeout no processamento do container.']);
            return ['success' => false, 'error' => 'Timeout no processamento da imagem pelo Instagram.'];
        }

        $postId = $this->publishContainer(
            $token->instagram_account_id,
            $token->access_token,
            $containerId
        );

        if ($postId) {
            $log->update([
                'status'       => 'published',
                'meta_post_id' => $postId,
                'published_at' => now(),
            ]);
            return ['success' => true, 'post_id' => $postId];
        }

        $log->update(['status' => 'failed', 'error_message' => 'Falha ao publicar container.']);
        return ['success' => false, 'error' => 'Falha ao publicar no Instagram.'];
    }

    private function createMediaContainer(
        string $igAccountId,
        string $token,
        string $imageUrl,
        string $caption
    ): ?string {
        $response = Http::post("{$this->baseUrl}/{$igAccountId}/media", [
            'image_url'    => $imageUrl,
            'caption'      => $caption,
            'access_token' => $token,
        ]);

        if ($response->failed()) {
            Log::error('Instagram createMediaContainer error', $response->json() ?? []);
            return null;
        }

        return $response->json()['id'] ?? null;
    }

    /**
     * Aguarda o container ficar FINISHED (polling com até 10 tentativas de 2s).
     */
    private function waitForContainer(string $containerId, string $token): bool
    {
        for ($i = 0; $i < 10; $i++) {
            $resp   = Http::get("{$this->baseUrl}/{$containerId}", [
                'fields'       => 'status_code',
                'access_token' => $token,
            ]);
            $status = $resp->json()['status_code'] ?? '';

            if ($status === 'FINISHED') {
                return true;
            }

            if ($status === 'ERROR') {
                Log::error('Instagram container ERROR', $resp->json() ?? []);
                return false;
            }

            sleep(2);
        }

        return false;
    }

    private function publishContainer(
        string $igAccountId,
        string $token,
        string $containerId
    ): ?string {
        $response = Http::post("{$this->baseUrl}/{$igAccountId}/media_publish", [
            'creation_id'  => $containerId,
            'access_token' => $token,
        ]);

        if ($response->failed()) {
            Log::error('Instagram publishContainer error', $response->json() ?? []);
            return null;
        }

        return $response->json()['id'] ?? null;
    }
}
