@extends('tenant.layout')

@section('title', 'Produtos')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1">Produtos</h1>
        <p class="text-muted small mb-0">Selecione um produto para publicar nas redes sociais.</p>
    </div>
</div>

@if($products->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-box-seam display-3 text-muted mb-3 d-block"></i>
            <p class="text-muted">Nenhum produto cadastrado ainda.</p>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach($products as $product)
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card h-100">
                @if($product->getPublicImageUrl())
                    <img src="{{ $product->getPublicImageUrl() }}" class="card-img-top"
                         alt="{{ $product->name }}" style="height:200px;object-fit:cover;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center"
                         style="height:200px;">
                        <i class="bi bi-image text-muted fs-1"></i>
                    </div>
                @endif
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">{{ $product->name }}</h6>
                    @if($product->price)
                        <p class="text-primary fw-semibold mb-3">
                            R$ {{ number_format($product->price, 2, ',', '.') }}
                        </p>
                    @endif
                    <button class="btn btn-primary btn-sm mt-auto"
                            onclick="openPublishModal({{ $product->id }}, '{{ $product->name }}', '{{ $product->getPublicImageUrl() }}')">
                        <i class="bi bi-send me-1"></i>Publicar nas Redes
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $products->links() }}</div>
@endif

{{-- Modal de publicação --}}
<div class="modal fade" id="publishModal" tabindex="-1" aria-labelledby="publishModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="publishModalLabel">
                    <i class="bi bi-send me-2 text-primary"></i>Publicar Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="publishForm">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="productPreview" src="" alt="Preview"
                             class="rounded-3 img-fluid" style="max-height:200px;object-fit:cover;">
                    </div>

                    <label class="form-label fw-semibold">Legenda do post</label>
                    <textarea id="captionInput" name="caption" class="form-control mb-3"
                              rows="4" maxlength="2200"
                              placeholder="Digite a legenda do post (máx. 2200 caracteres)…"></textarea>

                    <label class="form-label fw-semibold">Publicar em</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="platforms[]" value="instagram" id="chkInstagram" checked>
                            <label class="form-check-label" for="chkInstagram">
                                <i class="bi bi-instagram me-1"></i>Instagram
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="platforms[]" value="facebook" id="chkFacebook" checked>
                            <label class="form-check-label" for="chkFacebook">
                                <i class="bi bi-facebook me-1"></i>Facebook
                            </label>
                        </div>
                    </div>

                    <div id="publishResult" class="mt-3 d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnPublish" class="btn btn-primary">
                        <span id="btnPublishText"><i class="bi bi-send me-2"></i>Publicar Agora</span>
                        <span id="btnPublishLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-2"></span>Publicando…
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentProductId = null;

function openPublishModal(productId, productName, imageUrl) {
    currentProductId = productId;
    document.getElementById('productPreview').src    = imageUrl || '';
    document.getElementById('productPreview').style.display = imageUrl ? '' : 'none';
    document.getElementById('publishModalLabel').innerHTML =
        '<i class="bi bi-send me-2 text-primary"></i>' + productName;
    document.getElementById('captionInput').value    = '';
    document.getElementById('publishResult').className = 'mt-3 d-none';
    new bootstrap.Modal(document.getElementById('publishModal')).show();
}

document.getElementById('publishForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const caption   = document.getElementById('captionInput').value.trim();
    const platforms = [...document.querySelectorAll('input[name="platforms[]"]:checked')]
                        .map(c => c.value);

    if (! caption)     { alert('Digite uma legenda para o post.'); return; }
    if (! platforms.length) { alert('Selecione ao menos uma plataforma.'); return; }

    document.getElementById('btnPublishText').classList.add('d-none');
    document.getElementById('btnPublishLoading').classList.remove('d-none');
    document.getElementById('btnPublish').disabled = true;

    try {
        const resp = await fetch(`/products/${currentProductId}/publish`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ caption, platforms }),
        });

        const data = await resp.json();
        const el   = document.getElementById('publishResult');

        if (data.success) {
            el.innerHTML  = '<div class="alert alert-success mb-0"><i class="bi bi-check-circle me-2"></i>Publicado com sucesso!</div>';
        } else {
            let msg = '<div class="alert alert-danger mb-0"><i class="bi bi-x-circle me-2"></i>';
            if (data.results) {
                Object.entries(data.results).forEach(([p, r]) => {
                    if (! r.success) msg += `<strong>${p}:</strong> ${r.error} `;
                });
            } else {
                msg += data.error || 'Erro ao publicar.';
            }
            msg += '</div>';
            el.innerHTML = msg;
        }

        el.classList.remove('d-none');
    } catch (err) {
        const el = document.getElementById('publishResult');
        el.innerHTML  = '<div class="alert alert-danger mb-0">Erro de comunicação com o servidor.</div>';
        el.classList.remove('d-none');
    } finally {
        document.getElementById('btnPublishText').classList.remove('d-none');
        document.getElementById('btnPublishLoading').classList.add('d-none');
        document.getElementById('btnPublish').disabled = false;
    }
});
</script>
@endpush
