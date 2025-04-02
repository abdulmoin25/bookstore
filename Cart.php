<?php
require_once 'Book.php';

class Cart {
    private $items = [];

    public function __construct() {
        if (isset($_SESSION['cart'])) {
            $this->items = $_SESSION['cart'];
        }
    }

    // Add item to cart
    public function add($bookId, $quantity = 1) {
        $bookModel = new Book();
        $book = $bookModel->getById($bookId);

        if (!$book) {
            return false;
        }

        if (isset($this->items[$bookId])) {
            $this->items[$bookId]['quantity'] += $quantity;
        } else {
            $this->items[$bookId] = [
                'id' => $book['id'],
                'title' => $book['title'],
                'price' => $book['price'],
                'quantity' => $quantity,
                'image' => $book['image']
            ];
        }

        $this->save();
        return true;
    }

    // Update item quantity
    public function update($bookId, $quantity) {
        if (!isset($this->items[$bookId])) {
            return false;
        }

        if ($quantity <= 0) {
            $this->remove($bookId);
            return true;
        }

        $this->items[$bookId]['quantity'] = $quantity;
        $this->save();
        return true;
    }

    // Remove item from cart
    public function remove($bookId) {
        if (isset($this->items[$bookId])) {
            unset($this->items[$bookId]);
            $this->save();
        }
        return true;
    }

    // Get all items in cart
    public function getItems() {
        return $this->items;
    }

    // Get cart total
    public function getTotal() {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    // Get cart count
    public function getCount() {
        $count = 0;
        foreach ($this->items as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    // Clear cart
    public function clear() {
        $this->items = [];
        $this->save();
    }

    // Save cart to session
    private function save() {
        $_SESSION['cart'] = $this->items;
    }
}
?>