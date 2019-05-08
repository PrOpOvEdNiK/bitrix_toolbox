<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class AxiCookiesWarn extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $this->arResult['LINK_TO_PAGE'] = trim($arParams['LINK_TO_PAGE']);
    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}