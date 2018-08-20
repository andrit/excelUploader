<?php 

namespace App\Core;

class Request
{
    
    protected $root_dir;
    protected $config;

    public function __construct()
    {

        $this->config = require(dirname(dirname(dirname(__FILE__))) . "/config.php");
        $this->root_dir = $this->config["rootdir"];

    }
    
    public static function uri()
    {
        
        $request = new static;
        
        $uri = trim($_SERVER['REQUEST_URI'], '/');
        
        // This removes the root directory of the project
        $uri =  str_replace($request->root_dir, '', $uri) == '' ? 
                '/' : 
                str_replace($request->root_dir, '', $uri);
        
        return $uri;
        
    }
    
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
}