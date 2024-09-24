<?php

// Include the routing file
require_once 'Routes/routes.php';

// Get the current request URI
$request = $_SERVER['REQUEST_URI'];

// Call the router to handle the request
Router::handle($request);



//echo json_encode($user, JSON_PRETTY_PRINT);


