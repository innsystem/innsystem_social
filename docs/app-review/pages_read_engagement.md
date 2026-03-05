# App Review - pages_read_engagement

## Objetivo da permissão
A permissão `pages_read_engagement` é usada para validar dados da Página conectada (ex.: identificação e estado da conexão) e garantir que as publicações serão enviadas para o ativo correto do cliente.

No InnSystem Social, isso sustenta o fluxo de integração entre o módulo OpenCart (origem dos produtos) e a conta Meta vinculada ao tenant.

## Texto pronto (PT-BR) para colar na Meta
Usamos `pages_read_engagement` para consultar e validar informações da Página do Facebook conectada pelo cliente após o OAuth.

Nosso app (InnSystem Social) é um hub SaaS multi-tenant que recebe solicitações do OpenCart da loja cliente para conexão e publicação social.  
Essa permissão é necessária para:
- confirmar que a Página vinculada ao tenant é válida;
- validar status de conexão para evitar erros operacionais;
- garantir que o fluxo de publicação utilize o ativo correto.

Esse uso agrega valor ao usuário ao aumentar segurança, confiabilidade e rastreabilidade na operação de publicação em redes sociais.

Não vendemos dados pessoais, não coletamos senha e usamos somente o necessário para o funcionamento do recurso.

---

## Roteiro de gravação (cenário normal - 3 a 5 min)
1. Entrar no tenant no InnSystem Social.
2. Executar OAuth Meta.
3. Selecionar Página.
4. Mostrar tela com Página conectada.
5. Mostrar validação via endpoint interno `connection-status`.
6. Explicar que essa leitura evita publicação em página incorreta.

## Roteiro alternativo (quando OAuth ainda está bloqueado no review)
Se o OAuth não avançar por escopo pendente:
1. Mostrar tentativa real de OAuth e o bloqueio.
2. Mostrar no painel que o fluxo de integração depende da Página conectada.
3. No Postman, demonstrar o endpoint interno de status com credenciais do tenant.
4. Explicar no vídeo: "Fluxo completo será habilitado após aprovação das permissões; o backend já está preparado."

> Esse roteiro alternativo é útil enquanto a Meta ainda não liberou todos os escopos para contas fora de teste.

---

## Postman - configuração exata

### 1) Variáveis de ambiente (Environment)
- `base_url`: `https://social.innsystem.com.br`
- `api_key`: (gerada no painel admin do tenant)
- `api_secret`: (gerada no painel admin do tenant)

### 2) Request: connection-status
- **Method:** `GET`
- **URL:** `{{base_url}}/api/v1/opencart/connection-status`
- **Headers:**
  - `Accept: application/json`
  - `X-Innsystem-Key: {{api_key}}`
  - `X-Innsystem-Secret: {{api_secret}}`
- **Body:** nenhum

### 3) Resposta esperada (exemplo)
```json
{
  "tenant": { "id": 1, "name": "Loja Exemplo", "slug": "loja-exemplo" },
  "connected": true,
  "facebook_connected": true,
  "instagram_connected": true,
  "page_name": "Minha Pagina"
}
```

---

## O que precisa existir no sistema (sem OpenCart pronto)
- Tenant criado com `api_key` e `api_secret`.
- Fluxo OAuth implementado (já existe).
- Pode simular OpenCart totalmente pelo Postman (sem desenvolvimento extra obrigatório).
