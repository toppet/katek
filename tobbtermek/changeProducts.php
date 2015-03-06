<?php

session_start();
include 'simpleXLSX/simplexlsx.class.php';    
ini_set("default_charset","utf-8");

if(!isset($_SESSION['loggedin'])){
    header("location: login.php");
    exit();
}

/* @var $_GET type */
$id = filter_input(INPUT_GET,'id',FILTER_SANITIZE_STRING);

if(!isset($id)){
    echo "Missing argument(s)... <a href='index.php'>Back to homepage</a>";
    die();
}

$conn = mysqli_connect("localhost","root",""); // kapcsolódás az adatbázishoz
if(!$conn){
    echo 'productlist.php - Sikertelen csatlakozas...';
    die();
}

$query = "SELECT * from gyartas.termekek WHERE smt='".$_GET['id']."'";

$result = mysqli_query($conn,$query);


$xlsx = new SimpleXLSX('SMT_OUTPUT/SMT Output.xlsx');

// output worksheet 1
list($num_cols, $num_rows) = $xlsx->dimension();

$code = array();
$nev = array();
$norma = array();

/*$tht = array();
$ict = array();
$fct = array();
$assembling = array();*/


foreach( $xlsx->rows() as $r ) {
        $adat = (substr($r[0],-3) == 'SMD')?substr($r[0],0,-3):$r[0]; //Ha a kód végén ott van, hogy 'SMD', levágjuk.
        array_push($code,$adat);
        array_push($nev,$r[1]);
        array_push($norma,$r[2]);
    
        /*array_push($tht,$r[4]);
        array_push($ict,$r[5]);
        array_push($fct,$r[6]);
        array_push($assembling,$r[7]);*/
}

$hossz= count($code); // terméktömb hossza

$er ='';

$i = 1;

