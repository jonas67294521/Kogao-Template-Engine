<?php
/**
 * Copyright (c) 2011-present, Kogao Software, Inc. All rights reserved.
 * <www.kogaoscript.com>
 */
#@ Log Reporting
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-Type: text/html");

setlocale(LC_TIME, "de_DE.UTF-8");

error_reporting(E_ERROR);
ini_set("display_errors", 0);

#@ Base Dir
$protocol = "http://";
$url_parse = $_SERVER['PHP_SELF'];
$url_parse_path = parse_url($url_parse);
$url_parse = str_replace("/","",dirname($url_parse_path["path"])) . "/";
if($url_parse){ $sub_directory = $url_parse; }else{ $sub_directory = ""; }
if($url_parse){ $RoutePath = dirname($url_parse_path["path"]); }else{ $RoutePath = "/"; }

$base_href = $protocol . $_SERVER["SERVER_NAME"] . "/" . $sub_directory;

define("base_href", $base_href);
define("sub_folder", $sub_directory);
define("http_protocol", $protocol);

#@ compressed js Mode
define("js_functions", 1);

#@ database config
define("database_used", false);
define("database_host", 'localhost');
define("database_user", 'root');
define("database_pass", '');
define("database_name", '');
define("database_charset", 'utf8');

define("version_code", "1.0");
define("project_name", "Kogao Template Engine");

#@ Tags
define("Initialize", "");

#@ Languages
$available_languages = ["de", "en"];

#@ Module Facebook
define("facebook_app_id", "730606926996290");

#@ Javascript Modules
define("js_jQuery", 1);
define("js_jrange", 0);

#@ Meta Tags
define("facebook", false);
define("responsive_view", true);
define("theme_color", "#000000");

#@ SCSS Config
define("scss_formatter", "scss_formatter_compressed");
define("scss_folder", "view/assets/css");

#@ Super Functions
function _Replace($e){ return str_replace(array(chr(0x27), chr(0xbf)), array("", ""), $e); }
function _Get($e){ return (!empty($_GET[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_GET[$e])))) : ""; }
function _Post($e){ return (!empty($_POST[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_POST[$e])))) : ""; }
function _Session($e){ return (!empty($_SESSION[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_SESSION[$e])))) : ""; }
function _Cookie($e){ return (!empty($_COOKIE[$e])) ? _Replace(strip_tags(htmlspecialchars(addslashes($_COOKIE[$e])))) : ""; }
function _Price($val){ $val = str_replace(",",".",$val); $val = preg_replace('/\.(?=.*\.)/', '', $val); return floatval($val); }