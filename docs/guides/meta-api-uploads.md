Carregar arquivos
A API de Carregamento Retomável permite que você carregue grandes arquivos no gráfico social da Meta e retome as sessões de carregamento que foram interrompidas sem precisar reiniciar o processo. Depois de carregar um arquivo, será possível publicá-lo.

As referências de pontos de extremidade que aceitam identificadores de arquivo carregado indicarão se os identificadores retornados pela API de Carregamento Retomável são compatíveis.

Antes de começar
Para acompanhar este guia, presumimos que você já tenha lido os documentos Visão geral da Graph API do Facebook da Meta e Desenvolvimento de apps da Meta e executado as ações necessárias para desenvolver com a Meta.

Você precisará do seguinte:

O ID de um app da Meta
Um arquivo em um dos formatos a seguir:
pdf
jpeg
jpg
png
mp4
Um token de acesso de usuário
Etapa 1: iniciar uma sessão de carregamento
Para iniciar uma sessão de carregamento, envie uma solicitação POST ao ponto de extremidade /<APP_ID>/uploads, em que <APP_ID> é o ID do seu app da Meta, com os seguintes parâmetros obrigatórios:

file_name: o nome do arquivo.
file_length: o tamanho do arquivo em bytes.
file_type: o tipo MIME do arquivo. Valores aceitos: application/pdf, image/jpeg, image/jpg, image/png e video/mp4.
Sintaxe da solicitação
Texto formatado para facilitar a leitura.

curl -i -X POST "https://graph.facebook.com/v25.0/<APP_ID>/uploads
  ?file_name=<FILE_NAME>
  &file_length=<FILE_LENGTH>
  &file_type=<FILE_TYPE>
  &access_token=<USER_ACCESS_TOKEN>"
Em caso de sucesso, o app receberá uma resposta JSON com o ID da sessão de carregamento.

{
  "id": "upload:<UPLOAD_SESSION_ID>"
}
Etapa 2: iniciar o carregamento
Para iniciar o carregamento do arquivo, envie uma solicitação POST ao ponto de extremidade /upload:<UPLOAD_SESSION_ID> com file_offset definido como 0.

Sintaxe da solicitação
curl -i -X POST "https://graph.facebook.com/v25.0/upload:<UPLOAD_SESSION_ID>" --header "Authorization: OAuth <USER_ACCESS_TOKEN>" --header "file_offset: 0" --data-binary @<FILE_NAME>
Inclua o token de acesso no cabeçalho para evitar uma falha na chamada.

Em caso de sucesso, o app receberá o identificador do arquivo, que poderá ser usado nas chamadas de API para publicar o arquivo no seu ponto de extremidade.

{
  "h": "<UPLOADED_FILE_HANDLE>"
}
Exemplo de resposta
{
    "h": "2:c2FtcGxl..."
}
Retomar um carregamento interrompido
Caso a sessão de carregamento esteja demorando mais que o esperado ou tenha sido interrompida, envie uma solicitação GET ao ponto de extremidade /upload:<UPLOAD_SESSION_ID> da etapa 1.

curl -i -X GET "https://graph.facebook.com/v25.0/upload:<UPLOAD_SESSION_ID>" --header "Authorization: OAuth <USER_ACCESS_TOKEN>"
Se a solicitação for bem-sucedida, o app receberá uma resposta JSON com o valor de file_offset, que poderá ser usado para retomar o processo de carregamento do ponto em que você parou.

{
  "id": "upload:<UPLOAD_SESSION_ID>"
  "file_offset": "<FILE_OFFSET>"
}
Envie outra solicitação POST, como a que foi enviada na etapa 2, com file_offset definido como o valor file_offset que você acabou de receber. Com isso, o processo de carregamento será retomado do ponto em que parou.

curl -i -X POST "https://graph.facebook.com/v25.0/upload:<UPLOAD_SESSION_ID>" --header "Authorization: OAuth <USER_ACCESS_TOKEN>" --header "file_offset: <FILE_OFFSET>" --data-binary @<FILE_NAME>