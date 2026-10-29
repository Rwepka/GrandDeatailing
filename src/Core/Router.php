<?php
// ВАЖНО: здесь намеренно НЕТ declare(strict_types=1). Параметры маршрутов ({id}, {slug})
// приходят из URL как строки, и должны нестрого приводиться к типам сигнатур методов
// контроллеров (например, "5" → int для updateStatus(int $id)).

namespace App\Core;

/**
 * Простой маршрутизатор на регулярных выражениях.
 *
 * Поддерживает статические пути и параметры вида /booking/{id}.
 */
class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    /**
     * @param array{0: class-string, 1: string} $handler [ControllerClass, method]
     */
    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * @param array{0: class-string, 1: string} $handler [ControllerClass, method]
     */
    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes[$method] ?? [] as $path => $handler) {
            $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                // оставляем только именованные параметры маршрута
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($handler, array_values($params));
                return;
            }
        }

        http_response_code(404);
        View::render('404', ['title' => 'Страница не найдена']);
    }

    /**
     * @param array{0: class-string, 1: string} $handler
     * @param array<int, string> $params
     */
    private function call(array $handler, array $params): void
    {
        [$class, $method] = $handler;
        (new $class())->{$method}(...$params);
    }
}
