

InnSystem
Plataforma de Gestão Multicliente

Manual Completo de Integração com a Meta API
Instagram & Facebook — Publicação de Produtos via OAuth

Versão	1.0 — 2025
Stack	Laravel + Meta Graph API
Domínio Base	social.innsystem.com.br

ID App: 1614984229637742
Key Secret: bfb454ccb0e796257568dc108d56aedd

Token de Acesso: EAAW80dFTTm4BQzws4Qp08MZA8Jzv2NZAZAIr3IrJZCuHEuYRNEHeZAUldtJofa9MjOsnBsN7ISjpoz72H0ZBbLiAXZBjzWd0EoSSZBNkGwO9MOE7CPTkzfXrvETRTI0UTrBhSpvskBZCvMOIEKJIsHIZARBrINXSZBmvPLahZAuo0P3LtzVErwhbDCkFHXwbIXs6bObU5ZAhbHXOcz2CaecUP

Id Instagram InnSystem: 17841403057516432
 
Índice
1. Visão Geral e Arquitetura
2. Pré-requisitos
3. Configuração do App na Meta (Facebook Developers)
4. Configuração do Subdomínio e Ambiente Laravel
5. Fluxo OAuth — Conexão da Conta do Cliente
6. Publicação de Produtos no Instagram
7. Publicação de Produtos no Facebook
8. Interface do Cliente (Frontend)
9. Revisão da App e Aprovação da Meta
10. Boas Práticas, Segurança e Observações Importantes
 
1. Visão Geral e Arquitetura
Este manual descreve como integrar a plataforma InnSystem com a API da Meta para permitir que seus clientes (ex.: lojas de alianças, boutiques, etc.) publiquem produtos diretamente no Instagram e Facebook a partir do painel da loja no seu sistema.

1.1. Como o Fluxo Funciona
O modelo adotado é o de uma plataforma SaaS multi-tenant com OAuth delegado. Isso significa que você (InnSystem) é o dono do App na Meta, e cada cliente autoriza o seu App a postar em nome dele, sem precisar criar um App próprio.

Fluxo Resumido
1. Você cria e configura um único App na Meta Developers
2. Cada cliente da InnSystem faz login no painel e clica em 'Conectar com Instagram/Facebook'
3. A Meta exibe a tela de permissões e o cliente autoriza
4. Você armazena o Access Token do cliente (por tenant)
5. O cliente seleciona um produto na loja e clica em 'Publicar no Instagram'
6. O sistema chama a API da Meta com o token do cliente e publica o post

1.2. Subdomínio Recomendado
Recomendamos criar o subdomínio social.innsystem.com.br para centralizar os callbacks OAuth e as rotas de integração com a Meta. Isso facilita a revisão do App pela Meta e isola o tráfego de API social do restante da plataforma.

Subdomínio sugerido
social.innsystem.com.br	Por que usar subdomínio?
Permite configurar a URL de callback na Meta de forma limpa, com SSL próprio e escopo isolado de rotas Laravel.
 
2. Pré-requisitos
2.1. Conta e App na Meta
•	Conta pessoal no Facebook (necessária para criar o App)
•	Acesso ao Meta for Developers: developers.facebook.com
•	Conta de negócios verificada no Meta Business Manager (necessário para publicação)
•	Perfil do Instagram deve ser do tipo Profissional ou Criador (não conta pessoal)

2.2. Ambiente Laravel
•	Laravel 10 ou superior
•	PHP 8.1+
•	Pacote: composer require league/oauth2-client (ou socialite)
•	Laravel Socialite com driver personalizado, ou implementação manual via OAuth 2.0
•	Banco de dados com campo para armazenar tokens por tenant
•	HTTPS obrigatório no subdomínio (certificado SSL válido)

