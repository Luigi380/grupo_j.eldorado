<?php

namespace App\Controllers;

use App\Models\ItemRegistration;

class ItemsController
{
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

        // Validações
        if (!$name || !$text || !$imgUrl) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => "Todos os campos são obrigatórios!"
            ]);
            exit;
        }

        try {
            $itemRegistration = new ItemRegistration($adminId);
            $result = $itemRegistration->saveItem($text, $imgUrl, $name);

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
     * Lista todos os itens
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
            // Aqui você implementaria a lógica de listar itens
            // Exemplo básico:
            echo json_encode([
                "error" => false,
                "message" => "Lista de itens",
                "data" => [] // Adicione sua lógica aqui
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
            // Implemente a lógica para buscar um item específico
            echo json_encode([
                "error" => false,
                "message" => "Detalhes do item",
                "data" => ["id" => $id] // Adicione sua lógica aqui
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

        // Parse PUT data
        parse_str(file_get_contents("php://input"), $_PUT);

        try {
            // Implemente a lógica para atualizar um item
            echo json_encode([
                "error" => false,
                "message" => "Item atualizado com sucesso",
                "data" => ["id" => $id]
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
            // Implemente a lógica para deletar um item
            echo json_encode([
                "error" => false,
                "message" => "Item deletado com sucesso"
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
