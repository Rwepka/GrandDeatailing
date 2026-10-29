<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Category;
use App\Model\Service;

class ServiceController
{
    public function index(): void
    {
        $categories = Category::all();
        $services = Service::active();

        $servicesByCategory = [];
        foreach ($services as $service) {
            $servicesByCategory[(int) $service['category_id']][] = $service;
        }

        View::render('services', [
            'title' => 'Услуги — GRAND DETAILING',
            'categories' => $categories,
            'servicesByCategory' => $servicesByCategory,
        ]);
    }

    public function show(string $slug): void
    {
        $service = Service::findBySlug($slug);

        if ($service === null) {
            http_response_code(404);
            View::render('404', ['title' => 'Страница не найдена']);
            return;
        }

        $category = Category::find((int) $service['category_id']);
        $categoryTitle = $category['title'] ?? null;

        View::render('service', [
            'title' => $service['title'],
            'service' => $service,
            'categoryTitle' => $categoryTitle,
        ]);
    }
}
