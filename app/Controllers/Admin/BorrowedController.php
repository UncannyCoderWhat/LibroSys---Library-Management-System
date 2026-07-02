<?php
// app/Controllers/Admin/BorrowedController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';

class BorrowedController
{
    private PDO $pdo;
    private AdminModel $adminModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function getBorrowedPageData(): array
    {
        $activeBorrows = $this->adminModel->getActiveBorrows();
        return [
            'activeBorrows' => $activeBorrows,
        ];
    }
}
