<?php

/* kpro@tom010416 */

function recuperaNumeroFattura($fattura){
    global $adb, $table_prefix,$current_user;
    
    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     * @package fatturazioneConOdf
     * @version 1.0
     * 
     * Questo script recupera il numero di fattura
     */
    
    $q_fattura = "SELECT inv.invoice_number,
                inv.kp_business_unit,
                inv.kp_tipo_documento,
                acc.kp_fat_elettronica
                FROM {$table_prefix}_invoice inv
                INNER JOIN {$table_prefix}_account acc ON acc.accountid = inv.accountid
                WHERE inv.invoiceid = ".$fattura;
    $res_fattura = $adb->query($q_fattura);
    if($adb->num_rows($res_fattura)>0){
        $invoice_number = $adb->query_result($res_fattura, 0, 'invoice_number'); 
        $invoice_number = html_entity_decode(strip_tags($invoice_number), ENT_QUOTES,$default_charset);

        $tipo_documento = $adb->query_result($res_fattura, 0, 'kp_tipo_documento'); 
        $tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES,$default_charset);
        if($tipo_documento == null){
            $tipo_documento = '';
        }
        
        $business_unit = $adb->query_result($res_fattura, 0, 'kp_business_unit'); 
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);
        if($business_unit == '' || $business_unit == null){
            $business_unit = 0;
        }

        $fattura_elettronica = $adb->query_result($res_fattura, 0, 'kp_fat_elettronica'); 
        $fattura_elettronica = html_entity_decode(strip_tags($fattura_elettronica), ENT_QUOTES,$default_charset);
        if($fattura_elettronica == '1' || $fattura_elettronica == 1){
            $fattura_elettronica = '1';
        }
        else{
            $fattura_elettronica = '0';
        }
        
        if($invoice_number == "" || $invoice_number == null){

            if($tipo_documento == 'Fattura' || $tipo_documento == 'Fattura di acconto'){ /* kpro@bid250920181215 */
                $id_modulo = '23';
            }
            else{
                $id_modulo = '23N';
            }
            
            $q_numeratore = "SELECT num.use_prefix, 
                            num.start_sequence, 
                            num.modulenumberingid
                            FROM {$table_prefix}_crmentityrel entrel
                            INNER JOIN {$table_prefix}_modulenumbering num ON num.modulenumberingid = entrel.crmid
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
                            WHERE ent.deleted = 0 AND num.select_module = '{$id_modulo}'
                            AND entrel.relcrmid = {$business_unit}
                            AND num.kp_fat_elettronica = '{$fattura_elettronica}'
                            UNION
                            SELECT num.use_prefix, 
                            num.start_sequence, 
                            num.modulenumberingid
                            FROM {$table_prefix}_crmentityrel entrel
                            INNER JOIN {$table_prefix}_modulenumbering num ON num.modulenumberingid = entrel.relcrmid
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
                            WHERE ent.deleted = 0 AND num.select_module = '{$id_modulo}'
                            AND entrel.crmid = {$business_unit}
                            AND num.kp_fat_elettronica = '{$fattura_elettronica}'";

            $res_numeratore = $adb->query($q_numeratore);
            if($adb->num_rows($res_numeratore)>0){
                $use_prefix = $adb->query_result($res_numeratore, 0, 'use_prefix'); 
                $use_prefix = html_entity_decode(strip_tags($use_prefix), ENT_QUOTES,$default_charset);

                $start_sequence = $adb->query_result($res_numeratore, 0, 'start_sequence'); 
                $start_sequence = html_entity_decode(strip_tags($start_sequence), ENT_QUOTES,$default_charset);
                
                $modulenumberingid = $adb->query_result($res_numeratore, 0, 'modulenumberingid'); 
                $modulenumberingid = html_entity_decode(strip_tags($modulenumberingid), ENT_QUOTES,$default_charset);
                
                $invoice_number = $use_prefix.$start_sequence;
							
                $upd_invoice = "UPDATE {$table_prefix}_invoice SET 
                                invoice_number = '".$invoice_number."',
                                kp_fat_elettronica = '".$fattura_elettronica."'
                                WHERE invoiceid =".$fattura;
                $adb->query($upd_invoice);
				
                $length_sequence = strlen($start_sequence);			
                $start_sequence = (int)$start_sequence;

                $start_sequence++;
                $start_sequence = str_pad($start_sequence, $length_sequence, "0", STR_PAD_LEFT);
                
                $upd_numeratore = "UPDATE {$table_prefix}_modulenumbering
                                    SET start_sequence ='".$start_sequence."'
                                    WHERE modulenumberingid =".$modulenumberingid;
                $adb->query($upd_numeratore);
                                
            }
            
        }
        
    }
 
}
    
