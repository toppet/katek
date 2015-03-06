<?php
ini_set('default_charset','UTF-8');


if(!isset($_GET['id']) || empty($_GET['id'])){
    die("missing argument (id)");
}else{
    $id = $_GET['id'];
}

echo "<h2>Line {$id}</h2>";
echo "<h1 style='margin:0 auto 25px auto; width:100%; text-align:center;'>".date('Y.m. - F')."</h1>";

$conn = mysqli_connect("localhost","root","");

if(!$conn){
    die("Cannot connect to database.");
}

$query = "SELECT * FROM gyartas.termekek WHERE smt='".$_GET['id']."'";

$result = mysqli_query($conn,$query);

$szelesseg = array();
$formazott_ido_tomb = array();
$mp_ido_tomb = array();
$nev_tomb = array();

$osszesido="";
$tablewidth = "";

while($row = mysqli_fetch_array($result)){

$gyartasi_ido = $row['elkeszul'];
$osszesido += $row['elkeszul'];
$ora =floor($gyartasi_ido/3600);
$perc = (($gyartasi_ido/60)%60);
//echo "<h3>Line ".$row['smt'].", ".$row['quantity']." db, ".$row['product_name']." elkészül: ".$ora." óra ".$perc." perc</h3>";

if(floor($gyartasi_ido/3600)>24){
    $nagysag = ((floor($gyartasi_ido/3600)).(($gyartasi_ido/60)%60)/10);
}else{
    $nagysag = ((floor($gyartasi_ido/3600)).(($gyartasi_ido/60)%60)/10);
}
array_push($szelesseg,$nagysag);

$ora = checktime($ora); // átalakítom megfelelő alakúra (00:00)
$perc = checktime($perc); // átalakítom megfelelő alakúra (00:00)

array_push($formazott_ido_tomb, $ora.":".$perc);
array_push($mp_ido_tomb, $row['elkeszul']);
array_push($nev_tomb, $row['product_name']);
}

function checktime($x){
    if($x<10){
        $x="0".$x;
    }
    return $x;
}

$legkisebb = $szelesseg[0]; // alapból a legkisebb érték a tömb első eleme
//print_r($legkisebb);
// a legkisebb szélesség meghatározása, ami alapján kiszélesítem az egész grafikont
for($j=1;$j<count($szelesseg);$j++){
    if($szelesseg[$j]<$legkisebb){
        $legkisebb = $szelesseg[$j];
    }
}
$minimalis_szelesseg = 0; // az alap szélesség meghatározásához használt változó

// --- GRAFIKON SZÉLESSÉGÉNEK MEGHATÁROZÁSA --- //
for($i=0;$i<count($nev_tomb);$i++){
    
    if($legkisebb < 5){

        $minimalis_szelesseg = 0.25;    
        $szelesseg[$i] /= $minimalis_szelesseg;
        
    }elseif($legkisebb <= 20 && $legkisebb >= 5){
        
        $minimalis_szelesseg = round(300/$legkisebb);
        $szelesseg[$i] *= $minimalis_szelesseg; 
        
    }elseif( $legkisebb > 20  && $legkisebb <= 250){
        
        $minimalis_szelesseg = round((300/$legkisebb),2);
        //echo $minimalis_szelesseg;
        $szelesseg[$i] *= $minimalis_szelesseg; 
    }
    
    $tablewidth += $szelesseg[$i];    
}
//print_r($szelesseg);



$graph="";

// -- MŰSZAK -- //

$hanynap = (($osszesido/3600)/24>round(($osszesido/3600)/24)) ? round(($osszesido/3600)/24)+1 : round(round(($osszesido/3600)/24)); // hány egész nap alatt készül el

$nap_maradek = abs((round(($osszesido/3600)/24)-(($osszesido/3600)/24)));
$muszak = ["DE","DU","ÉJ"]; // A három 8 órás műszak
$szin = ["#f00","#f35","#f65"];

$muszakdb = 0; // a maradék szélesség meghatározásához szükséges változó
//$nap_maradek = (($osszesido/3600)%24); // az egész gyártási idő leoszva 24 órával kiadja, hogy hány*24 óra a gyártás, és a gyátrásból fennmaradó másodperceket
$hanymuszak = ($osszesido/3600)/8;

