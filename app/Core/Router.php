<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $handler = $this->routes[$method][$path] ?? null;
        $params = [];
        if ($handler === null) {
            // Fall back to {param} patterns, e.g. /db/item/{id}.
            foreach ($this->routes[$method] ?? [] as $pattern => $h) {
                if (!str_contains($pattern, '{')) {
                    continue;
                }
                $re = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
                if (preg_match($re, $path, $m)) {
                    $handler = $h;
                    $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                    break;
                }
            }
        }
        if ($handler === null) {
            if (str_starts_with($path, '/api/')) {
                self::json(['error' => 'Not found'], 404);
                return;
            }
            Theme::render('404', ['title' => '404'], 404);
            return;
        }

        $handler($params);
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
