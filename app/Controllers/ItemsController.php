<?php

namespace App\Controllers;

use App\Models\ItemRegistration;
use App\Core\SupabaseClient;

class ItemsController
{
    /**
     * Faz upload de imagem para o Supabase Storage
     */
    public function uploadImage()
    {
        session_start();

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');

        // Verifica autenticação
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado!"
            ]);
            exit;
        }

        // Verifica se o arquivo foi enviado
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Nenhum arquivo enviado"
            ]);
            exit;
        }

        $file = $_FILES['file'];
        $bucket = $_POST['bucket'] ?? 'imagens_itens';

        // Validações
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Tipo de arquivo não permitido. Use PNG, JPG ou JPEG"
            ]);
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Arquivo muito grande. Máximo: 5MB"
            ]);
            exit;
        }

        try {
            $supabase = new SupabaseClient();

            // Gerar nome único para o arquivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $extension;

            // Upload para o Supabase Storage
            $uploadResult = $supabase->uploadFile($bucket, $fileName, $file['tmp_name']);

            if (!$uploadResult['success']) {
                throw new \Exception($uploadResult['message'] ?? 'Erro ao fazer upload');
            }

            // Obter URL pública
            $publicUrl = $supabase->getPublicUrl($bucket, $fileName);

            echo json_encode([
                "error" => false,
                "message" => "Upload realizado com sucesso",
                "url" => $publicUrl,
                "fileName" => $fileName
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Erro ao fazer upload: " . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Cria um novo item
     */
    public function create()
    {
        session_start();

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type');

        // Verifica autenticação
        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado!"
            ]);
            exit;
        }

        $adminId = $_SESSION['admin_id'];
        $name = $_POST['name'] ?? null;
        $text = $_POST['text'] ?? null;
        $imgUrl = $_POST['img_url'] ?? null;
        $tipo = $_POST['tipo'] ?? null;

        // Validações
        if (!$name || !$text || !$imgUrl || !$tipo) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Todos os campos são obrigatórios!"
            ]);
            exit;
        }

        // Validar tipo
        $tiposPermitidos = ['Granito', 'Mármore', 'Quartzo'];
        if (!in_array($tipo, $tiposPermitidos)) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Tipo inválido! Use: Granito, Mármore ou Quartzo"
            ]);
            exit;
        }

        try {
            $itemRegistration = new ItemRegistration($adminId);
            $result = $itemRegistration->saveItem($text, $imgUrl, $name, $tipo);

            if ($result["error"] === true) {
                http_response_code(400);
                echo json_encode($result);
                exit;
            }

            http_response_code(201);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Lista todos os itens (com informações do admin via JOIN)
     */
    public function list()
    {
        session_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado!"
            ]);
            exit;
        }

        try {
            $adminId = $_SESSION['admin_id'];
            $itemRegistration = new ItemRegistration($adminId);

            // Lista todos os itens com JOIN
            $result = $itemRegistration->listAll();

            if ($result["error"]) {
                http_response_code(500);
                echo json_encode($result);
                exit;
            }

            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Mostra um item específico
     */
    public function show($id)
    {
        session_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado!"
            ]);
            exit;
        }

        try {
            $adminId = $_SESSION['admin_id'];
            $itemRegistration = new ItemRegistration($adminId);

            $result = $itemRegistration->findById($id);

            if ($result["error"]) {
                http_response_code(404);
                echo json_encode($result);
                exit;
            }

            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Atualiza um item
     */
    public function update($id)
    {
        session_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado!"
            ]);
            exit;
        }

        // Parse dados do PUT/POST
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            parse_str(file_get_contents("php://input"), $data);
        } else {
            $data = $_POST;
        }

        $name = $data['name'] ?? null;
        $text = $data['text'] ?? null;
        $imgUrl = $data['img_url'] ?? null;
        $tipo = $data['tipo'] ?? null;

        if (!$name || !$text || !$imgUrl || !$tipo) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Todos os campos são obrigatórios!"
            ]);
            exit;
        }

        try {
            $adminId = $_SESSION['admin_id'];
            $itemRegistration = new ItemRegistration($adminId);

            $result = $itemRegistration->updateItem($id, $text, $imgUrl, $name, $tipo);

            if ($result["error"]) {
                http_response_code(400);
                echo json_encode($result);
                exit;
            }

            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Deleta um item
     */
    public function delete($id)
    {
        session_start();

        header('Content-Type: application/json');

        if (!isset($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode([
                "error" => true,
                "message" => "Não autenticado!"
            ]);
            exit;
        }

        try {
            $adminId = $_SESSION['admin_id'];
            $itemRegistration = new ItemRegistration($adminId);

            $result = $itemRegistration->deleteItem($id);

            if ($result["error"]) {
                http_response_code(400);
                echo json_encode($result);
                exit;
            }

            echo json_encode($result);
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
