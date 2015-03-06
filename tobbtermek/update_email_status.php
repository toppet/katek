<?php
  
   $update_query = "UPDATE gyartas.termekek SET email = '1' WHERE product_code = '$product_code'";
  
   $update_result = mysqli_query($conn2,$update_query);
   
    if(!update_result){
       $responseText .= "námjó";
    }

    /*$row = mysqli_fetch_array($update_result);

    $emailElkuldve = $row['email'];


    mysql_close($conn2);
    */
?>
