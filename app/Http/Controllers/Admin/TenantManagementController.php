<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantManagementController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::withCount('users')->latest()->paginate(20);
        $users = User::with('tenant')->latest()->take(20)->get();

        return view('admin.tenants.index', compact('tenants', 'users'));
    }

    public function create(): View
    {
        return view('admin.tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tenant_name' => ['required', 'string', 'max:150'],
            'tenant_slug' => ['nullable', 'string', 'max:150', 'unique:tenants,slug'],
            'tenant_domain' => ['nullable', 'string', 'max:255'],
            'tenant_email' => ['nullable', 'email', 'max:150'],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'owner_password' => ['required', 'string', 'min:8'],
        ]);

        $slug = $data['tenant_slug'] ?: Str::slug($data['tenant_name']);

        if (Tenant::where('slug', $slug)->exists()) {
            return back()->withErrors(['tenant_slug' => 'Slug já existe.'])->withInput();
        }

        $tenant = Tenant::create([
            'name' => $data['tenant_name'],
            'slug' => $slug,
            'domain' => $data['tenant_domain'] ?? null,
            'email' => $data['tenant_email'] ?? null,
            'active' => true,
        ]);

        $credentials = $tenant->regenerateApiCredentials();

        User::create([
            'name' => $data['owner_name'],
            'email' => $data['owner_email'],
            'password' => $data['owner_password'],
            'tenant_id' => $tenant->id,
            'role' => 'tenant_admin',
        ]);

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant criado. API KEY: {$credentials['api_key']} | API SECRET: {$credentials['api_secret_plain']}");
    }

    public function regenerateCredentials(Tenant $tenant): RedirectResponse
    {
        $credentials = $tenant->regenerateApiCredentials();

        return back()->with('success', "Credenciais regeneradas para {$tenant->name}. API KEY: {$credentials['api_key']} | API SECRET: {$credentials['api_secret_plain']}");
    }
}
