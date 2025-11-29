<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Sao_Paulo');

// Autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Carrega variáveis do .env (opcional)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

ob_start();

try {

    // Carrega o arquivo que retorna o Router
    $router = require __DIR__ . '/../routes.php';

    // Executa o roteador
    $router->dispatch();
} catch (Exception $e) {

    http_response_code(500);

    echo '<h1>Erro 500</h1>';
    echo '<pre>' . $e->getMessage() . '</pre>';
}

ob_end_flush();
