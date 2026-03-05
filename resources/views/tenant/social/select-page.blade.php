@extends('tenant.layout')

@section('title', 'Selecionar Página')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-journals display-4 text-primary mb-3 d-block"></i>
                    <h2 class="h4">Selecione a Página do Facebook</h2>
                    <p class="text-muted">
                        Escolha qual Página será usada para publicações. Se a Página tiver
                        um Instagram Profissional vinculado, ele também será conectado automaticamente.
                    </p>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="{{ route('meta.save-page') }}">
                    @csrf
                    <div class="list-group mb-4">
                        @foreach($pages as $page)
                        <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 cursor-pointer">
                            <input type="radio" name="page_id" value="{{ $page['id'] }}" class="form-check-input mt-0" required>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $page['name'] }}</div>
                                <div class="small text-muted">ID: {{ $page['id'] }}</div>
                                @if(! empty($page['instagram_business_account']))
                                    <span class="badge mt-1" style="background:linear-gradient(45deg,#f09433,#bc1888);">
                                        <i class="bi bi-instagram me-1"></i>Instagram vinculado
                                    </span>
                                @else
                                    <span class="badge bg-secondary mt-1">Sem Instagram vinculado</span>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('meta.social.settings') }}" class="btn btn-outline-secondary flex-fill">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-check-lg me-2"></i>Confirmar Seleção
                        </button>
                    </div>
                </form>

                <div class="alert alert-info mt-4 small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Para publicar no Instagram, o perfil deve ser do tipo <strong>Profissional</strong> ou
                    <strong>Criador</strong> e deve estar vinculado a uma Página do Facebook.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
