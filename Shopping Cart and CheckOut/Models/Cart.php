<?php

namespace Models;

use Database\DB;
use PDO;

class Cart
{
    private $db;

    public function __construct()
    {
        $this->db = (new DB())->conn();
    }

    // Fetch all cart items for the specific logged-in user
    public function getUserCart($user_id)
    {
        // Prepare the SQL statement to select cart items based on the user's ID
        $stmt = $this->db->prepare('SELECT * FROM cart WHERE user_id = :user_id');

        // Bind the user ID parameter to avoid SQL injection
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);

        // Execute the query
        $stmt->execute();

        // Fetch the cart items for the logged-in user
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addItemCart($user_id, $product_id, $quantity)
    {

        //check the product id if it is already exist in cart
        $stmt = $this->db->prepare('SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id');

        //bind all the parameters user_id and product_id into the query statement
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        //execute the query
        $stmt->execute();

        // If the item exists, update the quantity
        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare('UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id');
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                return [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'added_at' => date('Y-m-d H:i:s') // Return the current timestamp
                ];
            }
        } else {
            // If the item doesn't exist, insert it into the cart
            $stmt = $this->db->prepare('INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'added_at' => date('Y-m-d H:i:s') // Return the current timestamp
                ];
            }
        }
    }

    public function updateItemQuantity($user_id, $product_id, $quantity)
    {
        //check the product id if it is already exist in cart
        $stmt = $this->db->prepare('SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id');

        //bind all the parameters user_id and product_id into the query statement
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        //execute the query
        $stmt->execute();

        // If the item exists, update the quantity
        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare('UPDATE cart SET quantity = :quantity WHERE user_id = :user_id AND product_id = :product_id');
            $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return [
                    'product_id' => $product_id,
                    'updated quantity' => $quantity,
                    'added_at' => date('Y-m-d H:i:s') // Return the current timestamp
                ];
            }
        }
    }

    public function deleteItemInCart($user_id, $product_id)
    {
        //check the product id if it is already exist in cart
        $stmt = $this->db->prepare('SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id');

        //bind all the parameters user_id and product_id into the query statement
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        //execute the query
        $stmt->execute();

        // If the item exists, delete the item
        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id');
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
            return $stmt->execute();
        }
    }

    public function removeAllItemInCart($user_id)
    {
        //check the product id if it is already exist in cart
        $stmt = $this->db->prepare('SELECT * FROM cart WHERE user_id = :user_id');

        //bind all the parameters user_id
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);

        //execute the query
        $stmt->execute();

        // If the item exists, remove all items
        if ($stmt->rowCount() > 0) {
            $stmt = $this->db->prepare('DELETE FROM cart WHERE user_id = :user_id');
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            return $stmt->execute();
        }
    }
}
