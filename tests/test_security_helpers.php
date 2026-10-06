<?php
// Test unitaire des helpers de sécurité (XSS & validation)
require_once __DIR__ . '/../energyprjt/includes/functions.php';

function test_xss_escape(): void {
    $input = '<script>alert(1)</script>';
    $escaped = e($input);
    assert(strpos($escaped, '<script>') === false, 'XSS tag non neutralisé');
    echo "[OK] Test anti-XSS validé.\n";
}

test_xss_escape();
