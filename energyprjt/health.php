<?php
// Point de contrôle de disponibilité système et base de données
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/database.php';

$status = ['status' => 'ok', 'timestamp' => date('c'), 'db' => 'disconnected'];
try {
    $pdo = get_pdo_connection();
    $pdo->query('SELECT 1');
    $status['db'] = 'connected';
} catch (Exception $e) {
    $status['status'] = 'degraded';
    $status['error'] = 'Database connection failed';
    http_response_code(503);
}
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
