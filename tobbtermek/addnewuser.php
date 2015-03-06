<?php
$conn = mysqli_connect("localhost","root","","users") or die("Oops can't connect to database");

ini_set("default_charset","UTF-8");

if(!empty( $_POST )){

    $vane = mysqli_num_rows(mysqli_query($conn," SELECT username FROM users WHERE username = '".$_POST['username']."'"));

    if($vane == 0){

        if(mysqli_query($conn,"INSERT INTO users (id,username,password,email,group_name,permission) VALUES(DEFAULT, '".$_POST['username']."', '".md5($_POST['password'])."', '".$_POST['email']."','".$_POST['group_name']."','".$_POST['permission']."' )")){
            echo  "<p>Sikeres feltöltés.<img style='width:20px;' src='images/check.png' alt='ok'><p>";
            
        }else{
            echo "Hiba a feltöltés közben!"; 
        }
    }else{
            echo "A felhasználó már szerepel az adatbázisban.";
    }
}
?>