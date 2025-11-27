<?php

namespace App\Controllers;

use App\Models\ItemRegistration;

class ItemRegistrationController
{
    public function itemRegister()
    {
        session_start();

        if (!isset($_SESSION['admin_id'])) {
            echo json_encode([
                "error" => true,
                "message" => "Você precisa estar autenticado."
            ]);
            return;
        }

        $name = $_POST['nome'] ?? null;
        $text = $_POST['texto'] ?? null;
        $img = $_FILES['imagem'] ?? null;

        if (!$name || !$text || !$img || $img['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                "error" => true,
                "message" => "Preencha todos os campos."
            ]);
            return;
        }

        // Upload da imagem ao Storage
        $supabase = new \App\Core\SupabaseClient();

        $fileName = uniqid() . "_" . basename($img['name']);
        $tempPath = $img['tmp_name'];

        $upload = $supabase->uploadFile("item_images", $fileName, $tempPath);

        if (!$upload['success']) {
            echo json_encode([
                "error" => true,
                "message" => "Erro ao fazer upload da imagem."
            ]);
            return;
        }

        // Pega a URL pública
        $imageUrl = $supabase->getPublicUrl("item_images", $fileName);

        // Salva no banco
        $itemRegistration = new ItemRegistration($_SESSION['admin_id']);
        $result = $itemRegistration->saveItem($text, $imageUrl, $name);

        echo json_encode($result);
    }
}
