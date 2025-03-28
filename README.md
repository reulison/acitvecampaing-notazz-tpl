## Integração Entre ActiveCampaign, Notazz e TPL para Disparo Personalizado de E-mails na Petvi

Este artigo detalha a integração entre **[ActiveCampaign](https://www.activecampaign.com/ "ActiveCampaign")**, **[Notazz](https://notazz.com/ "Notazz")** e [TPL](https://www.tpl.com.br/ "TPL") para automatizar o envio de e-mails personalizados sobre a entrega de pedidos para os clientes da [Petvi](https://www.petvi.com.br/ "Petvi"). Essa solução combina conhecimentos de Marketing, Web Analytics e Desenvolvimento para otimizar a comunicação com os clientes.

### Objetivo

O principal objetivo dessa integração é coletar informações sobre o status da nota fiscal e logística do pedido, para que os e-mails de acompanhamento sejam disparados de forma personalizada pelo ActiveCampaign. 

### Tecnologias Utilizadas

- **PHP** para processamento dos dados e requisições às APIs.
- **GuzzleHttp** para comunicação com as APIs.
- **dotenv** para gerenciamento seguro de credenciais.

### Fluxo da IntegraçãoFluxo da Integração

#### 1. Disparo via Webhook do ActiveCampaign

A API é acionada por um Webhook configurado em uma automação no ActiveCampaign.

#### 2. Recebimento dos dados do pedido

O script recebe os dados de CPF, e-mail e data do pedido a partir de uma requisição.

### Implementação do Código

#### Protegendo Credenciais com Variáveis de Ambiente

Para evitar expor chaves de API no código, utilizamos a biblioteca dotenv. As credenciais são armazenadas no arquivo .env:

### 3. Consulta à API da Notazz

A API da Notazz é consultada para verificar se a Nota Fiscal Eletrônica (NF-e) foi autorizada.

#### 4. Obtenção de status logístico na TPL

Caso a NF-e esteja autorizada, a API da TPL é utilizada para recuperar dados sobre a entrega, como previsão de chegada e código de rastreamento.

#### 5. Atualização no ActiveCampaign

Os dados coletados são enviados para o ActiveCampaign, que dispara e-mails personalizados aos clientes.
