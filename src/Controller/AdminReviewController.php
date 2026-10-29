<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Review;

class AdminReviewController
{
    public function index(): void
    {
        requireAdmin();
        $reviews = Review::latest();
        View::renderAdmin('reviews', ['reviews' => $reviews]);
    }

    public function toggle(int $id): void
    {
        requireAdmin();

        if (!csrf_verify($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            redirect('/admin/reviews');
        }

        $review = Review::find($id);
        if ($review !== null) {
            Review::setPublished($id, (int) $review['is_published'] ? false : true);
        }

        redirect('/admin/reviews');
    }
}
