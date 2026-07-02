<?php
// app/Controllers/Client/HomeController.php
require_once __DIR__ . '/ClientController.php';

class HomeController extends ClientController
{
    public function getHomePageData(array $session): array
    {
        $generated_user_id = $session['temp_user_id_for_display'] ?? null;

        return [
            'generated_user_id' => $generated_user_id,
            'cartCount' => $this->getCartCount($session),
        ];
    }
}
