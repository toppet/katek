<?php

?>   
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title></title>
        <script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>
        <style>
            
            body{
                
                background-color: #808080;
            }
            
            .circular-menu {
                width: 250px;
                height: 250px;
                margin: 150px auto 0;
                position: relative;
            }
            
            .circle {
                width: 250px;
                height: 250px;
                opacity: 0;
                -webkit-transform: scale(0);
                -moz-transform: scale(0);
                -transform: scale(0);
                -webkit-transition: all 0.3s ease-out;
                -moz-transition: all 0.3s ease-out;
                transition: all 0.3s ease-out;
                
                /*border: 2px solid red;*/
            }
            
            .open.circle {
                opacity: 1;
                -webkit-transform: scale(1);
                -moz-transform: scale(1);
                -transform: scale(1);
            }

            .circle a {
                text-decoration: none;
                color: #ff6a00;
                display: block;
                height: 40px;
                width: 40px;
                line-height: 40px;
               
                position: absolute;
                text-align: center;
                vertical-align: middle;
                background-color: #eee;
                border-radius: 50%;
            }
            
            .circle a:hover {
                color: #eef;
            }
            .SMT{
                position: absolute;
                display: block;
            }
            
            .lowerlevel {
                opacity: 0;
                -webkit-transform: scale(0);
                -moz-transform: scale(0);
                -transform: scale(0);
                -webkit-transition: all 0.3s ease-out;
                -moz-transition: all 0.3s ease-out;
                transition: all 0.3s ease-out;
                
                margin-top: -225px;
                width: 100%;
                top: 0;     
            }
            
            .lowerlevel a {
               z-index: -1;
               width:50px;
               height: 50px;
               line-height: 50px;
               color: green;
               text-decoration: none;
              
               font-size: 15px;
               border-radius: 50%;
               margin: 0;
               padding: 0;
               background-color: #b6ff00;
               
               position: absolute;
               text-align: center;
               vertical-align: middle;     
            }
            .lowerlevel .realtime{
               
               margin-left: 5%;
            }
            .lowerlevel .multbeli{
                margin-left: 75%;
            }
            
            .open.lowerlevel{
                opacity: 1;
                -webkit-transform: scale(1);
                -moz-transform: scale(1);
                -transform: scale(1);
            }
                        
            .menu-button {
                position: absolute;
                /*top: calc(50% - 10px);
                left: calc(50%  - 27.5px);
                */
                text-decoration: none;
                text-align: center;
                color: #444;
                border-radius: 50%;
                display: block;
                width: 150px;
                height: 150px;
                line-height: 150px;
              
                background: #dde;
            }

            .menu-button:hover {
                background-color: #eef;
            }
                     
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

                $('.menu-button').click(function (e) {
                    calc($('.circle a'));
                    e.preventDefault();
                    $('.circle').toggleClass('open');
                    if ($('.lowerlevel').attr('class') == 'lowerlevel open') {
                        $('.lowerlevel').removeClass('open');
                    }
                });

                $(".SMT").click(function (e) {
                    calcLower($('.lowerlevel a'));
                    e.preventDefault();
                    $('.lowerlevel').toggleClass('open');
                });

                function calc(items) {
                    for (var i = 0, l = items.length; i < l; i++) {
                        items[i].style.left = (40 - 35 * Math.cos(-0.5 * Math.PI - 2 * (1 / l) * i * Math.PI)).toFixed(4) + "%"
                        items[i].style.top = (50 + 35 * Math.sin(-0.5 * Math.PI - 2 * (1 / l) * i * Math.PI)).toFixed(4) + "%";
                    }
                }

                function calcLower(items) {
                   
                    for (var i = 0, l = items.length; i < l; i++) {
                        /*items[i].style.left = (50 - 35 * Math.cos(-0.5 * Math.PI - 2 * (1 / l) * i * Math.PI)).toFixed(4) + "%";
                        items[i].style.top = (50 + 35 * Math.sin(-0.5 * Math.PI - 2 * (1 / l) * i * Math.PI)).toFixed(4) + "%";*/

                        items[i].style.verticalAlign = "middle";
                    }
                }
            });
            
        </script>

    </head>
    <body>
        <nav class="circular-menu">
            <div class="circle">
                <a href="" class="SMT">SMT</a>
                <a href="" class="THT">THT</a>
                <a href="" class="TEST">TEST</a>
            </div>
            <div class="lowerlevel">
                <a href="#" class="realtime">realtime</a>
                <a href="#" class="multbeli">multbeli</a>
            </div>
          <a href="" class="menu-button">ADATBEVITEL</a>
        </nav>
    </body>
</html>
