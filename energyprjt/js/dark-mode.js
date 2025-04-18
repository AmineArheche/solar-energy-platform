// Fonction pour initialiser le mode sombre
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const sunIcon = document.getElementById('sunIcon');
    const moonIcon = document.getElementById('moonIcon');

    // Vérifier la préférence stockée
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Appliquer le mode sombre si nécessaire
    if (isDarkMode) {
        document.documentElement.classList.add('dark');
        sunIcon.classList.remove('hidden');
        moonIcon.classList.add('hidden');
    } else {
        document.documentElement.classList.remove('dark');
        sunIcon.classList.add('hidden');
        moonIcon.classList.remove('hidden');
    }

    // Gérer le clic sur le bouton
    darkModeToggle.addEventListener('click', () => {
        const isDark = document.documentElement.classList.toggle('dark');
        
        // Mettre à jour les icônes
        if (isDark) {
            sunIcon.classList.remove('hidden');
            moonIcon.classList.add('hidden');
        } else {
            sunIcon.classList.add('hidden');
            moonIcon.classList.remove('hidden');
        }

        // Sauvegarder la préférence
        localStorage.setItem('darkMode', isDark);
    });
}

// Initialiser le mode sombre au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const sunIcon = document.getElementById('sunIcon');
    const moonIcon = document.getElementById('moonIcon');

    // Vérifier la préférence enregistrée
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
        sunIcon.classList.add('hidden');
        moonIcon.classList.remove('hidden');
    }

    // Gérer le clic sur le bouton
    darkModeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        
        // Basculer les icônes
        sunIcon.classList.toggle('hidden');
        moonIcon.classList.toggle('hidden');
        
        // Enregistrer la préférence
        localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    });
}); 