2.3. Permissões da Meta Necessárias
Permissão	Plataforma	Finalidade
pages_manage_posts	Facebook	Criar posts na Página
pages_read_engagement	Facebook	Ler dados da Página
instagram_basic	Instagram	Leitura básica do perfil
instagram_content_publish	Instagram	Publicar fotos/vídeos
pages_show_list	Ambos	Listar Páginas do usuário
business_management	Meta	Acesso ao Business Manager
 
3. Configuração do App na Meta (Facebook Developers)
3.1. Criando o App

1	Acessar o portal de desenvolvedores
Acesse developers.facebook.com e faça login com sua conta Facebook pessoal (que deve ser administradora do Business Manager).

2	Criar novo App
Clique em 'Meus Apps' > 'Criar App'. Selecione o tipo 'Negócios' (Business). Isso permite acesso às permissões avançadas de publicação.

3	Preencher dados do App
Nome: 'InnSystem Social'. E-mail de contato: seu e-mail comercial. Business Account: selecione sua conta no Business Manager.

4	Adicionar produtos ao App
Na dashboard do App, clique em '+ Adicionar Produto'. Adicione: 'Facebook Login' e 'Instagram Graph API'.

3.2. Configurando o Facebook Login
Após adicionar o produto Facebook Login, acesse Configurações > Facebook Login:
•	Em 'URIs de redirecionamento OAuth válidos', adicione:
◦	https://social.innsystem.com.br/auth/meta/callback
•	Ative 'Login pelo cliente OAuth' e 'Login pelo Web OAuth'
•	Desative 'Aplicar HTTPS' somente durante desenvolvimento (nunca em produção)

3.3. Configurando as Permissões (Escopos)
Em 'Revisão do App' > 'Permissões e Recursos', solicite as permissões listadas na seção 2.3. Durante desenvolvimento, você pode testar com contas adicionadas como Testadores do App sem precisar de aprovação.

⚠️  Atenção — Modo de Desenvolvimento vs. Produção
Em modo de DESENVOLVIMENTO: apenas contas adicionadas como 'Testadores' ou 'Desenvolvedores' no App conseguem autorizar.
Em modo de PRODUÇÃO (Live): qualquer usuário pode autorizar, mas as permissões avançadas (instagram_content_publish, pages_manage_posts) precisam ser aprovadas pela Meta via processo de revisão.
Consulte a Seção 9 deste manual para o processo de aprovação.

3.4. Obtendo as Credenciais do App
Em 'Configurações' > 'Básico', copie:
•	App ID (também chamado de Client ID)
•	Chave Secreta do App (Client Secret) — nunca exponha no frontend

Dados para o arquivo .env do Laravel
META_APP_ID=seu_app_id_aqui
META_APP_SECRET=sua_chave_secreta_aqui
META_REDIRECT_URI=https://social.innsystem.com.br/auth/meta/callback
META_API_VERSION=v21.0
 
4. Configuração do Subdomínio e Ambiente Laravel
4.1. DNS e SSL
No painel do seu provedor de DNS (ex: Cloudflare, Registro.br), crie um registro CNAME:

Tipo	Nome	Valor
A ou CNAME	social	IP do servidor ou innsystem.com.br

Após o DNS propagar, gere o certificado SSL. Com Certbot (Let's Encrypt):
sudo certbot --nginx -d social.innsystem.com.br

4.2. Configuração do .env do Laravel
META_APP_ID=123456789
META_APP_SECRET=abcdef1234567890
META_REDIRECT_URI=https://social.innsystem.com.br/auth/meta/callback
META_API_VERSION=v21.0

4.3. Instalação de Dependências
composer require guzzlehttp/guzzle
# Opcional: use Socialite se preferir abstração
composer require laravel/socialite

4.4. Migration — Tabela de Tokens dos Clientes
Crie uma migration para armazenar os tokens OAuth por tenant:
php artisan make:migration create_meta_tokens_table

Conteúdo da migration:
Schema::create('meta_tokens', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained('tenants');
    $table->string('access_token', 1000);
    $table->string('user_id')->nullable();
    $table->string('page_id')->nullable();
    $table->string('page_name')->nullable();
    $table->string('instagram_account_id')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->timestamps();
});
 
