<?php
ini_set('display_errors', false);

$conn = mysqli_connect("10.10.1.205","admin_user","asdfgh","gyartas");

if(!$conn){
    die("Nem sikerült csatlakozni!");
    exit();
}

$product_code = $_POST["product_code"];
$po = $_POST["po"];

$update_query = "UPDATE termekek SET waiting_to_finish = '1' WHERE product_code = '".$product_code."' AND po = '".$po."'";

if(!mysqli_query($conn,$update_query)){
    die("Update production status error");
    exit();
}else{
    echo "<p>Frissítés sikeres!". $_POST['product_code'].", $po</p>";
}

?>