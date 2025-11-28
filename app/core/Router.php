<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath = '';

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /**
     * Registra uma rota GET
     */
    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Registra uma rota POST
     */
    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Registra uma rota PUT
     */
    public function put(string $path, $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Registra uma rota DELETE
     */
    public function delete(string $path, $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Adiciona uma rota ao registro
     */
    private function addRoute(string $method, string $path, $handler): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->convertToPattern($path)
        ];
    }

    /**
     * Converte o path em uma expressão regular para suportar parâmetros
     * Exemplo: /user/{id} -> /user/([^/]+)
     */
    private function convertToPattern(string $path): string
    {
        // Substitui {param} por grupos de captura
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Executa o roteamento
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remove o match completo
                $this->callHandler($route['handler'], $matches);
                return;
            }
        }

        // Rota não encontrada
        $this->notFound();
    }

    /**
     * Obtém a URI limpa
     */
    private function getUri(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove o basePath se existir
        if ($this->basePath !== '' && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Chama o handler da rota
     */
    private function callHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            // Se for uma função anônima
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            // Se for uma string no formato "Controller@method"
            if (strpos($handler, '@') !== false) {
                list($controller, $method) = explode('@', $handler);
                $this->callControllerMethod($controller, $method, $params);
            } else {
                // Se for apenas o nome de uma view
                $this->renderView($handler);
            }
        } elseif (is_array($handler) && count($handler) === 2) {
            // Se for um array [Controller::class, 'method']
            $this->callControllerMethod($handler[0], $handler[1], $params);
        }
    }

    /**
     * Chama um método de controller
     */
    private function callControllerMethod(string $controller, string $method, array $params = []): void
    {
        // Adiciona namespace se não existir
        if (strpos($controller, '\\') === false) {
            $controller = "App\\Controllers\\{$controller}";
        }

        if (!class_exists($controller)) {
            $this->error500("Controller {$controller} não encontrado");
            return;
        }

        $controllerInstance = new $controller();

        if (!method_exists($controllerInstance, $method)) {
            $this->error500("Método {$method} não encontrado no controller {$controller}");
            return;
        }

        call_user_func_array([$controllerInstance, $method], $params);
    }

    /**
     * Renderiza uma view
     */
    private function renderView(string $viewPath, array $data = []): void
    {
        $viewFile = __DIR__ . '/../../views/' . ltrim($viewPath, '/');

        // Adiciona .php se não tiver extensão
        if (pathinfo($viewFile, PATHINFO_EXTENSION) === '') {
            $viewFile .= '.php';
        }

        if (!file_exists($viewFile)) {
            $this->notFound();
            return;
        }

        extract($data);
        require $viewFile;
    }

    /**
     * Retorna erro 404
     */
    private function notFound(): void
    {
        http_response_code(404);

        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => 'Rota não encontrada'
            ]);
        } else {
            echo '<h1>404 - Página não encontrada</h1>';
        }
        exit;
    }

    /**
     * Retorna erro 500
     */
    private function error500(string $message): void
    {
        http_response_code(500);

        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $message
            ]);
        } else {
            echo "<h1>500 - Erro interno do servidor</h1><p>{$message}</p>";
        }
        exit;
    }

    /**
     * Verifica se é uma requisição de API
     */
    private function isApiRequest(): bool
    {
        $uri = $this->getUri();
        return strpos($uri, '/api/') === 0 ||
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Redireciona para uma URL
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: {$url}", true, $statusCode);
        exit;
    }
}
