<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\MetaToken;
use App\Models\SocialPost;
use Illuminate\Http\Request;

class SocialSettingsController extends Controller
{
    /**
     * Painel de configurações de redes sociais do tenant.
     */
    public function index(Request $request)
    {
        $tenantId  = auth()->user()->tenant_id;
        $metaToken = MetaToken::where('tenant_id', $tenantId)->first();

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
            'dailyFacebook'
        ));
    }
}
