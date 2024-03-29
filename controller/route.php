<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */

class Route{

    var $RouteAdapter;

    private $routes = [];
    private $pathNotFound = null;
    private $methodNotAllowed = null;

    public function __construct()
    {
        $Router = file_get_contents(".router");
        $Router = json_decode($Router,1);

        $this->RouteAdapter = $Router;

    }

    public function add($expression, $function, $method = 'get'){

        foreach($expression as $key => $value) {
            array_push($this->routes, [
                'expression'    => $value,
                'function'      => $function,
                'method'        => $method
            ]);
        }
    }

    public function pathNotFound($function){
        $this->pathNotFound = $function;
    }

    public function methodNotAllowed($function){
        $this->methodNotAllowed = $function;
    }

    public function run($basepath = '/'){

        $parsed_url = parse_url($_SERVER['REQUEST_URI']);

        if(isset($parsed_url['path'])){
            $path = $parsed_url['path'];
        }else{
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        $path_match_found = false;
        $route_match_found = false;

        foreach($this->routes as $route){

            if($basepath!=''&&$basepath!='/'){
                $route['expression'] = '('.$basepath.')'.$route['expression'];
            }

            $route['expression'] = '^'.$route['expression'];
            $route['expression'] = $route['expression'].'$';

            if(preg_match('#'.$route['expression'].'#',$path,$matches)){

                $path_match_found = true;

                if(strtolower($method) == strtolower($route['method'])){

                    array_shift($matches);

                    if($basepath != '' && $basepath != '/'){
                        array_shift($matches);
                    }

                    call_user_func_array($route['function'], $matches);

                    $route_match_found = true;
                    break;
                }
            }
        }

        if(!$route_match_found){

            if($path_match_found){
                header("HTTP/1.0 405 Method Not Allowed");
                if($this->methodNotAllowed){
                    call_user_func_array($this->methodNotAllowed, [$path,$method]);
                }
            }else{
                header("HTTP/1.0 404 Not Found");
                if($this->pathNotFound){
                    call_user_func_array($this->pathNotFound, [$path]);
                }
            }

        }

    }

}