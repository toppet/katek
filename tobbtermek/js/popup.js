
    /* event for close the popup */
    $("div.close").hover(
        function () {
            $('span.ecs_tooltip').show();
        },
        function () {
            $('span.ecs_tooltip').hide();
        }
    );

    $("div.close").click(function () {
        disablePopup();  // function close pop up
    });

    $(this).keyup(function (event) {
        if (event.which == 27) { // 27 is 'Ecs' in the keyboard
            disablePopup();  // function close pop up
        }
    });

    $("div#backgroundPopup").click(function () {
        disablePopup();  // function close pop up
    });

    /************** start: functions. **************/
    function loading() {
        $("div.loader").show();
    }

    function closeloading() {
        $("div.loader").fadeOut('normal');
    }

    var popupStatus = 0; // set value

    function loadPopup() {
        if (popupStatus == 0) { // if value is 0, show popup
            closeloading(); // fadeout loading

            $("#toPopup").fadeIn(500); // fadein popup div
            //alert(window.outerWidth + ", height: " + window.outerHeight);

            $("#toPopup").css("left", (window.outerWidth - $("#toPopup").width()) / 2);
            $("#toPopup").css("top", (window.outerHeight - $("#toPopup").height()) / 2);
            //$("#popup_content tr:nth-child(even").css("background-color", "#bbb");
            $("#backgroundPopup").css("opacity", "0.8"); // css opacity, supports IE7, IE8
            $("#backgroundPopup").fadeIn(0001);

            popupStatus = 1; // and set value to 1

        }

        /*setTimeout(function () {
            $("#toPopup").fadeOut("normal");
            $("#backgroundPopup").fadeOut("normal");
            popupStatus = 0;  // and set value to 0
        }, 8000);*/ // kapcsolja ki a popupot adott mp után  
    }

    function disablePopup() {
        if (popupStatus == 1) { // if value is 1, close popup
            $("#toPopup").fadeOut("normal");
            $("#backgroundPopup").fadeOut("normal");
            popupStatus = 0;  // and set value to 0
        }
    }

