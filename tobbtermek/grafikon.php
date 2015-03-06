<?php
ini_set('default_charset','UTF-8');

if(!isset($_GET['id']) || empty($_GET['id'])){
    die("missing argument (id)");
}else{
    $id = $_GET['id'];
}

echo "<h2 style='margin:0 auto 25px auto; width:100%; text-align:center;'>".date('Y.m. - F')."</h2>";

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
$db_tomb = array();
$kod_tomb = array();
$po_tomb = array();
$norma_tomb = array();
$osszesido="";
$tablewidth="";


while($row = mysqli_fetch_array($result)){

    $gyartasi_ido = $row['production_time'];
    $osszesido += $row['production_time'];
    $ora = floor($gyartasi_ido/3600);
    $perc = (($gyartasi_ido/60)%60);

    if(floor($gyartasi_ido/3600)>24){
        $nagysag = ((floor($gyartasi_ido/3600)).(($gyartasi_ido/60)%60));
    }else{
        $string_perc  = ($perc==0 || $perc<10 )?"0{$perc}":$perc;
        $nagysag = ((floor($gyartasi_ido/3600)).$string_perc);
    }
    array_push($szelesseg,$nagysag);

    $ora = checktime($ora); // átalakítom megfelelő alakra (00:00)
    $perc = checktime($perc); // átalakítom megfelelő alakra (00:00)

    array_push($formazott_ido_tomb, $ora.":".$perc);
    array_push($mp_ido_tomb, $row['production_time']);
    array_push($nev_tomb, $row['product_name']);
    array_push($db_tomb, $row['quantity']);
    array_push($kod_tomb, $row['product_code']);
    array_push($po_tomb, $row['po']);
    array_push($norma_tomb, $row['norma']);   
}

function checktime($x){
    if($x<10){
        $x="0".$x;
    }
    return $x;
}
$osszes_szelesseg = 0;

for($j=0;$j<count($szelesseg);$j++){
    $osszes_szelesseg += $szelesseg[$j];  
}



// -- REAL-TIME ADATOK LEKÉRDEZÉSE AZ ÉPPEN GYÁRTOTT TERMÉKHEZ

$conn2 = mysqli_connect("10.180.8.23","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz
if(!$conn2){
    die("Traceability csatlakozási hiba!");
}
$aoi = 4;
$product_code = $kod_tomb[0];
$realtime_query = "SELECT 
            count(distinct r2.recNr) as 'db', MIN(r2.changeDate) as 'min'
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

$realtime_result = mysqli_query($conn2,$realtime_query);
$realtime_row = mysqli_fetch_array($realtime_result);

$kesz_db = intval($realtime_row['db']);

echo "<h1>Elkészülési idő alapból: ".$mp_ido_tomb[0]."</h1>";
echo "<h1>Elkészült db: ".intval($kesz_db)."</h1>";
echo "<h1>Maradék db: ".($db_tomb[0]-$kesz_db)."</h1>";
$maradek_elkeszul_mp = ((($db_tomb[0]-$kesz_db)/$norma_tomb[0])*28800);
echo "<h1>Maradék elkészül:".$maradek_elkeszul_mp." mp</h1>";

$most = strtotime(date("Y-m-d H:i"));

$f = strtotime($realtime_row['min']); //első db elkészült
$e = strtotime(date("Y-m-d H:i",$mp_ido_tomb[0])); //kiszámolt elkészülési idő

$maradek_mikorrakeszul_el = ($most + $maradek_elkeszul_mp);

// $vége = hány másodperc kell összesen az elkészüléshez, az első elkészült darab idejétől kezdve, a hátralévő darabszám elkészülési idejévét beszámítva

$vege = ($f=="")?0:abs(($f+$e)-$maradek_mikorrakeszul_el); //ha nem készült még el egy db sem, akkor 
echo "<p>vége:".$vege."</p>";
echo "<p>f:".$f."</p>";
echo "<p>e:".$e."</p>";
echo "<h3>Az kiszámolt elkészülési idő és a maradék db elkészülési idejének különbsége: ".$vege." mp</h3>";
echo "<h3>összidő: ".abs($mp_ido_tomb[0]+$vege)." mp</h3>";
//$szelesseg[0] = ((floor(($mp_ido_tomb[0]+$maradek_elkeszul_mp)/3600)).((($mp_ido_tomb[0]+$maradek_elkeszul_mp)/60)%60));
$szelesseg[0] = ((floor(abs($vege+$mp_ido_tomb[0])/3600)).((abs($vege+$mp_ido_tomb[0])/60)%60));
$mp_ido_tomb[0] = abs($mp_ido_tomb[0]+$vege);
?>

<html>
<head>
<script src="js/jquery-2.0.3.min.js"></script>

<style>  
    body{
        font-family: 'Levenim MT';
        font-size:1em;
        color:#000;
        font-weight:bold;
    }
    
    .line_num{
        /*display:absolute;*/
        transform:rotate(-90deg);
        font-weight:bold;
        font-size:15px;
        float:left;
        text-align:justify;
        width:2.5%;
    }

    #graph_table{
        color:#000;
        border-collapse:collapse;
        text-align:center;
        width:100%;
    }
   #production_shift{
       font-weight:bold;
   }

