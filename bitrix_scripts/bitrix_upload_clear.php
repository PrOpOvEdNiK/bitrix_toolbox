<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

global $USER;
if (!$USER->IsAdmin()) {
    echo "Одумайся или авторизуйся...";
    return;
}
$time_start = microtime(true);
echo '<br>';
///////////////////////////////////////////////////////////////////

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

$deleteFiles = 'no'; //Удалять ли найденые файлы yes/no
$saveBackup = 'no'; //Создаст бэкап файла yes/no
//Папка для бэкапа
$patchBackup = $_SERVER['DOCUMENT_ROOT'] . "/upload/zixnru_Backup/";
//Целевая папка для поиска файлов
$rootDirPath = $_SERVER['DOCUMENT_ROOT'] . "/upload/iblock";
//Создание папки для бэкапа
if (!file_exists($patchBackup)) {
    CheckDirPath($patchBackup);
}

// Получаем записи из таблицы b_file
$arFilesCache = array();
$result = $DB->Query('SELECT FILE_NAME, SUBDIR FROM b_file WHERE MODULE_ID = "iblock"');
while ($row = $result->Fetch()) {
    $arFilesCache[$row['FILE_NAME']] = $row['SUBDIR'];
}


$hRootDir = opendir($rootDirPath);
$count = 0;
$contDir = 0;
$countFile = 0;
$i = 1;
$removeFile=0;
while (false !== ($subDirName = readdir($hRootDir))) {
    if ($subDirName == '.' || $subDirName == '..') {
        continue;
    }
    //Счётчик пройденых файлов
    $filesCount = 0;
    $subDirPath = "$rootDirPath/$subDirName"; //Путь до подкатегорий с файлами
    $hSubDir = opendir($subDirPath);
    
    while (false !== ($fileName = readdir($hSubDir))) {
        if ($fileName == '.' || $fileName == '..') {
            continue;
        }
        $countFile++;

        if (array_key_exists($fileName, $arFilesCache)) { //Файл с диска есть в списке файлов базы - пропуск
            $filesCount++;
            continue;
        }
        $fullPath = "$subDirPath/$fileName"; // полный путь до файла
        $backTrue = false; //для создание бэкапа
        if ($deleteFiles === 'yes') {
            if (!file_exists($patchBackup . $subDirName)) {
                if (CheckDirPath($patchBackup . $subDirName . '/')) { //создал поддиректорию
                    $backTrue = true;
                }
            } else {
                $backTrue = true;
            }
            if ($backTrue) {
                if ($saveBackup === 'yes') {
                    CopyDirFiles($fullPath, $patchBackup . $subDirName . '/' . $fileName); //копия в бэкап
                }
            }
            //Удаление файла
            if (unlink($fullPath)) {
                $removeFile++;
                echo "Удалил: " . $fullPath . '<br>';
            }
        } else {
            $filesCount++;
            echo 'Кандидат на удаление - ' . $i . ') ' . $fullPath . '<br>';
        }
        $i++;
        $count++;
        unset($fileName, $backTrue);
    }
    closedir($hSubDir);
    //Удалить поддиректорию, если удаление активно и счётчик файлов пустой - т.е каталог пуст
    if ($deleteFiles && !$filesCount) {
        rmdir($subDirPath);
    }
    $contDir++;
}
if ($count < 1) {
    echo 'Не нашёл данных для удаления<br>';
}
 if ($saveBackup === 'yes') {
     echo 'Бэкап файлов поместил в: <strong>' . $patchBackup . '</strong><br>';
 }
echo 'Всего файлов удалил: <strong>' . $removeFile . '</strong><br>';
echo 'Всего файлов в ' . $rootDirPath . ': <strong>' . $countFile . '</strong><br>';
echo 'Всего подкаталогов в ' . $rootDirPath . ': <strong>' . $contDir . '</strong><br>';
echo 'Всего записей в b_file: <strong>' . count($arFilesCache) . '</strong><br>';
closedir($hRootDir);


////////////////////////////////////////////////////////////////////
echo '<br>';
$time_end = microtime(true);
$time = $time_end - $time_start;

echo "Время выполнения $time секунд\n";
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
