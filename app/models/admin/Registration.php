<?php

namespace App\Models;

use App\Models\User;
use App\Core\SupabaseClient;

class Registration extends User
{

    private $supabase;

    public function __construct(string $email, string $password)
    {
        parent::__construct($email, $password);
        $this->supabase = new SupabaseClient();
    }

    public function saveData(): array
    {
        try {
            $verify = $this->supabase->get("login_admin?email=eq.{$this->getEmail()}");

            if (!empty($verify)) {
                throw new \Exception("Email já existente!");
            }

            $insert = $this->supabase->insert("login_admin", [
                "email" => $this->getEmail(),
                "senha" => $this->getPasswordHash()
            ]);

            return [
                "error" => false,
                "message" => "Cadastro realizado com sucesso",
                "data" => $insert
            ];
        } catch (\Exception $e) {

            return [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
    }
}