if(round($hanymuszak) == $hanymuszak){
    $hanymuszak = round($hanymuszak);
}elseif($hanymuszak > round($hanymuszak) ){
    $hanymuszak = round($hanymuszak)+1;
}else{
    $hanymuszak = round($hanymuszak);
}
/*echo "<h1>hanynap: ".$hanynap."</h1>";
echo "<h1>hanymuszak: ".$hanymuszak."</h1>";*/
$graph .= "<div class='production_shifts' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";
$j = 0; //műszakindex
for($i=0;$i<$hanymuszak;++$i){
    if($hanymuszak == 1){
        $graph .= "<li class='production_shift_item' style='background-color:".$szin[0]."; width:100%;'>".$muszak[0]."</li>";
    }else{

        if($j==3){ $j=0; } // műszakindex
        if($i==($hanymuszak-1)){
            $graph .= "<li class='production_shift_item' style='background-color:".$szin[$j]."; width:".($tablewidth/$hanymuszak)."px; '>".substr($muszak[$j],0,2)." - ". date("H:i",$ig)."</li>";
        }else{
            $graph .= "<li class='production_shift_item' style='background-color:".$szin[$j]."; width:".($tablewidth/$hanymuszak)."px;'>".$muszak[$j]."</li>";
        }
        $j++;

        /*if(($nap_maradek >= 0) && ($i == ($hanymuszak-1))){
            $maradek_div = ($tablewidth-((80*($muszakdb))));
            $maradek_egesz_muszak = floor($maradek_div/80);

            for($k=0; $k<3; $k++){
                $graph .= "<li class='production_shift_item' style='background-color:$szin[$k]; width:80px;'>".$muszak[$k]."</li>";
                $muszakdb++;
            }
            $graph .= "<li class='production_shift_item' style='background-color:".$szin[0]."; width:".round(($tablewidth-((80*($muszakdb)))),2)."px;'>".date("H:i",$ig)."</li>";
        }*/
    }
}

$graph .= "</ul>";
$graph .= "</div>";


/*// -- PRODUCTION_TIME_ITEM ----

$graph .= "<div class='production_time' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";
$tol = "";
$ig = "";
for($i=0; $i<count($nev_tomb); $i++){
    if($i==0){
        $tol = strtotime(date("Y-m-d 06:00"));
        $ig = $tol + $mp_ido_tomb[0];
        $graph .= "<li class='production_time_item' style='width:".$szelesseg[$i]."px; height:20px;'>".date('m.d. H:i',$tol)." - ". date('m.d. H:i',$ig). "</li>";       
    }else{
        $tol = $ig;
        $ig = $tol + $mp_ido_tomb[$i];
        $graph .= "<li class='production_time_item' style='width:".$szelesseg[$i]."px; height:20px;'>".date("m.d H:i",$tol)." - ".date("m.d H:i",$ig) ."</li>";    
    }
}
$graph .= "</ul>";
$graph .= "</div>";*/


$tol = "";
$ig = "";

// -- TERMEKEK -- //

$graph .= "<div class='production_products' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";
    for($i=0; $i<count($nev_tomb);$i++){
        $graph .= "<li class='production_item' style='width:".$szelesseg[$i]."px; background-color:#".generateRandomString(6).";'>".$nev_tomb[$i]."<br/>".$formazott_ido_tomb[$i]."</li>";
    }
$graph .= "</ul>";
$graph .= "</div>";



//-- NAPI BONTÁS -- //
/*$graph .= "<div class='production_days' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";
$eddigmennyi = 0;
for($i=0;$i<$hanynap;$i++){

    if($hanynap == 1 && $hanymuszak==3){
        $graph .= "<li class='production_day_item' style='width:".(($tablewidth/$hanynap)*0.8)."px;'>".date("m.d.")."</li>";
        if($i==($hanynap-1)){
            $graph .= "<li class='production_day_item' style='width:".($tablewidth-(($tablewidth/$hanynap)*0.8))."px;'>".date("m.d.",strtotime('+'.$hanynap.'day'))."</li>";
        }
    }elseif($hanynap == 1){
        $graph .= "<li class='production_day_item' style='width:".($tablewidth/$hanynap)."px;'>".date("m.d.")."</li>";
    }else{
        
        $napszelesseg = floor($hanymuszak/3);
        $egesz_nap_szelesseg=(round($tablewidth/$hanymuszak)*3); // egy egész napon belüli 3 műszak együttes szélessége

        if($i==0){
            $graph .= "<li class='production_day_item' style='width:".($egesz_nap_szelesseg*0.8)."px; '>".date("m.d.")."</li>";
            $eddigmennyi += ($egesz_nap_szelesseg*0.8);
        }else{
            if($i==($hanynap-1)){
                $graph .= "<li class='production_day_item' style='width:".($tablewidth-($eddigmennyi))."px; '>".date("m.d.",strtotime('+'.$i.'day'))."</li>";
            }else{
                $graph .= "<li class='production_day_item' style='width:".($egesz_nap_szelesseg)."px; '>".date("m.d.",strtotime('+'.$i.'day'))."</li>";
                $eddigmennyi += $egesz_nap_szelesseg;
            }
        }
    }
}
$graph .= "</ul>";
$graph .= "</div>";*/

