<?php

namespace App\Controllers;

use App\Models\ItemRegistration;

class ItemsController
{
    /**
     * Faz upload de imagem LOCALMENTE (mais simples, sem Supabase Storage)
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

        // Validações
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Tipo de arquivo não permitido. Use PNG, JPG, JPEG, GIF ou WEBP"
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

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Erro no upload do arquivo: " . $file['error']
            ]);
            exit;
        }

        try {
            // Criar diretório de uploads se não existir
            $uploadDir = __DIR__ . '/../../public/uploads/materiais/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Gerar nome único para o arquivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            // Mover arquivo para o diretório de uploads
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                throw new \Exception("Erro ao salvar o arquivo");
            }

            // Gerar URL pública
            $publicUrl = '/grupo_j.eldorado/public/uploads/materiais/' . $fileName;

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

            // Buscar o item para pegar a URL da imagem
            $item = $itemRegistration->findById($id);

            if (!$item["error"] && isset($item["data"]["foto"])) {
                // Tentar deletar a imagem local
                $fotoUrl = $item["data"]["foto"];
                if (strpos($fotoUrl, '/uploads/materiais/') !== false) {
                    $filePath = __DIR__ . '/../../public' . parse_url($fotoUrl, PHP_URL_PATH);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
            }

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
