<?php
// Configurações do banco de dados
$host = 'localhost';
$db   = 'db_tcc'; // Nome que você criou no passo anterior
$user = 'root';   // Padrão do Wamp
$pass = '';       // Padrão do Wamp (vazio)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
     // Se quiser testar se funcionou, descomente a linha abaixo:
     // echo "Conectado com sucesso!";
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>