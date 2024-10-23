<?php
namespace Models;
use Database\DB;
use Middleware\auth;
use PDO;

class Checkout{

    private $db;

    public function __construct()
    {   
        $this->db = (new DB())->conn();


    }

    public function initiate($cart_id){

        $auth = new auth();

        if($auth->check()){

            $user_id =  $_SESSION['user_id'];

            $stmt = $this->db->prepare('INSERT INTO orders(order_id, user_id, order_date, cart_id, address_id, total_amount, order_status) VALUES(uuid(), :user_id , curdate(), :cart_id , (SELECT address_id FROM shipping_address WHERE user_id = :user_id ) ,1.1, "Pending")');

            $stmt->bindParam(':user_id', $user_id , PDO::PARAM_STR_CHAR);
            $stmt->bindParam(':cart_id', $cart_id, PDO::PARAM_STR_CHAR);

            if($stmt->execute()){
                echo json_encode(["message" => "added successfull"], JSON_PRETTY_PRINT);
                return;
            }
            return http_response_code(500);

            return ;
        }else{
            http_response_code(401);
            return;
        }
        
        

    }

}
