@extends('admin.layout')

@section('title', 'Novo Tenant')

@section('content')
<h1 class="h4 mb-3">Criar conta de cliente (tenant)</h1>

<form method="POST" action="{{ route('admin.tenants.store') }}" class="card">
    @csrf
    <div class="card-body">
        <h2 class="h6">Dados do Tenant</h2>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label">Nome do tenant</label>
                <input type="text" name="tenant_name" class="form-control" value="{{ old('tenant_name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug (opcional)</label>
                <input type="text" name="tenant_slug" class="form-control" value="{{ old('tenant_slug') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Domínio da loja OpenCart</label>
                <input type="text" name="tenant_domain" class="form-control" value="{{ old('tenant_domain') }}" placeholder="aliancasmoedasbr.com.br">
            </div>
            <div class="col-md-6">
                <label class="form-label">E-mail do tenant</label>
                <input type="email" name="tenant_email" class="form-control" value="{{ old('tenant_email') }}">
            </div>
        </div>

        <h2 class="h6">Usuário responsável (tenant_admin)</h2>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nome</label>
                <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">E-mail</label>
                <input type="email" name="owner_email" class="form-control" value="{{ old('owner_email') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Senha inicial</label>
                <input type="text" name="owner_password" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end gap-2">
        <a href="{{ route('admin.tenants.index') }}" class="btn btn-outline-secondary">Cancelar</a>
        <button class="btn btn-primary">Criar Tenant</button>
    </div>
</form>
@endsection
