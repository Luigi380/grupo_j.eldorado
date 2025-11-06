<?php
    namespace App\Models;

    class User{
        private string $email;
        private string $password;

        public function __construct(string $email, string $password)
        {
            $this->email = $email;
            $this->password = password_hash($password, PASSWORD_DEFAULT);    
        }

        /* Acessa o valor de email */
        public function getEmail(): string {
            return $this->email;
        }

        /* Define um valor para email, mas o valor só é atribuido se passar pelo filtro */
        public function setEmail(string $email): static {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException("Email inválido: {$email}");
            }
            $this->email = $email;
            return $this;
        }

        /* Acessa o valor da senha */
        public function getHashedPassword(): string {
            return $this->password;
        }

        /* Define um valor para a senha e faz com que essa senha seja Hasheada para não ser de facil identificação e manter a segurança */
        public function setPassword(string $password): static {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            if ($hash === false) {
                throw new \RuntimeException("Erro ao hashear a senha.");
            }
            $this->password = $hash;
            return $this;
        }

        /* Verifica se a senha confere */
        public function verifyPassword(string $plainPassword): bool {
            return password_verify($plainPassword, $this->password);
        }
    }
?>