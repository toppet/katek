<?php
ini_set('default-charset','UTF-8');
setlocale(LC_ALL, 'en_EN');

session_start();

$conn = mysqli_connect("10.10.1.1","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz

if(!$conn){
  die('Cannot connect to database... :(');
    exit();
}
$muszak_kezd = date("Y-m-d"." 05:55:55");
$most = date("Y-m-d H:i");
echo "<p>". $most ."</p>";

$productCode = "13602512-01BF";
$aoi = 2;


/* ---------------------------------------- */



$productName = "";

if(!isset($_SESSION['pid']) || !isset($_SESSION['productName'])){

    $query_pid = "SELECT productId as 'pid',productName as 'name' FROM products WHERE productCode = '".$productCode."' LIMIT 1";

    $pid_res = mysqli_query($conn,$query_pid);
    $pid_row = mysqli_fetch_array($pid_res);

    
    if(!$pid_res){
        die("nemjó");
    }

    $_SESSION['pid'] = (int) $pid_row['pid'];
    $_SESSION['productName'] =  $pid_row['name'];
    
    mysqli_free_result($pid_res);
}

//session_destroy();
//echo $pid;

$pid = $_SESSION['pid'];
$productName = $_SESSION['productName'];

echo '<h1>name: '.$productName.' / pid: '.$pid.'</h1>';

/* ------------ */

$query = " SELECT 
   r2.recNr
FROM
    recnrsernr r1
        inner join
    recnrlaststation r2 ON r1.recNr = r2.recNr
        inner join
    products p ON r1.productId = p.productId
where
    (r2.changeDate between '$muszak_kezd' and '$most')
        and (r2.lastStation = '".$aoi."070'
        or r2.lastStation = '".$aoi."071')
        and (p.productId = '$pid')
GROUP BY r2.recNr";

$result = mysqli_query($conn,$query);
$row = mysqli_fetch_array($result);
$sorok_szama = mysqli_num_rows($result);
echo "<p>elkeszult db: ".$sorok_szama." db</p>";

mysqli_free_result($result);
/* ---------------- */

$query_first = " SELECT 
                        r2.changeDate as 'first'
                    FROM
                        recnrsernr r1
                            inner join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            inner join
                        products p ON r1.productId = p.productId
                    where
                        (r2.changeDate between '".$muszak_kezd."' and '$most')
                            and (r2.lastStation = '".$aoi."070'
                            and p.productId = '$pid')
                    ORDER BY r2.changeDate
                    limit 1";

if(!isset($_SESSION['first'])){
    $result = mysqli_query($conn,$query_first);
    $row = mysqli_fetch_array($result);
    mysqli_free_result($result);
    if($row['first'] == NULL){
        $_SESSION['first'] = "-";  
    }else{
        $_SESSION['first'] = $row['first'];      
    }
}

echo "<p>elso elkeszult: ".($_SESSION['first'])."</p>";

/* -------------------- */

$query_last = " SELECT 
    r2.changeDate as 'last'
FROM
    recnrsernr r1
        inner join
    recnrlaststation r2 ON r1.recNr = r2.recNr
        inner join
    products p ON r1.productId = p.productId
where
    (r2.changeDate between '".$muszak_kezd."' and '$most')
        and (r2.lastStation = '".$aoi."070'
        and p.productId = '$pid')
ORDER BY r2.changeDate DESC
limit 1";


$result = mysqli_query($conn,$query_last);
$row = mysqli_fetch_array($result);
mysqli_free_result($result);

if($row['last'] == NULL){
    echo "<p>utolso elkeszult: - </p>";
}else{
    echo "<p>utolso elkeszult: ".($row['last'])."</p>";
}


/* ------------------- */

$query_hibas = "SELECT 
    count(f.failureCode) as 'hibas'
FROM
    recnrsernr r1
        inner join
    recnrlaststation r2 ON r1.recNr = r2.recNr
        inner join
    products p ON r1.productId = p.productId
        inner join
    failures f ON failureCode = r2.procState
WHERE
    (r2.changeDate between '".$muszak_kezd."' and '".$most."')
        AND (r2.lastStation = '".$aoi."070'
        OR r2.lastStation = '".$aoi."071') AND (p.productId = '$pid')
LIMIT 1";

$hibas_db = 0;
$hibas_res = mysqli_query($conn,$query_hibas);
if(!$hibas_res){
    die("hibas_res nemjó");
}
$row = mysqli_fetch_array($hibas_res);
    
$hibas_db = $row['hibas'];

echo "<p>hibas: ".$hibas_db." db</p>";
mysqli_free_result($hibas_res);
mysqli_close($conn);
?>