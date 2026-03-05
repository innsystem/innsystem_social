<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\MetaFacebookService;
use App\Services\MetaInstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class OpenCartIntegrationController extends Controller
{
    public function __construct(
        private MetaInstagramService $instagramService,
        private MetaFacebookService $facebookService,
    ) {
    }

    public function connectionStatus(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');
        $metaToken = $tenant->metaToken;

        return response()->json([
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
            ],
            'connected' => (bool) $metaToken,
            'facebook_connected' => (bool) ($metaToken?->page_id),
            'instagram_connected' => (bool) ($metaToken?->instagram_account_id),
            'page_name' => $metaToken?->page_name,
        ]);
    }

    public function oauthUrl(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $url = URL::temporarySignedRoute(
            'meta.public.redirect',
            now()->addMinutes(20),
            ['tenant' => $tenant->id]
        );

        return response()->json([
            'oauth_connect_url' => $url,
            'expires_at' => now()->addMinutes(20)->toIso8601String(),
        ]);
    }

    public function publish(Request $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = $request->attributes->get('tenant');

        $data = $request->validate([
            'external_product_id' => ['nullable', 'string', 'max:120'],
            'source_domain' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2200'],
            'image_url' => ['required', 'url', 'max:1000'],
            'product_url' => ['nullable', 'url', 'max:1000'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:instagram,facebook'],
        ]);

        $caption = $data['caption'] ?? $data['title'];

        if (! empty($data['product_url'])) {
            $caption .= "\n\n" . $data['product_url'];
        }

        $payload = [
            'external_product_id' => $data['external_product_id'] ?? null,
            'source_domain' => $data['source_domain'] ?? null,
            'caption' => $caption,
            'image_url' => $data['image_url'],
        ];

        $results = [];

        if (in_array('instagram', $data['platforms'], true)) {
            $results['instagram'] = $this->instagramService->publishProduct($tenant->id, $payload);
        }

        if (in_array('facebook', $data['platforms'], true)) {
            $results['facebook'] = $this->facebookService->publishProduct($tenant->id, $payload);
        }

        $allSuccess = collect($results)->every(fn ($result) => ($result['success'] ?? false) === true);

        return response()->json([
            'success' => $allSuccess,
            'tenant_id' => $tenant->id,
            'results' => $results,
        ], $allSuccess ? 200 : 422);
    }
}
