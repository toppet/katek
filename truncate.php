<?php

$connect = mysqli_connect("localhost",'root','');

if(!$connect){
    die("hiba");
}

$query = "TRUNCATE TABLE gyartas.termekek";

$result = mysqli_query($connect,$query);

if(!$result){
    die("adatbázis trunclate hiba..");
}

mysqli_close($connect);
?>