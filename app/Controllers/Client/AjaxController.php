<?php
// app/Controllers/Client/AjaxController.php
require_once __DIR__ . '/ClientController.php';
require_once __DIR__ . '/../../Models/Client/ClientModel.php';
require_once __DIR__ . '/BookDetailController.php';

class AjaxController extends ClientController
{
    private ClientModel $model;
    private BookDetailController $bookDetail;

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
        $this->model = new ClientModel($pdo);
        $this->bookDetail = new BookDetailController($pdo);
    }

    /**
     * Handle /ajax.php?action=borrow_handler requests.
     * Delegates to ClientModel::handleBorrowAction().
     */
    public function handleBorrowHandler(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $bookId = isset($post['book_id']) ? (int)$post['book_id'] : null;
        $action = $post['action'] ?? '';

        try {
            $result = $this->model->handleBorrowAction($userId, $bookId, $action, $session);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Handle /ajax.php?action=return_handler requests.
     * Delegates to ClientModel::handleReturnAction().
     */
    public function handleReturnHandler(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $borrowId = isset($post['borrow_id']) ? (int)$post['borrow_id'] : 0;

        if ($borrowId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid borrow ID.']);
            exit();
        }

        try {
            $result = $this->model->handleReturnAction($userId, $borrowId);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Handle /ajax.php?action=mark_read requests.
     * Delegates to ClientModel::markNotificationRead().
     */
    public function handleMarkRead(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $notifId = isset($post['notification_id']) ? (int)$post['notification_id'] : 0;

        try {
            $result = $this->model->markNotificationRead($userId, $notifId);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error']);
        }
        exit();
    }

    /**
     * Handle /ajax.php?action=read_now requests.
     * Adds a book to the user's Reading list (Wattpad-style).
     */
    public function handleReadNow(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $bookId = isset($post['book_id']) ? (int)$post['book_id'] : 0;

        if ($bookId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid book ID.']);
            exit();
        }

        try {
            $result = $this->bookDetail->handleReadNow($userId, $bookId);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    /**
     * Handle /ajax.php?action=bookmark requests.
     * Adds a book to the user's Bookmarked list (Wattpad-style).
     */
    public function handleBookmark(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $bookId = isset($post['book_id']) ? (int)$post['book_id'] : 0;

        if ($bookId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid book ID.']);
            exit();
        }

        try {
            $result = $this->bookDetail->handleBookmark($userId, $bookId);
            echo json_encode($result);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function handleSaveReadingProgress(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $bookId = isset($post['book_id']) ? (int)$post['book_id'] : 0;
        $pageNumber = isset($post['page_number']) ? (int)$post['page_number'] : 1;

        if ($bookId <= 0 || $pageNumber < 1) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters.']);
            exit();
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO reading_progress (user_id, book_id, page_number, updated_at)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE page_number = ?, updated_at = NOW()
            ");
            $stmt->execute([$userId, $bookId, $pageNumber, $pageNumber]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }

    public function handleGetReadingProgress(array &$session, array $post): void
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
            exit();
        }

        $userId = (int)$session['user_id'];
        $bookId = isset($post['book_id']) ? (int)$post['book_id'] : 0;

        if ($bookId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid book ID.']);
            exit();
        }

        try {
            $stmt = $this->pdo->prepare("SELECT page_number FROM reading_progress WHERE user_id = ? AND book_id = ?");
            $stmt->execute([$userId, $bookId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $pageNumber = $row ? (int)$row['page_number'] : 1;
            echo json_encode(['status' => 'success', 'page_number' => $pageNumber]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}
