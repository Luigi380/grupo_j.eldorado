<?php

namespace App\Controllers;

use App\Models\Registration;

class RegistrationController
{
    public function register()
    {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if (!$email || !$password) {
            echo json_encode([
                "error" => true,
                "message" => "Email e Senha são obrigatório!"
            ]);
            return;
        }

        $registration = new Registration($email, $password);
        $result = $registration->saveData();
        echo json_encode($result);
    }
}
