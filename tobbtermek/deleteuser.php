<?php
$conn = mysqli_connect("localhost","root","","users") or die("Oops can't connect to database");
ini_set("default_charset","UTF-8");

$user_name = $_POST['user_name'];
$user_id = $_POST['user_id'];

if(isset($user_name) && isset($user_id)){
    if($user_name=='admin'){
        echo "Az admin felhasználót nem lehet törölni!";
        die();
    }
    if(!mysqli_query($conn,"DELETE FROM users WHERE id='$user_id' AND username='$user_name'")){
        echo "Hiba az adatok törlésénél.";
    }else{
        echo "A felhasználó törölve.<img style='width:20px;' src='images/check.png' alt='ok'>";
    }
}

?>