<?php

namespace App\Controllers;

class HomeController
{
    public function index()
    {
        // Caminho da view que será renderizada
        $viewPath = __DIR__ . '/../view/index.html';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Erro: View index.html não encontrada.";
            return;
        }

        // Renderiza o HTML do front
        readfile($viewPath);
    }
}
