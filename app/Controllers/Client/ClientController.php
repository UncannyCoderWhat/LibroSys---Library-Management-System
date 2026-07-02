<?php
// app/Controllers/Client/ClientController.php
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../Models/Client/ClientModel.php';

abstract class ClientController extends ClientModel
{
    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    protected function isAuthenticated(array $session): bool
    {
        return !empty($session['user_logged_in']) && !empty($session['user_id']);
    }

    protected function requireAuthentication(array $session): ?array
    {
        if (!$this->isAuthenticated($session)) {
            return ['redirect' => 'index.php?page=login'];
        }

        return null;
    }

    protected function getCartCount(array $session): int
    {
        return isset($session['borrow_cart']) && is_array($session['borrow_cart'])
            ? count($session['borrow_cart'])
            : 0;
    }
}
