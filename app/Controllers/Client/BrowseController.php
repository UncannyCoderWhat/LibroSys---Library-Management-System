<?php
require_once __DIR__ . '/ClientController.php';
require_once __DIR__ . '/../../Models/Client/ClientModel.php';

class BrowseController extends ClientController
{
    private ClientModel $model;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->model = new ClientModel($pdo);
    }

    public function handleRequest(array &$session): array
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            return $authResult;
        }

        $data = $this->buildBrowsePageData($session);

        if (!empty($data['totalFines']) && (float)$data['totalFines'] > 0) {
            $session['browse_error'] =
                "Access Denied: You have outstanding fines of ₱" . number_format((float)$data['totalFines'], 2) .
                ". Please settle your dues in your profile before browsing the collection.";

            return ['redirect' => 'index.php?page=profile'];
        }

        return $data;
    }

    public function buildBrowsePageData(array $session): array
    {
        $user_id = $session['user_id'] ?? null;
        if (!$user_id) {
            return [
                'current_score' => 0,
                'totalFines' => 0,
                'exclusive_books' => [],
                'regular_books' => [],
                'borrowed_books' => [],
                'cartCount' => 0,
                'error' => 'Not authenticated.'
            ];
        }

        $browseData = $this->model->getBrowsePageData((int)$user_id);
        $cartCount = $this->getCartCount($session);

        return [
            'current_score' => $browseData['current_score'] ?? 0,
            'totalFines' => $browseData['totalFines'] ?? 0,
            'exclusive_books' => $browseData['exclusive_books'] ?? [],
            'regular_books' => $browseData['regular_books'] ?? [],
            'borrowed_books' => $browseData['borrowed_books'] ?? [],
            'cartCount' => $cartCount,
            'error' => null
        ];
    }
}
