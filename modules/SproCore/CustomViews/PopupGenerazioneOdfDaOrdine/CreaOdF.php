<?php

/* kpro@tom30112017 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $site_URL;
session_start();

require_once('modules/SproCore/KpSalesOrderLine/ClassKpSalesOrderLineKp.php');

if(!isset($_SESSION['authenticated_user_id'])){
    header("Location: ".$site_URL."/index.php?module=Accounts&action=index");
}
$current_user->id = $_SESSION['authenticated_user_id'];

$rows = array();
if(isset($_GET['ordine']) && isset($_GET['prodotto']) && isset($_GET['amm_sconto']) && isset($_GET['valore'])){
    $ordine = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['ordine']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $ordine = substr($ordine,0,100);
    
    $prodotto = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['prodotto']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
	$prodotto = substr($prodotto,0,100);
	
	$amm_sconto = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['amm_sconto']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $amm_sconto = substr($amm_sconto,0,100);
    if($amm_sconto == null || $amm_sconto == ''){
        $amm_sconto = 0;
	}
	$amm_sconto = number_format($amm_sconto, 2, '.', '');
    
    $valore = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['valore']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $valore = substr($valore,0,100);
    if($valore == null || $valore == ''){
        $valore = 0;
    }
    
    if(isset($_GET['salesorderlineid'])){
        $salesorderlineid = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['salesorderlineid']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
        $salesorderlineid = substr($salesorderlineid,0,100);
    }
    else{
        $salesorderlineid = 0;
    }
    
    if(isset($_GET['ultima_riga'])){
        $ultima_riga = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['ultima_riga']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
        $ultima_riga = substr($ultima_riga,0,100);
    }
    else{
        $ultima_riga = 'false';
    }
    
    $data_corrente = date("Y-m-d");
    $data_corrente_inv = date("d-m-Y");
    
    $q_sales_order = "SELECT 
						so.salesorder_no salesorder_no,
						so.accountid accountid,
						so.data_ordine data_ordine,
						so.kp_conto_corrente kp_conto_corrente,
						so.kp_banca_cliente kp_banca_cliente,
						so.mod_pagamento mod_pagamento,
						so.contactid contactid,
						ent.smownerid assegnatario,
						so.kp_business_unit businessunit,
						so.commessa commessa,
						so.kp_rif_ordine_cli kp_rif_ordine_cli,
						so.kp_data_ord_cli kp_data_ord_cli,
						so.kp_codice_cup kp_codice_cup,
						so.kp_codice_cig kp_codice_cig,
						acc.accountname accountname,
						sol.kp_soggetto subject_riga,
						sol.kp_prodotto kp_prodotto,
						sol.kp_quantita_ordine kp_quantita_ordine,
						sol.kp_valore_riga kp_valore_riga,
						sol.kp_per_sconto kp_per_sconto,
						sol.kp_amm_sconto kp_amm_sconto,
						sol.kp_listprice kp_listprice,
						sol.kp_salesorder kp_salesorder,
						sol.kp_val_no_sconto kp_val_no_sconto,
						sol.description descrizione_riga,
						sol.kp_commento_riga commento_riga,
						sol.kp_agente kp_agente,
						sol.kp_id_tassa kp_id_tassa,
						sol.kp_nome_tassa kp_nome_tassa
						FROM {$table_prefix}_salesorder so
						INNER JOIN {$table_prefix}_salesordercf socf ON socf.salesorderid = so.salesorderid
						INNER JOIN {$table_prefix}_kpsalesorderline sol ON sol.kp_salesorder = so.salesorderid
						INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
						INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = sol.kpsalesorderlineid
						INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = sol.kp_prodotto
						LEFT JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
						WHERE sol.kpsalesorderlineid = ".$salesorderlineid;
					
	$q_sales_order .= " AND ent3.setype = 'Services'";

	$res_sales_order = $adb->query($q_sales_order); 							
	if($adb->num_rows($res_sales_order)>0){		
		$salesorder_no = $adb->query_result($res_sales_order, 0, 'salesorder_no'); 
		$salesorder_no = html_entity_decode(strip_tags($salesorder_no), ENT_QUOTES,$default_charset);

		$accountid = $adb->query_result($res_sales_order, 0, 'accountid'); 
		$accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset); 
		
		$data_ordine = $adb->query_result($res_sales_order, 0, 'data_ordine');
		$data_ordine = html_entity_decode(strip_tags($data_ordine), ENT_QUOTES,$default_charset);

		$rif_ordine_cli = $adb->query_result($res_sales_order, 0, 'kp_rif_ordine_cli');
		$rif_ordine_cli = html_entity_decode(strip_tags($rif_ordine_cli), ENT_QUOTES,$default_charset);

		$data_ordine_cliente = $adb->query_result($res_sales_order, 0, 'kp_data_ord_cli');
		$data_ordine_cliente = html_entity_decode(strip_tags($data_ordine_cliente), ENT_QUOTES, $default_charset);
		if( $data_ordine_cliente == null || $data_ordine_cliente == '0000-00-00' ){
			$data_ordine_cliente = "";
		}

		$codice_cup = $adb->query_result($res_sales_order, 0, 'kp_codice_cup');
		$codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES, $default_charset);
		if( $codice_cup == null ){
			$codice_cup = "";
		}

		$codice_cig = $adb->query_result($res_sales_order, 0, 'kp_codice_cig');
		$codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES, $default_charset);
		if( $codice_cig == null ){
			$codice_cig = "";
		}
		
		$assegnatario = $adb->query_result($res_sales_order, 0, 'assegnatario');
		$assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);
		
		$businessunit = $adb->query_result($res_sales_order, 0, 'businessunit');
		$businessunit = html_entity_decode(strip_tags($businessunit), ENT_QUOTES,$default_charset);
		
		$commessa = $adb->query_result($res_sales_order, 0, 'commessa');
		$commessa = html_entity_decode(strip_tags($commessa), ENT_QUOTES,$default_charset);
		
		$accountname = $adb->query_result($res_sales_order, 0, 'accountname');
		$accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
		
		$subject_riga = $adb->query_result($res_sales_order, 0, 'subject_riga');
		$subject_riga = html_entity_decode(strip_tags($subject_riga), ENT_QUOTES,$default_charset);
		
		$prodotto = $adb->query_result($res_sales_order, 0, 'kp_prodotto');
		$prodotto = html_entity_decode(strip_tags($prodotto), ENT_QUOTES,$default_charset);
		
		$valore_riga = $adb->query_result($res_sales_order, 0, 'kp_valore_riga');
		$valore_riga = html_entity_decode(strip_tags($valore_riga), ENT_QUOTES,$default_charset);
		
		$salesorder = $adb->query_result($res_sales_order, 0, 'kp_salesorder');
		$salesorder = html_entity_decode(strip_tags($salesorder), ENT_QUOTES,$default_charset);

		$descrizione_riga = $adb->query_result($res_sales_order, 0, 'descrizione_riga');
		$descrizione_riga = html_entity_decode(strip_tags($descrizione_riga), ENT_QUOTES,$default_charset);
		
		if($rif_ordine_cli != ""){
            if($descrizione_riga != ""){
                $descrizione_riga = "Rif. ".$rif_ordine_cli."
".$descrizione_riga;
            }
            else{
                $descrizione_riga = "Rif. ".$rif_ordine_cli;
            }
		}
		
		$quantita_ordine = $adb->query_result($res_sales_order, 0, 'kp_quantita_ordine');
		$quantita_ordine = html_entity_decode(strip_tags($quantita_ordine), ENT_QUOTES,$default_charset);
		if($quantita_ordine == null || $quantita_ordine == ""){
			$quantita_ordine = 0;
		}
		
		$per_sconto = $adb->query_result($res_sales_order, 0, 'kp_per_sconto');
		$per_sconto = html_entity_decode(strip_tags($per_sconto), ENT_QUOTES,$default_charset);
		if($per_sconto == null || $per_sconto == ""){
			$per_sconto = 0;
		}
		
		$amm_sconto_originale = $adb->query_result($res_sales_order, 0, 'kp_amm_sconto');
		$amm_sconto_originale = html_entity_decode(strip_tags($amm_sconto_originale), ENT_QUOTES,$default_charset);
		if($amm_sconto_originale == null || $amm_sconto_originale == ""){
			$amm_sconto_originale = 0;
		}
		
		$listprice = $adb->query_result($res_sales_order, 0, 'kp_listprice');
		$listprice = html_entity_decode(strip_tags($listprice), ENT_QUOTES,$default_charset);
		if($listprice == null || $listprice == ""){
			$listprice = 0;
		}

		$agente = $adb->query_result($res_sales_order, 0, 'kp_agente');
		$agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
		if($agente == null || $agente == ""){
			$agente = 0;
		}

		$val_no_sconto = $adb->query_result($res_sales_order, 0, 'kp_val_no_sconto');
		$val_no_sconto = html_entity_decode(strip_tags($val_no_sconto), ENT_QUOTES,$default_charset);
		if($val_no_sconto == null || $val_no_sconto == ""){
			$val_no_sconto = 0;
		}

		$conto_corrente = $adb->query_result($res_sales_order, 0, 'kp_conto_corrente');
		$conto_corrente = html_entity_decode(strip_tags($conto_corrente), ENT_QUOTES,$default_charset);
		if($conto_corrente == null || $conto_corrente == ""){
			$conto_corrente = 0;
		}

		$mod_pagamento = $adb->query_result($res_sales_order, 0, 'mod_pagamento');
		$mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
		if($mod_pagamento == null || $mod_pagamento == ""){
			$mod_pagamento = 0;
		}

		$contatto = $adb->query_result($res_sales_order, 0, 'contactid');
		$contatto = html_entity_decode(strip_tags($contatto), ENT_QUOTES,$default_charset);
		if($contatto == null || $contatto == ""){
			$contatto = 0;
		}

		$banca_cliente = $adb->query_result($res_sales_order, 0, 'kp_banca_cliente');
		$banca_cliente = html_entity_decode(strip_tags($banca_cliente), ENT_QUOTES,$default_charset);
		if($banca_cliente == null || $banca_cliente == ""){
			$banca_cliente = "";
		}
		/* kpro@bid250920181215 */
		$commento_riga = $adb->query_result($res_sales_order, 0, 'commento_riga');
		$commento_riga = html_entity_decode(strip_tags($commento_riga), ENT_QUOTES,$default_charset);
		if($commento_riga == null){
			$commento_riga = '';
		}
		/* kpro@bid250920181215 end */
		
		//$per_sconto = 100 - ( ($valore * 100) / $valore_riga );

		$prezzo_totale = ($valore * $val_no_sconto) / $valore_riga;
		
		$prezzo_unitario = $prezzo_totale / $quantita_ordine;

		/* kpro@tom101220181102 */
		$id_tassa = $adb->query_result($res_sales_order, 0, 'kp_id_tassa');
		$id_tassa = html_entity_decode(strip_tags($id_tassa), ENT_QUOTES,$default_charset);
		if($id_tassa == null || $id_tassa == ""){
			$id_tassa = "";
		}

		$nome_tassa = $adb->query_result($res_sales_order, 0, 'kp_nome_tassa');
		$nome_tassa = html_entity_decode(strip_tags($nome_tassa), ENT_QUOTES,$default_charset);
		if($nome_tassa == null || $nome_tassa == ""){
			$nome_tassa = "";
		}
		/* kpro@tom101220181102 end */
		
	}
	
	$q_servizio = "SELECT service_usageunit FROM {$table_prefix}_service
					INNER JOIN {$table_prefix}_crmentity ON crmid = serviceid
					WHERE deleted = 0 AND serviceid =".$prodotto;
			
	$ress_servizio = $adb->query($q_servizio); 							
	
	if($adb->num_rows($ress_servizio)>0){			
					 
		$service_usageunit = $adb->query_result($ress_servizio,0,'service_usageunit'); 
		$service_usageunit = html_entity_decode(strip_tags($service_usageunit), ENT_QUOTES,$default_charset); 
		
	}
	else{
		$service_usageunit = "";
	}
    
    $odf = CRMEntity::getInstance('OdF');
	$odf->column_fields['tipo_odf'] = 'Ordini di Vendita';
	$odf->column_fields['commessa'] = $commessa;
	$odf->column_fields['cliente_fatt'] = $accountid;
	$odf->column_fields['related_to'] = $salesorderlineid;
	$odf->column_fields['data_related_to'] = $data_ordine;
	$odf->column_fields['rif_related_to'] = $salesorder_no;
	$odf->column_fields['prezzo_unitario'] = $prezzo_unitario;
	$odf->column_fields['qta_eseguita'] = $quantita_ordine;
	$odf->column_fields['qta_fatturata'] = $quantita_ordine;
	$odf->column_fields['prezzo_totale'] = $prezzo_totale;
	$odf->column_fields['total_notaxes'] = $prezzo_totale;
	$odf->column_fields['servizio'] = $prodotto;
	$odf->column_fields['kp_business_unit'] = $businessunit;
	$odf->column_fields['data_odf'] = $data_corrente;
	$odf->column_fields['stato_odf'] = 'Creato';
	$odf->column_fields['service_usageunit'] = $service_usageunit;
	$odf->column_fields['discount_percent'] = $per_sconto;
	$odf->column_fields['discount_amount'] = $amm_sconto;
	$odf->column_fields['assigned_user_id'] = $assegnatario;
	$odf->column_fields['description'] = utf8_encode($descrizione_riga);
	if($agente != 0){
		$odf->column_fields['kp_agente'] = $agente;
	}
	if($conto_corrente != 0){
		$odf->column_fields['kp_conto_corrente'] = $conto_corrente;
	}
	if($mod_pagamento != 0){
		$odf->column_fields['kp_mod_pagamento'] = $mod_pagamento;
	}
	if($contatto != 0){
		$odf->column_fields['kp_contatto'] = $contatto;
	}
	if($ordine != 0){
		$odf->column_fields['kp_salesorder'] = $ordine;
	}
	$odf->column_fields['kp_banca_cliente'] = $banca_cliente;
	$odf->column_fields['comment_line'] = $commento_riga; /* kpro@bid250920181215 */

	/* kpro@tom031221081534 */
	$odf->column_fields['kp_rif_ordine_cli'] = $rif_ordine_cli; 
	if( $data_ordine_cliente != "" ){
		$odf->column_fields['kp_data_ord_cli'] = $data_ordine_cliente;
	}
	$odf->column_fields['kp_codice_cup'] = $codice_cup;
	$odf->column_fields['kp_codice_cig'] = $codice_cig;
	/* kpro@tom031221081534 end */

	/* kpro@tom101220181102 */
	$odf->column_fields['kp_id_tassa'] = $id_tassa;
	$odf->column_fields['kp_nome_tassa'] = $nome_tassa;
	/* kpro@tom101220181102 end */

	$odf->save('OdF', $longdesc=true, $offline_update=false, $triggerEvent=false); 
	$odfid = $odf->id;
	
	KpSalesOrderLineKp::aggiornaDatiRigaOrdineDiVendita($salesorderlineid);
	
	KpSalesOrderLineKp::aggiornaStatoSaldoOrdineDiVendita($salesorder);
        
    $rows[] = array('odfid' => $odfid);
        		
}

$json = json_encode($rows);
print $json;
	
?>