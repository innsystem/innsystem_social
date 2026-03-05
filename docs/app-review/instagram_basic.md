# App Review - instagram_basic

## Objetivo da permissão
A permissão `instagram_basic` é utilizada para identificar e validar a conta do Instagram Business vinculada à Página selecionada durante o fluxo de conexão.

Ela é dependência direta para `instagram_content_publish` e necessária para garantir que a publicação será feita no perfil correto do cliente.

## Texto pronto (PT-BR) para colar na Meta
Usamos a permissão `instagram_basic` para obter e validar a identificação da conta Instagram Business conectada ao tenant durante o OAuth da Meta.

No InnSystem Social, o cliente autoriza o app, seleciona a Página e o sistema armazena a referência da conta Instagram vinculada para uso nas publicações.

A permissão é necessária para:
- confirmar que o tenant possui Instagram Business válido;
- associar corretamente o ativo Instagram ao tenant;
- permitir o fluxo completo de publicação com `instagram_content_publish`.

Esse uso agrega valor ao usuário porque evita erros de publicação em conta incorreta e garante consistência operacional da integração social da loja.

Não usamos essa permissão para acesso indevido a dados não relacionados ao fluxo de integração.

---

## Roteiro de gravação (cenário normal - 3 a 4 min)
1. Mostrar início da conexão OAuth.
2. Mostrar seleção da Página.
3. Mostrar que o sistema detecta `instagram_account_id` vinculado.
4. Mostrar status de conexão do Instagram no painel.
5. Explicar que essa validação é pré-requisito para publicar produtos.

## Roteiro alternativo (quando OAuth bloqueado)
1. Mostrar tentativa real de OAuth e erro de escopo.
2. Mostrar endpoint de status interno retornando campos de conexão (quando já houver conexão de conta de teste).
3. Explicar no vídeo que a validação de `instagram_account_id` já está implementada no backend e será executada integralmente após aprovação dos escopos.

---

## Ligações de API para testar antes da submissão
- `GET /me/accounts?fields=id,name,instagram_business_account`
- (opcional) `GET /{ig-user-id}?fields=id,username`

## Postman - configuração exata

### 1) Variáveis
- `base_url`: `https://social.innsystem.com.br`
- `api_key`: credencial do tenant
- `api_secret`: credencial do tenant

### 2) Request de verificação interna
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/opencart/connection-status`
- **Headers:**
  - `Accept: application/json`
  - `X-Innsystem-Key: {{api_key}}`
  - `X-Innsystem-Secret: {{api_secret}}`
- **Body:** nenhum

### 3) O que mostrar no retorno
- `instagram_connected: true` (quando conectado)
- `connected: true`
- `page_name`

---

## O que precisa existir no sistema para validar (sem OpenCart pronto)
Sem necessidade de OpenCart para esta validação.

Opcional para fortalecer review: exibir `instagram_account_id` e (se disponível) `instagram_username` no painel de status.
