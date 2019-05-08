<?php

namespace Axi;

use \Bitrix\Main\Data\Cache as BXCache;
use \Bitrix\Main\Data\TaggedCache;

class Cache
{
    const USE_CACHE = true;

    const VARNAME = "arResult";
    const PREFIX  = "ccache_";

    private $obCache, $lifeTime, $cachePath, $cacheID, $var, $tag, $extend, $backtrace, $loaded;

    /**
     *  function foo()
     * {
     * $cache = new \Ext\Cache("10day");
     * $id    = $cache->load();
     * if (!$cache->loaded) $cache->save("value");
     * }
     *
     * @param String $time СТРОКА (!) - время кешеривания. Например, "10day"
     * @param null|string $tag тэг кеша
     * @param null|mixed $extend любые доп. параметры для идентификации кеша
     */
    function __construct($time = "1day", $tag = "iblock_id_new", $extend = null)
    {
        if (!self::USE_CACHE) return $this;

        $this->backtrace = debug_backtrace();
        $this->lifeTime = strtotime($time, 0);
        $this->tag = (string)$tag;
        $this->extend = $extend;

        $this->obCache = BXCache::createInstance();
        $this->cachePath = $this->getCahePath();
        $this->cacheID = $this->getCaheId();

        $this->load();

        return $this;
    }

    public function isLoad()
    {
        return (bool)$this->loaded;
    }

    public function getVar()
    {
        return $this->var;
    }

    /**
     * Загружает переменную из кеша
     * @return mixed
     */
    private function load()
    {
        if (!self::USE_CACHE) return false;

        if ($this->obCache->InitCache($this->lifeTime, $this->cacheID, $this->cachePath)) {
            $vars = $this->obCache->GetVars();

            if (isset($vars[self::VARNAME])) {
                $this->loaded = true;
                $this->var = $vars[self::VARNAME];
            }
        }

        return $this;
    }

    /**
     * Записывает переменную в кеш
     * @param mixed $value
     * @return mixed
     */
    public function save($value = null)
    {
        if (!self::USE_CACHE) return false;

        //кешируем
        $this->obCache->StartDataCache();

        $tagCache = new TaggedCache();
        $tagCache->startTagCache($this->cachePath);
        $tagCache->registerTag($this->tag);
        $tagCache->endTagCache();

        $this->obCache->EndDataCache([self::VARNAME => $value]);

        $this->var = $value;

        return $this;
    }

    /**
     * Сбрасывает кеш по тегу
     *
     * @param $tag
     */
    public static function clean($tag)
    {
        $tagCache = new TaggedCache();
        $tagCache->ClearByTag($tag);
    }

    private function getCahePath()
    {
        $class = $this->backtrace === null ? "noclass" : $this->backtrace[1]['class'];
        $function = $this->backtrace === null ? "nofunction" : $this->backtrace[1]['function'];
        return str_replace("\\", "/", self::PREFIX . $class . '/' . $function . '/');
    }

    private function getCaheId()
    {
        $args = $this->backtrace === null ? null : $this->backtrace[1]['args'];

        return self::VARNAME . serialize($args) . serialize($this->extend);
    }

}
