<?php

require 'vendor/autoload.php';
require 'currency.php';
require 'db.php';
require 'db_function.php';

use GuzzleHttp\Client;

$token = "7179060631:AAFUUH-JIKeGVMXSKI5Wh8EO6X407wBjuXY";
$tgApi = "https://api.telegram.org/bot$token/";

$client = new Client(['base_uri' => $tgApi]);

$update = json_decode(file_get_contents('php://input'));

$pdo = require 'db.php';

if (isset($update) && isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text = $message->text;
    $from_id = $message->from->id;
    $from_username = $message->from->username;
    $created_at = date('Y-m-d H:i:s');

    $currency = new Currency();
    if (preg_match('/\/convert (\d+(\.\d{1,2})?) (\w{3})/', $text, $matches)) {
        $amount = (float)$matches[1];
        $currencyCode = strtoupper($matches[3]);
        $convertedAmount = $currency->exchange($amount, $currencyCode);
        $responseText = "$amount UZS -> $convertedAmount $currencyCode";

        saveMessage($pdo, $chat_id, $text, $amount, $currencyCode, $convertedAmount, $created_at);
    } else {
        $responseText = "Valyutani konvertatsiya qilish uchun /convert [miqdor] [valyuta kodi] buyruğini kiriting.";
    }

    $client->post('sendMessage', [
        'form_params' => [
            'chat_id' => $chat_id,
            'text' => $responseText
        ]
    ]);
} else {
    error_log("Update yoki xabar mavjud emas.");
}
?>
