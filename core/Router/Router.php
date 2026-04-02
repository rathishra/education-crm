<?php
namespace Core\Router;

class Router
{
    private array $routes = [];
    private array $namedRoutes = [];
    private string $prefix = '';
    private array $middleware = [];
    private ?string $currentGroup = null;

    /**
     * Add GET route
     */
    public function get(string $uri, $handler, string $name = null): self
    {
        return $this->addRoute('GET', $uri, $handler, $name);
    }

    /**
     * Add POST route
     */
    public function post(string $uri, $handler, string $name = null): self
    {
        return $this->addRoute('POST', $uri, $handler, $name);
    }

    /**
     * Add PUT route
     */
    public function put(string $uri, $handler, string $name = null): self
    {
        return $this->addRoute('PUT', $uri, $handler, $name);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $uri, $handler, string $name = null): self
    {
        return $this->addRoute('DELETE', $uri, $handler, $name);
    }

    /**
     * Route group with shared prefix and middleware
     */
    public function group(array $options, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddleware = $this->middleware;

        if (isset($options['prefix'])) {
            $this->prefix .= '/' . trim($options['prefix'], '/');
        }

        if (isset($options['middleware'])) {
            $mw = is_array($options['middleware']) ? $options['middleware'] : [$options['middleware']];
            $this->middleware = array_merge($this->middleware, $mw);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
    }

    private function addRoute(string $method, string $uri, $handler, ?string $name): self
    {
        $uri = $this->prefix . '/' . trim($uri, '/');
        $uri = '/' . trim($uri, '/');

        // Convert {param} to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $uri);
        $pattern = '#^' . $pattern . '$#';

        $route = [
            'method'     => $method,
            'uri'        => $uri,
            'pattern'    => $pattern,
            'handler'    => $handler,
            'middleware'  => $this->middleware,
            'name'       => $name,
        ];

        $this->routes[] = $route;

        if ($name) {
            $this->namedRoutes[$name] = $uri;
        }

        return $this;
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();



        // Support PUT/DELETE via POST _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $middleware) {
                    $middlewareClass = $this->resolveMiddleware($middleware);
                    if ($middlewareClass) {
                        $result = $middlewareClass->handle();
                        if ($result === false) {
                                return;
                        }
                    }
                }

                // Call handler
                try {
                    $this->callHandler($route['handler'], $params);
                } catch (\Throwable $e) {
                    if (config('app.debug')) {
                        echo '<pre>Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</pre>';
                    } else {
                        $this->handleNotFound();
                    }
                }
                return;
            }
        }

        // 404
        $this->handleNotFound();
    }

    private function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // Remove base path
        $basePath = parse_url(config('app.url', ''), PHP_URL_PATH) ?? '';
        if ($basePath && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        // Remove query string
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = '/' . trim($uri, '/');
        return $uri ?: '/';
    }

    private function callHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }

        if (is_string($handler) && strpos($handler, '@') !== false) {
            [$controller, $method] = explode('@', $handler);

            // Prepend namespace if not already fully qualified
            if (!str_starts_with($controller, 'App\\')) {
                $controller = 'App\\Controllers\\' . $controller;
            }

            if (!class_exists($controller)) {
                appLog("Controller not found: {$controller}", 'error');
                $this->handleNotFound();
                return;
            }

            $instance = new $controller();

            if (!method_exists($instance, $method)) {
                appLog("Method not found: {$controller}@{$method}", 'error');
                $this->handleNotFound();
                return;
            }

            call_user_func_array([$instance, $method], $params);
        }
    }

    private function resolveMiddleware(string $name): ?object
    {
        $map = [
            'auth'         => \App\Middleware\AuthMiddleware::class,
            'guest'        => \App\Middleware\GuestMiddleware::class,
            'csrf'         => \App\Middleware\CsrfMiddleware::class,
            'permission'   => \App\Middleware\PermissionMiddleware::class,
            'api'          => \App\Middleware\ApiAuthMiddleware::class,
            'portal_auth'  => \App\Middleware\PortalAuthMiddleware::class,
            'portal_guest' => \App\Middleware\PortalGuestMiddleware::class,
        ];

        // Handle permission:slug format
        if (str_contains($name, ':')) {
            [$name, $param] = explode(':', $name, 2);
            $class = $map[$name] ?? null;
            if ($class && class_exists($class)) {
                return new $class($param);
            }
        }

        $class = $map[$name] ?? null;
        if ($class && class_exists($class)) {
            return new $class();
        }

        return null;
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        if (isAjax()) {
            jsonResponse(['error' => 'Not Found'], 404);
        } else {
            $viewFile = BASE_PATH . '/app/Views/errors/404.php';
            if (file_exists($viewFile)) {
                require $viewFile;
            } else {
                echo '<h1>404 - Page Not Found</h1>';
            }
        }
    }

    /**
     * Generate URL by route name
     */
    public function route(string $name, array $params = []): string
    {
        $uri = $this->namedRoutes[$name] ?? '#';
        foreach ($params as $key => $value) {
            $uri = str_replace('{' . $key . '}', $value, $uri);
        }
        return url($uri);
    }
}
