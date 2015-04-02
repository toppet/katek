<?php
include('config.php');
ini_set('default-charset','UTF-8');
setlocale(LC_ALL, 'en_EN');
session_start();

$loggedin_user=$_SESSION['login_user'];
$permission_level = $_SESSION['permission_level'];

if(!isset($loggedin_user) || !isset($_SESSION['permission_level'])){
    header("location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>KATEK Hungary Kft.</title>
        <link type="text/css" rel="stylesheet" href="css/header_design.css" />
        <script src="js/jquery-2.0.3.min.js"></script>
        <style>
            body{
                margin:0;
            }
            #page_wrapper{
                width:980px;
                margin:0 auto 0 auto;
                text-align:center;
            }
            #loading img{
                display:block;
                margin:0 auto 0 auto;
            }
            
            table{
                border-collapse:collapse;
                width:650px;              
            }
            
            table,td{
                padding:5px;
                text-align:center;
                color:#fff;
            }

            .header{
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
            .production_icon{
                width:50px;
            }
            #btnClear{
                margin-top:150px;
            }
            #clearDatabase{
                height:50px;
                background-color:gray;
                border:2px solid #123;
                color:#fff;
                font-size:15px;
                font-weight:bold;
                border-radius:25px;
                padding:15px;
            }
            #clearDatabase:hover{
                cursor:pointer;
                background-color:#555;
            }
            #clearDatabase:focus{
                outline:0;
            }
        </style>
        </head>
    <body>
        <?php include('header.php'); ?>
        
        <div id="page_wrapper">
            <p id="btnClear"><input type="button" id="clearDatabase" value="Clear Database Data"/></p>
            <!--<div id="loading"><img src="images/loading.gif" alt='loading' style="width: 50px;"/></div>-->
            <div id="adatbazis_adatok"></div>
        </div>
        
        <script>
            $(document).ready(function(){
               
               /*get_alldata();
               
                setInterval(function(){get_alldata()},150000);
                function get_alldata(){
                    $("#adatbazis_adatok").hide();
                    $("#loading").show();
                    
                    $.ajax({
                        url:'get_alldata.php',
                        type:'POST',
                        success: function(response){
                            $("#loading").hide();
                            $('#adatbazis_adatok').html(response);
                            $("#adatbazis_adatok").show();
                        }
                    });   
                }*/
                
               $('#clearDatabase').click(function(){
                 var clear = confirm("Biztos törli az adatbázis adatokat?");
                   
                   if(clear){
                       $.ajax({
                          url: 'truncate.php',
                           type: 'POST',
                           success: function(response){
                               alert("Adatbázis adatok törölve.");
                           }
                       });
                   }
               });
            });
        </script>
    </body>
    </html>
