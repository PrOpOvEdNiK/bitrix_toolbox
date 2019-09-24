<?php

function startsWith($haystack, $needle)
{
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function endsWith($haystack, $needle)
{
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

// разбиваем текст на "слова" по пробельным символам
function explodeText($text, $length, $striptags = false)
{
    if ($striptags) $text = strip_tags($text);

    preg_match_all("/[^\s]+/i", $text, $matches);
    $text_arr = $matches[0];

    // объединяем те "слова", которые содержатся внутри тегов
    $text_arr2 = array();
    $text_tmp = array();
    $tag_open = $tag_close = 0;
    foreach ($text_arr as $word) {
        $tag_open += preg_match_all("/<[^\/]+>/Ui", $word, $matches);
        $tag_close += preg_match_all("/<\/.+>/Ui", $word, $matches);
        $text_tmp[] = $word;
        if ($tag_open == $tag_close) {
            $text_arr2[] = implode(" ", $text_tmp);
            $text_tmp = array();
        }
    }

    // вычисляем кол-во слов для каждой части текста
    $result_len = mb_strlen($text_arr2[0]);
    $result_num_words[0] = 1;
    $part = 0;
    foreach (array_slice($text_arr2, 1) as $word) {
        $word_len = mb_strlen($word);
        if ($result_len + $word_len + $result_num_words[$part] > $length) {
            $part++;
            $result_len = $word_len;
            $result_num_words[$part] = 1;
            continue;
        }
        $result_len += $word_len;
        $result_num_words[$part]++;
    }

    // разбиваем текст на части
    $result = array();
    $offset = 0;
    foreach ($result_num_words as $rnw) {
        $result[] = implode(" ", array_slice($text_arr2, $offset, $rnw));
        $offset += $rnw;
    }

    return implode(" ", $result);
}

function array_insert(&$array, $position, $insert)
{
    if (is_int($position)) array_splice($array, $position, 0, $insert);
    else {
        $pos = array_search($position, array_keys($array));
        $array = array_merge(array_slice($array, 0, $pos), $insert, array_slice($array, $pos));
    }
}

function GetRelativePath($sPath)
{
    return substr_count($sPath, $_SERVER["DOCUMENT_ROOT"]) ? str_replace($_SERVER["DOCUMENT_ROOT"], "", $sPath) : $sPath;
}

function F($s)
{
    return CurrencyFormat($s, "RUB");
}

function ruDate()
{
    $translation = array(
        "am"        => "дп",
        "pm"        => "пп",
        "AM"        => "ДП",
        "PM"        => "ПП",
        "Monday"    => "Понедельник",
        "Mon"       => "Пн",
        "Tuesday"   => "Вторник",
        "Tue"       => "Вт",
        "Wednesday" => "Среда",
        "Wed"       => "Ср",
        "Thursday"  => "Четверг",
        "Thu"       => "Чт",
        "Friday"    => "Пятница",
        "Fri"       => "Пт",
        "Saturday"  => "Суббота",
        "Sat"       => "Сб",
        "Sunday"    => "Воскресенье",
        "Sun"       => "Вс",
        "January"   => "Января",
        "Jan"       => "Янв",
        "February"  => "Февраля",
        "Feb"       => "Фев",
        "March"     => "Марта",
        "Mar"       => "Мар",
        "April"     => "Апреля",
        "Apr"       => "Апр",
        "May"       => "Мая",
        "June"      => "Июня",
        "Jun"       => "Июн",
        "July"      => "Июля",
        "Jul"       => "Июл",
        "August"    => "Августа",
        "Aug"       => "Авг",
        "September" => "Сентября",
        "Sep"       => "Сен",
        "October"   => "Октября",
        "Oct"       => "Окт",
        "November"  => "Ноября",
        "Nov"       => "Ноя",
        "December"  => "Декабря",
        "Dec"       => "Дек",
        "st"        => "ое",
        "nd"        => "ое",
        "rd"        => "е",
        "th"        => "ое",
        "01"        => 'Января',
        "02"        => 'Февраля',
        "03"        => 'Марта',
        "04"        => 'Апреля',
        "05"        => 'Мая',
        "06"        => 'Июня',
        "07"        => 'Июля',
        "08"        => 'Августа',
        "09"        => 'Сентября',
        "10"        => 'Октября',
        "11"        => 'Ноября',
        "12"        => 'Декабря',
    );
    if (func_num_args() > 1) {
        $timestamp = func_get_arg(1);
        return strtr(date(func_get_arg(0), $timestamp), $translation);
    } else {
        return strtr(date(func_get_arg(0)), $translation);
    }
}

function array_key_istrue_multi($key, $array)
{
    if (!is_array($array)) return false;
    foreach ($array as $item) {
        if (isset($item[$key]) && $item[$key]) return true;
    }
    return false;
}

/**
 * передвигает элемент массива в его начало
 */
function array_move_key_first($key, &$array)
{
    $array = array($key => $array[$key]) + $array;
}

/**
 * @param int $value
 * @param array $texts array("день", "дня", "дней")
 * @return string
 */
function wordPlural($value, $texts)
{
    $value = intval($value);

    if ($value % 10 === 1 && ($value < 10 || $value > 20)) return $texts[0];
    if (($value % 10 === 2 || $value % 10 === 3 || $value % 10 === 4) && ($value < 10 || $value > 20)) return $texts[1];
    return $texts[2];
}

/**
 * Определяет юзерагент
 * @return string
 */
function getUA()
{
    $keyname_ua_arr = array(
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_OPERAMINI_PHONE_UA',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_X_MOBILE_UA',
        'HTTP_USER_AGENT');
    foreach ($keyname_ua_arr as $keyname_ua) {
        if (!empty($_SERVER[$keyname_ua])) {
            return $_SERVER[$keyname_ua];
        }
    }
    return 'Unknown';
}

function isBot()
{
    $user_agent = getUA();
    $mobile_agents = array("bot", "spider", "archiver", "php", "python", "perl", "wordpress", "crawl", "vkexport");
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            return true;
        }
    }
    return false;
}

