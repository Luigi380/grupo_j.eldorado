<?php

namespace App\Controllers;

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
        $viewPath = __DIR__ . '/../View/Admin/homeEdit.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        readfile($viewPath);
    }
}
