<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Service;

/**
 * Динамический sitemap.xml (актуальный домен из config('app.url')).
 */
class SitemapController
{
    public function index(): void
    {
        header('Content-Type: application/xml; charset=utf-8');

        $baseUrl = rtrim((string) config('app.url'), '/');

        $paths = ['/', '/services', '/news', '/reviews', '/contacts', '/booking'];
        $services = Service::active();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($paths as $path) {
            echo '  <url><loc>' . e($baseUrl . $path) . '</loc></url>' . "\n";
        }

        foreach ($services as $service) {
            $slug = (string) ($service['slug'] ?? '');
            if ($slug !== '') {
                echo '  <url><loc>' . e($baseUrl . url('/services/' . $slug)) . '</loc></url>' . "\n";
            }
        }

        echo '</urlset>' . "\n";
    }
}
