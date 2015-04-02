<?php
error_reporting(E_ERROR);
$conn = mysqli_connect("localhost","root",""); // kapcsolódás az adatbázishoz
mysqli_set_charset( $conn, 'utf-8');
if(!$conn){
    echo 'Sikertelen csatlakozas... :(';
    die();
}

$create_database = "CREATE DATABASE IF NOT EXISTS gyartas";
       
if(!mysqli_query($conn,$create_database)){
    echo "Hiba az adatbázis elkészítésében.<br/>";
    die();
}
    
$create_table = "CREATE TABLE IF NOT EXISTS gyartas.termekek(
                    pid INT UNSIGNED AUTO_INCREMENT NOT NULL,
                    smt INT NOT NULL,
                    product_name VARCHAR(50) NOT NULL,
                    product_code VARCHAR(35) NOT NULL,
                    norma INT NOT NULL,
                    ict INT NOT NULL,
                    tht INT NOT NULL,
                    fct INT NOT NULL,
                    assembling INT NOT NULL,
                    quantity INT NOT NULL,
                    po VARCHAR(7) NOT NULL,
                    production_time DECIMAL(12,3) NOT NULL,
                    sample_production TINYINT(1) UNSIGNED DEFAULT 0,
                    email_sent TINYINT(1) UNSIGNED DEFAULT 0,
                    file_written TINYINT(1) UNSIGNED DEFAULT 0,
                    sample_done TINYINT(1) UNSIGNED DEFAULT 0,
                    production_done TINYINT(1) UNSIGNED DEFAULT 0,
                    waiting_to_finish TINYINT(1) UNSIGNED DEFAULT 0,
                    production_time_final DECIMAL(12,3) DEFAULT 0,
                    PRIMARY KEY(pid)
                )";

if(!mysqli_query($conn,$create_table)){
    echo "Hiba a tábla elkészítésében.<br/>";
    die();
}


$tomb = $_POST['data'];
$sor_id = $_POST['sor'];
$termekek = array();

//$tabla_urites = "TRUNCATE TABLE gyartas.termekek";
// Ha üres terméktömb kerül átadásra akkor minden, az adott sorra vonatkozó adatot törölődik az adatbázisból! 
if (count($tomb)==0){
    soradatokTorlese($sor_id,$conn);
    echo "A sorra vonatkozó adatok törölve!";
    exit();
}
// minden egyes feltöltés előtt töröljük az adott sorra vonatkozó termékeket
soradatokTorlese($sor_id,$conn);
function soradatokTorlese($sor_id,$conn){
    $tabla_urites = "DELETE FROM gyartas.termekek WHERE smt='".$sor_id."'";

    if(!mysqli_query($conn,$tabla_urites)){
        echo "Hiba a tábla törlésében.<br/>";
        die();
    }
}

// feldarabolom az elküldött adatokat
for($i=0;$i<count($tomb);$i++){
    $termekek[$i] = explode(', ',$tomb[$i]);
}

//$hi = "INSERT INTO gyartas.termekek (smt,product_name,product_code,norma,ict,tht,fct,assembling,quantity,po,production_time,sample_production,email_sent,file_written,sample_done,production_done) VALUES";
//$hi = "INSERT INTO gyartas.termekek (smt,product_name,product_code,norma,ict,tht,fct,assembling,quantity,po,production_time,sample_production) VALUES";
$hi = "INSERT INTO gyartas.termekek (smt,product_name,product_code,norma,quantity,po,production_time,sample_production) VALUES";

// a for ciklus egy lekérdezésbe fűzi össze a feltöltendő termékeket 
for($j = 0; $j < count($termekek);$j++){
    $hi.=" ('$sor_id', ";
    
    for($val = 0; $val < count($termekek[$j]); $val++){
           $hi .= "'".$termekek[$j][$val]."'";
        
        if($val != (count($termekek[$j])-1)){ // itt az egyes termékhez tartozó attributumok kerülnek elválasztásra
            $hi .= ",";
        }
    }
    //$hi .= "'0', '0', '0', '0')";

    $hi .= ")";
    
    if($j != (count($termekek)-1)){ // ezzel a különálló termékek kerülnek elválasztásra
        $hi .= ",";
    }
}
   

if(!mysqli_query($conn,$hi)){
    echo ("$hi upload error - Hiba a feltöltésben.");
    die();
}

echo "Sikeres feltöltés.";
?>
