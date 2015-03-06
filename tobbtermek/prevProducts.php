<?php
    // Productlist.php alapján ellenőrizzük, hogy melyik termékről lehet emailt küldeni, illetve fájlba kiírni az eredményt
    // esetleges oldalfrissítés esetén.    

    $connect = mysqli_connect("localhost","root","");
    
    $create_database = "CREATE DATABASE IF NOT EXISTS prevdata";
       
    if(!mysqli_query($connect,$create_database)){
        echo 'hiba az adatbázis elkészítésében.<br/>';
        die();
    }
    
    $create_table = "CREATE TABLE IF NOT EXISTS prevdata.prev(
                       pid INT AUTO_INCREMENT NOT NULL,
                       smt INT NOT NULL,
                       product_code VARCHAR(35) NOT NULL,
                       quantity INT NOT NULL,
                       PRIMARY KEY(pid)
                    )";

    if(!mysqli_query($connect,$create_table)){
        echo 'hiba a tábla elkészítésében.<br/>';
        die();
    }

    $getData = "Select smt, product_code, quantity FROM prevdata.prev";

    $res = mysqli_query($connect,$getData);

    $inserted = 0;
    // Ellenőrzi az adatbázisban, hogy a megadott értékek szerepelnek-e már, ha igen akkor frissíti az értéket
    if(mysqli_num_rows($res) == 0 ){
        $insert_data = "INSERT INTO prevdata.prev (smt,product_code,quantity) VALUES ";

        for($i = 0; $i < count($productCodeArray); $i++){
            $insert_data .= "('".$id."','".$productCodeArray[$i]."','".$productQuantity[$i]."')";
            if($i != count($productCodeArray)-1){
                $insert_data .= ", ";
            }
           
        }
        if(!mysqli_query($connect,$insert_data)){
           echo "Hiba az előző termékek azonosító táblázat ellenőrzése közben.";
        }
        
    }else{

        while($row=mysqli_fetch_array($res)){
                $termekindex = 0;
                
            if($id == $row['smt']){
               
                $prev_code = $row['product_code'];
                $prev_quantity = $row['quantity'];
                
                if($prev_code != $productCodeArray[$termekindex]){
                    //($prev_code != $productCodeArray[$i] || $prev_quantity != $productQuantity[$i])){
                            //echo 'nem egyforma.</br>';
                            
                        $email_kiiras = TRUE; // a fájlba történő kiírás és email küldése esedékes.
               
                        $update_data = "UPDATE prevdata.prev SET product_code = '$productCodeArray[$termekindex]', quantity = '$productQuantity[$termekindex]' WHERE pid='".$termekindex."'"; // ez volt előtte product_code = '$prev_code'";
                        
                        if(!mysqli_query($connect,$update_data)){
                            die('hiba az adatbázis frissítésében.<br/>'); 
                        }else{
                            //echo 'egyforma.<br/>';
                            $email_kiiras = FALSE; // ne küldjön több emailt és ne írja ki többször a fájlba.
                        }
                        return FALSE;
                    }
                  $termekindex++;  
            
            } else{

                $inserted++;

                // Ha még nem illesztette be egyszer sem
                if($inserted==1){
                    
                    $insert_data2 = "INSERT INTO prevdata.prev (smt, product_code, quantity)
                                SELECT * FROM (SELECT '$id', '$productCode', '$givenValue') as tmp
                                WHERE NOT EXISTS (
                                    SELECT smt FROM prevdata.prev WHERE smt = '$id'
                                )";
                    mysqli_query($connect,$insert_data2);                   
                }  
            }
            
               
        }
    }
    
    // Ami eddig működött egy gyártósor alatt
    /*$adatok = mysqli_fetch_array(mysqli_query($connect,$getData));
    
    if($adatok == NULL){
        $insert_data = "INSERT INTO prevdata.prev (smt,product_code,quantity) VALUES ('$id','$productCode','$givenValue')";
        mysqli_query($connect,$insert_data);
    }else{
         $prev_code = $adatok['product_code'];
         $prev_quantity = $adatok['quantity'];
   
        if($prev_code != $productCode || $prev_quantity != $givenValue){
            $email_kiiras = TRUE; // a fájlba történő kiírás és email küldése esedékes.
            $update_data = "UPDATE prevdata.prev SET product_code = '$productCode', quantity = $givenValue WHERE smt='$id'"; // ez volt előtte product_code = '$prev_code'";
            if(!mysqli_query($connect,$update_data)){
               die('hiba az adatbázis frissítésében.<br/>'); 
            }
        }else{
            $email_kiiras = FALSE; // ne küldjön több emailt és ne írja ki többször a fájlba.
        }
    }*/
    mysql_close($connect);
?>