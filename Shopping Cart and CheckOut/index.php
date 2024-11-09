<?php
require_once './vendor/autoload.php';
require_once './Database/DB.php';
require_once './Router/routes.php';
require_once './api.php';
require_once './Middleware/Auth.php';


foreach (glob(__DIR__ . '/Models/*php') as $filename){
    require_once $filename;
}


use HTTP\Router;

// Include the routing file

// Get the current request URI
$request = $_SERVER['REQUEST_URI'];

// Call the router to handle the request
Router::handle($request);



