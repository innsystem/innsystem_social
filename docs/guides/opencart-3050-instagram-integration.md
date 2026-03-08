# Guia Completo - Integracao InnSystem Social + OpenCart 3.0.5.0 (Instagram)

Este guia define como operar e integrar o seu sistema `social.innsystem.com.br` com lojas OpenCart 3.0.5.0 para publicar produtos no Instagram.

## 1) Objetivo da integracao

Permitir que cada lojista:
- conecte sua conta Instagram (via OAuth Meta);
- publique produtos da loja OpenCart no Instagram;
- use legenda com nome do produto + preco + link do produto.

O OpenCart sera a origem dos produtos. O InnSystem Social sera o hub de autenticacao e publicacao.

---

## 2) Arquitetura resumida

### Componentes
- **InnSystem Social (hub)**: gerencia tenants, OAuth Meta e publicacao Instagram.
- **Modulo OpenCart (cliente)**: envia dados do produto para API do hub.
- **Meta Graph API**: autentica e publica no Instagram Business.

### Fluxo alto nivel
1. Admin InnSystem cria tenant e gera credenciais API.
2. Lojista configura modulo OpenCart com credenciais do tenant.
3. Modulo OpenCart chama `oauth-url` para gerar URL de conexao.
4. Lojista conecta Instagram na Meta e conclui autorizacao.
5. Modulo OpenCart envia produto para `publish`.
6. Hub publica no Instagram e retorna status.

---

## 3) Preparacao no InnSystem Social

### 3.1 Criar tenant
No painel admin do InnSystem:
1. Acesse `Admin > Tenants`.
2. Crie o tenant da loja.
3. Gere credenciais de API.

### 3.2 Credenciais para o modulo OpenCart
Entregar para o cliente (ou instalar no modulo):
- `API Key` (X-Innsystem-Key)
- `API Secret` (X-Innsystem-Secret)
- `Base URL` do hub (ex.: `https://social.innsystem.com.br`)

### 3.3 Verificar configuracao Meta no hub
- Redirect URI:
  - `https://social.innsystem.com.br/auth/meta/callback`
  - `https://social.innsystem.com.br/connect/meta/callback`
- Permissoes focadas:
  - `instagram_basic`
  - `instagram_content_publish`
  - `pages_show_list`
  - `pages_read_engagement`
  - `business_management`

---

## 4) Endpoints da API do hub (OpenCart)

Todos exigem headers:
- `X-Innsystem-Key: <api_key>`
- `X-Innsystem-Secret: <api_secret>`
- `Accept: application/json`

## 4.1 Status de conexao
- **GET** `/api/v1/opencart/connection-status`

Resposta esperada:
```json
{
  "tenant": { "id": 1, "name": "Loja Exemplo", "slug": "loja-exemplo" },
  "connected": true,
  "instagram_connected": true,
  "instagram_username": "lojaexemplo"
}
```

## 4.2 Gerar URL de conexao OAuth
- **POST** `/api/v1/opencart/oauth-url`
- Body raw JSON:
```json
{}
```

Resposta:
```json
{
  "oauth_connect_url": "https://social.innsystem.com.br/connect/meta/...",
  "expires_at": "2026-03-08T14:00:00+00:00"
}
```

## 4.3 Publicar produto no Instagram
- **POST** `/api/v1/opencart/publish`
- Body raw JSON:
```json
{
  "external_product_id": "12345",
  "source_domain": "aliancasmoedasbr.com.br",
  "title": "Alianca Ouro 18k",
  "price": "R$ 1.299,00",
  "caption": "Colecao nova disponivel.",
  "image_url": "https://aliancasmoedasbr.com.br/image/catalog/produtos/alianca-ouro.jpg",
  "product_url": "https://aliancasmoedasbr.com.br/produto/alianca-ouro-18k",
  "platforms": ["instagram"]
}
```

Resposta de sucesso:
```json
{
  "success": true,
  "tenant_id": 1,
  "results": {
    "instagram": {
      "success": true,
      "post_id": "17900000000000000"
    }
  }
}
```

---

## 5) Como o modulo OpenCart 3.0.5.0 deve funcionar

## 5.1 Estrutura recomendada (padrao OpenCart)
- `admin/controller/extension/module/innsystem_social.php`
- `admin/model/setting/innsystem_social.php`
- `admin/view/template/extension/module/innsystem_social.twig`
- `admin/language/pt-br/extension/module/innsystem_social.php`
- (Opcional) `admin/controller/catalog/product.php` override via OCMOD para adicionar botao de publicar
- arquivo OCMOD em `install.xml` para inserir botao na listagem de produtos.

## 5.2 Tela de configuracao do modulo (admin)
Campos minimos:
- Base URL do InnSystem
- API Key
- API Secret
- Status da conexao Instagram (somente leitura)
- Botao "Conectar Instagram"
- Botao "Testar conexao"

Acoes:
1. "Testar conexao" chama `connection-status`.
2. "Conectar Instagram" chama `oauth-url` e abre `oauth_connect_url` em nova aba/popup.
3. Ao finalizar OAuth, admin retorna e atualiza status.

## 5.3 Botao na listagem de produtos
Adicionar botao por linha:
- "Publicar no Instagram"

Ao clicar:
1. montar payload com nome, preco, link e imagem principal;
2. chamar endpoint `/publish`;
3. mostrar resultado (sucesso/erro) em alerta no admin.

## 5.4 Legenda recomendada no modulo
Padrao:
```text
{NOME_PRODUTO}
Preco: {PRECO}

{URL_PRODUTO}
```

---

## 6) Requisitos tecnicos criticos

1. Imagem do produto precisa ter URL publica HTTPS.
2. Conta Instagram deve ser Business/Criador.
3. Instagram deve estar vinculado corretamente no fluxo da Meta.
4. Credenciais API devem ficar salvas apenas no backend do OpenCart.
5. Implementar timeout/retry no modulo para chamadas HTTP.

---

## 7) Seguranca recomendada

- Nunca expor `API Secret` no frontend da loja.
- Requests para o hub sempre por servidor (backend-to-backend).
- Adicionar log local no OpenCart para rastrear falhas.
- Se possivel, whitelist de IP do servidor OpenCart no hub (futuro).

---

## 8) Checklist de implantacao

## InnSystem (hub)
- [ ] Tenant criado
- [ ] Credenciais API geradas
- [ ] Conta Instagram conectada
- [ ] Teste manual de publicacao funcionando

## OpenCart (cliente)
- [ ] Modulo instalado e configurado
- [ ] Credenciais API preenchidas
- [ ] Botao "Conectar Instagram" funcionando
- [ ] Botao "Publicar no Instagram" na listagem de produtos
- [ ] Retorno de status exibido no admin

---

## 9) O que ja esta pronto no projeto atual

- Conexao OAuth com selecao de conta.
- Status de conexao por tenant.
- Publicacao via endpoint API (`publish`) com foco Instagram.
- Upload manual no painel tenant com conversao automatica para JPG.
- Historico de publicacoes.

---

## 10) Proximos aprimoramentos recomendados

1. Job assíncrono (fila) para publicacoes em lote.
2. Regras de template de legenda por categoria/produto.
3. Relatorio de desempenho de publicacoes no painel do tenant.
4. Callback de retorno para OpenCart apos OAuth (UX melhor).
