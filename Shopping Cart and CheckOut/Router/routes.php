<?php

namespace HTTP;

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

            // Instantiate the controller
            $controller = new $controllerName();

            // Check if any parameters are passed via GET or POST
            $params = array_merge($_GET, $_POST); // Combine both GET and POST parameters

            // Call the controller action with parameters
            if (!empty($params)) {
                call_user_func_array([$controller, $actionName], $params);
            } else {
                call_user_func([$controller, $actionName]);
            }
        } else {
            http_response_code(404);
            echo "404 - Page Not Found";
        }
    }
}