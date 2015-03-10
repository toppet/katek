<?php

// set up basic connection 
$conn_id = ftp_connect("10.10.1.205"); 

// login with username and password 
$login_result = ftp_login($conn_id, "pi", "raspberry");
ftp_pasv($conn_id, true);

if(!$login_result){
    die("Nem sikerült csatlakozni");
}

$local_file = "local_smt_output.xls";
$server_file = "/var/www/SMT_OUTPUT/SMT Output.xlsx";

// download server file
/*if (ftp_get($conn_id, $local_file, $server_file,  FTP_BINARY)){
  echo "Successfully written to $local_file.";
}else{
    die("Error downloading $server_file.");
}*/

if (!ftp_get($conn_id, $local_file, $server_file,  FTP_BINARY)){
    die("Error downloading $server_file.");
}

include 'simpleXLSX/simplexlsx.class.php';

$xlsx = new SimpleXLSX($local_file);

// output worksheet 1
list($num_cols, $num_rows) = $xlsx->dimension();

$code = array();
$nev = array();
$norma = array();

foreach( $xlsx->rows() as $r ) {
        $adat = (substr($r[0],-3) == 'SMD')?substr($r[0],0,-3):$r[0]; //Ha a kód végén ott van, hogy 'SMD', levágjuk.
        array_push($code,$adat);
        array_push($nev,$r[1]);
        array_push($norma,$r[2]);
}

$er = "<table style='border: 2px solid; border-collapse:collapse;'><tr><th>Id</th><th>Material Number</th><th>Material Name</th></tr>";

for($i=2; $i<22; $i++){
    $er .= "<tr><td>".($i-1)."</td><td>".$code[$i]."</td><td>".$nev[$i]."</td></tr>";
}

$er .= "</table>";

echo $er;

ftp_close($conn_id);

?>