<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selecionar Página - {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h5 mb-3">Conectar Facebook/Instagram</h1>
                    <p class="text-muted">Selecione a página que será conectada para o tenant <strong>{{ $tenant->name }}</strong>.</p>
                    <form method="POST" action="{{ route('meta.public.save-page') }}">
                        @csrf
                        <input type="hidden" name="flow" value="{{ $flow }}">
                        <div class="list-group mb-3">
                            @foreach($pages as $page)
                                <label class="list-group-item d-flex align-items-center gap-2">
                                    <input type="radio" name="page_id" value="{{ $page['id'] }}" required>
                                    <span>{{ $page['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button class="btn btn-primary">Salvar conexão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
