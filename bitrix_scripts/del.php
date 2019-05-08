<?

echo del("./bitrix/");

function del($path)
{
    $id = 0;
    $handle = opendir($path);
    while (FALSE !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") {
            if (is_dir($path.$file)) {
                $i += del($path.$file."/");
                rmdir($path.$file);
            } else {
                chmod($path.$file, 0777);
        		@unlink($path.$file);
		        @system("del ".$path.$file);
                if (file_exists($path.$file) == false) {
                    $i++;
                    echo $path.$file."<br>";
                }
            }
        }
    }
    closedir($handle);
    return $i;
}

?>