<?php

// STANDARD

include_once(__DIR__.'/../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix;
session_start();

function AggiornaVisiteMediche($azienda,$giorni_in_scadenza){
	global $adb, $table_prefix,$current_user;

	$debug = false;
	
	$q_vecchi = "UPDATE {$table_prefix}_situazvisitemed SET
					aggiornato = '0'
					WHERE azienda =".$azienda;
	$adb->query($q_vecchi);

	$data_cor = date ("Y-m-d");
	list($anno_cor,$mese_cor,$giorno_cor) = explode("-",$data_cor);
	$in_scadenza = date("Y-m-d",mktime(0,0,0,$mese_cor,$giorno_cor+$giorni_in_scadenza,$anno_cor));

	$q_mansioni_risorsa = "SELECT manris.mansionirisorsaid, manris.stabilimento
                            FROM {$table_prefix}_mansionirisorsa manris
							INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = manris.mansionirisorsaid
							INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = manris.risorsa
							WHERE ent.deleted = 0 AND manris.stato_mansione = 'Attiva' AND cont.accountid =".$azienda;
	$res_mansioni_risorsa = $adb->query($q_mansioni_risorsa);
	$num_mansioni_risorsa = $adb->num_rows($res_mansioni_risorsa);

	for($i=0; $i<$num_mansioni_risorsa; $i++){
		$mansionirisorsaid = $adb->query_result($res_mansioni_risorsa,$i,'mansionirisorsaid');
		$stabilimento = $adb->query_result($res_mansioni_risorsa,$i,'stabilimento');
		
		$q_tipivisita = "(SELECT tv1.tipivisitamedid tipivisitamedid, mr1.risorsa risorsa, mr1.mansione mansione
                        FROM {$table_prefix}_mansionirisorsa mr1
						INNER JOIN {$table_prefix}_crmentityrel rel1 ON mr1.mansionirisorsaid =  rel1.crmid
						INNER JOIN {$table_prefix}_tipivisitamed tv1 ON rel1.relcrmid = tv1.tipivisitamedid
						INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = tv1.tipivisitamedid
						WHERE ent1.deleted = 0 AND mr1.mansionirisorsaid = ".$mansionirisorsaid." AND rel1.relmodule = 'TipiVisitaMed')
						UNION
						(SELECT tv2.tipivisitamedid tipivisitamedid, mr2.risorsa risorsa, mr2.mansione mansione 
                        FROM {$table_prefix}_mansionirisorsa mr2
						INNER JOIN {$table_prefix}_crmentityrel rel2 ON mr2.mansionirisorsaid = rel2.relcrmid
						INNER JOIN {$table_prefix}_tipivisitamed tv2 ON rel2.crmid = tv2.tipivisitamedid
						INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = tv2.tipivisitamedid
						WHERE ent2.deleted = 0 AND mr2.mansionirisorsaid = ".$mansionirisorsaid." AND rel2.module = 'TipiVisitaMed')";
		
		$res_tipivisita = $adb->query($q_tipivisita);
		$num_tipivisita = $adb->num_rows($res_tipivisita);
		
		for($y=0; $y<$num_tipivisita; $y++){
			$tipivisitamedid = $adb->query_result($res_tipivisita,$y,'tipivisitamedid');
			$risorsa = $adb->query_result($res_tipivisita,$y,'risorsa');
			$mansione = $adb->query_result($res_tipivisita,$y,'mansione');
			
			$q_visita = "SELECT evm.visita_medica,evm.data_visita,evm.data_fine_validita
							FROM {$table_prefix}_esitivisitemediche evm
							INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = evm.esitivisitemedicheid
							LEFT JOIN {$table_prefix}_visitemediche vm ON vm.visitemedicheid = evm.visita_medica
							WHERE ent.deleted = 0 AND evm.risorsa = ".$risorsa." 
							AND evm.tipo_visita_medica = ".$tipivisitamedid." 
							AND (vm.stato_visita_med = 'Eseguita' OR evm.visita_medica IS NULL OR evm.visita_medica = '' OR evm.visita_medica = 0)
							ORDER BY evm.data_fine_validita DESC";

			$res_visita = $adb->query($q_visita);	
			if($adb->num_rows($res_visita)>0){
				$visitemedicheid = $adb->query_result($res_visita,0,'visita_medica');
				/* kpro@tom180920191546 */
				if( $visitemedicheid == null && $visitemedicheid == '' ){
                    $visitemedicheid = 0;
				}
				/* kpro@tom180920191546 end */

				$data_visita = $adb->query_result($res_visita,0,'data_visita');
				$validita_visita = $adb->query_result($res_visita,0,'data_fine_validita');
				
				if($validita_visita <= $data_cor){
					$stato_sit_visita = 'Scaduta';
				}
				elseif($validita_visita > $data_cor && $validita_visita <= $in_scadenza){
					$stato_sit_visita = 'In scadenza';
				}
				else{
					$stato_sit_visita = 'Eseguita';
				}
			}
			else{
				$visitemedicheid = 0;
				$data_visita = '';
				$validita_visita = '';
				$stato_sit_visita = 'Non eseguita';
			}

			if( $debug ){
				
				if($risorsa == 26473 && $tipivisitamedid == 22131){
					print_r($q_visita);die;
				}

			}
			
			$q_verifica = "SELECT sit.situazvisitemedid FROM {$table_prefix}_situazvisitemed sit
							INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sit.situazvisitemedid
							WHERE ent.deleted = 0 AND sit.tipo_visita = ".$tipivisitamedid." 
							AND sit.mansione_risorsa =".$mansionirisorsaid;
			$res_verifica = $adb->query($q_verifica);
			if($adb->num_rows($res_verifica)>0){
				$situazvisitemedid = $adb->query_result($res_verifica,0,'situazvisitemedid');
				
				$q_upd_sit = "UPDATE {$table_prefix}_situazvisitemed SET
								tipo_visita = ".$tipivisitamedid.",
								visita_medica = ".$visitemedicheid.",
								data_visita = '".$data_visita."',
								validita_visita = '".$validita_visita."',
								azienda = ".$azienda.",
								stato_sit_visita = '".$stato_sit_visita."',
								risorsa = ".$risorsa.",
								mansione = ".$mansione.",
								mansione_risorsa = ".$mansionirisorsaid.",
								aggiornato = '1'
								WHERE situazvisitemedid =".$situazvisitemedid;
				$adb->query($q_upd_sit);
			}
			else{
				$situazione_visite = CRMEntity::getInstance('SituazVisiteMed'); 
				$situazione_visite->column_fields['assigned_user_id'] = '1';
				$situazione_visite->column_fields['mansione_risorsa'] = $mansionirisorsaid;
				$situazione_visite->column_fields['risorsa'] = $risorsa;
				$situazione_visite->column_fields['mansione'] = $mansione;
				$situazione_visite->column_fields['tipo_visita'] = $tipivisitamedid;
				if($visitemedicheid != 0){
					$situazione_visite->column_fields['visita_medica'] = $visitemedicheid;
				}
				/* kpro@bid240520180920 */
				$situazione_visite->column_fields['validita_visita'] = $validita_visita;
				$situazione_visite->column_fields['data_visita'] = $data_visita;
				/* kpro@bid240520180920 end */
				$situazione_visite->column_fields['stato_sit_visita'] = $stato_sit_visita;
				$situazione_visite->column_fields['azienda'] = $azienda;
				$situazione_visite->column_fields['stabilimento'] = $stabilimento;
				$situazione_visite->column_fields['aggiornato'] = '1';
				$situazione_visite->save('SituazVisiteMed', $longdesc=true, $offline_update=false, $triggerEvent=false); 
			}
		}
		
	}
	
	$del_vecchi = "SELECT situazvisitemedid FROM {$table_prefix}_situazvisitemed 
					WHERE aggiornato = '0' AND azienda =".$azienda;
	$res_del_vecchi = $adb->query($del_vecchi);
	$num_del_vecchi = $adb->num_rows($res_del_vecchi);
	for($i=0; $i<$num_del_vecchi; $i++){
		$vecchioid = $adb->query_result($res_del_vecchi,$i,'situazvisitemedid');
		
		$q_delete = "UPDATE {$table_prefix}_crmentity SET
						deleted = 1
						WHERE setype = 'SituazVisiteMed' AND crmid =".$vecchioid;
		$adb->query($q_delete);
		
		/*$q_delete1 = "DELETE FROM {$table_prefix}_situazvisitemed WHERE situazvisitemedid =".$vecchioid;
		$adb->query($q_delete1);
		$q_delete2 = "DELETE FROM {$table_prefix}_crmentity WHERE setype = 'SituazVisiteMed' AND crmid =".$vecchioid;
		$adb->query($q_delete2);*/
	}	

	$q_risorse = "SELECT cont.contactid FROM {$table_prefix}_contactdetails cont
					INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cont.contactid
					WHERE ent.deleted = 0 AND cont.accountid = ".$azienda;						
		
	$res_risorse = $adb->query($q_risorse);
	$num_risorse = $adb->num_rows($res_risorse);

	for($i=0; $i<$num_risorse; $i++){
		$contactid = $adb->query_result($res_risorse,$i,'contactid');
		
		$situazione_vis_contatto = 'Eseguita';
		
		$q_sit_vis = "SELECT sit.situazvisitemedid, sit.stato_sit_visita FROM {$table_prefix}_situazvisitemed sit
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sit.situazvisitemedid
						WHERE ent.deleted = 0 AND sit.risorsa = ".$contactid;
		$res_sit_vis = $adb->query($q_sit_vis);
		$num_sit_vis = $adb->num_rows($res_sit_vis);

		for($y=0; $y<$num_sit_vis; $y++){
			$situazvisitemedid = $adb->query_result($res_sit_vis,$y,'situazvisitemedid');
			$stato_sit_visita = $adb->query_result($res_sit_vis,$y,'stato_sit_visita');
			
			if($stato_sit_visita == 'Non eseguita' || $stato_sit_visita == 'Scaduta'){
				$situazione_vis_contatto = 'Da eseguire';
			}
			elseif($stato_sit_visita == 'In scadenza' && $situazione_vis_contatto == 'Eseguita'){
				$situazione_vis_contatto = 'In scadenza';
			}
			
		}
		
		$upd_cont = "UPDATE {$table_prefix}_contactdetails SET
						sit_vis_med_cont = '".$situazione_vis_contatto."'
						WHERE contactid = ".$contactid;
		$adb->query($upd_cont);
		
	}
	
}

?>