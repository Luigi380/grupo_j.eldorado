<?php

namespace App\Controllers;

use App\Models\Authentication;

class AuthenticationController
{
    public function auth()
    {
        // Define headers JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        session_start();

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Email ou senha estão incorretos!"
            ]);
            exit;
        }

        $authentication = new Authentication();
        $result = $authentication->verifyCredentials($email, $password);

        if ($result["error"] === true) {
            http_response_code(401);
            echo json_encode($result);
            exit;
        }

        $adminId = $result["data"]["id"] ?? null;

        if (!$adminId) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Erro interno: ID do administrador não encontrado."
            ]);
            exit;
        }

        $_SESSION['admin_id'] = $adminId;

        http_response_code(200);
        echo json_encode([
            "error" => false,
            "message" => "Login realizado com sucesso!",
            "admin_id" => $adminId
        ]);
        exit;
    }
}