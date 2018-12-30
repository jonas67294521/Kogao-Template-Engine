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

/**
 * @page Home
 */
$route->add(["/", "/index.php", "/home", "/profile/(.*)/"], function($id) use($view, $languageArray){
    $initialize = new Home_Index( $view, $languageArray, [
        "user_id" => $id
    ] );
    $initialize->onExecute();
});

/**
 * @page test
 */
$route->add(["/test"], function() use($view, $languageArray){
    $initialize = new Test_Index($view, $languageArray, []);
    $initialize->onExecute();
});

/**
 * @page Error
 */
$route->pathNotFound(function($path) use($view, $languageArray){
    $initialize = new Error_Index( $view, $languageArray, [
        "error_page" => $path
    ]);
    $initialize->onExecute();
});

$route->run('/kogao_template');