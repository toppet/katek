<?php
session_start();
if(!isset($_SESSION['login_user'])){
    header("location: index.php");
    exit();
}
?>
<html>
    <head>
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.4.min.js"></script>
        <title>User Management</title>
        <link type="text/css" href="css/header_design.css" rel="stylesheet"/>
        <style>
            body{
                text-align:center;
                margin:0; 
            }
            
            #page_wrapper{
                margin-top:25px;
            }
            
            table{
                margin: 0 auto 0 auto;
            }
            
            table th{
                border: 2px solid black;
                padding: 5px;
            }
            
            table{
                border-collapse: collapse;
                text-align: center;
            }
            
            table td{
                padding:5px;
            }
            
           #userlist tr:nth-child(even) {
                background: #bbb;
            }
            #userlist tr:last-child{
                background:#fff;
            }
            
            .delete{
                color:blue;
            }
            
            .delete:hover{
                cursor:pointer;
                text-decoration:underline;
                color:red;
            }
            
            #header {
                margin: 0;
                padding: 0;
                background-color: #1e81cc;
                width: 100%;
                text-align: center;
            }
            
            #header img{
                margin: 0 auto 0 auto;
                padding-top: 5px;
                width: 200px;
            }

            #login{
                text-align: center;
                color: #fff;
                position: absolute;
                margin: 10px 10px 0 0;
                right: 0;
                font-size: 75%;
            }
            
            #login a{
                color: #fff;
                text-decoration: none;
            }
            
            #login a:hover{
                text-decoration: underline;
            }
            
        </style>
    </head>
    <body>
        <?php include_once('header.php'); ?>
        
        <div id="page_wrapper">
           
