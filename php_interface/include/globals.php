<?

use \Axi\Common;

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"] . "/__logans/" . date("Y_m_d") . ".log");

$arSite = Common::getSiteInfo();

define("DEV_MODE", "debug");

define("SITE_NAME", $arSite['SITE_NAME']);
define("SITE_NAME_ALT", $arSite['NAME']);
define("SITE_URL", $arSite['SERVER_NAME']);
define("SITE_TEST", substr_count(SITE_URL, "horeca-nk.ru") === 0);
define("SITE_PROTOCOL", SITE_TEST ? "http://" : "http://");

define("SITE_SESSID", md5(date("d..m..", time()) . 'sessid')); //идентификатор сессии (имя переменной)
define("SITE_PREFIX", "HORECA");
define("ENCRYPTION_KEY", "!D8#?RG8#@^2*B$%");

// Инфоблоки
define("CATALOG_IB", 5);
define("INDEXPAGE_SLIDER_IB", 4);

//Paths
define("PATH_CATALOG", '/catalog/');
define("PATH_SEARCH", '/search/');

//search
define("MIN_QUERY_LENGTH", 2);

// price
define("BASE_PRICE", "Розничная");

//Properties
define("PROP_HOTEL_TYPE", 'PROPERTY_TYPE');
