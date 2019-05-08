<?php

define("AXI_CLASSES_PATH", "/local/php_interface/include/classes");

try {
    \Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
        '\Axi\Iblock'  => AXI_CLASSES_PATH . '/Iblock.php',
        '\Axi\Catalog'  => AXI_CLASSES_PATH . '/Catalog.php',
        '\Axi\Common'  => AXI_CLASSES_PATH . '/Common.php',
        '\Axi\Cache'   => AXI_CLASSES_PATH . '/Cache.php',
        '\Axi\File'    => AXI_CLASSES_PATH . '/File.php',
    ));
} catch (\Bitrix\Main\LoaderException $e) {
    printrau($e->getMessage());
}
