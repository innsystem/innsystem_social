@extends('tenant.layout')

@section('title', 'Histórico de Posts')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1">Histórico de Publicações</h1>
        <p class="text-muted small mb-0">Registro de todas as publicações nas redes sociais.</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        @if($posts->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-clock-history display-3 text-muted mb-3 d-block"></i>
                <p class="text-muted">Nenhuma publicação registrada ainda.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Produto</th>
                            <th>Plataforma</th>
                            <th>Status</th>
                            <th>Legenda</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($posts as $post)
                        <tr>
                            <td class="ps-4">{{ $post->product->name ?? '—' }}</td>
                            <td>
                                @if($post->platform === 'instagram')
                                    <span class="badge badge-platform-instagram">
                                        <i class="bi bi-instagram me-1"></i>Instagram
                                    </span>
                                @elseif($post->platform === 'facebook')
                                    <span class="badge badge-platform-facebook">
                                        <i class="bi bi-facebook me-1"></i>Facebook
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Ambos</span>
                                @endif
                            </td>
                            <td>
                                @if($post->isPublished())
                                    <span class="badge bg-success">Publicado</span>
                                @elseif($post->isFailed())
                                    <span class="badge bg-danger"
                                          data-bs-toggle="tooltip"
                                          title="{{ $post->error_message }}">
                                        Falhou
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">Pendente</span>
                                @endif
                            </td>
                            <td class="text-muted small" style="max-width:260px;">
                                <span class="d-inline-block text-truncate" style="max-width:240px;">
                                    {{ $post->caption }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                {{ $post->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $posts->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
@endpush