function is_iphone()
{
    $user_agent = getUA();
    $mobile_agents = array("iphone", "ipad", "ipod");
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            return true;
        }
    }
    return false;
}

function is_winphone()
{
    $user_agent = getUA();
    $mobile_agents = array("Windows Phone", "IEMobile", "Edge/1");
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            return true;
        }
    }
    return false;
}

function is_android()
{
    $user_agent = getUA();
    $mobile_agents = array("android");
    foreach ($mobile_agents as $device) {
        if (stristr($user_agent, $device)) {
            return true;
        }
    }
    return false;
}

function download($file, $fileName)
{
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Определим IE
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
        $fileName = rawurlencode($fileName);
    }
    $fileName = '"' . $fileName . '"';

    // заставляем браузер показать окно сохранения файла
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream;');
    header('Content-Disposition: attachment; filename=' . $fileName);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    // читаем файл и отправляем его пользователю
    readfile($file);
    exit;
}

function json_result($success, $result = null)
{
    header('Content-Type: application/json');
    echo \Bitrix\Main\Web\Json::encode(['success' => $success, 'result' => $result]);
    CMain::FinalActions();
    die();
}

function json_error($message = 'Error')
{
    json_result(false, $message);
}

function json_success($message = 'Success')
{
    json_result(true, $message);
}

function NormalizeLink($sURL = '')
{
    $sNewURL = preg_replace('#\/{2,}#', '/', $sURL);

    return $sNewURL;
}

function NormalizeQuery($sQuery = '', $removeParams = null)
{
    $sNewQuery = str_replace('&amp;', '&', $sQuery);
    $sNewQuery = preg_replace('#\&{2,}#', '&', $sNewQuery);
    $sNewQuery = preg_replace('#\?{2,}#', '?', $sNewQuery);
    $sNewQuery = rtrim($sNewQuery, "&");
    $sNewQuery = rtrim($sNewQuery, "?");
    $sNewQuery = removeQueryParams($sNewQuery, $removeParams);

    return $sNewQuery;
}

function removeQueryParams($url, $keys)
{
    if (!is_array($keys)) $keys = [$keys];

    $parsedUrl = parse_url($url);
    parse_str($parsedUrl["path"], $arQuery);

    foreach ($keys as $key) {
        if (array_key_exists($key, $arQuery)) {
            unset($arQuery[$key]);
        }
    }

    return http_build_query($arQuery);
}

