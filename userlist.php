<?php
$conn = mysqli_connect("localhost","root","","users") or die("Oops can't connect to database");
ini_set("default_charset","UTF-8");

$result = mysqli_query($conn,"SELECT * FROM users");

$output = "<table><th style='display:none;'>User id</th><th>Felhasználónév</th><th>Email</th><th>Group</th><th>Permission Level</th><th>Felhasználó törlése</th><th>Adatok módosítása</th>";

while($row = mysqli_fetch_array($result)){

    $output .= "<tr>
                    <input type='hidden' class='user_id' value='".$row['id']."' />
                    <td class='user_name'>".$row['username']."</td>
                    <td class='user_email'>".$row['email']."</td>
                    <td class='user_group'>".$row['group_name']."</td>
                    <td class='user_permission'>".$row['permission']."</td>
                    <td><span class='delete'>törlés</span></td>
                    <td><a href='#' class='modify'>módosítás</a></td>
                </tr>";
}

$output .= "<tr><td colspan='6' style='text-align:center; padding:15px;'><input type='button' id='newUser' value='Add New User'></td></tr>";
$output .= "</table>";

echo $output;
?>