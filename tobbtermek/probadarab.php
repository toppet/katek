<?php
ini_set('display_errors', false);
$conn = mysqli_connect("10.180.8.23","guest","GuestPass!","traceability");

//visszadandó eredmény tömb
$arr = array();
session_start();

if(!$conn){

    $resp =  "<p> Cannot connect to database... :( </p><p> Trying to reestablish connection.</p>";
    $arr["errconn"] =  $resp;
    echo json_encode($arr);
    die();

}else{
    date_default_timezone_set('Europe/Budapest');
    ini_set('date.timezone', 'Europe/Budapest');

    $muszak_kezd = date("Y.m.d."." 05:55:55");
    $interval = '5';
    $most = date("Y-m-d H:i:s");
    
    $product_code = $_POST['productCode'];
    
    $productPOArray = explode(",",$_POST['productPOArray']); // a beérkező string átalakítása tömbbé
    
    $po = $_POST['po'];
    $aoi = $_POST['id'];
   
    switch ($aoi){
        case 2:
            $aoi = 2;
            $line =2;
            break;
        case 3:
            $aoi = 3;
            $line = 3;
            break;
        case 1:
            $aoi = 4;
            $line =1;
            break;

        case 4:
            $aoi = 5;
            $line = 4;
            break;
    }
    
    
    /* ----- LEKÉRDEZEM, HOGY MI VOLT A LEGUTÓBB MÓDOSÍTOTT TERMÉK AZ ADOTT SORON ÉS 
    HA AZ KÜLÖNBÖZIK A JELENLEGI TERMÉKTŐL ÉS SZEREPEL A BEVITT TERMÉKEK KÖZÖTT, AKKOR A RENDSZERT ÁTÁLLÍTOM ARRA ----- */

    $recent_ordernr_query = "SELECT 
                               ordernr.orderNr
                            FROM
                                recnrsernr r1
                                    inner join
                                recnrlaststation r2 ON r1.recNr = r2.recNr
                                    inner join
                                products p ON r1.productId = p.productId
                                    inner join
                                recnrordernr ordernr ON ordernr.recNr = r1.recNr
                            where
                                (r2.changeDate between date_sub('".$muszak_kezd."',interval ".$interval." day) and '".$most."') and
                                (r2.lastStation = '".$aoi."070' or r2.lastStation = '".$aoi."071')
                            ORDER BY r2.changeDate desc 
                            LIMIT 1";
    
    $recent_res = mysqli_query($conn,$recent_ordernr_query);
    
    $recent_order_number = mysqli_fetch_array($recent_res);
    
    $eredmeny .= $muszak_kezd . ". ".$recent_order_number['orderNr'];
        
    if(($po != $recent_order_number['orderNr']) && in_array($recent_order_number['orderNr'],$productPOArray)){
        
        // a production order szám meghatározásához szükséges tömbindex
        $array_index = array_search($recent_order_number['orderNr'],$productPOArray);
        $arr['array_index'] = $array_index;
        
        $arr['atallas'] = TRUE;
        $arr['atallas_orderNr'] = $recent_order_number['orderNr'];
        
        echo json_encode($arr);
        die();
        exit();
    }
    
    //include_once("check_last_modified_product.php");
    
    $eredmeny = "<h2 style='background-color:yellow; text-transform:uppercase;'>Production Sample<h2>";
    
    $query2 = "Select 
                    r2.changeDate as 'elkeszult', r1.serNr as 'serNr'
                from
                    recnrsernr r1
                        inner join
                    recnrlaststation r2 ON r2.recNr = r1.recNr
                        inner join
                    recnrordernr ordernr ON ordernr.recNr = r1.recNr
                where
                    (r2.changeDate between date_sub('".$muszak_kezd."', interval ".$interval." day) and '".$most."')
                        and (r2.lastStation = '".$aoi."070' or r2.lastStation = '".$aoi."071')
                        and ordernr.orderNr ='".$po."'
                LIMIT 1";
    
    $nev_query = "Select 
                        p.productName as 'nev', p.productId as 'pid'
                    from
                        products p
                    where
                        p.productCode = '".$product_code."'
                    LIMIT 1";
    
    $nev_resp = mysqli_query($conn,$nev_query);
    $nev = mysqli_fetch_array($nev_resp);
    
    $eredmeny .= "<h1>".$nev['nev']."</h1>";
    $eredmeny .= "<h1 style='margin-top:-50px;'>PO: ".$po."</h1>";    
    
    $res2 = mysqli_query($conn,$query2);
    mysql_free_result($res2); // memória felszabadítás
    $er2 = mysqli_fetch_array($res2);
    $elso_aoi_serNr = $er2['serNr'];
    
    //ha még nincs beolvasva az első darab sem, akkor csak az üres táblázatot jelenítse meg
    if($er2['elkeszult'] == NULL){
        
        $eredmeny .= "<table>";
        $eredmeny .= "<tr><th>beolvasasdatum: </th><td style='width:250px'>-</td></tr>";
        $eredmeny .= "<tr><th>elkeszult: </th><td style='width:250px'>-</td>";
        $eredmeny .= "<tr><th>ido: </th><td style='color:green; width:250px'>-</td></tr>";
        
        $arr['eredmeny']=$eredmeny;
        
        die(json_encode($arr));
        exit();
        
    }else{
        
        $query = "Select 
                    r2.changeDate as 'elso'
                from
                    recnrsernr r1
                        inner join
                    recnrlaststation r2 ON r2.recNr = r1.recNr
                        inner join
                    recnrordernr ordernr ON ordernr.recNr = r1.recNr
                where
                    (r2.changeDate between date_sub('".$muszak_kezd."', interval ".$interval." day) and '".$most."')
                        and (r2.lastStation = '".$line."010' or r2.lastStation = '".$line."011')
                        and ordernr.orderNr ='".$po."'
                LIMIT 1";
        
        $res = mysqli_query($conn,$query);
        mysql_free_result($res);

        $er = mysqli_fetch_array($res);
        
        $sor_eleje = strtotime($er['elso']);
        $aoi_elkeszult = strtotime($er2['elkeszult']);

        $kul = ($aoi_elkeszult-$sor_eleje);
       /* 
        $eredmeny .= "<h2>kul: ". $kul."</h2>"; */
        $eredmeny .= "<table>";
        $eredmeny .=  "<tr><th>beolvasasdatum: </th><td>".$er['elso']."</td></tr>";
        $eredmeny .= "<tr><th>elkeszult: </th><td>".$er2['elkeszult']."</td>";
        $ido = ($er2 == NULL)? "-" : gmdate("H:i:s",$kul);
        $eredmeny .=  "<tr><th>ido: </th><td style='color:green'>".$ido."</td></tr>";

        $eredmeny .= "</table>";
    }
    
// ----- UPDATE PRODUCTION_SAMPLE ----- //
   $conn2 = mysqli_connect("10.10.1.205","root","");
    
   if($aoi_elkeszult != NULL){
       $sample_done_query = "UPDATE gyartas.termekek SET sample_done = '1' WHERE product_code = '".$product_code."' AND po = '".$po."'";
       $sample_done_res = mysqli_query($conn2,$sample_done_query);
       mysql_free_result($sample_done_res); // memória felszabadítás
       
       if(!$sample_done_res){
           $arr["error"] =  'product_sample update error...';
           echo json_encode($arr);
           die();
       }   
   }
// ------ ----- //
    
    if(!empty($aoi_elkeszult)){
        $eredmeny.="<h2 style='color:green'>A próbatermék gyártása befejeződött..</h2>";
        $arr["vege"]="befejezve";
    }

    $arr['eredmeny']=$eredmeny;
    echo json_encode($arr);
}

mysqli_close($conn);
?>
