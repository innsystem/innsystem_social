<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::where('tenant_id', auth()->user()->tenant_id)
            ->where('active', true)
            ->latest()
            ->paginate(12);

        return view('tenant.products.index', compact('products'));
    }
}
