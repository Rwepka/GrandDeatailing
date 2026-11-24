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
        $lastmod = date('Y-m-d');

        // path => [priority, changefreq]
        $pages = [
            '/' => ['1.0', 'weekly'],
            '/services' => ['0.9', 'weekly'],
            '/booking' => ['0.8', 'monthly'],
            '/reviews' => ['0.6', 'weekly'],
            '/news' => ['0.6', 'weekly'],
            '/contacts' => ['0.6', 'monthly'],
        ];

        $services = Service::active();

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $path => [$priority, $changefreq]) {
            $loc = $baseUrl . ($path === '/' ? '/' : $path);
            echo '  <url>'
                . '<loc>' . e($loc) . '</loc>'
                . '<lastmod>' . e($lastmod) . '</lastmod>'
                . '<changefreq>' . e($changefreq) . '</changefreq>'
                . '<priority>' . e($priority) . '</priority>'
                . '</url>' . "\n";
        }

        foreach ($services as $service) {
            $slug = (string) ($service['slug'] ?? '');
            if ($slug !== '') {
                echo '  <url>'
                    . '<loc>' . e($baseUrl . url('/services/' . $slug)) . '</loc>'
                    . '<lastmod>' . e($lastmod) . '</lastmod>'
                    . '<changefreq>monthly</changefreq>'
                    . '<priority>0.7</priority>'
                    . '</url>' . "\n";
            }
        }

        echo '</urlset>' . "\n";
    }
}
