<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\AuditLog;
use App\Model\Faq;

/**
 * CRUD вопросов и ответов для блока «Частые вопросы» на главной.
 */
class AdminFaqController
{
    public function index(): void
    {
        requireAdmin();

        View::renderAdmin('faqs', [
            'title' => 'Частые вопросы',
            'faqs' => Faq::ordered(),
            'flash' => flash(),
        ]);
    }

    public function create(): void
    {
        requireAdmin();

        View::renderAdmin('faq_form', [
            'title' => 'Новый вопрос',
            'faq' => null,
            'errors' => [],
            'old' => [],
        ]);
    }

    public function store(): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            $this->renderForm('Новый вопрос', null, ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'], $this->postedOld());
            return;
        }

        $result = $this->validate();

        if ($result['errors'] !== []) {
            $this->renderForm('Новый вопрос', null, $result['errors'], $this->postedOld());
            return;
        }

        $id = Faq::create($result['data']);
        AuditLog::record('faq.create', 'faq', $id, (string) $result['data']['question']);
        setFlash('Вопрос добавлен.');
        redirect('/admin/faqs');
    }

    public function edit(int $id): void
    {
        requireAdmin();

        $faq = Faq::find($id);
        if ($faq === null) {
            redirect('/admin/faqs');
        }

        View::renderAdmin('faq_form', [
            'title' => 'Редактирование вопроса',
            'faq' => $faq,
            'errors' => [],
            'old' => $faq,
        ]);
    }

    public function update(int $id): void
    {
        requireAdmin();

        $faq = Faq::find($id);
        if ($faq === null) {
            redirect('/admin/faqs');
        }

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            $this->renderForm('Редактирование вопроса', $faq, ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'], $this->postedOld());
            return;
        }

        $result = $this->validate();

        if ($result['errors'] !== []) {
            $this->renderForm('Редактирование вопроса', $faq, $result['errors'], $this->postedOld());
            return;
        }

        Faq::update($id, $result['data']);
        AuditLog::record('faq.update', 'faq', $id, (string) $result['data']['question']);
        setFlash('Вопрос сохранён.');
        redirect('/admin/faqs');
    }

    public function toggle(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/faqs');
        }

        $faq = Faq::find($id);
        if ($faq !== null) {
            $active = (int) ($faq['is_active'] ?? 0) === 1 ? false : true;
            Faq::setActive($id, $active);
            AuditLog::record('faq.toggle', 'faq', $id, $active ? 'on' : 'off');
        }

        redirect('/admin/faqs');
    }

    public function delete(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/faqs');
        }

        Faq::delete($id);
        AuditLog::record('faq.delete', 'faq', $id, '');
        setFlash('Вопрос удалён.');
        redirect('/admin/faqs');
    }

    /**
     * @param array<int, string> $errors
     * @param array<string, string> $old
     */
    private function renderForm(string $title, ?array $faq, array $errors, array $old): void
    {
        View::renderAdmin('faq_form', [
            'title' => $title,
            'faq' => $faq,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function postedOld(): array
    {
        return [
            'question' => trim((string) ($_POST['question'] ?? '')),
            'answer' => trim((string) ($_POST['answer'] ?? '')),
            'sort_order' => trim((string) ($_POST['sort_order'] ?? '0')),
            'is_active' => isset($_POST['is_active']) ? '1' : '0',
        ];
    }

    /**
     * @return array{data: array<string, mixed>, errors: array<int, string>}
     */
    private function validate(): array
    {
        $errors = [];

        $question = trim((string) ($_POST['question'] ?? ''));
        $questionLen = function_exists('mb_strlen') ? mb_strlen($question) : strlen($question);
        if ($question === '') {
            $errors[] = 'Укажите вопрос.';
        } elseif ($questionLen > 255) {
            $errors[] = 'Вопрос не должен быть длиннее 255 символов.';
        }

        $answer = trim((string) ($_POST['answer'] ?? ''));
        if ($answer === '') {
            $errors[] = 'Укажите ответ.';
        }

        $sortRaw = trim((string) ($_POST['sort_order'] ?? '0'));
        $sortOrder = 0;
        if ($sortRaw !== '') {
            if (filter_var($sortRaw, FILTER_VALIDATE_INT) === false) {
                $errors[] = 'Порядок сортировки должен быть целым числом.';
            } else {
                $sortOrder = (int) $sortRaw;
            }
        }

        return [
            'data' => [
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $sortOrder,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ],
            'errors' => $errors,
        ];
    }
}
