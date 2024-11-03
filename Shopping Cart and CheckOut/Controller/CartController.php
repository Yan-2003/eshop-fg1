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

    public function addItemToCart()
    {
        $auth = new auth();


        if ($auth->check()) {
            $user_id = $_SESSION['user_id'];

            // Retrieve JSON payload
            $data = json_decode(file_get_contents("php://input"), true);
            $product_id = $data['product_id'] ?? null;
            $quantity = $data['quantity'] ?? 1;

            // Validate input
            if (is_null($product_id) || !is_numeric($quantity) || $quantity < 1) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid input'], JSON_PRETTY_PRINT);
                return;
            }

            $Cart = new Cart();

            // Add item to the cart
            if ($itemsCart = $Cart->addItemCart($user_id, $product_id, $quantity)) {
                http_response_code(200);
                echo json_encode(['message' => 'Item added to cart successfully', 'item added' => $itemsCart], JSON_PRETTY_PRINT);
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

    //update item quantity
    public function updateItemQuantity($product_id)
    {
        $auth = new auth();

        if ($auth->check()) {
            $user_id = $_SESSION['user_id'];

            // Retrieve JSON payload
            $data = json_decode(file_get_contents("php://input"), true);
            $quantity = $data['quantity'] ?? null;

            //validate inputs
            if (!is_numeric($product_id) || is_null($quantity) || !is_numeric($quantity) || $quantity < 1) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid input'], JSON_PRETTY_PRINT);
                return;
            }

            $Cart = new Cart();

            // update the item queantity
            if ($itemsCart = $Cart->updateItemQuantity($user_id, $product_id, $quantity)) {
                http_response_code(200);
                echo json_encode(['message' => 'Quantity updated successfully', 'item' => $itemsCart], JSON_PRETTY_PRINT);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Product id = ' . $product_id . ' not exists'], JSON_PRETTY_PRINT);
            }
        } else {
            // If user is not authorized, return a 401 Unauthorized response
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized user'], JSON_PRETTY_PRINT);
        }
    }

    //delete item in cart
    public function deleteItemInCart($product_id)
    {
        $auth = new auth();

        if ($auth->check()) {
            $user_id = $_SESSION['user_id'];

            // Validate input
            if (!is_numeric($product_id)) {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid ID'], JSON_PRETTY_PRINT);
                return;
            }

            $Cart = new Cart();

            if ($Cart->deleteItemInCart($user_id, $product_id)) {
                http_response_code(200);
                echo json_encode(['message' => 'Item deleted successfully in cart'], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to delete the Item in Cart'], JSON_PRETTY_PRINT);
            }
        } else {
            // If user is not authorized, return a 401 Unauthorized response
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized user'], JSON_PRETTY_PRINT);
        }
    }

    //remove all item in cart
    public function removeAllItemInCart()
    {
        $auth = new auth();

        if ($auth->check()) {
            $user_id = $_SESSION['user_id'];

            $Cart = new Cart();

            if ($Cart->removeAllItemInCart($user_id)) {
                http_response_code(200);
                echo json_encode(['message' => 'Successfully removed all the Items in Cart'], JSON_PRETTY_PRINT);
            } else {
                http_response_code(500);
                echo json_encode(['message' => 'Failed to remove all the Items in Cart'], JSON_PRETTY_PRINT);
            }
        } else {
            // If user is not authorized, return a 401 Unauthorized response
            http_response_code(401);
            echo json_encode(['message' => 'Unauthorized user'], JSON_PRETTY_PRINT);
        }
    }
}
