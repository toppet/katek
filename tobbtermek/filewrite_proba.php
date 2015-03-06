<?php
header('Content-Type: text/html; charset=utf-8');
$document=new domDocument('1.0', 'utf-8'); 
$document->loadHTML('<html><head><title>Hülye karakterek</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>ßäéűáúő</body></html>'); 
$document->formatOutput=false; 
$document->encoding='UTF-8'; 
echo "ßäéűáúő. Recorded bytes: ".$document->saveHTMLFile('html.html');
?>