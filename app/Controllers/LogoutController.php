<?php

namespace App\Controllers;

class LogoutController
{
    public function logout()
    {
        session_start();

        // Limpa todas as variáveis da sessão
        $_SESSION = [];

        // Destrói a sessão no servidor
        session_destroy();

        echo json_encode([
            "error" => false,
            "message" => "Logout realizado com sucesso."
        ]);
    }
}