5. Fluxo OAuth — Conexão da Conta do Cliente
5.1. Rotas Laravel
Em routes/web.php, defina as rotas protegidas por autenticação do tenant:
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/auth/meta/redirect', [MetaAuthController::class, 'redirect'])
           ->name('meta.redirect');
    Route::get('/auth/meta/callback', [MetaAuthController::class, 'callback'])
           ->name('meta.callback');
    Route::delete('/auth/meta/disconnect', [MetaAuthController::class, 'disconnect'])
           ->name('meta.disconnect');
});

5.2. Controller de Autenticação OAuth
php artisan make:controller MetaAuthController

Conteúdo do MetaAuthController.php:
<?php
namespace App\Http\Controllers;

use App\Models\MetaToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MetaAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $state = Str::random(40);
        session(['meta_oauth_state' => $state]);

        $params = http_build_query([
            'client_id'     => config('services.meta.app_id'),
            'redirect_uri'  => config('services.meta.redirect_uri'),
            'scope'         => implode(',', [
                'pages_manage_posts',
                'pages_read_engagement',
                'instagram_basic',
                'instagram_content_publish',
                'pages_show_list',
                'business_management',
            ]),
            'response_type' => 'code',
            'state'         => $state,
        ]);

        return redirect('https://www.facebook.com/dialog/oauth?' . $params);
    }

    public function callback(Request $request)
    {
        // Validar state para prevenir CSRF
        if ($request->state !== session('meta_oauth_state')) {
            abort(403, 'State inválido');
        }

        // Trocar code por access_token
        $response = Http::get('https://graph.facebook.com/oauth/access_token', [
            'client_id'     => config('services.meta.app_id'),
            'client_secret' => config('services.meta.app_secret'),
            'redirect_uri'  => config('services.meta.redirect_uri'),
            'code'          => $request->code,
        ]);

        $data = $response->json();
        $shortToken = $data['access_token'];

        // Trocar por Long-Lived Token (válido 60 dias)
        $llResponse = Http::get('https://graph.facebook.com/oauth/access_token', [
            'grant_type'        => 'fb_exchange_token',
            'client_id'         => config('services.meta.app_id'),
            'client_secret'     => config('services.meta.app_secret'),
            'fb_exchange_token' => $shortToken,
        ]);

        $llData = $llResponse->json();
        $longToken = $llData['access_token'];
        $expiresIn = $llData['expires_in'] ?? 5184000;

        // Buscar Páginas do usuário
        $pagesResp = Http::get('https://graph.facebook.com/me/accounts', [
            'access_token' => $longToken,
            'fields' => 'id,name,access_token,instagram_business_account',
        ]);

        $pages = $pagesResp->json()['data'] ?? [];
        session(['meta_pages' => $pages, 'meta_long_token' => $longToken]);

        // Redirecionar para tela de seleção de Página
        return redirect()->route('meta.select-page');
    }
}

5.3. Seleção de Página e Conta do Instagram
Após o OAuth, exiba as páginas disponíveis para o cliente escolher qual usar para publicar. Armazene o Page Access Token (não o User Token) para publicações no Facebook e o instagram_business_account.id para o Instagram.
// Salvar token da página selecionada
MetaToken::updateOrCreate(
    ['tenant_id' => auth()->user()->tenant_id],
    [
        'access_token'          => $selectedPage['access_token'],
        'page_id'               => $selectedPage['id'],
        'page_name'             => $selectedPage['name'],
        'instagram_account_id'  => $selectedPage['instagram_business_account']['id'] ?? null,
        'expires_at'            => now()->addSeconds($expiresIn),
    ]
);

💡  Sobre Page Access Tokens
O Page Access Token não expira (diferente do User Token que dura 60 dias).
Sempre use o Page Access Token para postar — nunca o User Token.
O token da página é retornado no campo 'access_token' de cada objeto em /me/accounts.
 
