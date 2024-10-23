<?php

use HTTP\Router;



//Different routes

Router::add('/api/users/show', 'UserController', 'showUser');
Router::add('/api/getToken', 'TokenController', 'getToken');


/* Routes for the Checkout */

Router::add('/api/checkout/initiate', 'CheckoutController', 'checkout_initiate');
