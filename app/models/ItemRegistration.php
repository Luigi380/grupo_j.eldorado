<?php

namespace App\Models;

use App\Core\SupabaseClient;

class ItemRegistration
{
    private SupabaseClient $supabase;
    private string $adminId;

    public function __construct(string $adminId)
    {
        $this->supabase = new SupabaseClient();
        $this->adminId = $adminId;
    }

    /**
     * Salva um novo item no banco de dados
     */
    public function saveItem(string $text, string $imgUrl, string $name, string $tipo)
    {
        try {
            $insert = $this->supabase->insert("cadastrar_itens", [
                "id_adm" => $this->adminId,
                "nome" => $name,
                "foto" => $imgUrl,
                "texto" => $text,
                "tipo" => $tipo
            ]);

            return [
                "error" => false,
                "message" => "Cadastro do item realizado com sucesso",
                "data" => $insert
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao salvar item: " . $e->getMessage()
            ];
        }
    }

    /**
     * Lista todos os itens com informações do admin (JOIN)
     */
    public function listAll()
    {
        try {
            // Buscar itens sem JOIN primeiro (Supabase REST API tem limitações com joins complexos)
            $items = $this->supabase->get("cadastrar_itens");

            if (isset($items['error']) && $items['error'] === true) {
                throw new \Exception($items['message'] ?? 'Erro ao buscar itens');
            }

            // Se não for array, retornar vazio
            if (!is_array($items)) {
                return [
                    "error" => false,
                    "data" => []
                ];
            }

            // Buscar emails dos admins
            $adminEmails = [];
            foreach ($items as $item) {
                if (isset($item['id_adm']) && !isset($adminEmails[$item['id_adm']])) {
                    $admin = $this->supabase->getWhere("login_admin", "id_adm=eq.{$item['id_adm']}");
                    if (is_array($admin) && !empty($admin)) {
                        $adminEmails[$item['id_adm']] = $admin[0]['email'] ?? 'N/A';
                    }
                }
            }

            // Transformar o resultado para incluir o email do admin
            $formattedItems = [];
            foreach ($items as $item) {
                if (!is_array($item)) continue;

                $formattedItems[] = [
                    "id_itens" => $item["id_itens"] ?? null,
                    "nome" => $item["nome"] ?? '',
                    "texto" => $item["texto"] ?? '',
                    "foto" => $item["foto"] ?? '',
                    "tipo" => $item["tipo"] ?? null,
                    "id_adm" => $item["id_adm"] ?? null,
                    "admin_email" => $adminEmails[$item["id_adm"]] ?? "N/A"
                ];
            }

            return [
                "error" => false,
                "data" => $formattedItems
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao listar itens: " . $e->getMessage()
            ];
        }
    }

    /**
     * Lista itens de um administrador específico
     */
    public function listByAdmin()
    {
        try {
            $items = $this->supabase->getWhere(
                "cadastrar_itens",
                "id_adm=eq.{$this->adminId}"
            );

            if (isset($items['error']) && $items['error'] === true) {
                throw new \Exception($items['message'] ?? 'Erro ao buscar itens');
            }

            if (!is_array($items)) {
                return [
                    "error" => false,
                    "data" => []
                ];
            }

            // Buscar email do admin
            $admin = $this->supabase->getWhere("login_admin", "id_adm=eq.{$this->adminId}");
            $adminEmail = (is_array($admin) && !empty($admin)) ? $admin[0]['email'] : 'N/A';

            // Formatar resultado
            $formattedItems = [];
            foreach ($items as $item) {
                if (!is_array($item)) continue;

                $formattedItems[] = [
                    "id_itens" => $item["id_itens"] ?? null,
                    "nome" => $item["nome"] ?? '',
                    "texto" => $item["texto"] ?? '',
                    "foto" => $item["foto"] ?? '',
                    "tipo" => $item["tipo"] ?? null,
                    "id_adm" => $item["id_adm"] ?? null,
                    "admin_email" => $adminEmail
                ];
            }

            return [
                "error" => false,
                "data" => $formattedItems
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao listar itens: " . $e->getMessage()
            ];
        }
    }

    /**
     * Busca um item por ID
     */
    public function findById(string $id)
    {
        try {
            $item = $this->supabase->getWhere(
                "cadastrar_itens",
                "id_itens=eq.$id"
            );

            if (!is_array($item) || empty($item)) {
                return [
                    "error" => true,
                    "message" => "Item não encontrado"
                ];
            }

            $itemData = $item[0];

            // Buscar email do admin
            $admin = $this->supabase->getWhere("login_admin", "id_adm=eq.{$itemData['id_adm']}");
            $adminEmail = (is_array($admin) && !empty($admin)) ? $admin[0]['email'] : 'N/A';

            // Formatar resultado
            $formatted = [
                "id_itens" => $itemData["id_itens"] ?? null,
                "nome" => $itemData["nome"] ?? '',
                "texto" => $itemData["texto"] ?? '',
                "foto" => $itemData["foto"] ?? '',
                "tipo" => $itemData["tipo"] ?? null,
                "id_adm" => $itemData["id_adm"] ?? null,
                "admin_email" => $adminEmail
            ];

            return [
                "error" => false,
                "data" => $formatted
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao buscar item: " . $e->getMessage()
            ];
        }
    }

    /**
     * Atualiza um item
     */
    public function updateItem(string $id, string $text, string $imgUrl, string $name, string $tipo)
    {
        try {
            // Verifica se o item pertence ao admin atual
            $item = $this->findById($id);

            if ($item["error"]) {
                return $item;
            }

            if ($item["data"]["id_adm"] !== $this->adminId) {
                return [
                    "error" => true,
                    "message" => "Você não tem permissão para atualizar este item"
                ];
            }

            $update = $this->supabase->update("cadastrar_itens", [
                "nome" => $name,
                "foto" => $imgUrl,
                "texto" => $text,
                "tipo" => $tipo
            ], "id_itens=eq.$id");

            return [
                "error" => false,
                "message" => "Item atualizado com sucesso",
                "data" => $update
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao atualizar item: " . $e->getMessage()
            ];
        }
    }

    /**
     * Deleta um item
     */
    public function deleteItem(string $id)
    {
        try {
            // Verifica se o item pertence ao admin atual
            $item = $this->findById($id);

            if ($item["error"]) {
                return $item;
            }

            if ($item["data"]["id_adm"] !== $this->adminId) {
                return [
                    "error" => true,
                    "message" => "Você não tem permissão para deletar este item"
                ];
            }

            $this->supabase->delete("cadastrar_itens", "id_itens=eq.$id");

            return [
                "error" => false,
                "message" => "Item deletado com sucesso"
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao deletar item: " . $e->getMessage()
            ];
        }
    }
}