function generaScadenzeFattura($fattura){
    global $adb, $table_prefix,$current_user;
    
    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     * @package fatturazioneConOdf
     * @version 1.0
     * 
     * Questo script genera le scadenze della fattura
     */

    require_once('modules/SproCore/Scadenziario/Scadenziario_utils.php'); /* kpro@bid180420181220 */
    
    $q_dati_fattura = "SELECT 
                        inv.mod_pagamento mod_pagamento,
                        inv.invoicedate invoicedate,
                        inv.kp_tot_da_pagare total,
                        inv.accountid accountid,
                        inv.banca_pagamento banca_pagamento_pag,
                        inv.invoice_number invoice_number,
                        inv.commessa commessa,
                        inv.kp_business_unit kp_business_unit,
                        inv.invoicestatus invoicestatus,
                        inv.kp_banca_cliente kp_banca_cliente,
                        modp.nome_mod_pag nome_mod_pag,
                        modp.per_pag_1 per_pag_1,
                        modp.per_pag_2 per_pag_2,
                        modp.per_pag_3 per_pag_3,
                        modp.per_pag_4 per_pag_4,
                        modp.per_pag_5 per_pag_5,
                        modp.scad_pag_1 scad_pag_1,
                        modp.scad_pag_2 scad_pag_2,
                        modp.scad_pag_3 scad_pag_3,
                        modp.scad_pag_4 scad_pag_4,
                        modp.scad_pag_5 scad_pag_5,
                        modp.fine_mese fine_mese,
                        modp.condizioni_pagamento condizioni_pagamento,
                        modp.kp_mesi_ritardo kp_mesi_ritardo,
                        modp.kp_giorni_ritardo kp_giorni_ritardo,
                        modp.kp_pagato kp_pagato,
                        ent.smownerid assegnatario
                        FROM {$table_prefix}_invoice inv
                        INNER JOIN {$table_prefix}_modpagamento modp ON modp.modpagamentoid = inv.mod_pagamento
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = inv.invoiceid
                        WHERE inv.invoiceid = ".$fattura; //kpro@tom240120191400
    $res_dati_fattura = $adb->query($q_dati_fattura);
    
    if($adb->num_rows($res_dati_fattura)>0){
         
        $numero_scadenze = 0;
        
        $mod_pagamento = $adb->query_result($res_dati_fattura, 0, 'mod_pagamento'); 
        $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
        
        $invoicedate = $adb->query_result($res_dati_fattura, 0, 'invoicedate'); 
        $invoicedate = html_entity_decode(strip_tags($invoicedate), ENT_QUOTES,$default_charset);
        
        $accountid = $adb->query_result($res_dati_fattura, 0, 'accountid'); 
        $accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
        
        $banca_pagamento_pag = $adb->query_result($res_dati_fattura, 0, 'banca_pagamento_pag'); 
        $banca_pagamento_pag = html_entity_decode(strip_tags($banca_pagamento_pag), ENT_QUOTES,$default_charset);
        
        $invoice_number = $adb->query_result($res_dati_fattura, 0, 'invoice_number'); 
        $invoice_number = html_entity_decode(strip_tags($invoice_number), ENT_QUOTES,$default_charset);
        if($invoice_number == null){
            $invoice_number = '';
        }
        
        $total = $adb->query_result($res_dati_fattura, 0, 'total'); 
        $total = html_entity_decode(strip_tags($total), ENT_QUOTES,$default_charset);
        
        $commessa = $adb->query_result($res_dati_fattura, 0, 'commessa'); 
        $commessa = html_entity_decode(strip_tags($commessa), ENT_QUOTES,$default_charset);
        if($commessa == null || $commessa == ""){
            $commessa = 0;
        }
        
        $business_unit = $adb->query_result($res_dati_fattura, 0, 'kp_business_unit'); 
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);
        if($business_unit == '' || $business_unit == null){
            $business_unit = 0;
        }

        $banca_cliente = $adb->query_result($res_dati_fattura, 0, 'kp_banca_cliente'); 
        $banca_cliente = html_entity_decode(strip_tags($banca_cliente), ENT_QUOTES,$default_charset);
        
        $invoicestatus = $adb->query_result($res_dati_fattura, 0, 'invoicestatus'); 
        $invoicestatus = html_entity_decode(strip_tags($invoicestatus), ENT_QUOTES,$default_charset);
        
        $nome_mod_pag = $adb->query_result($res_dati_fattura, 0, 'nome_mod_pag'); 
        $nome_mod_pag = html_entity_decode(strip_tags($nome_mod_pag), ENT_QUOTES,$default_charset);
        
        $per_pag_1 = $adb->query_result($res_dati_fattura, 0, 'per_pag_1'); 
        $per_pag_1 = html_entity_decode(strip_tags($per_pag_1), ENT_QUOTES,$default_charset);
        $array_per_pag[1] = $per_pag_1;
        if($per_pag_1 != "" && $per_pag_1 != null && $per_pag_1 != 0){
            $numero_scadenze = 1;
        }
        
        $per_pag_2 = $adb->query_result($res_dati_fattura, 0, 'per_pag_2'); 
        $per_pag_2 = html_entity_decode(strip_tags($per_pag_2), ENT_QUOTES,$default_charset);
        $array_per_pag[2] = $per_pag_2;
        if($per_pag_2 != "" && $per_pag_2 != null && $per_pag_2 != 0){
            $numero_scadenze = 2;
        }
        
        $per_pag_3 = $adb->query_result($res_dati_fattura, 0, 'per_pag_3'); 
        $per_pag_3 = html_entity_decode(strip_tags($per_pag_3), ENT_QUOTES,$default_charset);
        $array_per_pag[3] = $per_pag_3;
        if($per_pag_3 != "" && $per_pag_3 != null && $per_pag_3 != 0){
            $numero_scadenze = 3;
        }
        
        $per_pag_4 = $adb->query_result($res_dati_fattura, 0, 'per_pag_4'); 
        $per_pag_4 = html_entity_decode(strip_tags($per_pag_4), ENT_QUOTES,$default_charset);
        $array_per_pag[4] = $per_pag_4;
        if($per_pag_4 != "" && $per_pag_4 != null && $per_pag_4 != 0){
            $numero_scadenze = 4;
        }
        
        $per_pag_5 = $adb->query_result($res_dati_fattura, 0, 'per_pag_5'); 
        $per_pag_5 = html_entity_decode(strip_tags($per_pag_5), ENT_QUOTES,$default_charset);
        $array_per_pag[5] = $per_pag_5;
        if($per_pag_5 != "" && $per_pag_5 != null && $per_pag_5 != 0){
            $numero_scadenze = 5;
        }
        
        $scad_pag_1 = $adb->query_result($res_dati_fattura, 0, 'scad_pag_1'); 
        $scad_pag_1 = html_entity_decode(strip_tags($scad_pag_1), ENT_QUOTES,$default_charset);
        $array_scad_pag[1] = $scad_pag_1;
        
        $scad_pag_2 = $adb->query_result($res_dati_fattura, 0, 'scad_pag_2'); 
        $scad_pag_2 = html_entity_decode(strip_tags($scad_pag_2), ENT_QUOTES,$default_charset);
        $array_scad_pag[2] = $scad_pag_2;
        
        $scad_pag_3 = $adb->query_result($res_dati_fattura, 0, 'scad_pag_3'); 
        $scad_pag_3 = html_entity_decode(strip_tags($scad_pag_3), ENT_QUOTES,$default_charset);
        $array_scad_pag[3] = $scad_pag_3;
        
        $scad_pag_4 = $adb->query_result($res_dati_fattura, 0, 'scad_pag_4'); 
        $scad_pag_4 = html_entity_decode(strip_tags($scad_pag_4), ENT_QUOTES,$default_charset);
        $array_scad_pag[4] = $scad_pag_4;
        
        $scad_pag_5 = $adb->query_result($res_dati_fattura, 0, 'scad_pag_5'); 
        $scad_pag_5 = html_entity_decode(strip_tags($scad_pag_5), ENT_QUOTES,$default_charset);
        $array_scad_pag[5] = $scad_pag_5;
        
        $fine_mese = $adb->query_result($res_dati_fattura, 0, 'fine_mese'); 
        $fine_mese = html_entity_decode(strip_tags($fine_mese), ENT_QUOTES,$default_charset);
        if($fine_mese == "Data fattura"){
            $fine_mese = false;
        }
        else{
            $fine_mese = true;
        }

        $condizioni_pagamento = $adb->query_result($res_dati_fattura, 0, 'condizioni_pagamento'); 
        $condizioni_pagamento = html_entity_decode(strip_tags($condizioni_pagamento), ENT_QUOTES,$default_charset);

        $mesi_ritardo = $adb->query_result($res_dati_fattura, 0, 'kp_mesi_ritardo'); 
        $mesi_ritardo = html_entity_decode(strip_tags($mesi_ritardo), ENT_QUOTES,$default_charset);
        if($mesi_ritardo == null){
            $mesi_ritardo = '';
        }

        $giorni_ritardo = $adb->query_result($res_dati_fattura, 0, 'kp_giorni_ritardo'); 
        $giorni_ritardo = html_entity_decode(strip_tags($giorni_ritardo), ENT_QUOTES,$default_charset);
        if($giorni_ritardo == '' || $giorni_ritardo == null){
            $giorni_ritardo = 0;
        }

        $pagato = $adb->query_result($res_dati_fattura, 0, 'kp_pagato'); 
        $pagato = html_entity_decode(strip_tags($pagato), ENT_QUOTES,$default_charset);
        if($pagato == '' || $pagato == null){
            $pagato = '0';
        }
        
        $assegnatario = $adb->query_result($res_dati_fattura, 0, 'assegnatario'); 
        $assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);
        
        $upd_scadenze = "UPDATE {$table_prefix}_scadenziario
                            SET aggiornato ='0'
                            WHERE invoice =".$fattura;
        $adb->query($upd_scadenze);
        
        for($i=1; $i<=$numero_scadenze; $i++){
            
            $nro_scadenza = $i;
            $importo_scadenza = ($total * $array_per_pag[$i])/100;
            $data_scadenza = calcolaDataScadenza($invoicedate,$array_scad_pag[$i],$fine_mese, $mesi_ritardo, $giorni_ritardo);
            
            $q_scadenza = "SELECT scad.scadenziarioid scadenziarioid,
                            scad.stato_scadenza_pag stato_scadenza_pag
                            FROM {$table_prefix}_scadenziario scad
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = scad.scadenziarioid
                            WHERE ent.deleted = 0 AND scad.invoice = ".$fattura." AND scad.nro_scadenza = ".$nro_scadenza."
                            ORDER BY scad.scadenziarioid ASC";
            $res_scadenza = $adb->query($q_scadenza);
            if($adb->num_rows($res_scadenza)==0){
                
                $scadenziario = CRMEntity::getInstance('Scadenziario');
                $scadenziario->column_fields['assigned_user_id'] = $assegnatario;
                $scadenziario->column_fields['invoice'] = $fattura;
                $scadenziario->column_fields['azienda'] = $accountid;
                $scadenziario->column_fields['data_scadenza'] = $data_scadenza;
                $scadenziario->column_fields['tipo_scadenza_pag'] = 'Pagamento cliente';
                $scadenziario->column_fields['import'] = $importo_scadenza;
                if($invoicestatus == 'Paid' || $invoicestatus == 'Pagata Proforma'){
                    $scadenziario->column_fields['stato_scadenza_pag'] = 'Pagata';
                    $scadenziario->column_fields['data_pagamento'] = date('Y-m-d');
                }
                else{
                    if($pagato == '1' || $pagato == 1){
                        $scadenziario->column_fields['stato_scadenza_pag'] = 'Pagata';
                        $scadenziario->column_fields['data_pagamento'] = date('Y-m-d');
                    }
                    else{
                        $scadenziario->column_fields['stato_scadenza_pag'] = 'Aperta';
                    }
                }
                $scadenziario->column_fields['nro_scadenza'] = $i;
                $scadenziario->column_fields['totale_scadenze'] = $numero_scadenze;
                $scadenziario->column_fields['banca_pagamento'] = $banca_pagamento_pag;
                $scadenziario->column_fields['mod_pagamento'] = $mod_pagamento;
                $scadenziario->column_fields['condizioni_pagamento'] = $condizioni_pagamento;
                if($commessa != 0){
                    $scadenziario->column_fields['commessa'] = $commessa;
                }
                $scadenziario->column_fields['kp_business_unit'] = $business_unit;
                $scadenziario->column_fields['kp_banca_cliente'] = $banca_cliente;
                $scadenziario->column_fields['aggiornato'] = '1';
                $scadenziario->column_fields['kp_data_scadenza_or'] = $data_scadenza;
                $scadenziario->column_fields['kp_data_fattura'] = $invoicedate;
                $scadenziario->column_fields['kp_numero_fattura'] = $invoice_number;
                $scadenziario->save('Scadenziario', $longdesc=true, $offline_update=false, $triggerEvent=false); 
                
            }
            else{
                
                $scadenziarioid = $adb->query_result($res_scadenza, 0, 'scadenziarioid');
                $scadenziarioid = html_entity_decode(strip_tags($scadenziarioid), ENT_QUOTES,$default_charset);
                
                $stato_scadenza_pag = $adb->query_result($res_scadenza, 0, 'stato_scadenza_pag');
                $stato_scadenza_pag = html_entity_decode(strip_tags($stato_scadenza_pag), ENT_QUOTES,$default_charset);
                
                if($stato_scadenza_pag != "Pagata"){
                    $upd_scadenza = "UPDATE {$table_prefix}_scadenziario SET
                                        data_scadenza = '".$data_scadenza."',
                                        import = ".$importo_scadenza.",
                                        totale_scadenze = ".$numero_scadenze.",
                                        banca_pagamento = '".$banca_pagamento_pag."',
                                        mod_pagamento = ".$mod_pagamento.",
                                        commessa = ".$commessa.",
                                        kp_business_unit = ".$business_unit.",
                                        kp_banca_cliente = '".$banca_cliente."',
                                        condizioni_pagamento = '".$condizioni_pagamento."',
                                        aggiornato = '1',
                                        kp_data_scadenza_or = '".$data_scadenza."',
                                        kp_data_fattura = '".$invoicedate."',
                                        kp_numero_fattura = '".$invoice_number."'
                                        WHERE scadenziarioid = ".$scadenziarioid;
                    $adb->query($upd_scadenza);
                }
                else{
                    $upd_scadenza = "UPDATE {$table_prefix}_scadenziario SET
                                        aggiornato = '1'
                                        WHERE scadenziarioid = ".$scadenziarioid;
                    $adb->query($upd_scadenza);
                }
                
            }
            
        }
        
        $upd_scadenze_vecchie = "UPDATE {$table_prefix}_crmentity ent
                                    INNER JOIN {$table_prefix}_scadenziario scad ON scad.scadenziarioid = ent.crmid
                                    SET ent.deleted = 1
                                    WHERE ent.deleted = 0 AND scad.aggiornato ='0' AND scad.invoice =".$fattura;
        $adb->query($upd_scadenze_vecchie);
                                
    }
 
}

