<?php
// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Récupérer les informations de l'utilisateur de manière sécurisée
$userInitials = '';
$userName = '';
$userEmail = '';

if ($isLoggedIn) {
    $firstname = $_SESSION['firstname'] ?? '';
    $lastname = $_SESSION['lastname'] ?? '';
    $userEmail = $_SESSION['email'] ?? '';
    
    $userInitials = substr($firstname, 0, 1) . substr($lastname, 0, 1);
    $userName = $firstname . ' ' . $lastname;
}
?>

<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <a href="../index.php" class="text-xl font-bold text-gray-800">
                    SolarTech
                </a>
            </div>
            
            <div class="flex items-center">
                <?php if ($isLoggedIn): ?>
                    <div class="relative">
                        <button type="button" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none" id="user-menu-button">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-500">
                                <span class="text-sm font-medium leading-none text-white">
                                    <?php echo htmlspecialchars($userInitials); ?>
                                </span>
                            </span>
                            <span class="hidden md:block text-sm font-medium">
                                <?php echo htmlspecialchars($userName); ?>
                            </span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" id="user-menu">
                            <?php if (!empty($userEmail)): ?>
                            <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                <div class="font-medium"><?php echo htmlspecialchars($userEmail); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <a href="../profile/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Mon profil
                            </a>
                            <a href="../profile/messages.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-envelope mr-2"></i>Mes messages
                            </a>
                            <a href="../profile/orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-shopping-cart mr-2"></i>Mes commandes
                            </a>
                            <a href="../profile/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Paramètres
                            </a>
                            <?php if ($isAdmin): ?>
                                <div class="border-t border-gray-100"></div>
                                <a href="../admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tachometer-alt mr-2"></i>Administration
                                </a>
                            <?php endif; ?>
                            <div class="border-t border-gray-100"></div>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex space-x-4">
                        <a href="../login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fas fa-sign-in-alt mr-2"></i>Se connecter
                        </a>
                        <a href="../register.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                            <i class="fas fa-user-plus mr-2"></i>S'inscrire
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    // Gestion du menu utilisateur
    const userMenuButton = document.getElementById('user-menu-button');
    const userMenu = document.getElementById('user-menu');
    
    if (userMenuButton && userMenu) {
        userMenuButton.addEventListener('click', () => {
            userMenu.classList.toggle('hidden');
        });
        
        // Fermer le menu quand on clique ailleurs
        document.addEventListener('click', (event) => {
            if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                userMenu.classList.add('hidden');
            }
        });
    }
</script> 