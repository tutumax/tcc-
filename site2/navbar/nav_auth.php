<?php
// Navbar para páginas de autenticação (login, registrar, etc)
// Apenas logo e botão home
?>
<nav class="fixed top-0 left-0 w-full bg-white z-50 shadow-md transition-all duration-300">
    <div class="w-full px-6 md:px-10 py-4 flex justify-between items-center">
        <!-- Logo -->
        <a href="../../index.html" class="text-2xl font-black tracking-tighter text-gray-900">
            Educa<span class="text-blue-500">TEC</span>
        </a>

        <!-- Home Button -->
        <a href="../../index.html" class="p-2.5 bg-blue-500 hover:bg-blue-600 rounded-full text-white transition-all shadow-lg" title="Home">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 1 1 18 0 9 9 0 0 1-18 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 10l3-3m0 0l3 3m-3-3v12" />
            </svg>
        </a>
    </div>
</nav>
