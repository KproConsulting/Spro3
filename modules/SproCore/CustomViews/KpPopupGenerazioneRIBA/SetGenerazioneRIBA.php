<?php

/* kpro@bid18062018 */

/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 */

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $site_URL, $default_charset;
session_start();

require_once('modules/SproCore/CustomViews/KpPopupGenerazioneRIBA/generazione_riba_utils.php');

$rows = array();

if (!isset($_SESSION['authenticated_user_id'])) {

    $json = json_encode($rows);
    print $json;
    die;

    header("Location: ". $site_URL."/index.php");
	die; 
}
$current_user->id = $_SESSION['authenticated_user_id'];

if(isset($_GET['mese']) && isset($_GET['anno'])){
    $mese_scadenza = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['mese']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $mese_scadenza = substr($mese_scadenza,0,100);

    $anno_scadenza = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['anno']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $anno_scadenza = substr($anno_scadenza,0,100);

    $totale_riba_create = 0;
    $riba_con_banca = 0;
    $riba_senza_banca = 0;

    $data_corrente = date("Y-m-d");
	
    $q = "SELECT scad.scadenziarioid
        FROM {$table_prefix}_scadenziario scad
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = scad.scadenziarioid
        WHERE ent.deleted = 0 AND scad.tipo_scadenza_pag = 'Pagamento cliente'
        AND scad.stato_scadenza_pag = 'Aperta' 
        AND (scad.condizioni_pagamento = 'RIBA' OR scad.condizioni_pagamento = 'MP12')
        AND MONTH(scad.data_scadenza) = '".$mese_scadenza."'
        AND YEAR(scad.data_scadenza) = '".$anno_scadenza."'";

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
	
    for($i=0; $i<$num; $i++){
        $id = $adb->query_result($res,$i,'scadenziarioid');

        $risultato = generaRIBA($id);

        switch ($risultato){
            case "1":	
                $totale_riba_create += 1;
                $riba_con_banca += 1;
                break;
            case "2":	
                $totale_riba_create += 1;
                $riba_senza_banca += 1;
                break;
        }

    }

    $rows[] = array(
        "totale_riba_create" => $totale_riba_create,
        "riba_con_banca" => $riba_con_banca,
        "riba_senza_banca" => $riba_senza_banca
    );
	
}

$json = json_encode($rows);
print $json;

?>