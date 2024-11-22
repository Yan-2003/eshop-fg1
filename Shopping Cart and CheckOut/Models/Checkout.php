<?php
namespace Models;
use Database\DB;
use Middleware\auth;
use PDO;
use PDOException;

class Checkout{

    private $db;

    public function __construct()
    {   
        $this->db = (new DB())->conn();


    }

    public function initiate($cart_id){

        $auth = new auth();

        if($auth->check()) { 
            $user_id = $_SESSION['user_id'];
    
            try {
                $stm = $this->db->prepare("
                    SELECT 
                        cart.user_id, 
                        cart.cart_id, 
                        shipping_address.address_id, 
                        cart.quantity, 
                        products.price, 
                        products.product_name  
                    FROM 
                        cart
                    INNER JOIN 
                        shipping_address ON cart.user_id = shipping_address.user_id
                    INNER JOIN 
                        products ON products.product_id = cart.product_id
                    WHERE 
                        cart.cart_id = :cart_id 
                        AND cart.user_id = :user_id
                ");
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stm->bindParam(':cart_id', $cart_id, PDO::PARAM_STR);
                $stm->execute();
                $result = $stm->fetchAll(PDO::FETCH_ASSOC);
    
                $address_id = $result[0]['address_id'];
                $total_amount = $result[0]['quantity'] * $result[0]['price'];
    
                $insert_data = $this->db->prepare("INSERT INTO orders(user_id , cart_id, address_id, total_amount , order_status) VALUES(:user_id, :cart_id ,:address_id , :total_amount , 'Pending')");
                $insert_data->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                $insert_data->bindParam(':cart_id', $cart_id , PDO::PARAM_STR_CHAR);
                $insert_data->bindParam(':address_id', $address_id, PDO::PARAM_STR_CHAR);
                $insert_data->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
                if(!$insert_data->execute()){
                    http_response_code(500);
                    return;
                }
    
                $order = $this->db->prepare("SELECT order_id FROM orders WHERE user_id=:user_id AND cart_id=:cart_id AND address_id=:address_id");
                $order->bindParam(':cart_id', $cart_id , PDO::PARAM_STR_CHAR);
                $order->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                $order->bindParam(':address_id', $address_id, PDO::PARAM_STR_CHAR);
                
                if($order->execute()){

                    $order = $order->fetchAll(PDO::FETCH_ASSOC);
                    
                    $resuponse =  array(    
                        "Order ID" => $order[0]['order_id'],
                        "Order Summary" => [
                            "Product Name" => $result[0]['product_name'],
                            "Price" => $result[0]['price'],
                            "Quantiy"=> $result[0]['quantity'],
                            "Total Price" => ($result[0]['price'] * $result[0]['quantity']),
                            "Paying Method" => [
                                "PayPal",
                                "Credit Card"
                            ]
                        ],
                    );
        
                    echo json_encode($resuponse, JSON_PRETTY_PRINT);
                    return;
                }

            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }

        }else{
            http_response_code(401);
        }

    }


    public function payment($order_id , $payment_method , $payment_details){

        $auth = new auth();

        if($auth->check()) {             
           
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

            $db = (new DB())->conn();


            $stmt = $db->prepare("

                    SELECT 
                        o.order_id, o.user_id, o.order_date, o.cart_id, o.address_id, o.total_amount, o.order_status,
                        sa.street_address, sa.city, sa.state, sa.postal_code, sa.country,
                        p.payment_id, p.payment_date, p.payment_method, p.payment_status, p.encrypted_payment_details,
                        pr.product_id, pr.product_name, pr.description, pr.price, pr.category_id, pr.image_url,
                        c.quantity, c.added_at
                    FROM 
                        orders o
                    JOIN 
                        shipping_address sa ON o.address_id = sa.address_id
                    JOIN 
                        payment p ON p.order_id = o.order_id
                    JOIN 
                        cart c ON c.cart_id = o.cart_id
                    JOIN 
                        products pr ON pr.product_id = c.product_id
                    WHERE 
                        o.order_id = ? 
                        AND o.user_id = ?

                                ");
            $stmt->execute([$order_id, $user_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($result != null){
                $review_order = array(
                    "order_id" => $order_id,
                    "user_id" => $user_id,
                    "item" => [
                        "product_id" => $result[0]['product_id'],
                        "product_name" => $result[0]['product_name'],
                        "price" => $result[0]['price'],
                        "quantity" => $result[0]['quantity'],
                        "total" => $result[0]['total_amount'],
                    ],
                    "shipping_address" => [
                        "street_address" => $result[0]['street_address'],
                        "city" => $result[0]['city'],
                        "state" => $result[0]['state'],
                        "postal_code" => $result[0]['postal_code'],
                        "country" => $result[0]['country']
                    ],
                    "payment_method" => [
                        "type" => $result[0]['payment_method'],
                    ]
                );
                echo json_encode($review_order , JSON_PRETTY_PRINT);
                return ;
            }else{
                http_response_code(403);
                echo json_encode(array(
                    "Message" => "no data found."
                ), JSON_PRETTY_PRINT);
                return ;
            }

        }else{
            http_response_code(401);
        }

    }


    public function confirmOrder(){

        $auth = new auth();

        if($auth->check()) {  
            $user_id = $_SESSION['user_id'];
            $data = json_decode(file_get_contents("php://input"), true);
            $order_id = $data['order_id'] ?? null;

            $stm = $this->db->prepare('UPDATE orders SET order_status="Processing" WHERE order_id = :order_id AND user_id = :user_id ');
            $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR);

            if($stm->execute()){


                $stm = $this->db->prepare('SELECT * FROM orders, cart, products, payment WHERE products.product_id = cart.product_id AND  payment.order_id = :order_id AND orders.user_id=:user_id AND orders.order_id =:order_id AND cart.user_id = :user_id' );
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);

                if($stm->execute()){
                    $order = $stm->fetchAll(PDO::FETCH_ASSOC);
                    $result = array(
                        "Order_details" => [
                            "order_id" => $order[0]['order_id'],
                            "cart_id" => $order[0]['cart_id'],
                            "order_status" => $order[0]['order_status'],
                            "item" => [
                                "product_name" => $order[0]['product_name'],
                                "price" => $order[0]['price'],
                                "quantity" => $order[0]['quantity'],
                            ],
                            "total_amount" => $order[0]['total_amount'],
                            "order_date" => $order[0]['order_date'],   
                            "payment" => [
                                "type" => $order[0]['payment_method'],
                                "payment_date" => $order[0]['payment_date']
                            ] 
                        ],
                        "Message" => "You're order has been Processed.",
                    );
                    echo json_encode($result, JSON_PRETTY_PRINT);
                }else{
                    http_response_code(500);
                }

            }

        }else{
            http_response_code(401);
        }

    }


    public function cancelOrder(){

        $auth = new auth();

        if($auth->check()) {  
            $user_id = $_SESSION['user_id'];
            $data = json_decode(file_get_contents("php://input"), true);
            $order_id = $data['order_id'] ?? null;

            $stm = $this->db->prepare('UPDATE orders SET order_status="Pending" WHERE order_id = :order_id AND user_id = :user_id ');
            $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR);

            if($stm->execute()){


                $stm = $this->db->prepare('SELECT * FROM orders, cart, products, payment WHERE products.product_id = cart.product_id AND  payment.order_id = :order_id AND orders.user_id=:user_id AND orders.order_id =:order_id AND cart.user_id = :user_id' );
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR_CHAR);
                $stm->bindParam(':order_id', $order_id, PDO::PARAM_STR_CHAR);

                if($stm->execute()){
                    $order = $stm->fetchAll(PDO::FETCH_ASSOC);
                    $result = array(
                        "Order_details" => [
                            "order_id" => $order[0]['order_id'],
                            "cart_id" => $order[0]['cart_id'],
                            "order_status" => $order[0]['order_status'],
                            "item" => [
                                "product_name" => $order[0]['product_name'],
                                "price" => $order[0]['price'],
                                "quantity" => $order[0]['quantity'],
                            ],
                            "total_amount" => $order[0]['total_amount'],
                            "order_date" => $order[0]['order_date'],   
                            "payment" => [
                                "type" => $order[0]['payment_method'],
                                "payment_date" => $order[0]['payment_date']
                            ] 
                        ],
                        "Message" => "You're order has been Cancelled.",
                    );
                    echo json_encode($result, JSON_PRETTY_PRINT);
                }else{
                    http_response_code(500);
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
