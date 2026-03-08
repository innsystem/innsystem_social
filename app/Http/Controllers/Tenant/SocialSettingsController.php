<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MetaToken;
use App\Models\SocialPost;
use App\Services\MetaInstagramService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SocialSettingsController extends Controller
{
    public function __construct(
        private MetaInstagramService $instagramService,
    ) {
    }

    /**
     * Painel de configurações de redes sociais do tenant.
     */
    public function index(Request $request)
    {
        $tenantId  = auth()->user()->tenant_id;
        $metaToken = MetaToken::where('tenant_id', $tenantId)->first();
        $instagramProfile = null;

        if ($metaToken) {
            $instagramProfile = $this->fetchInstagramProfile($metaToken);
        }

        $recentPosts = SocialPost::where('tenant_id', $tenantId)
            ->latest()
            ->take(10)
            ->get();

        $dailyInstagram = SocialPost::dailyCountForTenant($tenantId, 'instagram');

        return view('tenant.social.settings', compact(
            'metaToken',
            'recentPosts',
            'dailyInstagram',
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
            'platforms.*' => ['in:instagram'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp', 'max:10240'],
        ]);

        if (! function_exists('imagecreatefromstring') || ! function_exists('imagejpeg')) {
            return back()->with('error', 'A extensão GD do PHP não está habilitada no servidor. Ative-a para converter imagens em JPG.');
        }

        $uploadedFile = $request->file('image');
        $uploadedExtension = strtolower((string) $uploadedFile->getClientOriginalExtension());

        // Converte qualquer formato aceito para JPG para maior compatibilidade com Instagram Graph API.
        $path = $this->convertImageToJpegAndStore($uploadedFile, $tenantId);
        if (! $path) {
            return back()->with('error', 'Falha ao converter a imagem para JPG. Tente outra imagem.');
        }

        $relativePublicPath = Storage::url($path);
        $appUrl = rtrim(config('app.url') ?: $request->getSchemeAndHttpHost(), '/');
        $publicImageUrl = str_starts_with($relativePublicPath, 'http')
            ? $relativePublicPath
            : $appUrl . '/' . ltrim($relativePublicPath, '/');

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

        Log::info('Manual publish request finished', [
            'tenant_id' => $tenantId,
            'image_url' => $publicImageUrl,
            'image_extension' => $uploadedExtension,
            'converted_jpg_path' => $path,
            'platforms' => $data['platforms'],
            'results' => $results,
        ]);

        $allSuccess = collect($results)->every(fn ($result) => ($result['success'] ?? false) === true);

        if ($allSuccess) {
            return back()->with('success', 'Publicação enviada com sucesso para as plataformas selecionadas.');
        }

        return back()->with('error', 'Publicação concluída com falhas em uma ou mais plataformas. Verifique o histórico.');
    }

    private function fetchInstagramProfile(MetaToken $metaToken): ?array
    {
        $baseUrl = 'https://graph.facebook.com/' . config('services.meta.api_version', 'v25.0');
        $instagramProfile = null;

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

        return $instagramProfile;
    }

    private function convertImageToJpegAndStore(UploadedFile $file, int $tenantId): ?string
    {
        try {
            $contents = file_get_contents($file->getRealPath());
            if ($contents === false) {
                return null;
            }

            $source = imagecreatefromstring($contents);
            if (! $source) {
                return null;
            }

            $width = imagesx($source);
            $height = imagesy($source);

            // Fundo branco para preservar legibilidade em imagens com transparência.
            $canvas = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
            imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

            ob_start();
            imagejpeg($canvas, null, 90);
            $jpegBinary = ob_get_clean();

            imagedestroy($source);
            imagedestroy($canvas);

            if (! is_string($jpegBinary) || $jpegBinary === '') {
                return null;
            }

            $fileName = Str::uuid()->toString() . '.jpg';
            $path = 'social-posts/tenant-' . $tenantId . '/' . $fileName;
            Storage::disk('public')->put($path, $jpegBinary);

            return $path;
        } catch (\Throwable $e) {
            Log::error('Image conversion to JPG failed', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
            ]);

            return null;
        }
    }
}
