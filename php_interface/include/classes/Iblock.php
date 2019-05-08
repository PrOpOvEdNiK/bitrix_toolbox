<?php

namespace Axi;

use \Bitrix\Iblock\SectionTable;
use \Axi\Cache;

class Iblock
{
    public static function getSectionByCode($IBLOCK_ID, $SECTION_CODE)
    {
        $cache = new Cache("1min");
        $result = $cache->load();
        if (!$cache->loaded) {
            $arResult = SectionTable::getList([
                'filter' => [
                    'ACTIVE'    => 'Y',
                    'IBLOCK_ID' => $IBLOCK_ID,
                    'CODE'      => $SECTION_CODE,
                ],
                'select' => ['ID', 'NAME']
            ])->fetch();
            $result = $arResult;

            $cache->save($result);
        }

        return $result;
    }

    public static function getSiblingsSections($IBLOCK_ID, $SECTION_ID = false)
    {
        $cache = new Cache("1min");
        $result = $cache->load();
        if (!$cache->loaded) {
            $arResult = SectionTable::getList([
                'filter' => [
                    'ACTIVE'            => 'Y',
                    'IBLOCK_ID'         => $IBLOCK_ID,
                    'IBLOCK_SECTION_ID' => $SECTION_ID,
                ],
                'select' => ['ID', 'CODE', 'NAME']
            ])->fetchAll();
            $result = $arResult;

            $cache->save($result);
        }

        return $result;
    }

    public static function getPropertiesForSection($IBLOCK_ID, $SECTION_ID, $arProperties, $addFilter = [])
    {
        $dbFilter = [
            'ACTIVE'              => 'Y',
            'IBLOCK_ID'           => $IBLOCK_ID,
            'SECTION_ID'          => $SECTION_ID,
            'INCLUDE_SUBSECTIONS' => 'Y'
        ];

        $arFilterProperties = self::dbProcess($dbFilter, $arProperties);

        if ($addFilter) {
            $dbFilteredFilter = array_merge($dbFilter, array_slice($addFilter, 0, 1, true));
            $arPropertiesFiltered = self::dbProcess($dbFilteredFilter, $arProperties);
            foreach ($arPropertiesFiltered as $kAddFilter => $vAddFilter) {
                if ($kAddFilter == 'PROIZVODITEL') continue;
                $arFilterProperties[$kAddFilter] = $vAddFilter;
            }
        }
        
        return $arFilterProperties;
    }

    protected static function dbProcess($dbFilter, $arProperties)
    {
        $dbElements = \CIBlockElement::GetList(
            null,
            $dbFilter,
            null,
            null,
            ['ID', 'PROPERTY_*']
        );

        $resultProperties = [];
        while ($element = $dbElements->GetNextElement()) {
            $arElement = $element->GetFields();
            $arElement['PROPERTIES'] = $element->GetProperties();
            $arElement['DISPLAY_PROPERTIES'] = [];
            foreach ($arProperties as $property) {
                $prop = $arElement["PROPERTIES"][$property];
                $propValue = $prop["VALUE"];
                if ($propValue) {
                    $arElement["DISPLAY_PROPERTIES"][$property] = \CIBlockFormatProperties::GetDisplayValue($arElement, $prop, "catalog_filter_out");
                }

                $resultProperties[$property][] = $arElement["DISPLAY_PROPERTIES"][$property]['DISPLAY_VALUE'];
            }
        }
        $resultProperties = array_map('array_filter', $resultProperties);
        $resultProperties = array_map('array_unique', $resultProperties);
        foreach ($resultProperties as &$resultProperty) {
            sort($resultProperty, SORT_STRING);
        }

        return $resultProperties;
    }

    public static function preparePropertyFilter($arQuery)
    {
        $arPropertyFilter = [];
        foreach ($arQuery as $kQuery => $vQuery) {
            $arPropertyFilter["PROPERTY_{$kQuery}_VALUE"] = $vQuery;
        }

        return $arPropertyFilter;
    }
}