@extends('tenant.layout')

@section('title', 'Redes Sociais')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 mb-1">Redes Sociais</h1>
        <p class="text-muted small mb-0">Conecte sua conta Meta. As publicações chegam via API do módulo OpenCart.</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        {{ $errors->first() }}
    </div>
@endif

<div class="row g-4">
    {{-- Card de conexão --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-plug me-2 text-primary"></i>Status da Conexão
                </h5>

                @if($metaToken)
                    {{-- Facebook --}}
                    <div class="d-flex align-items-center gap-3 mb-3 p-3 bg-light rounded-3">
                        @if(!empty($pageProfile['picture']['data']['url']))
                            <img src="{{ $pageProfile['picture']['data']['url'] }}"
                                 alt="Imagem da Página"
                                 class="rounded-circle"
                                 style="width:48px;height:48px;object-fit:cover;flex-shrink:0;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:#1877f2;flex-shrink:0;">
                                <i class="bi bi-facebook text-white fs-5"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Facebook</div>
                            <div class="small text-muted">{{ $pageProfile['name'] ?? $metaToken->page_name ?? 'Página conectada' }}</div>
                            @if(!empty($pageProfile['id']))
                                <div class="small text-muted">ID: {{ $pageProfile['id'] }}</div>
                            @endif
                        </div>
                        <span class="badge bg-success">Conectado</span>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="border rounded-3 p-2 text-center">
                                <div class="small text-muted">Seguidores (Página)</div>
                                <div class="fw-bold">{{ $pageProfile['followers_count'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-2 text-center">
                                <div class="small text-muted">Curtidas (fan_count)</div>
                                <div class="fw-bold">{{ $pageProfile['fan_count'] ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Instagram --}}
                    <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded-3">
                        @if(!empty($instagramProfile['profile_picture_url']))
                            <img src="{{ $instagramProfile['profile_picture_url'] }}"
                                 alt="Imagem do Instagram"
                                 class="rounded-circle"
                                 style="width:48px;height:48px;object-fit:cover;flex-shrink:0;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;background:linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888);flex-shrink:0;">
                                <i class="bi bi-instagram text-white fs-5"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-semibold">Instagram</div>
                            <div class="small text-muted">
                                @if($metaToken->hasInstagram())
                                    {{ !empty($instagramProfile['username']) ? '@'.$instagramProfile['username'] : ($metaToken->instagram_username ? '@'.$metaToken->instagram_username : 'Conta conectada') }}
                                @else
                                    Não vinculado à página
                                @endif
                            </div>
                            @if(!empty($instagramProfile['name']))
                                <div class="small text-muted">Nome: {{ $instagramProfile['name'] }}</div>
                            @endif
                        </div>
                        @if($metaToken->hasInstagram())
                            <span class="badge bg-success">Conectado</span>
                        @else
                            <span class="badge bg-warning text-dark">Não vinculado</span>
                        @endif
                    </div>

                    @if(! $metaToken->hasInstagram())
                        <div class="alert alert-warning small mb-4">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            O Instagram não foi detectado. Verifique se a Página do Facebook está vinculada a uma
                            conta de Instagram Profissional ou Criador.
                        </div>
                    @endif

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="border rounded-3 p-2 text-center">
                                <div class="small text-muted">Seguidores (IG)</div>
                                <div class="fw-bold">{{ $instagramProfile['followers_count'] ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-2 text-center">
                                <div class="small text-muted">Posts (IG)</div>
                                <div class="fw-bold">{{ $instagramProfile['media_count'] ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    @if(!empty($instagramProfile['biography']))
                        <div class="small text-muted mb-3">
                            <strong>Bio:</strong> {{ $instagramProfile['biography'] }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('meta.disconnect') }}"
                          onsubmit="return confirm('Deseja desconectar a conta Meta? As publicações agendadas poderão falhar.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-2"></i>Desconectar conta
                        </button>
                    </form>

                @else
                    <div class="text-center py-4">
                        <i class="bi bi-link-45deg display-3 text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-4">Nenhuma conta conectada. Conecte para começar a publicar.</p>
                        <a href="{{ route('meta.redirect') }}"
                           class="btn btn-primary btn-lg px-4">
                            <i class="bi bi-facebook me-2"></i>Conectar com Facebook/Instagram
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Card de estatísticas diárias --}}
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-bar-chart me-2 text-primary"></i>Posts Hoje
                </h5>
                <div class="row g-3 text-center">
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background:linear-gradient(45deg,#f09433,#bc1888);">
                            <div class="display-6 fw-bold text-white">{{ $dailyInstagram }}</div>
                            <div class="small text-white opacity-75 mt-1">Instagram</div>
                            <div class="small text-white opacity-50">limite: 50/dia</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3" style="background:#1877f2;">
                            <div class="display-6 fw-bold text-white">{{ $dailyFacebook }}</div>
                            <div class="small text-white opacity-75 mt-1">Facebook</div>
                            <div class="small text-white opacity-50">limite: 50/dia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Publicação manual via upload --}}
    @if($metaToken)
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-cloud-upload me-2 text-primary"></i>Publicar imagem manualmente
                </h5>
                <p class="text-muted small">
                    Selecione uma imagem. Ela será salva no storage público e enviada para as plataformas conectadas.
                </p>

                <form method="POST" action="{{ route('meta.social.manual-publish') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Imagem</label>
                            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                            <div class="form-text">Formatos: JPG, PNG, WEBP (máx. 10MB).</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Legenda</label>
                            <textarea name="caption" class="form-control" rows="4" maxlength="2200"
                                      placeholder="Legenda da publicação (opcional)"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block">Plataformas</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="platforms[]" id="platformInstagram" value="instagram" checked>
                                <label class="form-check-label" for="platformInstagram">Instagram</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="platforms[]" id="platformFacebook" value="facebook">
                                <label class="form-check-label" for="platformFacebook">Facebook</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Publicar agora
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Histórico recente --}}
    @if($recentPosts->isNotEmpty())
    <div class="col-12">
        <div class="card">
            <div class="card-body p-4">
                <h5 class="card-title mb-4">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Publicações Recentes
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produto Externo</th>
                                <th>Plataforma</th>
                                <th>Status</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPosts as $post)
                            <tr>
                                <td>
                                    {{ $post->external_product_id ?: '—' }}
                                    @if($post->source_domain)
                                        <div class="small text-muted">{{ $post->source_domain }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($post->platform === 'instagram')
                                        <span class="badge badge-platform-instagram">Instagram</span>
                                    @elseif($post->platform === 'facebook')
                                        <span class="badge badge-platform-facebook">Facebook</span>
                                    @else
                                        <span class="badge bg-secondary">Ambos</span>
                                    @endif
                                </td>
                                <td>
                                    @if($post->isPublished())
                                        <span class="badge bg-success">Publicado</span>
                                    @elseif($post->isFailed())
                                        <span class="badge bg-danger" title="{{ $post->error_message }}">Falhou</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Pendente</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $post->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
