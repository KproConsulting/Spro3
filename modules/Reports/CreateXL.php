<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
********************************************************************************/
global $php_max_execution_time;
set_time_limit($php_max_execution_time);

// crmv@30385 - cambio classe php per scrivere i xls - tutto il file
/* crmv@101168 */

require_once('include/PHPExcel/PHPExcel.php');
require_once("modules/Reports/ReportRun.php");
require_once("modules/Reports/Reports.php");

global $tmp_dir, $root_directory, $mod_strings, $app_strings; // crmv@29686

// crmv@139057
if ($_REQUEST['batch_export'] == 1 && !empty($filePath)) {
	$fname = $filePath;
} else {
	$fname = tempnam($root_directory.$tmp_dir, "merge2.xls");
}
// crmv@139057e

# Write out the data
$reportid = intval($_REQUEST["record"]);
$folderid = intval($_REQUEST["folderid"]);

$oReport = Reports::getInstance($reportid);

//crmv@sdk-25785

$sdkrep = SDK::getReport($reportid, $folderid);
if (!is_null($sdkrep)) {
	require_once($sdkrep['reportrun']);
	$oReportRun = new $sdkrep['runclass']($reportid);
	$oReportRun->setOutputFormat('XLS');
} else {
	$oReportRun = new ReportRun($reportid);
}

$_REQUEST['limit_string'] = 'ALL'; // crmv@96742

//crmv@sdk-25785e

// crmv@97862
if ($_REQUEST["startdate"] && $_REQUEST["enddate"]) {
	$oReportRun->setStdFilterFromRequest($_REQUEST);
}
// crmv@97862e

// crmv@29686
$temp_xls_report = $oReportRun->GenerateReport("XLS"); // to initialize stuff in reports object
$mainOutClass = $oReportRun->getOutputClass(); // crmv@133409
$numrows = count($temp_xls_report); //crmv@139048
if ($_REQUEST['export_report_main'] == 1)
	$arr_val = $temp_xls_report;
if ($_REQUEST['export_report_totals'] == 1)
	$totalxls = $oReportRun->GenerateReport("TOTALXLS");
if ($_REQUEST['export_report_summary'] == 1)
	$counttotalxls = $oReportRun->GenerateReport("COUNTXLS");
// crmv@29686e

//crmv@36517 crmv@139048
//try caching method from best to worse
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_sqlite3;
$cacheEnabled = PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
if ($cacheEnabled !== true){
	$cacheSettings = array( 'memoryCacheSize' => '8GB');
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
	$cacheEnabled = PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);		
}
//crmv@36517e crmv@139048e

$objPHPExcel = new PHPExcel();
$objPHPExcel->removeSheetByIndex(0); // remove default sheet

$objPHPExcel->getProperties()
	->setCreator("VTE CRM")
	->setLastModifiedBy("VTE CRM")
	->setTitle("Report"); // TODO: report title

$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

$xlsStyle1 = new PHPExcel_Style();
$xlsStyle2 = new PHPExcel_Style();

$xlsStyle1->applyFromArray(
	array('font' => array(
		'name' => 'Arial',
		'bold' => true,
		'size' => 12,
		'color' => array( 'rgb' => '0000FF' )
	),
));

$xlsStyle2->applyFromArray(
	array('font' => array(
		'name' => 'Arial',
		'bold' => true,
		'size' => 11,
	),
));

// crmv@157509
// only the number of decimal digits and the presence of thousand separator
// can be decided, the character is in the Excel general options
$baseNumberFormat = 
	($current_user->thousands_separator != '' ? "#,##" : "")
	."0"
	.($current_user->decimals_num > 0 ? '.'.str_repeat('0', $current_user->decimals_num) : '');
// crmv@157509e

// crmv@139057
if (!function_exists('addXlsHeader')) {
	function addXlsHeader($sheet, $oReportRun) {
		global $xlsStyle1;
		$output = $oReportRun->getOutputClass();
		$head = $output->getSimpleHeaderArray();
		if ($head && count($head) > 0) {
			$count = 0;
			$sheet->setSharedStyle($xlsStyle1, 'A1:'.PHPExcel_Cell::stringFromColumnIndex(count($head)).'1');
			foreach($head as $key) {
				$sheet->setCellValueByColumnAndRow($count, 1, $key);
				//$sheet->getColumnDimensionByColumn($count)->setAutoSize(true); // crmv@97862 crmv@139048
				$count = $count + 1;
			}
		}
	}
}
// crmv@139057e

