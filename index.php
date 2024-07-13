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
function setUserState($chat_id, $state) {
    file_put_contents("user_states.json", json_encode([$chat_id => $state]), FILE_APPEND);
}

function getUserState($chat_id) {
    if (file_exists("user_states.json")) {
        $states = json_decode(file_get_contents("user_states.json"), true);
        return $states[$chat_id] ?? null;
    }
    return null;
}

if (isset($update)) {
    if (isset($update->message)) {
        $message = $update->message;
        $chat_id = $message->chat->id;
        $text = $message->text;

        if ($text === '/start') {
            $keyboard = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Convert',
                            'callback_data' => 'convert'
                        ]
                    ]
                ]
            ];

            $client->post('sendMessage', [
                'form_params' => [
                    'chat_id' => $chat_id,
                    'text' => "Valyutani konvertatsiya qilish uchun tugmani bosing.",
                    'reply_markup' => json_encode($keyboard)
                ]
            ]);
        }

    } elseif (isset($update->callback_query)) {
        $callbackQuery = $update->callback_query;
        $chat_id = $callbackQuery->message->chat->id;
        $data = $callbackQuery->data;

        if ($data === 'convert') {
            setUserState($chat_id, 'waiting_for_amount');

            $client->post('sendMessage', [
                'form_params' => [
                    'chat_id' => $chat_id,
                    'text' => "Iltimos, konvertatsiya qilish uchun miqdor va valyuta kodini kiriting (masalan, 100 UZS)."
                ]
            ]);
        }

    } elseif (isset($update->message) && isset($update->message->text)) {
        $message = $update->message;
        $chat_id = $message->chat->id;
        $text = $message->text;

        $userState = getUserState($chat_id);
        $currency = new Currency();

        if ($userState === 'waiting_for_amount') {
            if (preg_match('/^(\d+(\.\d{1,2})?)\s+(\w{3})$/', $text, $matches)) {
                $amount = (float)$matches[1];
                $currencyCode = strtoupper($matches[3]);

                $availableCurrencies = array_keys($currency->customCurrencies());
                if (!in_array($currencyCode, $availableCurrencies)) {
                    $client->post('sendMessage', [
                        'form_params' => [
                            'chat_id' => $chat_id,
                            'text' => "Kiritilgan valyuta kodi topilmadi. Iltimos, to'g'ri valyuta kodini kiriting."
                        ]
                    ]);
                    setUserState($chat_id, null);
                    return;
                }

                $client->post('sendMessage', [
                    'form_params' => [
                        'chat_id' => $chat_id,
                        'text' => "Siz konvertatsiya qilishni xohlaysizmi: $amount UZS -> $currencyCode? (Ha/Yo'q)"
                    ]
                ]);


                setUserState($chat_id, json_encode(['state' => 'confirming_conversion', 'amount' => $amount, 'currencyCode' => $currencyCode]));

            } else {
                $client->post('sendMessage', [
                    'form_params' => [
                        'chat_id' => $chat_id,
                        'text' => "Iltimos, to'g'ri formatda kiriting: [miqdor] [valyuta kodi] (masalan, 100 UZS)."
                    ]
                ]);
            }

            setUserState($chat_id, null);
        } elseif ($userState !== null && json_decode($userState, true)['state'] === 'confirming_conversion') {
            $data = json_decode($userState, true);
            $amount = $data['amount'];
            $currencyCode = $data['currencyCode'];

            if (stripos($text, 'ha') === 0) {
                $convertedAmount = $currency->exchange($amount, $currencyCode);

                $created_at = date('Y-m-d H:i:s');
                saveMessage($pdo, $chat_id, $text, $amount, $currencyCode, $convertedAmount, $created_at);

                $responseText = "$amount UZS -> $convertedAmount $currencyCode";
                $client->post('sendMessage', [
                    'form_params' => [
                        'chat_id' => $chat_id,
                        'text' => $responseText
                    ]
                ]);
            } else {
                $client->post('sendMessage', [
                    'form_params' => [
                        'chat_id' => $chat_id,
                        'text' => "Konvertatsiya bekor qilindi."
                    ]
                ]);
            }


            setUserState($chat_id, null);
        }
    }
} else {
    error_log("Update yoki xabar mavjud emas.");
}
?>