/* -- Termékek -- */
    #product_table_container{
        position:absolute;
    }
    #product_table_container,#production_line_info_containter{
        padding:0;
        margin:0;
    }
    
    #product_table{
        border-collapse:collapse;
        padding:0;
        margin:0;
        height:35px;
    }
    
    .production_item{
        font-size:15px;
        color:#fff;
        font-weight:bold;
        padding:0;
        margin:0;
        text-align:center;
    }

    .szunet{
        background-color:#fe4;
        padding:0;
        margin:0;
    }
    
    
    .production_line_info_containter{
        margin:100px 0;
        padding:33px 0;
    }
    .production_line_info_table{
        border-collapse:collapse;
        text-align:center;
        width:100%;
        padding:0;
        margin:0;
        display:block;
        vertical-align:top;
    }
    
    .production_line_info_table td,th{
        border:1px solid #000;
    }
</style>
</head>
<body>

    <div id='page_wrapper'> 
        
        <div class="grafikon">
        </div>
        <hr/>
        <p>
        <?php 
            echo floor($osszesido/3600)." óra ";
            echo (($osszesido/60)%60)." perc";
            echo ", ami: ".floor(floor($osszesido/3600)/24)." nap és ".(floor($osszesido/3600)%24) ." óra ". ($osszesido/60)%60 ." perc" ;
        ?>
        </p>
        <div class='line_num'></div>
    </div> <!--//page_wrapper-->

