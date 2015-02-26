<?php

ini_set("default-charset",'UTF-8');
header('Content-Type: text/html; charset=utf-8');

//settings
$cache_ext  = '.html'; //file extension
$cache_time = 300;  //Cache file expires after these seconds (1 hour = 3600 sec)
$cache_folder = 'cache/'; //folder to store Cache files

$dynamic_url = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']; // requested dynamic page (full url)

$cache_file = $cache_folder.md5($dynamic_url).$cache_ext; // construct a cache file

if (file_exists($cache_file) && time() - $cache_time < filemtime($cache_file)) { //check Cache exist and it's not expired.
    ob_start('ob_gzhandler'); //Turn on output buffering, "ob_gzhandler" for the compressed page with gzip.
    readfile($cache_file); //read Cache file
    
    echo '<!-- cached page - '.date('Y F l h:i:s ', filemtime($cache_file)).', Page : '.$dynamic_url.' -->';
    
    ob_end_flush(); //Flush and turn off output buffering
    exit(); //no need to proceed further, exit the flow.
}
//Turn on output buffering with gzip compression.
ob_start('ob_gzhandler');
?>

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>KATEK Hungary Kft.</title>
        <link type="text/css" rel="stylesheet" href="css/header_design.css" />
        <link type="text/css" rel="stylesheet" href="css/production_info_design.css" />
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src='js/production_info.js'></script>
        <script src='js/knob.js'></script>
    </head>
    <body>
        <div id="header"><img src="images/katek_white.png" alt="katek"/></div>
        <div id="page_wrapper">
            <div id="adatok"><?php include("get_alldata.php"); ?></div>
        </div>
        <div id="szamlalo"><input type="text" class="dial" value="0" data-width="50" data-height="50" ></div>
    </body>
</html>

<?php
if (!is_dir($cache_folder)) { //create a new folder if we need to
    mkdir($cache_folder);
}

$fp = fopen($cache_file, 'w');  //open file for writing
//fwrite($fp, ob_get_contents()); //write contents of the output buffer in Cache file

$document=new domDocument('1.0', 'utf-8'); 
$document->loadHTML(ob_get_contents()); 
$document->formatOutput=false; 
$document->encoding='UTF-8';
$document->saveHTMLFile($cache_file);

fclose($fp); //Close file pointer
ob_end_flush(); //Flush and turn off output buffering
?>
