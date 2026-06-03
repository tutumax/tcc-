<?php
require_once __DIR__ . '/../arquivos/session.php';
require_once __DIR__ . '/../arquivos/conexao.php';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/login.php', true, 302);
    exit;
}

// Buscar usuário
$stmt = $pdo->prepare("SELECT id, nome, email, role FROM usuarios WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

if (!$usuario) {
    session_destroy();
    header('Location: ../login/login.php', true, 302);
    exit;
}

// Matérias disponíveis
$materias = [
    ['id' => 1, 'nome' => 'Matemática', 'cor' => 'blue'],
    ['id' => 2, 'nome' => 'Português', 'cor' => 'red'],
    ['id' => 3, 'nome' => 'História', 'cor' => 'amber'],
    ['id' => 4, 'nome' => 'Geografia', 'cor' => 'green'],
    ['id' => 5, 'nome' => 'Ciências', 'cor' => 'purple'],
    ['id' => 6, 'nome' => 'Inglês', 'cor' => 'indigo'],
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <title>Dashboard — EducaTEC</title>
    
    <style>
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .dropdown-menu {
            animation: slideDown 0.2s ease-out forwards;
        }

        .dropdown-menu.hidden {
            animation: slideUp 0.2s ease-out forwards;
        }

        .menu-link-active {
            position: relative;
        }

        .menu-link-active::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            right: 0;
            height: 2px;
            background: #3b82f6;
            border-radius: 1px;
        }

        .sidebar-link-icon {
            transition: all 0.3s ease;
        }

        .sidebar-link:hover .sidebar-link-icon {
            transform: translateX(4px);
        }

        .materia-badge {
            transition: all 0.3s ease;
        }

        .materia-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800 overflow-hidden">

<!-- BOTÃO MOBILE -->
<button
id="menuBtn"
class="md:hidden fixed top-4 left-4 z-50 bg-blue-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-700 transition-all">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" y1="6" x2="20" y2="6"></line>
        <line x1="4" y1="12" x2="20" y2="12"></line>
        <line x1="4" y1="18" x2="20" y2="18"></line>
    </svg>
</button>

<div class="flex h-screen">

    <!-- OVERLAY -->
    <div
    id="overlay"
    class="fixed inset-0 bg-black/50 z-30 hidden md:hidden transition-opacity">
    </div>

    <!-- SIDEBAR -->
    <aside
    id="sidebar"
    class="fixed md:relative top-0 left-0 z-40 w-64 bg-white border-r border-gray-200 h-screen overflow-y-auto
    transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">

        <!-- LOGO -->
        <div class="h-20 flex items-center px-6 border-b border-gray-200">
            <a
            href="../index.html"
            class="text-2xl font-black tracking-tighter text-gray-900 flex items-center gap-2 hover:opacity-80 transition-opacity">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zM22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
                <span>Educa<span class="text-blue-600">TEC</span></span>
            </a>
        </div>

        <!-- MENU -->
        <nav class="p-4 space-y-2">

            <!-- INÍCIO -->
            <button
            class="menu-link w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-50 text-blue-600 font-semibold transition-all hover:bg-blue-100"
            data-section="inicio">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="sidebar-link-icon">Início</span>
            </button>

            <!-- MATÉRIAS - AGORA REDIRECIONA PARA materias.php -->
            <a
            href="materias.php"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="sidebar-link-icon">Matérias</span>
            </a>

            <!-- RESPOSTAS -->
            <button
            class="menu-link w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
            data-section="respostas">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="sidebar-link-icon">Respostas</span>
            </button>

            <!-- HISTÓRICO -->
            <button
            class="menu-link w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
            data-section="historico">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span class="sidebar-link-icon">Histórico</span>
            </button>

            <!-- CONFIGURAÇÕES -->
            <button
            class="menu-link w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all"
            data-section="configuracoes">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6"></path>
                    <path d="M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24"></path>
                    <path d="M1 12h6m6 0h6"></path>
                    <path d="M4.22 19.78l4.24-4.24m6.08 0l4.24 4.24"></path>
                    <path d="M12 17v6"></path>
                </svg>
                <span class="sidebar-link-icon">Configurações</span>
            </button>

            <hr class="my-4 border-gray-200">

            <!-- SAIR -->
            <a
            href="../login/logout.php"
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-all font-semibold group">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:translate-x-1 transition-transform">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Sair</span>
            </a>

        </nav>

        <!-- PERFIL -->
        <div class="border-t border-gray-200 p-4 mt-auto">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-gray-900 truncate">
                        <?php echo htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <p class="text-xs text-gray-500 capitalize">
                        <?php echo htmlspecialchars($usuario['role'] ?? 'aluno', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            </div>
        </div>

    </aside>

    <!-- MAIN -->
    <div class="flex-1 flex flex-col h-screen">

        <!-- NAVBAR SUPERIOR -->
        <nav
        class="bg-white border-b border-gray-200 h-20 flex items-center justify-between px-6 shadow-sm md:shadow-none">

            <!-- BUSCA -->
            <div class="relative w-full max-w-md ml-16 md:ml-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>

                <input
                type="text"
                id="searchInput"
                placeholder="Buscar..."
                class="w-full h-12 pl-12 pr-4 rounded-lg bg-gray-100 outline-none focus:ring-2 focus:ring-blue-400 focus:bg-white transition-all text-sm">
            </div>

            <!-- NOTIFICAÇÕES E PERFIL -->
            <div class="flex items-center gap-4">
                
                <!-- NOTIFICAÇÕES -->
                <button class="relative w-10 h-10 rounded-lg hover:bg-gray-100 transition-all flex items-center justify-center group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600 group-hover:text-blue-600 transition-colors">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full"></span>
                </button>

                <!-- PERFIL DROPDOWN -->
                <div class="relative group">
                    <button class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 transition-all">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 group-hover:rotate-180 transition-transform">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    <!-- PERFIL DROPDOWN MENU -->
                    <div class="absolute right-0 top-full mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-lg hidden group-hover:block z-50 dropdown-menu">
                        <div class="p-4 border-b border-gray-200">
                            <p class="text-sm font-bold text-gray-900">
                                <?php echo htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?php echo htmlspecialchars($usuario['email'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <div class="p-2 space-y-1">
                            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-blue-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Meu Perfil
                            </a>
                            <a href="#" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-700 hover:bg-blue-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <path d="M12 1v6m0 6v6"></path>
                                    <path d="M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24"></path>
                                    <path d="M1 12h6m6 0h6"></path>
                                    <path d="M4.22 19.78l4.24-4.24m6.08 0l4.24 4.24"></path>
                                    <path d="M12 17v6"></path>
                                </svg>
                                Configurações
                            </a>
                            <hr class="my-1 border-gray-200">
                            <a href="../login/logout.php" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                Sair
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- CONTEÚDO -->
        <main class="flex-1 overflow-y-auto p-8">

            <!-- INÍCIO -->
            <section id="inicio" class="content-section">
                <!-- HEADER -->
                <div class="mb-10">
                    <h1 class="text-4xl font-black text-gray-900 mb-2">Bem-vindo!</h1>
                    <p class="text-gray-500">Explore exercícios, aulas e muito mais</p>
                </div>

                <!-- FILTROS -->
                <div class="mb-8 flex flex-wrap gap-2">
                    <button class="filter-btn px-4 py-2 rounded-full bg-blue-500 text-white font-semibold transition-all hover:bg-blue-600"
                    data-filter="all">
                        Todos
                    </button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 hover:border-blue-300 transition-all"
                    data-filter="exercicios">
                        Exercícios
                    </button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 hover:border-blue-300 transition-all"
                    data-filter="aulas">
                        Aulas
                    </button>
                    <button class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 hover:border-blue-300 transition-all"
                    data-filter="topicos">
                        Tópicos
                    </button>
                </div>

                <!-- CARDS GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="cardsContainer">

                    <!-- CARD 1 -->
                    <div class="card bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-blue-300 group"
                    data-filter="exercicios">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-600 transition-all">
                                <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">
                                Exercício
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            Exercícios sobre sequência numérica com padrão
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            As sequências numéricas são conceitos matemáticos essenciais. Neste exercício...
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>10 questões</span>
                            <span class="text-blue-600 font-semibold group-hover:translate-x-1 transition-transform">
                                Iniciar →
                            </span>
                        </div>
                    </div>

                    <!-- CARD 2 -->
                    <div class="card bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-orange-300 group"
                    data-filter="exercicios">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center group-hover:bg-orange-600 transition-all">
                                <svg class="w-6 h-6 text-orange-600 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <span class="text-xs bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-semibold">
                                Exercício
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            Exercícios sobre relações métricas no triângulo retângulo
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Estes exercícios sobre relações métricas no triângulo...
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>8 questões</span>
                            <span class="text-orange-600 font-semibold group-hover:translate-x-1 transition-transform">
                                Iniciar →
                            </span>
                        </div>
                    </div>

                    <!-- CARD 3 -->
                    <div class="card bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-green-300 group"
                    data-filter="aulas">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-600 transition-all">
                                <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C6.228 6.228 2 10.228 2 15s4.228 8.772 10 8.772 10-4.228 10-8.772c0-4.772-4.228-8.747-10-8.747z"></path>
                                </svg>
                            </div>
                            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">
                                Aula
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            Integração de Carga e Análise em Circuitos
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            A tecnologia em uma sociedade em aceleração frequente...
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>45 min</span>
                            <span class="text-green-600 font-semibold group-hover:translate-x-1 transition-transform">
                                Assistir →
                            </span>
                        </div>
                    </div>

                    <!-- CARD 4 -->
                    <div class="card bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-purple-300 group"
                    data-filter="topicos">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-600 transition-all">
                                <svg class="w-6 h-6 text-purple-600 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <span class="text-xs bg-purple-100 text-purple-700 px-3 py-1 rounded-full font-semibold">
                                Tópico
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            14 dias de estudo para avançar em Física
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Qual é o propósito de um plano de estudos...
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>14 dias</span>
                            <span class="text-purple-600 font-semibold group-hover:translate-x-1 transition-transform">
                                Começar →
                            </span>
                        </div>
                    </div>

                    <!-- CARD 5 -->
                    <div class="card bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-red-300 group"
                    data-filter="exercicios">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center group-hover:bg-red-600 transition-all">
                                <svg class="w-6 h-6 text-red-600 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full font-semibold">
                                Exercício
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            Exercícios sobre literatura de cordel
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Comentários sobre a literatura de cordel...
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>12 questões</span>
                            <span class="text-red-600 font-semibold group-hover:translate-x-1 transition-transform">
                                Iniciar →
                            </span>
                        </div>
                    </div>

                    <!-- CARD 6 -->
                    <div class="card bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all p-6 border border-gray-100 cursor-pointer hover:border-cyan-300 group"
                    data-filter="topicos">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 bg-cyan-100 rounded-xl flex items-center justify-center group-hover:bg-cyan-600 transition-all">
                                <svg class="w-6 h-6 text-cyan-600 group-hover:text-white transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C6.228 6.228 2 10.228 2 15s4.228 8.772 10 8.772 10-4.228 10-8.772c0-4.772-4.228-8.747-10-8.747z"></path>
                                </svg>
                            </div>
                            <span class="text-xs bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full font-semibold">
                                Tópico
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">
                            Mais círculo de Linguagens, Códigos e suas Metodologias
                        </h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Neste círculo, você encontrará uma nova perspectiva...
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>8 módulos</span>
                            <span class="text-cyan-600 font-semibold group-hover:translate-x-1 transition-transform">
                                Explorar →
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- RESPOSTAS -->
            <section id="respostas" class="content-section hidden">
                <h1 class="text-4xl font-black text-gray-900 mb-4">Respostas</h1>
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 text-gray-400 mx-auto mb-4">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <p class="text-gray-600">Nenhuma resposta ainda</p>
                </div>
            </section>

            <!-- HISTÓRICO -->
            <section id="historico" class="content-section hidden">
                <h1 class="text-4xl font-black text-gray-900 mb-4">Histórico</h1>
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-12 h-12 text-gray-400 mx-auto mb-4">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <p class="text-gray-600">Seu histórico de atividades aparecerá aqui</p>
                </div>
            </section>

            <!-- CONFIGURAÇÕES -->
            <section id="configuracoes" class="content-section hidden">
                <h1 class="text-4xl font-black text-gray-900 mb-8">Configurações</h1>
                <div class="bg-white rounded-xl border border-gray-200 p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Preferências de Conta</h3>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                            <div>
                                <p class="font-semibold text-gray-900">Notificações</p>
                                <p class="text-sm text-gray-600">Receber notificações por e-mail</p>
                            </div>
                            <input type="checkbox" class="w-5 h-5" checked>
                        </div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                            <div>
                                <p class="font-semibold text-gray-900">E-mails de progresso</p>
                                <p class="text-sm text-gray-600">Receber relatórios semanais de progresso</p>
                            </div>
                            <input type="checkbox" class="w-5 h-5">
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // MENU MOBILE
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });
    
    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });

    // MENU LATERAL
    const menuLinks = document.querySelectorAll('.menu-link');
    const sections = document.querySelectorAll('.content-section');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            const target = link.dataset.section;
            
            menuLinks.forEach(btn => {
                btn.classList.remove('bg-blue-50', 'text-blue-600', 'font-semibold');
                btn.classList.add('text-gray-700');
            });
            
            link.classList.add('bg-blue-50', 'text-blue-600', 'font-semibold');
            link.classList.remove('text-gray-700');
            
            sections.forEach(section => {
                section.classList.add('hidden');
            });
            
            document.getElementById(target).classList.remove('hidden');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    });

    // FILTROS
    const filterButtons = document.querySelectorAll('.filter-btn');
    const cards = document.querySelectorAll('.card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-white', 'border', 'border-gray-200');
            });
            
            button.classList.remove('bg-white', 'border', 'border-gray-200');
            button.classList.add('bg-blue-500', 'text-white');
            
            cards.forEach(card => {
                const category = card.dataset.filter;
                if (filter === 'all' || category === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });
});
</script>

</body>
</html>