function ControlloDataFattura($record_id, $data_fattura, $tipo_documento, $business_unit){
    global $adb, $table_prefix, $default_charset;

    $messaggio = "";

    if($data_fattura != '' && $data_fattura != null && $data_fattura != '0000-00-00'){
        
        $data_fattura_dt = new DateTime($data_fattura);
        $data_fattura = $data_fattura_dt->format("Y-m-d");

        $fattura_elettronica = CheckFatturaElettronica($record_id);

        $q = "SELECT MAX(inv.invoicedate) AS data_ultima_fattura
            FROM {$table_prefix}_invoice inv
            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = inv.invoiceid
            WHERE ent.deleted = 0 AND inv.kp_tipo_documento = '{$tipo_documento}'
            AND inv.invoice_number <> '' AND inv.invoice_number IS NOT NULL
            AND inv.kp_business_unit = ".$business_unit;
        if($fattura_elettronica == '1' || $fattura_elettronica == 1){
            $q .= " AND inv.kp_fat_elettronica = '1'";
        }
        else{
            $q .= " AND (inv.kp_fat_elettronica <> '1' OR inv.kp_fat_elettronica IS NULL)";
        }
        $res = $adb->query($q);
        if($adb->num_rows($res) > 0){
            $data_ultima_fattura = $adb->query_result($res, 0, 'data_ultima_fattura'); 
            $data_ultima_fattura = html_entity_decode(strip_tags($data_ultima_fattura), ENT_QUOTES,$default_charset);
            if($data_ultima_fattura != '' && $data_ultima_fattura != null && $data_ultima_fattura != '0000-00-00'){
                $data_ultima_fattura_dt = new DateTime($data_ultima_fattura);
                $data_ultima_fattura_inv = $data_ultima_fattura_dt->format("d-m-Y");
            }
            else{
                $data_ultima_fattura = '';
                $data_ultima_fattura_inv = '';
            }
        }
        else{
            $data_ultima_fattura = '';
            $data_ultima_fattura_inv = '';
        }

        if($data_ultima_fattura != ''){
            if($data_ultima_fattura > $data_fattura){
                $messaggio = "Errore nell'imputazione del campo Data Fattura: ultima ".$tipo_documento." emessa in data ".$data_ultima_fattura_inv;
            }
        }
    }
    else{
        $messaggio = "Errore nell'imputazione del campo Data Fattura";
    }

    return $messaggio;
}

