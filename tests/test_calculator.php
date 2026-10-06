<?php
// Test unitaire autonome pour le dimensionnement solaire
require_once __DIR__ . '/../energyprjt/calculateur-energetique.php';

function test_dimensionnement(): void {
    $res = calculer_dimensionnement_panneaux(6000); // 6000 kWh/an
    assert($res['puissance_kwc'] > 0, 'La puissance crête doit être positive');
    assert($res['nb_panneaux'] >= 1, 'Le nombre de panneaux doit être au moins 1');
    echo "[OK] Test dimensionnement solaire validé.\n";
}

test_dimensionnement();
