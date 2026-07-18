<?php
session_start();
require_once 'config/db.php';
require_once 'app/Controllers/Client/HomeController.php';
require_once 'app/Controllers/Client/BrowseController.php';
require_once 'app/Controllers/Client/CartController.php';
require_once 'app/Controllers/Client/ProfileController.php';
require_once 'app/Controllers/Client/SettingsController.php';
require_once 'app/Controllers/Client/LogoutController.php';
require_once 'app/Controllers/Client/AjaxController.php';
require_once 'app/Controllers/Client/AuthController.php';

// Admin Controllers
require_once 'app/Controllers/Admin/AuthController.php';
require_once 'app/Controllers/Admin/DashboardController.php';
require_once 'app/Controllers/Admin/BooksController.php';
require_once 'app/Controllers/Admin/BorrowedController.php';
require_once 'app/Controllers/Admin/LedgerController.php';
require_once 'app/Controllers/Admin/UsersController.php';
require_once 'app/Controllers/Admin/SettingsController.php';

$base_url = '/LibroSys';

$page = $_GET['page'] ?? 'home';

// Admin routing through index.php (page=admin_{resource})
if (strpos($page, 'admin_') === 0) {
    $adminPage = substr($page, 6);

    // Public admin pages
    if ($adminPage === 'login') {
        $view = 'app/Views/admin/login.php';
        include $view;
        exit();
    }
    if ($adminPage === 'signup') {
        $view = 'app/Views/admin/signup.php';
        include $view;
        exit();
    }
    if ($adminPage === 'authenticate') {
        $controller = new AdminAuthController($pdo);
        $controller->handleLogin($_POST);
        exit();
    }
    if ($adminPage === 'register') {
        $controller = new AdminAuthController($pdo);
        $controller->handleSignup($_POST);
        exit();
    }

    // Protected admin pages - require authentication
    if (!isset($_SESSION['admin_logged_in'])) {
        header('Location: index.php?page=admin_login');
        exit();
    }

    switch ($adminPage) {
        case 'logout':
            $controller = new AdminAuthController($pdo);
            $controller->handleLogout();
            exit();

        case 'dashboard':
            $controller = new DashboardController($pdo);
            $metrics = $controller->getDashboardMetrics();
            $activities = $controller->getRecentActivities(10);
            $totalBooks = $metrics['totalBooks'];
            $availableBooks = $metrics['availableBooks'];
            $borrowedBooks = $metrics['borrowedBooks'];
            $exclusiveBooks = $metrics['exclusiveBooks'];
            $status = $controller->getUsersWithStatus();

            $controller = new LedgerController($pdo);
            $data = $controller->getLedgerPageData();
            $currentlyBorrowedCount = $data['currentlyBorrowedCount'] ?? 0;
            $totalFinesAccumulated = $data['totalFinesAccumulated'] ?? 0;
            $reservations = $data['reservations'] ?? [];
            
            $view = 'app/Views/admin/dashboard.php';
            break;

        case 'books':
            $controller = new BooksController($pdo);
            $controller->handleActions($_GET, $_POST, $_FILES);
            $all_books = $controller->getAllBooks();
            $all_categories = $controller->getAllCategories();
            $all_authors = $controller->getAllAuthors();
            $all_publishers = $controller->getAllPublishers();
            $view = 'app/Views/admin/books.php';
            break;

        case 'users':
            $controller = new UsersController($pdo);
            $data = $controller->getUsersPageData($_POST);
            $users = $data['users'] ?? [];
            $message = $data['message'] ?? '';
            $message_type = $data['message_type'] ?? '';
            $controller = new DashboardController($pdo);
            $activities = $controller->getRecentActivities(10);
            $view = 'app/Views/admin/users.php';
            break;

        case 'settings':
            $controller = new AdminSettingsController($pdo);
            $data = $controller->getSettingsPageData($_SESSION, $_POST, $_FILES, $_GET);
            $message = $data['message'] ?? '';
            $message_type = $data['message_type'] ?? '';
            $admin_session_user = $data['admin_session_user'] ?? '';
            $admin = $data['admin'] ?? null;
            $view = 'app/Views/admin/settings.php';
            break;

        default:
            header('Location: index.php?page=admin_dashboard');
            exit();
    }

    if (isset($view)) {
        include $view;
    }
    exit();
}

switch ($page) {
    case 'browse':
        $controller = new BrowseController($pdo);
        $result = $controller->handleRequest($_SESSION);
        if (!empty($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit();
        }
        $data = $result;
        $view = 'app/Views/client/browse.php';
        break;

    case 'cart':
        $controller = new CartController($pdo);
        $result = $controller->handleRequest($_SESSION);
        if (!empty($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit();
        }
        $data = $result;
        $view = 'app/Views/client/cart.php';
        break;

    case 'profile':
        $controller = new ProfileController($pdo);
        $result = $controller->handleRequest($_SESSION);
        if (!empty($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit();
        }
        $data = $result;
        $view = 'app/Views/client/profile.php';
        break;

    case 'settings':
        $controller = new SettingsController($pdo);
        $result = $controller->handleRequest($_SESSION, $_POST, $_SERVER['REQUEST_METHOD']);
        if (!empty($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit();
        }
        $data = $result;
        $view = 'app/Views/client/settings.php';
        break;

    case 'ajax':
        $controller = new AjaxController($pdo);
        $ajaxAction = $_GET['action'] ?? '';
        switch ($ajaxAction) {
            case 'borrow_handler':
                $controller->handleBorrowHandler($_SESSION, $_POST);
                break;
            case 'return_handler':
                $controller->handleReturnHandler($_SESSION, $_POST);
                break;
            case 'mark_read':
                $controller->handleMarkRead($_SESSION, $_POST);
                break;
            default:
                echo json_encode(['status' => 'error', 'message' => 'Unknown AJAX action.']);
                exit();
        }
        exit();

    case 'login':
        $controller = new AuthController($pdo);
        $data = $controller->handleLoginRequest();
        $view = 'app/Views/client/login.php';
        break;

    case 'signup':
        $controller = new AuthController($pdo);
        $data = $controller->handleSignupRequest();
        $view = 'app/Views/client/signup.php';
        break;

    case 'logout':
        $controller = new LogoutController();
        $controller->logoutAndRedirect();
        exit();

    case 'home':
    default:
        $controller = new HomeController($pdo);
        $data = $controller->getHomePageData($_SESSION);
        $view = 'app/Views/client/home.php';
        break;
}

include $view;
