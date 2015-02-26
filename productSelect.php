<?php
session_start();
include 'simpleXLSX/simplexlsx.class.php';

if(isset($_GET['id']) && isset($_SESSION['loggedin']) ){

    $id = $_GET['id'];
    switch($id){
        case '1': 
           // echo "PRODUCTION FOR LINE 1";
            break;
        case '2': 
            //echo "PRODUCTION FOR LINE 2";
            break; 
        case '3':
            //echo "PRODUCTION FOR LINE 3";
            break;
        case '4':
            //echo "PRODUCTION FOR LINE 4";
            break;
        default:
            echo "<h1 style='width:980px; margin:0 auto 0 auto; text-align:center;'><a href='index.php'>Back to previous page</a></h1>";
            die();
    }
}else{
    header("location:index.php");
    exit();
}

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
    
       /* array_push($tht,$r[4]);
        array_push($ict,$r[5]);
        array_push($fct,$r[6]);
        array_push($assembling,$r[7]);*/
}

$hossz= count($code); // terméktömb hossza
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Katek Hungary Kft.</title>
        <link rel="stylesheet" type="text/css" href="css/header_design.css" />
        <script src="js/jquery-2.0.3.min.js"></script>
        <script src="js/jquery-ui-1.10.4.min.js"></script>
        
        <!-- ide jön a css -->
        
        <style>
            body{
                padding: 0;
                margin: 0;
            }
            #page_wrapper{
                width: 980px;
                text-align: center;
                margin: 0 auto 0 auto;
            }
             table{
                margin: 0 auto 0 auto;
                /*width: 550px;*/
                border-collapse: collapse;
                text-align: left;
               
            }
             table td{
                padding: 5px;
         
            }
            .submit{
                text-align: center;
            }
            
            #prodName{
                width: 100%;
            }
            
            input,select{
                box-shadow: 2px 2px 2px #000;
            }

            body {
                font-family: 'Levenim MT';
            }
            hr{
                margin-top: -20px;
            }
            .bold{
                font-weight: bold;
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
            
            .check img, .po_check img{
                margin-left: 5px;
              
                padding: 0;
                width: 20px;
            }
            #note{
                resize: none;
            }
            textarea{
                width: 100%;
            }
            
