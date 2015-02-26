<?php
    /*$conn = mysqli_connect("192.168.0.12:3306","guest","GuestPass!","traceability"); // kapcsolódás az adatbázishoz
if($conn){
    echo 'Sikeres! :)';
}else{
    echo 'Sikertelen.... :(';
}*/

$conn2 = mysqli_connect('localhost','root','root') or die('Error connecting to MySQL server.'); 
 if(!$conn2){
     echo "szar";
 }else{
     echo "jo<br/>";
 }
     $email_query = "SELECT * FROM gyartas.termekek";
     $ress = mysqli_query($conn2,$email_query);
     
     while($row = mysqli_fetch_array($ress)){
         echo $row['email'];
     }
mysqli_close($conn2);
   
?>
