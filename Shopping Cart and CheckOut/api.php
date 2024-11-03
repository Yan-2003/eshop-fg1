<?php

use HTTP\Router;



//Different routes

Router::add('/api/users/show', 'UserController', 'showUser');
Router::add('/api/getToken', 'TokenController', 'getToken');
Router::add('/api/cart', 'CartController', 'viewCart');
Router::add('/api/cart/items', 'CartController', 'addItemToCart');
Router::add('/api/cart/items/{product_id}', 'CartController', 'updateItemQuantity');
