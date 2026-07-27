<?php
// app/Controllers/Client/AuthController.php
require_once __DIR__ . '/ClientController.php';

class AuthController extends ClientController
{
    public function handleLoginRequest(): array
    {
        $message = '';
        $message_type = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->handleLogin($_POST['user_id'] ?? '', $_POST['user_password'] ?? '');

            if ($result['success']) {
                $user = $result['user'];
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];

                header('Location: index.php?page=home');
                exit();
            }

            $message = $result['message'] ?? 'Invalid ID/Email or Password.';
            $message_type = 'error';
        }

        return [
            'message' => $message,
            'message_type' => $message_type,
        ];
    }

    public function handleSignupRequest(): array
    {
        $message = '';
        $message_type = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Support both first_name/last_name (new) and combined name (old) fields
            $firstName = $_POST['first_name'] ?? '';
            $lastName  = $_POST['last_name'] ?? '';

            // Fallback: if first_name is empty but 'name' is provided, try to split it
            if (empty($firstName) && !empty($_POST['name'])) {
                $nameParts = explode(' ', trim($_POST['name']), 2);
                $firstName = $nameParts[0] ?? '';
                $lastName  = $nameParts[1] ?? '';
            }

            $result = $this->handleSignup(
                $firstName,
                $lastName,
                $_POST['email'] ?? '',
                $_POST['password'] ?? '',
                $_POST['confirm_password'] ?? ''
            );

            if ($result['success']) {
                $userStmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
                $userStmt->execute([$result['insert_id']]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['temp_user_id_for_display'] = $result['user_id'];

                    header('Location: index.php?page=home');
                    exit();
                }
            }

            $message = $result['message'] ?? 'Unable to create account.';
            $message_type = $result['success'] ? 'success' : 'error';
        }

        return [
            'message' => $message,
            'message_type' => $message_type,
        ];
    }
}
