<?php

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
<title>KATEK Hungary Kft.</title>
<link type="text/css" rel="stylesheet" href="css/header_design.css" />
<script src="js/jquery-2.0.3.min.js"></script>
<script>
$(document).ready(function(){
    $.ajax({
        url:"get_creation_time.php",
        type:"POST",
        dataType:"json",
        success: function(response){
           if(response.refresh == true){
               alert("frissiteni kene...");
           }
            //alert(response.refresh);
        }
    });
});

</script>
<style>
body{
margin:0;
}
#page_wrapper{
min-width:1350px;
width:75%;
margin:25px auto 0 auto;
}
#loading img{
display:block;
margin:0 auto 0 auto;
}

table{
min-width:600px;
border-collapse:collapse;
width:45%;              
}

table,td{
padding:5px;
text-align:center;
color:#fff;
}

.line_header{
border-top-left-radius: 20px;
border-top-right-radius: 20px;
background-color: #eee;
padding:5px;
font-size: 20px;
}

table th{
background-color:#bbb;
padding: 5px;
}

.header, table th{
color:#000;
}

table tr:nth-child(even){
background-color:#1e81cc;
}

table tr:nth-child(odd){
background-color:cornflowerblue;
}
#line_1{
display:block;
top:0;
float:left;
}
#line_2{
float:right;

}
#line_3{
float:left;
}
#line_4{
float:right;
}

#line_1,#line_2{
margin-bottom:25px;
}
#loading_gif{
display:table-cell;
margin:10% auto 0 auto;

width: 50px;
}
#time_interval{
text-align:center;
}

</style>
</head>
<body>
<div id="header"><img src="images/katek_white.png" alt="katek"/></div>
<div id="page_wrapper">
<div id="adatbazis_adatok"><?php include_once("get_alldata.php"); ?></div>
<div id="szamlalo"></div>
</div>
</body>

</html>