function CheckFatturaElettronica($fattura){
    global $adb, $table_prefix, $default_charset;

    $fattura_elettronica = '0';

    $q_fattura = "SELECT acc.kp_fat_elettronica
                FROM {$table_prefix}_invoice inv
                INNER JOIN {$table_prefix}_account acc ON acc.accountid = inv.accountid
                WHERE inv.invoiceid = ".$fattura;
    $res_fattura = $adb->query($q_fattura);
    if($adb->num_rows($res_fattura)>0){
        $fattura_elettronica = $adb->query_result($res_fattura, 0, 'kp_fat_elettronica'); 
        $fattura_elettronica = html_entity_decode(strip_tags($fattura_elettronica), ENT_QUOTES,$default_charset);
        if($fattura_elettronica == '1' || $fattura_elettronica == 1){
            $fattura_elettronica = '1';
        }
        else{
            $fattura_elettronica = '0';
        }
    }

    return $fattura_elettronica;
}

function ControlloPresenzaRighe($fattura){
    global $adb, $table_prefix, $default_charset;

    $q = "SELECT * 
        FROM {$table_prefix}_inventoryproductrel
        WHERE id = ".$fattura;
    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){

        return true;

    }
    else{

        return false;

    }
}

function ControlloTasse($fattura){
    global $adb, $table_prefix, $default_charset;

    $res = false;

    $q_tassazione = "SELECT taxtype,
                    kp_tasse
                    FROM {$table_prefix}_invoice
                    WHERE invoiceid = ".$fattura;
    $res_tassazione = $adb->query($q_tassazione);
    if($adb->num_rows($res_tassazione) > 0){

        $taxtype = $adb->query_result($res_tassazione, 0, 'taxtype'); 
        $taxtype = html_entity_decode(strip_tags($taxtype), ENT_QUOTES,$default_charset);
        if($taxtype == null){
            $taxtype = '';
        }

        $tasse = $adb->query_result($res_tassazione, 0, 'kp_tasse'); 
        $tasse = html_entity_decode(strip_tags($tasse), ENT_QUOTES,$default_charset);
        if($tasse == null){
            $tasse = '';
        }

        $totale_tasse = 0;
        if($taxtype == 'group'){

            $q = "SELECT tax1
                FROM {$table_prefix}_inventorytotals
                WHERE id = ".$fattura;
            $res = $adb->query($q);
            if($adb->num_rows($res) > 0){
                $tax1 = $adb->query_result($res, 0, 'tax1'); 
                $tax1 = html_entity_decode(strip_tags($tax1), ENT_QUOTES,$default_charset);
                if($tax1 == '' || $tax1 == null){
                    $tax1 = 0;
                }

                $totale_tasse += $tax1;
            }

        }
        elseif($taxtype == 'individual'){

            $q = "SELECT SUM(COALESCE(tax1,0)) as tot_tax1
                FROM {$table_prefix}_inventoryproductrel
                WHERE id = ".$fattura;
            $res = $adb->query($q);
            if($adb->num_rows($res) > 0){
                $tot_tax1 = $adb->query_result($res, 0, 'tot_tax1'); 
                $tot_tax1 = html_entity_decode(strip_tags($tot_tax1), ENT_QUOTES,$default_charset);
                if($tot_tax1 == '' || $tot_tax1 == null){
                    $tot_tax1 = 0;
                }

                $totale_tasse += $tot_tax1;
            }

        }

        if(strpos($tasse, 'E') !== false && $totale_tasse != 0){
            
        }
        else{
            $res = true;
        }

    }

    return $res;
}
        
