<?php
session_start();
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['user']) ? trim($_POST['user']) : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';

    // Credenciais fixas
    if ($user === 'admin' && $pass === '1234') {
        $_SESSION['is_admin'] = true;
        $_SESSION['admin_user'] = $user;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Usuário ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Login Admin — EduShare</title>
</head>
<body class="bg-white text-gray-800">
    <div class="min-h-screen flex items-center justify-center px-6 py-12">
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8">
            <h1 class="text-2xl font-black text-gray-900 mb-2 text-center">Área Administrativa</h1>
            <p class="text-center text-gray-500 mb-6">Acesso restrito para administradores.</p>

            <?php if ($error !== ''): ?>
                <div class="bg-red-50 text-red-700 px-4 py-3 rounded mb-4 text-sm"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" class="grid gap-4">
                <input name="user" placeholder="Usuário" value="<?php echo isset($_POST['user']) ? htmlspecialchars($_POST['user']) : ''; ?>" class="w-full p-3 rounded-2xl border border-gray-300 focus:ring-2 focus:ring-blue-400 outline-none" required>
                <input name="pass" type="password" placeholder="Senha" class="w-full p-3 rounded-2xl border border-gray-300 focus:ring-2 focus:ring-blue-400 outline-none" required>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 rounded-3xl shadow transition">Entrar</button>
            </form>

            <p class="text-center text-xs text-gray-500 mt-6">Usuário: <strong>admin</strong><br>Senha: <strong>1234</strong></p>
        </div>
    </div>
</body>
</html>
