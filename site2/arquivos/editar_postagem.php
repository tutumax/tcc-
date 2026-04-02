<?php
require_once 'session.php';
require_once 'conexao.php';

// Apenas professores podem editar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_role'] !== 'professor') {
    header("Location: dashboard.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: dashboard.php");
    exit;
}

// Buscar a postagem
$stmt = $pdo->prepare("SELECT * FROM postagens WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$postagem = $stmt->fetch();

// Verificar se existe e se pertence ao professor
if (!$postagem || $postagem['professor_id'] != $_SESSION['usuario_id']) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $conteudo = isset($_POST['conteudo']) ? trim($_POST['conteudo']) : '';

    // Validação
    if (empty($titulo)) {
        $erro = 'Título é obrigatório.';
    } elseif (strlen($titulo) < 5) {
        $erro = 'Título deve ter no mínimo 5 caracteres.';
    } elseif (empty($conteudo)) {
        $erro = 'Conteúdo é obrigatório.';
    } elseif (strlen($conteudo) < 20) {
        $erro = 'Conteúdo deve ter no mínimo 20 caracteres.';
    } else {
        try {
            // Atualizar postagem
            $stmtUpdate = $pdo->prepare("
                UPDATE postagens 
                SET titulo = :titulo, conteudo = :conteudo
                WHERE id = :id
            ");
            $stmtUpdate->execute([
                ':titulo' => $titulo,
                ':conteudo' => $conteudo,
                ':id' => $id
            ]);

            $sucesso = 'Postagem atualizada com sucesso!';
            
            // Atualizar $postagem local
            $postagem['titulo'] = $titulo;
            $postagem['conteudo'] = $conteudo;

            // Redirecionar após 2 segundos
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 2000);
            </script>";
        } catch (PDOException $e) {
            $erro = 'Erro ao atualizar postagem. Tente novamente.';
            error_log('Erro editar_postagem.php: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title>Editar Postagem — EduShare</title>
</head>
<body class="bg-gray-50 text-gray-800">
    <?php include __DIR__ . '/nav.php'; ?>
    <main class="max-w-3xl mx-auto p-8 mt-20">
        <div class="bg-white rounded-3xl shadow-lg p-10" data-aos="fade-up">
            <h1 class="text-3xl font-black text-gray-900 mb-2">Editar Postagem</h1>
            <p class="text-gray-600 mb-8">Atualize seu exercício ou material.</p>

            <?php if ($erro): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded mb-6">
                    <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded mb-6">
                    <?php echo htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Título *</label>
                    <input 
                        type="text"
                        name="titulo"
                        value="<?php echo htmlspecialchars($postagem['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Ex: Exercícios de Álgebra - Capítulo 3"
                        class="w-full p-4 rounded-2xl border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-transparent outline-none transition"
                        required
                    >
                    <p class="text-xs text-gray-500 mt-1">Mínimo: 5 caracteres</p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Conteúdo *</label>
                    <textarea 
                        name="conteudo"
                        rows="12"
                        placeholder="Descreva o exercício, forneça instruções e exemplos..."
                        class="w-full p-4 rounded-2xl border border-gray-300 focus:ring-2 focus:ring-blue-400 focus:border-transparent outline-none transition resize-none"
                        required
                    ><?php echo htmlspecialchars($postagem['conteudo'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Mínimo: 20 caracteres</p>
                </div>

                <div class="flex gap-4">
                    <button 
                        type="submit"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-3xl shadow transition"
                    >
                        Salvar Alterações
                    </button>
                    <a 
                        href="dashboard.php"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-6 rounded-3xl shadow transition text-center"
                    >
                        Cancelar
                    </a>
                </div>
            </form>

            <div class="mt-8 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500">Postagem criada em: <?php echo date('d/m/Y H:i', strtotime($postagem['created_at'])); ?></p>
                <p class="text-sm text-gray-500">Última atualização: <?php echo date('d/m/Y H:i', strtotime($postagem['updated_at'])); ?></p>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({duration:600});</script>
</body>
</html>
