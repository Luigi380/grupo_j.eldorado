<?php

namespace App\Models;

class User
{
    private string $email;
    private string $passwordHash;

    public function __construct(string $email, string $password)
    {
        $this->setEmail($email);
        $this->setPassword($password); // senha pura → hash
    }

    /* GETTERS */
    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    /* SETTERS */
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email inválido.");
        }
        $this->email = $email;
    }

    // Recebe senha pura → hasheia
    public function setPassword(string $password): void
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (!$hash) {
            throw new \RuntimeException("Erro ao hashear a senha.");
        }
        $this->passwordHash = $hash;
    }

    // Recebe hash pronto
    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }
}
