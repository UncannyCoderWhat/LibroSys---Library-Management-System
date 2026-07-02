<?php
// app/Controllers/Admin/LedgerController.php

require_once __DIR__ . '/../../Models/Admin/AdminModel.php';

class LedgerController
{
    private PDO $pdo;
    private AdminModel $adminModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->adminModel = new AdminModel($pdo);
    }

    public function getLedgerPageData(): array
    {
        $metrics = $this->adminModel->getDashboardMetrics();
        $activities = $this->adminModel->getRecentActivities();
        $reservations = $this->adminModel->getReservationActivities();

        $currentlyBorrowedCount = (int)($metrics['borrowedBooks'] ?? 0);

        // Calculate Total Fines Accumulated from activities
        $totalFinesAccumulated = 0;
        foreach ($activities as $activity) {
            $fineValue = (float)str_replace(['₱', ','], '', (string)($activity['total_fine'] ?? '0'));
            $totalFinesAccumulated += $fineValue;
        }

        return [
            'currentlyBorrowedCount' => $currentlyBorrowedCount,
            'activities' => $activities,
            'reservations' => $reservations,
            'totalFinesAccumulated' => $totalFinesAccumulated,
        ];
    }
}
