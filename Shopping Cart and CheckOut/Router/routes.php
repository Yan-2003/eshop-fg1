<?php

namespace HTTP;

class Router
{
    private static $routes = [];

    // Method to define a route
    public static function add($route, $controller, $action)
    {
        // Convert route into a regular expression, replacing {param} with a regex group
        $route = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        $route = '/^' . str_replace('/', '\/', $route) . '$/';
        self::$routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    // Method to handle incoming requests
    public static function handle($request)
    {
        // Remove query strings from the request URI
        $request = strtok($request, '?');

        foreach (self::$routes as $routePattern => $route) {
            // Check if the request URI matches the route pattern
            if (preg_match($routePattern, $request, $matches)) {
                $controllerName = $route['controller'];
                $actionName = $route['action'];

                // Include the controller file
                require_once "Controller/$controllerName.php";

                // Instantiate the controller
                $controller = new $controllerName();

                // Extract named parameters from the matches
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Call the controller action with parameters
                call_user_func_array([$controller, $actionName], $params);
                return;
            }
        }

        // If no route matches, return a 404 response
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
