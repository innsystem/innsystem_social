<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    /**
     * Verificação do webhook pela Meta (GET).
     */
    public function verify(Request $request): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.meta.app_secret');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    /**
     * Recebe eventos do webhook (POST).
     */
    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::info('Meta Webhook received', [
            'object' => $payload['object'] ?? null,
            'entry'  => count($payload['entry'] ?? []),
        ]);

        // Processar eventos conforme necessário
        if (isset($payload['entry'])) {
            foreach ($payload['entry'] as $entry) {
                $this->processEntry($entry);
            }
        }

        return response('EVENT_RECEIVED', 200);
    }

    private function processEntry(array $entry): void
    {
        // Extensível: processar comentários, likes, etc.
        Log::debug('Meta Webhook entry', $entry);
    }
}
