<?php
//settings
$cache_ext  = '.html'; //file extension
$cache_time     = 300;  //Cache file expires afere these seconds (1 hour = 3600 sec)
$cache_folder   = 'cache/'; //folder to store Cache files
$ignore_pages   = array('', '');

$dynamic_url    = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $_SERVER['QUERY_STRING']; // requested dynamic page (full url)

$cache_file     = $cache_folder.md5($dynamic_url).$cache_ext; // construct a cache file
$ignore = (in_array($dynamic_url,$ignore_pages))?true:false; //check if url is in ignore list

if (!$ignore && file_exists($cache_file) && time() - $cache_time < filemtime($cache_file)) { //check Cache exist and it's not expired.
    ob_start('ob_gzhandler'); //Turn on output buffering, "ob_gzhandler" for the compressed page with gzip.
    readfile($cache_file); //read Cache file
    
    echo '<!-- cached page - '.date('Y F l h:i:s ', filemtime($cache_file)).', Page : '.$dynamic_url.' -->';
    ob_end_flush(); //Flush and turn off output buffering
    exit(); //no need to proceed further, exit the flow.
}
//Turn on output buffering with gzip compression.
?>

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
        <title>KATEK Hungary Kft.</title>
        <link type="text/css" rel="stylesheet" href="css/header_design.css" />
        <script src="js/jquery-2.0.3.min.js"></script>
        <style>
            body{
                margin:0;
            }
            #page_wrapper{
                min-width:1350px;
                width:75%;
                margin:25px auto 0 auto;
                text-align: center;
            }

            table{
                min-width:600px;
                border-collapse:collapse;
                width:45%;              
            }

            table,td{
                padding:5px;
                text-align:center;
                color:#fff;
            }

            .line_header{
                border-top-left-radius: 20px;
                border-top-right-radius: 20px;
                background-color: #eee;
                padding:5px;
                font-size: 20px;
            }

            table th{
                background-color:#bbb;
                padding: 5px;
            }

            .header, table th{
                color:#000;
            }

            table tr:nth-child(even){
                background-color:#1e81cc;
            }

            table tr:nth-child(odd){
                background-color:cornflowerblue;
            }
            #line_1{
                display:block;
                top:0;
                float:left;
            }
            #line_2{
                float:right;

            }
            #line_3{
                float:left;
            }
            #line_4{
                float:right;
            }

            #line_1,#line_2{
                margin-bottom:25px;
            }
        </style>
        
        <script>
            $(document).ready(function(){
                setTimeout(function(){location.reload()},150000);
            });
        </script>
    </head>
    <body>
        
        <div id="header"><img src="images/katek_white.png" alt="katek"/></div>
            <div id="page_wrapper">
            <div id="adatok"><?php include("get_alldata.php"); ?></div>
        </div>
    </body>
</html>

<?php

if (!is_dir($cache_folder)) { //create a new folder if we need to
    mkdir($cache_folder);
}
if(!$ignore){
    $fp = fopen($cache_file, 'w');  //open file for writing
    fwrite($fp, ob_get_contents()); //write contents of the output buffer in Cache file
    fclose($fp); //Close file pointer
}
ob_end_flush(); //Flush and turn off output buffering

?>