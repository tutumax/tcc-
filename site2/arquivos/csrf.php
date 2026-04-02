<?php
// Helper para geração e validação de tokens CSRF

function gerar_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function validar_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
