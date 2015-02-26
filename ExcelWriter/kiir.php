<?php

require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/Writer/Excel2007.php';

switch($aoi){
    case 4:
        $sorszam = 1;
        break;
    case 2:
        $sorszam = 2;
        break;
    case 3:
        $sorszam = 3;
        break;
    case 5:
        $sorszam = 4;
        break;
}

$cim = 'Summary/LINE'.$sorszam. '_Production_Summary_'.date('Ymd').'.xlsx';

if(!file_exists($cim)){
    // Ha meg nem letezik a fajl, keszitse el
    createFile($cim,$product_name,$elkeszultDB,$hibasdb,$keses,$hibatlanArany);

}else{
    //Ha mar letezik a fajl, fuzze hozza az adatokat
    addDatatoExcel($cim,$product_name,$elkeszultDB,$hibasdb,$keses,$hibatlanArany);
}

function createFile($cim,$product_name,$elkeszultDB,$hibasdb,$keses,$hibatlanArany){
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($cim);

    // meghivom az adatok kitoltseert felelos fuggvenyt
    addDatatoExcel($cim,$product_name,$elkeszultDB,$hibasdb,$keses,$hibatlanArany);
}
   
function addDatatoExcel($cim,$product_name,$elkeszultDB,$hibasdb,$keses,$hibatlanArany){
    //$objPHPExcel = new PHPExcel();
    $objPHPExcel = PHPExcel_IOFactory::load($cim);
    $row = $objPHPExcel->getActiveSheet()->getHighestRow()+2;
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A'.$row,$product_name)
                ->setCellValue('A'.($row+1),'Elkeszult darabszam:')
                ->setCellValue('B'.($row+1),$elkeszultDB)
                ->setCellValue('A'.($row+2),'Hibas darabszam:')
                ->setCellValue('B'.($row+2),$hibasdb)
                ->setCellValue('A'.($row+3),'Yield:')
                ->setCellValue('B'.($row+3),$hibatlanArany.'%')
                ->setCellValue('A'.($row+4),'Keses:')
                ->setCellValue('B'.($row+4),$keses)
                ->setCellValue('A'.($row+5),'Gyartas vege:')
                ->setCellValue('B'.($row+5),date('Y-m-d- H:i'))
                ->getColumnDimension('A','B')
                ->setAutoSize(true);
    cellColor($objPHPExcel,'A'.$row,'0094ff');
    $objPHPExcel->getActiveSheet()->getStyle('A'. $row.':B'.($row+5))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
    $objPHPExcel->setActiveSheetIndex(0);

    // Save Excel 2007 file
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($cim);
}

function cellColor($objPHPExcel,$cells,$color){
        
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()
    ->applyFromArray(array('type' => PHPExcel_Style_Fill::FILL_SOLID,
    'startcolor' => array('rgb' => $color)
    ));

    //$objPHPExcel->getActiveSheetIndex(0)->getStyle('B').getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('B')->setAutoSize(true);
    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('ffffff');
    
}
?>