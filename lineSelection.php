<?php
include('config.php');
session_start();


if(!isset($_SESSION['login_user']) || !isset($_SESSION['permission_level']))
{
    header("Location: login.php");
    exit();
}

/*//ellenőrzöm a felhasználói szintet, és aszerint adom meg a jogokat a használathoz.
switch($_SESSION['permission_level'] ){
    case '3':
        header('location: ajaxproba.php?id=1');
        exit();
        break;
    case '2':

        break;
    case '1':
        header("location: index.php");
        exit();
        break;
} */

  

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Katek KFT. - Production Line Selection</title>
        
        <script src="js/jquery-2.0.3.min.js"></script>
        <style>
            body{
                margin:0;
            }
            #selection{
                margin: 0 auto 0 auto;
                width: 980px;
                text-align: center;
            }
            #selection ul{
                padding: 0;
                margin: 0;
                list-style: none;
            }
            
            #selection ul li {
                display: inline-block;
                border: 2px solid #0094ff;
            }
            #selection ul li:hover{
                background-color: #ccc;
            }
            #selection ul li a{
                display: block;
                text-decoration: none;
                color: #0094ff;
                font-weight: bold;
                width: 150px;
                padding: 25px;    
            }
            #menu{
                border:2px solid red;
                height:500px;
            }
            #menu ul li{
                display:inline;
            }
            .mymenu li{
                display:inline;
            }
            
        </style>
        <link type="text/css" rel="stylesheet" href="css/header_design.css" />
    </head>
    <body>
        <?php include_once('header.php'); ?>
        <div id="selection">
            <ul>
                <li><a href="productSelect.php?id=1">LINE 1</a></li>
                <li><a href="productSelect.php?id=2">LINE 2</a></li>
                <li><a href="productSelect.php?id=3">LINE 3</a></li>
                <li><a href="productSelect.php?id=4">LINE 4</a></li>
            </ul>
        </div>

        <script type="text/javascript">
            var middle = window.innerHeight / 2;
            document.getElementById("selection").style.marginTop = middle + "px"; 
        </script>
    </body>
</html>
