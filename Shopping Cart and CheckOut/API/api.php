<?php

use HTTP\Router;



//Different routes

Router::add('/users/show', 'UserController', 'showUser');
Router::add('/getToken', 'TokenController', 'getToken');