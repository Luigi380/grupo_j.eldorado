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

    public function getWhere(string $table, string $filter): array
    {
        return $this->request("/rest/v1/{$table}?{$filter}", 'GET');
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

    /**
     * Upload de arquivo para o Supabase Storage (CORRIGIDO - sem cURL)
     */
    public function uploadFile(string $bucket, string $fileName, string $filePath): array
    {
        try {
            $endpoint = "/storage/v1/object/{$bucket}/{$fileName}";

            // Ler o conteúdo do arquivo
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                return [
                    "success" => false,
                    "message" => "Não foi possível ler o arquivo"
                ];
            }

            // Detectar o tipo MIME (fallback se fileinfo não estiver disponível)
            $mimeType = 'application/octet-stream';

            if (function_exists('mime_content_type')) {
                $mimeType = mime_content_type($filePath);
            } elseif (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $filePath);
                finfo_close($finfo);
            } else {
                // Fallback baseado na extensão
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp'
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            }

            // Tentar cURL primeiro, se disponível
            if (function_exists('curl_init')) {
                return $this->uploadWithCurl($endpoint, $fileContent, $mimeType);
            }

            // Fallback: usar file_get_contents com stream context
            return $this->uploadWithFileGetContents($endpoint, $fileContent, $mimeType);
        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Exceção: " . $e->getMessage()
            ];
        }
    }

    /**
     * Upload usando cURL (se disponível)
     */
    private function uploadWithCurl(string $endpoint, string $fileContent, string $mimeType): array
    {
        $headers = [
            "apikey: {$this->apiKey}",
            "Authorization: Bearer {$this->apiKey}",
            "Content-Type: {$mimeType}",
            "Content-Length: " . strlen($fileContent)
        ];

        $ch = curl_init($this->url . $endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                "success" => false,
                "message" => "Erro cURL: {$error}"
            ];
        }

        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                "success" => true,
                "response" => json_decode($response, true),
                "message" => "Upload realizado com sucesso"
            ];
        } else {
            return [
                "success" => false,
                "message" => "Erro HTTP {$httpCode}: {$response}"
            ];
        }
    }

    /**
     * Upload usando file_get_contents (fallback)
     */
    private function uploadWithFileGetContents(string $endpoint, string $fileContent, string $mimeType): array
    {
        $headers = "apikey: {$this->apiKey}\r\n" .
            "Authorization: Bearer {$this->apiKey}\r\n" .
            "Content-Type: {$mimeType}\r\n" .
            "Content-Length: " . strlen($fileContent) . "\r\n";

        $options = [
            "http" => [
                "header"  => $headers,
                "method"  => "POST",
                "content" => $fileContent,
                "ignore_errors" => true
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ];

        $context = stream_context_create($options);
        $response = @file_get_contents($this->url . $endpoint, false, $context);

        if ($response === false) {
            $error = error_get_last();
            return [
                "success" => false,
                "message" => "Erro no upload: " . ($error['message'] ?? 'Erro desconhecido')
            ];
        }

        // Verificar o código HTTP da resposta
        $httpCode = 200;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/\d\.\d\s+(\d+)/', $header, $matches)) {
                    $httpCode = intval($matches[1]);
                    break;
                }
            }
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                "success" => true,
                "response" => json_decode($response, true),
                "message" => "Upload realizado com sucesso"
            ];
        } else {
            return [
                "success" => false,
                "message" => "Erro HTTP {$httpCode}: {$response}"
            ];
        }
    }

    /**
     * Retorna a URL pública de um arquivo
     */
    public function getPublicUrl(string $bucket, string $fileName): string
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$fileName}";
    }
}
