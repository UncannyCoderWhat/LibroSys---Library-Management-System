<?php
// app/Controllers/Admin/AuthController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';

class AdminAuthController
{
    private PDO $pdo;
    private AdminModel $adminModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function handleLogin(array $post): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_login');
            exit();
        }

        $admin_id = $post['admin_id'] ?? '';
        $password = $post['password'] ?? '';

        $admin = $this->adminModel->authenticateAdmin($admin_id, $password);

        if ($admin) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $admin['admin_id'];
            $this->adminModel->logActivity($admin['admin_id'], 'Admin login', 'Admin logged in successfully');
            header('Location: index.php?page=admin_dashboard');
            exit();
        }

        echo "<script>
                alert('Invalid Admin ID or Password. Please try again.');
                window.location.href = 'index.php?page=admin_login';
              </script>";
        exit();
    }

    public function handleSignup(array $post): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=admin_signup');
            exit();
        }

        $admin_id = $post['admin_id'] ?? '';
        $password = $post['password'] ?? '';
        $confirm_password = $post['confirm_password'] ?? '';

        $result = $this->adminModel->registerAdmin($admin_id, $password, $confirm_password);

        if ($result['success']) {
            echo "<script>
                    alert('" . addslashes($result['message']) . "');
                    window.location.href = 'index.php?page=admin_login';
                  </script>";
            exit();
        }

        echo "<script>
                alert('" . addslashes($result['message']) . "');
                window.location.href = 'index.php?page=admin_signup';
              </script>";
        exit();
    }

    public function handleLogout(): void
    {
        $adminId = $_SESSION['admin_user'] ?? 'Unknown';
        $_SESSION = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        session_destroy();
        $this->adminModel->logActivity($adminId, 'Admin logout', 'Admin logged out');
        header("Location: index.php?page=admin_login");
        exit();
    }
}
