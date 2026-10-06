<?php
/**
 * Démarre une session sécurisée avec cookies HTTPOnly, SameSite et Secure si HTTPS
 */
function secure_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        session_set_cookie_params([
            'lifetime' => 86400 * 7,
            'path' => '/',
            'domain' => '',
            'secure' => $is_https,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}


/**
 * Fonctions utilitaires et sécurité — Solar Energy Platform
 */

