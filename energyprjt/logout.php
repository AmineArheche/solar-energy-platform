<?php
require_once 'includes/auth.php';

// Déconnecter l'utilisateur
$auth->logout();

// Rediriger vers la page d'accueil
header("Location: index.php");
exit; 