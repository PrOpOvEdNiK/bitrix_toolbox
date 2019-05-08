<?php

namespace Axi;

use \Bitrix\Main\Config\Option;
use \Axi\Cache;
use \Axi\File;

class Common
{

    private static $sIncludePath = "__include/";
    private static $sTextsPath = "_text/";
    private static $sSvgPath = "_svg/";

    /**
     * @param string $message Сообщение, которое будет отображено компонентом bitrix:system.show_message.
     * @param bool $defineConstant Если параметр принимает значение true, то константа ERROR_404 примет значение Y.
     * @param bool $setStatus Если параметр принимает значение true, то будет установлен статус 404 Not Found.
     * @param bool $showPage Если параметр принимает значение true, то рабочая область будет очищена и будет показано содержимое файла /404.php.
     * @param bool $pageFile Файл, который должен быть показан вместо /404.php.
     */
    public static function show404($message = '', $defineConstant = true, $setStatus = true, $showPage = true, $pageFile = false)
    {
        \Bitrix\Iblock\Component\Tools::process404(
            $message, //Сообщение
            $defineConstant, // Нужно ли определять 404-ю константу
            $setStatus, // Устанавливать ли статус
            $showPage, // Показывать ли 404-ю страницу
            $pageFile // Ссылка на отличную от стандартной 404-ю
        );
    }

    public static function getCurrTitle()
    {
        global $APPLICATION;

        return SITE_TEST
            ? "TEST "
            : ""
            . $APPLICATION->ShowTitle('title', true)
            . "—" . SITE_NAME . SITE_NAME_ALT;
    }

    public static function getCurrUrl()
    {
        global $APPLICATION;

        return \CMain::IsHTTPS() ? "https://" : "http://" . $_SERVER['HTTP_HOST'] . $APPLICATION->GetCurUri();
    }

    /**
     * Выводит $filename из папки $sIncludePath
     * @param string $filename Имя файла без расширения (возможно с указанием папки)
     * @param array $arParams массив параметров
     * @param string $sTitle
     * @param bool $bHideIcons запретить/разрешить редактирование в режиме правки
     * @param string $sEditMode html|text|php
     * @param string $sExtension Расширение файла
     */
    public static function GF($filename, $arParams = [], $sTitle = null, $bHideIcons = true, $sEditMode = "text", $sExtension = ".php")
    {
        global $APPLICATION;

        $path = SITE_DIR . self::$sIncludePath . $filename . $sExtension;
        $APPLICATION->IncludeFile($path, $arParams, [
            "MODE"        => $sEditMode,
            "NAME"        => $sTitle,
            "SHOW_BORDER" => !$bHideIcons
        ]);
    }

    /**
     * @see Axi::GF()
     */
    public static function GT($filename, $sTitle = null, $bHideIcons = false, $sEditMode = "text", $sExtension = ".php")
    {
        global $APPLICATION;

        $APPLICATION->IncludeFile(SITE_DIR . self::$sIncludePath . self::$sTextsPath . $filename . $sExtension, [], [
            "MODE"        => $sEditMode,
            "NAME"        => $sTitle,
            "SHOW_BORDER" => !$bHideIcons
        ]);
    }

    /**
     * возврашает результат, а не сразу выводит его в браузер
     * @see Axi::GF()
     */
    public static function GTE($filename, $sTitle = null, $bHideIcons = false, $sEditMode = "text", $sExtension = ".php")
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeFile(SITE_DIR . self::$sIncludePath . self::$sTextsPath . $filename . $sExtension, [], [
            "MODE"        => $sEditMode,
            "NAME"        => $sTitle,
            "SHOW_BORDER" => !$bHideIcons
        ]);

        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    /**
     * @see Axi::GF()
     */
    public static function GSVG($filename, $sTitle = "SVG-файл")
    {
        $path = self::$sSvgPath . $filename;
        self::GF($path, null, $sTitle, true, "text", ".svg");
    }

