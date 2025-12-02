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
            // Usando select com JOIN implícito do Supabase
            // A sintaxe é: tabela_relacionada(campos)
            $items = $this->supabase->get("cadastrar_itens?select=*,login_admin(email)");

            // Transformar o resultado para incluir o email do admin diretamente
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    "id_itens" => $item["id_itens"],
                    "nome" => $item["nome"],
                    "texto" => $item["texto"],
                    "foto" => $item["foto"],
                    "tipo" => $item["tipo"] ?? null,
                    "id_adm" => $item["id_adm"],
                    "admin_email" => $item["login_admin"]["email"] ?? "N/A"
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
                "cadastrar_itens?select=*,login_admin(email)",
                "id_adm=eq.{$this->adminId}"
            );

            // Formatar resultado
            $formattedItems = [];
            foreach ($items as $item) {
                $formattedItems[] = [
                    "id_itens" => $item["id_itens"],
                    "nome" => $item["nome"],
                    "texto" => $item["texto"],
                    "foto" => $item["foto"],
                    "tipo" => $item["tipo"] ?? null,
                    "id_adm" => $item["id_adm"],
                    "admin_email" => $item["login_admin"]["email"] ?? "N/A"
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
                "cadastrar_itens?select=*,login_admin(email)",
                "id_itens=eq.$id"
            );

            if (empty($item)) {
                return [
                    "error" => true,
                    "message" => "Item não encontrado"
                ];
            }

            // Formatar resultado
            $itemData = $item[0];
            $formatted = [
                "id_itens" => $itemData["id_itens"],
                "nome" => $itemData["nome"],
                "texto" => $itemData["texto"],
                "foto" => $itemData["foto"],
                "tipo" => $itemData["tipo"] ?? null,
                "id_adm" => $itemData["id_adm"],
                "admin_email" => $itemData["login_admin"]["email"] ?? "N/A"
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
