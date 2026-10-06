<?php
if (function_exists('apply_security_headers')) apply_security_headers();

session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de confidentialité - EnergyPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
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
                    <a href="index.php#products" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                        <i class="fas fa-box"></i>
                        <span>Produits</span>
                    </a>
                    <a href="about.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                        <i class="fas fa-info-circle"></i>
                        <span>À propos</span>
                    </a>
                    <a href="contact.php" class="text-gray-700 hover:text-blue-600 flex items-center space-x-2">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-user"></i>
                            <span>Mon Profil</span>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Connexion</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8">
                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-shield-alt mr-3"></i>
                            Politique de Confidentialité
                        </h2>
                        <a href="register.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50 transition duration-300 flex items-center space-x-2">
                            <i class="fas fa-arrow-left"></i>
                            <span>Retour à l'inscription</span>
                        </a>
                    </div>
                    <p class="text-blue-100 mt-2">
                        Comment nous protégeons vos données personnelles
                    </p>
                </div>

                <div class="p-8 space-y-8">
                    <div class="prose max-w-none">
                        <p class="text-gray-600 mb-6">
                            Cette politique explique comment nous collectons, utilisons et protégeons vos données personnelles sur notre plateforme EnergyPro.
                        </p>

                        <div class="space-y-8">
                            <section>
                                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-database text-blue-600 mr-2"></i>
                                    1. Données collectées
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <p class="text-gray-600">
                                        Nous collectons les informations suivantes :
                                    </p>
                                    <ul class="list-disc list-inside text-gray-600 ml-4 space-y-2">
                                        <li>Adresse e-mail (pour l'authentification et la communication)</li>
                                        <li>Mot de passe (stocké de manière sécurisée)</li>
                                        <li>Nom et prénom (optionnel)</li>
                                        <li>Données de calcul d'énergie (pour améliorer nos services)</li>
                                        <li>Historique des consultations</li>
                                    </ul>
                                </div>
                            </section>

                            <section>
                                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-cogs text-blue-600 mr-2"></i>
                                    2. Utilisation des données
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <p class="text-gray-600">
                                        Vos données sont utilisées pour :
                                    </p>
                                    <ul class="list-disc list-inside text-gray-600 ml-4 space-y-2">
                                        <li>Assurer l'accès sécurisé à votre compte</li>
                                        <li>Personnaliser votre expérience utilisateur</li>
                                        <li>Améliorer nos services et algorithmes de calcul</li>
                                        <li>Vous envoyer des notifications importantes</li>
                                        <li>Maintenir la sécurité de notre plateforme</li>
                                    </ul>
                                </div>
                            </section>

                            <section>
                                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-lock text-blue-600 mr-2"></i>
                                    3. Sécurité des données
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <p class="text-gray-600">
                                        Nous mettons en place plusieurs mesures de sécurité :
                                    </p>
                                    <ul class="list-disc list-inside text-gray-600 ml-4 space-y-2">
                                        <li>Chiffrement des données sensibles</li>
                                        <li>Hachage sécurisé des mots de passe</li>
                                        <li>Protection contre les attaques par force brute</li>
                                        <li>Surveillance continue des accès</li>
                                        <li>Sauvegardes régulières des données</li>
                                    </ul>
                                </div>
                            </section>

                            <section>
                                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-user-shield text-blue-600 mr-2"></i>
                                    4. Droits des utilisateurs
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <p class="text-gray-600">
                                        Conformément au RGPD, vous disposez des droits suivants :
                                    </p>
                                    <ul class="list-disc list-inside text-gray-600 ml-4 space-y-2">
                                        <li>Droit d'accès à vos données</li>
                                        <li>Droit de rectification</li>
                                        <li>Droit à l'effacement</li>
                                        <li>Droit à la portabilité des données</li>
                                        <li>Droit d'opposition au traitement</li>
                                    </ul>
                                </div>
                            </section>

                            <section>
                                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                    <i class="fas fa-cookie text-blue-600 mr-2"></i>
                                    5. Cookies et traceurs
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <p class="text-gray-600">
                                        Notre site utilise des cookies pour :
                                    </p>
                                    <ul class="list-disc list-inside text-gray-600 ml-4 space-y-2">
                                        <li>Maintenir votre session active</li>
                                        <li>Mémoriser vos préférences</li>
                                        <li>Améliorer la performance du site</li>
                                        <li>Analyser l'utilisation du site</li>
                                    </ul>
                                </div>
                            </section>
                        </div>

                        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-blue-600">
                                <i class="fas fa-info-circle mr-2"></i>
                                Pour toute question concernant notre politique de confidentialité, veuillez nous contacter via notre page de contact.
                            </p>
                        </div>
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
</body>
</html> 