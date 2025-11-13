<?php 
    namespace App\Models;

    use App\Models\User;

    class Authentication extends User{
        public function __construct(string $email, string $password)
        {
                return parent::__construct($email, $password);
        }
        
        public function verifyPassword(string $passwordEntered): bool {
            return password_verify($passwordEntered, $this->getHashedPassword());
        }

        public function verifyEmail(string $emailEntered): bool {
            if($this->getEmail() !== $emailEntered){
                echo 'Email incorreto';
                return false;
            }
            return true;
        }
    }
?>