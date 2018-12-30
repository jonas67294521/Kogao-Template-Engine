<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */

$language = substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);

if(in_array($language, $available_languages) == true){
    require( realpath(dirname(__DIR__)) ."/language/" . strtolower(trim(htmlspecialchars($language))) . ".php");
}else{
    #@default language
    require( realpath(dirname(__DIR__)) ."/language/en.php");
}