<?php
require_once 'conexao.php';

try {
    $result = $pdo->query("DESCRIBE links_emails");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($columns, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
