<?php require_once 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - SolarTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-blue-600">
                        <i class="fas fa-solar-panel mr-2"></i>
                        SolarTech
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-1"></i>
                        Accueil
                    </a>
                  
                    <a href="about.php" class="text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-info-circle mr-1"></i>
                        À propos
                    </a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-envelope mr-1"></i>
                        Contact
                    </a>
                  
                        <a href="register.php" class="bg-green-600 text-white px-3 py-2 rounded-md text-base font-medium flex items-center">
                            <i class="fas fa-user-plus mr-2"></i>S'inscrire
                        </a>
                        
                    
                
                </div>
                <div class="md:hidden flex items-center">
                    <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-blue-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <span class="sr-only">Open main menu</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="index.php" class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-home mr-1"></i>
                    Accueil
                </a>
                <a href="index.php#products" class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-box mr-1"></i>
                    Produits
                </a>
                <a href="about.php" class="text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-info-circle mr-1"></i>
                    À propos
                </a>
                <a href="contact.php" class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-envelope mr-1"></i>
                    Contact
                </a>
                <a href="admin/login.php" class="bg-blue-600 text-white hover:bg-blue-700 block px-3 py-2 rounded-md text-base font-medium">
                    <i class="fas fa-user mr-1"></i>
                    Espace administrateur
                </a>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-4xl font-bold text-gray-900 mb-8 text-center">À propos de SolarTech</h1>
            
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="p-8">
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Notre Mission</h2>
                        <p class="text-gray-600 leading-relaxed">
                            Chez SolarTech, notre mission est de rendre l'énergie solaire accessible à tous. Nous nous engageons à fournir des solutions solaires de haute qualité pour un avenir plus durable et écologique.
                        </p>
                    </div>

                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Notre Expertise</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-start">
                                <i class="fas fa-solar-panel text-blue-600 text-2xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Produits de Qualité</h3>
                                    <p class="text-gray-600 mt-2">Nous sélectionnons rigoureusement nos produits pour garantir performance et durabilité.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-tools text-blue-600 text-2xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Installation Professionnelle</h3>
                                    <p class="text-gray-600 mt-2">Notre équipe d'experts assure une installation conforme aux normes.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-headset text-blue-600 text-2xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Service Client</h3>
                                    <p class="text-gray-600 mt-2">Un support technique et un service après-vente dédiés.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-leaf text-blue-600 text-2xl mt-1"></i>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Engagement Écologique</h3>
                                    <p class="text-gray-600 mt-2">Nous contribuons activement à la transition énergétique.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">Nos Valeurs</h2>
                        <ul class="space-y-4 text-gray-600">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span>Qualité et fiabilité dans tous nos produits et services</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span>Innovation et amélioration continue</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span>Respect de l'environnement et développement durable</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                <span>Satisfaction client et service personnalisé</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact Information -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Contactez-nous
                    </h3>
                    <div class="space-y-3">
                        <p class="flex items-center text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fas fa-phone text-blue-600 mr-3"></i>
                            <a href="tel:+212651441563">+212 651 441 563</a>
                        </p>
                        <p class="flex items-center text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fas fa-phone text-blue-600 mr-3"></i>
                            <a href="tel:+212662611909">+212 662 611 909</a>
                        </p>
                        <p class="flex items-center text-gray-600 hover:text-blue-600 transition duration-300">
                            <i class="fas fa-envelope text-blue-600 mr-3"></i>
                            <a href="mailto:elyamanihamza@gmail.com">elyamanihamza@gmail.com</a>
                        </p>
                    </div>
                </div>

                <!-- Company Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-building text-blue-600 mr-2"></i>
                        Notre équipe
                    </h3>
                    <div class="space-y-3">
                        <p class="flex items-center text-gray-600">
                            <i class="fas fa-user text-blue-600 mr-3"></i>
                            Fondateur: Hassan El Yamani
                        </p>
                        <p class="flex items-center text-gray-600">
                            <i class="fas fa-user-tie text-blue-600 mr-3"></i>
                            Chef de projet: Hamza El Yamani
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-link text-blue-600 mr-2"></i>
                        Liens rapides
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="about.php" class="text-gray-600 hover:text-blue-600 transition duration-300 flex items-center">
                                <i class="fas fa-chevron-right mr-2"></i>
                                À propos de nous
                            </a>
                        </li>
                        <li>
                            <a href="contact.php" class="text-gray-600 hover:text-blue-600 transition duration-300 flex items-center">
                                <i class="fas fa-chevron-right mr-2"></i>
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-200 mt-8 pt-8 text-center">
                <p class="text-gray-500">
                    © <?php echo date('Y'); ?> Réparation d'énergie solaire et éolienne - Tous droits réservés
                </p>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.querySelector('.mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        });
    </script>
</body>
</html> 