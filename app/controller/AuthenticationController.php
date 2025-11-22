<?php

namespace App\Controllers;

use App\Models\Authentication;

class AuthenticationController
{
    public function auth()
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

        $authentication = new Authentication();
        $result = $authentication->verifyCredentials($email, $password);
        echo json_encode($result);
    }
}
