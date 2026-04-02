<?php
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
    <title>Criar Conta — EducaTEC</title>
</head>
<body class="bg-blue-50 text-gray-800">
    <?php
    if (file_exists(__DIR__ . '/nav_auth.php')) {
        include __DIR__ . '/nav_auth.php';
    } elseif (file_exists(__DIR__ . '/../navbar/nav.php')) {
        include __DIR__ . '/../navbar/nav.php';
    }
    ?>
    <div class="min-h-screen flex items-center justify-center px-6 py-10 mt-20">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8" data-aos="zoom-in">
            <h2 class="text-2xl font-black text-gray-900 mb-2">Criar sua conta</h2>
            <p class="text-gray-500 mb-6">Cadastre-se.</p>
            
            <form action="registrar.php" method="POST" class="grid gap-4" id="cadForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="role" id="roleInput" value="aluno">

                <div class="flex justify-center mb-4">
                    <div class="inline-flex rounded-full shadow-sm" role="tablist">
                        <button type="button" id="btnAluno" class="px-6 py-2 rounded-full bg-blue-500 text-white font-bold">Aluno</button>
                        <button type="button" id="btnProfessor" class="px-6 py-2 rounded-full ml-2 bg-white text-blue-500 font-bold border border-blue-500">Professor</button>
                    </div>
                </div>

                <input type="text" name="nome" id="nome" required placeholder="Ex: João Silva" minlength="3" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">

                <input type="email" name="email" id="email" required placeholder="seu@email.com" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">

                <input type="tel" name="telefone" id="telefone" required placeholder="(xx) xxxx-xxxx" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">

                <input type="password" name="senha" id="senha" required minlength="6" placeholder="No mínimo 6 caracteres" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-3xl shadow-xl transition-all">Finalizar Cadastro</button>
            </form>

            <script>
                (function(){
                    const btnAluno = document.getElementById('btnAluno');
                    const btnProfessor = document.getElementById('btnProfessor');
                    const roleInput = document.getElementById('roleInput');

                    function setAluno(){
                        roleInput.value = 'aluno';
                        btnAluno.classList.remove('bg-white','text-blue-500','border');
                        btnAluno.classList.add('bg-blue-500','text-white');
                        btnProfessor.classList.remove('bg-blue-500','text-white');
                        btnProfessor.classList.add('bg-white','text-blue-500','border');
                    }
                    function setProfessor(){
                        roleInput.value = 'professor';
                        btnProfessor.classList.remove('bg-white','text-blue-500','border');
                        btnProfessor.classList.add('bg-blue-500','text-white');
                        btnAluno.classList.remove('bg-blue-500','text-white');
                        btnAluno.classList.add('bg-white','text-blue-500');
                    }
                    // default: aluno selected styling
                    setAluno();
                    btnAluno.addEventListener('click', setAluno);
                    btnProfessor.addEventListener('click', setProfessor);
                })();
            </script>
            
            <p class="text-center text-sm text-gray-500 mt-6">Já tem uma conta? <a href="login.php" class="text-blue-500 font-bold">Entrar agora</a></p>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 700 });
    </script>
</body>
</html>
