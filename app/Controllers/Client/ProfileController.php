<?php
// app/Controllers/Client/ProfileController.php
require_once __DIR__ . '/ClientController.php';
require_once __DIR__ . '/../../Models/Client/ClientModel.php';

class ProfileController extends ClientController
{
    private ClientModel $model;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->model = new ClientModel($pdo);
    }

    public function handleRequest(array $session): array
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            return $authResult;
        }

        return $this->getProfilePageData($session);
    }

    /**
     * Build all data required by client/profile view.
     * @param array $session
     * @return array
     */
    public function getProfilePageData(array $session): array
    {
        $db_id = $session['user_id'] ?? null;
        if (!$db_id) {
            return ['redirect' => 'index.php?page=login'];
        }

        $user = $this->model->getUserById((int)$db_id);

        $displayname   = $user['name'] ?? '';
        $username      = $user['user_id'] ?? '';
        $member_id     = $user['user_id'] ?? '';
        $credit_score  = isset($user['credit_score']) ? (int)$user['credit_score'] : 0;
        $user_status   = ($credit_score > 5) ? "Exclusive" : "Regular";

        $metrics = $this->model->getProfileMetrics((int)$db_id);
        $totalBorrowed = $metrics['totalBorrowed'] ?? 0;
        $totalReturned = $metrics['totalReturned'] ?? 0;
        $totalPending = $metrics['totalPending'] ?? 0;

        $records = $this->model->getBorrowHistory((int)$db_id);
        $resRecords = $this->model->getReservations((int)$db_id);
        $notifications = $this->model->getNotifications((int)$db_id);

        $cartCount = $this->getCartCount($session);

        $totalFinesOwed = 0;
        $fineItems = [];

        $outstandingFinesRecords = $this->model->getOutstandingFines((int)$db_id);

        foreach ($outstandingFinesRecords as $row) {
            $fine = isset($row['fine_amount']) ? (float)$row['fine_amount'] : 0;

            if (($row['status'] ?? null) === 'borrowed' && !empty($row['due_date'])) {
                $now = time();
                $dueDate = strtotime($row['due_date']);

                if ($now > $dueDate) {
                    $daysLate = (int)ceil(($now - $dueDate) / (60 * 60 * 24));
                    if ($daysLate <= 3) $fine = $daysLate * 50;
                    elseif ($daysLate <= 10) $fine = $daysLate * 100;
                    else $fine = $daysLate * 150;
                } else {
                    $fine = 0;
                }
            }

            if ($fine > 0) {
                $fineItems[] = [
                    'title' => $row['title'] ?? '',
                    'amount' => $fine
                ];
            }

            $totalFinesOwed += $fine;
        }

        $credit_tooltip = ($credit_score <= 5)
            ? "Bad Standing: Your score is 5 or below, likely due to late returns. Exclusive perks are currently locked until your score improves."
            : "Good Standing: Your account is in great shape! You have full access to all library perks.";

        $browse_error = $session['browse_error'] ?? null;

        return [
            'redirect' => null,
            'displayname' => $displayname,
            'username' => $username,
            'member_id' => $member_id,
            'credit_score' => $credit_score,
            'user_status' => $user_status,
            'credit_tooltip' => $credit_tooltip,

            'totalBorrowed' => $totalBorrowed,
            'totalReturned' => $totalReturned,
            'totalPending' => $totalPending,

            'records' => $records,
            'resRecords' => $resRecords,
            'notifications' => $notifications,

            'cartCount' => $cartCount,
            'totalFinesOwed' => $totalFinesOwed,
            'fineItems' => $fineItems,

            'browse_error' => $browse_error,
        ];
    }
}
