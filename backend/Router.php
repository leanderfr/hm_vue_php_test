<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function addGet(string $path, Closure $handler): void
    {
        $this->routes[$path] = $handler;
    }

    public function addPost(string $path, Closure $handler): void
    {
        $this->routes[$path] = $handler;
    }

    public function addDelete(string $path, Closure $handler): void
    {
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