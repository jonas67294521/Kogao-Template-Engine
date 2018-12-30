<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */

class Functions{

    #@ random int
    public function getRandom($start, $end){
        return mt_rand($start, $end);
    }

    #@ $_GET
    public function getString($response){
        return (!empty($_GET[$response])) ? htmlspecialchars(addslashes($_GET[$response])) : "";
    }

    public function getInt($response){
        return (!empty($_GET[$response])) ? intval($_GET[$response]) : "";
    }

    #@ $_POST
    public function postString($response){
        return (!empty($_POST[$response])) ? htmlspecialchars(addslashes($_POST[$response])) : "";
    }

    public function postInt($response){
        return (!empty($_POST[$response])) ? intval($_POST[$response]) : "";
    }


    public function Debugger($message, $line){
        echo "Line $line: $message";
    }

    #@ lower uppercase
    public function lowercase(&$string){
        $string = (!empty($string)) ? strtolower($string)  : "";
    }

    public function uppercase(&$string){
        $string = (!empty($string)) ? strtolower($string)  : "";
    }

}