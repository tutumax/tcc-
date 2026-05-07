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
            
            <form action="registrar.php" method="POST" class="grid gap-4" id="cadForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="role" id="roleInput" value="aluno">

                <div class="flex justify-center mb-4">
                    <div class="inline-flex rounded-full shadow-sm" role="tablist">
                        <button type="button" id="btnAluno" class="px-6 py-2 rounded-full bg-blue-500 text-white font-bold">Aluno</button>
                        <button type="button" id="btnProfessor" class="px-6 py-2 rounded-full ml-2 bg-white text-blue-500 font-bold border border-blue-500">Professor</button>
                    </div>
                </div>

                <div>
                    <input type="text" name="nome" id="nome" required placeholder="Ex: João Silva" minlength="3" maxlength="100" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    <span id="nomeError" class="text-red-500 text-sm hidden"></span>
                </div>

                <div>
                    <input type="email" name="email" id="email" required placeholder="seu@email.com" maxlength="100" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    <span id="emailError" class="text-red-500 text-sm hidden"></span>
                </div>

                <!-- ✅ CAMPO TELEFONE - APENAS CELULAR -->
                <div>
                    <input type="tel" name="telefone" id="telefone" required placeholder="(xx) xxxxx-xxxx" inputmode="numeric" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none" data-mask="celular">
                    <span id="telefoneError" class="text-red-500 text-sm hidden"></span>
                    <small class="text-gray-400">Celular com 11 dígitos (com 9º dígito)</small>
                </div>

                <div>
                    <input type="password" name="senha" id="senha" required minlength="6" placeholder="No mínimo 6 caracteres" class="w-full p-4 rounded-3xl border-none shadow-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    <span id="senhaError" class="text-red-500 text-sm hidden"></span>
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-3xl shadow-xl transition-all">Finalizar Cadastro</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">Já tem uma conta? <a href="login.php" class="text-blue-500 font-bold">Entrar agora</a></p>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Controle de Role (Aluno/Professor)
        (function(){
            const btnAluno = document.getElementById('btnAluno');
            const btnProfessor = document.getElementById('btnProfessor');
            const roleInput = document.getElementById('roleInput');

            function setAluno(){
                roleInput.value = 'aluno';
                btnAluno.classList.remove('bg-white','text-blue-500','border');
                btnAluno.classList.add('bg-blue-500','text-white');
                btnProfessor.classList.remove('bg-blue-500','text-white');
                btnProfessor.classList.add('bg-white','text-blue-500','border','border-blue-500');
            }

            function setProfessor(){
                roleInput.value = 'professor';
                btnProfessor.classList.remove('bg-white','text-blue-500','border');
                btnProfessor.classList.add('bg-blue-500','text-white');
                btnAluno.classList.remove('bg-blue-500','text-white');
                btnAluno.classList.add('bg-white','text-blue-500','border','border-blue-500');
            }

            setAluno();
            btnAluno.addEventListener('click', setAluno);
            btnProfessor.addEventListener('click', setProfessor);
        })();

        // ✅ MÁSCARA DE TELEFONE - APENAS CELULAR (11 dígitos)
        const telefonInput = document.getElementById('telefone');
        telefonInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            // Limita a 11 dígitos
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            
            let formatted = '';
            if (value.length > 0) {
                // Formato: (xx) xxxxx-xxxx
                formatted = '(' + value.substring(0, 2);
                if (value.length > 2) {
                    formatted += ') ' + value.substring(2, 7);
                    if (value.length > 7) {
                        formatted += '-' + value.substring(7);
                    }
                }
            }
            
            e.target.value = formatted;
        });

        //  VALIDAÇÃO DE FORMULÁRIO
        const form = document.getElementById('cadForm');
        form.addEventListener('submit', function(e) {
            let isValid = true;

            // Validar Nome
            const nome = document.getElementById('nome').value.trim();
            const nomeError = document.getElementById('nomeError');
            if (nome.length < 3) {
                nomeError.textContent = 'Nome deve ter no mínimo 3 caracteres';
                nomeError.classList.remove('hidden');
                isValid = false;
            } else {
                nomeError.classList.add('hidden');
            }

            // Validar Email
            const email = document.getElementById('email').value.trim();
            const emailError = document.getElementById('emailError');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailError.textContent = 'E-mail inválido';
                emailError.classList.remove('hidden');
                isValid = false;
            } else {
                emailError.classList.add('hidden');
            }

            // Validar Telefone - APENAS 11 dígitos
            const telefone = document.getElementById('telefone').value.replace(/\D/g, '');
            const telefoneError = document.getElementById('telefoneError');
            if (telefone.length !== 11) {
                telefoneError.textContent = 'Celular deve ter 11 dígitos (com 9º dígito)';
                telefoneError.classList.remove('hidden');
                isValid = false;
            } else if (telefone[2] !== '9') {
                // Valida se o 3º dígito é 9 (característica de celular brasileiro)
                telefoneError.textContent = 'Celular deve conter o 9º dígito';
                telefoneError.classList.remove('hidden');
                isValid = false;
            } else {
                telefoneError.classList.add('hidden');
            }

            // Validar Senha
            const senha = document.getElementById('senha').value;
            const senhaError = document.getElementById('senhaError');
            if (senha.length < 6) {
                senhaError.textContent = 'Senha deve ter no mínimo 6 caracteres';
                senhaError.classList.remove('hidden');
                isValid = false;
            } else {
                senhaError.classList.add('hidden');
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        AOS.init({ duration: 700 });
    </script>
</body>
</html>
