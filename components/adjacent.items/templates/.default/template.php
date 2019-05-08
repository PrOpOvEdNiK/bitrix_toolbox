<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

//printrau($arResult);
?>

<? if (!empty($arResult)): ?>
    <div class="adjacent">

        <? foreach ($arResult as $class => $item): ?>
            <?php
            $isCatalog = $item['IBLOCK_ID'] == CATALOG_IB;
            if ($isCatalog) {
                $title = htmlspecialcharsBack($item["SEO"]["ELEMENT_PAGE_TITLE"]);

                $city = $item["PROPERTY_CITY_VALUE"];
                $district = $item["PROPERTY_DISTRICT_VALUE"];
                $address = $item["PROPERTY_ADDRESS_VALUE"];

                $description = $city;
                if ($district) $description .= ", " . $district;
                if ($address) $description .= ", " . $address;
            } else {
                $title = $item['NAME'];
                $description = $item['PROPERTY_POSITION_VALUE'];
            }
            ?>

            <div class="adjacent__item adjacent__item--<?= $class ?><?= empty($item) ? ' adjacent__item--empty' : ''; ?>">
                <? if (!empty($item)): ?>
                    <a href="<?= $item['DETAIL_PAGE_URL'] ?>"
                       class="adjacent__item__button adjacent__item__button--<?= $class ?>"
                    >
                        <i class="adjacent__item__arrow adjacent__item__arrow--<?= $class ?>"></i>
                    </a>

                    <div class="adjacent__item__content adjacent__item__content--<?= $class ?>">
                        <div class="adjacent__item__content__wrapper">

                            <div class="adjacent__item__content__row">
                                <a class="adjacent__item__content-title" href="<?= $item['DETAIL_PAGE_URL'] ?>">
                                    <?= $title ?>
                                </a>
                            </div>

                            <? if (!empty($description)): ?>
                                <div class="adjacent__item__content__row">
                                <span class="adjacent__item__content-description">
                                    <?= $description ?>
                                </span>
                                </div>
                            <? endif; ?>

                        </div>
                    </div>
                <? endif; ?>
            </div>

        <? endforeach; ?>

    </div>
<? endif; ?>