/* kpro@bid130920181600 */
function aggiornaDataFatturaOdF($fattura, $data_fattura){
    global $adb, $table_prefix, $default_charset;

    $data_fattura_dt = new DateTime($data_fattura);
    $data_fattura_inv = $data_fattura_dt->format('Y-m-d');

    $q = "SELECT odf.odfid
        FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        WHERE ent.deleted = 0 AND odf.fattura = {$fattura}
        AND (odf.kp_data_fattura IS NULL OR odf.kp_data_fattura <> ''
        OR odf.kp_data_fattura <> '0000-00-00' OR odf.kp_data_fattura <> '{$data_fattura_inv}')";
    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $odfid = $adb->query_result($res, $i, 'odfid');
        $odfid = html_entity_decode(strip_tags($odfid), ENT_QUOTES, $default_charset);

        $update = "UPDATE {$table_prefix}_odf
                SET kp_data_fattura = '{$data_fattura_inv}'
                WHERE odfid = ".$odfid;
        $adb->query($update);
    }
    
}
/* kpro@bid130920181600 end */

/* kpro@bid250920181215 */
function generaOdFdaFattura($fattura){
    global $adb, $table_prefix, $default_charset;

    require_once('modules/SproCore/KpTabelleProvvigional/ClassKpTabelleProvvigionalKp.php');

    $upd_odf = "UPDATE {$table_prefix}_odf
            SET kp_aggiornato = '0'
            WHERE fattura = ".$fattura;
    $adb->query($upd_odf);
    
    $dati_fattura = getDatiFattura($fattura);
    if(!empty($dati_fattura)){

        if($dati_fattura['tipo_documento'] == 'Fattura di acconto' && $dati_fattura['ordine_di_vendita'] != 0){
                        
            $agente = getAgenteDaOrdineDiVendita($dati_fattura['ordine_di_vendita']);

        }
        else{
            $agente = 0;
        }

        $dati_righe_fattura = getDatiRigheFattura($fattura);
        if(!empty($dati_righe_fattura)){

            foreach($dati_righe_fattura as $riga_fattura){

                $q_check = "SELECT odf.odfid 
                        FROM {$table_prefix}_odf odf
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
                        WHERE ent.deleted = 0 AND odf.fattura = ".$fattura." 
                        AND odf.comment_line = '".$riga_fattura['commento']."' 
                        AND odf.servizio = ".$riga_fattura['servizio']; 
                $res_check = $adb->query($q_check);
                if($adb->num_rows($res_check) == 0){

                    if($agente != 0 && $riga_fattura['totale_senza_tasse'] >= 0){

                        $fornitore = KpTabelleProvvigionalKp::getFornitoreAgente($agente);
    
                        $tabella_provvigiona = KpTabelleProvvigionalKp::getTabellaProvvigionale($fornitore, $riga_fattura['servizio']);
                        
                        if( $tabella_provvigiona["perc_provvigione"] > 0){
                        
                            $ammontare_provvigione = ( $riga_fattura['totale_senza_tasse'] * $tabella_provvigiona["perc_provvigione"] ) / 100;
                
                        }
                        else{
                
                            $ammontare_provvigione = 0;
                
                        }

                    }

                    $odf = CRMEntity::getInstance('OdF');
                    $odf->column_fields['tipo_odf'] = $dati_fattura['tipo_documento'];
                    $odf->column_fields['cliente_fatt'] = $dati_fattura['cliente'];
                    $odf->column_fields['data_related_to'] = $dati_fattura['data_fattura'];
                    $odf->column_fields['kp_data_fattura'] = $dati_fattura['data_fattura'];
                    $odf->column_fields['rif_related_to'] = $dati_fattura['numero_fattura'];
                    $odf->column_fields['data_odf'] = date('Y-m-d');
                    $odf->column_fields['stato_odf'] = 'Fatturato';
                    $odf->column_fields['fattura'] = $fattura;
                    $odf->column_fields['assigned_user_id'] = $dati_fattura['assegnatario'];
                    if($dati_fattura['commessa'] != 0){
                        $odf->column_fields['commessa'] = $dati_fattura['commessa'];
                    }
                    if($dati_fattura['business_unit'] != 0){
                        $odf->column_fields['kp_business_unit'] = $dati_fattura['business_unit'];
                    }
                    if($dati_fattura['conto_corrente_cliente'] != 0){
                        $odf->column_fields['kp_conto_corrente'] = $dati_fattura['conto_corrente_cliente'];
                    }
                    if($dati_fattura['mod_pagamento'] != 0){
                        $odf->column_fields['kp_mod_pagamento'] = $dati_fattura['mod_pagamento'];
                    }
                    if($dati_fattura['contatto'] != 0){
                        $odf->column_fields['kp_contatto'] = $dati_fattura['contatto'];
                    }
                    $odf->column_fields['kp_banca_cliente'] = $dati_fattura['banca_cliente'];

                    $odf->column_fields['prezzo_unitario'] = $riga_fattura['prezzo_unitario'];
                    $odf->column_fields['qta_eseguita'] = $riga_fattura['quantita'];
                    $odf->column_fields['qta_fatturata'] = $riga_fattura['quantita'];
                    $odf->column_fields['prezzo_totale'] = $riga_fattura['totale_senza_tasse'];
                    $odf->column_fields['total_notaxes'] = $riga_fattura['totale_senza_tasse'];
                    $odf->column_fields['servizio'] = $riga_fattura['servizio'];
                    $odf->column_fields['service_usageunit'] = $riga_fattura['unita_di_misura'];
                    $odf->column_fields['discount_percent'] = $riga_fattura['sconto_percentuale'];
                    $odf->column_fields['discount_amount'] = $riga_fattura['sconto_diretto'];
                    $odf->column_fields['comment_line'] = $riga_fattura['commento'];
                    $odf->column_fields['description'] = utf8_encode($riga_fattura['descrizione']);
                    $odf->column_fields['kp_aggiornato'] = '1';

                    if($agente != 0 && $riga_fattura['totale_senza_tasse'] >= 0){ 
    
                        $odf->column_fields['kp_agente'] = $agente;
                
                        if($tabella_provvigiona["id"] != 0){
                
                            $odf->column_fields['kp_tabella_provvigi'] = $tabella_provvigiona["id"];
                
                        }
                
                        $odf->column_fields['kp_importo_provvigi'] = $ammontare_provvigione;
                
                    }
                    
                    $odf->save('OdF', $longdesc=true, $offline_update=false, $triggerEvent=false); 
                    $odfid = $odf->id;
                }
                else{
                    $odfid = $adb->query_result($res_check, 0, 'odfid'); 
                    $odfid = html_entity_decode(strip_tags($odfid), ENT_QUOTES,$default_charset);
                    
                    $upd_odf = "UPDATE {$table_prefix}_odf
                            SET kp_aggiornato = '1'
                            WHERE odfid = ".$odfid;
                    $adb->query($upd_odf);
                }
            }
        }
    }

    $delete_odf = "UPDATE {$table_prefix}_crmentity ent
                INNER JOIN {$table_prefix}_odf odf ON odf.odfid = ent.crmid
                SET ent.deleted = 1
                WHERE odf.kp_aggiornato != '1' AND odf.fattura = ".$fattura;
    $adb->query($delete_odf);
}

