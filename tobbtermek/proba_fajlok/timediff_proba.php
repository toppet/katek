<?php
date_default_timezone_set('Europe/Budapest');
ini_set('date.timezone', 'Europe/Budapest');
header('Content-Type: text/html; charset=utf-8');

$conn = mysqli_connect("10.180.8.23","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz

/* ---- Ha 10 percnél tovább van jelen a query_progress.txt, akkor ott valószínűleg valami hiba van, ezért töröljük ---- */

$cache_file = 'query_progress.txt';
$cache_time = 600; // 1 hour = 3600 (sec)

if(file_exists($cache_file) && time() - $cache_time < filemtime($cache_file)){
    unlink($checking_file);   
}

$responseText = "";

if(!$conn){
    $responseText .= 'Cannot connect to database... :( ';
    $responseText .= 'Trying to reestablish connection!';

    $arr['error'] = $responseText;
    echo json_encode($arr);
    die($responseText);
    exit();
}

$d1 = "2015-02-02 05:55:00";
$d2 = "2015-03-04 14:50:00";
$po = "1048821";
$side = "4070";

$date1 = strtotime($d1);
$date2 = strtotime($d2);

$responseText .= "<h1>Lekérdezések felosztása 8 órás periódusokra, két időintervallum között.</h1>";
$responseText .= "<h1>($d1 és $d2)</h1>";

$time_diff = ($date2-$date1);
//$responseText .= "<p>A különbség másodpercben: $time_diff</p>";

//$responseText .= "<p>A különbség: ".floor($time_diff/(3600*24))." nap ".gmdate("H:i:s",$time_diff).", ami ";

$oszt = ($time_diff/28800);
$oszt = round($oszt,5);
//$responseText .= $oszt." műszak!</p>";
$hanyegesz_muszak = floor($oszt);
//$egesz_muszak_ora = ($hanyegesz_muszak * 14400);

//$responseText .= "<p>".$d2." MINUSZ ".$hanyegesz_muszak." egész műszak (".($hanyegesz_muszak*8)." ora)"."</p>";
//$responseText .= "<p>---------------------</p>";

$j = 1;
$db = 0;

$maradek= $time_diff - (floor($oszt)*28800);

$last_sql_pieces = 0; // az utoljára gyártott sql lekérdezes eredménye (darabszám)

$msc = microtime(true);
$db_query = "SELECT 
                r2.recNr
            FROM
                recnrsernr r1
                    inner join
                recnrlaststation r2 ON r1.recNr = r2.recNr
                    inner join
                products p ON r1.productId = p.productId
                   inner join
                stations s ON s.stationId = r2.lastStation
                    inner join 
                recnrordernr ordernr ON ordernr.recNr = r1.recNr
            WHERE
                (r2.changeDate between '".$d1."' and '".$d2."') 
            AND (r2.lastStation = '".$side."') AND (ordernr.orderNr = '".$po."')
            GROUP BY r2.recNr
            LIMIT 16000";

        $db_result = mysqli_query($conn,$db_query);
$msc=microtime(true)-$msc;
$responseText .= "<script>console.log('db_query: ".round($msc*1000,3)." ms')</script>";

if(!$db_result){ die("db_query hiba"); }
    
$responseText .= "<p>Ebben az időszakban gyártott darabszám: ".mysqli_num_rows($db_result)."</p>";    
        $db += mysqli_num_rows($db_result); //az adott POhoz tartozó összes elkészült darabszám műszaktól függetlenül
        mysqli_free_result($db_result);

$responseText .= "<h1>Elkeszült darab: $db.</h1>";
echo $responseText;
exit();

for($i=$j; $i <= $hanyegesz_muszak; $i++){
       
        if($i==1){
            $responseText .= "<p>$i.) ".date("Y-m-d H:i:s",($date2-28800))." - <u><strong>".date("Y-m-d H:i:s",($date2))."</u></strong>";
        }else{
            $responseText .= "<p>$i.) ".date("Y-m-d H:i:s",($date2-28800))." - ".date("Y-m-d H:i:s",($date2));    
        }

        $t1 = date("Y-m-d H:i:s",($date2-28800));
        $t2 = date("Y-m-d H:i:s",($date2));

        $date2 -= 28800;
        $j++;

    $msc=microtime(true);
    
        $db_query = "SELECT 
                            r2.recNr
                        from
                            recnrsernr r1
                                inner join
                            recnrlaststation r2 ON r1.recNr = r2.recNr
                                inner join
                            products p ON r1.productId = p.productId
                               inner join
                            stations s ON s.stationId = r2.lastStation
                                inner join 
                            recnrordernr ordernr ON ordernr.recNr = r1.recNr
                        WHERE
                            (r2.changeDate between '".$t1."' and '".$t2."') 
                        AND (r2.lastStation = '".$side."') AND (ordernr.orderNr = '".$po."')
                        GROUP BY r2.recNr
                        LIMIT 16000";

        $db_result = mysqli_query($conn,$db_query);
    
    $msc=microtime(true)-$msc;
    $responseText .= "<script>console.log('db_query: ".round($msc*1000,3)." ms')</script>";
    if(mysqli_num_rows($db_result) == 0){ break; }
    
    $last_sql_pieces = mysqli_num_rows($db_result);

    $responseText .= " - itt elkészült darabszám: ".$last_sql_pieces."</p>";   
    
    if(!$db_result){ die("db_query hiba"); }

    $db += mysqli_num_rows($db_result); //az adott POhoz tartozó összes elkészült darabszám műszaktól függetlenül

    /* -- ha a lekérdezés már 0 sort adott vissza akkor kilépek a ciklusból -- */

    mysqli_free_result($db_result);
}

if(($maradek > 0) && ($last_sql_pieces != 0)){
        $responseText .= "<p>---------------------</p>";

        $responseText .= "<p>$j.) <u><strong>".date("Y-m-d H:i:s",$date2-$maradek)."</strong></u>  -  ".date("Y-m-d H:i:s",$date2)."</p>";
        $responseText .= "<p>Maradék: ".gmdate("H:i:s",$maradek)."</p>";

        $t_maradek1 = date("Y-m-d H:i:s",$date2-$maradek);
        $t_maradek2 = date("Y-m-d H:i:s",$date2);
    
$msc=microtime(true); 
        $db_query = "SELECT 
                        r2.recNr
                    FROM
                        recnrsernr r1
                            inner join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            inner join
                        products p ON r1.productId = p.productId
                           inner join
                        stations s ON s.stationId = r2.lastStation
                            inner join 
                        recnrordernr ordernr ON ordernr.recNr = r1.recNr
                    WHERE
                        (r2.changeDate between '".$t_maradek1."' and '".$t_maradek2."') 
                    AND (r2.lastStation = '".$side."') AND (ordernr.orderNr = '".$po."')
                    GROUP BY r2.recNr
                    LIMIT 16000";

        $db_result = mysqli_query($conn,$db_query);
$msc=microtime(true)-$msc;
$responseText .= "<script>console.log('db_query: ".round($msc*1000,3)." ms')</script>";

if(!$db_result){ die("db_query hiba"); }
    
$responseText .= "<p>Ebben az időszakban gyártott darabszám: ".mysqli_num_rows($db_result)."</p>";    
        $db += mysqli_num_rows($db_result); //az adott POhoz tartozó összes elkészült darabszám műszaktól függetlenül
        mysqli_free_result($db_result);
}

$responseText .= "<h1>Elkeszült darab: $db.</h1>";

echo $responseText;
?>