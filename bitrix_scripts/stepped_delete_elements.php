<?php

use Bitrix\Main;
use Bitrix\Main\Application;
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\Data\Cache;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/prolog.php");

Loc::loadMessages(__FILE__);

if (!Main\Loader::includeModule('iblock'))
{
	die('Module `iblock` is not installed');
}
CJSCore::Init(array('jquery'));

$POST_RIGHT = $APPLICATION->GetGroupRight("catalog");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$context = Application::getInstance()->getContext();
$request = $context->getRequest();

if ($request->isPost() && $request->get('action') && $POST_RIGHT == "W" && check_bitrix_sessid())
{
	$arResult = array();
	$arPostData = $request->getPostList()->toArray();
	switch ($request->get('action'))
	{
		case 'clearElements':
			$arSelect = Array("ID", "IBLOCK_ID");
			$arFilter = Array("IBLOCK_ID" => IntVal($arPostData['IBLOCK_ID']));
			$maxForStep = 5;
			$pagesParams = ($arPostData['num'] == 1) ? array('nPageSize' => $maxForStep) : array('nTopCount' => $maxForStep);
			$res = CIBlockElement::GetList(Array(), $arFilter, false,
				$pagesParams, $arSelect);
			$arResult['CNT'] = $res->NavPageCount;
			global $DB;
			while ($arFields = $res->GetNext())
			{
				$DB->StartTransaction();
				if (!\CIBlockElement::Delete($arFields['ID']))
				{
					$arResult['STATUS']['ERROR'][] = $arFields['ID'] . ' not deleted!';
					$DB->Rollback();
				} else
				{
					$arResult['STATUS']['OK'][] = $arFields['ID'];
					$DB->Commit();
				}
				// $arResult[] = $arFields;
			}
			break;
		default:
			print_r($arPostData);
	}

	if ($request->isAjaxRequest() && !empty($arResult))
	{
		$APPLICATION->RestartBuffer();
		header('Content-type: application/json');
		echo \Bitrix\Main\Web\Json::encode($arResult);
		exit();
	}
}

$APPLICATION->SetTitle('Обработчик элементов информационного блока');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

class clIblockEntitiesProcessor
{
	public static function GetIblockTypes($life_time = 3600 * 24 * 30)
	{
		$result = false;
		$cache_params = array('function' => 'CIBlockType::GetList');
		$cache_id = md5(serialize($cache_params));
		$cache_dir = __CLASS__;
		$cache = Cache::createInstance();
		if ($life_time < 0)
		{
			$cache->clean($cache_id, $cache_dir);
		}
		if ($cache->initCache($life_time, $cache_id, $cache_dir))
		{
			$result = $cache->getVars();
		} elseif ($cache->startDataCache() && \Bitrix\Main\Loader::includeModule('iblock'))
		{
			$db_iblock_type = \CIBlockType::GetList();
			while ($ar_iblock_type = $db_iblock_type->Fetch())
			{
				if ($arIBType = \CIBlockType::GetByIDLang($ar_iblock_type["ID"], LANG))
				{
					$arIBType['NAME'] = htmlspecialcharsEx($arIBType["NAME"]);
					$result[$arIBType['IBLOCK_TYPE_ID']] = array(
						'IBLOCK_TYPE_ID' => $arIBType['IBLOCK_TYPE_ID'],
						'NAME' => $arIBType['NAME'],
					);
				}
			}

			$cache->endDataCache($result);
		}

		return $result;
	}

