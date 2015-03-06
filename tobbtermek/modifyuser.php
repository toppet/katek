<?php
$conn = mysqli_connect("localhost","root","","users") or die("Oops can't connect to database");

ini_set("default_charset","UTF-8");

if(!empty( $_POST )){

    $vane = mysqli_num_rows(mysqli_query($conn," SELECT username FROM users WHERE username = '".$_POST['username']."'"));

    if($vane == 1){

        if(mysqli_query($conn,"UPDATE users SET email='".$_POST['email']."', group_name='".$_POST['group_name']."', permission='".$_POST['permission']."' WHERE id='".$_POST['id']."'")){
            echo  "<p>Sikeres módosítás.<img style='width:20px;' src='images/check.png' alt='ok'><p>";

        }else{
            echo "Hiba a feltöltés közben!"; 
        }
    }else {
        echo "A felhasználó nem szerepel az adatbázisban.";
    }
}


?>