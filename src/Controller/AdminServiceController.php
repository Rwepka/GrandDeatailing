<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\AuditLog;
use App\Model\Category;
use App\Model\Service;
use App\Model\ServicePrice;

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
            'prices' => [],
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
                'prices' => [],
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
                'prices' => [],
            ]);
            return;
        }

        $serviceId = Service::create($result['data'] + ['is_active' => 1]);
        ServicePrice::save($serviceId, $result['prices']);
        AuditLog::record('service.create', 'service', $serviceId, (string) $result['data']['title']);
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
            'prices' => ServicePrice::forService($id),
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
                'prices' => [],
            ]);
            return;
        }

        $result = $this->validate($categories, $id);

        if ($result['errors'] !== []) {
            View::renderAdmin('service_form', [
                'title' => 'Редактирование услуги',
                'categories' => $categories,
                'service' => $service,
                'errors' => $result['errors'],
                'old' => $old,
                'prices' => [],
            ]);
            return;
        }

        Service::update($id, $result['data']);
        ServicePrice::save($id, $result['prices']);
        AuditLog::record('service.update', 'service', $id, (string) $result['data']['title']);
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
            $active = (int) $service['is_active'] ? false : true;
            Service::setActive($id, $active);
            AuditLog::record('service.toggle', 'service', $id, $active ? 'on' : 'off');
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
        $prices = [];
        foreach (car_classes() as $bodyType) {
            $prices[$bodyType] = trim((string) ($_POST['prices'][$bodyType] ?? ''));
        }

        return [
            'title' => trim((string) ($_POST['title'] ?? '')),
            'slug' => trim((string) ($_POST['slug'] ?? '')),
            'short_description' => trim((string) ($_POST['short_description'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'includes' => trim((string) ($_POST['includes'] ?? '')),
            'process' => trim((string) ($_POST['process'] ?? '')),
            'price_from' => trim((string) ($_POST['price_from'] ?? '')),
            'duration_min' => trim((string) ($_POST['duration_min'] ?? '')),
            'category_id' => trim((string) ($_POST['category_id'] ?? '')),
            'prices' => $prices,
        ];
    }

    /**
     * Валидация формы услуги (общая для create/update).
     *
     * @param array<int, array<string, mixed>> $categories
     * @param int|null $ignoreId id услуги, которую не учитываем при проверке slug
     * @return array{data: array<string, mixed>, prices: array<string, float|null>, errors: array<int, string>}
     */
    private function validate(array $categories, ?int $ignoreId = null): array
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
        } elseif (Service::slugExists($slug, $ignoreId)) {
            $errors[] = 'Услуга с таким slug уже существует.';
        }

        // --- Краткое и полное описание ---
        $shortDescription = trim((string) ($_POST['short_description'] ?? ''));
        $shortLen = function_exists('mb_strlen') ? mb_strlen($shortDescription) : strlen($shortDescription);
        if ($shortLen > 255) {
            $errors[] = 'Краткое описание не должно быть длиннее 255 символов.';
        }

        $description = trim((string) ($_POST['description'] ?? ''));
        $descriptionLen = function_exists('mb_strlen') ? mb_strlen($description) : strlen($description);
        if ($descriptionLen > 10000) {
            $errors[] = 'Полное описание слишком длинное (максимум 10 000 символов).';
        }

        $includes = trim((string) ($_POST['includes'] ?? ''));
        $process = trim((string) ($_POST['process'] ?? ''));
        foreach (['Что входит' => $includes, 'Как проходит работа' => $process] as $label => $value) {
            $valueLen = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
            if ($valueLen > 10000) {
                $errors[] = $label . ': максимум 10 000 символов.';
            }
        }

        // --- Длительность (необязательно, целое число) ---
        $durationRaw = trim((string) ($_POST['duration_min'] ?? ''));
        $durationMin = null;
        if ($durationRaw !== '') {
            if (filter_var($durationRaw, FILTER_VALIDATE_INT) === false) {
                $errors[] = 'Длительность должна быть целым числом.';
            } elseif ((int) $durationRaw < 0) {
                $errors[] = 'Длительность не может быть отрицательной.';
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

        // --- Цены по типам кузова (необязательно, число) ---
        $prices = [];
        foreach (car_classes() as $bodyType) {
            $raw = trim((string) ($_POST['prices'][$bodyType] ?? ''));
            if ($raw === '') {
                $prices[$bodyType] = null;
                continue;
            }
            if (filter_var($raw, FILTER_VALIDATE_FLOAT) === false) {
                $errors[] = 'Цена «' . $bodyType . '» должна быть числом.';
            } elseif ((float) $raw < 0) {
                $errors[] = 'Цена «' . $bodyType . '» не может быть отрицательной.';
            } else {
                $prices[$bodyType] = (float) $raw;
            }
        }

        // --- Базовая «цена от» (используется, если цены по кузовам не заданы) ---
        $basePrice = null;
        $basePriceRaw = trim((string) ($_POST['price_from'] ?? ''));
        if ($basePriceRaw !== '') {
            if (filter_var($basePriceRaw, FILTER_VALIDATE_FLOAT) === false) {
                $errors[] = 'Цена «от» должна быть числом.';
            } elseif ((float) $basePriceRaw < 0) {
                $errors[] = 'Цена «от» не может быть отрицательной.';
            } else {
                $basePrice = (float) $basePriceRaw;
            }
        }

        // --- «Цена от» = минимальная из цен по кузовам ---
        $priceFrom = null;
        foreach ($prices as $price) {
            if ($price !== null && ($priceFrom === null || $price < $priceFrom)) {
                $priceFrom = $price;
            }
        }
        if ($priceFrom === null) {
            $priceFrom = $basePrice;
        }

        return [
            'data' => [
                'category_id' => $categoryId,
                'title' => $title,
                'slug' => $slug,
                'short_description' => $shortDescription !== '' ? $shortDescription : null,
                'description' => $description !== '' ? $description : null,
                'includes' => $includes !== '' ? $includes : null,
                'process' => $process !== '' ? $process : null,
                'price_from' => $priceFrom,
                'duration_min' => $durationMin,
                'image' => null,
            ],
            'prices' => $prices,
            'errors' => $errors,
        ];
    }
}
