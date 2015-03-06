<?php
$conn = mysqli_connect("localhost","root","","gyartas");
ini_set("default_charset","utf-8");
$query = "SELECT * FROM termekek ORDER BY smt";
$result = mysqli_query($conn,$query);



/*
$termeknevek = array();
$darabszamok = array();
$elkeszul_idok = array();
$pok = array();

$res = "<h1>Tábla</h1>";
$res .= "<table>";
$res .= "<tr><th></th><th>".date("m.d.")." de</th><th>db</th><th>PO</th><th>elkeszul</th></tr>";

$i=0;
while($row = mysqli_fetch_array($result)){
   
    $osszido += ($row['elkeszul']);
    if((int) floor($row['elkeszul']/3600)>=8){
        echo "ezzel együtt már több mint egy műszak.".$row['product_name'];
        
        $de_ido = getIdo($osszido-($osszido-28800));
            echo "eddig:". $de_ido. ", majd a maradék pedig: " .getIdo($osszido-28800);
        
    }
    if($i==0){
        $res.="<tr><td>SMT Line ".$row['smt']."</td><td>". $row['product_name'] ."</td><td>".$row['quantity']."</td><td>".$row['po']."</td><td>".getIdo($row['elkeszul'])."</td></tr>";    
    }else{
        $res.="<tr><td>SMT Line ".$row['smt']."</td><td>". $row['product_name'] ."</td><td>".$row['quantity']."</td><td>".$row['po']."</td><td>".getIdo($row['elkeszul'])."</td></tr>";    
    }
    array_push($termeknevek,$row['product_name']);
    array_push($darabszamok,$row['quantity']);
    array_push($elkeszul_idok,$row['elkeszul']);
    array_push($pok,$row['po']);
    
    //$osszido += $row['elkeszul'];
}

$res .= "</table>";

$osszido = 0; // mp
$segedido = 0; // mp

$szamok = [1,20,3];
rekurziv($szamok);

function rekurziv($tomb){
    $osszido = 0;
    $segedtomb = array();//[5,3,5,3,1]
    $segednevtomb = array();
    
    for($i = 0; $i < 3; $i++){
        $osszido += $tomb[$i];
        if($tomb[$i]<=8){
            array_push($segedtomb,$tomb[$i]);
        }else{
           array_push($segedtomb,feloszt($tomb[$i]));
        }
    }
    
print_r ($segedtomb);
echo "<br/>osszido: ".$osszido;
}
*/




function feloszt($szamtomb,$hanyszorfusson){
    $szam = $szamtomb;
    $lefutott = $hanyszorfusson;
    
    if($lefutott == 0){
        print_r($szam);
    }else{
        $lefutott--;
        feloszt(tombosztas($szam),$lefutott);
    }
}

function tombosztas($tomb){
    $elsomuszak = array();
    $masodikmuszak = array();
    $harmadikmuszak = array();
    
    for($i = 0; $i<count($tomb); $i++){
        //echo $i.".lefutas tomb[i]:".$tomb[$i]."<br/>";
        if($i==0 && $tomb[$i]<=8){
            $tomb[0] = $tomb[$i];
            //muszak
        }elseif($i == 0 && $tomb[$i]>8){
            $ertek = $tomb[$i];
            $kul = ($tomb[$i]-8);
            $tomb[0] = ($tomb[$i]-$kul);
            array_splice($tomb,($i+1),0,($ertek - $tomb[$i]));
        }else if($tomb[$i]>=8){
            
            if($tomb[$i]+$tomb[$i-1]<=8){
                echo "hohoo";
            }else{
                $ertek = $tomb[$i];
                echo "$"."tomb szam:".$ertek."<br/>";
                $tomb[$i] = (8-$tomb[$i-1]);
                $maradek = ($ertek - $tomb[$i]);
                array_splice($tomb,($i+1),0,$maradek);
                $i++;
            }
        }
    }
    
    $osszeg = 0;
    for($k = 0; $k<count($tomb); $k++){
        $osszeg+=$tomb[$k];
        
        if($osszeg <= 8){
            array_push($elsomuszak,$tomb[$k]);
        }else if($osszeg >8 && $osszeg <=16){
            array_push($masodikmuszak,$tomb[$k]);
        }else{
            array_push($harmadikmuszak,$tomb[$k]);
        }
    }

   echo "Első műszak:";
    print_r($elsomuszak);
    echo "<br/>";
    echo "Második műszak:";
    print_r($masodikmuszak);
    echo "<br/>";
    echo "Harmadik műszak:";
    print_r($harmadikmuszak);
    echo "<br/>";
    
    echo "összeg:".$osszeg."<br/>";
    return $tomb;
}

echo "<h1>";
feloszt([2,6,6,5,5],1);
echo "</h1>";


function getOra($ido_mp){
    $ora = round($ido_mp/3600);
    
    return $ora;
}
/*$osszido += $elkeszul_idok[$i]; //mp
        //echo getOra($elkeszul_idok[$i])."<br/>";

        if(getOra($osszido)>8){
            //echo "ez már nagyobb,mint 8 óra. összdio:".getOra($osszido)."<br/>";
            echo $i.". műszak:".$termeknevek[$i]."<br/>";
            echo "ennyi a fennmaradó az előző műszakból:".getOra($osszido-($i)*28800)."<br/>";
            array_push($masodikmuszak,$termeknevek[$i]);
        }else{
            echo ($i+1).". műszak:". $termeknevek[$i]."<br>";
            array_push($elsomuszak,$termeknevek[$i]);
            array_push($elsomuszak,$termeknevek[$i+1]);
        }*/
function getmuszak($osszido){
    $muszakszam = 0;
    // visszadja ,hogy ez pontosan hány műszak 
    $mennyiido = round((($osszido/(3600))/8),4);
    // ez pedig lefelé kerekítva megadja, hogy hány teljes műszak
    $hanyegesz = floor($mennyiido);
    // ha az egészen felül van maradék akkor az már a következő műszakhoz tartozik, így növelem a műszakok számát egyel.
    if($mennyiido==$hanyegesz){
        $muszakszam = $hanyegesz;
    }else if($mennyiido>$hanyegesz || $mennyiido >0){
        $muszakszam = ($hanyegesz+1);
    } 
    return $mennyiido.", hanyegesz: ".$hanyegesz.", muszakszam: ".$muszakszam;
}

function getIdo($mp){
    $ora = (int) floor($mp/3600);
    $perc =(int) (($mp/60)%60);
    $er = checkTime($ora).":".checkTime($perc);
    return $er;
}

function checkTime($x){
    if($x<10){
        $x = "0".$x;
    }
    return $x;
}
?>

<html>
    <head>
        <style>
            table{
                border-collapse:collapse;
                text-align:center;
            }
            
            table td,th{
                padding:10px;
                border:2px solid #000;
            }
            </style>
        </head>
    <body>
        
        <?php 
/*
            echo $res;
        
            echo "<p>osszido:".getIdo($osszido)."</p>";
            echo "<p>osszido:".getmuszak($osszido)."</p>";*/
        ?>
        </body>
    </html>