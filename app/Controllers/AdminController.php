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

    /**
     * Retorna informações do admin atual (sessão)
     */
    public function getCurrentAdmin()
    {
        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["admin_id"])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado"
            ]);
            return;
        }

        $adminModel = new Admin();
        $admin = $adminModel->findById($_SESSION["admin_id"]);

        if (empty($admin)) {
            http_response_code(404);
            echo json_encode([
                "error" => true,
                "message" => "Administrador não encontrado"
            ]);
            return;
        }

        echo json_encode([
            "error" => false,
            "id" => $admin[0]["id_adm"],
            "email" => $admin[0]["email"]
        ]);
    }

    /**
     * Atualiza o email do admin
     * CORRIGIDO: Lê dados de php://input para requisições PUT
     */
    public function updateEmail()
    {
        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["admin_id"])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado"
            ]);
            return;
        }

        // Lê os dados do corpo da requisição PUT
        $putData = $this->getPutData();

        $newEmail = $putData["newEmail"] ?? null;
        $password = $putData["password"] ?? null;

        if (!$newEmail || !$password) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Campos obrigatórios"
            ]);
            return;
        }

        // Valida formato do email
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Email inválido"
            ]);
            return;
        }

        $adminModel = new Admin();
        $result = $adminModel->updateEmail($_SESSION["admin_id"], $newEmail, $password);

        if ($result["error"]) {
            http_response_code(400);
            echo json_encode($result);
            return;
        }

        // Atualiza a sessão com o novo email
        $_SESSION["admin_email"] = $newEmail;

        echo json_encode($result);
    }

    /**
     * Atualiza a senha do admin
     * CORRIGIDO: Lê dados de php://input para requisições PUT
     */
    public function updatePassword()
    {
        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION["admin_id"])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado"
            ]);
            return;
        }

        // Lê os dados do corpo da requisição PUT
        $putData = $this->getPutData();

        $currentPassword = $putData["currentPassword"] ?? null;
        $newPassword = $putData["newPassword"] ?? null;

        if (!$currentPassword || !$newPassword) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Campos obrigatórios"
            ]);
            return;
        }

        // Valida tamanho da senha
        if (strlen($newPassword) < 6) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "A senha deve ter no mínimo 6 caracteres"
            ]);
            return;
        }

        $adminModel = new Admin();
        $result = $adminModel->updatePassword($_SESSION["admin_id"], $currentPassword, $newPassword);

        if ($result["error"]) {
            http_response_code(400);
            echo json_encode($result);
            return;
        }

        echo json_encode($result);
    }

    /**
     * Função auxiliar para ler dados de requisições PUT
     * Suporta multipart/form-data e application/x-www-form-urlencoded
     */
    private function getPutData()
    {
        $putData = [];

        // Obtém o content-type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (strpos($contentType, 'multipart/form-data') !== false) {
            // Para multipart/form-data, precisamos fazer parsing manual
            $rawData = file_get_contents('php://input');

            // Extrai o boundary
            preg_match('/boundary=(.*)$/', $contentType, $matches);
            $boundary = $matches[1] ?? '';

            if ($boundary) {
                // Divide os dados pelo boundary
                $parts = array_slice(explode("--$boundary", $rawData), 1, -1);

                foreach ($parts as $part) {
                    // Extrai o nome do campo e o valor
                    if (preg_match('/name="([^"]+)"\r?\n\r?\n(.+)\r?\n/s', $part, $matches)) {
                        $putData[trim($matches[1])] = trim($matches[2]);
                    }
                }
            }
        } else {
            // Para application/x-www-form-urlencoded ou JSON
            parse_str(file_get_contents('php://input'), $putData);
        }

        return $putData;
    }
}
