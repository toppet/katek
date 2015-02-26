<?php

/*$conn = mysqli_connect("192.168.0.181","topospeti","asdfg"); // kapcsolódás az adatbázishoz
if(!$conn){
    echo 'Sikertelen csatlakozas.... :(';
    die();
}else{
    echo "SIKER!";
}*/

/* $conn2 = mysqli_connect("localhost","root","");
 if(!$conn2){
    echo 'Sikertelen csatlakozas.... :(';
    die();
}else{
    echo "SIKER!<br/>";
}
     
     $email_query = "SELECT * FROM gyartas.termekek WHERE product_code='12871596F1'";
     $ress = mysqli_query($conn2,$email_query);
     if(!ress){
         echo "<br>nemjó<br/>";
         die();
     }

    $row = mysqli_fetch_array($ress);

    $emailElkuldve =  $row['email'];

     echo "El van e küldve az email: ".$emailElkuldve;

     $update_query = "UPDATE gyartas.termekek SET email = '1' WHERE product_code = '12871596F1'";
  
   $update_result = mysqli_query($conn2,$update_query);
   
    if(!update_result){
       $responseText .= "námjó az update";
    }
    mysql_close($conn2);*/

/*

$create_database = "CREATE DATABASE IF NOT EXISTS gyartas";
       
if(!mysqli_query($conn,$create_database)){
    echo "Hiba az adatbázis elkészítésében.<br/>";
    die();
}
    
$create_table = "CREATE TABLE IF NOT EXISTS gyartas.termekek(
                    pid INT AUTO_INCREMENT NOT NULL,
                    smt INT NOT NULL,
                    product_name VARCHAR(50) NOT NULL,
                    product_code VARCHAR(35) NOT NULL,
                    norma INT NOT NULL,
                    quantity INT NOT NULL,
                    elkeszul DECIMAL(12,3) NOT NULL,
                    email TINYINT(1) UNSIGNED DEFAULT 0,
                    fajlba_kiirva TINYINT(1) UNSIGNED DEFAULT 0,
                    PRIMARY KEY(pid)
                )";

if(!mysqli_query($conn,$create_table)){
    echo "Hiba a tábla elkészítésében.<br/>";
    die();
}

$tabla_urites = "TRUNCATE TABLE gyartas.termekek";

mysqli_query($conn,$tabla_urites);
    
$tomb = $_POST['data'];
$sor_id = $_POST['sor'];
$termekek = array();

// feldarabolom az elküldött adatokat
for($i=0;$i<count($tomb);$i++){
    $termekek[$i] = explode(', ',$tomb[$i]);
}
   
$hi = "INSERT INTO gyartas.termekek (smt,product_name,product_code,norma,quantity,elkeszul,email,fajlba_kiirva) VALUES ";
$ait = new ArrayIterator($termekek);
$cit = new CachingIterator($ait);
  
foreach ($cit as $value){
    $hi.="('$sor_id', ";
    $it = new ArrayIterator($value);
    $c = new CachingIterator($it);

    foreach($c as $k){
        $hi .= "'".$k."'";
        if($c -> hasNext()){
            $hi .= ", ";
        }
    }

    $hi .= ",0,0)";
    if($cit -> hasNext()){
        $hi.=", ";
    }
}
       
//print_r($hi);

if(!mysqli_query($conn,$hi)){
    echo ("Hiba a feltöltésben");
}
*/


$conn = mysqli_connect("localhost","root","");
if(!$conn){
    echo 'Sikertelen csatlakozas.... :(';
    die();
}else{
    echo "SIKER!";
}

$create_database = "CREATE DATABASE IF NOT EXISTS users";

if(!mysqli_query($conn,$create_database)){
    echo "Hiba az adatbázis elkészítésében.<br/>";
    die();
}

$create_table = "CREATE TABLE IF NOT EXISTS users.users(
                    id INT AUTO_INCREMENT NOT NULL,
                    username VARCHAR(50) NOT NULL,
                    password VARCHAR(50) NOT NULL,
                    email VARCHAR(50) NOT NULL,
                    group_name VARCHAR(50) NOT NULL,
                    permission VARCHAR(10) NOT NULL,
                    PRIMARY KEY(id)
                )";

if(!mysqli_query($conn,$create_table)){
    echo "Hiba a tábla elkészítésében.<br/>";
    die();
}

$add_adminuser = "INSERT INTO users.users (id,username,password,email,group_name,permission) VALUES (DEFAULT,'admin','".md5("admin")."','admin@katek.hu','Quality','1')";

if(!mysqli_query($conn,$add_adminuser)){
    die("Hiba a user feltöltésében");
}
mysql_close($conn);

?>
