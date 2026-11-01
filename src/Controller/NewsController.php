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
            'title' => 'Новости',
            'posts' => Post::published(),
        ]);
    }
}
