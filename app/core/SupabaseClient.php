<?php

namespace App\Core;

class SupabaseClient
{
    private string $url;
    private string $apiKey;

    public function __construct()
    {
        $this->url = "https://rdslytjpzqpagfuevzzg.supabase.co";
        $this->apiKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InJkc2x5dGpwenFwYWdmdWV2enpnIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjI1NDQwODUsImV4cCI6MjA3ODEyMDA4NX0.cXHdBr446OsSf6lgbsYC2A1ehGWedjpL2Pf5PFEVPEE";
    }

    private function request(string $endpoint, string $method = 'GET', ?array $data = null): array
    {
        try {
            $headers = "apikey: {$this->apiKey}\r\nAuthorization: Bearer {$this->apiKey}\r\nContent-Type: application/json\r\n";
            $options = [
                "http" => [
                    "header"  => $headers,
                    "method"  => $method,
                    "content" => $data ? json_encode($data) : null,
                    "ignore_errors" => true
                ]
            ];

            $context = stream_context_create($options);
            $response = file_get_contents($this->url . $endpoint, false, $context);

            if ($response === false) {
                throw new \Exception("Falha ao conectar ao Supabase.");
            }

            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Erro ao decodificar JSON: " . json_last_error_msg());
            }

            return $decoded;
        } catch (\Exception $e) {
            return [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
    }

    // Métodos CRUD
    public function get(string $table): array
    {
        return $this->request("/rest/v1/{$table}", 'GET');
    }

    public function insert(string $table, array $data): array
    {
        return $this->request("/rest/v1/{$table}", 'POST', $data);
    }

    public function update(string $table, array $data, string $filter): array
    {
        return $this->request("/rest/v1/{$table}?{$filter}", 'PATCH', $data);
    }

    public function delete(string $table, string $filter): array
    {
        return $this->request("/rest/v1/{$table}?{$filter}", 'DELETE');
    }
}
