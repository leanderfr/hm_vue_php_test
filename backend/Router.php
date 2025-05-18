<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function addGet(string $path, Closure $handler): void
    {
        if ( $_SERVER['REQUEST_METHOD'] !== 'GET' ) {
          http_response_code(500);   
          die( 'Method not allowed' );
        }
        $this->routes[$path] = $handler;

    }

    public function dispatch(string $path): void
    {
        foreach ($this->routes as $route => $handler) {

            $pattern = preg_replace("#\{\w+\}#", "([^\/]+)", $route);

            if (preg_match("#^$pattern$#", $path, $matches)) {

                array_shift($matches);

                call_user_func_array($handler, $matches);

                return;

            }
        }

        echo "Page not found or invalid route";
    }
}