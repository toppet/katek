<?php
include('config.php');
session_start();
$user_check=$_SESSION['login_user'];
$permission_level = $_SESSION['permission_level'];

$ses_sql=mysql_query("SELECT username FROM users WHERE username='$user_check'");

$row=mysql_fetch_array($ses_sql);

$login_session=$row['username'];

if(!isset($login_session) || !isset($_SESSION['permission_level']))
{
    header("Location: login.php");
    exit();
}

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
        </style>
        </head>
    <body>
        <?php include('header.php'); ?>
        
        
    </body>
    </html>
