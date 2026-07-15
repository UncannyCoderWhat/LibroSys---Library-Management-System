<?php
// app/Controllers/Admin/DashboardController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';

class DashboardController
{
    private PDO $pdo;
    private AdminModel $adminModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function getDashboardMetrics(): array
    {
        return $this->adminModel->getDashboardMetrics();
    }

    public function getRecentActivities(?int $limit = null): array
    {
        return $this->adminModel->getRecentActivities($limit);
    }

    public function getReservationActivities(): array
    {
        return $this->adminModel->getReservationActivities();
    }

    public function getActiveBorrows(): array
    {
        return $this->adminModel->getActiveBorrows();
    }

    public function getUserTotalFines(int $userId): float
    {
        return $this->adminModel->getUserTotalFines($userId);
    }

    public function getUserFineDetails(int $userId): array
    {
        return $this->adminModel->getUserFineDetails($userId);
    }

    public function getUsersWithStatus(): array
    {
        return $this->adminModel->getUsersWithStatus();
    }
}
