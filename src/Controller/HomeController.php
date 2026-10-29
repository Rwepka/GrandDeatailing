<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Service;

class HomeController
{
    public function index(): void
    {
        // Мастер при открытии сайта сразу попадает в раздел «Заявки».
        if (isMaster()) {
            redirect('/admin/bookings');
        }

        $activeServices = Service::active();

        $services = array_map(
            static function (array $s): array {
                return [
                    'slug' => $s['slug'],
                    'title' => $s['title'],
                    'price' => price($s['price_from'] === null ? null : (float) $s['price_from']),
                ];
            },
            $activeServices
        );

        View::render('home', [
            'title' => 'GRAND DETAILING — детейлинг-студия',
            'services' => $services,
            'carClasses' => car_classes(),
            'calculatorServices' => $activeServices,
        ]);
    }
}
