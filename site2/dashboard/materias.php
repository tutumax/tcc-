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

// Matérias disponíveis com mais detalhes
$materias = [
    [
        'id' => 1,
        'nome' => 'Português',
        'cor' => 'red',
        'descricao' => 'Clique para baixar e encontre atividades como essas:',
        'tópicos' => ['Leitura e interpretação', 'Análise de textos', 'Gramática aplicada', 'Literatura brasileira'],
        'icon' => '📚',
        'exercicios' => 45,
        'aulas' => 12
    ],
    [
        'id' => 2,
        'nome' => 'Matemática',
        'cor' => 'blue',
        'descricao' => 'Clique para baixar e encontre atividades como essas:',
        'tópicos' => ['Álgebra', 'Geometria', 'Trigonometria', 'Cálculo'],
        'icon' => '🔢',
        'exercicios' => 67,
        'aulas' => 18
    ],
    [
        'id' => 3,
        'nome' => 'Geografia',
        'cor' => 'green',
        'descricao' => 'Clique para baixar e encontre atividades como essas:',
        'tópicos' => ['Geografia política', 'Geografia física', 'Geopolítica', 'Desenvolvimento sustentável'],
        'icon' => '🌍',
        'exercicios' => 38,
        'aulas' => 14
    ],
    [
        'id' => 4,
        'nome' => 'História',
        'cor' => 'amber',
        'descricao' => 'Clique para baixar e encontre atividades como essas:',
        'tópicos' => ['História do Brasil', 'História Geral', 'Idade Média', 'Período Contemporâneo'],
        'icon' => '🏛️',
        'exercicios' => 52,
        'aulas' => 16
    ],
    [
        'id' => 5,
        'nome' => 'Ciências',
        'cor' => 'purple',
        'descricao' => 'Clique para baixar e encontre atividades como essas:',
        'tópicos' => ['Física', 'Química', 'Biologia', 'Ecologia'],
        'icon' => '🔬',
        'exercicios' => 58,
        'aulas' => 15
    ],
    [
        'id' => 6,
        'nome' => 'Inglês',
        'cor' => 'indigo',
        'descricao' => 'Clique para baixar e encontre atividades como essas:',
        'tópicos' => ['Conversação', 'Gramática', 'Leitura', 'Escrita'],
        'icon' => '🌐',
        'exercicios' => 41,
        'aulas' => 13
    ],
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Matérias — EducaTEC</title>
    
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

        .dropdown-menu {
            animation: slideDown 0.2s ease-out forwards;
        }

        .sidebar-link-icon {
            transition: all 0.3s ease;
        }

        .sidebar-link:hover .sidebar-link-icon {
            transform: translateX(4px);
        }

        /* Card de Matéria Expandido */
        .materia-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .materia-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(59, 130, 246, 0.15);
            border-color: currentColor;
        }

        .materia-card-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .materia-icon-wrapper {
            flex-shrink: 0;
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            transition: all 0.3s;
        }

        .materia-card:hover .materia-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }

        .materia-topics {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .topic-tag {
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 20px;
            transition: all 0.2s;
            display: inline-block;
        }

        .topic-tag:hover {
            transform: translateY(-2px);
        }

        .stats-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.04);
            transition: all 0.2s;
        }

        .stats-badge:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        .search-box {
            transition: all 0.3s;
        }

        .search-box:focus-within {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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
            href="dashboard.php"
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
            <a
            href="dashboard.php"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="sidebar-link-icon">Início</span>
            </a>

            <!-- MATÉRIAS - ATIVO -->
            <button
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-50 text-blue-600 font-semibold transition-all hover:bg-blue-100"
            onclick="location.href='materias.php'">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <span class="sidebar-link-icon">Matérias</span>
            </button>

            <!-- RESPOSTAS -->
            <a
            href="dashboard.php"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                </svg>
                <span class="sidebar-link-icon">Respostas</span>
            </a>

            <!-- HISTÓRICO -->
            <a
            href="dashboard.php"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span class="sidebar-link-icon">Histórico</span>
            </a>

            <!-- CONFIGURAÇÕES -->
            <a
            href="dashboard.php"
            class="w-full text-left flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6"></path>
                    <path d="M4.22 4.22l4.24 4.24m6.08 0l4.24-4.24"></path>
                    <path d="M1 12h6m6 0h6"></path>
                    <path d="M4.22 19.78l4.24-4.24m6.08 0l4.24 4.24"></path>
                    <path d="M12 17v6"></path>
                </svg>
                <span class="sidebar-link-icon">Configurações</span>
            </a>

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
            <div class="relative w-full max-w-md ml-16 md:ml-0 search-box">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>

                <input
                type="text"
                id="searchInput"
                placeholder="Buscar matérias..."
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

            <!-- HEADER -->
            <div class="mb-12">
                <h1 class="text-5xl font-black text-gray-900 mb-3">Matérias</h1>
                <p class="text-gray-600 text-lg">Explore todas as disciplinas disponíveis e escolha sua jornada de aprendizado</p>
            </div>

            <!-- FILTROS -->
            <div class="mb-8 flex flex-wrap gap-2">
                <button class="filter-btn px-4 py-2 rounded-full bg-blue-500 text-white font-semibold transition-all hover:bg-blue-600"
                data-filter="all">
                    Todas
                </button>
                <button class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 hover:border-blue-300 transition-all"
                data-filter="popular">
                    Mais Populares
                </button>
                <button class="filter-btn px-4 py-2 rounded-full bg-white border border-gray-200 hover:border-blue-300 transition-all"
                data-filter="novo">
                    Novos Conteúdos
                </button>
            </div>

            <!-- LISTA DE MATÉRIAS -->
            <div class="space-y-4" id="materiasContainer">
                <?php foreach ($materias as $index => $materia): ?>
                <a href="materia.php?id=<?php echo $materia['id']; ?>" 
                   class="materia-card block bg-white rounded-2xl border border-gray-200 p-6 cursor-pointer transition-all duration-300 hover:border-<?php echo $materia['cor']; ?>-500 group"
                   data-filter="all">
                    
                    <div class="materia-card-header">
                        <div class="materia-icon-wrapper bg-<?php echo $materia['cor']; ?>-100">
                            <?php echo $materia['icon']; ?>
                        </div>

                        <div class="flex-1">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">
                                        <?php echo htmlspecialchars($materia['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </h2>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <?php echo htmlspecialchars($materia['descricao'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400 flex-shrink-0 group-hover:text-<?php echo $materia['cor']; ?>-600 transition-colors">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>

                            <!-- TÓPICOS -->
                            <div class="materia-topics">
                                <?php foreach ($materia['tópicos'] as $topico): ?>
                                <span class="topic-tag bg-<?php echo $materia['cor']; ?>-100 text-<?php echo $materia['cor']; ?>-700">
                                    <?php echo htmlspecialchars($topico, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php endforeach; ?>
                            </div>

                            <!-- STATS -->
                            <div class="flex gap-3 mt-4">
                                <span class="stats-badge text-<?php echo $materia['cor']; ?>-700 bg-<?php echo $materia['cor']; ?>-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M9 11l3 3L22 4"></path>
                                    </svg>
                                    <?php echo $materia['exercicios']; ?> exercícios
                                </span>
                                <span class="stats-badge text-<?php echo $materia['cor']; ?>-700 bg-<?php echo $materia['cor']; ?>-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                                    </svg>
                                    <?php echo $materia['aulas']; ?> aulas
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

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

    // BUSCA
    const searchInput = document.getElementById('searchInput');
    const materiasContainer = document.getElementById('materiasContainer');
    const materiaCards = materiasContainer.querySelectorAll('.materia-card');

    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        
        materiaCards.forEach(card => {
            const title = card.querySelector('h2').textContent.toLowerCase();
            const topics = card.querySelectorAll('.topic-tag');
            let topicsMatch = false;

            topics.forEach(topic => {
                if (topic.textContent.toLowerCase().includes(searchTerm)) {
                    topicsMatch = true;
                }
            });

            if (title.includes(searchTerm) || topicsMatch || searchTerm === '') {
                card.style.display = 'block';
                card.style.opacity = '1';
                card.style.animation = 'slideIn 0.3s ease-out';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // FILTROS
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-blue-500', 'text-white');
                btn.classList.add('bg-white', 'border', 'border-gray-200');
            });
            
            button.classList.remove('bg-white', 'border', 'border-gray-200');
            button.classList.add('bg-blue-500', 'text-white');
            
            if (filter === 'all') {
                materiaCards.forEach(card => {
                    card.style.display = 'block';
                });
            } else if (filter === 'popular') {
                materiaCards.forEach(card => {
                    const exercicios = parseInt(card.querySelector('.stats-badge').textContent);
                    card.style.display = exercicios > 45 ? 'block' : 'none';
                });
            } else if (filter === 'novo') {
                // Mostrar últimas 3 matérias
                materiaCards.forEach((card, index) => {
                    card.style.display = index >= materiaCards.length - 3 ? 'block' : 'none';
                });
            }
        });
    });
});
</script>

</body>
</html>