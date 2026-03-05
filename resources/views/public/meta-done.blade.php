<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conexão Meta - Concluída</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body p-4 text-center">
                    @if(session('success'))
                        <h1 class="h4 text-success mb-3">Conexão concluída</h1>
                        <p>{{ session('success') }}</p>
                    @else
                        <h1 class="h4 text-danger mb-3">Falha na conexão</h1>
                        <p>{{ session('error', 'Não foi possível concluir a conexão.') }}</p>
                    @endif
                    <p class="text-muted mb-0">Você pode fechar esta aba e voltar ao OpenCart.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
