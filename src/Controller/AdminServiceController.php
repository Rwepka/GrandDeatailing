<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Category;
use App\Model\Service;

/**
 * CRUD услуг в админ-панели.
 */
class AdminServiceController
{
    public function index(): void
    {
        requireAdmin();

        $services = Service::latest();
        $categories = Category::all();

        // Карта id → title для вывода названия категории в таблице.
        $categoryTitles = [];
        foreach ($categories as $category) {
            $categoryTitles[(int) $category['id']] = $category['title'];
        }

        View::renderAdmin('services', [
            'title' => 'Услуги — GRAND DETAILING',
            'services' => $services,
            'categoryTitles' => $categoryTitles,
        ]);
    }

    public function create(): void
    {
        requireAdmin();

        View::renderAdmin('service_form', [
            'title' => 'Новая услуга',
            'categories' => Category::all(),
            'service' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(): void
    {
        requireAdmin();

        $categories = Category::all();
        $old = $this->postedOld();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::renderAdmin('service_form', [
                'title' => 'Новая услуга',
                'categories' => $categories,
                'service' => null,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        $result = $this->validate($categories);

        if ($result['errors'] !== []) {
            View::renderAdmin('service_form', [
                'title' => 'Новая услуга',
                'categories' => $categories,
                'service' => null,
                'errors' => $result['errors'],
                'old' => $old,
            ]);
            return;
        }

        Service::create($result['data'] + ['is_active' => 1]);
        redirect('/admin/services');
    }

    public function edit(int $id): void
    {
        requireAdmin();

        $service = Service::find($id);
        if ($service === null) {
            redirect('/admin/services');
        }

        View::renderAdmin('service_form', [
            'title' => 'Редактирование услуги',
            'categories' => Category::all(),
            'service' => $service,
            'errors' => [],
            'old' => $service,
        ]);
    }

    public function update(int $id): void
    {
        requireAdmin();

        $service = Service::find($id);
        if ($service === null) {
            redirect('/admin/services');
        }

        $categories = Category::all();
        $old = $this->postedOld();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::renderAdmin('service_form', [
                'title' => 'Редактирование услуги',
                'categories' => $categories,
                'service' => $service,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
            ]);
            return;
        }

        $result = $this->validate($categories);

        if ($result['errors'] !== []) {
            View::renderAdmin('service_form', [
                'title' => 'Редактирование услуги',
                'categories' => $categories,
                'service' => $service,
                'errors' => $result['errors'],
                'old' => $old,
            ]);
            return;
        }

        Service::update($id, $result['data']);
        redirect('/admin/services');
    }

    public function toggle(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/services');
        }

        $service = Service::find($id);
        if ($service !== null) {
            Service::setActive($id, (int) $service['is_active'] ? false : true);
        }

        redirect('/admin/services');
    }

    /**
     * Исходные значения формы из POST (для повторного заполнения при ошибках).
     *
     * @return array<string, string>
     */
    private function postedOld(): array
    {
        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'price_from' => trim((string) ($_POST['price_from'] ?? '')),
            'duration_min' => trim((string) ($_POST['duration_min'] ?? '')),
            'category_id' => trim((string) ($_POST['category_id'] ?? '')),
        ];
    }

    /**
     * Валидация формы услуги (общая для create/update).
     *
     * @param array<int, array<string, mixed>> $categories
     * @return array{data: array<string, mixed>, errors: array<int, string>}
     */
    private function validate(array $categories): array
    {
        $errors = [];

        // --- Название ---
        $title = trim((string) ($_POST['title'] ?? ''));
        $titleLen = function_exists('mb_strlen') ? mb_strlen($title) : strlen($title);
        if ($title === '') {
            $errors[] = 'Укажите название услуги.';
        } elseif ($titleLen > 140) {
            $errors[] = 'Название не должно быть длиннее 140 символов.';
        }

        // --- Slug ---
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $slugLen = function_exists('mb_strlen') ? mb_strlen($slug) : strlen($slug);
        if ($slug === '') {
            $errors[] = 'Укажите slug (латиница, цифры и дефис).';
        } elseif ($slugLen > 160) {
            $errors[] = 'Slug не должен быть длиннее 160 символов.';
        } elseif (preg_match('/^[a-z0-9-]+$/', $slug) !== 1) {
            $errors[] = 'Slug может содержать только латиницу, цифры и дефис.';
        }

        // --- Цена (необязательно, число) ---
        $priceRaw = trim((string) ($_POST['price_from'] ?? ''));
        $priceFrom = null;
        if ($priceRaw !== '') {
            if (filter_var($priceRaw, FILTER_VALIDATE_FLOAT) === false) {
                $errors[] = 'Цена должна быть числом.';
            } else {
                $priceFrom = (float) $priceRaw;
            }
        }

        // --- Длительность (необязательно, целое число) ---
        $durationRaw = trim((string) ($_POST['duration_min'] ?? ''));
        $durationMin = null;
        if ($durationRaw !== '') {
            if (filter_var($durationRaw, FILTER_VALIDATE_INT) === false) {
                $errors[] = 'Длительность должна быть целым числом.';
            } else {
                $durationMin = (int) $durationRaw;
            }
        }

        // --- Категория (обязательно, должна существовать) ---
        $validCategoryIds = [];
        foreach ($categories as $category) {
            $validCategoryIds[(int) $category['id']] = true;
        }

        $categoryIdRaw = trim((string) ($_POST['category_id'] ?? ''));
        $categoryId = null;
        if ($categoryIdRaw === '' || filter_var($categoryIdRaw, FILTER_VALIDATE_INT) === false) {
            $errors[] = 'Выберите категорию.';
        } else {
            $categoryId = (int) $categoryIdRaw;
            if (!isset($validCategoryIds[$categoryId])) {
                $errors[] = 'Выбранная категория не существует.';
            }
        }

        return [
            'data' => [
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'price_from' => $priceFrom,
                'duration_min' => $durationMin,
                'image' => null,
            ],
            'errors' => $errors,
        ];
    }
}
