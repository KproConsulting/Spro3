<?php

/* kpro@tom18072017 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $site_URL, $default_charset;
session_start();

require_once('modules/SproCore/CustomViews/KpBPMNcreator/KpBPMN.php');

$rows = array();

if (!isset($_SESSION['authenticated_user_id'])) {

    $json = json_encode($rows);
    print $json;
    die;

    header("Location: ". $site_URL."/index.php");
	die; 
}
$current_user->id = $_SESSION['authenticated_user_id'];

if(isset($_GET['nome'])){
    $nome = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['nome']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $nome = substr($nome, 0, 255);
}
else{
    $nome = "";
}

if(isset($_GET['citta'])){
    $citta = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['citta']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $citta = substr($citta, 0, 255);
}
else{
    $citta = "";
}

$filtro = array("nome" => $nome,
                "citta" => $citta);

$focus = CRMEntity::getInstance('KpProcedure'); 
$rows = $focus->getListaAziende($filtro);

$json = json_encode($rows);
print $json;

?>