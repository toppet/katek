<?php
$mysql_hostname = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_database = "users";

$connect = mysql_connect($mysql_hostname, $mysql_user, $mysql_password,$mysql_database) or die("Kapcsolodasi hiba");

?>

