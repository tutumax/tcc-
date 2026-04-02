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

    if (empty($nome) || strlen($nome) < 3) {
        $msg = htmlspecialchars('Nome deve ter no mínimo 3 caracteres.', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    $email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $msg = htmlspecialchars('E-mail inválido.', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    if (strlen($senha) < 6) {
        $msg = htmlspecialchars('A senha deve ter no mínimo 6 caracteres.', ENT_QUOTES, 'UTF-8');
        echo "<script>alert('{$msg}'); history.back();</script>";
        exit;
    }

    // Criptografar a senha (obrigatório)
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        // Detecta se a tabela `usuarios` tem colunas para role/aprovado
        $hasRole = false;
        $hasAprovado = false;
        try {
            $hasRole = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'role'")->fetch();
        } catch (Exception $e) { }
        try {
            $hasAprovado = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'aprovado'")->fetch();
        } catch (Exception $e) { }

        // Determina papel e estado de aprovação padrão
        $roleInput = isset($_POST['role']) ? trim($_POST['role']) : 'aluno';
        $role = ($roleInput === 'professor') ? 'professor' : 'aluno';
        // Professores precisam de aprovação do admin
        $aprovado = ($role === 'professor') ? 0 : 1;

        // Captura telefone (obrigatório no front-end)
        $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
        if (empty($telefone)) {
            echo "<script>alert('Informe um número de telefone válido.'); history.back();</script>";
            exit;
        }

        if ($hasRole || $hasAprovado) {
            $columns = ['nome','email','senha'];
            $placeholders = [':nome',':email',':senha'];
            $params = [':nome'=>$nome,':email'=>$email,':senha'=>$senhaHash];
            if ($hasRole) {
                $columns[] = 'role';
                $placeholders[] = ':role';
                $params[':role'] = $role;
            }
            if ($hasAprovado) {
                $columns[] = 'aprovado';
                $placeholders[] = ':aprovado';
                $params[':aprovado'] = $aprovado;
            }
            // Adiciona telefone se a coluna existir
            try {
                $hasTelefone = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'telefone'")->fetch();
            } catch (Exception $e) { $hasTelefone = false; }
            if ($hasTelefone) { $columns[] = 'telefone'; $placeholders[] = ':telefone'; $params[':telefone'] = $telefone; }

            $sql = "INSERT INTO usuarios (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            // Estrutura antiga: apenas insere nome, email, senha
            $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash
            ]);
        }

        // Pega o ID do usuário recém-criado
        $usuarioId = $pdo->lastInsertId();

        // Gera uma chave segura para confirmação (16 bytes -> 32 hex chars)
        try {
            $chave = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            // fallback
            $chave = md5(uniqid((string) time(), true));
        }

        // Insere o link na tabela links_emails (situação 1 = pendente)
        $sqlLink = "INSERT INTO links_emails (link, usuario_id) VALUES (:link, :usuario_id)";
        $stmtLink = $pdo->prepare($sqlLink);
        $stmtLink->execute([':link' => $chave, ':usuario_id' => $usuarioId]);

        // Monta a URL de confirmação
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        // confirma em arquivos/confirmar.php (arquivo está em /site/arquivos)
        $linkConfirm = $scheme . '://' . $host . '/site/arquivos/confirmar.php?chave=' . $chave;

        // Prepara e-mail simples
        $subject = 'Confirme seu e-mail';
        $message = "Olá " . htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') . ",\n\n";
        $message .= "Obrigado por se cadastrar no EduShare. Para ativar sua conta, clique no link abaixo:\n\n";
        $message .= $linkConfirm . "\n\n";
        $message .= "Se você não se registrou, ignore esta mensagem.\n";

        $headers = 'From: ' . ($host ?? 'localhost') . "\r\n" .
                   'Reply-To: ' . ($host ?? 'localhost') . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();

        // Tenta usar PHPMailer manual (sem Composer) se estiver disponível
        $mailSent = false;
        $phpMailerPath = __DIR__ . '/../arquivos/PHPMailer';
        if (file_exists($phpMailerPath . '/PHPMailer.php')) {
            // Carrega PHPMailer manualmente
            require_once $phpMailerPath . '/Exception.php';
            require_once $phpMailerPath . '/PHPMailer.php';
            require_once $phpMailerPath . '/SMTP.php';

            // Carrega configuração (modelo em mail_config.php)
            if (file_exists(__DIR__ . '/../arquivos/mail_config.php')) {
                $cfg = require __DIR__ . '/../arquivos/mail_config.php';
            } else {
                $cfg = null;
            }

            try {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                if ($cfg) {
                    $mail->isSMTP();
                    $mail->Host = $cfg['host'];
                    $mail->SMTPAuth = $cfg['smtp_auth'];
                    $mail->Username = $cfg['username'];
                    $mail->Password = $cfg['password'];
                    $mail->SMTPSecure = $cfg['secure'];
                    $mail->Port = $cfg['port'];
                    $mail->setFrom($cfg['from_email'], $cfg['from_name']);
                } else {
                    // Se não houver config, tenta enviar via localhost
                    $mail->isMail();
                }

                $mail->addAddress($email, $nome);
                $mail->Subject = $subject;
                $mail->Body = $message;
                $mail->AltBody = strip_tags($message);

                $mail->send();
                $mailSent = true;
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                error_log('PHPMailer send error: ' . $e->getMessage());
                $mailSent = false;
            } catch (\Exception $e) {
                error_log('Mail send error: ' . $e->getMessage());
                $mailSent = false;
            }
        } else {
            // Fallback para mail() nativo
            if (function_exists('mail')) {
                $mailSent = mail($email, $subject, $message, $headers);
            }
        }

        if ($mailSent) {
            echo "<script>alert('Cadastro realizado! Verifique seu e-mail para ativar a conta.'); window.location='login.php';</script>";
        } else {
            // Em desenvolvimento, mostramos o link para que possas testar sem SMTP
            echo "<h2>Cadastro realizado!</h2>";
            echo "<p>Em ambiente de desenvolvimento o servidor pode não enviar e-mails. Use o link abaixo para ativar a conta:</p>";
            echo "<p><a href=\"" . htmlspecialchars($linkConfirm, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($linkConfirm, ENT_QUOTES, 'UTF-8') . "</a></p>";
            echo "<p><a href=\"login.php\">Ir para Login</a></p>";
        }
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Erro de e-mail duplicado
            $msg = htmlspecialchars('Erro: Este e-mail já está cadastrado.', ENT_QUOTES, 'UTF-8');
            echo "<script>alert('{$msg}'); window.location='cadastro.php';</script>";
        } else {
            // Em produção, não exibir mensagens de erro detalhadas
            error_log('Erro ao cadastrar: ' . $e->getMessage());
            $msg = htmlspecialchars('Erro ao cadastrar. Tente novamente mais tarde.', ENT_QUOTES, 'UTF-8');
            echo "<script>alert('{$msg}'); history.back();</script>";
        }
    }
}
?>