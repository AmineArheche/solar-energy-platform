<?php
// Vérifier si une session n'est pas déjà active avant d'en démarrer une
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

/**
 * Classe Auth pour gérer l'authentification des utilisateurs
 */
class Auth {
    private $pdo;
    
    /**
     * Constructeur
     * @param PDO $pdo Instance de PDO pour la connexion à la base de données
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Vérifie si l'utilisateur est connecté
     * @return bool True si l'utilisateur est connecté, false sinon
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Vérifie si l'utilisateur est un administrateur
     * @return bool True si l'utilisateur est un administrateur, false sinon
     */
    public function isAdminUser() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        try {
            $stmt = $this->pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            return $user && $user['is_admin'] == 1;
        } catch (PDOException $e) {
            error_log("Erreur de vérification admin: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Déconnecte l'utilisateur
     */
    public function logout() {
        // Détruire toutes les variables de session
        $_SESSION = array();
        
        // Détruire la session
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Redirige l'utilisateur vers la page de connexion s'il n'est pas connecté
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    /**
     * Redirige l'utilisateur vers la page d'accueil s'il n'est pas un administrateur
     */
    public function requireAdmin() {
        $this->requireLogin();
        
        if (!$this->isAdminUser()) {
            header('Location: /index.php');
            exit;
        }
    }
}

// Créer une instance de la classe Auth
$auth = new Auth($pdo); 