<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Category;
use App\Model\Service;
use App\Model\ServicePrice;

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
            'title' => 'Услуги детейлинга в Туапсе — GRAND DETAILING',
            'metaDescription' => 'Полировка кузова, керамика, оклейка плёнкой PPF, тонировка, химчистка салона и мойка в Туапсе. Цены, описание работ и онлайн-запись в GRAND DETAILING.',
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
        $prices = ServicePrice::forService((int) $service['id']);
        $priceText = price($service['price_from'] === null ? null : (float) $service['price_from']);

        $shortDescription = trim((string) ($service['short_description'] ?? ''));
        $fullDescription = trim((string) ($service['description'] ?? ''));
        $metaBase = $shortDescription !== '' ? $shortDescription : $fullDescription;
        if ($metaBase === '') {
            $metaBase = 'Услуга детейлинг-студии GRAND DETAILING. Онлайн-запись.';
        }
        if (function_exists('mb_substr')) {
            $metaBase = mb_substr($metaBase, 0, 120);
        } else {
            $metaBase = substr($metaBase, 0, 120);
        }

        $priceMeta = $service['price_from'] !== null
            ? 'Цена ' . $priceText
            : 'Цена по запросу';

        View::render('service', [
            'title' => $service['title'] . ' в Туапсе — GRAND DETAILING',
            'metaDescription' => rtrim(trim($metaBase), '.') . '. ' . $priceMeta . '. Детейлинг-студия GRAND DETAILING, Туапсе. Онлайн-запись.',
            'service' => $service,
            'categoryTitle' => $categoryTitle,
            'prices' => $prices,
        ]);
    }
}
