<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/*
<?$APPLICATION->IncludeComponent(
    "axi:feedback", 
    "", 
    array(
        "EVENT_ALIAS" => EVENT_ALIAS,
        "STORE_IBLOCK_ID" => STORE_IBLOCK_ID,
        "FIELDS" => array(
            "ANSWER_NAME" => "Имя",
            "ANSWER_PHONE" => "Телефон",
            "ANSWER_EMAIL" => "Электронная почта",
            "ANSWER_TEXT" => "Сообщение",
            ),
        "REQUIRED_FIELDS" => array(
            "ANSWER_NAME",
            "ANSWER_PHONE",
            "ANSWER_EMAIL",
            "ANSWER_TEXT",
            ),
        "UPLOAD_FILE" => "Прикрепить файл",
        "OK_MESSAGE" => "Спасибо, ваше сообщение принято!"
    ),
    false,
    array(
       "HIDE_ICONS" =>  "Y"
    )
);?>
*/

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\Json;
use Bitrix\Iblock\ElementTable;

class AxiBSFeedbackComponent extends CBitrixComponent
{
    private $fileId;

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException(Loc::getMessage('IES_MODULE_NOT_INSTALLED', ['MODULE_ID' => 'iblock']));
        }
    }

    protected function SanitizeData($data)
    {
        return trim(strip_tags(htmlspecialcharsbx($data)));
    }

    protected function PrepareFileArray($file)
    {
        $arFile = $file;
        $arFile["MODULE_ID"] = "main";
        $arFile["del"] = ${"ANSWER_FILE_del"};

        return $arFile;
    }

    public function onPrepareComponentParams($params)
    {
        $request = Context::getCurrent()->getRequest();

        if (\Axi\Helpers::GS('LOTTERY_WITH_GIFTS') && $params['EVENT_ALIAS'] == FOS_LOTTERY_EVENT) {
            $params['FIELDS']['ANSWER_GIFT'] = 'Подарок';
            $params['REQUIRED_FIELDS'][] = 'ANSWER_GIFT';
        }

        $params['IS_AJAX'] = false;
        if ($request->isAjaxRequest() || $request->getPost('AJAX') == 'Y') {
            define('STOP_STATISTICS', true);
            define('NO_AGENT_CHECK', true);
            define('DisableEventsCheck', true);
            define('BX_SECURITY_SHOW_MESSAGE', true);
            $params['IS_AJAX'] = true;

            foreach ($params['FIELDS'] as $field => $field_value) {
                $params[$field] = $this->SanitizeData($request->getPost($field));
            }
            $params['ANSWER_FILE'] = $this->PrepareFileArray($request->getFile('ANSWER_FILE'));

            $params['CURRENT_PAGE_URL'] = $this->SanitizeData($request->getPost('CURRENT_PAGE_URL'));
            $params['CURRENT_PAGE'] = $this->SanitizeData($request->getPost('CURRENT_PAGE'));
            $params['FORM_TITLE'] = $this->SanitizeData($request->getPost('FORM_TITLE'));

            $params['ANSWER_PARAMS_HASH'] = $this->SanitizeData($request->getPost('PARAMS_HASH'));
            $params['ANSWER_EMPTY'] = $this->SanitizeData($request->getPost('EMPTY'));
        }

        $params['EVENT_ALIAS'] = trim($params['EVENT_ALIAS']);
        $params['STORE_IBLOCK_ID'] = intval($params['STORE_IBLOCK_ID']);
        $params['OK_MESSAGE'] = trim($params['OK_MESSAGE']);
        $params["PARAMS_HASH"] = md5($this->GetTemplateName() . $params['EVENT_ALIAS']);

        foreach ($params['REQUIRED_FIELDS'] as &$r_field) {
            $r_field = trim($r_field);
        }

        return $params;
    }

    protected function checkRequiredParams()
    {
        $listRequiredParams = ['PARAMS_HASH', 'EVENT_ALIAS', 'STORE_IBLOCK_ID'];
        foreach ($listRequiredParams as $requiredParam) {
            if (empty($this->arParams[$requiredParam])) {
                throw new SystemException("Обязательный параметр отсутствует - " . $requiredParam);
            }
        }

        if ($this->arParams["IS_AJAX"]) {
            if ($this->arParams["PARAMS_HASH"] != $this->arParams["ANSWER_PARAMS_HASH"]) {
                throw new SystemException("Форма отправленна не с сайта");
            }
            if (!empty($this->arParams["ANSWER_EMPTY"])) {
                throw new SystemException("Бот");
            }
            //@todo разобраться что с сессиями
            //            if (!check_bitrix_sessid()) {
            //                die(Json::encode([$_REQUEST['sessid'], $_SESSION["fixed_session_id"]]));
            //                throw new SystemException("Сессия истекла");
            //            }
            foreach ($this->arParams['REQUIRED_FIELDS'] as $r_field) {
                if (empty($r_field)) {
                    throw new SystemException("Не заполнено обязательное поле");
                }
            }
            if ($this->arParams['ANSWER_FILE']["size"] > UPLOAD_MAX_FILE_SIZE) {
                $maxFileSize = UPLOAD_MAX_FILE_SIZE / 1024 / 1024;
                throw new SystemException("Максимальный размер загружаемого файла " . $maxFileSize . "мб");
            }
        }
    }

    protected function fillResult()
    {
        $this->arResult['PARAMS_HASH'] = $this->arParams['PARAMS_HASH'];
        $this->arResult['TEMPLATE'] = $this->arParams['EVENT_ALIAS'];

        $this->arResult['FIELDS'] = $this->arParams['FIELDS'];
        $this->arResult['UPLOAD_FILE'] = $this->arParams['UPLOAD_FILE'];

        if (\Axi\Helpers::GS('LOTTERY_WITH_GIFTS')) {
            $this->arResult['GIFTS'] = self::getLotteryGifts();
        }
    }

    protected function saveResult()
    {
        if (!$this->arParams['IS_AJAX']) {
            return false;
        } else {
            $this->fileId = CFile::SaveFile($this->arParams['ANSWER_FILE'], "user_files");

            $element = new CIBlockElement();
            $PROPERTY_VALUES = [
                'PHONE' => $this->arParams['ANSWER_PHONE'],
                'EMAIL' => $this->arParams['ANSWER_EMAIL'],
                'PAGE'  => $this->arParams['CURRENT_PAGE'] . ' (' . $this->arParams['CURRENT_PAGE_URL'] . ')',
                'FILE'  => $this->fileId
            ];

            $arMessage = [
                "MODIFIED_BY"       => $GLOBALS['USER']->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID"         => $this->arParams['STORE_IBLOCK_ID'],
                "NAME"              => $this->arParams['FORM_TITLE'] . ' — ' . $this->arParams['ANSWER_NAME'],
                "PREVIEW_TEXT"      => $this->arParams['ANSWER_TEXT'],
                "ACTIVE"            => "Y",
                "ACTIVE_FROM"       => date('d.m.Y'),
                "PROPERTY_VALUES"   => $PROPERTY_VALUES
            ];
            if ($elementId = $element->add($arMessage)) {
                return true;
            } else {
                throw new SystemException($element->LAST_ERROR);
            }
        }
    }

    protected function createEvent()
    {
        $CEventType = new CEventType;
        $EventTypeID = $CEventType->Add(
            [
                "LID"         => "ru",
                "EVENT_NAME"  => $this->arParams["EVENT_ALIAS"],
                "NAME"        => $this->arParams["EVENT_ALIAS"],
                "DESCRIPTION" => "
                    #EMAIL_TO# - E-Mail администратора сайта (отправитель по умолчанию)
                    #EMAIL_ADD# - E-Mail (или список через запятую), на который будут дублироваться исходящие сообщения
                    #MESSAGE_NAME# - Поле ФИО
                    #MESSAGE_PHONE# - Поле ТЕЛЕФОН
                    #MESSAGE_EMAIL# - Поле EMAIL
                    #MESSAGE_TEXT# - Поле СООБЩЕНИЕ/ОТЗЫВ/ВОПРОС
                    #FORM_TITLE# - Название формы
                    #PAGE# - Название страницы с которой отправлена форма
                    #PAGE_URL# - URL страницы с которой отправлена форма
                    #FILE# - Ссылка на файл
                    #MESSAGE# - Все заполненые поля в одном макросе
                    "
            ]
        );
        if ($EventTypeID > 0) {
            $CEventMessage = new CEventMessage;
            $CEventMessage->Add(
                [
                    "ACTIVE"     => "Y",
                    "EVENT_NAME" => $this->arParams["EVENT_ALIAS"],
                    "LID"        => SITE_LID,
                    "EMAIL_FROM" => "message@#SERVER_NAME#",
                    "EMAIL_TO"   => "#EMAIL_TO#",
                    "BCC"        => "#EMAIL_ADD#",
                    "SUBJECT"    => "#FORM_TITLE#",
                    "BODY_TYPE"  => "text",
                    "MESSAGE"    => "#MESSAGE#"
                ]
            );
        }
    }

    protected function sendAdminMail()
    {
        $arEventFields = [
            'EMAIL_TO'      => Option::get('main', 'email_from'),
            'EMAIL_ADD'     => Option::get('main', 'all_bcc'),
            'MESSAGE_NAME'  => $this->arParams['ANSWER_NAME'],
            'MESSAGE_PHONE' => $this->arParams['ANSWER_PHONE'],
            'MESSAGE_EMAIL' => $this->arParams['ANSWER_EMAIL'],
            'MESSAGE_TEXT'  => $this->arParams['ANSWER_TEXT'],
            'FORM_TITLE'    => $this->arParams['FORM_TITLE'],
            'PAGE'          => $this->arParams['CURRENT_PAGE'],
            'PAGE_URL'      => $this->arParams['CURRENT_PAGE_URL'],
            'FILE'          => $this->fileId ? $_SERVER['SERVER_NAME'] . \CFile::GetPath($this->fileId) : 'Файл не прикреплен'
        ];
        $MESSAGE = "";
        foreach ($arEventFields as $fKey => $fValue) {
            if (empty($fValue)) continue;
            $MESSAGE .= $fKey . " — " . $fValue . PHP_EOL;
        }
        $arEventFields['MESSAGE'] = $MESSAGE;

        $sendParams = [
            "EVENT_NAME" => $this->arParams['EVENT_ALIAS'],
            "LID"        => SITE_ID,
            "C_FIELDS"   => $arEventFields
        ];

        $check = Event::send($sendParams);
    }

    protected static function getLotteryGifts()
    {
        $arGifts = ElementTable::getList([
            'filter' => [
                'ACTIVE'    => 'Y',
                'IBLOCK_ID' => LOTTERY_GIFTS_IB
            ],
            'select' => ['ID', 'NAME', 'PREVIEW_PICTURE']
        ])->fetchAll();
        foreach ($arGifts as $key => $arGift) {
            $arGifts[$key]['IMAGE_URL'] = \CFile::GetPath($arGift['PREVIEW_PICTURE']);
        }
        return $arGifts;
    }

    protected static function getLotteryCodeId($params)
    {
        $codeAr = ElementTable::getList([
            'filter' => [
                'ACTIVE'    => 'Y',
                'IBLOCK_ID' => LOTTERY_IB,
                'NAME'      => $params['ANSWER_CODE']
            ],
            'select' => ['ID']
        ])->fetch();

        if ($codeAr) {
            return $codeAr['ID'];
        } else {
            throw new SystemException(Loc::getMessage('IES_WRONG_CODE'));
        }
    }

    protected function saveLotteryResult()
    {
        if (!$this->arParams['IS_AJAX']) {
            return false;
        } else {
            $codeId = self::getLotteryCodeId($this->arParams);

            $element = new CIBlockElement();
            $PROPERTY_VALUES = [
                'CITY'  => $this->arParams['ANSWER_CITY'],
                'SHOP'  => $this->arParams['ANSWER_SHOP_ID'],
                'FIO'   => $this->arParams['ANSWER_FIO'],
                'PHONE' => $this->arParams['ANSWER_PHONE'],
                'GIFT'  => $this->arParams['ANSWER_GIFT'] ?? ''
            ];

            $arFields = [
                "MODIFIED_BY"       => $GLOBALS['USER']->GetID(),
                "IBLOCK_SECTION_ID" => false,
                "IBLOCK_ID"         => $this->arParams['STORE_IBLOCK_ID'],
                "NAME"              => $this->arParams['ANSWER_CODE'],
                "ACTIVE"            => "N",
                "ACTIVE_FROM"       => date('d.m.Y'),
                "PROPERTY_VALUES"   => $PROPERTY_VALUES
            ];
            if ($elementId = $element->Update($codeId, $arFields)) {
                return true;
            } else {
                throw new SystemException($element->LAST_ERROR);
            }
        }
    }

    public function executeComponent()
    {
        $event = $this->arParams["EVENT_ALIAS"];
        try {
            $this->checkModules();
            $this->checkRequiredParams();

            if ($event != FOS_LOTTERY_EVENT) {
                // обработка обычных форм
                $dbET = CEventType::GetList(["TYPE_ID" => $event, "LID" => "ru"]);
                $arET = $dbET->Fetch();
                if (!$arET) {
                    $this->createEvent();
                }

                if ($this->saveResult()) {
                    $this->sendAdminMail();
                }
            } else {
                // обработка формы розыгрыша
                $this->saveLotteryResult();
            }

            $this->fillResult();
        } catch (SystemException $exception) {
            if ($this->arParams['IS_AJAX']) {
                $GLOBALS['APPLICATION']->RestartBuffer();

                header('Content-Type: application/json; charset=utf-8');
                $result = [
                    'status'  => 'exception',
                    'message' => $exception->getMessage()
                ];
                echo Json::encode($result, JSON_UNESCAPED_UNICODE);
                die;
            } else {
                ShowError($exception->getMessage());
            }
        }

        if ($this->arParams['IS_AJAX']) {
            $GLOBALS['APPLICATION']->RestartBuffer();
        }

        ob_start();
        $this->includeComponentTemplate();
        $componentResult = ob_get_clean();

        if ($this->arParams['IS_AJAX']) {
            header('Content-Type: application/json; charset=utf-8');
            $result = [
                'status'  => 'OK',
                'message' => $this->arParams['OK_MESSAGE'],
                'result'  => $this->arResult,
                'HTML'    => $componentResult,
            ];
            echo Json::encode($result);
            die;
        } else {
            $result = $componentResult;
            echo $result;
        }
    }
}