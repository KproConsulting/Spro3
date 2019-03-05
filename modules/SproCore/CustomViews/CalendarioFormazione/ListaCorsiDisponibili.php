<?php

/* kpro@tom29112016 */
		
/**
 * @author Tomiello Marco
 * @copyright (c) 2016, Kpro Consulting Srl
 */
 
require_once('KproConfig.ini.php');

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $site_URL, $default_language;
session_start();

//print_r($_SESSION);die;

if (!isset($_SESSION['authenticated_user_id'])) {
    header("Location: " . $site_URL . "/index.php?module=Accounts&action=index");
}
$current_user->id = $_SESSION['authenticated_user_id'];

$data_corrente = date("Y-m-d");
list($anno_corrente, $mese_corrente, $giorno_corrente) = explode("-", $data_corrente);
$data_corrente_inv = date("d-m-Y", mktime(0, 0, 0, $mese_corrente, $giorno_corrente, $anno_corrente));

if(isset($_GET['tipi_corso'])){
    $filtro_tipi_corso = addslashes(html_entity_decode(strip_tags($_GET['tipi_corso']), ENT_QUOTES, $default_charset));
    $filtro_tipi_corso = substr($filtro_tipi_corso, 0, 255);
    if($filtro_tipi_corso == ''){
        $filtro_tipi_corso = 'all';
    }
}
else{
    $filtro_tipi_corso = 'all';
}

$rows = array();
    
$q_formazione = "SELECT 
					form.kpformazioneid formazioneid,
					form.kp_nome_corso formazione_name,
					form.kp_data_scad_for validita_formazione,
					form.kp_data_inizio_form data_inizio_formazione,
					form.kp_data_formazione data_formazione,
					form.kp_tot_ore_formazio durata_formazione,
					form.kp_locazione locazione_formazione,
					form.kp_stato_corso_for stato_formazione_for,
					form.kp_ora_inizio_for ora_inizio_for,
					form.kp_ora_fine_for ora_fine_for,
					tc.tipicorso_name tipicorso_name
					from {$table_prefix}_kpformazione form
					inner join {$table_prefix}_crmentity ent on ent.crmid = form.kpformazioneid
					inner join {$table_prefix}_tipicorso tc on tc.tipicorsoid = form.kp_tipo_corso
					where ent.deleted = 0 and form.kp_stato_corso_for in ('Confermato', 'In definizione')";

if($filtro_tipi_corso != 'all'){

	$q_formazione .= " and tc.tipicorsoid = ".$filtro_tipi_corso;

}

$q_formazione .= " order by form.kp_data_inizio_form asc";

$res_formazione = $adb->query($q_formazione);
$num_formazione = $adb->num_rows($res_formazione);

