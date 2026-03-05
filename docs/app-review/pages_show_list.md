# App Review - pages_show_list

## Objetivo da permissão
A permissão `pages_show_list` é usada para listar as Páginas administradas pelo usuário no momento da conexão OAuth e permitir que ele escolha qual Página será vinculada ao tenant.

Ela é pré-requisito para operações de publicação e para permissões dependentes como `pages_read_engagement` e `instagram_content_publish`.

## Texto pronto (PT-BR) para colar na Meta
Usamos a permissão `pages_show_list` para exibir ao usuário a lista de Páginas do Facebook que ele administra, para que ele selecione explicitamente a Página que será conectada ao tenant no InnSystem Social.

Esse passo é essencial para nosso modelo multi-tenant, pois cada cliente deve escolher sua própria Página no fluxo de autorização, evitando vinculação incorreta entre contas.

Após a seleção, armazenamos os identificadores necessários para publicações futuras e validação de conexão.

Valor para o usuário:
- controle explícito da Página conectada;
- redução de erros operacionais;
- segurança e segregação correta entre tenants.

---

## Roteiro de gravação (cenário normal - 2 a 4 min)
1. Iniciar conexão OAuth (link assinado ou painel).
2. Mostrar retorno do OAuth e tela/lista de páginas.
3. Selecionar uma Página.
4. Confirmar conexão salva com nome da Página no status.

## Roteiro alternativo (quando OAuth bloqueado)
1. Mostrar tentativa de conexão e erro de escopo.
2. Mostrar no código/painel que existe etapa de seleção de página (`/connect/meta/select-page`).
3. Mostrar `connection-status` para demonstrar uso da página quando conexão existir em conta de teste.
4. Explicar que a listagem de páginas depende do OAuth ser liberado no review.

---

## Ligações de API para testar antes da submissão
- `GET /me/accounts?fields=id,name,access_token,instagram_business_account`
- fluxo interno de seleção de página (`/connect/meta/select-page` ou `/auth/meta/select-page`)

## Postman - configuração exata (apoio para vídeo)

### 1) Variáveis
- `base_url`: `https://social.innsystem.com.br`
- `api_key`: credencial do tenant
- `api_secret`: credencial do tenant

### 2) Gerar URL de conexão (simulação OpenCart)
- **Method:** `POST`
- **URL:** `{{base_url}}/api/v1/opencart/oauth-url`
- **Headers:**
  - `Content-Type: application/json`
  - `Accept: application/json`
  - `X-Innsystem-Key: {{api_key}}`
  - `X-Innsystem-Secret: {{api_secret}}`
- **Body (raw JSON):**
```json
{}
```

### 3) O que mostrar no vídeo
- Campo `oauth_connect_url` retornado.
- Abrir essa URL no navegador para iniciar OAuth.

---

## O que precisa existir no sistema para validar (sem OpenCart pronto)
Já está implementado no sistema atual.

Sem desenvolvimento adicional obrigatório para validação desta permissão.
