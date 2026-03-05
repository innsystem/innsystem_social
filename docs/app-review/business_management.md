# App Review - business_management

## Objetivo da permissão
A permissão `business_management` é solicitada para suportar cenários de ativos empresariais vinculados ao Business Manager durante o fluxo de autorização e operação de contas comerciais.

No InnSystem Social, ela é usada em conjunto com as demais permissões para garantir que o cliente empresarial consiga conectar e operar corretamente seus ativos de Página/Instagram usados na publicação.

## Texto pronto (PT-BR) para colar na Meta
Usamos a permissão `business_management` para permitir que contas empresariais conectem e gerenciem corretamente seus ativos da Meta no contexto de integração social do InnSystem Social.

Nosso aplicativo atende clientes de e-commerce com contas comerciais e precisa garantir que os ativos vinculados (Página/Instagram profissional) estejam acessíveis no fluxo de conexão e publicação.

A permissão é utilizada somente para viabilizar e manter a integração empresarial entre o tenant e os ativos autorizados no ecossistema da Meta.

Valor para o usuário:
- compatibilidade com estrutura de contas empresariais reais;
- redução de falhas de autorização em ambientes com Business Manager;
- operação estável para publicação social de produtos.

Não usamos essa permissão para finalidades fora do escopo da integração social autorizada pelo cliente.

---

## Roteiro de gravação (cenário normal - 3 a 5 min)
1. Mostrar tenant empresarial iniciando OAuth.
2. Mostrar concessão de permissões no fluxo Meta.
3. Mostrar seleção de Página e conclusão da conexão.
4. Mostrar publicação de teste (Instagram/Facebook) concluída.
5. Explicar que a permissão é necessária para operar ativos comerciais no contexto do cliente.

## Roteiro alternativo (quando OAuth bloqueado)
1. Mostrar tentativa de OAuth e bloqueio por escopo em análise.
2. Mostrar tenant empresarial no admin (domínio, segregação, credenciais).
3. Mostrar geração de `oauth-url` e validação `connection-status` via Postman.
4. Mostrar endpoint `publish` com payload externo (simulação OpenCart).
5. Explicar que fluxo completo com ativos empresariais é executado após aprovação dos escopos.

---

## Ligações de API para testar antes da submissão
- fluxo OAuth completo com escopos empresariais
- `GET /me/accounts` para retorno dos ativos utilizados na integração

## Postman - configuração exata

### Variáveis
- `base_url`: `https://social.innsystem.com.br`
- `api_key`: credencial do tenant
- `api_secret`: credencial do tenant

### Request 1 - oauth-url
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/opencart/oauth-url`
- **Headers:** `Content-Type`, `Accept`, `X-Innsystem-Key`, `X-Innsystem-Secret`
- **Body raw JSON:**
```json
{}
```

### Request 2 - connection-status
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/opencart/connection-status`
- **Headers:** `Accept`, `X-Innsystem-Key`, `X-Innsystem-Secret`
- **Body:** nenhum

### Request 3 - publish
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/opencart/publish`
- **Headers:** `Content-Type`, `Accept`, `X-Innsystem-Key`, `X-Innsystem-Secret`
- **Body raw JSON (exemplo):**
```json
{
  "external_product_id": "SKU-BM-001",
  "source_domain": "aliancasmoedasbr.com.br",
  "title": "Produto de teste Business Manager",
  "caption": "Publicação de teste para validação de integração empresarial.",
  "image_url": "https://aliancasmoedasbr.com.br/image/catalog/produtos/teste-bm.jpg",
  "product_url": "https://aliancasmoedasbr.com.br/produto/teste-bm",
  "platforms": ["instagram", "facebook"]
}
```

---

## O que precisa existir no sistema para validar (sem OpenCart pronto)
O OpenCart não é obrigatório para provar esta permissão.

Basta usar tenant empresarial de teste + chamadas simuladas via Postman.
