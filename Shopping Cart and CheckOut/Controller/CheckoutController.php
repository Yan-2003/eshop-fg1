<?php

use Models\Checkout;


class CheckoutController{

    public $cart_id;
    public $address_id;
    public $total_amount;

    public function checkout_initiate(){

        $checkout = new Checkout();

        $data = json_decode(file_get_contents("php://input"), true);
    
        $this->cart_id = $data['cart_id'] ?? null;


        return $checkout->initiate($this->cart_id);

    }   


    public function paymentMethod(){

        $checkout = new Checkout();

        $data = json_decode(file_get_contents("php://input"), true);

        $order_id = $data['order_id'] ?? null;
        $payment_method = $data['payment_method'] ?? null;

        $payment_details = $data['payment_details'] ?? null;

        return $checkout->payment($order_id, $payment_method, $payment_details);
    }


    public function review($order_id){

        $checkout = new Checkout();

        return $checkout->reviewOrder($order_id);

    }

    
    public function confirm_order(){


        $checkout = new Checkout();
        return $checkout->confirmOrder();
        
    }

    public function cancel_order(){
        
        $checkout = new Checkout();
        return $checkout->cancelOrder();
    }


    public function track_order($order_id){
        
        $checkout = new Checkout();
    
        return $checkout->track($order_id);
    }


}