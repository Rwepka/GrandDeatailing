<?php
/**
 * GRAND DETAILING — фронт-контроллер (единая точка входа).
 *
 * Все запросы к сайту проходят через этот файл (см. public/.htaccess).
 */
declare(strict_types=1);

// Корень проекта — на один уровень выше public/.
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/src/bootstrap.php';

use App\Core\Router;
use App\Controller\HomeController;
use App\Controller\ServiceController;
use App\Controller\BookingController;
use App\Controller\AuthController;
use App\Controller\CabinetController;
use App\Controller\AdminController;
use App\Controller\AdminServiceController;
use App\Controller\AdminReviewController;
use App\Controller\AdminMessageController;
use App\Controller\AdminMasterController;

$router = new Router();

// --- Публичные маршруты ---
$router->get('/', [HomeController::class, 'index']);
$router->get('/services', [ServiceController::class, 'index']);
$router->get('/services/{slug}', [ServiceController::class, 'show']);
$router->get('/booking', [BookingController::class, 'show']);
$router->post('/booking', [BookingController::class, 'store']);
$router->get('/booking/success', [BookingController::class, 'success']);

// --- Авторизация ---
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// --- Личный кабинет (требует авторизации) ---
$router->get('/cabinet', [CabinetController::class, 'index']);
$router->get('/cabinet/cars', [CabinetController::class, 'cars']);
$router->post('/cabinet/cars', [CabinetController::class, 'storeCar']);
$router->get('/cabinet/profile', [CabinetController::class, 'profile']);
$router->get('/cabinet/security', [CabinetController::class, 'security']);
$router->post('/cabinet/security/phone', [CabinetController::class, 'updatePhone']);
$router->post('/cabinet/security/email', [CabinetController::class, 'updateEmail']);
$router->post('/cabinet/security/password', [CabinetController::class, 'updatePassword']);

// --- Админ-панель (только для администратора) ---
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/admin/bookings', [AdminController::class, 'bookings']);
$router->post('/admin/bookings/{id}/status', [AdminController::class, 'updateStatus']);
$router->post('/admin/bookings/{id}/create-client', [AdminController::class, 'createClient']);
$router->get('/admin/services', [AdminServiceController::class, 'index']);
$router->get('/admin/services/create', [AdminServiceController::class, 'create']);
$router->post('/admin/services', [AdminServiceController::class, 'store']);
$router->get('/admin/services/{id}/edit', [AdminServiceController::class, 'edit']);
$router->post('/admin/services/{id}', [AdminServiceController::class, 'update']);
$router->post('/admin/services/{id}/toggle', [AdminServiceController::class, 'toggle']);
$router->get('/admin/reviews', [AdminReviewController::class, 'index']);
$router->post('/admin/reviews/{id}/toggle', [AdminReviewController::class, 'toggle']);
$router->get('/admin/messages', [AdminMessageController::class, 'index']);
$router->post('/admin/messages/{id}/read', [AdminMessageController::class, 'markRead']);
$router->get('/admin/masters', [AdminMasterController::class, 'index']);
$router->get('/admin/masters/create', [AdminMasterController::class, 'create']);
$router->post('/admin/masters', [AdminMasterController::class, 'store']);

// По мере разработки добавляются:
//   $router->get('/login', [AuthController::class, 'showLogin']);
//   $router->post('/login', [AuthController::class, 'login']);
//   $router->post('/logout', [AuthController::class, 'logout']);
//   $router->get('/cabinet', [CabinetController::class, 'index']);
//   $router->get('/admin', [AdminController::class, 'index']);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/'
);