function FetchSQLRows($table, $select = "*", $where = null, $limit = null, $oneResult = false, $sort = false)
{
    global $DB;

    $q = "SELECT $select FROM $table";
    if (!empty($where)) $q .= " WHERE " . $where;
    if (!empty($limit)) $q .= " LIMIT " . $limit;
    if (!empty($sort)) $q .= " ORDER BY " . $sort[0] . " " . $sort[1];

    $rows = [];

    $rs = $DB->Query($q);
    while ($rw = $rs->Fetch()) {
        $rows[] = $rw;

        if ($oneResult) break;
    }

    return $rows;
}

function FetchSQLOneResult($table, $select = "*", $where = null, $limit = null)
{
    $rows = FetchSQLRows($table, $select, $where, $limit, true);

    return $rows[0];
}

function SplitSQL($file, $delimiter = ';')
{
    set_time_limit(0);
    global $DB;

    if (is_file($file) === true) {
        $file = fopen($file, 'r');

        if (is_resource($file) === true) {
            $query = array();

            while (feof($file) === false) {
                $query[] = fgets($file);

                if (preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1) {
                    $query = trim(implode('', $query));

                    /* if (mysql_query($query) === false)
                      {
                      echo '<h3>ERROR: ' . $query . '</h3>' . "\n";
                      }
                      else
                      {
                      echo '<h3>SUCCESS: ' . $query . '</h3>' . "\n";
                      } */

                    $DB->Query($query);

                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }

                    flush();
                }

                if (is_string($query) === true) {
                    $query = array();
                }
            }

            return fclose($file);
        }
    }

    return false;
}

/**
 * Сохраняет base64 строку в картинку с регистрацией в БД через API битрикса
 * @param type $base64String
 * @param string $path руть до папки внутри /upload/
 * @param string $fileName имя файла
 * @return int ID созданного файла
 */
function base64ToFile($base64String, $path, $fileName, $oldId = false)
{
    //полный  путь до временного файла
    $dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/' . $path;

    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    $fullPath = NormalizeLink($dir . '/' . $fileName);

    $fp = fopen($fullPath, "wb");
    fwrite($fp, base64_decode($base64String));
    fclose($fp);

    $arFile = \CFile::MakeFileArray($fullPath);

    $arIMAGE = (array)$arFile + array(
            "old_file"  => $oldId,
            "del"       => "Y",
            "MODULE_ID" => "iblock"
        );

    $fid = \CFile::SaveFile($arIMAGE, $path);

    return $fid;
}

function timerGet()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

/**
 * выводит время работы куска кода от предыдущего вызова timerFlag() до текущего
 */
function timerFlag($round = 4, $debug = true, $adminOnly = false)
{
    global $timer, $timer_iteration, $USER;

    if ($adminOnly && !$USER->IsAdmin()) return;

    if (empty($timer)) $timer = timerGet();
    if (empty($timer_iteration)) $timer_iteration = 0;
    $old_timer = $timer;
    $timer = timerGet();

    if ($debug) {
        $backtrace = debug_backtrace();
        $dbg_nfo = " " . $backtrace[0]['file'] . " (" . $backtrace[0]['line'] . ") [i = " . $timer_iteration . "]";
    }
    $timer_iteration++;

    $razn = round($timer - $old_timer, $round);
    if ($razn > 0.5) $razn = "<b>$razn</b>";
    echo $razn;

    if ($debug) {
        echo $dbg_nfo;
    }

    echo '<br/>';
}

if (!function_exists('mb_ucfirst')) {

    function mb_ucfirst($string, $encoding = null)
    {
        $encoding = is_null($encoding) ? mb_detect_encoding($string) : $encoding;
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }

}

function is_array_has_next($array)
{
    !is_array($array) ? false : (next($array) === false ? false : true);
}

function is_array_has_prev($array)
{
    !is_array($array) ? false : (prev($array) === false ? false : true);
}

function get_last_array_key($array)
{
    end($array);         // move the internal pointer to the end of the array
    return key($array);  // fetches the key of the element pointed to by the internal pointer
}

function isPost($action = false)
{
    $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

    if ($action === false) {
        return $request->isPost();
    } elseif ($action === true) {
        return $request->isPost() && $request->getPost("AJAX") == "Y";
    } else {
        return $request->isPost() && $request->getPost("AJAX") == "Y" && $request->getPost("ACTION") == $action;
    }
}

