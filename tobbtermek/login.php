<?php

$mysql_hostname = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_database = "users";

$db = mysqli_connect($mysql_hostname, $mysql_user, $mysql_password, $mysql_database) or die("Error during connection.");

session_start();

// Ha a felhaszáló már be van jelentkezve akkor átirányítom a főoldalra
if(isset($_SESSION['loggedin']) && $_SESSION['permission_level'] == '1'){
    header('location: index.php');
    exit();
}else if(isset($_SESSION['loggedin']) && $_SESSION['permission_level'] == '2'){
    header('location: lineSelection.php');
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // username and password sent from form 

    $myusername=addslashes(filter_input(INPUT_POST,'username',FILTER_SANITIZE_STRING)); 
    $mypassword=addslashes(filter_input(INPUT_POST,'password',FILTER_SANITIZE_STRING)); 

    $sql="SELECT id,permission FROM users WHERE username='".$myusername."' and password='".md5($mypassword)."'";
    
    $result=mysqli_query($db,$sql);

    while($row=mysqli_fetch_array($result)){
        $permission_level = $row['permission'];
    }
    
    $count=mysqli_num_rows($result);

    if($count==1){
        
        $_SESSION['loggedin'] = true;
        $_SESSION['login_user'] = $myusername;
        $_SESSION['permission_level']=$permission_level;
        
        //ellenőrzöm a felhasználói szintet, és aszerint adom meg a jogokat a használathoz.
        switch($_SESSION['permission_level'] ){
            case '3':
                header('location: lineSelection.php');
                exit();
                break;
            case '2':
                header('location: index.php'); //csökkentett jogosultságokkal ellátott felhaszáló 
                exit();
                break;
            case '1':
                header("location: index.php");
                exit();
                break;
        } 
    }else{
        $error="Username or Password is invalid!<br/>";
    }
}
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<title>KATEK - Login</title>
    <script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>

    <script>
        $(document).ready(function () {
            $('#username').focus();
        });
    </script>
<style type="text/css">
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
    }

    #wrapper{
        text-align: center;
        width: 980px;
        margin: 20% auto 0 auto;
    }
    
    #login_box{
        width:300px;
        border: solid 1px #0367b3;
        text-align: left;
        margin: 0 auto 0 auto;
    }
    
    .box {
        border: 1px solid #0367b3;
    }
    
    #header{
        background-color:#0367b3;
        color:#FFFFFF;
        padding:3px;
        font-weight: bold;
    }

    form{
        padding: 30px;
    }
       
    label {
        font-weight: bold;
        width: 100px;
        font-size: 14px;
        color: #0367b3;
    }

    #error_div{
        font-size: 11px;
        color: #f00;
        text-align: center;
        margin-top: -25px;
        padding-bottom: 10px;    
    }
    
    #submit_image{
        width: 75px;
    }
</style>
</head>
<body>

<div id="wrapper">
    <div id="login_box">
        <div id="header">Production Check Login</div>
                <form action="" method="post">
                    <table>
                        <tr>
                            <td><label>Username: </label></td>
                            <td><input type="text" name="username" id="username" class="box" autocomplete="off"/></td>
                        </tr>
                    <tr>
                        <td><label>Password: </label></td>
                        <td><input type="password" name="password" id="password" class="box" /></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center;"><input id="submit_image" type="image" src="images/login.png"/></td>
                    </tr>
                    
                    </table>
                </form>
        <div id="error_div"><?php echo (empty($error))?'':$error; ?></div>
    </div>
</div>
</body>
</html>