6. Publicação de Produtos no Instagram
6.1. Como Funciona a API do Instagram
A publicação no Instagram via API segue um processo de duas etapas obrigatórias:
1.	Criar um container de mídia (upload da imagem e texto)
2.	Publicar o container (tornar o post visível)

⚠️  Requisito: Imagem deve ser uma URL pública
A API do Instagram não aceita upload direto de arquivo (multipart).
A imagem do produto deve estar em uma URL pública acessível na internet (ex: https://social.innsystem.com.br/storage/produtos/imagem.jpg).
Certifique-se de que o storage do Laravel está configurado para acesso público.

6.2. Service de Publicação no Instagram
php artisan make:service MetaInstagramService

<?php
namespace App\Services;

use App\Models\MetaToken;
use Illuminate\Support\Facades\Http;

class MetaInstagramService
{
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiVersion = config('services.meta.api_version', 'v21.0');
        $this->baseUrl = 'https://graph.facebook.com/' . $this->apiVersion;
    }

    public function publishProduct(int $tenantId, array $product): array
    {
        $token = MetaToken::where('tenant_id', $tenantId)->firstOrFail();

        if (empty($token->instagram_account_id)) {
            return ['success' => false, 'error' => 'Instagram não conectado'];
        }

        // ETAPA 1: Criar container de mídia
        $containerId = $this->createMediaContainer(
            $token->instagram_account_id,
            $token->access_token,
            $product['image_url'],
            $product['caption']
        );

        if (!$containerId) {
            return ['success' => false, 'error' => 'Falha ao criar container'];
        }

        // ETAPA 2: Publicar container (aguardar processamento)
        sleep(3); // Aguardar processamento da imagem

        $postId = $this->publishContainer(
            $token->instagram_account_id,
            $token->access_token,
            $containerId
        );

        return [
            'success' => (bool) $postId,
            'post_id' => $postId,
        ];
    }

    private function createMediaContainer(string $igAccountId, string $token,
                                           string $imageUrl, string $caption): ?string
    {
        $response = Http::post(
            "{$this->baseUrl}/{$igAccountId}/media",
            [
                'image_url'    => $imageUrl,
                'caption'      => $caption,
                'access_token' => $token,
            ]
        );

        return $response->json()['id'] ?? null;
    }

    private function publishContainer(string $igAccountId, string $token,
                                       string $containerId): ?string
    {
        $response = Http::post(
            "{$this->baseUrl}/{$igAccountId}/media_publish",
            [
                'creation_id'  => $containerId,
                'access_token' => $token,
            ]
        );

        return $response->json()['id'] ?? null;
    }
}

6.3. Verificação do Status do Container
Para evitar erros de publicação, você pode verificar o status do container antes de publicar (recomendado para imagens grandes):
private function waitForContainer(string $containerId, string $token): bool
{
    for ($i = 0; $i < 10; $i++) {
        $resp = Http::get("{$this->baseUrl}/{$containerId}", [
            'fields' => 'status_code',
            'access_token' => $token,
        ]);
        $status = $resp->json()['status_code'] ?? '';
        if ($status === 'FINISHED') return true;
        if ($status === 'ERROR') return false;
        sleep(2);
    }
    return false;
}
 
7. Publicação de Produtos no Facebook
7.1. Publicando na Página do Facebook
Para o Facebook, a publicação é mais simples: um único endpoint aceita a imagem e o texto em uma só chamada.
<?php
namespace App\Services;

use App\Models\MetaToken;
use Illuminate\Support\Facades\Http;

class MetaFacebookService
{
    private string $baseUrl;

    public function __construct()
    {
        $version = config('services.meta.api_version', 'v21.0');
        $this->baseUrl = 'https://graph.facebook.com/' . $version;
    }