function fixPhone($phone)
{
    $ex_phone = NormalizePhone($phone, 11);

    return empty($ex_phone) || strlen($ex_phone) != 11 ? NULL : substr($ex_phone, 1);
}

/**
 * удаляет начальные и конечные пробелы, неразрывные пробелы, табуляции, переносы строк и т.д. и т.п.
 * @param string $str
 * @return string
 */
function allTrim($str)
{
    $str = str_replace("\xA0", " ", $str);
    $str = preg_replace("/\s+/", " ", $str);
    $str = preg_replace('~\x{00a0}~siu', ' ', $str);

    return trim($str);
}

function isEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function printPrice($iPrice = 0, $sCode = PUBL_CURRENCY, $precision = 0)
{
    $roundedPrice = (float)round($iPrice, $precision);
    return empty($roundedPrice) ?
        " руб." :
        CurrencyFormat(round($iPrice, $precision), $sCode);
}

/**
 *
 * @param string $path относительно папки /bitrix/cache/ (если не задан параметр $siteCache)
 * @param bool $siteCache если TRUE, то $path относительно папки /bitrix/cache/SITE_ID/bitrix/
 */
function clearCache($path, $siteCache = false)
{
    $dir = $siteCache ? SITE_ID . '/bitrix/' . $path : $path;

    BXClearCache(true, $dir);
}

function ShowCondTitle()
{
    global $APPLICATION;
    $altTitle = $APPLICATION->GetTitle('alt_title', true);
    if (!empty($altTitle)) {
        echo $altTitle;
    } else {
        $APPLICATION->ShowTitle(false, true);
    }
}

function AdminException($arErrors)
{
    global $APPLICATION;

    if (!empty($arErrors)) {
        $obAdminException = new \CAdminException();
        foreach ($arErrors as $sError) {
            $obAdminException->AddMessage(array("text" => $sError));
        }

        $APPLICATION->ThrowException($obAdminException);
        return false;
    }

    return true;
}

function getHash($action, $string)
{
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'Vf5dfddHEhSDfdgF';
    $secret_iv = 'fdddgVHEhfSDf5dF';

    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if ($action == 'encrypt') {
        $output = base64_encode(openssl_encrypt($string, $encrypt_method, $key, 0, $iv));
    }

    if ($action == 'decrypt') {
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

/**
 * Удаляет все файлы из папки. Полный путь, в конце - слеш
 * @param string $dir
 */
function clearPath($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir . "/" . $object)) clearPath($dir . "/" . $object);
                else unlink($dir . "/" . $object);
            }
        }

        rmdir($dir);
    }
}

function isAdmin()
{
    global $USER;

    if (!$USER->IsAuthorized()) {
        return false;
    }

    if ($USER->IsAdmin()) {
        return true;
    }
}

/**
 * Вырезает из строки всё, кроме цифр
 * @param string $string
 * @return string
 */
function cutAllButNumbers($string)
{
    return (int)preg_replace("/[^0-9]/", "", $string);
}

/**
 * @param string $string
 * @param string $mode 7,8
 * @param bool $clear
 * @return string
 */
function getPhoneFromString($string, $mode, $clear = false)
{
    $string = preg_replace("/[^0-9]/", "", $string);
    if (strlen($string) == 11) {
        $string = substr($string, 1);
    }
    $phone = intval($string);
    if ($clear) {
        return $phone;
    }

    switch ($mode) {
        case "7":
            return "7{$phone}";
        case "8":
            return "8{$phone}";
        default:
            return "+7 ("
                . substr($phone, 0, 3) . ") "
                . substr($phone, 3, 3) . "-"
                . substr($phone, 6, 2) . "-"
                . substr($phone, 8, 2);
    }
}

function pars($var, $die = true, $onlyAdmin = true)
{
    global $APPLICATION;
    global $USER;

    if ($onlyAdmin && !$USER->IsAdmin()) {
        return false;
    }
    if ($die) {
        $APPLICATION->RestartBuffer();
    }

    $backtrace = debug_backtrace();
    echo '<pre>' . $backtrace[0]['file'] . ' on line ' . $backtrace[0]['line'] . '</pre>';
    echo '<pre>';
    var_dump($var);

    if ($die) {
        die("---END---");
    }
    return var_export($var, true);
}

function getSiteInfo($siteId = SITE_ID)
{
    return CSite::GetByID($siteId)->Fetch();
}