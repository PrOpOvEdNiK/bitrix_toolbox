<?php

namespace Axi;

use \Bitrix\Sale\Basket;
use \Bitrix\Sale\BasketComponentHelper;
use \Bitrix\Sale\Fuser;

class Catalog
{
    /**
     * Вернет ассоциативный массив содержимого корзины
     *
     * @return array
     */
    public static function getBasketItems()
    {
        $dbBasket = Basket::getList([
            'filter' => [
                'FUSER_ID' => Fuser::getId(),
                '=LID'     => SITE_ID,
                'ORDER_ID' => null
            ],
            'select' => ['PRODUCT_ID', 'PRICE', 'QUANTITY']
        ]);

        $arBasket = [];
        while ($basketItem = $dbBasket->fetch()) {
            $arBasket[$basketItem['PRODUCT_ID']] = $basketItem;
        }

        return $arBasket;
    }

    /**
     * Вернет информацию о конкретном товаре в корзине
     *
     * @param $productId
     * @param $property - не обязательный, если нужен конкретный параметр
     * @return int | array
     */
    public static function getBasketItem($productId, $property = false)
    {
        $arBasket = Basket::getList([
            'filter' => [
                'FUSER_ID'   => Fuser::getId(),
                '=LID'       => SITE_ID,
                'ORDER_ID'   => null,
                'PRODUCT_ID' => $productId
            ],
            'select' => ['PRODUCT_ID', 'PRICE', 'QUANTITY']
        ])->fetchAll();

        return $property ? (int)$arBasket[0][mb_strtoupper($property)] : $arBasket[0];
    }

    /**
     * Вернет суммарную информацию о корзине
     *
     * @return array
     */
    public static function getBasketSummary()
    {
        $fUser = Fuser::getId();
        $totalPrice = BasketComponentHelper::getFUserBasketPrice($fUser, SITE_ID);
        $totalQuantity = BasketComponentHelper::getFUserBasketQuantity($fUser, SITE_ID);

        $arResult = [
            'PRICE'    => number_format($totalPrice, 0, '.', ' ') ?? 0,
            'QUANTITY' => $totalQuantity ?? 0,
        ];

        return $arResult;
    }
}