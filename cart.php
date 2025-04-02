<?php
require_once '../includes/config.php';
require_once '../includes/Cart.php';

header("Content-Type: application/json");

$cart = new Cart();
$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'add':
                if (isset($input['bookId']) && isset($input['quantity'])) {
                    $success = $cart->add($input['bookId'], $input['quantity']);
                    $response = [
                        'success' => $success,
                        'count' => $cart->getCount(),
                        'total' => $cart->getTotal()
                    ];
                }
                break;
                
            case 'update':
                if (isset($input['bookId']) && isset($input['quantity'])) {
                    $success = $cart->update($input['bookId'], $input['quantity']);
                    $response = [
                        'success' => $success,
                        'count' => $cart->getCount(),
                        'total' => $cart->getTotal()
                    ];
                }
                break;
                
            case 'remove':
                if (isset($input['bookId'])) {
                    $success = $cart->remove($input['bookId']);
                    $response = [
                        'success' => $success,
                        'count' => $cart->getCount(),
                        'total' => $cart->getTotal()
                    ];
                }
                break;
                
            case 'get':
                $response = [
                    'success' => true,
                    'items' => $cart->getItems(),
                    'count' => $cart->getCount(),
                    'total' => $cart->getTotal()
                ];
                break;
        }
    }
}

echo json_encode($response);
?>