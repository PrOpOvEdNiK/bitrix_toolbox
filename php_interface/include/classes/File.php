<?php

namespace Axi;

use \Axi\Cache;

class File
{

    const NO_IMAGE_SRC  = "/upload/_base/no-image.png";
    const QUALITY       = 90;
    const USE_FILEMTIME = true;

    public static function getRelativePath($path)
    {
        return self::normalizeLink(substr_count($path, $_SERVER["DOCUMENT_ROOT"]) ? str_replace($_SERVER["DOCUMENT_ROOT"], "", $path) : $path);
    }

    public static function normalizeLink($url = '')
    {
        return preg_replace('#\/{2,}#', '/', $url);
    }

    public static function getResized($file_id, $width = 100, $heght = 100, $sMode = BX_RESIZE_IMAGE_PROPORTIONAL_ALT, $sPath = self::NO_IMAGE_SRC, $quality = self::QUALITY)
    {
        $cache    = new Cache("30day");
        $arResult = $cache->load();

        if (!$cache->loaded)
        {
            $arResult = null;
            $src      = \CFile::GetPath($file_id);

            if (empty($file_id) || empty($src) || !file_exists($_SERVER["DOCUMENT_ROOT"] . $src))
            {
                $arPathParts = pathinfo($sPath);
                $sFileName   = md5($arPathParts['filename'] . $sMode . $width . $heght . $quality . $arPathParts['extension']);

                $destinationFile = $_SERVER["DOCUMENT_ROOT"] . '/upload/resize_cache/no_photo/' . $sFileName . '.' . $arPathParts['extension'];

                if (!file_exists($destinationFile))
                {
                    \CFile::ResizeImageFile($_SERVER["DOCUMENT_ROOT"] . $sPath, $destinationFile, [
                        'width'  => $width,
                        'height' => $heght
                            ], $sMode, true, [], true, $quality);
                }

                $arResult = self::getRelativePath($destinationFile);
            }
            else
            {
                $fileGet  = \CFile::ResizeImageGet($file_id, [
                            'width'  => $width,
                            'height' => $heght
                                ], $sMode, true, [], true, $quality);
                $arResult = $fileGet['src'];
            }

            $cache->save($arResult);
        }


        if (!empty($arResult))
        {

            return self::USE_FILEMTIME ? $arResult : $arResult . "?" . filemtime($_SERVER["DOCUMENT_ROOT"] . $arResult);
        }
        else
        {
            return self::NO_IMAGE_SRC;
        }
    }

    public static function makeFile($src, $path = "saved_files")
    {
        $cache    = new Cache("360day");
        $arResult = $cache->load();

        if (!$cache->loaded)
        {
            $arFile   = \CFile::MakeFileArray($src);
            $arResult = \CFile::SaveFile($arFile, $path);

            $cache->save($arResult);
        }

        return $arResult;
    }

    public static function getFile($ID)
    {
        $cache    = new Cache("360day");
        $arResult = $cache->load();

        if (!$cache->loaded)
        {
            $arResult = \CFile::GetFileArray($ID);

            $cache->save($arResult);
        }

        return $arResult;
    }

}
