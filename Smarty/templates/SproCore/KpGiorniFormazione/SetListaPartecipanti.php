<?php

/* kpro@tom06072017 */
		
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

require_once('modules/SproCore/KpGiorniFormazione/ClassKpGiorniFormazioneKp.php');

$rows = array();

if (!isset($_SESSION['authenticated_user_id'])) {

    $json = json_encode($rows);
    print $json;
    die;

    header("Location: ". $site_URL."/index.php");
	die; 
}
$current_user->id = $_SESSION['authenticated_user_id'];

$rows = array();

if( isset($_POST['id']) && isset($_POST['dati']) ){
    $id = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['id']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $id = substr($id, 0, 100);

    $dati = $_POST['dati'];
    
    $dati_decode = json_decode($dati);

    $lista = array();
    
    foreach($dati_decode as $dato){

        $lista[] = array("partecipazione" => $dato->partecipazione,
                        "presente" => $dato->presente,
                        "ore_effettuate" => $dato->ore_effettuate);

    }

    //print_r($lista); die;

    KpGiorniFormazioneKp::setGiorniPartecipazioniFormazione($id, $lista);

    $rows[] = array("return" => "ok");

}

$json = json_encode($rows);
print $json;

?>