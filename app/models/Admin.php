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

    /**
     * Atualiza o email do administrador
     */
    public function updateEmail(string $id, string $newEmail, string $password)
    {
        try {
            // Primeiro verifica se a senha está correta
            $admin = $this->findById($id);

            if (empty($admin)) {
                return [
                    "error" => true,
                    "message" => "Administrador não encontrado"
                ];
            }

            if (!password_verify($password, $admin[0]["senha"])) {
                return [
                    "error" => true,
                    "message" => "Senha incorreta"
                ];
            }

            // Verifica se o novo email já existe
            $existingEmail = $this->findByEmail($newEmail);
            if (!empty($existingEmail)) {
                return [
                    "error" => true,
                    "message" => "Este email já está em uso"
                ];
            }

            // Atualiza o email
            $result = $this->supabase->update(
                "login_admin",
                ["email" => $newEmail],
                "id_adm=eq.$id"
            );

            return [
                "error" => false,
                "message" => "Email atualizado com sucesso",
                "data" => $result
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao atualizar email: " . $e->getMessage()
            ];
        }
    }

    /**
     * Atualiza a senha do administrador
     */
    public function updatePassword(string $id, string $currentPassword, string $newPassword)
    {
        try {
            // Primeiro verifica se a senha atual está correta
            $admin = $this->findById($id);

            if (empty($admin)) {
                return [
                    "error" => true,
                    "message" => "Administrador não encontrado"
                ];
            }

            if (!password_verify($currentPassword, $admin[0]["senha"])) {
                return [
                    "error" => true,
                    "message" => "Senha atual incorreta"
                ];
            }

            // Hash da nova senha
            $newPasswordHashed = password_hash($newPassword, PASSWORD_DEFAULT);

            // Atualiza a senha
            $result = $this->supabase->update(
                "login_admin",
                ["senha" => $newPasswordHashed],
                "id_adm=eq.$id"
            );

            return [
                "error" => false,
                "message" => "Senha atualizada com sucesso",
                "data" => $result
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => "Erro ao atualizar senha: " . $e->getMessage()
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
