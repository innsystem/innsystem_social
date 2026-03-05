<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use Illuminate\Http\Request;

class PostHistoryController extends Controller
{
    public function index(Request $request)
    {
        $posts = SocialPost::where('tenant_id', auth()->user()->tenant_id)
            ->with('product:id,name')
            ->latest()
            ->paginate(20);

        return view('tenant.posts.history', compact('posts'));
    }
}
