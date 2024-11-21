<?php


use Middleware\auth;
use Database\DB;


class CheckoutController{

    public $cart_id;
    public $address_id;
    public $total_amount;

    public function checkout_initiate(){

        $auth = new auth();

        if($auth->check()) { 
            $user_id = $_SESSION['user_id'];
            
            $data = json_decode(file_get_contents("php://input"), true);
    
            $this->cart_id = $data['cart_id'] ?? null;
          
            $DB = new DB();
            $DB->conn();
    
            $result = $DB->query("SELECT cart.user_id, cart.cart_id, shipping_address.address_id, cart.quantity, products.price , products.product_name  FROM cart , shipping_address, products WHERE cart.cart_id='$this->cart_id' AND cart.user_id='$user_id' AND shipping_address.user_id='$user_id' AND products.product_id=cart.product_id");
            
            $result = json_encode($result);
            $result = json_decode($result, true);

            $this->address_id = $result[0]['address_id'];
            $this->total_amount = $result[0]['quantity'] * $result[0]['price'];

            $DB->query("INSERT INTO orders(user_id , cart_id, address_id, total_amount , order_status) VALUES('$user_id','$this->cart_id','$this->address_id', '$this->total_amount', 'Pending')");

            $order = $DB->query("SELECT order_id FROM orders WHERE user_id='$user_id' AND cart_id='$this->cart_id' AND address_id='$this->address_id'");

            $order = json_encode($order);

            $order = json_decode($order, true);
            
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

            
        }else{
            http_response_code(401);
        }

    }   


    public function paymentMethod(){
        $auth = new auth();

        if($auth->check()) {             
            $data = json_decode(file_get_contents("php://input"), true);

            $order_id = $data['order_id'] ?? null;
            $payment_method = $data['payment_method'] ?? null;

            $payment_details = $data['payment_details'] ?? null;


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

            $DB = new DB();
            $DB->conn();
    
            $DB->query("INSERT INTO payment(order_id ,payment_method, payment_status, encrypted_payment_details) VALUES('$order_id', '$payment_method', 'Pending', '$encoded_encrypted_data')");


            $result = array(
                "Message" => "Payment Added Successfully"
            );

            echo json_encode($result, JSON_PRETTY_PRINT);
            return;

        }else{
            http_response_code(401);
        }
    }


    public function review($order_id){

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
            
   
        }else{
            http_response_code(401);
        }

    }

    


}