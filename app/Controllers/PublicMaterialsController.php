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

            // Buscar materiais do tipo específico usando o método correto
            $items = $supabase->getWhere(
                "cadastrar_itens",
                "tipo=eq.{$tipo}&select=id_itens,nome,texto,foto,tipo"
            );

            // Verificar se houve erro
            if (isset($items['error']) && $items['error'] === true) {
                throw new \Exception($items['message'] ?? 'Erro ao buscar materiais');
            }

            // Verificar se retornou array
            if (!is_array($items)) {
                $items = [];
            }

            // Ordenar por nome (apenas se houver itens)
            if (count($items) > 0) {
                usort($items, function ($a, $b) {
                    return strcmp($a['nome'] ?? '', $b['nome'] ?? '');
                });
            }

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

            // Buscar todos os materiais usando o método correto
            $items = $supabase->get("cadastrar_itens");

            // Verificar se houve erro
            if (isset($items['error']) && $items['error'] === true) {
                throw new \Exception($items['message'] ?? 'Erro ao buscar materiais');
            }

            // Verificar se retornou array
            if (!is_array($items)) {
                $items = [];
            }

            // Agrupar por tipo
            $grouped = [
                'Granito' => [],
                'Mármore' => [],
                'Quartzo' => []
            ];

            foreach ($items as $item) {
                if (!is_array($item)) continue;

                $tipo = $item['tipo'] ?? 'Outros';
                if (isset($grouped[$tipo])) {
                    $grouped[$tipo][] = $item;
                }
            }

            // Ordenar cada grupo por nome
            foreach ($grouped as &$group) {
                if (count($group) > 0) {
                    usort($group, function ($a, $b) {
                        return strcmp($a['nome'] ?? '', $b['nome'] ?? '');
                    });
                }
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
                "cadastrar_itens",
                "id_itens=eq.{$id}&select=id_itens,nome,texto,foto,tipo"
            );

            // Verificar se retornou array válido
            if (!is_array($item) || empty($item)) {
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
