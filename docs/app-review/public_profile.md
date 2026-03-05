# App Review - public_profile

## Objetivo da permissão
A permissão `public_profile` é usada como permissão básica do login para identificar o usuário autenticado no fluxo OAuth.

No InnSystem Social, ela auxilia na validação de sessão e integridade do processo de autorização.

## Texto pronto (PT-BR) para colar na Meta
Usamos `public_profile` como permissão base do fluxo de autenticação OAuth da Meta para identificar o usuário autenticado durante a conexão da conta ao tenant.

Essa permissão é necessária para o funcionamento padrão do login e para validações de segurança no processo de autorização.

Não usamos `public_profile` para perfilamento indevido nem para finalidades não relacionadas ao fluxo de autenticação e conexão da integração social.

---

## Roteiro de gravação (cenário normal)
Normalmente a Meta não exige screencast detalhado apenas para `public_profile` quando ela está como permissão base.

Se o campo de vídeo aparecer, reutilize o trecho inicial do vídeo de OAuth mostrando:
1. usuário iniciando conexão;
2. consentimento;
3. retorno ao sistema com conexão concluída.

## Roteiro alternativo (quando OAuth bloqueado)
1. Mostrar tentativa de OAuth e retorno de erro de escopo.
2. Explicar que `public_profile` é permissão base do fluxo.
3. Demonstrar endpoint interno com autenticação de tenant para comprovar backend ativo.

---

## Ligações de API para testar antes da submissão
- `GET /me?fields=id,name` no contexto do token obtido no OAuth.

## Postman - configuração exata (apoio)

### Variáveis
- `base_url`: `https://social.innsystem.com.br`
- `api_key`: credencial do tenant
- `api_secret`: credencial do tenant

### Request de apoio para vídeo
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/opencart/connection-status`
- **Headers:**
  - `Accept: application/json`
  - `X-Innsystem-Key: {{api_key}}`
  - `X-Innsystem-Secret: {{api_secret}}`
- **Body:** nenhum

---

## O que precisa existir no sistema para validar (sem OpenCart pronto)
Sem dependência de OpenCart.

Permissão já coberta no fluxo de login/conexão existente.
