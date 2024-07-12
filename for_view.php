<?php
function getAllMessages($pdo) {
    $stmt = $pdo->query("SELECT * FROM user_requests");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
