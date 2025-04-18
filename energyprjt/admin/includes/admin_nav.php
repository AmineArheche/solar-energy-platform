<?php
// Navigation pour l'administration
?>
<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="dashboard.php" class="text-gray-800 hover:text-gray-600">
                        <i class="fas fa-home mr-2"></i>Tableau de bord
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="products.php" class="text-gray-800 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-box mr-1"></i>Produits
                    </a>
                    <a href="categories.php" class="text-gray-800 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-tags mr-1"></i>Catégories
                    </a>
                    <a href="messages.php" class="text-gray-800 hover:text-gray-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-envelope mr-1"></i>Messages
                    </a>
                </div>
            </div>
            <div class="flex items-center">
                <a href="../logout.php" class="text-gray-800 hover:text-gray-600">
                    <i class="fas fa-sign-out-alt mr-1"></i>Déconnexion
                </a>
            </div>
        </div>
    </div>
</nav> 