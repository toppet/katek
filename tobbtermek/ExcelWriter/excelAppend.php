<?php 

/** Include PHPExcel */
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

$datum = date("Ymd");

$cim = "AOI Line 1 - Production Summary_".$datum.".xlsx";

$filename = $cim;


if (!file_exists($filename)) {
    echo "The file does not exist.<br/>";
    createExcelFile($cim);
} else {
    echo "The file exists.<br/>";
    addDataToExcel();
}

function createExcelFile($cim){
    // Először elkészítjük a fájlt
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();

    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($cim);
    echo "<p>A fájl elkészült.</p>";
    addDataToExcel();
}

$row='';

function addDataToExcel(){
    
    // Add some data
    $datum = date("Ymd");
    $cim = "AOI Line 1 - Production Summary_".$datum.".xlsx";
    $objPHPExcel = PHPExcel_IOFactory::load($cim);
    $row = $objPHPExcel->getActiveSheet()->getHighestRow()+2;
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$row, 'Termék neve')
                ->setCellValue('A'.($row+1),'Elkészült darabszám')
                ->setCellValue('B'.($row+1),'darab')
                ->setCellValue('A'.($row+2),'Hibás darab')
                ->setCellValue('B'.($row+2),'darab')
                ->setCellValue('A'.($row+3),'Yield')
                ->setCellValue('B'.($row+3),'%')
                ->getColumnDimension('A','B')
                ->setAutoSize(true);
    cellColor($objPHPExcel,'A'.$row,'00f');
                
    //echo "A excel fájlban található sorok száma: " . ($row-1);
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);

    // Save Excel 2007 file
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($cim);
    
    echo "Az adatok kiírása sikeres!";
    
    //kiir($objPHPExcel,$row);
}
// SZÍNEZÉS //

function cellColor($objPHPExcel,$cells,$color){
        
        $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()
        ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array('rgb' => $color)
        ));
        $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true)->setSize(15);
    }


function kiir($objPHPExcel,$row){ 
    // Kíírjuk a fájl tartalmát //
    $szam = (int)$row;

    for($i=0;$i<$szam+1;$i++){
        $row = $objPHPExcel->getActiveSheet()->getRowIterator($i)->current();

    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(true);

    foreach ($cellIterator as $cell) {
        // echo $cell->getValue();
    }
    // echo "<br/>";
    }
}

?>