<?php
    ini_set('default_charset', 'UTF-8');

    $error = '';
            ?> <div id="user_list">
            </div> <!-- //user_list -->
            
            <div id="addNewUser" style="display: none; width:500px; margin:50px auto 0 auto;">
                
                <form id='newuserform' action="" method="post">
                    <table>
                        <tr><th colspan='3' id="userBox">Add New User</th></tr>
                        <tr><td>Username: </td><td><input type="text" name="username" id="username" class='forminput' autocomplete="off"/></td></tr>
                        <tr><td>Password:</td><td><input type="password" id="password" name="password" class='forminput'/></td></tr>
                        <tr><td>Email: </td><td><input type="text" name="email" id="email" class='forminput' autocomplete="off"/></td></tr>
                        <tr><td>Group</td>
                            <td>
                                <select class="forminput" id="group">
                                    <option></option>
                                    <option value='Production'>Production</option>
                                    <option value='Management'>Management</option>
                                    <option value='Quality'>Quality</option>
                                    <option value='Logistics'>Logistics</option>
                                    <option value='Project Manager'>Project Manager</option>
                                    <option value='Warehouse'>Warehouse</option>
                                </select>
                        <tr><td>Permission level</td>
                            <td>
                                <select class="forminput" id='permission_level'>
                                    <option></option>
                                    <option value='3'>3 - view</option>
                                    <option value='2'>2 - display+edit</option>
                                    <option value='1'>1 - admin</option>
                                </select>
                            </td>
                            <td><img id="help" style='width:25px;' src='images/help.png' alt='' title=''></td>
                        </tr>
                        <tr><td colspan="3" id='error'></td></tr>
                        <tr><td colspan="3"><input type='submit' id='submit_newuser' value='Submit'/><input type="hidden" name="submitted"/></td></tr>
                    </table>
                </form>
            </div>
            <div class='resp'><?php echo $error; ?></div>
        </div>
        
        <script>
            $(document).ready(function () {
                var resp =  <?php echo json_encode($error); ?>;
                
                $('.resp').html(resp); // az adatbázis fájlból érkező válaszüzenet
               
                getUserList(); // felhasználók listájának lekérdezése
                
/* ----- Addnew user gomb megnyomásakor ----- */     
                
                $(document).on('click','#newUser',function () {
                    $(".forminput").each(function(){
                        $(this).val('');
                    });
                    $('#modifyUserData').attr('id','addNewUser');
                    $('#modifyuserform').attr('id','newuserform');
                    $("#userBox").html('Add New User');
                    $("#error").html('');
                    $("#addNewUser").after($('user_list')).fadeIn();
                    
                    $("#username").parent().html('<input type="text" name="username" id="username" class="forminput" autocomplete="off"/>');
                    $("#username").focus();
                    
                    $('#password').parent().parent().show(); // a password megjelenítése
                    $('#password').attr('class','forminput');
                    $('.resp').html('');
                });
                
/* ----- Új felhasználó hozzáadása ----- */
                
                $('#addNewUser select').change(function(){
                    var optionSelected = $("option:selected", this);
                    var valueSelected = this.value;
                    checkData();
                    switch(valueSelected){
                            case '1':
                                $('#help').attr('title','This user has admin level permissions, can add/edit users');
                                break;
                            case '2':
                                $('#help').attr('title','This user can edit and display SMT line attributes.');
                                break;
                            case '3':
                                $('#help').attr('title','This user can only view SMT line properties.');
                                break;
                            default:
                                $("#help").attr('title','');
                                break;
                    }
                });

/* ----- Az új felhasználó adatainak felvitele ----- */

                $('#submit_newuser').click(function(e){
                    e.preventDefault();
                    
                    if(!checkData()){
                        return false;
                    }else{
                        
                        if($('#userBox').html()=="Add New User") {

                            $.ajax({
                                url:'addnewuser.php',
                                type:'POST',
                                data: 'username='+$('#username').val()+"&password="+$('#password').val()+"&email="+$("#email").val()+"&group_name="+$("#addNewUser #group").attr('selected','selected').val()+"&permission="+$('#addNewUser #permission_level').attr('selected','selected').val(),
                                success:function(response){
                                    $("#addNewUser").css('display','none');
                                    $(".resp").html(response);
                                    getUserList();
                                }
                            }); 
                        }else if($('#userBox').html()== "Modify User Data"){
                            
                            // Ellenőrzöm, hogy az "új" adatok eltérnek-e az előzőektől, ha igen csak akkor módosítom az adatbázist.
                            if(checkDifference($('#rowindex').html())){
                                //ha nincs változás akkor csak simán eltüntetem a formot
                                $('#modifyUserData').hide();
                            }else{ 
                            
                                    $.ajax({
                                        url:'modifyuser.php',
                                        type:'POST',
                               data:'id='+$('#uid').html()+'&username='+$('#username').html()+"&password="+$('#password').val()+"&email="+$("#email").val()+"&group_name="+$("#modifyUserData #group").attr('selected','selected').val()+"&permission="+$('#modifyUserData #permission_level').attr('selected','selected').val(),
                                        success:function(response){
                                            $("#addNewUser").css('display','none');
                                            $(".resp").html(response);

                                            getUserList($('#rowindex').html());
                                        }
                                    });
                                    $('#modifyUserData').hide();
                           }
                        }
                    }
                });
                
/* ----- Felhasználó törlése ----- */   
                
                $(document).on('click','.delete',function(){

                    var u_id = $('.delete').index(this);

                    var answer = confirm("Biztos hogy törölni akarja: "+$('.user_name:eq('+u_id+")").html()+"?");
                    if(answer){
                        $.ajax({
                            url:'deleteuser.php',
                            data: "user_id="+ $('.user_id:eq('+u_id+")").val()+"&user_name="+$('.user_name:eq('+u_id+")").html(),
                            type:"POST",
                            success: function(response){
                                $(".resp").html(response);
                                getUserList();
                                $("#addNewUser").hide();
                                $("#modifyUserData").hide();
                            }
                        });
                    }
                });
                
/* ----- Felhasználó adatainak módosítása ----- */
        
                $(document).on('click','.modify',function(){
                    var index = ($('.modify').index(this));
                
                   $("#addNewUser").fadeIn('fast');
                    $("#modifyUserData").css('display','block');
                    $(".resp").html('');
                    $("#error").html('');
                    $('#addNewUser').attr("id","modifyUserData");
                    $("#newuserform").attr('id','modifyuserform');
                    $('#userBox').html('Modify User Data');
                    $('#username').parent().html("<span id='username'>"+$('.user_name:eq('+index+')').html()+"</span>");
                    // A password mező elrejtése
                    $('#password').attr('class','');
                    $('#password').parent().parent().hide();
                    // ------ //
                    $('#email').val($('.user_email:eq('+index+')').html());
                    $("#group").val($('.user_group:eq('+index+')').html());
                    
                    $('#permission_level').val($('.user_permission:eq('+index+')').html()); // beállítás az adott option value-ja alapján
                    switch($('#permission_level').attr('selected','selected').val()){
                        case '1':
                            $('#help').attr('title','This user has admin level permissions, can add/edit users');
                            break;
                        case '2':
                            $('#help').attr('title','This user can edit and display SMT line attributes.');
                            break;
                        case '3':
                            $('#help').attr('title','This user can only view SMT line properties.');
                            break;
                        default:
                            $("#help").attr('title','');
                            break;
                    }
                    // hozzáadom az elküldendő user_id-t
                    $('#username').parent().append('<span id="uid" style="display:none">'+$('.user_id:eq('+index+')').val()+'</span>');
                    $('#username').parent().append('<span id="rowindex" style="display:none">'+($(".modify").index(this)+1)+'</span>');
                   
                });

/* ---- Megadott adatok ellenőrzése ----- */
                
                function checkData(){
                    var jodb = 0;
                    
                    $('.forminput').each(function(){
                       if($(this).val() == "" || $(this).attr('selectedIndex')== 0){
                            $(this).css('border','2px solid red');
                           $(this).focus();
                           $('#error').html('nem lehet üres!');
                           jodb--;
                           return false;
                       }else{
                           $(this).css('border','');   
                       }
                        jodb++;
                    });
                    
                    if(jodb == $(".forminput").length){
                        $("#error").html("");
                        return true;
                    }else{
                        return false;
                    }
                }
                
/* ----- Eltérő adatok ellenőrzése ----- */
                function checkDifference(rowindex){
                    
                    var i=0;
                   /* $('#user_list tr:eq('+rowindex+')').find('[class*="user"]').each(function(e){
                        alert($(this).html());
                        var new_data;
                        var old_data;
                        
                        if(i==0){
                            new_data = $(this).val());
                            old_data = $('#modifyUserData')
                        }else{
                            new_data = $(this).html());
                        }
                        i++;
                    });*/
                    var egyformae = true;
                    for(var i= 0;i < $('.forminput').length;i++){
                        
                        if($('.forminput:eq('+i+')').val() != $('#user_list tr:eq('+rowindex+')').find('[class*="user"]:eq('+(i+2)+')').html() ){
                            egyformae = false;
                        }
                    }
                    return egyformae;
                }
                
/* ----- Felhasználók listájának lekérdezése ----- */
                
                function getUserList(index){

                    $.ajax({
                        url:'userlist.php',
                        type:"POST",
                        success:function(response){
                            $('#user_list').html(response);
                            if(index!="undefined"){
                                $("#user_list table tr:eq("+index+")").effect('highlight',{color:"#0f0"},1500); 
                            }
                            
                        }
                    });
                }
/* --------------------------- */
            }); /* ----- document.ready ----- */
        </script>
        
    </body>
</html>