/* ----- MENU ----- */
            

            /*nav {
                background:#1e81cc;
                width:100%;
                padding-bottom:22px;
            }
            nav ul {
                text-align:center;
                margin:0 300px;
                padding:0;
            }
            nav ul li {
                float:left;
                display:inline;

            }
            nav ul li:hover {
                background:#E6E6E6;
            }
            nav ul li a {
                display:block;
                padding:0 20px;
                color:#fff;
                text-decoration: none;
            }
            nav ul li ul {
                float:left;
                position:absolute;
                text-align:left;
                background:#1e81cc;
                margin:0;
            }*/
        </style>
    </head>
    <body>
        <?php include_once('header.php'); ?>

        <div id="page_wrapper">
            
            <div id="content">
                <!--<div id="logo"><img src="images/katek.jpg" alt="logo"/></div>-->
                <h1>LINE <?php echo $id; ?></h1>
                <hr/>
                <!-- ajaxproba.php?id=<?php //echo $id; ?>-->
                <form class="productForm" action="" method="POST">
                    <table class='productList'>
                        <tr class='nav' >
                            <td colspan='2' ><input type='image' src="images/plus.jpg" width="75px" height="25px" id='addProduct' title='Add Product' style="float: left"/></td>
                            <td colspan='2' ><input type='image' src="images/minus.jpg" width="75px" height="25px" id='removeProduct'  title='Remove Product' style="float: right;"/></td>
                        </tr>
                        <tr><td colspan='4' class='submit'><input id='submitButton' type='submit' value='Submit'/></td></tr>
                    </table>
                </form>
            </div> <!-- // content -->
        </div><!-- // page_wrapper -->

        <script>
            var sor = <?php echo json_encode($id);?>;
            var norma = <?php echo json_encode($norma); ?>;
            var hossz = <?php echo json_encode($hossz); ?>;
            var nev = <?php echo json_encode($nev); ?>;
            var code = <?php echo json_encode($code); ?>;
            
            /*var php_tht = <?php echo json_encode($tht); ?>;
            var php_ict = <?php echo json_encode($ict); ?>;
            var php_fct = <?php echo json_encode($fct); ?>;
            var php_assembling = <?php echo json_encode($assembling); ?>;*/
        </script>
        <script>
            $(document).ready(function () {

                var product_code = new Array();
                var termekek = new Array();
                var masiknorma = new Array();
                
                /*var tht = new Array();
                var ict = new Array();
                var fct = new Array();
                var assembling = new Array();*/
                
                var darabszam = new Array();
                var po = new Array();
                
                var elkeszules = new Array();
                var sampleproduction = new Array();
                
                var prod1_selected; // az ellenőrzéshez szükséges darabszám indexe(norma[prod1_selected])

                var termekazon = 1; // hány terméket szeretnénk gyártani ( Product 1, Product 2...)

                // elkeszítem az első termekhez tartozó táblázatot
                create();

                function create() {
                    var ujtermek = "<tr class='product" + termekazon + "'>" +
                                                    "<th colspan='4' style='background-color:#ccc; text-align:center'>Product " + termekazon + "</th></tr>";
                    ujtermek += "<tr class='product" + termekazon + "'><td style='width:200px;'>Product Code: </td><td><input id='product" + termekazon + "_autocomplete' class='autocomplete' type='text' placeholder='Type here...'/></td><td><input type='hidden' id='product" + termekazon + "_norma' value=''/></td></tr>";
                    ujtermek += "<tr class='product" + termekazon + "'><td>ProductName: </td><td colspan='3' style='font-weight:bold;' class='productName'></td></tr>";
                    ujtermek += "<tr class='product" + termekazon + "'><td>Quantity: </td><td colspan='3' ><input type='text' class='quantity' autocomplete='off'/><span class='check'></span></tr>";
                    /*ujtermek += "<tr class='product" + termekazon + "'><td></td><td style='text-align:center;'><input type='image' src='images/ok.jpg' width='40px' style='margin:0 5px -5px ; padding-left:0;' class='okes' value='ok'></td></tr>";*/
                    ujtermek += "<tr class='product" + termekazon + "'><td>Production Order No.</td><td><input type='text' class='po' name='po'/><span class='po_check'></span></td></tr>";
                    ujtermek += "<tr class='product" + termekazon + "'><td>Sample Production:</td><td><input type='checkbox' class='sample' name='sample'/></td></tr>";
                    ujtermek += "<tr class='product" + termekazon + "'><td class='error' colspan='4' style='text-align:center; color:red;'></td></tr>";
                    $(".nav").before(ujtermek);
                    // autocomplete funkció a termékkód kikereséséhez

                    var code_index; // a kiválaszott termékkód indexe
                    $('#product' + termekazon + '_autocomplete').autocomplete({
                        source: code,
                        max: 2,
                        minChars: 3,
                        select: function (event, ui) {
                            code_index = ($.inArray(ui.item.value, code));
                            //az autocomplete input box indexe
                            var input_index = $('.ui-autocomplete-input').index(this) + 1;

                            addCucc(input_index, code_index);
                        }
                    });
                }

                $("#addProduct").click(function () {

                    if (!ellenorzes()) {
                        return false;
                    }

                    termekazon++;
                    
                    //elkészítem a következő termékhez tartozó mezőket
                    create();
                    
                    //elmentem az előző termék adatait
                    addCucc(termekazon);
                    
                    return false;
                });

                function addCucc(i, code_index) {

                    var megadottClass = '.product' + i;

                    //$('#product' + i + '_norma').val(code_index);
                    $(megadottClass + ' .productName').html(nev[code_index]);
                    $('#product' + i + '_norma').val(norma[code_index]);
                    //$(megadottClass + ' #product_' + i).val($(this).val());

                    clearData(i, '');

                    // beleteszem a feltöltendő termékek adatait tömbökbe, ahonnan feltöltöm az adatbázisba
                    product_code[i - 1] = code[code_index];
                    termekek[i - 1] = nev[code_index];
                    masiknorma[i - 1] = norma[code_index];
                    
                    /*tht[i-1] = (php_tht[code_index]=='')?"-":php_tht[code_index];
                    ict[i-1] = (php_ict[code_index]=='')?"-":php_ict[code_index];
                    fct[i-1] = (php_fct[code_index]=='')?"-":php_fct[code_index];
                    assembling[i-1] = (php_assembling[code_index]=='')?"-":php_assembling[code_index];*/

                }
                // --- //addCucc() ----
                
                
                $(document).on('keydown','.quantity',function (e) {

                    var index = $('.quantity').index(this);
                    
                    if (e.which == 13 || e.keyCode == 13) {
                        e.preventDefault();
                        quantityCheck(index);
                        return false;
                    } else {
                        $(".check:eq("+index+")").html('');
                    }
                    return true;
                });
                
                $(document).on('input','.quantity', function (event) {
                    var index = $(".quantity").index(this);
                    if($(this).val().charAt(0)=='0'){
                        $(this).val('');
                    }
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
                
                $(document).on('keydown','.po',function (e) {
                    var index = $(".po").index(this);
                    if (e.which == 13 || e.keyCode == 13) {
                        poCheck(index+1);
                        return false;
                    }
                    return true;
                });
                
                $(document).on('input', '.po', function (event) {
                    var index = $(".po").index(this)+1;
                    $('.product'+index+' .po_check').html("");
                    this.value = this.value.replace(/[^0-9]/g, '');
                });
                
                function poCheck(index){
                    var po_val = $('.product'+index+' .po').val();
                    var po_value_length = $('.product'+index+' .po').val().length;     

                    if(po_value_length > 7 || po_value_length < 7){
                        $('.product'+index+' .error').html('Value length is not correct (7 character needed).');
                        $('.product'+index+' .po').css('border','2x solid red');
                        $('.product'+index+' .po').val('');
                        $('.product'+index+' .po_check').html('');
                        $('.product'+index+' .po').focus();
                        
                        return false;
                    }else if(po_val==" "){
                        $('.product'+index+' .error').html('Value is missing.');
                        $('.product'+index+' .po').css('border','2x solid red');
                        $('.product'+index+' .po').val('');
                        $('.product'+index+' .po_check').html('');
                        $('.product'+index+' .po').focus();
                        return false;
                    }
                    $('.product'+index+' .error').html('');
                    $('.product'+index+' .po_check').html("<img src='images/check.png' alt='check'/>");
                    po[index-1] = $('.product'+index+' .po').val();
                    return true;
                }


               function quantityCheck(index) {
                   
                    var num = parseInt(index) + 1;
                    var db = parseInt($('.product' + num + ' .quantity').val());
                    var adott_termeknorma = parseInt($('#product' + num + '_norma').val());
                   
                    if (db == 0 || isNaN(db)) {
                        clearData(num, 'Invalid Value');
                        $('.product' + num + ' .quantity').css("border","2px solid red");
                        $('.product' + num + ' .quantity').focus();
                        $('.product' + num + '.check').html('');
                        return false;

                    } else if (db > 100000) {
                        clearData(num, "Maximum value is 100000");
                        $('.product' + num + ' .quantity').css("border","2px solid red");
                        $('.product' + num + ' .quantity').focus();
                        $('.product' + num + '.check').html('');
                        return false;

                    } else {
                        $(".product" + num + " .quantity").css('border', "");
                        $('.product' + num + ' .check').html("<img src='images/check.png' alt='check'/>");
                    }
                   
                   $('.product' + num + " .error").html('');
                   
                   return true;
                };

                function clearData(num, hibauzenet) {

                    if (hibauzenet == '') {
                        $('.product' + num + ' .quantity').val('');
                        $('.product' + num + ' .check').html('');
                        $('.product' + num + " .error").html('');
                        $('.product' + num + ' .po').val('');
                        $('.product' + num + ' .po_check').html('');
                        $('.productCode' + num).css('border', '');
                        $('.productCode' + num + " .quantity").css('border', '');
                    } else {
                        $('.product' + num + ' .quantity').val('');
                        $('.product' + num + ' .check').html('');
                        $('.product' + num + " .error").html(hibauzenet);
                        //$(".product" + num + " #pcs").html('');
                        $(".product" + num + " .quantity").css('border', "2px solid red");
                    }
                }

                $('#removeProduct').click(function () {
                    if (termekazon <= 1) {
                        return false;

                    } else if (termekek[termekazon - 1] == undefined) {

                        $('.product' + termekazon).remove();
                        termekek.pop();
                        termekazon--;

                    } else {
                        $('.product' + termekazon).remove();
                        termekek.pop();
                        termekazon--;
                    }
                    return false;
                });

                $('#submitButton').click(function (e) {
                    
                    var submit = false;
                    
                    if (!ellenorzes()) {
                        //alert(termekazon);
                        e.preventDefault();

                        return false;
                    } else {
                       
                        var termekTomb = [];
                       
                        for (var i = 0; i < termekek.length; i++) { 
                            //termekTomb[i] = termekek[i] + ', ' + product_code[i] + ', ' + masiknorma[i] + ', '+ tht[i]+', '+ict[i]+', '+fct[i]+', '+assembling[i]+', '+ darabszam[i] + ', ' +po[i]+', ' + elkeszules[i]+', '+sampleproduction[i];
                            termekTomb[i] = termekek[i] + ', ' + product_code[i] + ', ' + masiknorma[i] + ', ' + darabszam[i] + ', ' +po[i]+', ' + elkeszules[i]+', '+sampleproduction[i];
                        }
                        //alert(termekTomb);
                        $.ajax({
                            url: 'upload.php',
                            type: 'POST',
                            data: { 'data': termekTomb, 'sor': sor },
                            success: function (response) {
                                // Submit event indítása, ha befejeződött az ajax kérés
                                alert(response);
                                location.reload();
                            }
                        });
                    }

                    return false;
                });

                // ------------------ ELLENŐRZÉS ---------------------

                function ellenorzes() {

                    for (var i = 1; i <= termekazon; i++) {

                        var num = i;
                        var db = parseInt($('.product' + num + ' .quantity').val(),10);
                        
                        var adott_termeknorma = parseInt($('#product' + num + '_norma').val());

                        if ($('#product' + i + '_autocomplete').val() == '' || $('#product' + i + '_autocomplete').val() == undefined) {
                            $('.product' + i + " .error").html('Product Code is missing.');
                            $('#product' + i + '_autocomplete').focus();
                            return false;

                        } else if ($.inArray($('#product' + i + '_autocomplete').val(), code) == -1) {
                            $('.product' + i + " .error").html('Product Code does not exist.');
                            $('#product' + i + '_autocomplete').focus();
                            return false;

                        } else if($('.product' + num + ' .quantity').val().substring(0,1)=='0'){
                            alert("nulla az első");
                            return false;
                        }else if (db == 0 || isNaN(db)) {
                            $('.product' + i + " .error").html('Quantity is missing.');
                            $('.product' + i + ' .quantity').css('border','2px solid red');
                            $('.product' + i + ' .quantity').focus();
                            return false;
                        }else if(db>100000){
                            $('.product' + i + " .error").html('Can\'t be larger than 100000!');
                            $('.product' + i + ' .quantity').css('border','2px solid red');
                            $('.product' + i + ' .quantity').focus();
                            
                            return false;
                //---- PO NUMBER CHECK
                            
                        } else if($('.product'+num+' .po').val() == ""){
                            $('.product' + num + " .error").html('Production Order Number is missing.');
                            $('.product' + num + " .po_check").html('');
                            $('.product' + num + " .po").focus();
                            return false;
                        }else if($('.product'+num+' .po').val().length > 7 || $('.product'+num+' .po').val().length < 7 ){
                            $('.product' + num + " .error").html('Value length is not correct (7 character needed).');
                            $('.product' + num + " .po_check").html('');
                            $('.product' + num + " .po").focus();
                            return false;
                        }else {
                            $(".product" + num + " .quantity").css('border', "");
                            $('.product' + num + " .check").html("<img src='images/check.png' alt='check'/>");
                            $('.product' + num + " .po_check").html("<img src='images/check.png' alt='check'/>");
                            $('.product' + num + " .error").html('');
                        }

                        
                        // beleteszem a feltöltendő termékek adatait tömbökbe, ahonnan feltöltöm az adatbázisba
                        darabszam[i-1] = parseInt($('.product' + i+ ' .quantity').val());
                        
                        var elkeszul = (28800 / masiknorma[masiknorma.length - 1] * parseInt($('.product' + i + ' .quantity').val())).toFixed(3);
                        
                        elkeszules[i-1] = (elkeszul);
                        
                        po[i-1] = $('.product'+i+' .po').val();
                        
                        sampleproduction[i-1] = ($('.product'+i+' .sample').is(':checked')==true)?1:0;
                        
                    }
                    return true;
                };
            });
        </script>
    </body>
</html>