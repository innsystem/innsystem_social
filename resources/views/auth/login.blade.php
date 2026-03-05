<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar - InnSystem Social</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        :root {
            --brand-primary: #1296db;
            --brand-dark: #0f172a;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(1200px circle at 5% 10%, rgba(18, 150, 219, 0.16), transparent 45%),
                radial-gradient(1200px circle at 95% 90%, rgba(18, 150, 219, 0.12), transparent 45%),
                linear-gradient(180deg, #f8fbff 0%, #eef3f9 100%);
        }

        .auth-wrapper {
            min-height: 100vh;
        }

        .auth-card {
            border: 0;
            border-radius: 1.1rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
        }

        .brand-logo {
            max-width: 220px;
            width: 100%;
            height: auto;
        }

        .auth-title {
            color: var(--brand-dark);
            font-weight: 700;
        }

        .auth-subtitle {
            color: #6b7280;
            font-size: .95rem;
        }

        .form-control {
            border-radius: .7rem;
            padding: .68rem .85rem;
            border-color: #d7deea;
        }

        .form-control:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 .2rem rgba(18, 150, 219, 0.18);
        }

        .btn-login {
            border-radius: .7rem;
            padding: .7rem .9rem;
            font-weight: 600;
            background: linear-gradient(135deg, #1296db 0%, #1784c4 100%);
            border: 0;
        }
    </style>
</head>
<body>
<div class="container auth-wrapper d-flex align-items-center py-4 py-md-5">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5 col-xl-4">
            <div class="text-center mb-4">
                <img
                    src="{{ asset(str_replace(' ', '%20', 'identidade_visual_preto.png')) }}"
                    alt="InnSystem Social"
                    class="brand-logo"
                >
            </div>
            <div class="card auth-card">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 auth-title mb-2">Acessar painel</h1>
                    <p class="auth-subtitle mb-4">Entre com suas credenciais para gerenciar integrações sociais.</p>

                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.attempt') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">E-mail</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Senha</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Lembrar login</label>
                        </div>
                        <button class="btn btn-primary btn-login w-100">Entrar</button>
                    </form>

                    @if(\App\Models\User::count() === 0)
                        <hr class="my-4">
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100">Criar primeiro usuário admin</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
