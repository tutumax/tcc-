<?php
require_once __DIR__ . '/../arquivos/session.php';
require_once __DIR__ . '/../arquivos/conexao.php';
require_once __DIR__ . '/../arquivos/csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar CSRF token
    $token = isset($_POST['csrf_token']) ? trim($_POST['csrf_token']) : '';
    if (!validar_csrf_token($token)) {
        http_response_code(400);
        $_SESSION['erro'] = 'Erro de segurança: token inválido.';
        header('Location: login.php', true, 302);
        exit;
    }
    
    // Validação básica e sanitização
    $emailRaw = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    $email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $_SESSION['erro'] = 'E-mail inválido.';
        header('Location: login.php', true, 302);
        exit;
    }

    // Buscar o usuário pelo e-mail usando prepared statement
    $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // Verificar se o usuário existe e a senha está correta
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        // ✅ REGENERAR SESSION ID
        session_regenerate_id(true);
        
        // ✅ GUARDAR DADOS DA SESSÃO
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        
        // Busca role e email
        if ($hasRole) {
            try {
                $stmtSession = $pdo->prepare("SELECT role, email FROM usuarios WHERE id = :id LIMIT 1");
                $stmtSession->execute([':id' => $usuario['id']]);
                $sessData = $stmtSession->fetch();
                $_SESSION['usuario_role'] = $sessData['role'] ?? 'aluno';
                $_SESSION['usuario_email'] = $sessData['email'] ?? '';
            } catch (Exception $e) {
                $_SESSION['usuario_role'] = 'aluno';
                $_SESSION['usuario_email'] = '';
            }
        }

        // ✅ REDIRECIONAR PARA DASHBOARD
        header('Location: ../dashboard/dashboard.php', true, 302);
        exit;
    } else {
        $_SESSION['erro'] = 'E-mail ou senha incorretos.';
        header('Location: login.php', true, 302);
        exit;
    }

} else {
    header('Location: login.php', true, 302);
    exit;
}
?>