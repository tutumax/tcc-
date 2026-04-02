<?php
require_once __DIR__ . '/../arquivos/conexao.php';
require_once __DIR__ . '/../arquivos/session.php';
require_once __DIR__ . '/../arquivos/csrf.php';
$token = gerar_csrf_token();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>Entrar — EducaTEC</title>
</head>
<body class="bg-white text-gray-800">
    <?php 
    if (file_exists(__DIR__ . '/nav_auth.php')) {
        include __DIR__ . '/nav_auth.php';
    } elseif (file_exists(__DIR__ . '/../navbar/nav.php')) {
        include __DIR__ . '/../navbar/nav.php';
    } else {
        // opcional: nada a incluir — evita warnings
    }
    ?>
    <div class="min-h-screen flex items-center justify-center px-6 py-10 mt-20">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8" data-aos="fade-up">
            <h2 class="text-2xl font-black text-gray-900 mb-2">Acesse sua Conta</h2>
            <p class="text-gray-500 mb-6">Entre para gerenciar seu conteúdo e contribuir com a comunidade.</p>
            
            <form action="logar.php" method="POST" class="grid gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">

                <input type="email" name="email" id="email" required placeholder="Seu e-mail cadastrado" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">

                <input type="password" name="senha" id="senha" required minlength="6" placeholder="Sua senha" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-3xl shadow-xl transition-all">Entrar</button>
            </form>
            
            <p class="text-center text-sm text-gray-500 mt-6">Não é cadastrado? <a href="cadastro.php" class="text-blue-500 font-bold">Criar uma conta</a></p>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 700 });
    </script>
</body>
</html>
