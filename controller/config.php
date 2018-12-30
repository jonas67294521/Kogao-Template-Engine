<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */
define('base_url', http_protocol .$_SERVER["HTTP_HOST"].'/' . sub_folder);
define('modal_dir', 'modal/');
define('view_dir', 'view/');
define('cache_dir', 'view/cache/');
define('controller_dir', 'controller/');

/* Include RainTPL */
require('template.php');

raintpl::configure("base_url", base_url );
raintpl::configure("tpl_dir", view_dir );
raintpl::configure("cache_dir", cache_dir );

$view = new RainTPL;

/**
 * @param $requireClass
 * @package AutoLoader
 */

spl_autoload_register(function ($requireClass){

    $OrginalClassName = $requireClass;

    $requireClass = str_replace('\\', '', $requireClass);
    $requireClass = strtolower($requireClass);
    $requireClass = str_replace('_', '/', $requireClass);
    $requireClassModal = modal_dir . $requireClass . '.php';
    $requireClassController = controller_dir . $requireClass . '.php';

    // Prüfe ob Modal Ordner vorhanden ist
    if(!is_dir("modal/" . dirname($requireClass))){
        //Class
        mkdir("modal/" . dirname($requireClass));
        $Builder = file_get_contents("controller/builder.txt");
        $Builder = str_replace(["{ClassName}", "{TemplatePage}", "{Date}"], [$OrginalClassName, "page/".dirname($requireClass)."/index", date("d.m.Y")], $Builder);
        file_put_contents("modal/" . dirname($requireClass) . "/index.php", $Builder, FILE_APPEND || LOCK_EX);

        // Template
        mkdir("view/page/" . dirname($requireClass));
        file_put_contents("view/page/" . dirname($requireClass) . "/index.html", '{include="../header"}<bigpipe id="'.dirname($requireClass).'_index" css="" js=""></bigpipe>{include="../footer"}', FILE_APPEND || LOCK_EX);
    }

    if( !file_exists($requireClassModal) ) {
        require($requireClassController);
    }else{
        require($requireClassModal);
    }

});
