<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Setting;

/**
 * Управление настройками сайта в админ-панели.
 */
class AdminSettingsController
{
    /**
     * Список настроек: ключ → [подпись, значение по умолчанию].
     *
     * @var array<string, array{label: string, default: string}>
     */
    private const SETTINGS = [
        'address'    => ['label' => 'Адрес',             'default' => 'г. Туапсе, ул. Богдана Хмельницкого, 16'],
        'phone'      => ['label' => 'Телефон',           'default' => '+7 (988) 413-07-77'],
        'work_hours' => ['label' => 'Режим работы',      'default' => 'Ежедневно с 9:00 до 20:00'],
        'email'      => ['label' => 'Email',             'default' => ''],
        'telegram'   => ['label' => 'Telegram (ссылка)', 'default' => ''],
        'whatsapp'   => ['label' => 'WhatsApp (ссылка)', 'default' => ''],
        'vk'         => ['label' => 'VK (ссылка)',       'default' => ''],
        'map_lat'    => ['label' => 'Широта карты',      'default' => '44.104854'],
        'map_lng'    => ['label' => 'Долгота карты',     'default' => '39.087091'],
        'telegram_bot_token'      => ['label' => 'Telegram: токен бота', 'default' => ''],
        'telegram_master_chat_id' => ['label' => 'Telegram: chat_id для заявок', 'default' => ''],
    ];

    public function index(): void
    {
        requireAdmin();

        $values = [];
        foreach (self::SETTINGS as $key => $meta) {
            $values[$key] = setting($key, $meta['default']);
        }

        View::renderAdmin('settings', [
            'title' => 'Настройки',
            'fields' => self::SETTINGS,
            'values' => $values,
            'flash' => flash(),
        ]);
    }

    public function store(): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/settings');
        }

        foreach (array_keys(self::SETTINGS) as $key) {
            Setting::set($key, trim((string) ($_POST[$key] ?? '')));
        }

        setFlash('Настройки сохранены.');
        redirect('/admin/settings');
    }
}
