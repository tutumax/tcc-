<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require_once '../arquivos/conexao.php';

// Tratar ações (aprovar professor)
if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    try {
        // Só atualiza se coluna `aprovado` existir
        $hasAprovado = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'aprovado'")->fetch();
        if ($hasAprovado) {
            $u = $pdo->prepare("UPDATE usuarios SET aprovado = 1 WHERE id = :id");
            $u->execute([':id' => $id]);
            header('Location: admin_dashboard.php');
            exit;
        } else {
            $errorMsg = 'Coluna `aprovado` não encontrada na tabela `usuarios`.';
        }
    } catch (Exception $e) {
        $errorMsg = 'Erro ao aprovar usuário: ' . $e->getMessage();
    }
}

// Carregar pendências (se as colunas existirem)
$pending = [];
$columnsOk = true;
try {
    $hasRole = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'role'")->fetch();
    $hasAprovado = (bool) $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'aprovado'")->fetch();
    if ($hasRole && $hasAprovado) {
        $q = $pdo->prepare("SELECT id, nome, email FROM usuarios WHERE role = 'professor' AND (aprovado IS NULL OR aprovado != 1)");
        $q->execute();
        $pending = $q->fetchAll();
    } else {
        $columnsOk = false;
    }
} catch (Exception $e) {
    $columnsOk = false;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard Admin — EduShare</title>
</head>
<body class="bg-white text-gray-800">
    <main class="max-w-4xl mx-auto p-8 mt-20">
        <div class="bg-blue-50 rounded-3xl p-8 shadow-inner">
            <h2 class="text-2xl font-black text-gray-900">Bem-vindo, <?php echo htmlspecialchars($_SESSION['admin_user'], ENT_QUOTES, 'UTF-8'); ?>!</h2>
            <p class="text-gray-600 mt-4">Esta área é restrita apenas ao administrador.</p>

            <div class="mt-6 space-y-3">
                <a class="inline-block bg-white border border-blue-500 text-blue-500 px-4 py-2 rounded-2xl" href="../arquivos/dashboard.php">Ver Dashboard do Usuário</a>
                <a class="inline-block bg-red-500 text-white px-4 py-2 rounded-2xl" href="admin_logout.php">Encerrar Sessão Admin</a>
            </div>

            <div class="mt-8">
                <h3 class="text-lg font-bold mb-3">Professores pendentes de aprovação</h3>
                <?php if (isset($errorMsg)): ?>
                    <div class="bg-red-50 text-red-700 px-4 py-3 rounded mb-4"><?php echo htmlspecialchars($errorMsg); ?></div>
                <?php endif; ?>

                <?php if (!$columnsOk): ?>
                    <div class="bg-yellow-50 text-yellow-800 px-4 py-3 rounded mb-4">
                        As colunas `role` e/ou `aprovado` não existem na tabela `usuarios`.<br>
                        Para habilitar aprovação de professores, adicione-as no banco com os comandos SQL abaixo (execute no seu gerenciador de banco):
                        <pre class="mt-2 text-xs bg-white p-3 rounded">ALTER TABLE usuarios ADD COLUMN role VARCHAR(20) DEFAULT 'aluno';
ALTER TABLE usuarios ADD COLUMN aprovado TINYINT(1) DEFAULT 1;</pre>
                    </div>
                <?php else: ?>
                    <?php if (count($pending) === 0): ?>
                        <p class="text-gray-600">Nenhum professor pendente.</p>
                    <?php else: ?>
                        <ul class="space-y-2">
                            <?php foreach ($pending as $p): ?>
                                <li class="flex items-center justify-between bg-white p-3 rounded shadow">
                                    <div>
                                        <div class="font-bold"><?php echo htmlspecialchars($p['nome']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($p['email']); ?></div>
                                    </div>
                                    <div>
                                        <a href="admin_dashboard.php?action=approve&id=<?php echo intval($p['id']); ?>" class="bg-green-500 text-white px-3 py-2 rounded">Aprovar</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
