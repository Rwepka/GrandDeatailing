<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Booking;
use App\Model\Review;

class ReviewController
{
    public function index(): void
    {
        $reviews = Review::published();

        View::render('reviews', [
            'title' => 'Отзывы',
            'reviews' => $reviews,
        ]);
    }

    public function create(): void
    {
        requireAuth();
        $user = auth();
        $bookingId = (int) ($_GET['booking_id'] ?? 0);
        $booking = Booking::find($bookingId);

        if ($booking === null
            || (int) $booking['user_id'] !== (int) $user['id']
            || $booking['status'] !== 'done'
        ) {
            redirect('/cabinet');
        }

        if (Review::findByBooking($bookingId) !== null) {
            redirect('/cabinet');
        }

        View::render('review_form', [
            'title' => 'Оставить отзыв',
            'booking' => $booking,
            'errors' => [],
            'old' => [],
            'flash' => null,
        ]);
    }

    public function store(): void
    {
        requireAuth();

        // Honeypot-ловушка для ботов.
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            redirect('/cabinet');
        }

        $old = [
            'rating' => trim((string) ($_POST['rating'] ?? '')),
            'text' => trim((string) ($_POST['text'] ?? '')),
        ];

        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $booking = Booking::find($bookingId);

        // CSRF-защита.
        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            View::render('review_form', [
                'title' => 'Оставить отзыв',
                'booking' => $booking,
                'errors' => ['Сессия устарела. Обновите страницу и попробуйте ещё раз.'],
                'old' => $old,
                'flash' => null,
            ]);
            return;
        }

        // Только выполненная заявка текущего клиента и без уже оставленного отзыва.
        if ($booking === null
            || (int) $booking['user_id'] !== (int) auth()['id']
            || $booking['status'] !== 'done'
            || Review::findByBooking($bookingId) !== null
        ) {
            redirect('/cabinet');
        }

        $errors = [];

        $ratingRaw = trim((string) ($_POST['rating'] ?? ''));
        $rating = (int) $ratingRaw;
        if ($ratingRaw === '' || $rating < 1 || $rating > 5 || (string) $rating !== $ratingRaw) {
            $errors[] = 'Выберите оценку';
        }

        $text = trim((string) ($_POST['text'] ?? ''));
        $text = $text === '' ? null : $text;

        if ($errors !== []) {
            View::render('review_form', [
                'title' => 'Оставить отзыв',
                'booking' => $booking,
                'errors' => $errors,
                'old' => $old,
                'flash' => null,
            ]);
            return;
        }

        Review::create([
            'user_id' => (int) auth()['id'],
            'booking_id' => $bookingId,
            'rating' => (int) $rating,
            'text' => $text,
        ]);

        setFlash('Спасибо! Отзыв отправлен на модерацию.');
        redirect('/cabinet');
    }
}
