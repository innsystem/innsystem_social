<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @php
        $metaTitle = trim($__env->yieldContent('meta_title', 'InnSystem Social'));
        $metaDescription = trim($__env->yieldContent('meta_description', 'Páginas institucionais da InnSystem Social.'));
        $currentUrl = url()->current();
        $ogImage = asset(str_replace(' ', '%20', 'identidade_visual_preto.png'));
    @endphp

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index,follow">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="InnSystem Social">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        body {
            background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            min-height: 100vh;
        }
        .page-card {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
        }
        .brand-logo {
            max-width: 220px;
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <header class="py-4">
        <div class="container text-center">
            <a href="{{ url('/') }}" class="d-inline-block" aria-label="InnSystem Social">
                <img src="{{ asset(str_replace(' ', '%20', 'identidade_visual_preto.png')) }}" alt="Logo InnSystem Social" class="brand-logo">
            </a>
        </div>
    </header>

    <main class="pb-5">
        <div class="container">
            <div class="card page-card">
                <div class="card-body p-4 p-md-5">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <footer class="pb-4">
        <div class="container text-center text-secondary small">
            <p class="mb-1">InnSystem Social</p>
            <p class="mb-0">
                <a class="text-decoration-none me-2" href="{{ url('/institucional/politica-privacidade') }}">Política de Privacidade</a>
                <a class="text-decoration-none me-2" href="{{ url('/institucional/termos-servicos') }}">Termos de Serviços</a>
                <a class="text-decoration-none" href="{{ url('/institucional/exclusao-dados-usuario') }}">Exclusão de Dados</a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