    /**
     * Обертка для модуля grain.customsettings
     * @param $code string символьный код опции
     * @return string Значение опции
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function GCS($code)
    {
        return Option::get("grain.customsettings", $code) ?? "";
    }

    public static function getAlias($asArray = false)
    {
        global $APPLICATION;
        $sCurDir = $APPLICATION->GetCurDir();
        $arAlias = [];

        if ($sCurDir == "/") $arAlias[] = "index-page";
        elseif (defined('ERROR_404')) $arAlias[] = "e404-page";
        elseif (\CSite::InDir('/catalog/')) $arAlias[] = "catalog-page";
        elseif (\CSite::InDir('/search/')) $arAlias[] = "search";
        elseif (\CSite::InDir('/personal/cart/')) $arAlias[] = "cart";
        elseif (\CSite::InDir('/personal/order/')) $arAlias[] = "order";
        else $arAlias[] = "text-page";

        return $asArray ? $arAlias : implode(' ', $arAlias);
    }

    public static function getContentTpl()
    {
        $tpl = "";
        switch (self::getAlias()) {
            case "index-page":
            case "e404-page":
            case "cart":
            case "order":
                $tpl = "container-main";
                break;
            case "text-page":
                $tpl = "container-content";
                break;
            case "catalog-page":
            case "search":
                $tpl = "container-content-withsidebar";
        }
        return $tpl;
    }

    public static function GetPageProperty($property_id, $default_value = false)
    {
        global $APPLICATION;
        return $APPLICATION->AddBufferContent([&$APPLICATION, "GetProperty"], $property_id, $default_value);
    }

    public static function getSiteInfo($siteId = SITE_ID)
    {
        $cache = new Cache("1day");
        $arResult = $cache->load();

        if (!$cache->loaded) {
            $rsSites = \CSite::GetList($by = "sort", $order = "desc", [
                "LOGIC" => "OR",
                ["LANGUAGE_ID" => $siteId],
                ["LID" => $siteId],
            ]);
            $arResult = $rsSites->Fetch();

            $cache->save($arResult);
        }

        return $arResult;
    }

    public static function getContacts()
    {
        $cache = new Cache();
        $arResult = $cache->load();

        if (!$cache->loaded) {
            $arResult = [];

            $arSelect = ["ID", "IBLOCK_ID", "NAME", 'PROPERTY_PHONES', 'PROPERTY_ADDRESS', 'PROPERTY_REQUISITES', 'PROPERTY_COORDINATES'];
            $arFilter = ["IBLOCK_ID" => CONTACTS_IB];
            $obList = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            if ($arFetch = $obList->Fetch()) {
                $arResult = $arFetch;
            }

            $cache->save($arResult);
        }

        return $arResult;
    }

    public static function getPersonal()
    {
        $cache = new Cache();
        $arResult = $cache->load();

        if (!$cache->loaded) {
            $arResult = [];

            $arSelect = ["ID", "IBLOCK_ID", "NAME", "DETAIL_PICTURE", 'PROPERTY_POSITION', 'PROPERTY_PHONES', 'PROPERTY_EMAILS'];
            $arFilter = ["IBLOCK_ID" => PERSONAL_IB];
            $obList = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            while ($arFetch = $obList->Fetch()) {
                $arFetch['DETAIL_RESIZED'] = File::getResized($arFetch['DETAIL_PICTURE'], 250, 250);
                $arResult[] = $arFetch;
            }

            $cache->save($arResult);
        }

        return $arResult;
    }

    public static function getSocials()
    {
        $cache = new Cache();
        $arResult = $cache->load();

        if (!$cache->loaded) {
            $arResult = [];

            $arSelect = ["ID", "IBLOCK_ID", "NAME", "CODE", 'PROPERTY_URL'];
            $arFilter = ["IBLOCK_ID" => SOCIALS_IB];
            $obList = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            while ($arFetch = $obList->Fetch()) {
                $arResult[] = $arFetch;
            }

            $cache->save($arResult);
        }

        return $arResult;
    }

}
