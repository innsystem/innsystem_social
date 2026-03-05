<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class OpencartApiAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Innsystem-Key');
        $secret = $request->header('X-Innsystem-Secret');

        if (! $key || ! $secret) {
            return response()->json([
                'message' => 'Credenciais ausentes. Envie X-Innsystem-Key e X-Innsystem-Secret.',
            ], 401);
        }

        $tenant = Tenant::where('api_key', $key)->where('active', true)->first();

        if (! $tenant || ! $tenant->api_secret || ! Hash::check($secret, $tenant->api_secret)) {
            return response()->json([
                'message' => 'Credenciais inválidas.',
            ], 401);
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
