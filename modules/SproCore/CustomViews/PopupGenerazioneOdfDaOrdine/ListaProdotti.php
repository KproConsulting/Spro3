<?php

/* kpro@tom04082016 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2016, Kpro Consulting Srl
 * @version 1.0
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
if(isset($_GET['record'])){
    $record = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['record']), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset);
    $record = substr($record,0,100);
    
    $q_lista_prodotti = "SELECT
                            sol.kpsalesorderlineid id
                            FROM {$table_prefix}_kpsalesorderline sol
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sol.kpsalesorderlineid
                            WHERE ent.deleted = 0 AND sol.kp_salesorder = ".$record."
                            ORDER BY sol.kp_numero_riga ASC";

    $res_lista_prodotti = $adb->query($q_lista_prodotti);
    $num_lista_prodotti = $adb->num_rows($res_lista_prodotti);

    for($i=0; $i<$num_lista_prodotti; $i++){
        $id = $adb->query_result($res_lista_prodotti, $i, 'id');
        $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
        
        KpSalesOrderLineKp::aggiornaDatiRigaOrdineDiVendita($id);
        
    }
    
    KpSalesOrderLineKp::aggiornaStatoSaldoOrdineDiVendita($record);
    
    $q_lista_prodotti = "SELECT
                            sol.kpsalesorderlineid salesorderlineid,
                            sol.kp_prodotto kp_prodotto,
                            sol.kp_valore_riga kp_valore_riga,
                            sol.kp_valore_fatturato kp_valore_fatturato,
                            sol.kp_valore_da_fat kp_valore_da_fat,
                            sol.kp_listprice kp_listprice,
                            sol.kp_val_no_sconto kp_val_no_sconto,
                            sol.kp_per_sconto kp_per_sconto,
                            sol.kp_amm_sconto kp_amm_sconto,
                            sol.description
                            FROM {$table_prefix}_kpsalesorderline sol
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = sol.kpsalesorderlineid
                            INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = sol.kp_prodotto
                            WHERE ent.deleted = 0 AND sol.kp_salesorder = ".$record;

    $q_lista_prodotti .= " AND ent2.setype = 'Services'";
    $q_lista_prodotti .= " ORDER BY sol.kp_numero_riga ASC";

    $res_lista_prodotti = $adb->query($q_lista_prodotti);
    $num_lista_prodotti = $adb->num_rows($res_lista_prodotti);

    for($i=0; $i<$num_lista_prodotti; $i++){
        $salesorderlineid = $adb->query_result($res_lista_prodotti, $i, 'salesorderlineid');
        $salesorderlineid = html_entity_decode(strip_tags($salesorderlineid), ENT_QUOTES,$default_charset);

        $prodotto = $adb->query_result($res_lista_prodotti, $i, 'kp_prodotto');
        $prodotto = html_entity_decode(strip_tags($prodotto), ENT_QUOTES,$default_charset);
        
        $valore_riga = $adb->query_result($res_lista_prodotti, $i, 'kp_valore_riga');
        $valore_riga = html_entity_decode(strip_tags($valore_riga), ENT_QUOTES,$default_charset);
        if($valore_riga == '' || $valore_riga == null){
            $valore_riga = 0;
        }
        //$valore_riga = number_format($valore_riga, 2, '.', ',');

        $listprice = $adb->query_result($res_lista_prodotti, $i, 'kp_listprice');
        $listprice = html_entity_decode(strip_tags($listprice), ENT_QUOTES,$default_charset);
        if($listprice == '' || $listprice == null){
            $listprice = 0;
        }

        $val_no_sconto = $adb->query_result($res_lista_prodotti, $i, 'kp_val_no_sconto');
        $val_no_sconto = html_entity_decode(strip_tags($val_no_sconto), ENT_QUOTES,$default_charset);
        if($val_no_sconto == '' || $val_no_sconto == null){
            $val_no_sconto = 0;
        }

        $per_sconto = $adb->query_result($res_lista_prodotti, $i, 'kp_per_sconto');
        $per_sconto = html_entity_decode(strip_tags($per_sconto), ENT_QUOTES,$default_charset);
        if($per_sconto == '' || $per_sconto == null){
            $per_sconto = 0;
        }

        $amm_sconto_originale = $adb->query_result($res_lista_prodotti, $i, 'kp_amm_sconto');
        $amm_sconto_originale = html_entity_decode(strip_tags($amm_sconto_originale), ENT_QUOTES,$default_charset);
        if($amm_sconto_originale == '' || $amm_sconto_originale == null){
            $amm_sconto_originale = 0;
        }
		
		$valore_fatturato = $adb->query_result($res_lista_prodotti, $i, 'kp_valore_fatturato');
        $valore_fatturato = html_entity_decode(strip_tags($valore_fatturato), ENT_QUOTES,$default_charset);
        if($valore_fatturato == '' || $valore_fatturato == null){
            $valore_fatturato = 0;
        }
        //$valore_fatturato = number_format($valore_fatturato, 2, '.', ',');
        
        $valore_da_fat = $adb->query_result($res_lista_prodotti, $i, 'kp_valore_da_fat');
        $valore_da_fat = html_entity_decode(strip_tags($valore_da_fat), ENT_QUOTES,$default_charset);
        if($valore_da_fat == '' || $valore_da_fat == null){
            $valore_da_fat = 0;
        }
        if($valore_da_fat < 0){
			$valore_da_fat = 0;
		}
        //$valore_da_fat = number_format($valore_da_fat, 2, '.', ',');
        
        $description = $adb->query_result($res_lista_prodotti, $i, 'description');
        $description = html_entity_decode(strip_tags($description), ENT_QUOTES,$default_charset);
        if($description == null){
            $description = '';
        }

        $productname = "";
        $q_type = "SELECT setype
					from {$table_prefix}_crmentity
					where crmid = ".$prodotto;
		$res_type = $adb->query($q_type);
		if($adb->num_rows($res_type) > 0){
			
			$setype = $adb->query_result($res_type, 0, 'setype');
			$setype = html_entity_decode(strip_tags($setype), ENT_QUOTES,$default_charset);
			
			if($setype == 'Products'){
				
				$q_name = "SELECT 
							prod.productname name
							from {$table_prefix}_products prod
							where prod.productid = ".$prodotto;
				
			}
			elseif($setype == 'Services'){
				
				$q_name = "SELECT 
							serv.servicename name
							from {$table_prefix}_service serv
							where serv.serviceid = ".$prodotto;
				
			}
			
			$res_name = $adb->query($q_name);
			if($adb->num_rows($res_name) > 0){
				
				$productname = $adb->query_result($res_name, 0, 'name');
				$productname = html_entity_decode(strip_tags($productname), ENT_QUOTES,$default_charset);
				
			}
			
        }
        
        $amm_sconto = 0;
        if($amm_sconto_originale > 0){
            if($valore_fatturato > 0){
                $q_totale_sconto_diretto = "SELECT SUM(COALESCE(odf.discount_amount, 0)) AS totale_sconto_diretto
                                        FROM {$table_prefix}_odf odf
                                        INNER JOIN {$table_prefix}_kpsalesorderline line ON line.kpsalesorderlineid = odf.related_to
                                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
                                        WHERE ent.deleted = 0 AND line.kpsalesorderlineid = ".$salesorderlineid;
                $res_totale_sconto_diretto = $adb->query($q_totale_sconto_diretto);
                if($adb->num_rows($res_totale_sconto_diretto) > 0){
                    $totale_sconto_diretto = $adb->query_result($res_totale_sconto_diretto, 0, 'totale_sconto_diretto');
                    if($amm_sconto_originale > $totale_sconto_diretto){
                        $amm_sconto = $amm_sconto_originale - $totale_sconto_diretto;
                    }
                }
            }
            else{
                $amm_sconto = $amm_sconto_originale;
            }
        }

        $rows[] = array('salesorderlineid' => $salesorderlineid,
                        'prodotto' => $prodotto,
                        'productname' => $productname,
                        'valore_riga' => $valore_riga,
                        'valore_fatturato' => $valore_fatturato,
                        'listprice' => $listprice,
                        'val_no_sconto' => $val_no_sconto,
                        'per_sconto' => $per_sconto,
                        'amm_sconto_originale' => $amm_sconto_originale,
                        'amm_sconto' => $amm_sconto,
                        'valore_da_fat' => $valore_da_fat,
                        'descrizione' => $description);

    }

}

$json = json_encode($rows);
print $json;
    
?>