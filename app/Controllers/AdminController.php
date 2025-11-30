<?php

namespace App\Controllers;

use App\Models\Admin;

class AdminController
{
    public function login()
    {
        header("Content-Type: application/json");

        $email = $_POST["email"] ?? null;
        $password = $_POST["password"] ?? null;

        if (!$email || !$password) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Campos obrigatórios"
            ]);
            return;
        }

        $adminModel = new Admin();
        $auth = $adminModel->verifyCredentials($email, $password);

        if ($auth["error"]) {
            http_response_code(401);
            echo json_encode($auth);
            return;
        }

        session_start();
        $_SESSION["admin_id"] = $auth["data"]["id"];

        echo json_encode([
            "error" => false,
            "message" => "Login realizado",
            "admin_id" => $auth["data"]["id"]
        ]);
    }

    public function logout()
    {
        session_start();
        $_SESSION = [];
        session_destroy();

        echo json_encode([
            "error" => false,
            "message" => "Logout realizado com sucesso."
        ]);
    }

    public function register()
    {
        header("Content-Type: application/json");

        $email = $_POST["email"] ?? null;
        $password = $_POST["password"] ?? null;
        $confirm = $_POST["confirm_password"] ?? null;

        if (!$email || !$password || !$confirm) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Campos obrigatórios"
            ]);
            return;
        }

        if ($password !== $confirm) {
            echo json_encode([
                "error" => true,
                "message" => "Senhas diferentes"
            ]);
            return;
        }

        $adminModel = new Admin();

        if (!empty($adminModel->findByEmail($email))) {
            echo json_encode([
                "error" => true,
                "message" => "Email já existe"
            ]);
            return;
        }

        $result = $adminModel->create($email, $password);

        echo json_encode([
            "error" => false,
            "message" => "Admin criado",
            "data" => $result
        ]);
    }

    public function listAdmins()
    {
        header("Content-Type: application/json");

        $adminModel = new Admin();
        $result = $adminModel->listAll();

        echo json_encode($result);
    }

    public function deleteAdmin($id)
    {
        header("Content-Type: application/json");

        $adminModel = new Admin();
        $result = $adminModel->delete($id);

        echo json_encode($result);
    }
}
