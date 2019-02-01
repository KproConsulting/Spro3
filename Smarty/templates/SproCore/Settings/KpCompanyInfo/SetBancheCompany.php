<?php

/* kpro@tom290120190943 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2019, Kpro Consulting Srl
 */

include_once('../../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $site_URL, $default_charset;
session_start();

$rows = array();

if (!isset($_SESSION['authenticated_user_id'])) {

    $json = json_encode($rows);
    print $json;
    die;

    header("Location: ". $site_URL."/index.php");
	die; 
}
$current_user->id = $_SESSION['authenticated_user_id'];

if( isset($_POST['dati']) ){

    $dati = $_POST['dati'];

    $dati_decode = Zend_Json::decode($dati);

    //file_put_contents( __DIR__."/kp_log.txt", print_r($dati_decode, true) );
    
    foreach($dati_decode as $dato){

        SetDatiBanca($dato["id"], $dato["banca"], $dato["nome_istituto"], $dato["iban"], $dato["abi"], $dato["cab"], $dato["bic"]);

    }

}

$rows[] = array("result" => "ok");

$json = json_encode($rows);
print $json;

function SetDatiBanca($id, $banca, $nome_istituto, $iban, $abi, $cab, $bic){
    global $current_language, $adb, $table_prefix, $default_charset;

    $nome_istituto = html_entity_decode(strip_tags($nome_istituto), ENT_QUOTES, $default_charset);
    $nome_istituto = addslashes($nome_istituto);

    $banca = html_entity_decode(strip_tags($banca), ENT_QUOTES, $default_charset);
    $banca = addslashes($banca);

    $update = "UPDATE kp_banche_company SET
                banca = '".$banca."',
                nome_istituto = '".$nome_istituto."',
                iban = '".$iban."',
                abi = '".$abi."',
                cab = '".$cab."',
                bic = '".$bic."',
                aggiornato = '1'
                WHERE id = ".$id;

    $adb->query($update);

}

?>