<?php
namespace App\Core;

class Router
{
    /** @var array<string,array<int,array{pattern:string,handler:array}>> */
    protected $routes = [];
    protected string $currentPath = '/';

    public function __construct()
    {
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        // Public pages
        $this->add('GET', '/', ['App\\Controllers\\LandingController', 'index']);
        $this->add('GET', '/agencia', ['App\\Controllers\\AgencyController', 'index']);
        $this->add('GET', '/login', ['App\\Controllers\\AuthController', 'loginForm']);
        $this->add('POST', '/login', ['App\\Controllers\\AuthController', 'login']);
        $this->add('GET', '/register', ['App\\Controllers\\AuthController', 'registerForm']);
        $this->add('POST', '/register', ['App\\Controllers\\AuthController', 'register']);
        $this->add('GET', '/logout', ['App\\Controllers\\AuthController', 'logout']);

        // Dashboard & CRM legacy routes (mantidos por compatibilidade).
        $this->add('GET', '/dashboard', ['App\\Controllers\\DashboardController', 'index']);

        // Leads CRUD
        $this->add('GET', '/leads/create', ['App\\Controllers\\LeadController', 'createForm']);
        $this->add('POST', '/leads/create', ['App\\Controllers\\LeadController', 'create']);
        $this->add('GET', '/leads/edit', ['App\\Controllers\\LeadController', 'editForm']);
        $this->add('POST', '/leads/edit', ['App\\Controllers\\LeadController', 'edit']);
        $this->add('POST', '/leads/delete', ['App\\Controllers\\LeadController', 'delete']);

        // Stages CRUD
        $this->add('GET', '/stages/create', ['App\\Controllers\\StageController', 'createForm']);
        $this->add('POST', '/stages/create', ['App\\Controllers\\StageController', 'create']);
        $this->add('GET', '/stages/edit', ['App\\Controllers\\StageController', 'editForm']);
        $this->add('POST', '/stages/edit', ['App\\Controllers\\StageController', 'edit']);
        $this->add('POST', '/stages/delete', ['App\\Controllers\\StageController', 'delete']);

        // Perfil & API legacy
        $this->add('GET', '/profile', ['App\\Controllers\\UserController', 'profileForm']);
        $this->add('POST', '/profile', ['App\\Controllers\\UserController', 'profile']);
        $this->add('POST', '/api/kanban/move', ['App\\Controllers\\ApiKanbanController', 'move']);

        // Password reset
        $this->add('GET', '/reset', ['App\\Controllers\\PasswordResetController', 'requestForm']);
        $this->add('POST', '/reset', ['App\\Controllers\\PasswordResetController', 'request']);
        $this->add('GET', '/reset/{token}', ['App\\Controllers\\PasswordResetController', 'form']);
        $this->add('POST', '/reset/{token}', ['App\\Controllers\\PasswordResetController', 'update']);

        // Novas rotas API BRN Pixel (a serem implementadas).
        $this->add('POST', '/api/auth/register', ['App\\Controllers\\Api\\AuthController', 'register']);
        $this->add('POST', '/api/auth/login', ['App\\Controllers\\Api\\AuthController', 'login']);
        $this->add('POST', '/api/auth/logout', ['App\\Controllers\\Api\\AuthController', 'logout']);
        $this->add('POST', '/api/ingest', ['App\\Controllers\\Api\\IngestController', 'ingest']);
        $this->add('GET', '/api/pixels', ['App\\Controllers\\Api\\PixelsController', 'index']);
        $this->add('POST', '/api/pixels', ['App\\Controllers\\Api\\PixelsController', 'store']);
        $this->add('GET', '/api/pixels/{id}', ['App\\Controllers\\Api\\PixelsController', 'show']);
        $this->add('PATCH', '/api/pixels/{id}', ['App\\Controllers\\Api\\PixelsController', 'update']);
        $this->add('DELETE', '/api/pixels/{id}', ['App\\Controllers\\Api\\PixelsController', 'deactivate']);
        $this->add('POST', '/api/pixels/{id}/tokens', ['App\\Controllers\\Api\\PixelsController', 'issueToken']);
        $this->add('DELETE', '/api/pixels/{id}/tokens/{tokenId}', ['App\\Controllers\\Api\\PixelsController', 'revokeToken']);
        $this->add('GET', '/api/events', ['App\\Controllers\\Api\\EventsController', 'index']);
        $this->add('GET', '/api/events/metrics', ['App\\Controllers\\Api\\EventsController', 'metrics']);
        $this->add('GET', '/api/events/{id}', ['App\\Controllers\\Api\\EventsController', 'show']);
        $this->add('POST', '/api/consents', ['App\\Controllers\\Api\\ConsentsController', 'store']);
        $this->add('GET', '/api/consents', ['App\\Controllers\\Api\\ConsentsController', 'index']);
        $this->add('POST', '/api/consents/{id}/revoke', ['App\\Controllers\\Api\\ConsentsController', 'revoke']);
        $this->add('GET', '/api/webhooks', ['App\\Controllers\\Api\\WebhooksController', 'index']);
        $this->add('POST', '/api/webhooks', ['App\\Controllers\\Api\\WebhooksController', 'store']);
        $this->add('PATCH', '/api/webhooks/{id}', ['App\\Controllers\\Api\\WebhooksController', 'update']);
        $this->add('DELETE', '/api/webhooks/{id}', ['App\\Controllers\\Api\\WebhooksController', 'destroy']);
        $this->add('POST', '/api/webhooks/{id}/rotate', ['App\\Controllers\\Api\\WebhooksController', 'rotate']);
        $this->add('POST', '/api/webhooks/run', ['App\\Controllers\\Api\\WebhooksController', 'run']);
    }

    protected function add(string $method, string $pattern, array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][] = [
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $method = strtoupper($method);
        $this->currentPath = $path;

        if (!isset($this->routes[$method])) {
            $this->abort(404, 'Rota não encontrada.');
            return;
        }

        foreach ($this->routes[$method] as $route) {
            $params = $this->match($path, $route['pattern']);
            if ($params !== null) {
                [$controller, $action] = $route['handler'];
                if (!class_exists($controller) || !method_exists($controller, $action)) {
                    $this->abort(500, 'Controlador inválido.');
                    return;
                }
                $instance = new $controller();
                $instance->$action(...$params);
                return;
            }
        }

        $this->abort(404, 'Página não encontrada');
    }

    /**
     * @return array<int,string>|null
     */
    protected function match(string $path, string $pattern): ?array
    {
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_-]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (preg_match($regex, $path, $matches)) {
            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[] = $value;
                }
            }
            return $params;
        }
        return null;
    }

    protected function abort(int $status, string $message): void
    {
        if (str_starts_with($this->currentPath, '/api/')) {
            json_response(['error' => $message], $status);
            return;
        }
        http_response_code($status);
        echo $message;
    }
}
