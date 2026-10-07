<?php
/**
 * Valide un fichier image uploadé (MIME, extension, taille max)
 */
function validate_image_upload(array $file, int $max_size_mb = 5): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Erreur lors du téléchargement.'];
    }
    if ($file['size'] > $max_size_mb * 1024 * 1024) {
        return ['valid' => false, 'error' => "Taille maximale dépassée ($max_size_mb Mo)."];
    }
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowed_mimes)) {
        return ['valid' => false, 'error' => 'Format de fichier non autorisé (JPEG, PNG, WebP uniquement).'];
    }
    return ['valid' => true, 'mime' => $mime];
}


/**
 * Formate une puissance en Watt crête (Wc) ou kiloWatt (kWc)
 */
function format_power_kw($watts): string {
    $w = floatval($watts ?? 0);
    if ($w >= 1000) {
        return number_format($w / 1000, 2, ',', ' ') . ' kWc';
    }
    return number_format($w, 0, ',', ' ') . ' Wc';
}


/**
 * Formate un prix en Dirhams Marocains (MAD / DH)
 */
function format_price_dh($amount): string {
    $val = floatval($amount ?? 0);
    return number_format($val, 2, ',', ' ') . ' DH';
}


/**
 * Nettoie une chaîne saisie en supprimant les espaces et balises nulles
 */
function sanitize_text_input(?string $input): string {
    if ($input === null) return '';
    return trim(strip_tags($input));
}


/**
 * Échappe une chaîne pour un affichage HTML sécurisé (anti-XSS)
 */
function e($data): string {
    return htmlspecialchars((string)($data ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}


/**
 * Génère un jeton CSRF cryptographique pour le formulaire
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}


/**
 * Applique les en-têtes de sécurité HTTP modernes
 */
function apply_security_headers(): void {
    if (!headers_sent()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
}


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

