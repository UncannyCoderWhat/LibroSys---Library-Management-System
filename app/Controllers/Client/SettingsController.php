<?php
// app/Controllers/Client/SettingsController.php
require_once __DIR__ . '/ClientController.php';
require_once __DIR__ . '/../../Models/Client/ClientModel.php';

class SettingsController extends ClientController
{
    private ClientModel $model;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->model = new ClientModel($pdo);
    }

    public function handleRequest(array &$session, array $post = [], string $requestMethod = 'GET'): array
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            return $authResult;
        }

        if ($requestMethod === 'POST') {
            $postResult = $this->handleSettingsPost($session, $post, $requestMethod);
            if (!empty($postResult['redirect'])) {
                return $postResult;
            }
        }

        return $this->getSettingsPageData($session);
    }

    public function getSettingsPageData(array $session): array
    {
        $db_id = $session['user_id'] ?? null;
        if (!$db_id) {
            return ['redirect' => 'index.php?page=login'];
        }

        $user = $this->model->getUserById((int)$db_id);

        if (!$user) {
            return ['redirect' => 'index.php?page=login'];
        }

        $current_name = $user['name'];
        $current_email = $user['email'];

        return [
            'redirect' => null,
            'cartCount' => $this->getCartCount($session),
            'current_name' => $current_name,
            'current_email' => $current_email,
            'message' => $session['settings_message'] ?? '',
            'message_type' => $session['settings_message_type'] ?? '',
        ];
    }

    public function handleSettingsPost(array &$session, array $post, string $requestMethod = 'GET'): array
    {
        $db_id = $session['user_id'] ?? null;
        if (!$db_id) {
            return ['redirect' => 'index.php?page=login'];
        }

        $user = $this->model->getUserById((int)$db_id);

        $userPasswordHash = $user['password'] ?? '';

        $message = '';
        $message_type = '';

        if ($requestMethod === 'POST' && isset($post['update_account'])) {
            $new_name = trim($post['name'] ?? '');
            $new_email = trim($post['email'] ?? '');

            if (empty($new_name) || empty($new_email)) {
                $message = 'Name and Email cannot be empty.';
                $message_type = 'error';
            } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Invalid email format.';
                $message_type = 'error';
            } else {
                try {
                    $checkEmailStmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
                    $checkEmailStmt->execute([$new_email, $db_id]);

                    if ((int)$checkEmailStmt->fetchColumn() > 0) {
                        $message = 'This email is already registered to another account.';
                        $message_type = 'error';
                    } else {
                        $this->model->updateAccountDetails((int)$db_id, $new_name, $new_email);

                        $session['user_name'] = $new_name;

                        $message = 'Account details updated successfully!';
                        $message_type = 'success';
                    }
                } catch (PDOException $e) {
                    error_log("Error updating user settings: " . $e->getMessage());
                    $message = 'An error occurred while updating your details. Please try again.';
                    $message_type = 'error';
                }
            }
        }

        if ($requestMethod === 'POST' && isset($post['update_password'])) {
            $current_pass = $post['current_password'] ?? '';
            $new_pass = $post['new_password'] ?? '';
            $confirm_pass = $post['confirm_password'] ?? '';

            if (empty($current_pass) || empty($new_pass) || empty($confirm_pass)) {
                $message = 'All password fields are required.';
                $message_type = 'error';
            } elseif (!password_verify($current_pass, $userPasswordHash)) {
                $message = 'Current password is incorrect.';
                $message_type = 'error';
            } elseif ($new_pass !== $confirm_pass) {
                $message = 'New passwords do not match.';
                $message_type = 'error';
            } elseif (strlen($new_pass) < 6) {
                $message = 'New password must be at least 6 characters long.';
                $message_type = 'error';
            } else {
                try {
                    $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                    $this->model->updatePassword((int)$db_id, $hashed_pass);

                    $message = 'Password updated successfully!';
                    $message_type = 'success';
                } catch (PDOException $e) {
                    error_log("Error updating password: " . $e->getMessage());
                    $message = 'An error occurred. Please try again.';
                    $message_type = 'error';
                }
            }
        }

        if ($requestMethod === 'POST' && isset($post['delete_account'])) {
            try {
                $checkBorrowStmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'borrowed'");
                $checkBorrowStmt->execute([$db_id]);
                $activeLoans = (int)$checkBorrowStmt->fetchColumn();

                $checkFineStmt = $this->pdo->prepare("SELECT status, due_date, fine_amount FROM borrows WHERE user_id = ? AND is_fine_paid = FALSE");
                $checkFineStmt->execute([$db_id]);
                $fines = $checkFineStmt->fetchAll();

                $unpaidFines = 0;
                foreach ($fines as $f) {
                    $amt = $f['fine_amount'] ?? 0;

                    if (($f['status'] ?? null) === 'borrowed' && !empty($f['due_date'])) {
                        $now = time();
                        $dueDate = strtotime($f['due_date']);
                        if ($now > $dueDate) {
                            $daysLate = (int)ceil(($now - $dueDate) / (60 * 60 * 24));
                            if ($daysLate <= 3) $amt = $daysLate * 50;
                            elseif ($daysLate <= 10) $amt = $daysLate * 100;
                            else $amt = $daysLate * 150;
                        } else {
                            $amt = 0;
                        }
                    }

                    $unpaidFines += $amt;
                }

                if ($activeLoans > 0 || $unpaidFines > 0) {
                    $message = "Account deletion blocked: ";
                    $message .= ($activeLoans > 0) ? $activeLoans . " active book loan(s) " : '';
                    if ($unpaidFines > 0) {
                        if ($activeLoans > 0) $message .= "and ";
                        $message .= "₱" . number_format($unpaidFines, 2) . " in outstanding fines. ";
                    }
                    $message .= "Please return all books and settle fines before closing your account.";
                    $message_type = 'error';
                } else {
                    $this->model->deleteAccount((int)$db_id);

                    $session = [];
                    session_destroy();
                    return ['redirect' => 'index.php?page=login'];
                }
            } catch (PDOException $e) {
                error_log("Error deleting account: " . $e->getMessage());
                $message = 'An error occurred while deleting your account.';
                $message_type = 'error';
            }
        }

        $session['settings_message'] = $message;
        $session['settings_message_type'] = $message_type;

        return [
            'redirect' => null,
            'message' => $message,
            'message_type' => $message_type,
        ];
    }
}