    public function publishProduct(int $tenantId, array $product): array
    {
        $token = MetaToken::where('tenant_id', $tenantId)->firstOrFail();

        if (empty($token->page_id)) {
            return ['success' => false, 'error' => 'Página não conectada'];
        }

        // Publicar foto com legenda na Página
        $response = Http::post(
            "{$this->baseUrl}/{$token->page_id}/photos",
            [
                'url'          => $product['image_url'],
                'message'      => $product['caption'],
                'access_token' => $token->access_token,
            ]
        );

        $result = $response->json();

        if (isset($result['id'])) {
            return ['success' => true, 'post_id' => $result['id']];
        }

        return [
            'success' => false,
            'error'   => $result['error']['message'] ?? 'Erro desconhecido',
        ];
    }
}

7.2. Controller de Publicação
php artisan make:controller ProductPublishController

public function publish(Request $request, Product $product)
{
    $request->validate([
        'caption'   => 'required|string|max:2200',
        'platforms' => 'required|array',
        'platforms.*' => 'in:instagram,facebook',
    ]);

    $tenantId = auth()->user()->tenant_id;
    $results = [];

    $productData = [
        'image_url' => $product->getPublicImageUrl(),
        'caption'   => $request->caption,
    ];

    if (in_array('instagram', $request->platforms)) {
        $results['instagram'] = (new MetaInstagramService)
            ->publishProduct($tenantId, $productData);
    }

    if (in_array('facebook', $request->platforms)) {
        $results['facebook'] = (new MetaFacebookService)
            ->publishProduct($tenantId, $productData);
    }

    return response()->json($results);
}
 
8. Interface do Cliente (Frontend)
8.1. Tela de Configurações — Conectar Redes Sociais
No painel do cliente, crie uma seção 'Redes Sociais' com os status de conexão:
<!-- Blade: resources/views/tenant/social/settings.blade.php -->
<div class="card">
  <h2>Conexão com Redes Sociais</h2>

  @if($metaToken)
    <div class="connected">
      <span>✅ Facebook: {{ $metaToken->page_name }}</span>
      @if($metaToken->instagram_account_id)
        <span>✅ Instagram: Conectado</span>
      @endif
      <form method="POST" action="{{ route('meta.disconnect') }}">
        @csrf @method('DELETE')
        <button type="submit">Desconectar</button>
      </form>
    </div>
  @else
    <a href="{{ route('meta.redirect') }}" class="btn-connect">
      Conectar com Facebook/Instagram
    </a>
  @endif
</div>

8.2. Modal de Publicação de Produto
<!-- Botão na listagem de produtos -->
<button onclick="openPublishModal({{ $product->id }}, '{{ $product->image_url }}')"
        class="btn btn-primary">
  📸 Publicar nas Redes Sociais
</button>

<!-- Modal -->
<div id="publish-modal" class="modal hidden">
  <form id="publish-form">
    <h3>Publicar Produto</h3>
    <img id="product-preview" src="" alt="Preview">
    <textarea name="caption" placeholder="Legenda do post (máx. 2200 chars)"
              maxlength="2200"></textarea>
    <div class="platforms">
      <label><input type="checkbox" name="platforms[]" value="instagram"> Instagram</label>
      <label><input type="checkbox" name="platforms[]" value="facebook"> Facebook</label>
    </div>
    <button type="submit">Publicar Agora</button>
  </form>
</div>

<script>
async function submitPublish(productId) {
    const form = document.getElementById('publish-form');
    const data = new FormData(form);
    const resp = await fetch(`/products/${productId}/publish`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: data
    });
    const result = await resp.json();
    showNotification(result);
}
</script>
 
9. Revisão do App e Aprovação da Meta
9.1. Por Que a Revisão é Necessária
Para que clientes reais (fora do seu time de desenvolvimento) possam usar a integração, o App precisa ser aprovado pela Meta para as permissões avançadas de publicação. Este processo é obrigatório e pode levar de 3 a 10 dias úteis.

9.2. Passo a Passo da Solicitação de Revisão

