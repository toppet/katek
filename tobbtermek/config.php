<?php
    
$mysql_hostname = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_database = "users";

$bd = mysql_connect($mysql_hostname, $mysql_user, $mysql_password) or die("Oops, there was an error");
mysql_select_db($mysql_database, $bd) or die("Opps some thing went wrong");
/*
$conn = mysqli_connect("192.168.0.12:3306","guest","GuestPass!","traceability"); // kapcoslódás az adatbázishoz
if($conn){
    echo 'Sikeres! :)';
}else{
    echo 'Sikertelen.... :(';
}*/
?>