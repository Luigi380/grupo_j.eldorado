<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;

class ViewController
{
    public function index()
    {
        // Caminho da view que será renderizada
        $viewPath = __DIR__ . '/../View/index.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        // Renderiza o HTML do front
        readfile($viewPath);
    }

    public function materiais()
    {
        $viewPath = __DIR__ . '/../View/Home/materiais.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }

    public function ultimosTrabalhos()
    {
        $viewPath = __DIR__ . '/../View/Home/ultimosTrabalhos.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }

    public function login()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: /grupo_j.eldorado/public/admin/dashboard');
            exit;
        }

        $viewPath = __DIR__ . '/../View/Admin/admin.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }

    public function dashboard()
    {
        AuthMiddleware::checkAuth();
        $viewPath = __DIR__ . '/../View/Admin/dashboard.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }

    public function adminConteudo()
    {
        AuthMiddleware::checkAuth();
        $viewPath = __DIR__ . '/../View/Admin/conteudo.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }

    public function homeEdit()
    {
        AuthMiddleware::checkAuth();
        $viewPath = __DIR__ . '/../View/Admin/homeEdit.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }
}
