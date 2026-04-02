<?php
// SCRIPT PARA LIMPAR COMPLETAMENTE COOKIES E SESSÕES
// Acesse este arquivo uma vez: http://localhost:81/tcc_login/tcc_login/limpar_cookies.php

// Limpar todos os cookies
foreach ($_COOKIE as $name => $value) {
    setcookie($name, '', time() - 3600, '/');
}

// Destruir sessão
session_start();
session_destroy();
session_unset();

// Limpar variáveis superglobais
$_SESSION = [];
$_COOKIE = [];

echo "<h1>Cookies e Sessões Limpas!</h1>";
echo "<p>Aguarde 5 segundos...</p>";
echo "<script>setTimeout(function() { window.location='login.php'; }, 5000);</script>";
?>
