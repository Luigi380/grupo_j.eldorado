<?php

namespace App\Controllers;

use App\Core\SupabaseClient;

/**
 * Controller para acesso público aos materiais
 * NÃO requer autenticação - apenas leitura
 */
class PublicMaterialsController
{
    /**
     * Lista materiais públicos filtrados por tipo
     * Rota: GET /api/materiais/publico/listar?tipo=Granito
     */
    public function list()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');

        try {
            $tipo = $_GET['tipo'] ?? null;

            if (!$tipo) {
                http_response_code(400);
                echo json_encode([
                    "error" => true,
                    "message" => "Parâmetro 'tipo' é obrigatório"
                ]);
                exit;
            }

            // Validar tipo
            $tiposPermitidos = ['Granito', 'Mármore', 'Quartzo'];
            if (!in_array($tipo, $tiposPermitidos)) {
                http_response_code(400);
                echo json_encode([
                    "error" => true,
                    "message" => "Tipo inválido. Use: Granito, Mármore ou Quartzo"
                ]);
                exit;
            }

            $supabase = new SupabaseClient();

            // Buscar materiais do tipo específico
            $items = $supabase->getWhere(
                "cadastrar_itens?select=id_itens,nome,texto,foto,tipo",
                "tipo=eq.{$tipo}"
            );

            // Verificar se houve erro
            if (isset($items['error']) && $items['error'] === true) {
                throw new \Exception($items['message'] ?? 'Erro ao buscar materiais');
            }

            // Ordenar por nome
            usort($items, function ($a, $b) {
                return strcmp($a['nome'], $b['nome']);
            });

            echo json_encode([
                "error" => false,
                "data" => $items,
                "total" => count($items)
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Erro ao buscar materiais: " . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Lista todos os materiais públicos (para a página de materiais geral)
     * Rota: GET /api/materiais/publico/todos
     */
    public function listAll()
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');

        try {
            $supabase = new SupabaseClient();

            // Buscar todos os materiais
            $items = $supabase->get("cadastrar_itens?select=id_itens,nome,texto,foto,tipo");

            // Verificar se houve erro
            if (isset($items['error']) && $items['error'] === true) {
                throw new \Exception($items['message'] ?? 'Erro ao buscar materiais');
            }

            // Agrupar por tipo
            $grouped = [
                'Granito' => [],
                'Mármore' => [],
                'Quartzo' => []
            ];

            foreach ($items as $item) {
                $tipo = $item['tipo'] ?? 'Outros';
                if (isset($grouped[$tipo])) {
                    $grouped[$tipo][] = $item;
                }
            }

            // Ordenar cada grupo por nome
            foreach ($grouped as &$group) {
                usort($group, function ($a, $b) {
                    return strcmp($a['nome'], $b['nome']);
                });
            }

            echo json_encode([
                "error" => false,
                "data" => $grouped,
                "total" => count($items)
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Erro ao buscar materiais: " . $e->getMessage()
            ]);
        }
        exit;
    }

    /**
     * Busca detalhes de um material específico
     * Rota: GET /api/materiais/publico/detalhes/{id}
     */
    public function show($id)
    {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');

        try {
            $supabase = new SupabaseClient();

            $item = $supabase->getWhere(
                "cadastrar_itens?select=id_itens,nome,texto,foto,tipo",
                "id_itens=eq.{$id}"
            );

            if (empty($item)) {
                http_response_code(404);
                echo json_encode([
                    "error" => true,
                    "message" => "Material não encontrado"
                ]);
                exit;
            }

            echo json_encode([
                "error" => false,
                "data" => $item[0]
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Erro ao buscar material: " . $e->getMessage()
            ]);
        }
        exit;
    }
}
