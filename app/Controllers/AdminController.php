<?php

namespace App\Controllers;

use App\Models\Admin;

class AdminController
{
    public function login()
    {
        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        $_SESSION["admin_id"] = $auth["data"]["id_adm"];
        $_SESSION["admin_email"] = $auth["data"]["email"];
        $_SESSION["logged_in"] = true;

        echo json_encode([
            "error" => false,
            "message" => "Login realizado",
            "admin_id" => $auth["data"]["id_adm"]
        ]);
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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

        // Verifica se há uma sessão ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = $_POST["email"] ?? null;
        $password = $_POST["password"] ?? null;
        $passwordConfirm = $_POST["passwordConfirm"] ?? null;
        $adminPassword = $_POST["adminPassword"] ?? null;

        if (!$email || !$password || !$passwordConfirm) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Campos obrigatórios"
            ]);
            return;
        }

        if ($password !== $passwordConfirm) {
            echo json_encode([
                "error" => true,
                "message" => "Senhas diferentes"
            ]);
            return;
        }

        $adminModel = new Admin();

        // Valida a senha do admin atual (quem está cadastrando)
        $currentAdminId = $_SESSION["admin_id"];
        $currentAdmin = $adminModel->findById($currentAdminId);

        if (empty($currentAdmin)) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Administrador atual não encontrado"
            ]);
            return;
        }

        // Verifica se a senha do admin atual está correta
        if (!password_verify($adminPassword, $currentAdmin[0]['senha'])) {
            echo json_encode([
                "error" => true,
                "message" => "Senha do administrador atual incorreta. Validação falhou."
            ]);
            return;
        }

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

        try {
            $adminModel = new Admin();
            $result = $adminModel->delete($id);

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Erro ao excluir administrador: " . $e->getMessage()
            ]);
        }
    }
}
