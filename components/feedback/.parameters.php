<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arCurrentValues */

$arComponentParameters = array(
    "GROUPS" => [
        "BASE" => array("NAME" => "Основные настройки"),
    ],
    "PARAMETERS" => [
        "EVENT_ALIAS" => [
            "PARENT" => "BASE",
            "NAME" => "Название события",
            "TYPE" => "STRING",
        ],
        "STORE_IBLOCK_ID" => [
            "PARENT" => "BASE",
            "NAME" => "ID инфоблока для сохранения результатов",
            "TYPE" => "STRING",
        ],
        "FIELDS" => [
            "PARENT" => "BASE",
            "NAME" => "Список алиасов полей",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "ADDITIONAL_VALUES" => "Y"
        ],
        "REQUIRED_FIELDS" => [
            "PARENT" => "BASE",
            "NAME" => "Список алиасов обязательных полей",
            "TYPE" => "STRING",
            "MULTIPLE" => "Y",
            "ADDITIONAL_VALUES" => "Y"
        ],
        "UPLOAD_FILE" => [
            "PARENT" => "BASE",
            "NAME" => "Название поля загрузки файла (если пустое то выкл.)",
            "TYPE" => "STRING"
        ],
        "OK_MESSAGE" => [
            "PARENT" => "BASE",
            "NAME" => "Сообщение об успешной отправке",
            "TYPE" => "STRING"
        ],
        "CACHE_TIME" => ["DEFAULT" => 36000000]
    ]
);
