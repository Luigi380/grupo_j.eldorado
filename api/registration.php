<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\RegistrationController;

// Allow only JSON POST request
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$filePath = __DIR__ . '/../data/users.json';

// Create controller and process registration
try {
    $controller = new RegistrationController($filePath);
    $response = $controller->register($input);
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
