<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Post;

/**
 * Публичная страница новостей.
 */
class NewsController
{
    public function index(): void
    {
        View::render('news', [
            'title' => 'Новости детейлинг-студии GRAND DETAILING, Туапсе',
            'metaDescription' => 'Новости и полезные материалы о детейлинге в Туапсе: полировка, керамика, уход за кузовом и салоном от студии GRAND DETAILING.',
            'posts' => Post::published(),
        ]);
    }
}
