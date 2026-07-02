<?php
// app/Controllers/Client/CartController.php
require_once __DIR__ . '/ClientController.php';
require_once __DIR__ . '/../../Models/Client/ClientModel.php';

class CartController extends ClientController
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

        return $this->getCartPageData($session);
    }

    /**
     * Returns data required by the Cart page view.
     * @param array $session
     * @return array{cart_items: array, cartCount: int, error: string|null}
     */
    public function getCartPageData(array $session): array
    {
        $cart_items = [];

        if (!empty($session['borrow_cart']) && is_array($session['borrow_cart'])) {
            $cartIds = array_values(array_filter($session['borrow_cart'], fn($v) => is_numeric($v)));
            $cart_items = $this->model->getCartPageData($cartIds);
        }

        return [
            'cart_items' => $cart_items,
            'cartCount'  => count($cart_items),
            'error'       => null,
        ];
    }
}
