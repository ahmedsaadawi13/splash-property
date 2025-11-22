// FILE: /app/core/Router.php
<?php

/**
 * Router Class
 * Handles URL routing and dispatching
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class Router
{
    private $routes = [];
    private $namedRoutes = [];

    /**
     * Add GET route
     */
    public function get($pattern, $callback, $name = null)
    {
        $this->addRoute('GET', $pattern, $callback, $name);
    }

    /**
     * Add POST route
     */
    public function post($pattern, $callback, $name = null)
    {
        $this->addRoute('POST', $pattern, $callback, $name);
    }

    /**
     * Add route
     */
    private function addRoute($method, $pattern, $callback, $name = null)
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];

        if ($name) {
            $this->namedRoutes[$name] = $pattern;
        }
    }

    /**
     * Dispatch current request
     */
    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];

        // Remove base path if exists
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertPattern($route['pattern']);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match
                return $this->invokeCallback($route['callback'], $matches);
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found";
        exit;
    }

    /**
     * Convert route pattern to regex
     */
    private function convertPattern($pattern)
    {
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
        return '#^' . $pattern . '$#';
    }

    /**
     * Invoke callback
     */
    private function invokeCallback($callback, $params = [])
    {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_string($callback)) {
            list($controller, $method) = explode('@', $callback);

            $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerObj = new $controller();
                return call_user_func_array([$controllerObj, $method], $params);
            }
        }

        throw new Exception("Invalid route callback");
    }

    /**
     * Generate URL from named route
     */
    public function url($name, $params = [])
    {
        if (!isset($this->namedRoutes[$name])) {
            throw new Exception("Route {$name} not found");
        }

        $url = $this->namedRoutes[$name];
        foreach ($params as $key => $value) {
            $url = str_replace('{' . $key . '}', $value, $url);
        }

        return $url;
    }
}
