<?php

use Middleware\auth;

use Models\Cart;


class CartController
{
    public function viewCart()
    {
        $auth = new auth();


        if ($auth->check()) {
            $user_id = $_SESSION['user_id'];

            $allCart = new Cart();

            // Fetch all users cart
            $cart = $allCart->getUserCart($user_id);

            echo json_encode($cart, JSON_PRETTY_PRINT);
        } else {
            http_response_code(401);
        }
    }

    public function addItemToCart($product_id, $quantity)
    {
        $auth = new auth();


        if ($auth->check()) {
            $user_id = $_SESSION['user_id'];
            
            $product_id = $_GET['product_id'] ?? null;  // Replace with actual key from your request
            $quantity = $_GET['quantity'] ?? 1;   // Default quantity to 1 if not specified

            // Validate input
            if (is_null($product_id) || !is_numeric($quantity) || $quantity < 1) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid input'], JSON_PRETTY_PRINT);
                return;
            }

            $Cart = new Cart();

            // Add item to the cart
            if ($Cart->addItemCart($user_id, $product_id, $quantity)) {
                http_response_code(200);
                echo json_encode(['message' => 'Item added to cart successfully'], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to add item to cart'], JSON_PRETTY_PRINT);
            }
        } else {
            // If user is not authorized, return a 401 Unauthorized response
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized user'], JSON_PRETTY_PRINT);
        }
    }
}
