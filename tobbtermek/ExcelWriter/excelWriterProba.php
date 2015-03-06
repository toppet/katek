<?php

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/Budapest');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

/** Include PHPExcel */
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Topos Peter")
							 ->setTitle("PHPExcel Test Document");

// Add some data
$datum = date("Y-m-d");
$teljesdatum = date("Y-m-d")." 05:55:55 - ". date("Y-m-d")." 17:55:55";

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'AOI LINE 1')
            ->setCellValue('A2', 'Production Summary - '.$datum)
            ->setCellValue('A3', $teljesdatum);

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

/* RAJZ BEILLESZTESE */
$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName('Logo');
$objDrawing->setDescription('Logo');
$objDrawing->setPath('./katek.jpg');
$objDrawing->setCoordinates('H1');
$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
//----------------------------

// Save Excel 2007 file
$cim = "AOI Line 1 - Production Summary (".$datum.").xlsx";

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save($cim);

//echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo($cim, PATHINFO_BASENAME)) , EOL;

// Echo done
echo "Done writing file." , EOL;
//echo 'Files have been created in ' , __DIR__ , EOL;
