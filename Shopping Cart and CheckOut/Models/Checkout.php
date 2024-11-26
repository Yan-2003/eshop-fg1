<?php
namespace Models;
use Database\DB;
use Middleware\auth;
use PDO;
use Ramsey\Uuid\Uuid;

class Checkout{

    private $db;
    public $address_id;

    public function __construct()
    {   
        $this->db = (new DB())->conn();


    }

    public function initiate($checkout){

        $auth = new auth();

        if($auth->check()) { 
            $user_id = $_SESSION['user_id'];
            $order_id = Uuid::uuid1();

            $total_amount = 0;
            $items = [];
            foreach($checkout as $item){
                $stm = $this->db->prepare('SELECT * FROM cart, products, shipping_address WHERE cart.is_selected_for_checkout = 0 AND shipping_address.user_id = :user_id AND cart.product_id = products.product_id AND cart.product_id = :product_id AND cart.user_id = :user_id');
                $stm->bindParam(":product_id", $item['product_id'], PDO::PARAM_STR_CHAR);
                $stm->bindParam(":user_id", $user_id, PDO::PARAM_STR_CHAR);
                $stm->execute();
                $result = $stm->fetchAll(PDO::FETCH_ASSOC);

                if(count($result) != 0){
                    $set_product_to_seleced = $this->db->prepare('UPDATE cart SET is_selected_for_checkout=1 WHERE cart_id = :cart_id');
                    $set_product_to_seleced->bindParam(':cart_id', $result[0]['cart_id'], PDO::PARAM_STR_CHAR);
                    $set_product_to_seleced->execute();

                    $total_amount += $result[0]['price'] * $result[0]['quantity']; 
                    $this->address_id = $result[0]['address_id']; 
                    $item = array(
                        "product_id" => $result[0]['product_id'],
                        "product_name" => $result[0]['product_name'],
                        "quantity" => $result[0]['quantity']
                    );

                    $items[] = $item;

                }else {
                    echo json_encode(["message" => "No ID product Found in Cart"], JSON_PRETTY_PRINT);
                    return;
                }

                
            }

            $INSERT_ORDER = $this->db->prepare('INSERT INTO orders(order_id, user_id, address_id , total_amount) VALUES(:order_id, :user_id, :address_id, :total_amount )');
            $INSERT_ORDER->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
            $INSERT_ORDER->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            $INSERT_ORDER->bindParam(':address_id', $this->address_id, PDO::PARAM_STR_CHAR);
            $INSERT_ORDER->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);

            if($INSERT_ORDER->execute()){

                $order = $this->db->prepare('SELECT * FROM orders, shipping_address WHERE shipping_address.user_id = :user_id AND orders.order_id = :order_id AND orders.user_id = :user_id ');
                $order->bindParam(':order_id', $order_id , PDO::PARAM_STR_CHAR);
                $order->bindParam(':user_id', $user_id , PDO::PARAM_STR_CHAR);
                
                if($order->execute()){

                    $getOrder = $order->fetchAll(PDO::FETCH_ASSOC);


                    foreach($items as $item){

                        $insert_to_checkout = $this->db->prepare('INSERT INTO checkout(order_id, product_id, quantity) VALUES(:order_id, :product_id, :quantity)');
                        $insert_to_checkout->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
                        $insert_to_checkout->bindParam(':product_id', $item['product_id'], PDO::PARAM_STR_CHAR);
                        $insert_to_checkout->bindParam(':quantity', $item['quantity']);
                        if($insert_to_checkout->execute()){
                            $order_summary = array(
                                "order_summary" => [
                                    "order_details" => $getOrder,
                                    "items" => $items,
                                ],
                                "payment_method" => [
                                    "PayPal",
                                    "Credit Card"
                                ]
                            );  
                        }else{
                            http_response_code(500);
                            return;
                        }

                    }
                    echo json_encode($order_summary, JSON_PRETTY_PRINT);
                }
            }
            

        }else{
            http_response_code(401);
        }

    }


    public function payment($order_id , $payment_method , $payment_details){

        $auth = new auth();

        if($auth->check()) {      
            
            $user_id = $_SESSION['user_id'];

            $check_order_id = $this->db->prepare('SELECT * FROM orders WHERE user_id = :user_id AND order_id = :order_id');
            $check_order_id->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            $check_order_id->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
            if($check_order_id->execute()){
                $check_order = $check_order_id->fetchAll(PDO::FETCH_ASSOC);
                if(count($check_order) == 0){
                    echo json_encode(["message" => "No order found"], JSON_PRETTY_PRINT);
                    return;
                }
            }

            
           
            $encryption_key = "F39YrhaEQ_DIm5gm8Slfnd2TjGDGweG_"; 
            $encryption_iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));

            $serialized_data = json_encode($payment_details);
            $encrypted_data = openssl_encrypt(
                $serialized_data,
                'aes-256-cbc',
                $encryption_key,
                0,
                $encryption_iv
            );

            $encoded_encrypted_data = base64_encode($encrypted_data);
    
            $stm = $this->db->prepare("INSERT INTO payment(order_id ,payment_method, payment_status, encrypted_payment_details) VALUES(:order_id, :payment_method, 'Pending', :encoded_encrypted_data)");
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
            $stm->bindParam(':payment_method', $payment_method , PDO::PARAM_STR_CHAR);
            $stm->bindParam(':encoded_encrypted_data', $encoded_encrypted_data, PDO::PARAM_STR);

            if($stm->execute()){
                $result = array(
                    "Message" => "Payment Added Successfully"
                );

                echo json_encode($result, JSON_PRETTY_PRINT);
                return;
            }else{
                http_response_code(403);
                return;
            }

        }else{
            http_response_code(401);
        }

    }

    public function reviewOrder($order_id){

        $auth = new auth();

        if($auth->check()) {  
            $user_id = $_SESSION['user_id'];

            $getOrderDetails = $this->db->prepare('SELECT * FROM orders as o, payment as pt, shipping_address as sa WHERE pt.order_id = :order_id AND sa.user_id = :user_id AND o.user_id = :user_id AND o.order_id = :order_id');
            $getOrderDetails->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
            $getOrderDetails->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            if($getOrderDetails->execute()){
                $result = $getOrderDetails->fetchAll(PDO::FETCH_ASSOC);
                if(count($result) !=0){

                    $getItems = $this->db->prepare('SELECT p.product_name, p.price, c.quantity FROM products as p , checkout as c, orders as o WHERE c.order_id = :order_id AND c.product_id = p.product_id AND o.order_id = c.order_id AND o.user_id = :user_id');
                    $getItems->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                    $getItems->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
                    if($getItems->execute()){
                        $item = $getItems->fetchAll(PDO::FETCH_ASSOC);
                        
                        $review = array(
                            "order_details" => $result,
                            "items" => $item,
                        );       


                        echo json_encode($review , JSON_PRETTY_PRINT);

                    }



                }else{
                    echo json_encode(["message" => "No data found."], JSON_PRETTY_PRINT);
                    return ;
                }
            }



        }else{
            http_response_code(401);
        }

    }

 public function confirmOrder($order_id){

    $auth = new auth();

    if($auth->check()) {  
        $user_id = $_SESSION['user_id'];

        $stm = $this->db->prepare('UPDATE orders SET order_status="Processing" WHERE order_id = :order_id AND user_id = :user_id ');
        $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR);

        if($stm->execute()){


            $getOrderDetails = $this->db->prepare('SELECT * FROM orders as o, payment as pt, shipping_address as sa WHERE pt.order_id = :order_id AND sa.user_id = :user_id AND o.user_id = :user_id AND o.order_id = :order_id');
            $getOrderDetails->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
            $getOrderDetails->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
            if($getOrderDetails->execute()){
                $result = $getOrderDetails->fetchAll(PDO::FETCH_ASSOC);
                if(count($result) !=0){
    
                    $getItems = $this->db->prepare('SELECT p.product_id, p.product_name, p.price, c.quantity FROM products as p , checkout as c, orders as o WHERE c.order_id = :order_id AND c.product_id = p.product_id AND o.order_id = c.order_id AND o.user_id = :user_id');
                    $getItems->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                    $getItems->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
                    if($getItems->execute()){
                        $items = $getItems->fetchAll(PDO::FETCH_ASSOC);

                        
                        foreach($items as $item){
                            $delete_selected_items_to_cart = $this->db->prepare('DELETE FROM cart WHERE is_selected_for_checkout=1 AND user_id = :user_id AND product_id = :product_id');
                            $delete_selected_items_to_cart->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                            $delete_selected_items_to_cart->bindParam(':product_id', $item['product_id'], PDO::PARAM_STR_CHAR);
                            $delete_selected_items_to_cart->execute();
                        }

                        $review = array(
                            "order_details" => $result,
                            "items" => $items,
                            "Message"=> "You're order has been Processed."
                        );       
    
    
                        echo json_encode($review , JSON_PRETTY_PRINT);
    
                    }
    
    
    
                }else{
                    echo json_encode(["message" => "No data found."], JSON_PRETTY_PRINT);
                    return ;
                }
            }
        }



    }else{
        http_response_code(401);
    }

}

    public function cancelOrder($order_id){

        $auth = new auth();

        if($auth->check()) {  
            $user_id = $_SESSION['user_id'];
            

            $stm = $this->db->prepare('UPDATE orders SET order_status="Cancelled" WHERE order_id = :order_id AND user_id = :user_id ');
            $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR);

            if($stm->execute()){

                $getOrderDetails = $this->db->prepare('SELECT * FROM orders as o, payment as pt, shipping_address as sa WHERE pt.order_id = :order_id AND sa.user_id = :user_id AND o.user_id = :user_id AND o.order_id = :order_id');
                $getOrderDetails->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
                $getOrderDetails->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                if($getOrderDetails->execute()){
                    $result = $getOrderDetails->fetchAll(PDO::FETCH_ASSOC);
                    if(count($result) !=0){
    
                        $getItems = $this->db->prepare('SELECT p.product_name, p.price, c.quantity FROM products as p , checkout as c, orders as o WHERE o.order_id = c.order_id AND c.product_id = p.product_id AND o.user_id = :user_id AND c.order_id = :order_id');
                        $getItems->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                        $getItems->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);
    
                        if($getItems->execute()){
                            $item = $getItems->fetchAll(PDO::FETCH_ASSOC);
                            
                            $review = array(
                                "order_details" => $result,
                                "items" => $item,
                                "Message"=> "You're order has been Cancelled."
                            );       
    
                            echo json_encode($review , JSON_PRETTY_PRINT);
    
                        }
                    }else{
                        echo json_encode(["message" => "No data found."], JSON_PRETTY_PRINT);
                        return ;
                    }
                }


            }

        }else{
            http_response_code(401);
        }

    }


    public function track($order_id){


        $auth = new auth();

        if($auth->check()){

            $user_id = $_SESSION['user_id'];

            $stm = $this->db->prepare('SELECT * FROM orders WHERE order_id = :order_id AND user_id = :user_id');
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR);
            $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);

            if($stm->execute()){
                $result = $stm->fetchAll(PDO::FETCH_ASSOC);

                if(count($result) != 0){
                    
                    $response = array(
                        "order_id" => $result[0]['order_id'],
                        "order_status" => $result[0]['order_status'],
                        "order_date" => $result[0]['order_date'],
                    );
                    
                    echo json_encode($response , JSON_PRETTY_PRINT);
                    return;
                }else{
                    http_response_code(403);
                }

            }else{
                http_response_code(500);
            }

        }else{
            http_response_code(401);
        }
    }
      
}
