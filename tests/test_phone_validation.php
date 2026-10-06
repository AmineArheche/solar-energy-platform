<?php
// Test unitaire de validation téléphonique marocaine
require_once __DIR__ . '/../energyprjt/includes/functions.php';

function test_phone_validator(): void {
    assert(validate_moroccan_phone('+212612345678') === true, 'Numéro international valide rejeté');
    assert(validate_moroccan_phone('0612345678') === true, 'Numéro national valide rejeté');
    assert(validate_moroccan_phone('12345') === false, 'Numéro invalide accepté');
    echo "[OK] Test validation téléphone validé.\n";
}

test_phone_validator();
