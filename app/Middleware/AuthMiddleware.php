<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function checkAuth()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Verifica se o admin está logado
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            // Se for uma requisição AJAX/API, retorna JSON
            if (
                !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
            ) {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'error' => true,
                    'message' => 'Não autorizado. Faça login.'
                ]);
                exit;
            }

            // Se for uma requisição normal, redireciona para login
            header('Location: /grupo_j.eldorado/public/admin/login');
            exit;
        }

        return true;
    }

    public static function getAdminId()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['admin_id'] ?? null;
    }

    public static function getAdminEmail()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['admin_email'] ?? null;
    }
}
