<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Динамический robots.txt: Sitemap указан абсолютным URL из config('app.url').
 */
class RobotsController
{
    public function index(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        $baseUrl = rtrim((string) config('app.url'), '/');

        echo "User-agent: *\n";
        echo "Disallow: /cabinet/\n";
        echo "Disallow: /admin/\n";
        echo "\n";
        echo 'Sitemap: ' . $baseUrl . "/sitemap.xml\n";
    }
}
