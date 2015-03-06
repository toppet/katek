<?php

//$conn = mysqli_connect("localhost","root",""); // kapcsolódás az adatbázishoz
$conn = mysqli_connect("10.10.1.205","root",""); // kapcsolódás az adatbázishoz
if(!$conn){
    echo 'productlist.php - Sikertelen csatlakozas...';
    die();
}

if(!isset($_GET['id'])){
    die("missing argument (id)");
    exit();
}

$productNameArray = array();
$productCodeArray = array();
$productNormaArray = array();
$productQuantity = array();
$productElkeszulArray = array();
$SampleProductionArray = array();
$productPOArray = array();
$sample_done = array();

$query_1 = "SELECT * from gyartas.termekek WHERE smt='".$_GET['id']."' AND (production_done IS NULL OR production_done='0') ORDER BY waiting_to_finish";

$result = mysqli_query($conn,$query_1);

if(mysqli_num_rows($result)==0){
    $res['error'] = "No production data.";
    
}else{

$er =  '<div id="toPopup" style="display:none;">
        <div class="close"></div>
       	<span class="ecs_tooltip">Press Esc to close <span class="arrow"></span></span>
		<div id="popup_content"> <!--your content start--> 
        <table>
        <tr><th colspan="5" style="color:#fff; background-color:#0367b3; font-size:150%;">LINE '.$_GET['id'].' - Planned Products</th></tr>
        <tr><th>Product Name</th><th>Product Code</th><th>Required</th><th>Manufacturing Time</th><th>PO</th></tr>';
        
while($row = mysqli_fetch_array($result)){
    array_push($productNameArray,$row['product_name']);
    array_push($productCodeArray,$row['product_code']);
    array_push($productNormaArray,$row['norma']);
    array_push($productQuantity,$row['quantity']);
    array_push($productPOArray,$row['po']);
    array_push($productElkeszulArray,$row['production_time']);
    array_push($SampleProductionArray,$row['sample_production']);
    array_push($sample_done,($row['sample_done']));
    $er .= "<tr class='product_row'><td>".$row['product_name']."</td><td>".$row['product_code']."</td><td>".$row['quantity']."</td><td>".gmdate("H:i",$row['production_time'])."</td><td>".$row['po']."</td></tr>";
    //$er .= "<td>".$row['norma']."</td>";

    //$er .= "<td>".$row['elkeszul']."</td></tr>";
}
               
$er .= "</table><div id='kovetkezo_termek'>";
    
    if(count($productCodeArray)==1){
        $er .= "Next Product: - ";
    }else{
        $er .= "Next Product: ".$productNameArray[1];
    }
        

$er .= "</div>";
$er .= '</div> <!--your content end-->
        </div> <!--toPopup end-->
	    <div class="loader"></div>
   	    <div id="backgroundPopup"></div>';
//echo $er;

$res['productLista'] = $er;
$res['productNameArray'] = $productNameArray;
$res['productNormaArray'] = $productNormaArray;
$res['productCodeArray'] = $productCodeArray;
$res['productQuantity'] = $productQuantity;
$res['productPOArray'] = $productPOArray;
$res['productElkeszulArray'] = $productElkeszulArray;
$res['SampleProductionArray'] = $SampleProductionArray;         
$res['sample_done'] = $sample_done;
$productCode = $productCodeArray[0];
}
// visszaadom a válasz értéket
echo json_encode($res);


?>