	public static function GetIblockList($filter = array(), $life_time = 3600 * 24 * 30)
	{
		$result = false;

		$cache_params = array('function' => 'CIBlock::GetList', 'filter_params' => $filter);
		$cache_id = md5(serialize($cache_params));
		$cache_dir = __CLASS__;
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($life_time < 0)
		{
			$cache->clean($cache_id, $cache_dir);
		}

		if ($cache->initCache($life_time, $cache_id, $cache_dir))
		{
			$result = $cache->getVars();
		} elseif ($cache->startDataCache() && \Bitrix\Main\Loader::includeModule('iblock'))
		{
			$res = \CIBlock::GetList(
				Array(),
				$filter,
				false
			);
			while ($ar_res = $res->Fetch())
			{
				$result[$ar_res['ID']] = array(
					'ID' => $ar_res['ID'],
					'IBLOCK_TYPE_ID' => $ar_res['IBLOCK_TYPE_ID'],
					'CODE' => $ar_res['CODE'],
					'NAME' => $ar_res['NAME'],
					'ACTIVE' => $ar_res['ACTIVE'],
					'PICTURE' => $ar_res['PICTURE'],
					'DESCRIPTION' => $ar_res['DESCRIPTION'],
					'DESCRIPTION_TYPE' => $ar_res['DESCRIPTION_TYPE'],
					'CATALOG' => \CCatalogSKU::GetInfoByProductIBlock(intval($ar_res['ID'])),
				);
			}

			$cache->endDataCache($result);
		}

		return $result;
	}

