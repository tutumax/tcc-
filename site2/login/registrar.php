<?php
require_once __DIR__ . '/../arquivos/conexao.php';
require_once __DIR__ . '/../arquivos/csrf.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar CSRF token
    $token = isset($_POST['csrf_token']) ? trim($_POST['csrf_token']) : '';
    if (!validar_csrf_token($token)) {
        echo "<script>alert('Erro de segurança: token inválido. Tente novamente.'); history.back();</script>";
        exit;
    }
    
    // Sanitização e validação básica
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $emailRaw = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $telefoneBruto = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';

    // Validar nome
    if (empty($nome) || strlen($nome) < 3) {
        $msg = htmlspecialchars('Nome deve ter no mínimo 3 caracteres.', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // Validar email
    $email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $msg = htmlspecialchars('E-mail inválido.', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // Validar senha
    if (strlen($senha) < 6) {
        $msg = htmlspecialchars('A senha deve ter no mínimo 6 caracteres.', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    //  NOVA VALIDAÇÃO: Formatar e validar telefone
    $telefone = validar_e_formatar_telefone($telefoneBruto);
    if (!$telefone) {
        $msg = htmlspecialchars('Telefone inválido. Use um celular válido com 11 dígitos: (xx) xxxxx-xxxx', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // Criptografar a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        // Detectar colunas
        $hasRole = false;
        $hasAprovado = false;
        try {
            $hasRole = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'role'")->fetch();
        } catch (Exception $e) { }
        try {
            $hasAprovado = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'aprovado'")->fetch();
        } catch (Exception $e) { }

        // Determinar role e aprovação
        $roleInput = isset($_POST['role']) ? trim($_POST['role']) : 'aluno';
        $role = ($roleInput === 'professor') ? 'professor' : 'aluno';
        $aprovado = ($role === 'professor') ? 0 : 1;

        // VERIFICAR DUPLICATA: Telefone + Role
        $stmtCheckPhone = $pdo->prepare(
            "SELECT id FROM usuarios WHERE telefone = :telefone AND role = :role LIMIT 1"
        );
        $stmtCheckPhone->execute([
            ':telefone' => $telefone,
            ':role' => $role
        ]);

        if ($stmtCheckPhone->fetch()) {
            $msg = htmlspecialchars(
                'Este telefone já está registrado como ' . ucfirst($role) . '. Tente outro número.',
                ENT_QUOTES,
                'UTF-8'
            );
            echo "<script>alert('{$msg}'); history.back();</script>";
            exit;
        }

        // Preparar INSERT baseado nas colunas disponíveis
        if ($hasRole && $hasAprovado) {
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, role, aprovado) 
                    VALUES (:nome, :email, :senha, :telefone, :role, :aprovado)";
            $params = [
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash,
                ':telefone' => $telefone,
                ':role' => $role,
                ':aprovado' => $aprovado
            ];
        } elseif ($hasRole) {
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone, role) 
                    VALUES (:nome, :email, :senha, :telefone, :role)";
            $params = [
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash,
                ':telefone' => $telefone,
                ':role' => $role
            ];
        } else {
            $sql = "INSERT INTO usuarios (nome, email, senha, telefone) 
                    VALUES (:nome, :email, :senha, :telefone)";
            $params = [
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash,
                ':telefone' => $telefone
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // ID do usuário recém-criado
        $usuarioId = $pdo->lastInsertId();

        // Gerar chave de confirmação
        try {
            $chave = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $chave = bin2hex(openssl_random_pseudo_bytes(16));
        }

        // Inserir link de confirmação
        $sqlLink = "INSERT INTO links_emails (link, usuario_id) VALUES (:link, :usuario_id)";
        $stmtLink = $pdo->prepare($sqlLink);
        $stmtLink->execute([':link' => $chave, ':usuario_id' => $usuarioId]);

        // Construir URL de confirmação
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $linkConfirm = $scheme . '://' . $host . '/site/arquivos/confirmar.php?chave=' . urlencode($chave);

        // Preparar e-mail
        $subject = 'Confirme seu e-mail - EducaTEC';
        $message = "Olá " . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ",\n\n";
        $message .= "Obrigado por se cadastrar no EducaTEC. Para ativar sua conta, clique no link abaixo:\n\n";
        $message .= $linkConfirm . "\n\n";
        $message .= "Se você não se registrou, ignore esta mensagem.\n\n";
        $message .= "Atenciosamente,\nEquipe EducaTEC";

        $headers = "From: noreply@" . $host . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Tentar enviar e-mail
        $mailSent = @mail($email, $subject, $message, $headers);

        if ($mailSent) {
            echo "<script>alert('Cadastro realizado com sucesso! Verifique seu e-mail para confirmar a conta.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Cadastro realizado, mas não conseguimos enviar o e-mail de confirmação. Contate o suporte.'); window.location='login.php';</script>";
        }
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Erro de duplicata (constraint)
            if (strpos($e->getMessage(), 'unique_telefone_role') !== false) {
                $msg = htmlspecialchars(
                    'Este telefone já está registrado como ' . ucfirst($role) . '.',
                    ENT_QUOTES,
                    'UTF-8'
                );
            } elseif (strpos($e->getMessage(), 'email') !== false) {
                $msg = htmlspecialchars('Este e-mail já está cadastrado.', ENT_QUOTES, 'UTF-8');
            } else {
                $msg = htmlspecialchars('Dados duplicados. Verifique seus dados.', ENT_QUOTES, 'UTF-8');
            }
            echo "<script>alert('{$msg}'); history.back();</script>";
        } else {
            error_log('Erro ao registrar: ' . $e->getMessage());
            $msg = htmlspecialchars('Erro ao registrar. Tente novamente mais tarde.', ENT_QUOTES, 'UTF-8');
            echo "<script>alert('{$msg}'); history.back();</script>";
        }
        exit;
    }
}

// ✅ FUNÇÃO: Validar e formatar telefone (APENAS CELULAR - 11 dígitos)
function validar_e_formatar_telefone($telefone) {
    // Remove caracteres não numéricos
    $telefoneLimpo = preg_replace('/\D/', '', $telefone);
    
    // ✅ APENAS 11 dígitos (celular)
    if (!preg_match('/^\d{11}$/', $telefoneLimpo)) {
        return false;
    }
    
    // Formatar: (xx) xxxxx-xxxx
    return '(' . substr($telefoneLimpo, 0, 2) . ') ' 
           . substr($telefoneLimpo, 2, 5) . '-' 
           . substr($telefoneLimpo, 7);
}
?>