<?php
// app/Controllers/Admin/UsersController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';

class UsersController
{
    private PDO $pdo;
    private AdminModel $adminModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function getUsersPageData(array $post): array
    {
        $message = '';
        $message_type = '';

        // Handle user deletion (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($post['delete_user'])) {
            $targetId = (int)($post['target_user_id'] ?? 0);

            if ($targetId === 0) {
                $message = "Invalid user target.";
                $message_type = "error";
            } else {
                $canDelete = $this->adminModel->canDeleteUser($targetId);

                if (!$canDelete['allowed']) {
                    $message = $canDelete['message'];
                    $message_type = "error";
                } else {
                    if ($this->adminModel->deleteUserWithHistory($targetId)) {
                        $message = "User account and all related history have been permanently removed.";
                        $message_type = "success";
                    } else {
                        $message = "Database Error: Could not delete user.";
                        $message_type = "error";
                    }
                }
            }
        }

        // Fetch real users
        $users = $this->adminModel->getAllUsers();

        // Precompute fines for rendering + modal JS payload
        foreach ($users as &$user) {
            $userId = (int)($user['id'] ?? 0);
            if ($userId > 0) {
                $totalFines = $this->adminModel->getUserTotalFines($userId);
                $user['total_fines'] = $totalFines;
                $fineDetails = $this->adminModel->getUserFineDetails($userId);
                $user['fine_details'] = $fineDetails;
            } else {
                $user['total_fines'] = 0;
                $user['fine_details'] = [];
            }
        }
        unset($user);

        return [
            'users' => $users,
            'message' => $message,
            'message_type' => $message_type,
        ];
    }
}
