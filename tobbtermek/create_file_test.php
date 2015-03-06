<?php
 // szöveges fájl készíteése amíg a lekérdezés folyamatban van
    $tmp = fopen("test_file.txt", "w") or die("Unable to open file!");
    $txt = "1";
    fwrite($tmp, $txt);
    fclose($tmp);
?>