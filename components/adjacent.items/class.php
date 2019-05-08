<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Iblock\InheritedProperty;


/*
$APPLICATION->IncludeComponent(
    "axi:adjacent.items",
    "",
    array(
        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
        "ELEMENT_ID" => $ElementID,
        "FILTER_ADD" => [
            "PROPERTY_SHOW_IN_HISTORIES_VALUE" => "Y"
        ],
        "SELECT_ADD" => [
            "PROPERTY_POSITION"
        ],
        "SORT_BY1" => $arParams["SORT_BY1"],
        "SORT_ORDER1" => $arParams["SORT_ORDER1"],
        "SORT_BY2" => $arParams["SORT_BY2"],
        "SORT_ORDER2" => $arParams["SORT_ORDER2"],
    ),
    false,
    array(
        "HIDE_ICONS" => "Y"
    )
);
*/

class AdjacentItemsComponent extends CBitrixComponent
{
    protected function checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException('Модуль инфоблоков не установлен');
        }
    }

    public function onPrepareComponentParams($params)
    {
        $addFilter = $params['FILTER_ADD'] ?: [];
        $addSelect = $params['SELECT_ADD'] ?: [];

        $defaultFilter = [
            "ACTIVE"     => "Y",
            "IBLOCK_ID"  => $params["IBLOCK_ID"],
            "SECTION_ID" => $params["SECTION_ID"],
        ];
        $params['GET_LIST_FILTER'] = array_merge($defaultFilter, $addFilter);

        $defaultSelect = ["ID", 'IBLOCK_ID', "NAME", "DETAIL_PAGE_URL"];
        $params['GET_LIST_SELECT'] = array_merge($defaultSelect, $addSelect);

        return $params;
    }

    protected function getAdjacentItems()
    {
        $params = $this->arParams;

        $adjSort = [
            $params["SORT_BY1"] => $params["SORT_ORDER1"],
            $params["SORT_BY2"] => $params["SORT_ORDER2"]
        ];
        $adjFilter = $this->arParams['GET_LIST_FILTER'];
        $adjPagen = [
            "nElementID" => $params['ELEMENT_ID'],
            "nPageSize"  => 1
        ];
        $adjSelect = $this->arParams['GET_LIST_SELECT'];

        $adjacent = [];
        $rawResult = CIBlockElement::GetList($adjSort, $adjFilter, false, $adjPagen, $adjSelect);
        while ($result = $rawResult->GetNext()) {
            if ($params['IBLOCK_ID'] == CATALOG_IB) {
                $ipropValues = new InheritedProperty\ElementValues(
                    $params["IBLOCK_ID"],
                    $result["ID"]
                );
                $result['SEO'] = $ipropValues->getValues();
            }

            $adjacent[] = $result;
        }

        $this->setResult($adjacent);
    }

    protected function setResult($adjacent)
    {
        $currentID = $this->arParams['ELEMENT_ID'];
        $count = count($adjacent);

        switch ($count) {
            case $count === 3:
                $this->arResult['prev'] = $adjacent[0];
                $this->arResult['next'] = $adjacent[2];
                break;
            case $count === 2:
                if ($currentID == $adjacent[0]['ID']) {
                    $this->arResult['prev'] = [];
                    $this->arResult['next'] = $adjacent[1];
                }
                if ($currentID == $adjacent[1]['ID']) {
                    $this->arResult['prev'] = $adjacent[0];
                    $this->arResult['next'] = [];
                }
                break;
            default:
                $this->arResult = [];
        }
    }

    public function executeComponent()
    {
        try {
            $this->checkModules();
            $this->getAdjacentItems();
        } catch (SystemException $exception) {
            ShowError($exception->getMessage());
        }

        $this->includeComponentTemplate();
    }
}