function generateRandomString($length) {
$characters = '0123456789abcd';
$randomString = '';
for ($i = 0; $i < $length; $i++) {
$randomString .= $characters[rand(0, strlen($characters) - 1)];
}
return $randomString;
}

?>

<html>
<head>
<script src="js/jquery-2.0.3.min.js"></script>

<style>  
.grafikon{

}

/* --- PRODUCTION TIME --- */
.production_time ul{
list-style:none;
margin:0;
padding:0;
text-align:center;
color:#fff;
text-shadow: 0.5px 1px 0 #000;
}
.production_time_item{
background-color:#bbb;
display:inline-block;
margin:0;
padding:0;
}

.production_time_item:nth-child(even){
background-color:#ddd;

}

/* --- PRODUCT NAME --- */
.production_products{
height:75px;
}
.production_products ul{
list-style:none;
margin:0;
padding:0;
text-align:center;
color:#fff;
text-shadow: 1px 1px #000; 
}

.production_item{
display:inline-block;
vertical-align:middle;
height:100%;
}

.production_item:nth-child(even){
background-color:pink;

}

/* --- PRODUCTION SHIFTS --- */
.production_shifts ul{
list-style:none;
margin:0;
padding:0;  
}
.production_shifts ul{
text-align:center;
color:#fff;
text-shadow: 1px 1px #000;
}
.production_shift_item{
display:inline-block;
width:80px;
}

.production_shift_item:nth-child(even){
}
/* --- PRODUCTION DAY --- */
.production_days ul{
list-style:none;
margin:0;
padding:0; 
text-align:center;
color:#fff;
text-shadow: 1px 1px #000;
}

.production_day_item{
display:inline-block;
background-color:#00f;
width:80px;
height:20px;
}

.production_day_item:nth-child(even){
background-color:#0af;
}

</style>
</head>
<body>

<div id='page_wrapper'> 

<div class="grafikon">
<?php echo $graph; ?>
</div>
<hr/>
<p>
<?php 
echo floor($osszesido/3600)." óra ";
echo (($osszesido/60)%60)." perc";
echo ", ami: ".floor(floor($osszesido/3600)/24)." nap és ".(floor($osszesido/3600)%24) ." óra ". ($osszesido/60)%60 ." perc" ;
?>
</p>

</div> <!--//page_wrapper-->

<script>
$(document).ready(function(){
var szelesseg = <?php echo json_encode($szelesseg); ?>;
var ido_tomb = <?php echo json_encode($mp_ido_tomb); ?>;
var nev_tomb = <?php echo json_encode($nev_tomb); ?>;

var osszesido=<?php echo json_encode($osszesido); ?>;
var tablewidth = <?php echo json_encode($tablewidth); ?>;

$('table').css("width",tablewidth+"px");
$('.grafikon').css("width",(tablewidth*1.00625)+"px"); // az egész grafikont körbevevő divnek adok egy kicsit nagyobb szélességes, mint maga a grafikon, így megfelelően széthúzza az oldalt

/*if(tablewidth < 500){
var ossz = 0;
    $('.production_time li').each(function(){
        var c = (parseInt($(this).css("width")))*3;
        ossz += c;
        $(this).css("width",c+"px");
    });
    $('.production_products li').each(function(){
        var c = (parseInt($(this).css("width")))*3;
       
        $(this).css("width",c+"px");
    });
    
    $('.production_shifts li').each(function(){
        var c = (parseInt($(this).css("width")))*3;
        $(this).css("width",c+"px");
    });
    
    $('.production_days li').each(function(){
        var c = (parseInt($(this).css("width")))*3;
        $(this).css("width",c+"px");
    });

    $('.grafikon').css("width",(ossz*1.00625)+"px");
    $('.production_time').css("width",ossz+"px");
    $('.production_products').css("width",ossz+"px");
    $('.production_shifts').css("width",ossz+"px");
    $('.production_days').css("width",ossz+"px");

    //$('.production_products li').css("width",(1000/3)+"px");
}*/
    
    /*var merettomb = [];
    
    $('.production_products li').each(function(){
        merettomb.push(parseInt($(this).css("width")));
    });
    
    var legkisebb = merettomb[0];
    for(var i=1;i<merettomb.length;i++){
        if(merettomb[i]<legkisebb){
            legkisebb = merettomb[i];
        }
    }
    alert(legkisebb);*/
});
</script>

</body>
</html>