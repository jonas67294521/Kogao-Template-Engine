<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */
error_reporting(0);
ini_set("display_errors", 0);

function minify_js($javascriptFile) {
    $javascriptFile = str_replace(array("\r\n", "\r", "\n"), ' ', $javascriptFile);
    $javascriptFile = preg_replace("#/\*.*?\*/#", "", $javascriptFile);
    $javascriptFile = preg_replace("#\n+|\t+| +#", " ", $javascriptFile);
    return $javascriptFile;
}

$jsList = [];
$jsFiles = glob("*.{js}", GLOB_BRACE);
$jsList[] = minify_js(file_get_contents("jquery.js") . " function __d(e,array,func){}");
foreach($jsFiles as $js_key => $js_item){
    if(strpos($js_item, "jquery") !== false) {
        //jQuery remove
    }else{
        $jsList[] = "__d(\"ServerJS".ucfirst(strtolower(str_replace(".js","",$js_item)))."\",[\"ENV\",\"".time()."\",\"".base64_encode($js_item)."\"], function(){ \"use strict\"; }); " . minify_js(file_get_contents($js_item));
    }
}
#$jsList[] = "__d(\"BigPipe\",[\"ENV\",\"".time()."\",\"".base64_encode("bigpipe/prototype.js")."\"], function(){ \"use strict\"; }); " . file_get_contents("bigpipe/index.js");
$jsList = join("\n", $jsList);
file_put_contents("../dist/core.js", $jsList, FILE_APPEND || LOCK_EX);