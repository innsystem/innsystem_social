@extends('admin.layout')

@section('title', 'Tenants')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 mb-0">Gestão de Contas (Tenants)</h1>
        <p class="text-muted mb-0">Crie e administre contas de clientes OpenCart.</p>
    </div>
    <a href="{{ route('admin.tenants.create') }}" class="btn btn-primary">Novo Tenant</a>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Slug</th>
                    <th>Domínio Loja</th>
                    <th>Usuários</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                @forelse($tenants as $tenant)
                    <tr>
                        <td>{{ $tenant->name }}</td>
                        <td><code>{{ $tenant->slug }}</code></td>
                        <td>{{ $tenant->domain ?: '-' }}</td>
                        <td>{{ $tenant->users_count }}</td>
                        <td>
                            @if($tenant->active)
                                <span class="badge bg-success">Ativo</span>
                            @else
                                <span class="badge bg-secondary">Inativo</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.tenants.regenerate-credentials', $tenant) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary">Regenerar API</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sem tenants cadastrados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{ $tenants->links() }}

<div class="card mt-4">
    <div class="card-header">Usuários recentes</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tenant</th>
                    <th>Perfil</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->tenant?->name ?: '-' }}</td>
                        <td>{{ $user->role }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-3">Sem usuários.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
