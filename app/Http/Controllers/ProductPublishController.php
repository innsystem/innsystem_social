<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\MetaFacebookService;
use App\Services\MetaInstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductPublishController extends Controller
{
    public function __construct(
        private MetaInstagramService $instagram,
        private MetaFacebookService  $facebook,
    ) {}

    /**
     * Publica o produto nas plataformas selecionadas.
     */
    public function publish(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'caption'       => 'required|string|max:2200',
            'platforms'     => 'required|array|min:1',
            'platforms.*'   => 'in:instagram,facebook',
        ]);

        // Garantir que o produto pertence ao tenant do usuário autenticado
        if ($product->tenant_id !== auth()->user()->tenant_id) {
            return response()->json(['error' => 'Produto não encontrado.'], 404);
        }

        $imageUrl = $product->getPublicImageUrl();

        if (empty($imageUrl)) {
            return response()->json(['error' => 'O produto não possui imagem pública configurada.'], 422);
        }

        $tenantId    = auth()->user()->tenant_id;
        $productData = [
            'product_id' => $product->id,
            'image_url'  => $imageUrl,
            'caption'    => $request->caption,
        ];

        $results = [];

        if (in_array('instagram', $request->platforms)) {
            $results['instagram'] = $this->instagram->publishProduct($tenantId, $productData);
        }

        if (in_array('facebook', $request->platforms)) {
            $results['facebook'] = $this->facebook->publishProduct($tenantId, $productData);
        }

        $allSuccess = collect($results)->every(fn ($r) => $r['success'] === true);

        return response()->json([
            'success' => $allSuccess,
            'results' => $results,
        ], $allSuccess ? 200 : 422);
    }

    /**
     * Histórico de posts publicados do tenant.
     */
    public function history(Request $request): JsonResponse
    {
        $posts = auth()->user()->tenant
            ->socialPosts()
            ->with('product:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($posts);
    }
}
