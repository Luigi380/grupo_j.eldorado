<?php

namespace App\Models;

use App\Models\User;
use App\Core\SupabaseClient;

class Authentication extends User
{
    private $supabase;

    public function __construct(string $email, string $password)
    {
        return parent::__construct($email, $password);
        $this->supabase = new SupabaseClient();
    }

    public function verifyCredentials(string $emailEntered, string $passwordEntered)
    {
        try {
            $result = $this->supabase->get("login_admin?email=eq.$emailEntered");
            if (empty($result)) {
                throw new \Exception("Email Inválido");
            }

            $user = $result[0];
            if (!isset($user['senha'])) {
                throw new \Exception("Erro interno: campo 'password' não encontrado no banco");
            }

            if (!password_verify($passwordEntered, $user['senha'])) {
                throw new \Exception("Email ou senha inválidos");
            }

            return [
                "error" => false,
                "message" => "Login realizado com sucesso",
                "data" => $user
            ];
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
    }
}
