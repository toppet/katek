<?php
session_start();

if(!isset($_SESSION['login_user']) || !isset($_SESSION['permission_level'])){
    header("location: index.php");
    exit();
}
$permission_level = $_SESSION['permission_level'];
?>
<html>
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
        <meta http-equiv="Content-Language" content="hu" />
        <title>Katek Hungary Kft.</title>
        <link rel="stylesheet" type="text/css" href="css/header_design.css" />
        <script src="js/jquery-2.0.3.min.js"></script>
        
        <style>
            body{
                margin:0;
            }
            #page_wrapper{
                width:980px;
                margin:0 auto;
                text-align:center;
            }
            
            #upload_form{
                margin:0 auto;
                text-align:center;
            }
            
            #fileToUpload{
                width:500px;
            }
        </style>
    </head>
    <body>
        <?php include("header.php") ?>
        <div id="page_wrapper">
            <h1>Upload SMT Output File</h1>
            <table id="upload_form">
                    <tr><td colspan='2'>Select SMT Output file to upload:</td></tr>
                    <tr><td><input type="file" name="fileToUpload" id="fileToUpload"></td></tr>
                    <tr><td><input type="submit" value="Upload" name="submit" id="submit"></td></tr>
            </table>
        </div>
        <script>
            $(document).ready(function(){
                $("#submit").click(function(){
                    event.preventDefault();
                    
                    var file_data = $('#fileToUpload').prop('files')[0];   
                    var form_data = new FormData();                  
                    form_data.append('file', file_data);
                    
                    $.ajax({
                        url:'output_upload.php',
                        type:'POST',
                        data:form_data,
                        dataType:'json',
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function(response){
                            
                            if(response.error != undefined){
                                alert(response.error);
                            }else{
                                alert(response.resp);    
                            }
                           
                            // a kiválaszott fájl ürítése a mezőből
                            $("#fileToUpload").replaceWith($("#fileToUpload").val('').clone( true ));
                        }
                   });
                });
            });
        </script>
    </body>
</html>