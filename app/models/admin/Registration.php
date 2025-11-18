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

    public function saveData(string $emailEntered, string $passwordEntered)
    {
        try {
            $verify = $this->supabase->get("login_admin?email=eq.$emailEntered");

            if (!empty($verify)) {
                throw new \Exception("Email já existente!");
            }

            $insert = $this->supabase->insert("login_admin", [
                "email" => $emailEntered,
                "senha" => $passwordEntered
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
