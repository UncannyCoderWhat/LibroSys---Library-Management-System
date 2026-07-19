<?php
// app/Controllers/Admin/ActivityLogController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';

class ActivityLogController
{
    private PDO $pdo;
    private AdminModel $adminModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function getActivityLogs(): array
    {
        return $this->adminModel->getActivityLogs(50);
    }
}
