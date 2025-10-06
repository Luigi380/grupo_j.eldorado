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

        public function getEmail(){
            return $this->email;
        }

        public function setEmail(string $email){
            $this->email = $email;
            return $this;
        }

        public function verifyPassword(string $passwordEntered): bool {
            return password_verify($passwordEntered, $this->password);
        }

        /* public function getPassword(){
            return $this->password;
        }

        public function setPassword($password){
            $this->password = $password;
            return $this;
        } */
    }
?>