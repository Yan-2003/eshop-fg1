<?php

class Router
{
    private static $routes = [];

    // Method to define a route
    public static function add($route, $controller, $action)
    {
        self::$routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    // Method to handle incoming requests
    public static function handle($request)
    {
        // Remove query strings from the request URI
        $request = strtok($request, '?');

        if (array_key_exists($request, self::$routes)) {
            $controllerName = self::$routes[$request]['controller'];
            $actionName = self::$routes[$request]['action'];

            // Include the controller file
            require_once "Controller/$controllerName.php";

            // Instantiate the controller and call the action
            $controller = new $controllerName();
            call_user_func([$controller, $actionName]);
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}

//Different routes
Router::add('/users/show', 'UserController', 'showUser');