// crmv@29686 - riepilogo
if (is_array($counttotalxls) && count($counttotalxls) > 0) {
	$sheet0 = new PHPExcel_Worksheet($objPHPExcel, $mod_strings['LBL_REPORT_SUMMARY']);
	$objPHPExcel->addSheet($sheet0);

	// header
	$colcount = 0;
	$rowcount = 1;
	$sheet0->setSharedStyle($xlsStyle1, 'A1:'.PHPExcel_Cell::stringFromColumnIndex(count($counttotalxls[0])).'1');
	foreach ($counttotalxls[0] as $key=>$v) {
		$sheet0->setCellValueByColumnAndRow($colcount++, $rowcount, $key);
	}

	foreach ($counttotalxls as $xlsrow) {
		++$rowcount;
		$colcount = 0;
		foreach ($xlsrow as $k=>$xlsval) {
			$sheet0->setCellValueByColumnAndRow($colcount++, $rowcount, $xlsval);
		}
	}
} elseif ($_REQUEST['export_report_summary'] == 1 && $oReportRun->hasSummary()) {
	// add an empty sheet with the column names
	$sheet0 = new PHPExcel_Worksheet($objPHPExcel, $mod_strings['LBL_REPORT_SUMMARY']);
	$objPHPExcel->addSheet($sheet0);
	$oReportRun->setReportTab('COUNT');
	addXlsHeader($sheet0, $oReportRun);
}

