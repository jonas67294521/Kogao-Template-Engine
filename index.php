<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */
require('config.php');
require('controller/config.php');
require('controller/language.php');

require("controller/route.php");
$route = new Route();

foreach($route->RouteAdapter as $key => $item){

    $route->add($item["url"], function() use($view, $languageArray, $item){
        $initialize = new $item["page"]( $view, $languageArray, func_get_args() );
        $initialize->onExecute();
    }, $item["method"]);

}

/**
 * @page Error
 */
$route->pathNotFound(function($path) use($view, $languageArray){
    $initialize = new Error_Index( $view, $languageArray, [
        "error_page" => $path
    ]);
    $initialize->onExecute();
});

$route->run($RoutePath);