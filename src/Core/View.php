<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Простой рендер шаблонов: общий layout (header + footer) + представление.
 */
class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        // Защита от path traversal: используем только имя файла без пути.
        $view = basename($view);
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("Представление не найдено: {$view}");
        }

        require BASE_PATH . '/views/layout/header.php';
        require $viewFile;
        require BASE_PATH . '/views/layout/footer.php';
    }

    /**
     * Рендер шаблона админ-панели (отдельный layout с боковым меню).
     */
    public static function renderAdmin(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        $view = basename($view);
        $viewFile = BASE_PATH . '/views/admin/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("Представление не найдено: {$view}");
        }

        require BASE_PATH . '/views/admin/layout/header.php';
        require $viewFile;
        require BASE_PATH . '/views/admin/layout/footer.php';
    }
}
