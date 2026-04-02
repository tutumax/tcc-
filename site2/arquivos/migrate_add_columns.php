<?php
/**
 * Migração simples para adicionar colunas necessárias à tabela `usuarios`.
 * Executa ALTER TABLE apenas se a coluna ainda não existir.
 * Uso: acesse via navegador ou CLI: php migrate_add_columns.php
 */

require_once __DIR__ . '/conexao.php';

$checks = [
    'role' => "ALTER TABLE usuarios ADD COLUMN role VARCHAR(20) DEFAULT 'aluno'",
    'aprovado' => "ALTER TABLE usuarios ADD COLUMN aprovado TINYINT(1) DEFAULT 1",
    'telefone' => "ALTER TABLE usuarios ADD COLUMN telefone VARCHAR(30) DEFAULT ''",
];

$results = [];

foreach ($checks as $col => $sql) {
    try {
        $res = $pdo->query("SHOW COLUMNS FROM usuarios LIKE '" . $col . "'")->fetch();
        if ($res) {
            $results[$col] = 'exists';
        } else {
            $pdo->exec($sql);
            $results[$col] = 'added';
        }
    } catch (Exception $e) {
        $results[$col] = 'error: ' . $e->getMessage();
    }
}

header('Content-Type: text/plain; charset=utf-8');
echo "Migration results:\n";
foreach ($results as $k => $v) {
    echo "- $k: $v\n";
}

echo "\nConcluído.\n";

?>
