$(document).ready(function(){
   
    $(".dial").knob().trigger(
         'configure', {
             'min': 0,
             'max': 60,
             "fgColor": "#0367b3",
             "inputColor": '#0367b3',
             'thickness': 0.2,
             'readOnly': true
         }
    );
    var hozzaad_szamlalo;
    clearInterval(hozzaad_szamlalo);
    hozzaad_szamlalo = setInterval(function(){hozzaad()},1000);
 // lekerdez();
/*
function lekerdez(){
    //clearTimeout(hozzaad_szamlalo);
    $.ajax({
       url:'get_alldata.php',
       type:'post',
       success:function(response){
           $("#adatok").html(response);
       }
    });
  }  
*/
    //kiszámítom a táblázat magasságát meg megszorzom minusz egyel és még kivonok 35px margin távolságotS
           var l1_height = parseInt($("#line_1").css("height"));
            var l2_height = parseInt($("#line_2").css("height"));

            var l3_height = parseInt($("#line_3").css("height"));
            var l4_height = parseInt($("#line_4").css("height"));

            var f_height = (l1_height>l2_height)?l1_height:l2_height;

            $("#first_half").css("height",f_height); // első fél div magassága (felső)
            $("#line_2").css("margin-top",l1_height*(-1)+"px");
            $("#line_2").css("margin-left","755px");

            $("#line_1").css("margin-left","65px");
            $("#line_3").css("margin-left","65px");

            var s_height = (l3_height>l4_height)?l3_height:l4_height; // második fél div magassága (alsó)
            $("#second_half").css("height",s_height);

            $("#line_4").css("margin-top",l3_height*(-1)+"px");
            $("#line_4").css("margin-left","755px");
           
            $("#line_1 tr:eq(2)").css("background-color","green");
            $("#line_2 tr:eq(2)").css("background-color","green");
            $("#line_3 tr:eq(2)").css("background-color","green");
            $("#line_4 tr:eq(2)").css("background-color","green");
    
    var x = parseInt($('.dial').val());
    // t, a hozzad függvényhez tartozó időváltozó...
    var t;
    
    function hozzaad() {
        
        if (x == 59) {
            x = 0;
            location.reload();
        }else {
            x += 1;
        }

        $('.dial').val(x).trigger('change');
        //t = setTimeout(function(){hozzaad()}, 1000);
    }    
});