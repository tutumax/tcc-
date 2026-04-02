<?php
require_once 'session.php';
require_once 'conexao.php';

// Se não houver sessão, bloqueia o acesso
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar role do usuário
$isProf = (isset($_SESSION['usuario_role']) && $_SESSION['usuario_role'] === 'professor');

// Buscar postagens com tratamento de erro
$postagens = [];
try {
    $stmtPostagens = $pdo->prepare("
        SELECT p.id, p.titulo, p.conteudo, p.created_at, p.professor_id, u.nome as professor_nome 
        FROM postagens p
        JOIN usuarios u ON p.professor_id = u.id
        ORDER BY p.created_at DESC
        LIMIT 20
    ");
    $stmtPostagens->execute();
    $postagens = $stmtPostagens->fetchAll();
} catch (PDOException $e) {
    // Tabela postagens não existe ainda
    $postagens = [];
    $tableError = true;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>Dashboard — EduShare</title>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/nav.php'; ?>
    <main class="max-w-5xl mx-auto p-8 mt-20">
        <!-- Seção de Boas-vindas -->
        <div class="bg-blue-50 rounded-3xl p-8 shadow-inner mb-8" data-aos="fade-up">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-black text-gray-900">Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
                    <p class="text-gray-600 mt-2">
                        <?php echo $isProf ? 'Compartilhe seus exercícios com os alunos.' : 'Acompanhe os exercícios dos professores.'; ?>
                    </p>
                </div>
                <?php if ($isProf): ?>
                    <a href="criar_postagem.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-3xl shadow transition">
                        + Nova Postagem
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Postagens -->
        <div class="space-y-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Postagens Recentes</h3>

            <?php if (isset($tableError) && $tableError): ?>
                <div class="bg-yellow-50 rounded-3xl p-8 shadow border-l-4 border-yellow-500">
                    <p class="text-yellow-800 font-semibold">Tabela de postagens não encontrada</p>
                    <p class="text-yellow-700 mt-2 text-sm">Execute o arquivo <strong>database.sql</strong> no seu banco de dados MySQL para criar as tabelas necessárias.</p>
                    <p class="text-yellow-700 text-sm mt-2">Via phpMyAdmin: Import → Selecione database.sql → Go</p>
                </div>
            <?php elseif (empty($postagens)): ?>
                <div class="bg-white rounded-3xl p-12 shadow text-center">
                    <p class="text-gray-500 text-lg">Nenhuma postagem ainda. Volte em breve!</p>
                </div>
            <?php else: ?>
                <?php foreach ($postagens as $post): ?>
                    <div class="bg-white rounded-3xl p-8 shadow-md hover:shadow-lg transition" data-aos="fade-up">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($post['titulo'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p class="text-sm text-gray-500 mt-1">
                                    Por <strong><?php echo htmlspecialchars($post['professor_nome'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    • <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                </p>
                            </div>
                            <?php if ($isProf && $_SESSION['usuario_id'] == $post['professor_id']): ?>
                                <a href="editar_postagem.php?id=<?php echo $post['id']; ?>" class="text-blue-500 hover:text-blue-700 text-sm font-semibold">
                                    Editar
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="text-gray-700 leading-relaxed mb-4">
                            <?php echo nl2br(htmlspecialchars($post['conteudo'], ENT_QUOTES, 'UTF-8')); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({duration:600});</script>
</body>
</html>