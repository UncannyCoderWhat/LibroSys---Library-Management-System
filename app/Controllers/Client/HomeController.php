<?php
// app/Controllers/Client/HomeController.php
require_once __DIR__ . '/ClientController.php';

class HomeController extends ClientController
{
    public function getHomePageData(array $session): array
    {
        $generated_user_id = $session['temp_user_id_for_display'] ?? null;

        // Fetch real books from the database
        $books = $this->getHomePageBooks();

        return [
            'generated_user_id' => $generated_user_id,
            'cartCount' => $this->getCartCount($session),
            'exclusive_books' => $books['exclusive_books'] ?? [],
            'regular_books' => $books['regular_books'] ?? [],
            'new_releases' => $books['new_releases'] ?? [],
            'available_books' => $books['available_books'] ?? [],
            'borrowed_books' => $books['borrowed_books'] ?? [],
            'all_books' => $books['all_books'] ?? [],
            'genre_groups' => $books['genre_groups'] ?? [],
            'book_type_groups' => $books['book_type_groups'] ?? [],
        ];
    }
}
