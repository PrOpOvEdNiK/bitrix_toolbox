<?php

use \Bitrix\Main\Loader;

if ($_REQUEST['errors'] == 'on') {
    error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

date_default_timezone_set("Asia/Novokuznetsk");

try {
    Loader::includeModule("main");
    Loader::includeModule("iblock");
    Loader::includeModule("catalog");
    Loader::includeModule("sale");
//    Loader::includeModule("form");
//    Loader::includeModule("highloadblock");
//    Loader::includeModule("subscribe");
    Loader::includeModule("search");
} catch (\Bitrix\Main\LoaderException $e) {
    printrau($e->getMessage());
}

require_once "include/handlers.php";
require_once "include/classes.php";
require_once "include/functions.php";
require_once "include/globals.php";

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/wsrubi.smtp/classes/general/wsrubismtp.php");