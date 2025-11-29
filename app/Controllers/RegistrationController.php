<?php

namespace App\Controllers;

use App\Models\Registration;

class RegistrationController
{
    public function register()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;
        $confirmPassword = $_POST['confirm_password'] ?? null;

        // Validações
        if (!$email || !$password || !$confirmPassword) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Todos os campos são obrigatórios!"
            ]);
            exit;
        }

        if ($password !== $confirmPassword) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "As senhas não coincidem!"
            ]);
            exit;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "A senha deve ter no mínimo 6 caracteres!"
            ]);
            exit;
        }

        try {
            $registration = new Registration($email, $password);
            $result = $registration->saveData();

            if ($result["error"] === true) {
                http_response_code(400);
                echo json_encode($result);
                exit;
            }

            http_response_code(201);
            echo json_encode([
                "error" => false,
                "message" => "Cadastro realizado com sucesso!",
                "data" => $result["data"]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }
}
