<?php
require_once __DIR__ . '/../arquivos/session.php';
require_once __DIR__ . '/../arquivos/conexao.php';
require_once __DIR__ . '/../arquivos/csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar CSRF token
    $token = isset($_POST['csrf_token']) ? trim($_POST['csrf_token']) : '';
    if (!validar_csrf_token($token)) {
        die("<script>alert('Erro de segurança: token inválido. Tente novamente.'); window.location='login.php';</script>");
    }
    
    // Validação básica e sanitização
    $emailRaw = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    $email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        die("<script>alert('E-mail inválido.'); window.location='login.php';</script>");
    }

    // Buscar o usuário pelo e-mail usando prepared statement
    $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();

    // Verificar se o usuário existe e se a senha está correta
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        
        // Checar se existe registro na tabela links_emails e se está confirmado (situacao = 2)
        try {
            $stmtCheck = $pdo->prepare("SELECT situacao FROM links_emails WHERE usuario_id = :uid ORDER BY id DESC LIMIT 1");
            $stmtCheck->execute([':uid' => $usuario['id']]);
            $rowCheck = $stmtCheck->fetch();
            if ($rowCheck && intval($rowCheck['situacao']) !== 2) {
                die("<script>alert('Conta não confirmada. Por favor verifique seu e-mail e confirme a conta antes de entrar.'); window.location='login.php';</script>");
            }
        } catch (PDOException $e) {
            // Se a tabela links_emails não existir, não bloquear o login (compatibilidade)
        }

        // Antes de criar sessão, checa se o usuário é professor e se está aprovado pelo admin
        try {
            $hasRole = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'role'")->fetch();
            $hasAprovado = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'aprovado'")->fetch();
        } catch (Exception $e) {
            $hasRole = false;
            $hasAprovado = false;
        }

        if ($hasRole) {
            try {
                $stmtRole = $pdo->prepare("SELECT role" . ($hasAprovado ? ", aprovado" : "") . " FROM usuarios WHERE id = :id LIMIT 1");
                $stmtRole->execute([':id' => $usuario['id']]);
                $r = $stmtRole->fetch();
                $roleVal = isset($r['role']) ? strtolower($r['role']) : '';
                $aprovVal = isset($r['aprovado']) ? intval($r['aprovado']) : 1;

                if (in_array($roleVal, ['professor','teacher','prof']) && $hasAprovado && $aprovVal !== 1) {
                    die("<script>alert('Conta de professor pendente aprovação do administrador. Aguarde aprovação.'); window.location='login.php';</script>");
                }
            } catch (Exception $e) {
                // se algo falhar aqui, permitimos o login normal (compatibilidade)
            }
        }

        // Regenera o id de sessão ANTES de adicionar dados (segurança)
        session_regenerate_id(true);

        // Sucesso! Guarda os dados na Sessão
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

        // Redireciona para a página principal
        header("Location: dashboard.php", true, 302);
        exit;
    } else {
        // Falha no login (não detalhar se e-mail ou senha estão incorretos)
        die("<script>alert('E-mail ou senha incorretos.'); window.location='login.php';</script>");
    }
} else {
    // Se não for POST, redireciona para login
    header("Location: login.php", true, 302);
    exit;
}
?>