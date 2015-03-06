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

$leptek = 1; //méretezéshez használt változó
print_r($szelesseg);
// --- GRAFIKON SZÉLESSÉGÉNEK MEGHATÁROZÁSA --- //
for($i=0;$i<count($nev_tomb);$i++){
   /* if($szelesseg[$i]<100 &&$szelesseg[$i]>10){
       
        $tablewidth += ($szelesseg[$i]*10);
        $szelesseg[$i] *= 10;
    }elseif($szelesseg[$i]<10){
        
        $tablewidth += ($szelesseg[$i]*100);
        $szelesseg[$i] *= 100;
    }else{
        $tablewidth += $szelesseg[$i];    
    }*/
    if(count($nev_tomb)==1 && $szelesseg[$i]<100){
        while($szelesseg[$i]<250){
            $szelesseg[$i] *= 10;    
        }
        
        $tablewidth += $szelesseg[$i];
        $leptek = 150;
    }else{
        $tablewidth += $szelesseg[$i];    
    }
    
}
print_r($szelesseg);
$graph="";

// -- PRODUCTION_TIME_ITEM ----

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
$graph .= "</div>";

// -- TERMEKEK -- //

$graph .= "<div class='production_products' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";
for($i=0; $i<count($nev_tomb);$i++){
    $graph .= "<li class='production_item' style='width:".$szelesseg[$i]."px; background-color:#".generateRandomString(6).";'>".$nev_tomb[$i]."<br/>".$formazott_ido_tomb[$i]."</li>";
}
$graph .= "</ul>";
$graph .= "</div>";

// -- MŰSZAK -- //

$hanynap = floor(floor($osszesido/3600)/24); // hány egész nap alatt készül el

$muszak = ["06-14","14-22","22-06"]; // A három 8 órás műszak
$szin = ["#f00","#f35","#f65"];
$szorzo = -80; // -80ról indítom azért hogy az elején ne legyen eltolva 80 px-el jobbra
$muszakdb = 0; // a maradék szélessék meghatározásához szükséges változó
$nap_maradek = (($osszesido/3600)%24); // az egész gyártási idő leoszva 24 órával kiadja, hogy hány*24 óra a gyártás, és a gyátrásból fennmaradó másodperceket

$graph .= "<div class='production_shifts' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";

for($i=0;$i<$hanynap;++$i){

    for($j=0; $j<3; $j++){
        $graph .= "<li class='production_shift_item' style='background-color:".$szin[$j]."; width:80px;'>".$muszak[$j]."</li>";
        $muszakdb++;
    }

    if(($nap_maradek >= 0) && ($i == ($hanynap-1))){
        $maradek_div = ($tablewidth-((80*($muszakdb))));
        $maradek_egesz_muszak = floor($maradek_div/80);
        for($k=0; $k<$maradek_egesz_muszak; $k++){
            $graph .= "<li class='production_shift_item' style='background-color:$szin[$k]; width:80px;'>".$muszak[$k]."</li>";
            $muszakdb++;
        }
        $graph .= "<li class='production_shift_item' style='background-color:".$szin[0]."; width:".round(($tablewidth-((80*($muszakdb)))),2)."px;'>".date("H:i",$ig)."</li>";
    }
}

$graph .= "</ul>";
$graph .= "</div>";

//-- NAPI BONTÁS -- //
$graph .= "<div class='production_days' style='width:".$tablewidth."px;'>";
$graph .= "<ul>";
    for($i=0;$i<$hanynap;$i++){
        if($i==0){
            $graph .= "<li class='production_day_item' style='width:180px; '>".date("m.d.")."</li>";
        }else{
            $graph .= "<li class='production_day_item' style='width:240px; '>".date("m.d.",strtotime('+'.$i.'day'))."</li>";
        }
        if(($nap_maradek >= 0) && ($i == ($hanynap-1))){
            $fennmarado_div = ($tablewidth-(($i*240)+180)); //az első 180px-es nap * 240pxeles napok, levonva az egész div hosszából, kiadja a fennmaradó területet.

            if(($fennmarado_div)>=240){
                
                $meg_hany_napra_oszthato = $fennmarado_div-240;
                $ebbol_marad = $fennmarado_div-240;
            
                $graph .= "<li class='production_day_item' style='width:240px'>".date("m.d.",strtotime('+'.$hanynap.'day'))."</li>";
                $graph .= "<li class='production_day_item' style='width:".$ebbol_marad."px;'>".date("m.d.",strtotime('+'.($hanynap).'day'))."</li>";
                
            }else{
                $graph .= "<li class='production_day_item' style='width:".$fennmarado_div."px;'>".date("m.d.",strtotime('+'.($hanynap).'day'))."</li>";
            }
            
        }
    }
$graph .= "</ul>";
$graph .= "<p>nap_maradek:".$nap_maradek."</p>";
$graph .= "</div>";

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
                if(tablewidth < 300){
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
                }
            });
            </script>
    
    </body>
    </html>