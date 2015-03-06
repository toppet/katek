<?php

$conn = mysqli_connect("10.10.1.1","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz

$responseText = "";
$arr = array();

ini_set('display_errors', false);

date_default_timezone_set('Europe/Budapest');
ini_set('date.timezone', 'Europe/Budapest');

ini_set('default-charset','UTF-8');
setlocale(LC_ALL, 'hu_HU');

if(!$conn){
    $responseText .= 'Cannot connect to database... :( ';
    $responseText .= 'Trying to reestablish connection!';

    $arr['error'] = $responseText;
    echo json_encode($arr);
    die($responseText);
}

$today = date("Y-m-d");
$yesterday = date("Y-m-d",strtotime("-1 day"));
$now = date("Y-m-d H:i:s");
$pid = '443';
$aoi = 4;

/*$query_24 = "select 
                count(distinct r2.recNr) as 'db'
            from
                recnrsernr r1
                    join
                recnrlaststation r2 ON r1.recNr = r2.recNr
                    join
                products p ON r1.productId = p.productId
            where
                (r2.changeDate between '$yesterday 05:55:55' and '$yesterday 14:00:00')
                    and (r2.lastStation like '".$aoi."070'
                    or r2.lastStation like '".$aoi."070') and p.productId = '$pid'
            group by p.productId";*/

$query_24 = "SELECT 
               p.productCode as 'kod',p.productName as 'nev'
            FROM
                recnrsernr r1
                    inner join
                recnrlaststation r2 ON r1.recNr = r2.recNr
                    inner join
                products p ON r1.productId = p.productId
            where
                (r2.changeDate between '".date("Y-m-d H:i:s",strtotime('-5 hour'))."' and '".$now."') and
                (r2.lastStation = '4070' or r2.lastStation = '4071')
            ORDER BY r2.changeDate desc 
            LIMIT 1";

$result_query_24 = mysqli_query($conn,$query_24);

$db_query_24 = mysqli_fetch_array($result_query_24);

echo "nev: ".$db_query_24['nev'];
die();
exit();
$query_16 = "select 
                count(distinct r2.recNr) as 'db'
            from
                recnrsernr r1
                    join
                recnrlaststation r2 ON r1.recNr = r2.recNr
                    join
                products p ON r1.productId = p.productId
            where
                (r2.changeDate between '$yesterday 14:00:00' and '$yesterday 22:00:00')
                    and (r2.lastStation like '".$aoi."070'
                    or r2.lastStation like '".$aoi."070') and p.productId = '$pid'
            group by p.productId";

$result_query_16 = mysqli_query($conn,$query_16);
$db_query_16 = mysqli_fetch_array($result_query_16);

$query_8 = "select 
                count(distinct r2.recNr) as 'db'
            from
                recnrsernr r1
                    join
                recnrlaststation r2 ON r1.recNr = r2.recNr
                    join
                products p ON r1.productId = p.productId
            where
                (r2.changeDate between '$yesterday 22:00:00' and '$today 05:55:55')
                    and (r2.lastStation like '".$aoi."070'
                    or r2.lastStation like '".$aoi."070') and p.productId = '$pid'
            group by p.productId";

$result_query_8 = mysqli_query($conn,$query_8);
$db_query_8 = mysqli_fetch_array($result_query_8);

$query_till_now = "select 
                        count(distinct r2.recNr) as 'db'
                    from
                        recnrsernr r1
                            join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            join
                        products p ON r1.productId = p.productId
                    where
                        (r2.changeDate between '$today 05:55:55' and '".$now."')
                            and (r2.lastStation like '".$aoi."'
                            or r2.lastStation like '".$aoi."') and p.productId = '$pid'
                    group by p.productId";

$result_query_till_now = mysqli_query($conn,$result_query_till_now);
$db_query_till_now = mysqli_fetch_array($result_query_till_now);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
   <title>Osszeg</title>
   <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
   <meta http-equiv="Content-Language" content="hu" />
</head>
<body>
   <?php 
        /*echo "<p>query 24:". intval($db_query_24['db']) ."</p>";
        echo "<p>query 16:". intval($db_query_16['db'])."</p>";
        echo "<p>query 8:". intval($db_query_8['db'])."</p>"; 
        echo "<p>query now:";
        echo (is_null($db_query_till_now['db']))?0:intval($db_query_till_now['db']);
        echo "</p>"; */

        $osszeg = intval($db_query_24['db'])+intval($db_query_16['db'])+intval($db_query_8['db'])+intval($db_query_till_now['db']);
        echo "<p>osszesen:". $osszeg."</p>" ?>
</body>
</html>