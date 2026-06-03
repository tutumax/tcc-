<?php
require_once __DIR__ . '/../arquivos/session.php';
require_once __DIR__ . '/../arquivos/conexao.php';
require_once __DIR__ . '/../arquivos/csrf.php';
if (isset($_SESSION)){
    header('Location: ../dashboard/dashboard.php', true, 302);
} else{
    header('Location: index_logar.php', true, 302);
}
?>