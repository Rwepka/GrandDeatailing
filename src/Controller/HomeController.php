<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Faq;
use App\Model\Service;
use App\Model\ServicePrice;

class HomeController
{
    public function index(): void
    {
        // Мастер при открытии сайта сразу попадает в раздел «Заявки».
        if (isMaster()) {
            redirect('/admin/bookings');
        }

        $activeServices = Service::active();
        $servicePrices = ServicePrice::mapForServices(array_column($activeServices, 'id'));

        $services = array_map(
            static function (array $s): array {
                return [
                    'slug' => $s['slug'],
                    'title' => $s['title'],
                    'short_description' => trim((string) ($s['short_description'] ?? '')),
                    'price' => price($s['price_from'] === null ? null : (float) $s['price_from']),
                ];
            },
            $activeServices
        );

        View::render('home', [
            'title' => 'Детейлинг в Туапсе — GRAND DETAILING: полировка, керамика, химчистка',
            'metaDescription' => 'Детейлинг-студия GRAND DETAILING в Туапсе: мойка, полировка, керамика, плёнка PPF, тонировка и химчистка салона. Онлайн-запись, гарантия.',
            'services' => $services,
            'carClasses' => car_classes(),
            'calculatorServices' => $activeServices,
            'servicePrices' => $servicePrices,
            'faqs' => Faq::active(),
        ]);
    }
}
