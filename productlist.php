<?php

$conn = mysqli_connect("localhost","root",""); // kapcsolódás az adatbázishoz
if(!$conn){
    echo 'productlist.php - Sikertelen csatlakozas...';
    die();
}

$query_1 = "SELECT * from gyartas.termekek WHERE smt='".$id."'";

$result = mysqli_query($conn,$query_1);
if(mysqli_num_rows($result)==0){
    echo "<p id='empty_database'>No product data found for this line in the database.</p>";
    echo "<script>$('.szamlalo').hide();</script>";
    die();
}
$er =  '<div id="toPopup" style="display:none;">
        <div class="close"></div>
       	<span class="ecs_tooltip">Press Esc to close <span class="arrow"></span></span>
		<div id="popup_content"> <!--your content start--> 
        <table>
        <tr><th colspan="4" style="color:#fff; background-color:#0367b3; font-size:150%;">LINE '.$id.' - Planned Products</th></tr>
        <tr><th>Product Name</th><th>Product Code</th><th>Required</th><th>Manufacturing Time</th></tr>';
        
$productNameArray = array();
$productCodeArray = array();
$productNormaArray = array();
$productQuantity = array();
$productElkeszulArray = array();
$productNote = array();

while($row = mysqli_fetch_array($result)){
    array_push($productNameArray,$row['product_name']);
    array_push($productCodeArray,$row['product_code']);
    array_push($productNormaArray,$row['norma']);
    array_push($productQuantity,$row['quantity']);
    array_push($productElkeszulArray,$row['elkeszul']);
    array_push($productNote,$row['megjegyzes']);
    
    $er .= "<tr class='product_row'><td>".$row['product_name']."</td><td>".$row['product_code']."</td><td>".$row['quantity']."</td><td>".gmdate("H:i",$row['elkeszul'])."</td></tr>";
    //$er .= "<td>".$row['norma']."</td>";

    //$er .= "<td>".$row['elkeszul']."</td></tr>";
}
$er .= "</table><div id='kovetkezo_termek'></div>";
$er .= '</div> <!--your content end-->
        </div> <!--toPopup end-->
	    <div class="loader"></div>
   	    <div id="backgroundPopup"></div>
        ';
echo $er;

$productCode = $productCodeArray[0];

?>