function getDatiFattura($fattura){
    global $adb, $table_prefix, $default_charset;

    $result = array();

    $q = "SELECT ent.smownerid assegnatario,
        inv.kp_tipo_documento tipo_documento,
        inv.accountid cliente,
        inv.invoice_number numero_fattura,
        inv.invoicedate data_fattura,
        inv.commessa commessa,
        inv.kp_business_unit business_unit,
        inv.contactid contatto,
        inv.kp_conto_corrente conto_corrente_cliente,
        inv.kp_banca_cliente banca_cliente,
        inv.mod_pagamento mod_pagamento,
        inv.salesorderid ordine_di_vendita
        FROM {$table_prefix}_invoice inv
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = inv.invoiceid
        WHERE inv.invoiceid = ".$fattura;
    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $assegnatario = $adb->query_result($res, 0, 'assegnatario'); 
        $assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);
        if($assegnatario == 0 || $assegnatario == '' || $assegnatario == null){
            $assegnatario = 1;
        }

        $tipo_documento = $adb->query_result($res, 0, 'tipo_documento'); 
        $tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES,$default_charset);
        if($tipo_documento == null){
            $tipo_documento = '';
        }

        $cliente = $adb->query_result($res, 0, 'cliente'); 
        $cliente = html_entity_decode(strip_tags($cliente), ENT_QUOTES,$default_charset);
        if($cliente == '' || $cliente == null){
            $cliente = 0;
        }

        $numero_fattura = $adb->query_result($res, 0, 'numero_fattura'); 
        $numero_fattura = html_entity_decode(strip_tags($numero_fattura), ENT_QUOTES,$default_charset);
        if($numero_fattura == null){
            $numero_fattura = '';
        }

        $data_fattura = $adb->query_result($res, 0, 'data_fattura'); 
        $data_fattura = html_entity_decode(strip_tags($data_fattura), ENT_QUOTES,$default_charset);
        if($data_fattura == null || $data_fattura == '0000-00-00'){
            $data_fattura = '';
        }

        $commessa = $adb->query_result($res, 0, 'commessa'); 
        $commessa = html_entity_decode(strip_tags($commessa), ENT_QUOTES,$default_charset);
        if($commessa == null || $commessa == ''){
            $commessa = 0;
        }
        
        $business_unit = $adb->query_result($res, 0, 'business_unit'); 
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);
        if($business_unit == '' || $business_unit == null){
            $business_unit = 0;
        }

        $contatto = $adb->query_result($res, 0, 'contatto'); 
        $contatto = html_entity_decode(strip_tags($contatto), ENT_QUOTES,$default_charset);
        if($contatto == '' || $contatto == null){
            $contatto = 0;
        }

        $conto_corrente_cliente = $adb->query_result($res, 0, 'conto_corrente_cliente'); 
        $conto_corrente_cliente = html_entity_decode(strip_tags($conto_corrente_cliente), ENT_QUOTES,$default_charset);
        if($conto_corrente_cliente == '' || $conto_corrente_cliente == null){
            $conto_corrente_cliente = 0;
        }

        $banca_cliente = $adb->query_result($res, 0, 'banca_cliente'); 
        $banca_cliente = html_entity_decode(strip_tags($banca_cliente), ENT_QUOTES,$default_charset);
        if($banca_cliente == null){
            $banca_cliente = '';
        }

        $mod_pagamento = $adb->query_result($res, 0, 'mod_pagamento'); 
        $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
        if($mod_pagamento == '' || $mod_pagamento == null){
            $mod_pagamento = 0;
        }

        $ordine_di_vendita = $adb->query_result($res, 0, 'ordine_di_vendita'); 
        $ordine_di_vendita = html_entity_decode(strip_tags($ordine_di_vendita), ENT_QUOTES,$default_charset);
        if($ordine_di_vendita == '' || $ordine_di_vendita == null){
            $ordine_di_vendita = 0;
        }
        
        $result = array(
            'id' => $fattura,
            'assegnatario' => $assegnatario,
            'tipo_documento' => $tipo_documento,
            'cliente' => $cliente,
            'numero_fattura' => $numero_fattura,
            'data_fattura' => $data_fattura,
            'business_unit' => $business_unit,
            'contatto' => $contatto,
            'conto_corrente_cliente' => $conto_corrente_cliente,
            'banca_cliente' => $banca_cliente,
            'mod_pagamento' => $mod_pagamento,
            'commessa' => $commessa,
            'ordine_di_vendita' => $ordine_di_vendita
        );

    }

    return $result;
}

