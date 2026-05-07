<?php
require_once __DIR__ . '/../arquivos/session.php';
require_once __DIR__ . '/../arquivos/conexao.php';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php', true, 302);
    exit;
}

// Buscar dados do usuário
$stmt = $pdo->prepare("SELECT id, nome, email, role FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_destroy();
    header('Location: ../login/login.php', true, 302);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>Dashboard — EducaTEC</title>
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-white border-r border-gray-200 fixed h-screen overflow-y-auto left-0 top-0">
            <div class="p-6 border-b border-gray-200">
                <a href="../index.html" class="text-2xl font-black tracking-tighter text-gray-900 flex items-center gap-1">
                    Educa<span class="text-blue-500">TEC</span>
                </a>
            </div>

            <!-- MENU -->
            <nav class="p-4 space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-50 text-blue-600 transition-all font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-3m0 0l7-4 7 4M5 9v10a1 1 0 001 1h12a1 1 0 001-1V9m-9 4v4m4-4v4m-9-8l2-3m11 0l2 3M3 20h18"></path>
                    </svg>
                    Início
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.228 6.228 2 10.228 2 15s4.228 8.772 10 8.772 10-4.228 10-8.772c0-4.772-4.228-8.747-10-8.747z"></path>
                    </svg>
                    Materias
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Respostas
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Histórico
                </a>

                <hr class="my-4 border-gray-200">

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8a4 4 0 014-4h10a4 4 0 014 4v8a4 4 0 01-4 4H7a4 4 0 01-4-4V8z"></path>
                    </svg>
                    Mensagem
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">1</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0018 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    Notificações
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">1</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configurações
                </a>
            </nav>

            <!-- DIVIDER -->
            <div class="border-t border-gray-200 p-4 mt-6">
                <a href="../login/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sair
                </a>
            </div>

            <!-- PROFILE -->
            <div class="border-t border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate"><?php echo htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($usuario['role'] ?? 'aluno', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 ml-64 flex flex-col h-screen">
            
            <!-- TOP NAV -->
            <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center">
                <div class="relative flex-1 max-w-md">
                    <input type="text" id="searchInput" placeholder="Buscar..." class="w-full px-4 py-2 rounded-full bg-gray-100 border-none focus:ring-2 focus:ring-blue-400 outline-none">
                    <svg class="absolute right-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <div class="text-right ml-8">
                    <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($usuario['role'] ?? 'aluno', ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </nav>

            <!-- SCROLLABLE CONTENT -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-8">
                    
                    <!-- HEADER -->
                    <div class="mb-10">
                        <h1 class="text-4xl font-black text-gray-900 mb-2">Bem-vindo!</h1>
                        <p class="text-gray-500">Explore exercícios, aulas e muito mais</p>
                    </div>

                    <!-- FILTROS/TABS -->
                    <div class="mb-8 flex flex-wrap gap-2">
                        <button class="px-4 py-2 rounded-full bg-blue-500 text-white font-semibold hover:bg-blue-600 transition-all" data-filter="todos">Todos</button>
                        <button class="px-4 py-2 rounded-full bg-white text-gray-700 font-semibold border border-gray-200 hover:border-blue-400 transition-all" data-filter="exercicios">Exercícios</button>
                        <button class="px-4 py-2 rounded-full bg-white text-gray-700 font-semibold border border-gray-200 hover:border-blue-400 transition-all" data-filter="aulas">Aulas</button>
                        <button class="px-4 py-2 rounded-full bg-white text-gray-700 font-semibold border border-gray-200 hover:border-blue-400 transition-all" data-filter="tópicos">Tópicos</button>
                    </div>

                    <!-- CARDS GRID -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="cardsContainer">
                        
                        <!-- CARD 1 -->
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-blue-300" data-aos="fade-up" data-filter="exercicios">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">Exercício</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Exercícios sobre sequência numérica com padrão</h3>
                            <p class="text-sm text-gray-600 mb-4">As sequências numéricas são conceitos matemáticos essenciais. Neste exercício...</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>10 questões</span>
                                <span class="text-blue-600 font-semibold">Iniciar →</span>
                            </div>
                        </div>

                        <!-- CARD 2 -->
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-orange-300" data-aos="fade-up" data-filter="exercicios">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-semibold">Exercício</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Exercícios sobre relações métricas no triângulo retângulo</h3>
                            <p class="text-sm text-gray-600 mb-4">Estes exercícios sobre relações métricas no triângulo...</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>8 questões</span>
                                <span class="text-orange-600 font-semibold">Iniciar →</span>
                            </div>
                        </div>

                        <!-- CARD 3 -->
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-green-300" data-aos="fade-up" data-filter="aulas">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.228 6.228 2 10.228 2 15s4.228 8.772 10 8.772 10-4.228 10-8.772c0-4.772-4.228-8.747-10-8.747z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">Aula</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Integração de Carga e Análise em Circuitos</h3>
                            <p class="text-sm text-gray-600 mb-4">A tecnologia em uma sociedade em aceleração frequente...</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>45 min</span>
                                <span class="text-green-600 font-semibold">Assistir →</span>
                            </div>
                        </div>

                        <!-- CARD 4 -->
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-purple-300" data-aos="fade-up" data-filter="tópicos">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-semibold">Tópico</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">14 dias de estudo para avançar em Física</h3>
                            <p class="text-sm text-gray-600 mb-4">Qual é o propósito de um plano de estudos...</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>14 dias</span>
                                <span class="text-purple-600 font-semibold">Começar →</span>
                            </div>
                        </div>

                        <!-- CARD 5 -->
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-red-300" data-aos="fade-up" data-filter="exercicios">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full font-semibold">Exercício</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Exercícios sobre literatura de cord</h3>
                            <p class="text-sm text-gray-600 mb-4">Comentários sobre a</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>12 questões</span>
                                <span class="text-red-600 font-semibold">Iniciar →</span>
                            </div>
                        </div>

                        <!-- CARD 6 -->
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-cyan-300" data-aos="fade-up" data-filter="tópicos">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C6.228 6.228 2 10.228 2 15s4.228 8.772 10 8.772 10-4.228 10-8.772c0-4.772-4.228-8.747-10-8.747z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full font-semibold">Tópico</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Mais círculo de Linguagens, Códigos e suas Metodologias</h3>
                            <p class="text-sm text-gray-600 mb-4">Neste círculo, você encontrará uma nova perspective...</p>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>8 módulos</span>
                                <span class="text-cyan-600 font-semibold">Explorar →</span>
                            </div>
                        </div>

                        

                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <footer class="border-t border-gray-200 bg-white py-6 px-8">
                <div class="text-center text-gray-500 text-sm">
                    <p>&copy; 2026 EducaTEC. Todos os direitos reservados.</p>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Inicializar AOS
        AOS.init({ duration: 700 });

        // Filtro de Cards
        const filterButtons = document.querySelectorAll('[data-filter]');
        const cards = document.querySelectorAll('#cardsContainer > div[data-filter]');

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const filter = this.dataset.filter;
                
                filterButtons.forEach(b => {
                    b.classList.remove('bg-blue-500', 'text-white');
                    b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-200');
                });
                this.classList.add('bg-blue-500', 'text-white');
                this.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-200');

                cards.forEach(card => {
                    if (filter === 'todos' || card.dataset.filter === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Busca em tempo real
        const searchInput = document.getElementById('searchInput');
        searchInput?.addEventListener('input', function(e) {
            const termo = e.target.value.toLowerCase();
            cards.forEach(card => {
                const titulo = card.querySelector('h3').textContent.toLowerCase();
                if (titulo.includes(termo) || termo === '') {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>

</body>
</html>