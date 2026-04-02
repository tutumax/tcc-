<?php
// ARQUIVO DE DEBUG - Para identificar problemas no login
// Remova este arquivo após resolver o problema

session_start();

ob_start();

echo "<h1>🔍 Diagnóstico de Login</h1>";
echo "<hr>";

// 1. Verificar se a sessão está ativa
echo "<h2>1. Status da Sessão:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . (session_status() === PHP_SESSION_NONE ? "Não iniciada" : "Iniciada") . "<br>";
echo "Session Data: <pre>" . print_r($_SESSION, true) . "</pre>";

// 2. Verificar POST data
echo "<h2>2. Dados POST:</h2>";
echo "POST Recebido: " . (count($_POST) > 0 ? "SIM" : "NÃO") . "<br>";
if (count($_POST) > 0) {
    echo "<pre>" . print_r($_POST, true) . "</pre>";
}

// 3. Testar conexão com banco
echo "<h2>3. Conexão com Banco de Dados:</h2>";
try {
    require_once 'conexao.php';
    echo "Conexão PDO estabelecida com sucesso<br>";
    
    // Testar uma query
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "Total de usuários no banco: " . $result['total'] . "<br>";
} catch (Exception $e) {
    echo "Erro na conexão: " . $e->getMessage() . "<br>";
}

// 4. Testar CSRF
echo "<h2>4. Verificação CSRF:</h2>";
try {
    require_once 'csrf.php';
    echo "CSRF funcionando<br>";
} catch (Exception $e) {
    echo "Erro no CSRF: " . $e->getMessage() . "<br>";
}

// 5. Headers já enviados?
echo "<h2>5. Headers:</h2>";
if (headers_sent($file, $line)) {
    echo "Headers já foram enviados em $file na linha $line<br>";
} else {
    echo "Headers não foram enviados ainda<br>";
}

// 6. Simular login
echo "<h2>6. Teste Manual de Login:</h2>";
if (count($_POST) > 0) {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    
    echo "E-mail fornecido: " . htmlspecialchars($email) . "<br>";
    echo "Senha fornecida: " . (strlen($senha) > 0 ? "SIM (". strlen($senha) ." caracteres)" : "NÃO") . "<br>";
    
    try {
        $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();
        
        if ($usuario) {
            echo "Usuário encontrado: " . htmlspecialchars($usuario['nome']) . "<br>";
            $match = password_verify($senha, $usuario['senha']);
            echo "Senha correta: " . ($match ? "SIM" : "NÃO") . "<br>";
        } else {
            echo "Usuário não encontrado no banco<br>";
        }
    } catch (Exception $e) {
        echo "Erro ao consultar: " . $e->getMessage() . "<br>";
    }
} else {
    echo "<p><strong>Para testar o login, use o formulário abaixo:</strong></p>";
    
    // Mostrar todos os usuários do banco para teste
    try {
        $stmt = $pdo->prepare("SELECT id, email, nome FROM usuarios");
        $stmt->execute();
        $usuarios = $stmt->fetchAll();
        
        if (count($usuarios) > 0) {
            echo "<p><strong>Usuários cadastrados no banco:</strong></p>";
            echo "<ul>";
            foreach ($usuarios as $u) {
                echo "<li>Email: <code>" . htmlspecialchars($u['email']) . "</code> - Nome: " . htmlspecialchars($u['nome']) . "</li>";
            }
            echo "</ul>";
        }
    } catch (Exception $e) {
        echo "Erro ao listar usuários: " . $e->getMessage() . "<br>";
    }
    
    echo "<h3>Formulário de Teste de Login:</h3>";
    echo "<form method='POST' class='space-y-3'>";
    echo "<label for='email'>E-mail: <input type='email' name='email' id='email' required class='w-full p-2 rounded border'></label><br>";
    echo "<label for='senha'>Senha: <input type='password' name='senha' id='senha' required class='w-full p-2 rounded border'></label><br>";
    echo "<button type='submit' class='px-4 py-2 bg-blue-500 text-white rounded'>Testar Login</button>";
    echo "</form>";
}

echo "<hr>";
echo "<a href='login.php' class='text-blue-500'>Voltar para Login</a> | <a href='dashboard.php' class='text-blue-500'>Ir para Dashboard</a>";

$body = ob_get_clean();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>Debug Login — EduShare</title>
</head>
<body class="bg-white text-gray-800 p-6">
    <?php include __DIR__ . '/nav.php'; ?>
    <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-xl p-6 mt-20" data-aos="fade-up">
        <?php echo $body; ?>
    </div>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({duration:600});</script>
</body>
</html>
?>
