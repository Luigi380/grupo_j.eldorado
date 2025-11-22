<?php

namespace App\Controllers;

use App\Models\ItemRegistration;

class ItemRegistrationController
{
    public function itemRegister()
    {
        session_start();

        // Só entra se estiver logado
        if (!isset($_SESSION['admin_id'])) {
            echo json_encode([
                "error" => true,
                "message" => "Você precisa estar autenticado."
            ]);
            return;
        }

        $name = $_POST['nome'] ?? null;
        $text = $_POST['texto'] ?? null;
        $img = $_POST['imagem'] ?? null;

        if (!$name || !$text || !$img) {
            echo json_encode([
                "error" => true,
                "message" => "Evite deixar informações não preenchidas!"
            ]);
            return;
        }

        $itemRegistration = new ItemRegistration($_SESSION['admin_id']);
        $result = $itemRegistration->saveItem($text, $img, $name);
        echo json_encode($result);
    }
}
