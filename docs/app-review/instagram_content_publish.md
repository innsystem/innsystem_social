# App Review - instagram_content_publish

## Objetivo da permissão
A permissão `instagram_content_publish` é usada para publicar imagens de produtos no Instagram Business do cliente, em nome do tenant que autorizou o app via OAuth.

A origem dos dados do produto é externa (OpenCart do cliente). O InnSystem Social atua como hub API para receber payload do produto e publicar no Instagram.

## Texto pronto (PT-BR) para colar na Meta
Usamos a permissão `instagram_content_publish` para publicar conteúdo (imagem + legenda) no Instagram Business da empresa cliente após autorização explícita via OAuth.

Fluxo de uso:
1. O cliente conecta sua conta Meta no InnSystem Social.
2. O cliente seleciona a Página/conta associada.
3. O módulo OpenCart envia os dados do produto (imagem pública, título e legenda) para nossa API.
4. Nosso backend cria o container de mídia no Instagram Graph API e publica o conteúdo.

Essa permissão é essencial para a funcionalidade principal do produto: permitir que lojistas publiquem produtos da loja virtual nas redes sociais sem sair do fluxo operacional.

Valor para o usuário:
- reduz trabalho manual de postagem;
- acelera divulgação de produtos;
- centraliza integração social por tenant, com rastreabilidade de sucesso/falha.

Não coletamos senha de Instagram/Facebook. O acesso é exclusivamente via OAuth oficial da Meta.

---

## Roteiro de gravação (cenário normal - 4 a 6 min)
1. Mostrar tenant conectado no InnSystem Social.
2. Mostrar status de conexão com Instagram ativo.
3. Mostrar requisição simulada do OpenCart para `POST /api/v1/opencart/publish` com `platforms=[instagram]`.
4. Mostrar resposta de sucesso (`post_id`) no retorno da API.
5. Mostrar no Instagram Business que o post foi publicado.
6. Mostrar histórico no sistema (`/posts/history`) com status `Publicado`.

## Roteiro alternativo (quando OAuth ainda está bloqueado)
1. Mostrar tentativa de OAuth e bloqueio por escopo.
2. Explicar que o backend de publicação já está implementado.
3. Mostrar no Postman a chamada `publish` com payload completo.
4. Mostrar resposta de sucesso/erro tratado.
5. Se não houver conta conectada, mostrar mensagem de erro de conexão e explicar que a publicação real depende da aprovação do OAuth.

---

## Ligações de API para testar antes da submissão
- `POST /{ig-user-id}/media`
- `POST /{ig-user-id}/media_publish`
- endpoint interno: `POST /api/v1/opencart/publish`

## Postman - configuração exata

### 1) Variáveis de ambiente
- `base_url`: `https://social.innsystem.com.br`
- `api_key`: credencial do tenant
- `api_secret`: credencial do tenant

### 2) Request: publish
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/opencart/publish`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `X-Innsystem-Key: {{api_key}}`
  - `X-Innsystem-Secret: {{api_secret}}`
- **Body (raw JSON):**
```json
{
  "external_product_id": "SKU-12345",
  "source_domain": "aliancasmoedasbr.com.br",
  "title": "Aliança Ouro 18k",
  "caption": "Coleção nova disponível. Consulte condições no link.",
  "image_url": "https://aliancasmoedasbr.com.br/image/catalog/produtos/alianca-ouro.jpg",
  "product_url": "https://aliancasmoedasbr.com.br/produto/alianca-ouro-18k",
  "platforms": ["instagram"]
}
```

### 3) Resposta esperada (exemplo)
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

## O que precisa existir no sistema para validar (sem OpenCart pronto)
Como o OpenCart ainda não está pronto, use simulação por Postman/cURL da chamada `publish`.

Recomendação (opcional, melhora aprovação): criar uma tela interna de "Publicação de teste" no painel tenant para reduzir fricção na demonstração do reviewer.
