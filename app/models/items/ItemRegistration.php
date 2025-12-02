<?php

namespace App\Models;

use App\Core\SupabaseClient;

class ItemRegistration
{

    private $supabase;
    private $adminId;

    public function __construct($adminId)
    {
        $this->supabase = new SupabaseClient();
        $this->$adminId = $adminId;
    }

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
                "message" => "Cadatro do item realizado com sucesso",
                "data" => $insert
            ];
        } catch (\Exception $e) {
            return [
                "error" => false,
                "message" => $e->getMessage()
            ];
        }
    }
}
