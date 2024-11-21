<?php

use HTTP\Router;



//Different routes

Router::add('/api/users/show', 'UserController', 'showUser');
Router::add('/api/getToken', 'TokenController', 'getToken', 'POST');
Router::add('/api/cart', 'CartController', 'viewCart', 'GET');
Router::add('/api/cart/items', 'CartController', 'addItemToCart', 'POST');
Router::add('/api/cart/items/{product_id}', 'CartController', 'updateItemQuantity', 'PUT');
Router::add('/api/cart/items/{product_id}', 'CartController', 'deleteItemInCart', 'DELETE');
Router::add('/api/cart', 'CartController', 'removeAllItemInCart', 'DELETE');


/* Routes for the Checkout */

Router::add('/api/checkout/initiate', 'CheckoutController', 'checkout_initiate', 'POST');
Router::add('/api/checkout/payment', 'CheckoutController', 'paymentMethod', 'POST');
Router::add('/api/checkout/review/{order_id}', 'CheckoutController', 'review', 'GET');