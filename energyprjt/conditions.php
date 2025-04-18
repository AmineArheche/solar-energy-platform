<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions d'Utilisation - RéparationÉnergie</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-md fixed w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-sun text-2xl text-blue-600"></i>
                    <i class="fas fa-wind text-2xl text-blue-600"></i>
                    <h1 class="text-xl font-bold text-gray-800">Réparation<span class="text-blue-600">Énergie</span></h1>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="index.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                        <i class="fas fa-home"></i>
                        <span>Accueil</span>
                    </a>
                    <a href="about.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                        <i class="fas fa-info-circle"></i>
                        <span>À propos</span>
                    </a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                    <a href="register.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center space-x-2">
                        <i class="fas fa-user-plus"></i>
                        <span>S'inscrire</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-gavel mr-3"></i>
                            Conditions d'Utilisation
                        </h2>
                        <a href="register.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Retour à l'inscription</span>
                        </a>
                    </div>
                    <p class="text-blue-100 mt-2">
                        Nos règles et conditions d'utilisation du service
                    </p>
                </div>

                <div class="p-8 space-y-6">
                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">1. Acceptation des conditions</h3>
                        <p class="text-gray-600">
                            En accédant et en utilisant ce site, vous acceptez d'être lié par ces conditions d'utilisation, toutes les lois et réglementations applicables.
                        </p>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">2. Utilisation du service</h3>
                        <p class="text-gray-600">
                            Nos services sont destinés à un usage personnel et professionnel. Vous vous engagez à :
                        </p>
                        <ul class="list-disc pl-6 text-gray-600 space-y-2">
                            <li>Ne pas utiliser le service à des fins illégales</li>
                            <li>Ne pas perturber ou interrompre le service</li>
                            <li>Ne pas tenter d'accéder à des zones restreintes</li>
                            <li>Respecter les droits des autres utilisateurs</li>
                        </ul>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">3. Comptes utilisateurs</h3>
                        <p class="text-gray-600">
                            Pour certains services, vous devrez créer un compte. Vous êtes responsable de :
                        </p>
                        <ul class="list-disc pl-6 text-gray-600 space-y-2">
                            <li>Maintenir la confidentialité de votre compte</li>
                            <li>Toutes les activités sur votre compte</li>
                            <li>Mettre à jour vos informations personnelles</li>
                            <li>Nous informer de toute utilisation non autorisée</li>
                        </ul>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">4. Propriété intellectuelle</h3>
                        <p class="text-gray-600">
                            Le contenu du site (textes, images, logos, etc.) est protégé par le droit d'auteur. Vous ne pouvez pas :
                        </p>
                        <ul class="list-disc pl-6 text-gray-600 space-y-2">
                            <li>Copier ou reproduire le contenu sans autorisation</li>
                            <li>Utiliser le contenu à des fins commerciales</li>
                            <li>Modifier ou créer des œuvres dérivées</li>
                        </ul>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">5. Limitation de responsabilité</h3>
                        <p class="text-gray-600">
                            Nous nous efforçons de maintenir les informations à jour et exactes, mais :
                        </p>
                        <ul class="list-disc pl-6 text-gray-600 space-y-2">
                            <li>Le service est fourni "tel quel"</li>
                            <li>Nous ne garantissons pas l'exactitude des informations</li>
                            <li>Nous ne sommes pas responsables des dommages indirects</li>
                        </ul>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">6. Modifications</h3>
                        <p class="text-gray-600">
                            Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications prennent effet dès leur publication sur le site.
                        </p>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-xl font-semibold text-gray-900">7. Contact</h3>
                        <p class="text-gray-600">
                            Pour toute question concernant ces conditions, contactez-nous :
                        </p>
                        <ul class="list-none space-y-2 text-gray-600">
                            <li class="flex items-center">
                                <i class="fas fa-envelope text-blue-600 mr-2"></i>
                                <a href="mailto:elyamanihamza@gmail.com" class="text-blue-600 hover:underline">elyamanihamza@gmail.com</a>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-phone text-blue-600 mr-2"></i>
                                <a href="tel:+212651441563" class="text-blue-600 hover:underline">+212 651 441 563</a>
                            </li>
                        </ul>
                    </section>
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
</body>
</html> 