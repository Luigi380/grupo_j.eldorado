<?php

namespace App\Models;

use App\Core\SupabaseClient;

class Admin
{
    private SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = new SupabaseClient();
    }

    public function findByEmail(string $email)
    {
        return $this->supabase->getWhere("login_admin", "email=eq.$email");
    }

    public function findById(string $id)
    {
        return $this->supabase->getWhere("login_admin", "id_adm=eq.$id");
    }

    public function listAll()
    {
        return $this->supabase->get("login_admin");
    }

    public function create(string $email, string $password)
    {
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        return $this->supabase->insert("login_admin", [
            "email" => $email,
            "senha" => $hashed
        ]);
    }

    public function delete(string $id)
    {
        try {
            $result = $this->supabase->delete("login_admin", "id_adm=eq.$id");

            return [
                "error" => false,
                "message" => "Administrador deletado com sucesso",
                "id" => $id
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao deletar: " . $e->getMessage()
            ];
        }
    }

    public function verifyCredentials(string $email, string $password)
    {
        $result = $this->findByEmail($email);

        if (empty($result)) {
            return [
                "error" => true,
                "message" => "Email inválido"
            ];
        }

        $admin = $result[0];

        if (!password_verify($password, $admin["senha"])) {
            return [
                "error" => true,
                "message" => "Senha incorreta"
            ];
        }

        return [
            "error" => false,
            "data" => $admin
        ];
    }
}
