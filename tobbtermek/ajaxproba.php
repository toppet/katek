<?php
     if(isset($_GET['id'])){
         
         $id = $_GET['id'];
         $email_kiiras = TRUE;
         
         session_start();
         $cleared = 0;
         
         if($cleared == 0){
            session_unset();
            $cleared++;
         }
         
?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2" />
    <meta http-equiv="Content-Language" content="hu" />
    
    <script src="js/jquery-2.0.3.min.js"></script>
    <script src="js/knob.js"></script>
    <script src="js/knockout-3.0.0.js"></script>
	<script src="js/globalize.min.js"></script>
	<script src="js/dx.chartjs.js"></script>
	<script src="js/jquery-ui-1.10.4.min.js"></script>

    <!-- popup window javascript/CSS -->
    <script src="js/popup.js"></script>
    <link rel="stylesheet" type="text/css" href="css/popup_design.css"/>
    <link rel="stylesheet" type="text/css" href="css/ajax_proba_design.css"/>
    <!--  /////////////////////  -->


    <script>
        $(document).ready(function(){
            if($('#empty_database').html()!= '' && $('#empty_database').html() != undefined){
                // ha még nincsenek adatok akkor frissítjük az oldalt adott időközönként
                setTimeout(function(){
                    location.reload();
                },15000);
            }
        });
        </script>
</head>
    <body>

        <div id='smt_line'>
            <h1>SMT LINE <?php echo $id; ?></h1> <div id="logo"><img src="images/katek_white.png" alt="logo" title="Katek Hungary Kft." /></div>
        </div><!-- //smt_line -->
        
        <div id="page_wrapper">
            <div id='pop'>
                <?php /*include_once('productlist.php');*/ // kislitázzuk az gyártandó termékeket az adatbázisból ?>
            </div>
            <div class="response"></div>
        </div> <!-- //page_wrapper -->
        
        
        <div class="szamlalo">
            <input type="text" value="0" data-width="50" data-height="50" class="dial">
        </div>
        
        <script>
            
            var id = <?php echo json_encode($id);?>;
            
            var productNameArray = [];
            var productCodeArray = [];
            var productQuantity = [];
            var productPOArray = [];
            var productElkeszulArray = [];
            var productNormaArray = [];
            var SampleProductionArray = [];
            
            var aktTermek = 0;
            
        </script>
        <script>
            var probadarab_kesz;
            var termekdb;
            
            $(document).ready(function () {
                
                $('.szamlalo').hide();
                
                $('.product_row:eq(0)').css('background-color','green');
                $('.product_row:eq(0)').css('color','#fff');

                $('#page_wrapper').append("<div id='buffer' style='margin-top: 250px;text-align:center;'><img src='images/loading-circle.gif' style='width:50px;' alt=''/><p>Connecting to database...</p></div>");
                
                x = 0;
                
                // egyből lekérem a terméklistát, majd onnan indítom a próba vagy lekérdezés függvényt,
                // attól függően, hogy van-e már kész próbadarab
                getProductList(); 
            });

            function getProductList() {
                $.ajax({
                    url: 'ajaxproductList.php',
                    type: 'get',
                    dataType: 'json',
                    data: "id="+id,
                    success: function (response) {
                        
                        // Ha nem tud csatlakozni az adatbázishoz, 
                        // mondjuk azért mert ki van kapcsolva,
                        // akkor próbálkozzon 15 másodpercenként újrakapcsolódni
                        
                        if(response.errconn != undefined && response.error != ''){
                            $('#page_wrapper').html(response.errconn);
                            
                            if(response.errconn == 1){
                               setTimeout(function(){ getProductList();},15000);
                               $('.szamlalo').hide(); //számláló div elrejtése
                               clearTimeout(t); //számláló időzítő leállítása
                            }
                            
                        }else if(response.error != undefined && response.error != '' ){
                            $('#page_wrapper').html(response.error);
                            
                            if(response.error="No production data."){
                                //setTimeout("location.reload()",15000);
                               setTimeout(function(){ getProductList();},15000);
                                $('.szamlalo').hide(); //számláló div elrejtése
                               clearTimeout(t); //számláló időzítő leállítása
                            }

                        }else{
                            
                            $("#page_wrapper").html("<div id='pop'></div><div class='response'></div>");
                            $('#pop').html(response.productLista);
                            
                            $('.product_row:eq(0)').css('background-color','green');
                            $('.product_row:eq(0)').css('color','#fff');
                            
                            productCodeArray = response.productCodeArray;
                            productNameArray = response.productNameArray;
                            productQuantity = response.productQuantity;
                            productPOArray = response.productPOArray;
                            productElkeszulArray = response.productElkeszulArray;
                            productNormaArray = response.productNormaArray;
                            sample_done = response.sample_done;
                            SampleProductionArray = response.SampleProductionArray;
                            
                             /*if(response.SampleProductionArray[aktTermek] == 1){
                                $.ajax({
                                    url: 'sampleProduction.php',
                                    
                                });
                            }*/
 
                            termekdb = productNameArray.length;
                            
                            $('.szamlalo').show()
                            $('#buffer').hide();

                            x = 0;
                            clearTimeout(t);
                            
                            //hozzaad(); // elindítom a szamlalot
                            
                            if(sample_done[aktTermek]==0){
                                proba();
                            }else{
                                //probadarab_kesz = true;
                                lekerdez();
                            }
                        }
                    }
                });
            }
            
            /*function adjustHeights(elem) {
                var fontstep = 2;
                if ($(elem).height()>$(elem).parent().height() || $(elem).width()>$(elem).parent().width()) {
                    $(elem).css('font-size',(($(elem).css('font-size').substr(0,2)-fontstep)) + 'px').css('line-height',(($(elem).css('font-size').substr(0,2))) + 'px');
                    adjustHeights(elem);
                }
            }*/
            
            function proba() {
                
                // amíg a lekérdezés tart, addig a számláló áll, és megjelenik a töltést jelző animáció
                    clearTimeout(t); 
                    clearTimeout(error_flash); // hibajelző kikapcsolása
                
                    $('.response').html("<div id='buffer' style='margin-top: 250px;text-align:center;'><img src='images/loading-circle.gif' style='width:50px;' alt=''/><p>Fetching data...</p></div>");
                // ----------
                
                $.ajax({
                    type: 'POST',
                    url: 'probadarab.php',
                    dataType: 'json',
                    data: 'productCode=' + productCodeArray[aktTermek] + "&id=" + id + "&po=" +productPOArray[0]+"&productCodeArray="+productCodeArray,
                    success: function (response) {

                        $('.szamlalo').show()
                        $('#buffer').hide();
                      
                        x = 0;
                        
                        // Ha a soron már egy másik termék van ellenőrzés alatt, akkor váltson át egyből arra,
                        // függetlenül attól, hogy a jelenlegi elkészült-e.
                        
                        if(response.atallas != undefined && response.atallas == true){
                            //alert("Product_code: "+response.atallas_kod);
                            //$(".response").html("At kellene allni erre: "+response.atallas_kod);
                            //$(".response").append(", amihez a PO: "+productPOArray[response.array_index]);
                            
                            // beállítom az aktuális terméket elkészültre
                            $.ajax({
                                url:'update_product_status.php',
                                type:'POST',
                                data: 'product_code='+productCodeArray[aktTermek]+'&po='+productPOArray[aktTermek],
                                success: function(response){
                                    $(".response").append(response);
                                }
                            });
                        }
                        //ha hiba van a csatlakozásban
                        if(response.errconn != undefined && !empty(response.errconn)){
                                $(".response").html(response.errconn);
                                $(".szamlalo").css('display', 'none');
                                setTimeout(function(){proba();}, 10000);
                        }else if (response.error != undefined) {
                            //ha bármilyen más hiba van
                            $(".response").html(response.error);
                            $(".szamlalo").css('display', 'none');
                            setTimeout("proba()", 10000);
                        } else {

                            //alert(response.eredmeny);
                            hozzaad(); // elinditom a szamlalot
                            if (response.vege == "befejezve") {
                                probadarab_kesz = true;
                                setTimeout(function(){lekerdez();}, 5000);
                            }
                        }
                        $(".response").html(response.eredmeny);
                    }
                });
            }

            var emailElkuldve = 0;
            var eredmenyKiirva = 0;
            var error_flash;
            

            function lekerdez() {
                // amíg a lekérdezés tart, addig a számláló áll, és megjelenik a töltést jelző animáció
                    clearTimeout(t);
                    clearTimeout(error_flash);
                
                    $('.response').html("<div id='buffer' style='margin-top: 250px;text-align:center;'><img src='images/loading-circle.gif' style='width:50px;' alt=''/><p>Fetching data...</p></div>");
                // ----------
               
                x = 0;

                // ellenőrzöm, hogy fut-e valamilyen lekérdezés
                function testFile() {
                  return $.ajax({
                      url: "checkfile.php",
                      dataType:'json'
                  });
                }

                var fileExists = testFile(); // ellenőrizzük, hogy létezik-e a temp file
                
 // ha befejeződött a fájl ellenőrző ajax script akkor eldöntjük, hogy mehet-e a lekérdezés vagy sem
            fileExists.success(function (response) {
                if(response['exists']){
                    console.log("lekerdezes, folyamatban, varunk par masodpercet...");
                    clearTimeout(t);
                    setTimeout(function(){lekerdez();}, 5000); // várok 5 másodpercet, mielőtt újra próbálkoznék az ellenőrzéssel
                }else{
                    console.log("nincs folyamatban masik query, ugyhogy mehet a lekerdezes...");
                    dataString = 'productCode=' + productCodeArray[0] + "&givenValue=" + productQuantity[0] + '&norma=' + productNormaArray[0] +"&po="+productPOArray[0]+"&elkeszul=" + productElkeszulArray[0] + '&emailElkuldve=' + emailElkuldve + '&id=' + id+"&productCodeArray="+productCodeArray;

                    $.ajax({
                        type: 'POST',
                        url: "ajaxScript.php",
                        data: dataString,
                        dataType: 'json',
                        success: function (response) {
                            
                            hozzaad();    

                            if (response.emailElkuldve == 1) {
                                emailElkuldve = 1;
                            }

                            // Ha a soron már egy másik termék van ellenőrzés alatt, akkor váltson át egyből arra,
                            // függetlenül attól, hogy a jelenlegi elkészült-e.

                            if(response.atallas != undefined && response.atallas == true){
                                //alert("Product_code: "+response.atallas_kod);
                                // $(".response").html("At kellene allni erre: "+response.atallas_kod);
                                //$(".response").append(", amihez a PO: "+productPOArray[response.array_index]);

                                // beállítom az aktuális terméket elkészültre
                                $.ajax({
                                    url:'update_product_status.php',
                                    type:'POST',
                                    data: 'product_code='+productCodeArray[aktTermek]+'&po='+productPOArray[aktTermek],
                                    success: function(response){
                                        $(".response").append(response);
                                    }
                                });
                            }

                            // Ha hiba van a csatlakozásban.
                            if(response.errconn != undefined && !empty(response.errconn)){
                                $(".response").html(response.errconn);
                                $(".szamlalo").css('display', 'none');
                                setTimeout(function(){lekerdez();}, 10000);
                            }else if (response.error != undefined) {
                                //ha bármilyen másik hiba van
                                $(".response").html(response.error);
                                $(".szamlalo").css('display', 'none');
                                setTimeout(function(){lekerdez();}, 10000);
                            }else{
                                if(response.high_fail_rate == true){
                                  error_flash = setInterval(function(){
                                       $("body").effect('highlight',{color:"red"},750);
                                   },3000);
                                }
                                $('.response').html(response.responseText);
                            }

                            if (response.kesz) {
                                $('.dial').val('done');

                                clearInterval(error_flash);
                                clearTimeout(t);
                                //az exact_endtime az ajaxscript phpan van deklarálva
                                //$("#exact_endtime").html(response.tenyleges);
                                $(".delay p").html(response.tenyleges);
                                $(".delay p").css("font-size",'25px');
                                if (aktTermek == (termekdb - 1)) {

                                    // ha az utolsó termék is elkészült
                                    $('.done').html('<p>All required products are manufactured!</p>');

                                    clearTimeout(t); //számláló időzítő leállítása
                                    setTimeout(function(){getProductList();}, 15000); //terméklista lekérése
                                } else {
                                    $('.done').append("<h3 style='color:red;'>Next product is loading...</h3>");

                                    clearTimeout(t); //számláló időzítő leállítása
                                    setTimeout(function(){getProductList();}, 15000); //terméklista lekérésee
                                }
                            }
                            $("#page_wrapper").append()
                        }
                    });
                }

                });
            }
            
            /*-----  /lekerdez ----- */

            $(".dial").knob().trigger(
                 'configure', {
                     'min': 0,
                     'max': 90,
                     "fgColor": "#0367b3",
                     "inputColor": '#0367b3',
                     'thickness': 0.2,
                     'readOnly': true,
                     'format' : function (value) { 
                        var perc = Math.floor(value/60);
                        var mp = value-perc*60;
                        if(mp < 10){ mp = "0"+mp;}
                        ido = perc+":"+mp;
                        return ido;
                     }
                 }
            );
            
            var x = parseInt($('.dial').val());
            // t, a hozzad függvényhez tartozó időváltozó...
            var t;
            var lefutascount = 1;
            
            function hozzaad() {
 
                if (x == 89) {
                    x = 0;
                    getProductList(); // A terméklista frissítése

                } else if (x == 25 && lefutascount == 5) {
                    loading();
                    setTimeout(function () { // then show popup, delay in .5 second
                        loadPopup(); // function show popup
                    }, 500); // .5 second
                    lefutascount = 1;
                    x += 1;
                } else if (x == 55) {
                    //A felugró ablak kikapcsolása
                    disablePopup();
                    //lefutások számának növelése
                    lefutascount++;
                    x += 1;
                } else {
                    x += 1; 
                }
                
                $('.dial').val(x).trigger('change');
                t = setTimeout('hozzaad()', 1000);
            }
            
            
        </script>
    </body>
</html>
<?php
 }else{
     echo "Missing values...";
 }
     //mysql_close($conn);
?>