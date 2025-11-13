<?php
    namespace App\Models;

    use App\Models\User;

    class Registration extends User{

        public function __construct(string $email, string $password)
        {
            parent::__construct($email, $password);
        }

        // Salva os Usuários em arquivo JSON
        public function saveData(string $filePath): bool {
            // Garantir que o arquivo existe
            if (!file_exists($filePath)) {
                file_put_contents($filePath, json_encode([]));
            }

            $users = json_decode(file_get_contents($filePath), true);
            
            // Checar se o email existe
            foreach ($users as $user) {
                if ($user['email'] === $this->getEmail()) {
                    return false;
                }
            }

            // Adicionar novo Usuáriuo
            $users[] = [
                'email' => $this->getEmail(),
                'password' => $this->getHashedPassword()
            ];

            // Salavar arquivo
            return file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT)) !== false;
        }
    }
?>