	public static function GetIblockElementItems(
		$arParams = array('filter' => array(), 'select' => false, 'sort' => array('name' => 'asc'),
			'page_params' => false, 'group' => false),
		$life_time = 3600)
	{
		if (!isset($arParams['filter']) || empty($arParams['filter'])) return false;
		$arFilter = $arParams['filter'];
		$arSelect = (isset($arParams['select'])) ? $arParams['select'] : false;
		$arSort = (isset($arParams['sort'])) ? $arParams['sort'] : array('name' => 'asc');
		$pageParams = (isset($arParams['page_params'])) ? $arParams['page_params'] : false;
		$groupParams = (isset($arParams['group'])) ? $arParams['group'] : false;

		$result = false;
		$cache_params = array();
		foreach (array_keys($arParams) as $array_key)
		{
			if (is_array($arParams[$array_key]))
			{
				foreach ($arParams[$array_key] as $key => $value)
				{
					$cache_params[$array_key . '-' . $key] = $value;
				}
			} elseif (is_bool($arParams[$array_key]))
			{
				$cache_params[$array_key] = $arParams[$array_key] ? 1 : 0;
			}
		}
		$cache_id = md5(serialize($cache_params));

		$cache_dir = __CLASS__ . '/' . __FUNCTION__;
		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if ($life_time < 0)
		{
			$cache->clean($cache_id, $cache_dir);
		}

		if ($cache->initCache($life_time, $cache_id, $cache_dir))
		{
			$result = $cache->getVars();
		} elseif ($cache->startDataCache() && \Bitrix\Main\Loader::includeModule('iblock'))
		{
			$rsItems = \CIBlockElement::GetList($arSort, $arFilter, $groupParams, $pageParams, $arSelect);
			if (
				is_array($arSelect)
				&& in_array('IBLOCK_ID', $arSelect)
				&& in_array('ID', $arSelect)
			)
			{
				$filterProperties = array();
				foreach ($arSelect as $select)
				{
					if (strpos($select, 'PROPERTY_') !== false)
					{
						$filterProperties[] = str_replace('PROPERTY_', '', $select);
					}
				}

				while ($arElement = $rsItems->GetNextElement())
				{
					$arFields = $arElement->GetFields();
					if (!empty($filterProperties))
					{
						foreach ($filterProperties as $arFilterCode)
						{
							if (!isset($arFields['PROPERTIES'])) $arFields['PROPERTIES'] = array();
							$arFields['PROPERTIES'] = array_merge($arFields['PROPERTIES'], $arElement->GetProperties(false, array(
								'CODE' => $arFilterCode
							)));
							// $arFields['PROPERTIES'][$arFilterCode] = ;
						}
						// $arFields['PROPERTIES'] = $arElement->GetProperties(false,array('CODE'=>$filterProperties));
					}
					$result[] = $arFields;
				}
			} elseif (empty($arSelect))
			{
				while ($arElement = $rsItems->GetNextElement())
				{
					$arFields = $arElement->GetFields();
					$arFields['PROPERTIES'] = $arElement->GetProperties();
					$result[] = $arFields;
				}
			} else
			{
				while ($arElement = $rsItems->GetNext())
				{
					$result[] = $arElement;
				}
			}

			if (!empty($result))
			{
				foreach ($result as $key => $arItem)
				{
					if (!empty($arItem['PROPERTIES']))
					{
						foreach ($arItem['PROPERTIES'] as $pCode => $arProperty)
						{
							$result[$key]['PROPERTIES'][$pCode] = \CIBlockFormatProperties::GetDisplayValue(
								array('ID' => $arItem['ID'], 'NAME' => $arItem['NAME']), $arProperty, '');
						}
					}
				}
			}

			if (isset($pageParams) && !empty($pageParams) && $pageParams['nPageSize'] == 1 && !empty($result[0]))
			{
				$result = $result[0];
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}

$tmp = clIblockEntitiesProcessor::GetIblockList(array('ACTIVE' => 'Y'));
$arIblockList = array();
foreach ($tmp as $item)
{
	$arIblockList[$item['ID']] = $item;
}
// echo '<pre>'; print_r($arIblockList);echo '</pre>';
?>
    <form method="post" action="<? echo $APPLICATION->GetCurPage() ?>"
          enctype="multipart/form-data" name="iblocksProcessorForm">
		<? echo bitrix_sessid_post(); ?>
        <div>
            <select name="IBLOCK_ID" id="IBLOCK_ID">
				<? foreach ($arIblockList as $arIblock)
				{
					?>
                    <option value="<?= $arIblock['ID'] ?>">[<?= $arIblock['ID'] ?>]
                        <?= $arIblock['NAME'] ?></option>
				<? } ?>
            </select>
        </div>
        <div id="loading"><p class="persents" style="display: none;">
            Обработано: <span class="pv">0</span>%  (Строка: <span class="rv">1</span>/
                <span class="maxrows">0</span>) </p></div>
        <div>
            <button id="DeleteElements">Удалить все элементы</button>
        </div>
    </form>

    <script type="text/javascript">
        var deferreds = [];
        var i = 10;
        var maxRows = false;
        var Form;
        $(document).ready(function(){
           lastSelectedIblock = localStorage.getItem('IBLOCK_ID');
           if(lastSelectedIblock!==undefined && parseInt(lastSelectedIblock)>0){
               $('form[name="iblocksProcessorForm"]').find('option[value="'+lastSelectedIblock+'"]').attr('selected','selected');
           }
        });

        $(document).on('change','form[name="iblocksProcessorForm"] select[name="IBLOCK_ID"]', function () {
           localStorage.setItem('IBLOCK_ID',$(this).val()) ;
        });

        $(document).on('click', 'form[name="iblocksProcessorForm"] button#DeleteElements', function () {
            var that = this;
            Form = $(this).parents('form');
            var num = 1;
            var wait = BX.showWait('loading');
            var persentsContainer = Form.find('p.persents');

            var PostParams = {
                num: num, action: 'clearElements', sessid: Form.find('input[name="sessid"]').val(),
                'IBLOCK_ID': Form.find('select[name="IBLOCK_ID"]').val()
            };

            function work_with_row(num, d) {
                var Persents = (parseInt(num)-1) * 100 / parseInt(maxRows);
                persentsContainer.find('span.pv').html(Math.round(Persents));
                persentsContainer.find('span.rv').html(parseInt(num)-1);
                PostParams.num = num;
                $.ajax({
                    type: "POST",
                    url: location.href,
                    data: PostParams,
                    dataType: "json",
                    success: function (data) {
                        d && d.resolve();
                    },
                    onfailure: function () {
                        d && d.resolve();
                    }
                });
            }

            $.ajax({
                type: "POST",
                url: location.href,
                data: PostParams,
                dataType: "json"
            }).then(function (data) {
                if (!maxRows && data.CNT !== undefined) {
                    maxRows = data.CNT;

                    var deferreds = [];
                    var i = 10;

                    persentsContainer.find('span.maxrows').html(maxRows);
                    persentsContainer.show();
                    for (var index = num + 1; index <= maxRows; index++) {
                        (function (index) {
                            var d = new $.Deferred();
                            window.setTimeout(function () {
                                work_with_row(index, d)
                            }, 3000 * index + (i++));
                            deferreds.push(d);
                        })(index);
                    }

                    $.when.apply($, deferreds).done(function () {
                        $('p.persents').html('Обработка завершена!');
                        BX.closeWait('loading', wait);
                        $(that).remove();
                    });
                }

            }, function (reason) {
                console.debug(reason);
            });

            return false;
        });
    </script>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");