function getDatiRigheFattura($fattura){
    global $adb, $table_prefix, $default_charset;

    $result = array();

    $q = "SELECT ser.serviceid AS servizio,
        ser.service_usageunit AS unita_di_misura,
        righe.lineitem_id AS id,
        righe.quantity AS quantita, 
        righe.listprice AS prezzo_unitario, 
        righe.total_notaxes AS totale_senza_tasse, 
        righe.linetotal AS totale,
        righe.discount_amount AS sconto_diretto,
        righe.discount_percent AS sconto_percentuale,
        righe.comment AS commento,
        righe.description AS descrizione
        FROM {$table_prefix}_inventoryproductrel righe
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = righe.productid
        WHERE righe.id =".$fattura;
    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){      
        $servizio = $adb->query_result($res, $i, 'servizio'); 
        $servizio = html_entity_decode(strip_tags($servizio), ENT_QUOTES,$default_charset);
        if($servizio == '' || $servizio == null){
            $servizio = 0;
        }

        $unita_di_misura = $adb->query_result($res, $i, 'unita_di_misura'); 
        $unita_di_misura = html_entity_decode(strip_tags($unita_di_misura), ENT_QUOTES,$default_charset);
        if($unita_di_misura == null){
            $unita_di_misura = '';
        }

        $id = $adb->query_result($res, $i, 'id'); 
        $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
        if($id == null || $id == ''){
            $id = 0;
        }

        $quantita = $adb->query_result($res, $i, 'quantita'); 
        $quantita = html_entity_decode(strip_tags($quantita), ENT_QUOTES,$default_charset);
        if($quantita == null || $quantita == ''){
            $quantita = 0;
        }
        
        $prezzo_unitario = $adb->query_result($res, $i, 'prezzo_unitario'); 
        $prezzo_unitario = html_entity_decode(strip_tags($prezzo_unitario), ENT_QUOTES,$default_charset);
        if($prezzo_unitario == '' || $prezzo_unitario == null){
            $prezzo_unitario = 0;
        }

        $totale_senza_tasse = $adb->query_result($res, $i, 'totale_senza_tasse'); 
        $totale_senza_tasse = html_entity_decode(strip_tags($totale_senza_tasse), ENT_QUOTES,$default_charset);
        if($totale_senza_tasse == '' || $totale_senza_tasse == null){
            $totale_senza_tasse = 0;
        }

        $totale = $adb->query_result($res, $i, 'totale'); 
        $totale = html_entity_decode(strip_tags($totale), ENT_QUOTES,$default_charset);
        if($totale == '' || $totale == null){
            $totale = 0;
        }

        $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto'); 
        $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
        if($sconto_diretto == '' || $sconto_diretto == null){
            $sconto_diretto = 0;
        }

        $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale'); 
        $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
        if($sconto_percentuale == '' || $sconto_percentuale == null){
            $sconto_percentuale = 0;
        }

        $commento = $adb->query_result($res, $i, 'commento'); 
        $commento = html_entity_decode(strip_tags($commento), ENT_QUOTES,$default_charset);
        if($commento == null){
            $commento = '';
        }

        $descrizione = $adb->query_result($res, $i, 'descrizione'); 
        $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES,$default_charset);
        if($descrizione == null){
            $descrizione = '';
        }
        
        $result[] = array(
            'id' => $id,
            'servizio' => $servizio,
            'unita_di_misura' => $unita_di_misura,
            'quantita' => $quantita,
            'prezzo_unitario' => $prezzo_unitario,
            'totale_senza_tasse' => $totale_senza_tasse,
            'totale' => $totale,
            'sconto_diretto' => $sconto_diretto,
            'sconto_percentuale' => $sconto_percentuale,
            'commento' => $commento,
            'descrizione' => $descrizione
        );

    }

    return $result;
}

function getAgenteDaOrdineDiVendita($ordine_di_vendita){
    global $adb, $table_prefix, $default_charset;

    $agente = 0;

    $q_ordine = "SELECT so.kp_agente kp_agente
            FROM {$table_prefix}_salesorder so
            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
            WHERE so.salesorderid = ".$ordine_di_vendita;       

    $res_ordine = $adb->query($q_ordine);
    if($adb->num_rows($res_ordine) > 0){

        $agente = $adb->query_result($res_ordine,0,'kp_agente');
        $agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
        if($agente == null || $agente == ''){
            $agente = 0;
        }
        
    }

    return $agente;
}
/* kpro@bid250920181215 end */
?>