if (is_array($arr_val) && is_array($arr_val[0])) {
	$count = 0;
	$sheet1 = new PHPExcel_Worksheet($objPHPExcel, $app_strings['Report']);
	$objPHPExcel->addSheet($sheet1);
	// crmv@29686e

	$sheet1->setSharedStyle($xlsStyle1, 'A1:'.PHPExcel_Cell::stringFromColumnIndex(count($arr_val[0])).'1');
	foreach($arr_val[0] as $key=>$value) {
		$sheet1->setCellValueByColumnAndRow($count, 1, $key);
		//$sheet1->getColumnDimensionByColumn($count)->setAutoSize(true); // crmv@97862 crmv@139048
		$count = $count + 1;
	}

	$rowcount=2;
	foreach($arr_val as $key=>$array_value)
	{
		$dcount = 0;
		foreach($array_value as $hdr=>$value)
		{
			$value = decode_html($value);
			if (strpos($value,'=') === 0) $value = "'".$value;	//crmv@52501
			
			$hcell = $mainOutClass->getHeaderByIndex($dcount); // crmv@133409
			$cell = $mainOutClass->getCellByIndex($rowcount-2, $dcount); // crmv@157509

			//crmv@29016
			//check for strings that looks like numbers (starting with 0)
			if (is_numeric($value) && $value !== '0' && substr(strval($value), 0, 1) == '0' && !preg_match('/[,.]/', $value)) { // crmv@30385 crmv@98764
				$sheet1->setCellValueExplicitByColumnAndRow($dcount, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_STRING);
			// crmv@133409
			} elseif ($hcell['wstype'] == 'date' || $hcell['wstype'] == 'datetime') {
				// set the date format (the value is in db format)
				
				/* kpro@tom171220201531 */
				//$value = PHPExcel_Shared_Date::stringToExcel($value);
				/* kpro@tom171220201531 end */
				$dateFormat = ($hcell['wstype'] == 'datetime' ? 'yyyy-mm-dd h:mm:ss' : PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2);
				$sheet1->setCellValueByColumnAndRow($dcount, $rowcount, $value);
				$sheet1->getStyleBycolumnAndRow($dcount, $rowcount)->getNumberFormat()->setFormatCode($dateFormat);
			// crmv@133409e
			// crmv@38798 crmv@157509 - currency fields
			} elseif ($cell['currency_symbol']) {
				$cell['currency_symbol'] = html_entity_decode($cell['currency_symbol']); //mycrmv@167567
				$value = floatval(trim(str_replace($cell['currency_symbol'], '', $value)));
				$sheet1->setCellValueExplicitByColumnAndRow($dcount, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$numberFormat = "\"{$cell['currency_symbol']}\" ".$baseNumberFormat;
				/* kpro@tom121120181158 */
				//$sheet1->getStyleBycolumnAndRow($dcount, $rowcount)->getNumberFormat()->setFormatCode($numberFormat); //mycrmv@168944
				/* kpro@tom121120181158 end */
			} elseif (is_numeric($value)) {
				$sheet1->setCellValueExplicitByColumnAndRow($dcount, $rowcount, $value, PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet1->getStyleBycolumnAndRow($dcount, $rowcount)->getNumberFormat()->setFormatCode($baseNumberFormat);
			// crmv@38798e crmv@157509e
			} else {
				$sheet1->setCellValueByColumnAndRow($dcount, $rowcount, $value);
			}
			//crmv@29016e
			$dcount = $dcount + 1;
		}
		$rowcount++;
	}
} elseif ($_REQUEST['export_report_main'] == 1) {
	$sheet1 = new PHPExcel_Worksheet($objPHPExcel, $app_strings['Report']);
	$objPHPExcel->addSheet($sheet1);
	$oReportRun->setReportTab('MAIN');
	addXlsHeader($sheet1, $oReportRun);
}


$rowcount = 1; // crmv@29686
$count=1;
if (is_array($totalxls)) {
	if(is_array($totalxls[0])) {
		$sheet2 = new PHPExcel_Worksheet($objPHPExcel, $mod_strings['LBL_REPORT_TOTALS']);
		$objPHPExcel->addSheet($sheet2);

		$sheet2->setSharedStyle($xlsStyle1, 'A1:'.PHPExcel_Cell::stringFromColumnIndex(count($totalxls[0])).'1');
		foreach($totalxls[0] as $key=>$value) {
			$chdr=substr($key,-3,3);
			$sheet2->setCellValueByColumnAndRow($count++, $rowcount, $mod_strings[$chdr]);
		}
	}
	$rowcount++;
	foreach($totalxls as $key=>$array_value) {
		$dcount = 1;
		foreach($array_value as $hdr=>$value) {
			if ($dcount==1)	{
				$sheet2->setCellValueByColumnAndRow(0, $rowcount, substr($hdr,0,strlen($hdr)-4));
			}
			$value = decode_html($value);
			$sheet2->setCellValueByColumnAndRow($dcount++, $rowcount, $value);
		}
		$rowcount++; //crmv@36517
	}
} elseif ($_REQUEST['export_report_totals'] == 1 && $oReportRun->hasTotals()) {
	// add an empty sheet with the column names
	$sheet2 = new PHPExcel_Worksheet($objPHPExcel, $mod_strings['LBL_REPORT_TOTALS']);
	$objPHPExcel->addSheet($sheet2);
	$oReportRun->setReportTab('TOTAL');
	addXlsHeader($sheet2, $oReportRun);
}


// add an empty sheet if none inserted, otherwise MS Excel won't open the file
if ($objPHPExcel->getSheetCount() == 0) {
	$sheet = new PHPExcel_Worksheet($objPHPExcel, $app_strings['Report']);
	$objPHPExcel->addSheet($sheet);
}

$objPHPExcel->setActiveSheetIndex(0);	// crmv@112208

//crmv@139057 crmv@139048
$excel_type = 'Excel5';
$excel_ext = 'xls';
$app_type = 'application/vnd.ms-excel';
if ($numrows > 80000){
	$excel_type = 'CSV';
	$excel_ext = 'csv';
	$app_type = 'text/csv';
} elseif ($numrows > 65000){	
	$excel_type = 'Excel2007';
	$excel_ext = 'xlsx';
	$app_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $excel_type); // replace with Excel2007 and change extension to xlsx for the new format
$objWriter->setPreCalculateFormulas(false);
$objWriter->save($fname);

if ($_REQUEST['batch_export'] != 1) {

	if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
	{
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	}
	header("Content-Type: {$app_type}");
	header("Content-Length: ".@filesize($fname));
	header('Content-disposition: attachment; filename="Reports.'.$excel_ext.'"');
	$fh=fopen($fname, "rb");
	fpassthru($fh);
	//unlink($fname);
	exit;
}
//crmv@139057e crmv@139048e