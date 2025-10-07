<?php
namespace App\Controllers;

use App\Models\Registration;

class RegistrationController{
    private string $filePath;

    public function __construct(string $filePath){
        $this->filePath = $filePath;
    }

    public function registerData(array $data): array {
        if (empty($data['email']) || empty($data['password'])){
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        $registration = new Registration($data['email'], $data['password']);

        if ($registration -> saveData($this->filePath)){
            return ['success' => true, 'message' => 'User registered successfully'];
        }

        return ['success' => false, 'message' => 'Email already exists'];
    }
}
?>