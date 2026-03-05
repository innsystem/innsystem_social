<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant_id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Tenant não identificado.'], 403);
            }

            abort(403, 'Tenant não identificado.');
        }

        view()->share('currentTenant', $user->tenant);

        return $next($request);
    }
}
