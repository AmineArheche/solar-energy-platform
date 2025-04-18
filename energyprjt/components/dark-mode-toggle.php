<!-- Bouton de basculement du mode sombre -->
<button id="darkModeToggle" class="flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-200" aria-label="Basculer le mode sombre">
    <!-- Icône soleil (mode clair) -->
    <svg id="sunIcon" class="w-5 h-5 text-gray-800 dark:text-gray-200 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>
    <!-- Icône lune (mode sombre) -->
    <svg id="moonIcon" class="w-5 h-5 text-gray-800 dark:text-gray-200 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
    </svg>
</button>

<!-- Script pour initialiser le mode sombre -->
<script>
    // Vérifier si le mode sombre est activé dans le localStorage
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    // Mettre à jour l'icône en fonction du mode actuel
    function updateIcon() {
        const isDark = document.documentElement.classList.contains('dark');
        document.getElementById('sunIcon').classList.toggle('hidden', isDark);
        document.getElementById('moonIcon').classList.toggle('hidden', !isDark);
    }

    // Initialiser l'icône
    updateIcon();

    // Gérer le clic sur le bouton
    document.getElementById('darkModeToggle').addEventListener('click', function() {
        document.documentElement.classList.toggle('dark');
        localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        updateIcon();
    });
</script> 