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
    public function saveItem(string $text, string $imgUrl, string $name)
    {
        try {
            $insert = $this->supabase->insert("cadastrar_itens", [
                "id_adm" => $this->adminId,
                "nome" => $name,
                "foto" => $imgUrl,
                "texto" => $text
            ]);

            return [
                "error" => false,
                "message" => "Cadastro do item realizado com sucesso", // FIX: corrigido typo "Cadatro"
                "data" => $insert
            ];
        } catch (\Exception $e) {
            return [
                "error" => true, // FIX: corrigido para true quando há erro
                "message" => "Erro ao salvar item: " . $e->getMessage()
            ];
        }
    }

    /**
     * Lista todos os itens
     */
    public function listAll()
    {
        try {
            $items = $this->supabase->get("cadastrar_itens");

            return [
                "error" => false,
                "data" => $items
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
            $items = $this->supabase->getWhere("cadastrar_itens", "id_adm=eq.{$this->adminId}");

            return [
                "error" => false,
                "data" => $items
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
            $item = $this->supabase->getWhere("cadastrar_itens", "id=eq.$id");

            if (empty($item)) {
                return [
                    "error" => true,
                    "message" => "Item não encontrado"
                ];
            }

            return [
                "error" => false,
                "data" => $item[0]
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
    public function updateItem(string $id, string $text, string $imgUrl, string $name)
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
                "texto" => $text
            ], "id=eq.$id");

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

            $this->supabase->delete("cadastrar_itens", "id=eq.$id");

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