<script>
$(document).ready(function(){
    var szelesseg = <?php echo json_encode($szelesseg); ?>;
    var eredeti_szelesseg = []; //az alapból kiszámolt szélességek az adott műszakra eső darabszámok kiszámításához szükséges
    var ido_tomb = <?php echo json_encode($mp_ido_tomb); ?>;
    var nev_tomb = <?php echo json_encode($nev_tomb); ?>;
    var norma_tomb = <?php echo json_encode($norma_tomb); ?>;
    var db_tomb = <?php echo json_encode($db_tomb); ?>;
    var po_tomb = <?php echo json_encode($po_tomb); ?>;
    var id = <?php echo json_encode($id); ?>;
    
    var osszesido=<?php echo json_encode($osszesido); ?>;
    var tablewidth = <?php echo json_encode($tablewidth); ?>;
    var muszak = ["DE","DU","ÉJ","DE"];

    var graph = "<table id='graph_table'>";
   
    graph += "<tr id='production_shift'>";
    graph += "<td class='line_num' rowspan='3'>Line "+id+"</td>";
    
/* ----- MŰSZAKOK -----*/
    var date = new Date();
    var honap = date.getMonth()+1;
    var nap = date.getDate();
    var negy_muszak_szelesseg = ($(".grafikon").width()-$(".line_num").width());
    var negy_muszak_aranya = (negy_muszak_szelesseg/$(".grafikon").width())*100;
    
    for(var i = 0; i < 4; i++){
        if(i==2){
            nap += 1;
        }
        
        if(i%2){
            graph += "<td class='production_shift_item' style='background-color:#999; width:"+(negy_muszak_aranya/4)+"%; '>"+honap+"."+nap+". - "+muszak[i]+"</td>";
        }else{
            graph += "<td class='production_shift_item' style='background-color:#bbb; width:"+(negy_muszak_aranya/4)+"%; '>"+honap+"."+nap+". - "+muszak[i]+"</td>";
        }
    }
    graph+= "</tr>";
    $('.grafikon').html(graph);
    
/* -----  TERMÉK GYÁRTÁSÁNAK HOSSZA GRAFIKUS FORMÁBAN-----*/
    graph += "<tr><td colspan='4' id='product_table_container'><table id='product_table'>";
   var mennyilenne = 0; //az adott táblázat, termékek grafikus részét tartalmazó sorának hossza
    var szunet = 0;
    var muszak_szelesseg = (parseInt($(".production_shift_item:eq(0)").css("width"))/8);
   
   for(var i= 0; i < nev_tomb.length; i++){
       if(i==0 || nev_tomb.length == 1){
           szelesseg[i] = parseFloat((parseFloat(szelesseg[i])/100)*muszak_szelesseg);      
       }else{
           szelesseg[i] = parseFloat((parseFloat(szelesseg[i])/100)*muszak_szelesseg); // szünettel együtt
           szunet++;
       }
       eredeti_szelesseg[i] = szelesseg[i];
       mennyilenne += szelesseg[i];
      
   }
    
    var szunet_width = Math.round(parseInt($(".production_shift_item").width())/16); // a fél órányi átállási idő szélessége
    
    mennyilenne += (szunet*szunet_width);

    $('.grafikon').html(graph); // kiiratom
    
    for(var i=0; i< nev_tomb.length; i++){
        
        if(i==0 || nev_tomb.length == 1){
            graph += "<td class='production_item' style=' width:"+szelesseg[i]+"px;'>"+nev_tomb[i]+"</td>";

        }else{ // ha nem az első és nem is az utolsó elem akkor teszünk a termék után egy 30 perces szünetnek megfelelő elemet
            graph += "<td class='szunet' style='width:"+szunet_width+"px; text-align:center;'>&#8594;</td>";
            graph += "<td class='production_item' style=' width:"+szelesseg[i]+"px;'>"+nev_tomb[i]+"</td>";
        }
    }
    graph += "</table><td></tr>";
    
    var muszakok={
        "muszak_0":{
        },
        "muszak_1":{
        },
        "muszak_2":{
        },
        "muszak_3":{
        }
    };
    
    /*var muszakhossz = parseInt($('.production_shift_item').css("width")); // egy műszak hossz INT-ben
    
    var elozo_szunet= 0;
    
    for(var muszak_index = 0; muszak_index < 3; muszak_index++){
        var osszmuszakhossz = 0;
        var muszak_maradek = 0;
        var hany = 0;
        
        for( var termek_index = 0; termek_index < nev_tomb.length; termek_index++){
            
            muszakok["muszak_"+muszak_index]["termek"+termek_index] = [];
            var ido = szelesseg[termek_index];
            //alert(ido);
            //alert("muszak: "+(muszak_index+1)+", termek: "+nev_tomb[termek_index]+",db: "+db_tomb[termek_index]+" szelesseg: "+eredeti_szelesseg[termek_index]);
            //alert("muszak: "+(muszak_index+1)+", termek: "+nev_tomb[termek_index]+",osszmuszakhossz: "+osszmuszakhossz);
            if(elozo_szunet != 0){
                osszmuszakhossz += elozo_szunet;
                elozo_szunet = 0;
                //alert("muszak"+muszak_index+",termek"+nev_tomb[termek_index]+"osszmuszakhossz"+osszmuszakhossz);
            }
               if(ido < muszakhossz){
                   
                    if(termek_index == 0){
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = ido;
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        osszmuszakhossz += (ido+30);
                        if(osszmuszakhossz > muszakhossz){
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                            
                            hany++;
                            elozo_szunet = osszmuszakhossz-muszakhossz;
                            break;
                        }
                        hany++;
                        
                    }else{
                        if((osszmuszakhossz + ido) < muszakhossz){
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                            //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = ido;
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                            osszmuszakhossz += (ido+30);
                            if(osszmuszakhossz > muszakhossz){
                                hany++;
                                elozo_szunet = osszmuszakhossz-muszakhossz;
                                break;
                            }
                            hany++;
                        }else{
                            var kul = muszakhossz - osszmuszakhossz;
                            var maradek = ido - kul;
                            
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                            //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = kul; // levonjuk a szünetet
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((kul/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                            ido = (ido-kul);
                            szelesseg[termek_index] = ido; // levonom a szünetet
                            osszmuszakhossz = muszakhossz;
                          
                            if(osszmuszakhossz == muszakhossz){

                                break;
                            }
                        } 
                    }
                }else if(ido>muszakhossz){
                    if(termek_index == 0){
                        
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = (muszakhossz-osszmuszakhossz);
                         muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round(((muszakhossz-osszmuszakhossz)/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                        
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        szelesseg[termek_index] -= muszakhossz;
                        osszmuszakhossz = muszakhossz;
                        
                        if(osszmuszakhossz = muszakhossz){
                            break;
                        }
                    }else{
                      
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = (muszakhossz-osszmuszakhossz);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round(((muszakhossz-osszmuszakhossz)/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        szelesseg[termek_index] = ido - (muszakhossz - osszmuszakhossz);
                        break;
                    }
                }else{
                  // alert("muszak:"+muszak_index + ", termek: "+nev_tomb[termek_index]);
                    if(termek_index == 0){
                        if((osszmuszakhossz + ido) < muszakhossz){
                            
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                            //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = ido;
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                            osszmuszakhossz += (ido+szunet_width);
                            if(osszmuszakhossz > muszakhossz){
                                elozo_szunet = szunet_width;
                                break;
                            }
                        }else if((osszmuszakhossz+ido) > muszakhossz){
                            
                            var kul = muszakhossz - osszmuszakhossz;
                            var maradek = ido - kul;
                            
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                            //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = kul; // levonjuk a szünetet
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((kul/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                            ido = maradek;
                            szelesseg[termek_index] = ido; 
                            osszmuszakhossz += (ido+szunet_width);
                            break;

                        }else{
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                            
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                            muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                            osszmuszakhossz = muszakhossz;
                            elozo_szunet = szunet_width;
                            
                            hany++;
                            break;
                        }
                    }else{
                        var kul = muszakhossz - osszmuszakhossz;
                        var maradek = ido - kul;
                        
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        //muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = kul; // levonjuk a szünetet
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((kul/eredeti_szelesseg[termek_index])*db_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        ido = maradek;
                        szelesseg[termek_index] = ido; 
                        osszmuszakhossz = muszakhossz;
                        
                        break;
                    }
                }
        }
        
        for(var i= 0; i < hany; i++){
            nev_tomb.shift();
            szelesseg.shift();
            eredeti_szelesseg.shift();
            db_tomb.shift();
            po_tomb.shift();
        }
    }*/
    
    var muszakhossz = 28800; // egy műszak hossz INT-ben

    var elozo_szunet= 0;

    for(var muszak_index = 0; muszak_index < 4; muszak_index++){
        var osszmuszakhossz = 0;
        var muszak_maradek = 0;
        var hany = 0;

        for( var termek_index = 0; termek_index < nev_tomb.length; termek_index++){

            muszakok["muszak_"+muszak_index]["termek"+termek_index] = [];
            var ido = parseFloat(ido_tomb[termek_index]);
            
            //alert("muszak: "+(muszak_index+1)+", termek: "+nev_tomb[termek_index]+",db: "+db_tomb[termek_index]+" szelesseg: "+eredeti_szelesseg[termek_index]);
            //alert("muszak: "+(muszak_index+1)+", termek: "+nev_tomb[termek_index]+",osszmuszakhossz: "+osszmuszakhossz);
            if(elozo_szunet != 0){
                osszmuszakhossz += elozo_szunet;
                elozo_szunet = 0;
                alert(osszmuszakhossz);
                //alert("muszak"+muszak_index+",termek"+nev_tomb[termek_index]+"osszmuszakhossz"+osszmuszakhossz);
            }
            if(ido < muszakhossz){

                if(termek_index == 0){
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/muszakhossz)*norma_tomb[termek_index]);
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                    osszmuszakhossz += (ido+1800);
                    if(osszmuszakhossz > muszakhossz){
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];

                        hany++;
                        elozo_szunet = osszmuszakhossz-muszakhossz;
                        break;
                    }
                    hany++;

                }else{
                    if((osszmuszakhossz + ido) < muszakhossz){
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/muszakhossz)*norma_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        osszmuszakhossz += (ido+1800);
                        if(osszmuszakhossz > muszakhossz){
                            hany++;
                            elozo_szunet = osszmuszakhossz-muszakhossz;
                            break;
                        }
                        hany++;
                    }else{
                        var kul = muszakhossz - osszmuszakhossz;
                        
                        var maradek = ido - kul;

                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((kul/muszakhossz)*norma_tomb[termek_index]);
                        
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        
                        ido_tomb[termek_index] = maradek;
                        osszmuszakhossz = muszakhossz;
                        break;
                        
                    } 
                }
            }else if(ido>muszakhossz){
                if(termek_index == 0){

                    muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round(((muszakhossz-osszmuszakhossz)/muszakhossz)*norma_tomb[termek_index]);
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                    ido_tomb[termek_index] -= muszakhossz;
                    osszmuszakhossz = muszakhossz;

                    if(osszmuszakhossz = muszakhossz){
                        break;
                    }
                }else{

                    muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round(((muszakhossz-osszmuszakhossz)/muszakhossz)*norma_tomb[termek_index]);
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                    ido_tomb[termek_index] = ido - (muszakhossz - osszmuszakhossz);
                    break;
                }
            }else{
                // alert("muszak:"+muszak_index + ", termek: "+nev_tomb[termek_index]);
                if(termek_index == 0){
                    if((osszmuszakhossz + ido) < muszakhossz){

                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/muszakhossz)*norma_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        osszmuszakhossz += (ido+1800);
                        if(osszmuszakhossz > muszakhossz){
                            elozo_szunet = 1800;
                            break;
                        }
                    }else if((osszmuszakhossz+ido) > muszakhossz){

                        var kul = muszakhossz - osszmuszakhossz;
                        var maradek = ido - kul;

                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((kul/muszakhossz)*norma_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                        ido = maradek;
                        ido_tomb[termek_index] = ido; 
                        osszmuszakhossz += (ido+1800);
                        break;

                    }else{
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];

                        muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((ido/muszakhossz)*norma_tomb[termek_index]);
                        muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];

                        elozo_szunet = 1800;

                        hany++;
                        break;
                    }
                }else{
                    var kul = muszakhossz - osszmuszakhossz;
                    var maradek = ido - kul;

                    muszakok['muszak_'+muszak_index]['termek'+termek_index][0] = nev_tomb[termek_index];
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][1] = Math.round((kul/muszakhossz)*norma_tomb[termek_index]);
                    muszakok['muszak_'+muszak_index]['termek'+termek_index][2] = po_tomb[termek_index];
                    
                    osszmuszakhossz = muszakhossz;

                    break;
                }
            }
        }

        for(var i= 0; i < hany; i++){
            nev_tomb.shift();
            szelesseg.shift();
            eredeti_szelesseg.shift();
            ido_tomb.shift();
            norma_tomb.shift();
            db_tomb.shift();
            po_tomb.shift();
        }
    }
        
        
    
    graph += "<tr>";
    for(var muszak_index = 0; muszak_index < 4; muszak_index++){
        graph += "<td class='production_line_info_containter' valign='top'>";
        graph += "<table class='production_line_info_table'><tr><th>Típus</th><th>DB</th><th>PO</th></tr>";
        
        for( var termek_index = 0; termek_index < Object.keys(muszakok["muszak_"+muszak_index]).length; termek_index++){

            var nev = muszakok["muszak_"+muszak_index]['termek'+termek_index][0];
            var ido = muszakok["muszak_"+muszak_index]['termek'+termek_index][1];
            var po = muszakok["muszak_"+muszak_index]['termek'+termek_index][2];
            graph += "<tr><td>"+nev+"</td>";
            graph += "<td>"+ido+"</td>";
            graph += "<td>"+po+"</td>";
            graph += "</tr>";
        }
        
        graph += "</table>";
        graph += "</td>";
    }
    graph += "</tr>";
    graph += "</table>";
    
    
    $('.grafikon').html(graph);
   
    $('.production_item:odd').css('background-color','navy');
    $('.production_item:even').css('background-color','sandybrown');
    $('.production_item:first').css('background-color','forestgreen');
    var kepernyoszelesseg = parseInt($(".grafikon").css("width"));
    
    $('#product_table_container').css('width',((mennyilenne/kepernyoszelesseg)*100).toFixed(5)+"%");
    $('#product_table').css('width',"100%");
    
    var production_table_width = parseFloat($("#product_table").css("width"));
    
    $('.production_item').each(function(){
        var elem_width = parseFloat($(this).css("width"));
        
        $(this).css("width",((elem_width/production_table_width)*100)+"%");
    });
    
    
    $(".szunet").css("width",(szunet_width/production_table_width)*100+"%");
    
    
});
</script>

</body>
</html>