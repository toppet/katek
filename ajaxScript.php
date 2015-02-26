<?php
ini_set('display_errors', false);

date_default_timezone_set('Europe/Budapest');
ini_set('date.timezone', 'Europe/Budapest');

ini_set('default-charset','UTF-8');
setlocale(LC_ALL, 'en_EN');

session_start();

$conn = mysqli_connect("10.180.8.23","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz

$responseText = "";

$arr = array();

if(!$conn){
    $responseText .= 'Cannot connect to database... :( ';
    $responseText .= 'Trying to reestablish connection!';

    $arr['error'] = $responseText;
    echo json_encode($arr);
    die($responseText);
    exit();
}
    
    $product_code = $_POST['productCode'];
    $db = $_POST['givenValue'];
    $productCodeArray = $productCodeArray = explode(",",$_POST['productCodeArray']); // a beérkező string átalakítása tömbbé
    
    $norma = $_POST['norma'];
    $elkeszul = $_POST['elkeszul'];
    $po = $_POST['po'];

    $side_order = $_POST['side_order'];

    $emailElkuldve;

    $aoi;
    $side;

$responseText .= "<script>console.clear()</script>";

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
    
    $muszak_kezd = date("Y-m-d 05:55:55");
    $interval = '5';
    $most = date("Y-m-d H:i:s");

    
    // szöveges fájl készítése, amíg a lekérdezés folyamatban van
    $tmp = fopen("query_progress.txt", "w") or die("Unable to open file!");
    $txt = "1";
    fwrite($tmp, $txt);
    fclose($tmp);
    
    
/* ----- LEKÉRDEZEM, HOGY MI VOLT A LEGUTÓBB MÓDOSÍTOTT TERMÉK AZ ADOTT SORON ÉS HA AZ KÜLÖNBÖZIK A JELENLEGI TERMÉKTŐL AKKOR A RENDSZERT ÁTÁLLÍTOM ARRA ----- */
$msc=microtime(true);
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
                                (r2.changeDate between date_sub('".$most."',interval 5 hour) and '".$most."') and
                                (r2.lastStation = '".$aoi."070' or r2.lastStation = '".$aoi."071')
                            LIMIT 1";
    
    $recent_res = mysqli_query($conn,$recent_ordernr_query);
    $msc=microtime(true)-$msc;
    $responseText .= "<script>console.log('recent_ordernr_query: ".round($msc*1000,3)." ms')</script>";

    $recent_order_number = mysqli_fetch_array($recent_res);
/*  
    Ha a visszaadott po nem egyezik meg a most vizsgált termékkel ÉS benne van a legyártandó termékeket tároló tömbben, akkor váltunk
*/
    if( ($po != $recent_order_number['orderNr']) && in_array($recent_order_number['orderNr'],$productPOArray) ){
        
        // a production order szám meghatározásához szükséges tömbindex
        $array_index = array_search($recent_order_number['orderNr'],$productPOArray);
        $arr['array_index'] = $array_index;
        
        $arr['atallas'] = TRUE;
        $arr['atallas_orderNr'] = $recent_order_number['orderNr'];
        
        session_destroy(); //az adott termékre vonatkozó $_SESSION változókat megsemmisítem.
        
        echo json_encode($arr);
        die();
        exit();
    }  
/* --------------- end recent_ordernr_query ----------------- */

    if((!isset($_SESSION['pid']) || empty($_SESSION['pid'])) && (!isset($_SESSION['product_name']) || empty($_SESSION['product_name']))){

        $query_pid = "SELECT 
                       productId as 'pid', productName as 'name', doubleSide as 'doubleSide'
                    FROM
                        products
                    WHERE
                       (productCode = '".$product_code."')";

        $pid_res = mysqli_query($conn,$query_pid);
        $pid_row = mysqli_fetch_array($pid_res);
        
        if(!$pid_res){
            die("nemjó");
            exit();
        }

        $_SESSION['pid'] = (int) $pid_row['pid'];        
        $_SESSION['product_name'] = iconv("ISO-8859-2","UTF-8",$pid_row['name']);
        $_SESSION['doubleSide'] = $pid_row['doubleSide'];

        mysqli_free_result($pid_res);
    
    }

    $pid = $_SESSION['pid'];
    $product_name = $_SESSION['product_name'];
    $doubleSide = $_SESSION['doubleSide'];
    $doubleSide = (int) $doubleSide; // számmá konvertálom


/* --------  A legelső darab ami az adott megrendelés számhoz tartozik mikor készült el -------- */
    if( !isset($_SESSION['first_done_date']) || empty($_SESSION['first_done_date']) ){
        $msc = microtime(TRUE);
            $elso_db_po_query = "SELECT
                                    r2.changeDate as 'mini'
                                FROM
                                    recnrsernr r1
                                        inner join
                                    recnrlaststation r2 ON r1.recNr = r2.recNr
                                        inner join
                                    stations s ON s.stationId = r2.lastStation
                                        inner join
                                    recnrordernr ordernr ON ordernr.recNr = r1.recNr
                                WHERE
                                    (r2.changeDate between date_sub('".$muszak_kezd."',interval ".$interval." day) and '".$most."') 
                                    and (r2.lastStation like '".$aoi."07%') and (orderNr = '".$po."')
                                ORDER BY r2.changeDate
                                LIMIT 1";
    
        $first_done_res = mysqli_query($conn,$elso_db_po_query);
        $msc = microtime(TRUE)-$msc;
        $responseText .= "<script>console.log('elso_db_po_query: ".round($msc*1000,3)." ms')</script>";
        
        if(!$first_done_res){
            die("elso_db_po_query nem jó");
        }
        
        $first_done_date_arr = mysqli_fetch_array($first_done_res);
        $first_done_date = $first_done_date_arr['mini'];
        
        $_SESSION['first_done_date'] = $first_done_date;
    }
        

    $elso_db_kesz_datum = $_SESSION['first_done_date'];

/* ------ end $elso_db_po_query ------ */


//    Az egyoldalas terméknél a adott top vagy bottom oldal meghatározása, azért,
//    hogy a lekérdezésnél ne kelljen külön a top és bottom oldara ellenőrzni

if( (!isset($_SESSION['side']) || empty($_SESSION['side'])) && $doubleSide == 0){
    
    $msc = microtime(TRUE);
    
    $top_or_bottom_query = "SELECT 
                                r2.lastStation AS 'station'
                            FROM
                                recnrsernr r1
                                    INNER JOIN
                                recnrlaststation r2 ON r1.recNr = r2.recNr
                                    INNER JOIN
                                products p ON r1.productId = p.productId
                                    INNER JOIN
                                recnrordernr ordernr ON ordernr.recNr = r1.recNr
                            WHERE
                                (r2.changeDate BETWEEN '".$muszak_kezd."' AND '".$most."')
                                    AND (r2.lastStation IN ('".$aoi."070','".$aoi."071'))
                                    AND (orderNr = '".$po."')
                            LIMIT 1";
    $top_or_bottom_res = mysqli_query($conn,$top_or_bottom_query);
    
    $msc = microtime(TRUE)-$msc;
    $responseText .= "<script>console.log('top_or_bottom_query: ".round($msc*1000,3)." ms')</script>";
    
    if(!$top_or_bottom_res){ die("top_or_bottom_query error");  }
    
    $top_or_bottom = mysqli_fetch_array($top_or_bottom_res);
    
    $_SESSION['side'] = ($top_or_bottom['station'] == $aoi.'070')?$aoi."070":$aoi.'071';
}

$side = $_SESSION['side'];

/* ----- end top_or_bottom_query ----- */

    
/*  ---------- DARAB ----------- */
if($doubleSide == 1){    
    $msc=microtime(true);
         $top_query = "SELECT                                                           
                        p.productId
                    FROM
                        recnrsernr r1
                            inner join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            inner join
                        products p ON r1.productId = p.productId
                            inner join 
                        recnrordernr ordernr ON ordernr.recNr = r1.recNr
                    where
                        (r2.changeDate between '".$elso_db_kesz_datum."' and '".$most."')
                            and (r2.lastStation = '".$aoi."070')
                            and (ordernr.orderNr = '".$po."')
                    group by r2.recNr";
        
        $top_res = mysqli_query($conn,$top_query);
    
    $msc=microtime(true)-$msc;
    $responseText .= "<script>console.log('top_query: ".round($msc*1000,3)." ms')</script>";
    
$msc=microtime(true);
        $bottom_query = "SELECT 
                        p.productId
                    FROM
                        recnrsernr r1
                            inner join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            inner join
                        products p ON r1.productId = p.productId
                            inner join 
                        recnrordernr ordernr ON ordernr.recNr = r1.recNr
                    WHERE
                        (r2.changeDate between '".$elso_db_kesz_datum."' AND '".$most."')
                            AND (r2.lastStation = '".$aoi."071')
                            AND (ordernr.orderNr = '".$po."')
                    GROUP BY r2.recNr";

        $bottom_res = mysqli_query($conn,$bottom_query);
$msc=microtime(true)-$msc;
$responseText .= "<script>console.log('bottom_query: ".round($msc*1000,3)." ms')</script>";
    
        
        /* ------  TOP-BOTTOM OLDALON HÁNY AZONOS recNr TALÁLHATÓ ------ */
    $msc=microtime(true);
        
    $top_bottom_db_query = "SELECT 
                                r2.recNr
                            FROM
                                recnrsernr r1
                                    INNER JOIN
                                recnrlaststation r2 ON r1.recNr = r2.recNr
                                    INNER JOIN
                                products p ON r1.productId = p.productId
                                    INNER JOIN
                                stations s ON s.stationId = r2.lastStation
                                    INNER JOIN
                                recnrordernr ordernr ON ordernr.recNr = r1.recNr
                                    INNER JOIN
                                (SELECT 
                                    r2.recNr
                                FROM
                                    recnrsernr r1
                                INNER JOIN recnrlaststation r2 ON r1.recNr = r2.recNr
                                INNER JOIN products p ON r1.productId = p.productId
                                INNER JOIN stations s ON s.stationId = r2.lastStation
                                INNER JOIN recnrordernr ordernr ON ordernr.recNr = r1.recNr
                                WHERE
                                    (r2.changeDate BETWEEN '".$elso_db_kesz_datum."' AND '".$most."')
                                        AND (r2.lastStation = '".$aoi."070')
                                        AND (orderNr = '".$po."')) dup ON dup.recNr = r2.recNr
                            WHERE
                                (r2.changeDate BETWEEN '".$elso_db_kesz_datum."' AND '".$most."')
                                    AND (r2.lastStation = '".$aoi."071')
                                    AND (orderNr = '".$po."')
                            LIMIT ".$db;
        $top_bottom_db_res = mysqli_query($conn,$top_bottom_db_query);
    
    $msc=microtime(true)-$msc;
    $responseText .= "<script>console.log('top_bottom_db_query: ".round($msc*1000,3)." ms')</script>";
    
        if(!$top_bottom_db_res){
            die("nemjó a topbottomquery");
        }
        // összeadom a top és bottom oldalon egyező recNr-ök számát (ideális esetben pl.: 5+0 = 5)
        $osszes_elkeszultDB = mysqli_num_rows($top_bottom_db_res);
    
        $db_kiiras = "Top: ".mysqli_num_rows($top_res)." / Bottom: ".mysqli_num_rows($bottom_res); //az a szöveg amit darabszámként kiiratunk
        mysql_free_result($top_res);
        mysql_free_result($bottom_res);
        mysql_free_result($top_bottom_db_res_res);
    
    }else{
        //if( !isset($_SESSION['osszes_elkeszult_db']) || empty($_SESSION['osszes_elkeszult_db']) ){
            
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
                        (r2.changeDate between '".$elso_db_kesz_datum."' and '".$most."') 
                    AND (r2.lastStation = '".$side."') AND (ordernr.orderNr = '".$po."')
                    GROUP BY r2.recNr
                    LIMIT ".$db;

            $db_result = mysqli_query($conn,$db_query);
            
            if(!$db_result){ die("db_query hiba"); }
    
            $msc=microtime(true)-$msc;
            $responseText .= "<script>console.log('db_query: ".round($msc*1000,3)." ms')</script>";
    
            $osszes_elkeszultDB = mysqli_num_rows($db_result); //az adott POhoz tartozó összes elkészült darabszám műszaktól függetlenül

            $db_kiiras = $osszes_elkeszultDB; //az a darabszám amit darabszámként kiiratunk
            mysqli_free_result($db_result);
            
            //$_SESSION['osszes_elkeszult_db'] = $osszes_elkeszultDB;
            
       /* }else{
            $osszes_elkeszultDB = $_SESSION['osszes_elkeszult_db'];
            $db_kiiras = $osszes_elkeszultDB;
        }*/
    }



/* ------ ADOTT MŰSZAKBAN ELKÉSZÜLT DARABSZÁM ------ */
/* ------------------------------------------------- */
    
    $muszak_div = "<div id='shift'>";
    switch($most){
        case($most >= date("Y-m-d 05:55:00") && $most < date("Y-m-d 13:55:00")):
            $muszak_div .= "<h2>Morning Shift</h2>";
            $muszak_kezd = date("Y-m-d 05:55:00");
            break;
        case($most >= date("Y-m-d 13:55:00") && $most < date("Y-m-d 21:55:00")):
            $muszak_div .= "<h2>Afternoon shift</h2>";
            $muszak_kezd = date("Y-m-d 13:55:00");
            break;
        case($most >= date("Y-m-d 21:55:00") && $most < date("Y-m-d 05:55:00")):
            $muszak_div .= "<h2>Night Shift</h2>";
            $muszak_kezd = date("Y-m-d 21:55:00");
            break;
    }
    $muszak_div .= "</div>";
    $responseText .= $muszak_div;

    /*
    $muszak_db_query = "SELECT 
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
                            (r2.changeDate between '".$muszak_kezd."' and '".$most."') 
                        and (r2.lastStation like '".$aoi."07%') and ordernr.orderNr = '".$po."' and p.productId = '".$pid."'
                        GROUP BY r2.recNr";
    
    $muszak_db_res = mysqli_query($conn,$muszak_db_query);
    $muszak_db = mysqli_num_rows($muszak_db_res);*/
    
/* ------ ADOTT MŰSZAKBAN HIBÁS DARABSZÁM ------ */
/* ------------------------------------------------- */
    
    /*$muszak_hibas_query = "SELECT 
                        count(f.failureCode) as 'hibas'
                    FROM
                        recnrsernr r1
                            inner join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            inner join
                        products p ON r1.productId = p.productId
                            inner join
                        failures f ON failureCode = r2.procState
                            inner join 
                        recnrordernr ordernr ON ordernr.recNr = r1.recNr
                    WHERE
                        (r2.changeDate between '".$muszak_kezd."' and '".$most."')
                            AND (r2.lastStation like '".$aoi."07%'
                            OR r2.lastStation = '".$aoi."071') AND (p.productId = '$pid') and ordernr.orderNr = '".$po."'
                    LIMIT 1";

    $muszak_hibas_res = mysqli_query($conn,$muszak_hibas_query);
    if(!$muszak_hibas_res){
        die("hibas_res nemjó");
    }
    $muszak_hibas_arr = mysqli_fetch_array($muszak_hibas_res);
    $muszak_hibas_db = $muszak_hibas_arr['hibas'];*/
    
    //$responseText .= "<p>A adott műszakban elkészült darab: ".$muszak_db." db, ebből hibás: ".$muszak_hibas_db. " db</p>";
    
    /* ------------------- HIBAS ------------------- */

$msc=microtime(true);

    $query_hibas = "SELECT 
                        COUNT(rfail.failCode) AS 'hibas'
                    FROM
                        recnrsernr r1
                            inner join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            inner join
                        products p ON r1.productId = p.productId
                            INNER JOIN
                        recnrprocfail rfail ON r2.procId = rfail.procId
                            inner join 
                        recnrordernr ordernr ON ordernr.recNr = r1.recNr
                    WHERE
                        (r2.changeDate between '".$elso_db_kesz_datum."' and '".$most."')
                            AND (r2.lastStation = '".$side."') AND (ordernr.orderNr = '".$po."')
                    LIMIT 1";

    $hibas_db = 0;
    $hibas_res = mysqli_query($conn,$query_hibas);

$msc=microtime(true)-$msc;
$responseText .= "<script>console.log('query_hibas: ".round($msc*1000,3)." ms')</script>";

    if(!$hibas_res){
        die("hibas_res nemjó");
    }
    
    $row = mysqli_fetch_array($hibas_res);
    mysqli_free_result($hibas_res);

    $hibas_db = $row['hibas'];

    //$f = strtotime($_SESSION['first']); //első db elkészült
    $f = strtotime($elso_db_kesz_datum); //első db elkészült
    //$l = strtotime($last_row['last']); // utolsó darab elkeszült

    $e = strtotime(date("Y-m-d H:i",$elkeszul)); // a megadott darabszám elkészülési ideje

    $hiba = mysqli_query($conn,$query_hibas);
    $hibasdb_tomb = mysqli_fetch_array($hiba);
    $hibasdb = $hibasdb_tomb['hibas'];
    
/* -------------------------------- ADATOK KIÍRATÁSA --------------------------------- */
/* ---------------------------------------------------------------------------------- */
    
    $responseText .= "<div id='product_info'><p>".$product_name." </p><p>(".$product_code.") - PO: ".$po."</p></div>"; 

    $maradek = ($db - $osszes_elkeszultDB > 0) ? ($db - $osszes_elkeszultDB) : '0';
    $maradek_elkeszul = (28800 / $norma) * $maradek;
    $most_mpben = strtotime(date('Y-m-d H:i'));

    $maradek_ido = gmdate("H:i",$maradek_elkeszul);
    $maradek_konv = date("Y-m-d H:i",$most_mpben+$maradek_elkeszul);

    $responseText .= "<table id='data_table'><tr><th id='required'>Required</th><th id='manufactured'>MANUFACTURED</th><th id='faulty'>FAIL</th><th id='remaining'>remaining</th></tr>";
    $responseText .= "<tr><td>".$db."</td>"; 
    $responseText .= "<td>".$db_kiiras."</td>";
    $responseText .= "<td>".$hibasdb."</td>";
    $responseText .= "<td rowspan='2'>".$maradek."</td></tr>";
    
/* ------------ MŰSZAKRA VONATKOZÓ ADATOK ---------- */
    /*
    $responseText .= "<tr>";
    $responseText .= "<td>Current Shift</td><td>".$muszak_db."</td><td>".$muszak_hibas_db."</td>";
    $responseText .= "</tr>";*/
    $responseText .= "</table>";

    /* ------------- Előre kiszámolt elkészülési idő ------------------ */

    //$planned = date("Y-m-d H:i",$f+$e); //formázott várható befejezési idő

    //------------ Expected Information ------------- //

    $egydarab = (28800/$norma);
    $expected = ($most_mpben+$maradek_elkeszul);

    //---- KÉSÉS KISZÁMÍTÁSA ----
    function checkTime($x){
        if($x<10){
            $x = "0".$x;
        }
        return $x;
    }

   /*if($expected>($f+$e)){

        $start = date("Y-m-d H:i",$expected);
        $end = date("Y-m-d H:i",($f+$e));

        $ts1 = strtotime($start);
        $ts2 = strtotime($end);

        $seconds_diff = $ts1 - $ts2;

        $elteres = checkTime(floor($seconds_diff/3600)).":".checkTime((($seconds_diff/60)%60));

        $keses = "<p style='color:red;'><img src='images/delay.png' alt='delay' style='width:75px; margin:0; padding:0;'> -".$elteres."</p>";
    }else{
        $start = date("Y-m-d H:i",($f+$e));
        $end = date("Y-m-d H:i",$expected);

        $ts1 = strtotime($start);
        $ts2 = strtotime($end);

        $seconds_diff = $ts1 - $ts2;

        $elteres = checkTime(floor($seconds_diff/3600)).":".checkTime((($seconds_diff/60)%60));

        $keses = "<p style='color:green;'><img src='images/nodelay.png' alt='delay' style='width:75px; margin:0; padding:0;'> +".$elteres."</p>";
    }*/

    // ---------- TÁBLÁZAT KIÍRÁS ------ //
    $responseText .= "<table id='production_time'>";
    //$responseText .= "<tr><th class='planned_end_time'>Output/shift</th><th class='delay'>Delay</th><th class='expected_end_time'>Expected End Time</th></tr>";
    $responseText .= "<tr><th class='planned_end_time'>Output/shift</th><th class='expected_end_time'>Expected End Time</th></tr>";
    //$responseText .= "<tr><td class='planned_end_time' style='font-weight: bold;'>".$planned."</td>";
    $responseText .= "<tr><td class='output' style='font-weight: bold;'>".$norma."</td>";

    //$responseText .= "<td class='delay' style='background-color: #fff; border-bottom:none;'>".$keses."</td>";
    //$responseText .= "<td class='delay' style='background-color: #fff; border-bottom:none;'>".$keses."</td>";

    $responseText .= "<td class='expected_end_time'>".date("Y-m-d H:i",$expected)."</td>";
    $responseText .= "</tr>";
    $responseText .= "</table>";

    // ----- TÁBLÁZAT KIÍRÁS VÉGE  -----

    //$kovetkezo = date("Y-m-d H:i",strtotime("-30 minutes",$f+$e));

    //$responseText .= "<p>Következő betöltése: <br/>".$kovetkezo."</p>"; // következő termék betöltésének kiírása

    // ellenőrzés, hogy az email el lett-e küldve
    /*$conn2 = mysqli_connect("localhost","root","");

     $email_query = "SELECT * FROM gyartas.termekek WHERE product_code='$product_code'";
     $ress = mysqli_query($conn2,$email_query);
     $row = mysqli_fetch_array($ress);*/

    //---- egyelőre nem    $emailElkuldve =  $row['email'];

    //if(strtotime(date("Y-m-d H:i")) >= strtotime($kovetkezo) && $emailElkuldve == 0){

    //elküldjük a figyelmeztető emailt

    //------ egyelőre kivesszük        require_once("email/email.php"); --------//

    // az adatbázisban frissítem, hogy elküldtük az email-t és beállítom az $emailElkuldve változót 1-re
    //require_once("update_email_status.php");
    //}

    if($osszes_elkeszultDB >= $db){
        $responseText .= "<h3 class='done'style='color:green'>A megadott darabszám elkészült!</h3>";
$msc=microtime(true);
        $last_query = "SELECT 
                            p.productName as 'nev', r2.changeDate
                        FROM
                            recnrsernr r1
                               inner join
                            recnrlaststation r2 ON r1.recNr = r2.recNr
                                inner join
                            products p ON r1.productId = p.productId
                                inner join 
                            recnrordernr ordernr ON ordernr.recNr = r1.recNr
                        WHERE
                            (r2.changeDate between '".$elso_db_kesz_datum."' and  '".$most."')
                                and (r2.lastStation = '".$side."')
                                and (p.productId = '".$pid."') and (orderNr = '".$po."')
                        ORDER BY r2.changeDate desc 
                        LIMIT 1";

        $last_result = mysqli_query($conn,$last_query);
        $msc=microtime(true)-$msc;
    $responseText .= "<script>console.log('last_query: ".round($msc*1000,3)." ms')</script>";
        mysql_free_result($last_result); // memória felszabadítás

        $last_product_row = mysqli_fetch_array($last_result);
        // Ha az utolsó darab is elkészült, akkor a késés nem számolom tovább és beállítom az utolsó elkészült darab idejéhez

        $keses = $last_product_row['changeDate'];
        $arr['tenyleges'] = $last_product_row['changeDate'];
        $arr['kesz'] = TRUE;

        // ----- Update Production ----- //
        $update_production_status = mysqli_connect("10.10.1.205","root","");

        $production_done_query = "UPDATE gyartas.termekek SET production_done = '1' WHERE (product_code = '".$product_code."' and po = '".$po."')";
        $production_done_res = mysqli_query($update_production_status,$production_done_query);
        mysql_free_result($production_done_res); // memória felszabadítás

        if(!$production_done_res){
            $arr["error"] =  'product_sample update error...';
            $arr['p_code'] = $product_code;
            echo json_encode($arr);
            die();
            exit();
        }
        
        session_unset(); //SESSION változók felszabadítása
    }

    $hibatlandb = $osszes_elkeszultDB-$hibasdb;

// ----- DIAGRAMM SCRIPT ----- //
    $hibasArany = round(($hibasdb/$osszes_elkeszultDB)*100,2);
    $hibatlanArany = round((($osszes_elkeszultDB-$hibasdb)/$osszes_elkeszultDB)*100,2);


// HA A HIBAARÁNY NAGYOBB MINT 2% (SMT ESETÉN)
    if($hibasArany>=2){
        $arr['high_fail_rate'] = true;
    }
/* -------------- Diagrams div kezdete -------------- */
   $responseText .= "<div id='diagrams'>";

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
                                        return this.value + "%";
                                        }
                                } // label
                            }, // series

                            palette: ["green","red"],
                            legend: {
                                visible: false,
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
    /*if($elkeszultDB >= $db){
        include_once('ExcelWriter/kiir.php'); 
    }

    $arr['emailElkuldve'] = $emailElkuldve;
 */

    /* ----- HIBA DIAGRAM ----- */
$msc=microtime(true);
   $hibatipus_query = "SELECT 
                            count(rfail.failText) as 'hibasdb', rfail.failText
                        FROM
                            recnrsernr r1
                                inner join
                            recnrlaststation r2 ON r1.recNr = r2.recNr
                                inner join
                            products p ON r1.productId = p.productId
                                inner join
                            recnrprocfail rfail ON r2.procId = rfail.procId
                                inner join 
                            recnrordernr ordernr ON ordernr.recNr = r1.recNr
                        WHERE
                            (r2.changeDate between '".$elso_db_kesz_datum."' and '".$most."')
                                and (r2.lastStation = '".$side."') and (r2.procState = 0)
                                and (orderNr = '".$po."')
                        GROUP BY rfail.failtext";

    $hiba_result = mysqli_query($conn,$hibatipus_query);

    $msc=microtime(true)-$msc;
    $responseText .= "<script>console.log('hibatipus_query: ".round($msc*1000,3)." ms')</script>";

    mysql_free_result($hiba_result);
    
    $hiba_leiras = array();
    $hibas_elemek_szama = array();

    if(!hiba_result){
        $responseText = "Hiba hibatipus_query..";
    }

    while($error_row = mysqli_fetch_array($hiba_result)){
        //array_push($hiba_leiras,iconv("ISO-8859-2//IGNORE","UTF-8",$error_row['failText'])." (".$error_row['hibasdb']."db)");
        $str = $error_row['failText'];
        $hiba_szoveg = explode(" ",$str);
        
        if($hiba_szoveg[2] != ""){
            array_push($hiba_leiras,iconv("ISO-8859-2//IGNORE","UTF-8",$hiba_szoveg[0]." ".$hiba_szoveg[1]."...")." (".$error_row['hibasdb']."db)");    
        }else{
            array_push($hiba_leiras,iconv("ISO-8859-2//IGNORE","UTF-8",$hiba_szoveg[0]." ".$hiba_szoveg[1])." (".$error_row['hibasdb']."db)");
        }

        array_push($hibas_elemek_szama,round(($error_row['hibasdb']/$hibasdb)*100,1));
    }

    $fail_datasource = "";

    for($i = 0; $i<count($hiba_leiras);$i++){
        $fail_datasource .= '{error_desc:"'.$hiba_leiras[$i].'",error_db:'.$hibas_elemek_szama[$i].'}';
        if($i !=(count($hiba_leiras)-1)){
            $fail_datasource .= ",";
        }
    }

$responseText .= '<script>
                    $(function () {
                        var dataSource2 = [
                           '.$fail_datasource.'
                        ];

                        $("#failContainer").dxPieChart({

                            dataSource: dataSource2,
                            series: 
                            {
                                argumentField: "error_desc",
                                valueField: "error_db",
                                verticalAlignment: "top",

                                label: {
                                    visible: true,
                                    connector: {
                                        visible: true,
                                        width: 1
                                    },
                                    font: {
                                        color:"white",
                                        size: 40
                                    },
                                    customizeText: function(){
                                        return this.value + "%";
                                        }
                                } // label
                            }, // series

                            palette: ["orange","red","blue","pink","purple","yellow"],
                            legend: {
                                visible: true,
                                horizontalAlignment: "right",
                                verticalAlignment: "top",
                                columnCount:1,
                                font: {
                                  color:"#555",
                                  size: 20
                                }
                            } // legend
                        }); //piechart
                    });
            </script>	
<div id="failContainer"></div>';

$responseText.="</div>"; // ----- diagrams div vége ----- //

mysqli_close($conn);

/*
    Ideiglenes fájl törlése, ami ahhoz kellett,
    hogy amíg a fájl létezik, addig ne lehessen
    másik lekérdezést végrehajrani az adatbázisban
*/
unlink('query_progress.txt'); 

$arr['responseText'] = $responseText;
echo json_encode($arr);
exit();

?>