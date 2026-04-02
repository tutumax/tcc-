<?php
require_once 'session.php';

// Limpa variáveis de sessão
$_SESSION = [];

// Apaga cookie da sessão
if (ini_get('session.use_cookies')) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Destrói a sessão
session_unset();
session_destroy();

header("Location: index.html");
exit;
?>