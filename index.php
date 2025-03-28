<?php

require_once 'vendor/autoload.php';
require 'notazz-php.php';

use GuzzleHttp\Client;

dotenv\Dotenv::createImmutable(__DIR__)->load();

$cpf = $_REQUEST['contact']['fields']['cpf'] ?? '';
$email = $_REQUEST['contact']['email'] ?? '';
$dataOriginal = $_REQUEST['contact']['fields']['data_ordem_pedido'] ?? '';

// Validar entradas
if (empty($cpf) || empty($email) || empty($dataOriginal)) {
    die('Erro: Dados insuficientes para processar a solicitação.');
}

// Calcular datas
$dataFormatadaInicial = date('Y-m-d H:i:s', strtotime($dataOriginal . ' -2 days'));
$dataFormatadaFinal = date('Y-m-d H:i:s', strtotime($dataOriginal . ' +2 hours'));

// Consultar NF-e
$fields = json_encode([
    "API_KEY" => $_ENV['NOTAZZ_API_KEY'],
    "METHOD" => "consult_all_nfe_55",
    "FILTER" => [
        "INITIAL_DATE" => $dataFormatadaInicial, 
        "FINAL_DATE" => $dataFormatadaFinal,                  
        "DOC" => $cpf                                
     ]
]);

$retorno = send_data($fields);
$statusNota = $retorno[1]['statusNota'] ?? '';
if ($statusNota !== "Autorizada") {
    die('Erro: Nota fiscal não autorizada.');
}

// Capturar dados logísticos
$statusLogistica = $retorno[1]['statusLogistica'] ?? '';
$rastreio = $retorno[1]['rastreio'] ?? '';
$rastreio_externo = $retorno[1]['rastreio_externo'] ?? '';
$rastro_array = $retorno[1]['rastro'] ?? [];
$rastro_last_update = end($rastro_array) ?? [];
$rastro_data = $rastro_last_update['data'] ?? '';
$rastro_descricao = $rastro_last_update['descricao'] ?? '';
$id_logistica = $retorno[1]['id_logistica'] ?? '';

$client = new Client();

// Autenticação TPL
$post_data = [
    'apikey' => $_ENV['TPL_API_KEY'],
    'token' => $_ENV['TPL_TOKEN'],
    'email' => $_ENV['TPL_EMAIL']
];

$response = $client->post('https://oms.tpl.com.br/api/get/auth', [
    'json' => $post_data,
    'headers' => ['accept' => 'application/json', 'content-type' => 'application/json']
]);
$token = json_decode($response->getBody(), true)['token'] ?? '';

// Consultar pedido na TPL
$post_order = ['auth' => $token, 'order' => ['number' => $id_logistica]];
$order = $client->post('https://oms.tpl.com.br/api/get/orderdetail', [
    'json' => $post_order,
    'headers' => ['accept' => 'application/json', 'content-type' => 'application/json']
]);
$data_order = json_decode($order->getBody(), true);

$previsao_entrega = $data_order['order']['info']['prediction'] ?? '';
$empresa_entrega = $data_order['order']['shippment']['nick'] ?? '';
$trackercode = $data_order['order']['shippment']['tracker'] ?? '';

// Atualizar ActiveCampaign
$post_data_active = [
    'contact' => [
        'email' => $email,
        'fieldValues' => [
            ['field' => '163', 'value' => $previsao_entrega],
            ['field' => '164', 'value' => $empresa_entrega],
            ['field' => '166', 'value' => $trackercode],
            ['field' => '167', 'value' => $rastreio],
            ['field' => '168', 'value' => $rastro_data],
            ['field' => '169', 'value' => $rastro_descricao],
            ['field' => '170', 'value' => $rastreio_externo],
            ['field' => '171', 'value' => $statusLogistica],
            ['field' => '172', 'value' => $statusNota],
        ]
    ]
];

$response = $client->post('https://petlabs.api-us1.com/api/3/contact/sync', [
    'json' => $post_data_active,
    'headers' => ['Api-Token' => $_ENV['ACTIVE_CAMPAIGN_TOKEN'], 'accept' => 'application/json', 'content-type' => 'application/json']
]);

echo 'Active: ' . $response->getBody();
?>
