<?php 

namespace App\Core;

use Exception;

class Router
{
    
    protected $routes = array(
        'GET'   => array(),
        'POST'  => array()
    );
    
    public static function load($file)
    { 
        $router = new self;
        require $file;
        return $router;
    }
    
    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
        //error_log('Method: ' . $_SERVER['REQUEST_METHOD'] . '. Route: ' . $uri . '. Controller: ' . $controller,0);
    }
    
    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
        //error_log('Method: ' . $_SERVER['REQUEST_METHOD'] . '. Route: ' . $uri . '. Controller: ' . $controller,0);
    }
    
    public function direct($uri, $request)
    {
        if(array_key_exists($uri, $this->routes[$request]))
        {
            $ca = explode('@', $this->routes[$request][$uri]);         
            $controller = $ca[0];
            $action = $ca[1];
            return $this->callAction($controller, $action);
        }
        
        throw new Exception("No {$uri} route found!");
    }
    
    protected function callAction($controller, $action)
    {
        $controller = "App\\Controllers\\{$controller}";
        $controller = new $controller;

        if( ! method_exists($controller, $action) )
        {
            throw new Exception("{$controller} does not respond to the {$action} action.");
        }
        
        $c = new $controller();

        return $c->$action();
    }
    
}