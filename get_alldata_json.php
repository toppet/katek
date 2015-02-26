<?php
ini_set('default-charset','UTF-8');
setlocale(LC_ALL, 'en_EN');
$conn = mysqli_connect("10.10.1.1","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz
if(!$conn){
    die("Hiba az adazbázihoz történő csatlakozás közben");
    exit();
}

/* ----------   SOR 1    ----------- */
$kezdo_datum = date("Y-m-d"." 05:55:55");
$most = date("Y-m-d H:i:s");
$er = "";

$query_aoi = "SELECT
    count(distinct r2.recNr) AS 'db',p.productName as 'nev',
	p.productId as 'pid',r2.lastStation as 'station'
FROM
    recnrsernr r1
        join
    recnrlaststation r2 ON r1.recNr = r2.recNr
        join
    products p ON r1.productId = p.productId
        join
    stations s ON s.stationId = r2.lastStation
where
    (r2.changeDate between '$kezdo_datum'and '$most') 
        and (r2.lastStation like '%070'  or  r2.lastStation like '%071')
group by p.productId
order by max(r2.changeDate) desc";

$result_aoi = mysqli_query($conn,$query_aoi);

$er .= "<h1 id='time_interval'>".date("Y.m.d. 05:55") ." - ".date("Y.m.d. H:i")."</h1>";

$query_hibak = "SELECT 
                        count(failureCode) as 'hibas_db', p.productId as 'pid'
                    FROM
                        recnrsernr r1
                            join
                        recnrlaststation r2 ON r1.recNr = r2.recNr
                            join
                        products p ON r1.productId = p.productId
                            join
                        failures f ON failureCode = r2.procState
                    where
                       (r2.changeDate between '$kezdo_datum' and '$most')
                            and (r2.lastStation like '%070' or r2.lastStation like '%071')
                    group by p.productId";

$hibas_aoi_res = mysqli_query($conn,$query_hibak);

$hibak = array();
$i = 0;

while($hiba_row = mysqli_fetch_array($hibas_aoi_res)){
    $hibak[$i] = array();
    $hibak[$i][0] = $hiba_row['pid'];
    $hibak[$i][1] = $hiba_row['hibas_db'];
   $i++;
}

$line1 = "<table id='line_1'><tr><th colspan='4' class='line_header'>SMT Line 1</th></tr>
        <tr><th>Name</th><th>Manufactured</th><th>Fail</th><th>Fail percent</th></tr>";
$line2 = "<table id='line_2'><tr><th colspan='4' class='line_header'>SMT Line 2</th></tr>
        <tr><th>Name</th><th>Manufactured</th ><th>Fail</th><th>Fail percent</th></tr>";
$line3 = "<table id='line_3'><tr><th colspan='4' class='line_header'>SMT Line 3</th></tr>
        <tr><th>Name</th><th>Manufactured</th><th>Fail</th><th>Fail percent</th></tr>";
$line4 = "<table id='line_4'><tr><th colspan='4' class='line_header'>SMT Line 4</th></tr>
        <tr><th>Name</th><th>Manufactured</th><th>Fail</th><th>Fail percent</th></tr>";

while($row=mysqli_fetch_array($result_aoi)){
    
    
    switch($row['station']){
        case '4070':
            $volthibas = 0;
            $line1 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";        
        
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line1 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line1 .= "<td>0</td><td>-</td></tr>";
            }
            break;
        
        case '4071':
            $volthibas = 0;
            $line1 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";        

            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line1 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line1 .= "<td>0</td><td>-</td></tr>";
            }
            break;
        
        case '2070':
            $volthibas = 0;    
            $line2 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line2 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line2 .= "<td>0</td><td>-</td></tr>";
            }       
            break;
        
        case '2071':
            $volthibas = 0;    
            $line2 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line2 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line2 .= "<td>0</td><td>-</td></tr>";
            }       
            break;
        
        case '3070':
            $volthibas = 0;    
            $line3 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line3 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line3 .= "<td>0</td><td>-</td></tr>";
            }       
            break;
        
        case '3071':
            $volthibas = 0;    
            $line3 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line3 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line3 .= "<td>0</td><td>-</td></tr>";
            }       
            break;
        
        case '5070':
            $volthibas = 0;    
            $line4 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line4 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line4 .= "<td>0</td><td>-</td></tr>";
            }        
            break;
        case '5071':
            $volthibas = 0;    
            $line4 .= "<tr><td>".$row['nev']."</td><td>".$row['db']."</td>";
            for($i = 0; $i < count($hibak); $i++){
                if($row['pid'] == $hibak[$i][0]){
                    $line4 .= "<td>".$hibak[$i][1]."</td><td>".round(($hibak[$i][1]/$row['db'])*100,3)."%</td></tr>";
                    $volthibas = 1;
                }
            }
            if($volthibas == 0){
                $line4 .= "<td>0</td><td>-</td></tr>";
            }        
            break;
    }
}

$line1 .= "</table>";
$line2 .= "</table>";
$line3 .= "</table>";
$line4 .= "</table>";

$er .= $line1;
$er .= $line2;
$er .= $line3;
$er .= $line4;

$arr['result'] = $er;
$arr['lekerdezve'] = time();
echo json_encode($arr);
mysqli_close($conn);

?>