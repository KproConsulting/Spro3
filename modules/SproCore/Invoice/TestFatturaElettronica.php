<?php 

/* kpro@tom26012018*/

/**
 * @author Tomiello Marco
 * @copyright (c) 2018, Kpro Consulting Srl
 */
die("Togliere die!");

include_once('../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $default_charset, $site_URL, $small_page_title;
session_start();

$id = 227351;

$path_temp = __DIR__.'/'.date("YmdHis")."_".rand(0 , 100000).'/';

$focus_fattura = CRMEntity::getInstance('Invoice');
$focus_fattura->getFatturaElettronica($id, $path_temp);

?>