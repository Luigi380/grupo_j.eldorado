<?php

namespace App\Controllers;

use App\Models\Authentication;

class AuthenticationController
{
    public function auth()
    {
        session_start();

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            echo json_encode([
                "error" => true,
                "message" => "Email e Senha são obrigatório!"
            ]);
            return;
        }

        $authentication = new Authentication();
        $result = $authentication->verifyCredentials($email, $password);

        if ($result["error"] === true) {
            echo json_encode($result);
            return;
        }

        // Pegamos o id do admin vindo do banco
        $adminId = $result["data"]["id"] ?? null;

        if (!$adminId) {
            echo json_encode([
                "error" => true,
                "message" => "Erro interno: ID do administrador não encontrado."
            ]);
            return;
        }

        // Guardamos o ID na sessão
        $_SESSION['admin_id'] = $adminId;

        echo json_encode([
            "error" => false,
            "message" => "Login realizado com sucesso!",
            "admin_id" => $adminId
        ]);
    }
}
