<?php
// Helper para iniciar sessão com parâmetros mais seguros
// Chame require_once 'session.php' antes de acessar/usar $_SESSION

// Apenas configure cookies se a sessão ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    // Configura cookies de sessão: HttpOnly e SameSite ajudam a mitigar XSS/CSRF
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    
    session_start();
}
?>
