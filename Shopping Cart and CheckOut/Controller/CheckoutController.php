<?php


use Models\Checkout;


class CheckoutController{

    public function checkout_initiate($cart_id){
        $checkout = new Checkout();

        $checkout->initiate($cart_id);

    }   


    


}