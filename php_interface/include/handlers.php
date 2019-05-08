<?php

define("AXI_HANDLERS_PATH", "/local/php_interface/include/classes");

try {
    \Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
        '\Axi\Handler\IBlock'                  => '/local/php_interface/include/handlers/iblock.php',
        '\Axi\Handler\Main'                    => '/local/php_interface/include/handlers/main.php'
    ));
} catch (\Bitrix\Main\LoaderException $e) {
    printrau($e->getMessage());
}

/**
 * Регистрируем обработчики событий
 */
$em = \Bitrix\Main\EventManager::getInstance();
