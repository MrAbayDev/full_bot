<?php

function saveMessage($pdo, $chat_id, $message, $amount, $currency_code, $converted_amount, $created_at) {
    $stmt = $pdo->prepare("INSERT INTO user_requests (chat_id, message, amount, currency_code, converted_amount, created_at) VALUES (:chat_id, :message, :amount, :currency_code, :converted_amount, :created_at)");
    $stmt->execute([
        ':chat_id' => $chat_id,
        ':message' => $message,
        ':amount' => $amount,
        ':currency_code' => $currency_code,
        ':converted_amount' => $converted_amount,
        ':created_at' => $created_at
    ]);
}
?>