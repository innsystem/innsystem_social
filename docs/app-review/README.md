# App Review Meta API - Índice de submissão

Este diretório contém um arquivo separado para cada permissão solicitada no App Review.

## Arquivos criados
- `pages_read_engagement.md`
- `instagram_content_publish.md`
- `instagram_basic.md`
- `pages_show_list.md`
- `business_management.md`
- `public_profile.md`

Cada arquivo contém:
1. Texto pronto (PT-BR) para colar na Meta.
2. Roteiro de gravação de vídeo.
3. Ligações de API para validar antes da submissão.
4. Observações sobre o que precisa existir no sistema.

---

## Ordem recomendada de gravação (1 vídeo único pode cobrir quase tudo)

1. Login no InnSystem Social (tenant de teste).
2. Conexão OAuth Meta (consentimento + callback).
3. Seleção de Página (`pages_show_list`).
4. Exibição de status da conexão (`pages_read_engagement` + `instagram_basic`).
5. Chamada simulada do OpenCart para publicação (`instagram_content_publish` e opcional Facebook).
6. Confirmação no histórico e, se possível, no canal social publicado.

> Dica: grave um vídeo de 5-8 minutos e use o mesmo link para várias permissões quando o fluxo for o mesmo.

---

## Como validar sem OpenCart pronto

Como o módulo OpenCart ainda não está finalizado, valide usando chamadas API simuladas (Postman/cURL):

- `POST /api/v1/opencart/oauth-url`
- `GET /api/v1/opencart/connection-status`
- `POST /api/v1/opencart/publish`

Com os headers:
- `X-Innsystem-Key: <api_key_tenant>`
- `X-Innsystem-Secret: <api_secret_tenant>`

Isso é suficiente para demonstrar uso real do backend de integração.

---

## Gap opcional para melhorar taxa de aprovação

Embora não seja obrigatório, pode ajudar criar uma tela no painel tenant chamada **"Publicação de teste"** que dispare internamente o endpoint de publish. Isso deixa a demonstração mais amigável para o revisor e reduz dependência de Postman no vídeo.
