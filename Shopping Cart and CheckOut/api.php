<?php

use HTTP\Router;



//Different routes

Router::add('/api/users/show', 'UserController', 'showUser');
Router::add('/api/getToken', 'TokenController', 'getToken');
