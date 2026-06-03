<?php
// Navbar para dashboards - Apenas logo e logout
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$isLogado = isset($_SESSION['usuario_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
?>
<nav class="fixed top-0 left-0 w-full bg-white z-50 shadow-md transition-all duration-300">
    <div class="w-full px-6 md:px-10 py-4 flex justify-between items-center">
        <!-- Logo -->
        <a href="<?php echo $isAdmin ? 'admin_dashboard.php' : 'dashboard.php'; ?>" class="text-2xl font-black tracking-tighter text-gray-900">
            Educa<span class="text-blue-500">TEC</span>
        </a>

        <!-- Logout Button -->
        <?php if ($isLogado): ?>
            <a href="<?php echo $isAdmin ? 'admin_logout.php' : '../login/logout.php'; ?>" class="p-2.5 bg-red-500 hover:bg-red-600 rounded-full text-white transition-all shadow-lg" title="Sair">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg> 
            </a>
        <?php endif; ?>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}
</script>