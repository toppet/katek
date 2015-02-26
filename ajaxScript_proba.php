<?php
ini_set('display_errors', false);
$conn = mysqli_connect("10.10.1.1","guest","GuestPass!","traceability"); // kapcoslódás az adatbázishoz

$responseText = "";
$arr = array();

if(!$conn){
$responseText .= 'Cannot connect to database... :( ';
$responseText .= 'Trying to reestablish connection!';

$arr['error'] = $responseText;
echo json_encode($arr);

}else{
date_default_timezone_set('Europe/Budapest');
ini_set('date.timezone', 'Europe/Budapest');        

$datum = date("Y.m.d.");
$product_code = $_POST['productCode'];
$db = $_POST['givenValue'];

$norma = $_POST['norma'];
$elkeszul = $_POST['elkeszul'];
$emailElkuldve;

//$posted_name = $_POST['product_name'];

$aoi;
   
switch ($_POST['id']){
case 1:
$aoi = 4;
$line = 1;
break;
case 2:
$aoi = 2;
$linee =2;
break;
case 3:
$aoi = 3;
$line = 3;
break;
case 4:
$aoi = 5;
$line = 4;
break;
}
    
$query = "SELECT 
count(distinct r2.recNr) as 'db', p.productName as 'nev', MIN(r2.changeDate) as 'min', MAX(r2.changeDate) as 'max'
FROM
recnrsernr r1
join
recnrlaststation r2 ON r1.recNr = r2.recNr
join
products p ON r1.productId = p.productId
where
r2.changeDate > concat(curdate(), ' 05:55:55')
and (r2.lastStation like '".$aoi."07%'
and not r2.lastStation = '".$aoi."072') and p.productCode = '".$product_code."'";

$query_hibas = "SELECT 
count(failureCode) as 'hibas'
FROM
recnrsernr r1
join
recnrlaststation r2 ON r1.recNr = r2.recNr
join
products p ON r1.productId = p.productId
join
failures f ON failureCode = r2.procState
where
r2.changeDate > concat(curdate(), ' 05:55:55')
and (r2.lastStation like '".$aoi."07%'
and not r2.lastStation = '".$aoi."072') and p.productCode = '".$product_code."'";
    
$res = mysqli_query($conn,$query);

$row = mysqli_fetch_array($res);
$product_name = $row['nev']; // A gyártott termék neve

$elkeszultDB = $row['db']; // adatbázis alapján visszadott elkészült darabszám

$f = strtotime($row['min']); //első db elkészült
$l = strtotime($row['max']); // utolsó darab elkeszült

$e = strtotime(date("Y-m-d H:i",$elkeszul)); // az megadott darabszám elkészülési ideje
$varhato = strtotime(date("Y-m-d H:i",$f+$e)); //formázott várható befejezési idő

$hiba = mysqli_query($conn,$query_hibas);
$hibasdb_tomb = mysqli_fetch_array($hiba);
$hibasdb = $hibasdb_tomb['hibas'];

$responseText .= "<h1><span id='smt_line'>SMT LINE ".$line."</span> - ".$product_name."<br/><span style='font-size:50%; color:#bbb'>(".$product_code.")</span></h1>"; 

$maradek = ($db - $elkeszultDB > 0) ? ($db - $elkeszultDB) : '0';
$maradek_elkeszul = 27000 / 2400 * $maradek;
$most = strtotime(date('Y-m-d H:i'));

$maradek_ido = gmdate("H:i",$maradek_elkeszul);
$maradek_konv = date("Y-m-d H:i",$most+$maradek_elkeszul);
    
$responseText .= "<table id='data_table'><tr><th id='required'>Required</th><th id='manufactured'>MANUFACTURED</th><th id='faulty'>FAIL</th><th id='remaining'>remaining (pcs)</th></tr>";
$responseText .= "<tr><td>".$db."</td><td>".$row['db']."</td><td>".$hibasdb."</td><td>".$maradek."</td></tr>";            
$responseText .= "</table>";

$responseText .= "<table id='varhato_veg'><tr><th>Expected End Time: </th></tr>";
$responseText .= "<tr><td style='font-weight: bold;'>".date("Y-m-d H:i",$varhato)."</td></tr>";
$responseText .= "</table>";

$kovetkezo = date("Y-m-d H:i",strtotime("-30 minutes",$f+$e));

//$responseText .= "<p>Következő betöltése: <br/>".$kovetkezo."</p>"; // következő termék betöltésének kiírása

// ellenőrzés, hogy az email el lett-e küldve
$conn2 = mysqli_connect("localhost","root","");

$email_query = "SELECT * FROM gyartas.termekek WHERE product_code='$product_code'";
$ress = mysqli_query($conn2,$email_query);
$row = mysqli_fetch_array($ress);

$emailElkuldve =  $row['email'];

if(strtotime(date("Y-m-d H:i")) >= strtotime($kovetkezo) && $emailElkuldve == 0){
    
//elküldjük a figyelmeztető emailt
require_once("email/email.php");
// az adatbázisban frissítem, hogy elküldtük az email-t és beálíltom az $emailElkuldve változót 1-re
require_once("update_email_status.php");
}

//mysql_close($conn2);

//késés
$keses = ($most>$varhato && $l > $varhato)?gmdate("H:i",$l-$varhato):"-";

$responseText .= "<h1 id='delay'>Delay: ".$keses."</h1>";
$responseText .= "<hr/>";

if($elkeszultDB >= $db){
$responseText .= "<h3 class='done'style='color:green'>A megadott darabszám elkészült!</h3>";

$last_query = "SELECT 
p.productName as 'nev', r2.changeDate
FROM
recnrsernr r1
join
recnrlaststation r2 ON r1.recNr = r2.recNr
join
products p ON r1.productId = p.productId
where
r2.changeDate > concat(curdate(), ' 05:55:55')
and (r2.lastStation like '".$aoi."07%'
and not r2.lastStation = '".$aoi."072')
and p.productCode = '".$product_code."'
limit ".$db." , 1";

$last_result = mysqli_query($conn,$last_query);
$last_product_row = mysqli_fetch_array($last_result);

$arr['tenyleges'] = $last_product_row['changeDate'];
$arr['kesz'] = TRUE;
}

$hibatlandb = $elkeszultDB-$hibasdb;

// ----- DIAGRAMM SCRIPT ----- //
$hibasArany = round(($hibasdb/$elkeszultDB)*100,3);
$hibatlanArany = round((($elkeszultDB-$hibasdb)/$elkeszultDB)*100,2);

$responseText .= '<script>
$(function () {
var dataSource = [
{ product: "Pass: '.$hibatlandb.'", percentage: '.$hibatlanArany.' },
{ product: "Fail: '.$hibasdb.'", percentage: '.$hibasArany.' }
];

$("#chartContainer").dxPieChart({

dataSource: dataSource,
series: 
{
argumentField: "product",
valueField: "percentage",
label: {
visible: true,
connector: {
visible: true,
width: 1
},
font: {
color:"white",
size: 50
},
customizeText: function(){
return this.value + " %";
}
} // label
}, // series
palette: ["green","red"],
legend: {
horizontalAlignment: "center",
verticalAlignment: "top",
font: {
color:"#555",
size: 25
}
} // legend
}); //piechart
});
</script>	
<div id="chartContainer"></div>';

// ADATOK KIÍRÁSA FÁJLBA!
if($elkeszultDB >= $db){
include_once('ExcelWriter/kiir.php'); 
}

$arr['emailElkuldve'] = $emailElkuldve;

$arr['responseText'] = $responseText;
echo json_encode($arr);

}


?>