1	Completar o perfil do App
Em 'Configurações Básicas', preencha: Política de Privacidade URL, Termos de Serviço URL, Ícone do App (1024x1024px), Categoria do App, e Domínio do App (innsystem.com.br).

2	Preparar vídeo de demonstração
A Meta exige um vídeo mostrando o fluxo completo: login OAuth, seleção de permissões, e a publicação de um post. Use uma conta de teste. Hospede o vídeo no Google Drive ou YouTube (não listado).

3	Solicitação de permissões
Em 'Revisão do App' > 'Permissões e Recursos', clique em cada permissão necessária e em 'Solicitar Revisão Avançada'. Para cada permissão, informe: caso de uso, como será utilizada, link do vídeo.

4	Verificação de Negócios
A Meta pode solicitar verificação do negócio via Meta Business Manager. Prepare CNPJ, comprovante e documento da empresa.

5	Colocar o App em Live
Após aprovação de todas as permissões, mude o status do App de 'Desenvolvimento' para 'Live' na dashboard. Somente após isso clientes reais podem usar.

📋  Checklist para a Revisão da Meta
✅ Política de Privacidade acessível publicamente
✅ Termos de Serviço publicados
✅ Domínio verificado (social.innsystem.com.br com HTTPS)
✅ Vídeo demonstrativo do fluxo OAuth + publicação
✅ Ícone do App em alta resolução (1024x1024)
✅ Descrição clara do caso de uso de cada permissão
✅ App testado com contas de teste antes de submeter
 
10. Boas Práticas, Segurança e Observações
10.1. Segurança dos Tokens
•	Nunca armazene tokens em texto plano sem criptografia adicional. Considere usar o encrypt/decrypt do Laravel
•	Implemente rotação automática de tokens antes do vencimento (60 dias)
•	Registre todas as ações de publicação em tabela de logs com tenant_id, timestamp e resultado
•	Não exponha o App Secret no frontend nem em respostas de API

10.2. Rate Limits da Meta
A Meta possui limites de requisições. Para publicação de imagens no Instagram, o limite é de 50 posts por usuário por dia. Implemente controle no sistema:
// Verificar limite antes de publicar
$dailyCount = SocialPost::where('tenant_id', $tenantId)
    ->where('platform', 'instagram')
    ->whereDate('created_at', today())
    ->count();

if ($dailyCount >= 50) {
    return response()->json(['error' => 'Limite diário atingido'], 429);
}

10.3. Configuração do config/services.php
'meta' => [
    'app_id'      => env('META_APP_ID'),
    'app_secret'  => env('META_APP_SECRET'),
    'redirect_uri'=> env('META_REDIRECT_URI'),
    'api_version' => env('META_API_VERSION', 'v21.0'),
],

10.4. Webhook para Notificações (Opcional)
Você pode configurar um Webhook na Meta para receber notificações de eventos (ex: comentários no post). Crie um endpoint em:
Route::get('/meta/webhook', [MetaWebhookController::class, 'verify']);
Route::post('/meta/webhook', [MetaWebhookController::class, 'handle']);

10.5. Observações Finais Importantes

🔑  Pontos Críticos para o Sucesso da Integração
1. O perfil do Instagram do cliente DEVE ser Profissional/Criador (não pessoal).
2. O Instagram deve estar vinculado a uma Página do Facebook para usar a API.
3. Imagens precisam ser URLs públicas HTTPS. Storage local sem HTTPS não funciona.
4. O App precisa estar em modo LIVE para funcionar com clientes reais.
5. Salve SEMPRE o Page Access Token (não o User Token) para publicações.
6. A revisão da Meta pode ser rigorosa: documente bem o caso de uso.
7. Mantenha a versão da API atualizada (ex: v21.0). Versões antigas são descontinuadas.

Para suporte técnico e dúvidas sobre a Graph API, consulte a documentação oficial em developers.facebook.com/docs/instagram-api e developers.facebook.com/docs/pages-api.