for($i=0; $i < $num_formazione; $i++){
	
	$formazioneid = $adb->query_result($res_formazione, $i, 'formazioneid');
	$formazioneid = html_entity_decode(strip_tags($formazioneid), ENT_QUOTES,$default_charset);
	
	$formazione_name = $adb->query_result($res_formazione, $i, 'formazione_name');
	$formazione_name = html_entity_decode(strip_tags($formazione_name), ENT_QUOTES,$default_charset);
	
	$tipicorso_name = $adb->query_result($res_formazione, $i, 'tipicorso_name');
	$tipicorso_name = html_entity_decode(strip_tags($tipicorso_name), ENT_QUOTES,$default_charset);
	
	$durata_formazione = $adb->query_result($res_formazione, $i, 'durata_formazione');
	$durata_formazione = html_entity_decode(strip_tags($durata_formazione), ENT_QUOTES,$default_charset);
	if($durata_formazione == null || $durata_formazione == ""){
		$durata_formazione = 0;
	}
	
	$locazione_formazione = $adb->query_result($res_formazione, $i, 'locazione_formazione');
	$locazione_formazione = html_entity_decode(strip_tags($locazione_formazione), ENT_QUOTES,$default_charset);
	
	$ora_inizio_for = $adb->query_result($res_formazione, $i, 'ora_inizio_for');
	$ora_inizio_for = html_entity_decode(strip_tags($ora_inizio_for), ENT_QUOTES,$default_charset);
	if($ora_inizio_for == null || $ora_inizio_for == ""){
		$ora_inizio_for = "08:00";
	}
	
	$ora_fine_for = $adb->query_result($res_formazione, $i, 'ora_fine_for');
	$ora_fine_for = html_entity_decode(strip_tags($ora_fine_for), ENT_QUOTES,$default_charset);
	if($ora_fine_for == null || $ora_fine_for == "" || $ora_fine_for == "00:00"){
		$ora_fine_for = "18:00";
	}
	
	$validita_formazione = $adb->query_result($res_formazione, $i, 'validita_formazione');
	$validita_formazione = html_entity_decode(strip_tags($validita_formazione), ENT_QUOTES,$default_charset);
	if($validita_formazione != null && $validita_formazione != "" && $validita_formazione != "0000-00-00"){
		list($anno_val, $mese_val, $giorno_val) = explode("-", $validita_formazione);
		$validita_formazione = date("d/m/Y", mktime(0, 0, 0, $mese_val, $giorno_val, $anno_val));
	}
	else{
		$validita_formazione = "";
	}

	$data_inizio_formazione = $adb->query_result($res_formazione, $i, 'data_inizio_formazione');
	$data_inizio_formazione = html_entity_decode(strip_tags($data_inizio_formazione), ENT_QUOTES,$default_charset);
	if($data_inizio_formazione != null && $data_inizio_formazione != "" && $data_inizio_formazione != "0000-00-00"){
		
		list($anno_for, $mese_for, $giorno_for) = explode("-", $data_inizio_formazione);
		$data_inizio_formazione = date("d/m/Y", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
			
		$start_date = date("Y-m-d", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
		$start_date = $start_date." ".$ora_inizio_for;
	}
	else{
		$data_inizio_formazione = "";
	}
	
	$data_formazione = $adb->query_result($res_formazione, $i, 'data_formazione');
	$data_formazione = html_entity_decode(strip_tags($data_formazione), ENT_QUOTES,$default_charset);
	if($data_formazione != null && $data_formazione != "" && $data_formazione != "0000-00-00"){
		
		if($data_inizio_formazione == ""){
			list($anno_for, $mese_for, $giorno_for) = explode("-", $data_formazione);
			$data_inizio_formazione = date("d/m/Y", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
		
			$start_date = date("Y-m-d", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
			$start_date = $start_date." ".$ora_inizio_for;
		}

		list($anno_for_end, $mese_for_end, $giorno_for_end) = explode("-", $data_formazione);
		$data_formazione = date("d/m/Y", mktime(0, 0, 0, $mese_for_end, $giorno_for_end, $anno_for_end));
		
		$end_date = date("Y-m-d", mktime(0, 0, 0, $mese_for_end, $giorno_for_end, $anno_for_end));
		$end_date = $end_date." ".$ora_fine_for;
			
	}
	else{
		$data_formazione = "";
	}

	$stato_formazione = $adb->query_result($res_formazione, $i, 'stato_formazione_for');
	$stato_formazione = html_entity_decode(strip_tags($stato_formazione), ENT_QUOTES,$default_charset);
	if($stato_formazione == 'In definizione'){
		$colore = "#ffcc00";
	}
	else{
		$colore = "#42f4b9";
	}
	
	$array_giorni_formazione = getGiorniFormazione($formazioneid);
	$numero_giorni_formazione = count($array_giorni_formazione);
	if($numero_giorni_formazione > 0){
		for($j = 0; $j < $numero_giorni_formazione; $j++){

			$rows[] = array(
				'id' => $array_giorni_formazione[$j]['id_giorno_formazione'],
				'formazioneid' => $formazioneid,
				'formazione_name' => $formazione_name." (Giorno ".($j + 1)." di {$numero_giorni_formazione})",
				'tipicorso_name' => $tipicorso_name,
				'data_inizio_formazione' => $data_inizio_formazione,
				'data_formazione' => $array_giorni_formazione[$j]['data_formazione'],
				'validita_formazione' => $validita_formazione,
				'colore' => $colore,
				'durata_formazione' => (int)$array_giorni_formazione[$j]['durata_formazione'],
				'locazione_formazione' => $array_giorni_formazione[$j]['locazione_formazione'],
				'corso_interaziendale' => 'si',
				'stato_formazione' => $stato_formazione,	//kpro@tom050320191416
				'start_date' => $array_giorni_formazione[$j]['start_date'],
				'end_date' => $array_giorni_formazione[$j]['end_date'],
				'anno_for' => (int)$array_giorni_formazione[$j]['anno_for'],
				'mese_for' => (int)$array_giorni_formazione[$j]['mese_for'],
				'giorno_for' => (int)$array_giorni_formazione[$j]['giorno_for'],
				'ora_for' => $array_giorni_formazione[$j]['ora_for'],
				'minuti_for' => $array_giorni_formazione[$j]['minuti_for'],
				'anno_for_end' => (int)$array_giorni_formazione[$j]['anno_for_end'],
				'mese_for_end' => (int)$array_giorni_formazione[$j]['mese_for_end'],
				'giorno_for_end' => (int)$array_giorni_formazione[$j]['giorno_for_end'],
				'ora_for_end' => $array_giorni_formazione[$j]['ora_for_end'],
				'minuti_for_end' => $array_giorni_formazione[$j]['minuti_for_end'],
				'anno_val' => (int)$anno_val,
				'mese_val' => (int)$mese_val,
				'giorno_val' => (int)$giorno_val,
				'ora_end' => (int)0,
				'minuti_end' =>(int)0
			);
		}
	}
	else{
		$rows[] = array(
			'id' => $formazioneid,
			'formazioneid' => $formazioneid,
			'formazione_name' => $formazione_name,
			'tipicorso_name' => $tipicorso_name,
			'data_inizio_formazione' => $data_inizio_formazione,
			'data_formazione' => $data_formazione,
			'validita_formazione' => $validita_formazione,
			'colore' => $colore,
			'durata_formazione' => (int)$durata_formazione,
			'locazione_formazione' => $locazione_formazione,
			'corso_interaziendale' => 'si',
			'stato_formazione' => $stato_formazione,
			'start_date' => $start_date,
			'end_date' => $end_date,
			'anno_for' => (int)$anno_for,
			'mese_for' => (int)$mese_for,
			'giorno_for' => (int)$giorno_for,
			'ora_for' => (int)8,
			'minuti_for' => (int)0,
			'anno_for_end' => (int)$anno_for_end,
			'mese_for_end' => (int)$mese_for_end,
			'giorno_for_end' => (int)$giorno_for_end,
			'ora_for_end' => (int)18,
			'minuti_for_end' => (int)0,
			'anno_val' => (int)$anno_val,
			'mese_val' => (int)$mese_val,
			'giorno_val' => (int)$giorno_val,
			'ora_end' => (int)0,
			'minuti_end' =>(int)0
		);

	}
	
}

$json = json_encode($rows);
print $json;

function getGiorniFormazione($formazioneid){
	global $adb, $table_prefix, $current_user, $site_URL, $default_language;

	$result = array();

	$q = "SELECT gform.kpgiorniformazioneid,
		gform.kp_data_formazione data_formazione,
		gform.kp_ora_inizio_for ora_inizio_for,
		gform.kp_ora_fine_for ora_fine_for,
		gform.kp_locazione locazione,
		gform.kp_ore_formazione durata_formazione
		FROM {$table_prefix}_kpgiorniformazione gform
		INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = gform.kpgiorniformazioneid
		WHERE ent.deleted = 0 AND gform.kp_corso_formazione = ".$formazioneid."
		ORDER BY gform.kp_data_formazione ASC";
	$res = $adb->query($q);
	$num = $adb->num_rows($res);
	if($num > 0){
		for($i = 0; $i < $num; $i++){
			$id_giorno_formazione = $adb->query_result($res, $i, 'kpgiorniformazioneid');
			$id_giorno_formazione = html_entity_decode(strip_tags($id_giorno_formazione), ENT_QUOTES,$default_charset);

			$locazione_formazione = $adb->query_result($res, $i, 'locazione');
			$locazione_formazione = html_entity_decode(strip_tags($locazione_formazione), ENT_QUOTES,$default_charset);

			$ora_inizio_for = $adb->query_result($res, $i, 'ora_inizio_for');
			$ora_inizio_for = html_entity_decode(strip_tags($ora_inizio_for), ENT_QUOTES,$default_charset);
			if($ora_inizio_for == null || $ora_inizio_for == ""){
				$ora_inizio_for = "08:00";
			}
			list($ora_for, $min_for) = explode(":", $ora_inizio_for);

			$ora_fine_for = $adb->query_result($res, $i, 'ora_fine_for');
			$ora_fine_for = html_entity_decode(strip_tags($ora_fine_for), ENT_QUOTES,$default_charset);
			if($ora_fine_for == null || $ora_fine_for == ""){
				$ora_fine_for = "18:00";
			}
			list($ora_for_end, $min_for_end) = explode(":", $ora_fine_for);

			$durata_formazione = $adb->query_result($res, $i, 'durata_formazione');
			$durata_formazione = html_entity_decode(strip_tags($durata_formazione), ENT_QUOTES,$default_charset);
			if($durata_formazione == null || $durata_formazione == ""){
				$durata_formazione = 0;
			}

			$data_formazione = $adb->query_result($res, $i, 'data_formazione');
			$data_formazione = html_entity_decode(strip_tags($data_formazione), ENT_QUOTES,$default_charset);
			if($data_formazione != null && $data_formazione != "" && $data_formazione != "0000-00-00"){
				
				list($anno_for, $mese_for, $giorno_for) = explode("-", $data_formazione);
				$data_formazione = date("d/m/Y", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
				
				$data_formazione_end = date("d/m/Y", mktime(0, 0, 0, $anno_for, $giorno_for, $anno_for));
				list($giorno_for_end, $mese_for_end, $anno_for_end) = explode("/", $data_formazione_end);
				
				$start_date = date("Y-m-d", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
				$start_date = $start_date." ".$ora_inizio_for;
				
				$end_date = date("Y-m-d", mktime(0, 0, 0, $mese_for, $giorno_for, $anno_for));
				$end_date = $end_date." ".$ora_fine_for;
					
			}
			else{
				$data_formazione = "";
			}

			$result[] = array(
				'id_giorno_formazione' => $id_giorno_formazione,
				'data_formazione' => $data_formazione,
				'durata_formazione' => $durata_formazione,
				'locazione_formazione' => $locazione_formazione,
				'start_date' => $start_date,
				'end_date' => $end_date,
				'anno_for' => $anno_for,
				'mese_for' => $mese_for,
				'giorno_for' => $giorno_for,
				'ora_for' => $ora_for,
				'minuti_for' => $min_for,
				'anno_for_end' => $anno_for_end,
				'mese_for_end' => $mese_for_end,
				'giorno_for_end' => $giorno_for_end,
				'ora_for_end' => $ora_for_end,
				'minuti_for_end' => $min_for_end
			);
		}
	}

	return $result;
}
	
?>