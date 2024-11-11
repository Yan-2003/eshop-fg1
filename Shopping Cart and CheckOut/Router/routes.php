<?php

namespace HTTP;

class Router
{
    private static $routes = [];

    // Method to define a route with HTTP method
    public static function add($route, $controller, $action, $method = 'GET')
    {
        // Convert route into a regex pattern, replacing dynamic segments with regex groups
        $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $route);
        self::$routes[] = ['route' => $routePattern, 'controller' => $controller, 'action' => $action, 'method' => strtoupper($method)];
    }

    // Method to handle incoming requests
    public static function handle($request)
    {
        // Remove query strings from the request URI
        $requestUri = strtok($request, '?');
        $requestMethod = $_SERVER['REQUEST_METHOD']; // Get the HTTP method

        foreach (self::$routes as $route) {
            // Match the route with regex and method
            if ($route['method'] === $requestMethod && preg_match("#^{$route['route']}$#", $requestUri, $matches)) {
                $controllerName = $route['controller'];
                $actionName = $route['action'];

                // Include the controller file
                require_once "Controller/$controllerName.php";

                // Instantiate the controller
                $controller = new $controllerName();

                // Filter out numeric keys from matches array to get only named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Call the controller action with parameters
                call_user_func_array([$controller, $actionName], $params);
                return;
            }
        }

        // If no route matches, return a 404 error
        http_response_code(404);
        echo "404 - Page Not Found";
    }
}