<?php
$pdo = include 'db.php';
include 'for_view.php';

$messages = getAllMessages($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Requests</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
<h1>User Requests</h1>
<table>
    <thead>
    <tr>
        <th>Chat ID</th>
        <th>Message</th>
        <th>Amount</th>
        <th>Currency Code</th>
        <th>Converted Amount</th>
        <th>Created At</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $message): ?>
        <tr>
            <td><?= htmlspecialchars($message['chat_id']); ?></td>
            <td><?= htmlspecialchars($message['message']); ?></td>
            <td><?= htmlspecialchars($message['amount']); ?></td>
            <td><?= htmlspecialchars($message['currency_code']); ?></td>
            <td><?= htmlspecialchars($message['converted_amount']); ?></td>
            <td><?= htmlspecialchars($message['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
