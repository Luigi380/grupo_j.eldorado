<?php
    namespace App\Models;

    class Registration{
        private string $email;
        private string $password;

        public function __construct(string $email, string   $password)
        {
            $this->email = $email;
            $this->password = password_hash($password, PASSWORD_DEFAULT);
        }
   
        public function verifyPassword(string $passwordEntered): bool {
            return password_verify($passwordEntered, $this->password);
        }

        // Save user to JSON file
        public function saveData(string $filePath): bool {
            // Ensure file exists
            if (!file_exists($filePath)) {
                file_put_contents($filePath, json_encode([]));
            }

            $users = json_decode(file_get_contents($filePath), true);
            
            // Check if email already exists
            foreach ($users as $user) {
                if ($user['email'] === $this->email) {
                    return false;
                }
            }

            // Add new user
            $users[] = [
                'email' => $this->email,
                'password' => $this->password
            ];

            // Save to file
            return file_put_contents($filePath, json_encode($users, JSON_PRETTY_PRINT)) !== false;
        }

        /* public function getEmail(){
            return $this->email;
        }

        public function setEmail(string $email){
            $this->email = $email;
            return $this;
        } */

        /* public function getPassword(){
            return $this->password;
        }

        public function setPassword($password){
            $this->password = $password;
            return $this;
        } */
    }
?>