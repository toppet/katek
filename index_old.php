<?php

include("config.php");
session_start();

if($_SERVER["REQUEST_METHOD"] == "POST")
{
// username and password sent from form 

$myusername=addslashes($_POST['username']); 
$mypassword=addslashes($_POST['password']); 

$sql="SELECT id FROM users WHERE username='$myusername' and password='".md5($mypassword)."'";
$result=mysql_query($sql);
$row=mysql_fetch_array($result);

$count=mysql_num_rows($result);

if($count==1){

$_SESSION['loggedin'] = true;
$_SESSION['login_user'] = $myusername;

header("location: lineSelection.php");
}else{
$error="Username or Password is invalid";
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
<div id="header">Production Login</div>
<form action="" method="post">
<table>
<tr>
<td><label>Username: </label></td>
<td><input type="text" name="username" id="username" class="box"/></td>
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
<div id="error_div"><?php echo empty($error)?"":$error; ?></div>
</div>
</div>
</body>
</html>