while($row = mysqli_fetch_array($result)){
    $er .= "<table  class='ui-state-default'>";
    $er .= "<tbody class='product".$i."'>";
    $er .= "<tr><th colspan='2' class='product_num'>Product ".$i."</th><th colspan='2'><img class='del_prod' src='images/red_close.png' alt='delete product'/></th></tr>";

    $er .= "<tr><td>Product Code</td><td class='editable_productcode'>".$row['product_code']."</td><input type='hidden' class='masiknorma' value='".$row['norma']."'/></tr>";
    $er .= "<tr><td>ProductName:</td><td class='product_name'>".$row['product_name']."</td></tr>";
    $er .= "<tr><td>Quantity</td><td class='editable_quantity'>".$row['quantity']."</td><td colspan='2' class='quantity_check'></td></tr>";
    $er .= "<tr><td>Production Order</td><td class='editable_po'>".$row['po']."</td><td colspan='2' class='po_check'></td></tr>";
    $er .= "<tr><td>Sample Production:</td><td>";
    
    if($row['sample_production'] == 1){
        $er .= "<input type='checkbox' class='sample' name='sample' checked='checked'/></td></tr>";
    }else{
        $er .= "<input type='checkbox' class='sample' name='sample' /></td></tr>";
    }
    $er .= "</td></tr>";
    $er .="<tr><td colspan='3' class='error'></td></tr>";
    $er .="</tbody></table>";
    $i++;
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Change Productlist Details</title>
        <meta charset="utf-8" />
        <link type="text/css" rel="stylesheet" href="css/header_design.css" />
        <script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.10.4.min.js"></script>
        <style>
            body{
                margin: 0;
                padding: 0;
                font-family: 'Levenim MT';
            }
            #page_wrapper{
                text-align: center;
                width: 980px;
                margin: 15px auto 0 auto;
            }
            table{
                margin: 0 auto 0 auto;
                margin-bottom: 15px;
            }
            table,th,td{
                /*border: 1px solid #000;*/
                border-collapse: collapse;
                padding: 5px;
                text-align: center;
                width: 650px;
            }
            
            .ui-autocomplete {
                background: #fff;
                border: 1px solid #000;
                list-style: none;
                width: 150px;
                height: 150px;
                overflow-y: scroll;
                overflow-x: hidden;
                margin: 0;
                padding: 0;
            }
            
            .ui-autocomplete li:hover{
                cursor: pointer;
            }
            
            .ui-helper-hidden-accessible{
                display: none;
            }
            
            .autocomplete{
                border: none;
                border-bottom: 1px solid #1e81cc;
                background: transparent;
                box-shadow: none;
            }
            .autocomplete:focus{
                outline: none;
            }
            
           .product_num{
             
                background-color: #eee;
            }
            
            #sortable{
                width:100%;
            }
            #sortable:hover{
                cursor: pointer;
            }
            #sortable tr:hover {
                background-color: #eee;
            }
            
            .editable_productcode, .editable_quantity, .editable_po{
                cursor: pointer;
            }
            
            .product_num:hover{
                cursor: move;
            }
            
            textarea{
                resize: none;
            }
            
            .del_prod{
                width:25px;
                /*display:block;*/
                float:right;
            }
            
            .del_prod:hover{
                cursor:pointer;
            }
            
            .quantity_check img, .po_check img{
                width:25px;
            }
            
            #addproduct{
                margin-right:6.5em;
            }
            
            #submit{
                margin-bottom: 2em;
            }
            #submit:hover{
                cursor:pointer;
            }
        </style>
        <script>
                var sor = <?php echo json_encode($id); ?>;
                var code = <?php echo json_encode($code); ?>;
                var nev = <?php echo json_encode($nev); ?>;
                var norma = <?php echo json_encode($norma);?>;
                
                /*var php_tht = <?php echo json_encode($tht); ?>;
                var php_ict = <?php echo json_encode($ict); ?>;
                var php_fct = <?php echo json_encode($fct); ?>;
                var php_assembling = <?php echo json_encode($assembling); ?>;*/

                
        </script>
        <script>
            $(document).ready(function () {
                
                var termekkod = new Array();
                var termeknev = new Array();
                var masiknorma = new Array();
                var termekdb = new Array();
                var po = new Array();
                var elkeszules = new Array();
                
                var sampleproduction = [];
                /*var tht = new Array();
                var ict = new Array();
                var fct = new Array();
                var assembling = new Array();*/
                

                $('.sortable').sortable({
                    handle: '.product_num',

                    update: function (event, ui) {
                        /*var i = 1;
                        $(".product_num").each(function () {
                            $(this).html('Product ' + i+"<img class='del_prod' src='images/red_close.png' alt='delete product'/>");
                            i++;
                        });
                        i = 1;
                        var table_index = ui.item.index() + 1;

                        $("table").each(function(){
                            $(this).find('tbody').attr('class', 'product' + i);
                            //$(this).find('product' + i + '_quantity').attr('class', 'product' + i);
                            i++;
                        });*/
                        
                        updateIndex();
                    },
                    items: 'table'
                });

                function updateIndex() {
                    var i = 1;
                    
                    $('.product_num').each(function(){
                        $(this).html('Product ' + i);
                        i++;
                    });
                    
                    i = 1;
                    $("table").each(function(){
                            $(this).find('tbody').attr('class', 'product' + i);
                            i++;
                        });
                }


                $(document).on('dblclick', '.editable_productcode', function (e) {

                    $(this).html('<input type="text" class="auto" />');
                    $(this).find('.auto').focus();
                    var input_index = $('.editable_productcode').index(this) + 1;
                    
                    /*$(document).keypress('.product'+ input_index + ' .auto',function(e){
                        if (e.which == 13 || e.keyCode == 13) {
                            //alert($('.product'+ input_index + ' .auto').attr('class'));
                            $('.product'+ input_index + ' .auto').val('');
                            $('.product'+ input_index + ' .auto').css('border','2px solid red');
                            $('.product'+ input_index + ' .auto').focus();
                            $('.product'+ input_index + ' .error').html('Invalid data');

                            return false;
                        }   
                    });*/
                    
                    //alert(input_index);
                    $(this).find(".auto").autocomplete({
                        source: code,
                        max: 2,
                        minChars: 3,
                        select: function (event, ui) {
                            code_index = ($.inArray(ui.item.value, code));
                            //alert("index: " + index + ", nev: " + nev[index]);
                            if(code_index == -1){
                                $(this).val('');
                                return false;
                            }
                            $('.product' + input_index + ' .product_name').html(nev[code_index]);
                            $('.product' + input_index + ' .masiknorma').val(norma[code_index]);
                            
                            $('.product' + input_index + ' .editable_productcode').html(ui.item.value);
                            $('.product' + input_index + ' .editable_quantity').html("<input class='product" + input_index + "_quantity' type='text'>");
                            $('.product' + input_index + ' .editable_quantity input').focus();

                           /* $(document).on('keypress','.product' + input_index + '_quantity', function (e) {
                                if (e.which == 13 || e.keyCode == 13) {
                                    var c = $(this).val();
                                    
                                    if(!validate(input_index,c)){
                                        return false;
                                    }else{
                                        $('.product' + input_index + ' .editable_quantity').html(c); 
                                        $('.product' + input_index + ' .error').html('');
                                        $('.product' + input_index + '_po').focus();
                                    }
                                    return false;
                                }
                                return true;
                            });*/

                            $('.product' + input_index + ' .note').text('');
                        }
                    });
                });

                
                $(document).on('dblclick', '.editable_quantity', function () {
                    var index = $('.editable_quantity').index(this) + 1;
                   
                    $(this).html("<input class='product" + index + "_quantity' type='text'>");
                    
                    $('.product' + index + '_quantity').focus();

                        $('.product'+index+' .error').html('');
                        return true;
                   
                    $('.product' + index + '_quantity').on('focusout',function(){
                        var ertek = parseInt($(this).val());
                    
                        if(!validate(index,ertek)){
                            return false;
                        }else{
                            $('.product' + index + ' .editable_quantity').html(ertek);    
                        }
                    });
                    
                    inputEll();
                });
                
                // a megadott érték csak szám lehet
                $(document).on('input','.editable_quantity', function (event) {
                    $(this).find('input').val(function (i,v){
                        return v.replace(/[^0-9]/g, '');
                    }); 
                });
                
                $(document).on('keypress','.editable_quantity input', function (e) {
                    
                    var input_index = $('.editable_quantity').index($(this).parent())+1;
                   
                    if (e.which == 13 || e.keyCode == 13) {
                        var c = $(this).val();

                        if(!validate(input_index,c)){
                            return false;
                        }else{
                            $('.product' + input_index + ' .editable_quantity').html(c); 
                            $('.product' + input_index + ' .error').html('');
                            $('.product' + input_index + '_po').focus();
                        }
                        return false;
                    }
                    return true;
                });

                /*$(document).on('input','.editable_quantity input',function (e) {
                    
                    var index = $('.editable_quantity').index()+1;
                    
                    if (e.which == 13 || e.keyCode == 13) {
                        
                        alert(index);
                        var ertek = parseInt($(this).val());
                        
                        if(!validate(index,ertek)){
                            return false;
                        }else{
                            $(this).html(ertek);    
                        }
                    }
                    return true;
                });*/
                
// PRODUCTION ORDER ------
                $(document).on('dblclick', '.editable_po', function () {
                    var index = $('.editable_po').index(this) + 1;

                    $(this).html("<input class='product" + index + "_po' type='text'>");

                    $('.product' + index + '_po').focus();

                    $('.product'+index+' .error').html('');
                    return true;

                    $('.product' + index + '_po').on('focusout',function(){
                        var ertek = parseInt($(this).val());

                        if(!validate(index,ertek)){
                            return false;
                        }else{
                            $('.product' + index + ' .editable_po').html(ertek);    
                        }
                    });

                    inputEll();
                });
                
                $(document).on('keypress','.editable_po',function (e) {

                    var index = $('.editable_po').index(this)+1;
                    if (e.which == 13 || e.keyCode == 13) {
                        var ertek = $(this).find('input').val();

                        if(!validate_PO(index,ertek)){
                            return false;
                        }else{
                            $(this).html(ertek); 
                        }
                    }
                });

                function validate(index,value){
            
                    if(isNaN(value) || value > 100000 || value == 0){
                        $('.product'+index +' .error').html('Can\'t be bigger than 100000');
                        $('.product'+index+'_quantity').val('');
                        $('.product'+index+'_quantity').focus();
                        $('.product'+index+'_quantity').css('border','2px solid red');
                        $('.product'+index +' .quantity_check').html('');
                        return false;
                    }
                    $('.product'+index +' .error').html('');
                    $('.product'+index +' .quantity_check').html('<img src="images/check.png" alt="ok">');
                    return true;
                }
                
                function validate_PO(index,value){
                    if(value.length > 7 || value.length<7){
                        $('.product'+index +' .error').html('The PO number must be 7 character');
                        $('.product'+index+'_po').val('');
                        $('.product'+index+'_po').focus();
                        $('.product'+index+'_po').css('border','2px solid red');
                        $('.product'+index +' .po_check').html('');
                        return false;
                    }else{
                        $('.product'+index +' .error').html('');    
                        $('.product'+index +' .po_check').html('<img src="images/check.png" alt="ok">');   
                        return true;    
                    }
                }
                

                $("#addproduct").click(function (e) {
                    
                    e.preventDefault();
                    
                    if (!ellenorzes()) {
                        return false;
                    }
                    
                    var db = $('.product_num').length;

                    var uj = "<table class='ui-state-default'>";
                    uj += "<tbody class='product" + (db + 1) + "'>";
                    uj += "<tr><th colspan='2' class='product_num'>Product " + (db + 1) + "</th><th colspan='2'><img class='del_prod' src='images/red_close.png' alt='delete product'/></th></tr>";
                    uj += "<tr><td>Product Code</td><td class='editable_productcode'><input type='text' class='product" + (db + 1) + "_quantity'/></td><input type='hidden' class='masiknorma' value=''/></tr>";

                    uj += "<tr><td>Product Name</td><td class='product_name'></td></tr>";
                    uj += "<tr><td>Quantity</td><td class='editable_quantity'></td><td colspan='2' class='quantity_check'></td></tr>";
                    uj += "<tr><td>Production Order No.</td><td class='editable_po'><input type='text' class='product" + (db + 1) + "_po'/></td><td colspan='2' class='po_check'></td></tr>";
                    uj += "<tr><td>Sample Production</td><td><input type='checkbox' class='product" + (db + 1) + "_sample_production'/></td></tr>";
                    uj += "<tr><td colspan='3' class='error'></td></tr>";
                    uj += "</table>";
                    $(uj).insertBefore(this);
                    $('.product' + (db + 1) + '_quantity').trigger('dblclick');
                    $('.product' + (db + 1) + ' .auto').focus();
                    $(".sortable").sortable('refresh');
                });

                $('#removeproduct').click(function (e) {
                    e.preventDefault();
                    var tablaindex = parseInt(($('table').length) - 1);

                    if (tablaindex <= 0) {
                        return false;
                    }

                    $('table:eq(' + tablaindex + ')').remove();
                    termekkod.pop()
                    termeknev.pop();
                    termekdb.pop();
                   
                    //$('.ui-state-default:eq(' + parseInt($('table').length) + ')').remove();
                });
                
                $(document).on('click','.del_prod',function(){
                    var del_index = $(".del_prod").index(this); 

                    $('.ui-state-default:eq('+del_index+')').remove();
                    updateIndex();
                });

                $('#submit').click(function (e) {
                    e.preventDefault();
                    
                    if(!ellenorzes()){
                        return false;
                    }
                    
                    for (var i = 1; i <= $('table').length; i++) {
                        
                        termeknev[i - 1] = $('.product' + i + ' .product_name').html();
                        termekkod[i - 1] = $('.product' + i + ' .editable_productcode').html();
                        termekdb[i - 1] = $('.product' + i + ' .editable_quantity').html();
                        po[i-1] = $(".product"+i+" .editable_po").html();
                        masiknorma[i-1] = $('.product' + i + ' .masiknorma').val();
                        
                        /*tht[i-1] = (php_tht[i]=='')?"0":php_tht[i];
                        ict[i-1] = (php_ict[i]=='')?"0":php_ict[i];
                        fct[i-1] = (php_fct[i]=='')?"0":php_fct[i];
                        assembling[i-1] = (php_assembling[i]=='')?"0":php_assembling[i];*/
                        
                        var elkeszul = (28800 / masiknorma[i-1] * termekdb[i-1]).toFixed(3);
                       
                        elkeszules[i-1] = (elkeszul);
                        sampleproduction[i-1] = ($('.product'+i+' .sample').is(':checked')==true)?1:0;  
                    }
                    
                    var termekTomb = [];

                    for (var i = 0; i < $('table').length; i++) {
                        //termekTomb[i] = termeknev[i] + ', ' + termekkod[i] + ', ' + masiknorma[i] + ', '+ tht[i]+', '+ict[i]+', '+fct[i]+', '+assembling[i]+', '+ termekdb[i] + ', ' + po[i] + ", " + elkeszules[i];
                        termekTomb[i] = termeknev[i] + ', ' + termekkod[i] + ', ' + masiknorma[i] + ', '+ termekdb[i] + ', ' + po[i] + ", " + elkeszules[i]+', '+sampleproduction[i];
                    }
                    
                    var datastring = "product_name="+termeknev+"&product_code="+termekkod+"&norma="+masiknorma+"&quantity="+termekdb+"&po="+po+"&elkeszul="+elkeszules;
                    
                    $.ajax({
                        url: 'upload.php',
                        type: 'POST',
                        data: { 'data': termekTomb, 'sor': sor },
                        success: function (response) {
                            // Submit event indítása, ha befejeződött az ajax kérés
                            alert(response);
                        }
                    });
                    console.log(datastring);
                });
                

                // ------------------ ELLENŐRZÉS ---------------------

                function ellenorzes() {
                    
                    for (var i = 1; i <= $('.product_num').length; i++) {
                        
                        if($('.product'+i+' .editable_productcode span').html() != undefined){ //Product Code

                            var kod = $('.product'+i+' .editable_productcode input').val()
                            var code_index = ($.inArray(kod, code));

                            if(code_index == -1){
                                alert("nincs benne a termekkódok között");
                                $('.product'+i+' .editable_productcode input').focus();
                                return;
                            }
                        }

                        if($('.product'+i+' .editable_quantity input').html() != undefined){ //Quantity 

                            if($('.product'+i+' .editable_quantity input').val() == ""){
                                $('.product' + i + " .editable_quantity input").css("border","2px solid red");
                                $('.product'+i+' .editable_quantity input').focus();
                                return;
                            }else{
                                if(validate(i,$('.product'+i+' .editable_quantity input').val())){
                                    $('.product'+i+' .editable_quantity').html($('.product'+i+' .editable_quantity input').val());
                                }else{
                                    return false;
                                }
                            }
                        }

                        if($('.product'+i+' .editable_po input').html() != undefined){ // Production Order

                            if($('.product'+i+' .editable_po input').val() == ""){
                                $('.product' + i + " .editable_po input").css("border","2px solid red");
                                $('.product'+i+' .editable_po input').focus();
                                return;
                            }else{
                                if(validate_PO(i,$('.product'+i+' .editable_po input').val())){
                                    $('.product'+i+' .editable_po').html($('.product'+i+' .editable_po input').val());
                                }else{
                                    
                                    $('.product'+i+' .editable_po input').focus();
                                    $('.product'+i+' .editable_po input').css("border","2px solid red");
                                    return false;
                                }
                            }
                        }
                    }
                    
                    return true;
                };
            });
        </script>
    </head>
    <body>
       <?php include_once('header.php'); ?>
        <div id="page_wrapper" >
            <h1>LINE <?php echo $id; ?> </h1>
            <form method="POST" action="" class="sortable">
                <?php 
                    echo $er;
                ?>
                <input type='image' id='addproduct' src="images/plus.jpg" width="75px" height="25px" title='Add Product' />
                <input type='image' id='removeproduct' src="images/minus.jpg" width="75px" height="25px"   title='Remove Product' />
                <!--<input type="button" id="addproduct" value="+" /><input type="button" id="removeproduct" value="-" />-->
                <p><input type="image" src='images/submit.jpg' id="submit" /></p>
            </form>
        </div>
    </body>
</html>


