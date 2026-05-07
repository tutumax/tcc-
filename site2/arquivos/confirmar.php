<?php
require_once 'conexao.php';

$chave_recebida = isset($_GET['chave']) ? trim($_GET['chave']) : '';

if (empty($chave_recebida)) {
    echo "<h1>Erro: Chave inválida.</h1>";
    echo "<p><a href='index.html'>Voltar ao início</a></p>";
    exit;
}

try {
    // Verifica se a chave existe e não foi confirmada
    $stmt = $pdo->prepare("SELECT * FROM links_emails WHERE link = :link LIMIT 1");
    $stmt->execute([':link' => $chave_recebida]);
    $link_db = $stmt->fetch();

    if (!$link_db) {
        echo "<h1>Erro: Link inválido ou já utilizado.</h1>";
        echo "<p><a href='index.html'>Voltar ao início</a></p>";
        exit;
    }

    // Se situacao = 2, já foi confirmado
    if ($link_db['situacao'] == 2) {
        echo "<h1>Atenção: Este link já foi utilizado.</h1>";
        echo "<p>Sua conta já foi confirmada. Acesse seu perfil ou <a href='../login/login.php'>faça login</a>.</p>";
        exit;
    }

    // Verifica se o token expirou (24 horas)
    if (isset($link_db['created_at'])) {
        $created = strtotime($link_db['created_at']);
        $agora = time();
        $diferenca = $agora - $created;
        $horas = $diferenca / 3600;

        if ($horas > 24) {
            echo "<h1>Erro: Link expirado.</h1>";
            echo "<p>O link de confirmação expirou. <a href='index.html'>Registre-se novamente</a>.</p>";
            exit;
        }
    }

    // Atualiza a situação para 2 (Confirmado)
    $update = $pdo->prepare("UPDATE links_emails SET situacao = 2 WHERE link = :link");
    $update->execute([':link' => $chave_recebida]);

    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <title>E-mail Confirmado</title>
    </head>
    <body class="bg-blue-50 text-gray-800">
        <?php
        if (file_exists(__DIR__ . '/nav_auth.php')) {
            include __DIR__ . '/nav_auth.php';
        } elseif (file_exists(__DIR__ . '/../navbar/nav.php')) {
            include __DIR__ . '/../navbar/nav.php';
        } else {
            // nenhum menu disponível — evita warnings
        }
        ?>
        <div class="min-h-screen flex items-center justify-center p-8 mt-20">
            <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl p-8 text-center" data-aos="zoom-in">
                <h1 class="text-2xl font-black text-green-600 mb-2">E-mail confirmado com sucesso!</h1>
                <p class="text-gray-600 mb-6">Sua conta foi ativada. Agora você pode fazer login.</p>
                <a href="../login/login.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-3xl shadow hover:bg-blue-600">Ir para o Login</a>
            </div>
        </div>

        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>AOS.init({duration:700});</script>
    </body>
    </html>
    <?php
    exit;

} catch (PDOException $e) {
    error_log('Erro confirmar.php: ' . $e->getMessage());
    echo "<h1>Erro no servidor.</h1>";
    echo "<p>Tente novamente mais tarde. <a href='index.html'>Voltar</a></p>";
    exit;
}
?>

