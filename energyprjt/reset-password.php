<?php
session_start();
require_once 'config/database.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger la configuration SMTP
$mailConfig = require 'config/mail.php';

// Initialisation des variables
$message = '';
$error = '';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    // Validation de l'email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez entrer une adresse email valide.";
    } else {
        try {
            // Vérifier si l'email existe dans la base de données
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 3600); // Expire dans 1 heure
                
                // Stocker le token dans la base de données
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);
                
                // Préparer le lien de réinitialisation
                $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/new-password.php?token=" . $token;
                
                try {
                    // Configuration de PHPMailer
                    $mail = new PHPMailer(true);
                    
                    // Configuration du serveur SMTP
                    $mail->isSMTP();
                    $mail->Host = $mailConfig['smtp']['host'];
                    $mail->SMTPAuth = true;
                    $mail->Username = $mailConfig['smtp']['username'];
                    $mail->Password = $mailConfig['smtp']['password'];
                    $mail->SMTPSecure = $mailConfig['smtp']['encryption'];
                    $mail->Port = $mailConfig['smtp']['port'];
                    $mail->CharSet = 'UTF-8';
                    
                    // Activer le débogage SMTP
                    $mail->SMTPDebug = 2;
                    $mail->Debugoutput = function($str, $level) {
                        error_log("SMTP Debug: $str");
                    };
                    
                    // Destinataires
                    $mail->setFrom($mailConfig['smtp']['from']['email'], $mailConfig['smtp']['from']['name']);
                    $mail->addAddress($email);
                    
                    // Contenu
                    $mail->isHTML(true);
                    $mail->Subject = 'Réinitialisation de votre mot de passe - RéparationÉnergie';
                    $mail->Body = "
                    <html>
                    <head>
                        <title>Réinitialisation de mot de passe</title>
                    </head>
                    <body>
                        <div style='max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>
                            <div style='background-color: #1a56db; color: white; padding: 20px; text-align: center;'>
                                <h1>RéparationÉnergie</h1>
                            </div>
                            <div style='background-color: #f8fafc; padding: 20px; border: 1px solid #e2e8f0;'>
                                <h2>Réinitialisation de votre mot de passe</h2>
                                <p>Bonjour,</p>
                                <p>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez cliquer sur le lien ci-dessous pour créer un nouveau mot de passe :</p>
                                <p style='text-align: center;'>
                                    <a href='$reset_link' style='display: inline-block; background-color: #1a56db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Réinitialiser mon mot de passe</a>
                                </p>
                                <p>Ce lien expirera dans 1 heure.</p>
                                <p>Si vous n'avez pas fait cette demande, veuillez ignorer cet email.</p>
                                <p>Cordialement,<br>L'équipe RéparationÉnergie</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ";
                    
                    // Envoyer l'email
                    if ($mail->send()) {
                        $message = "Un email contenant les instructions de réinitialisation de mot de passe a été envoyé à votre adresse email.";
                    } else {
                        $error = "Impossible d'envoyer l'email. Veuillez réessayer plus tard.";
                        error_log("Échec d'envoi d'email à $email");
                    }
                } catch (Exception $e) {
                    $error = "Erreur lors de l'envoi de l'email : " . $e->getMessage();
                    error_log("Erreur PHPMailer: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                }
            } else {
                // Ne pas révéler si l'email existe ou non pour des raisons de sécurité
                $message = "Si cette adresse email est associée à un compte, vous recevrez un lien de réinitialisation de mot de passe.";
            }
        } catch (PDOException $e) {
            $error = "Erreur de base de données : " . $e->getMessage();
            error_log("Erreur PDO: " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - RéparationÉnergie</title>
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

    <!-- Reset Password Form -->
    <div class="pt-24 pb-16">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-6 px-8">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-key mr-3"></i>
                        Réinitialisation de mot de passe
                    </h2>
                    <p class="text-blue-100 mt-2">
                        Entrez votre adresse email pour recevoir un lien de réinitialisation
                    </p>
                </div>

                <?php if (!empty($error)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($message)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 mx-8 mt-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p><?php echo $message; ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="reset-password.php" class="py-8 px-8 space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="votre.email@exemple.com">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div class="pt-4">
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300 flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Envoyer le lien de réinitialisation
                        </button>
                    </div>

                    <div class="text-center mt-6">
                        <p class="text-gray-600">
                            Vous vous souvenez de votre mot de passe? <a href="login.php" class="text-blue-600 hover:underline">Se connecter</a>
                        </p>
                    </div>
                </form>
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