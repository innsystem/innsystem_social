<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Painel') — InnSystem Social</title>

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body        { background: #f1f4f9; min-height: 100vh; }
        .sidebar    { width: 260px; min-height: 100vh; background: #1a1d2e; }
        .sidebar a  { color: #adb5bd; text-decoration: none; transition: color .2s; }
        .sidebar a:hover, .sidebar a.active { color: #fff; background: rgba(255,255,255,.07); border-radius: 8px; }
        .brand-logo { max-width: 160px; }
        .nav-label  { font-size: .7rem; letter-spacing: .08em; text-transform: uppercase; color: #6c757d; padding: .5rem 1rem; margin-top: .75rem; }
        .card       { border: 0; border-radius: 1rem; box-shadow: 0 4px 20px rgba(15,23,42,.06); }
        .badge-platform-instagram { background: linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); }
        .badge-platform-facebook  { background: #1877f2; }
    </style>

    @stack('head')
</head>
<body>
<div class="d-flex">
    {{-- Sidebar --}}
    <nav class="sidebar d-flex flex-column p-3 gap-1" style="position:sticky;top:0;height:100vh;overflow-y:auto;">
        <a href="{{ url('/') }}" class="d-block text-center py-3 mb-2">
            <img src="{{ asset(str_replace(' ', '%20', 'identidade visual_preto.png')) }}"
                 class="brand-logo" alt="InnSystem Social" style="filter:brightness(10)">
        </a>

        @if(isset($currentTenant))
            <div class="text-center small text-secondary mb-2 border-bottom border-secondary pb-2">
                {{ $currentTenant->name }}
            </div>
        @endif

        <div class="nav-label">Redes Sociais</div>
        <a href="{{ route('meta.social.settings') }}" class="d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('meta.social.*') ? 'active' : '' }}">
            <i class="bi bi-share"></i> Redes Sociais
        </a>

        <div class="nav-label">Produtos</div>
        <a href="{{ route('tenant.products.index') }}" class="d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('tenant.products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Produtos
        </a>
        <a href="{{ route('tenant.posts.history') }}" class="d-flex align-items-center gap-2 px-3 py-2 {{ request()->routeIs('tenant.posts.*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i> Histórico de Posts
        </a>

        <div class="mt-auto pt-3 border-top border-secondary">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </button>
            </form>
        </div>
    </nav>

    {{-- Conteúdo --}}
    <main class="flex-grow-1 p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
