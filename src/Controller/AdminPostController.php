<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Post;

/**
 * Управление новостями в админ-панели.
 */
class AdminPostController
{
    public function index(): void
    {
        requireAdmin();

        View::renderAdmin('posts', [
            'title' => 'Новости',
            'posts' => Post::allForAdmin(),
            'flash' => flash(),
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(): void
    {
        requireAdmin();

        $old = [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'content' => (string) ($_POST['content'] ?? ''),
        ];

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::renderAdmin('posts', [
                'title' => 'Новости',
                'posts' => Post::allForAdmin(),
                'flash' => null,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));

        $errors = [];

        $titleLen = function_exists('mb_strlen') ? mb_strlen($title) : strlen($title);
        if ($title === '') {
            $errors[] = 'Укажите заголовок новости.';
        } elseif ($titleLen > 160) {
            $errors[] = 'Заголовок не должен быть длиннее 160 символов.';
        }

        if ($content === '') {
            $errors[] = 'Укажите текст новости.';
        }

        if ($errors !== []) {
            View::renderAdmin('posts', [
                'title' => 'Новости',
                'posts' => Post::allForAdmin(),
                'flash' => null,
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        Post::create(['title' => $title, 'content' => $content]);
        setFlash('Новость опубликована.');
        redirect('/admin/posts');
    }

    public function toggle(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/posts');
        }

        Post::toggle($id);
        redirect('/admin/posts');
    }

    public function delete(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/posts');
        }

        Post::delete($id);
        setFlash('Новость удалена.');
        redirect('/admin/posts');
    }
}
