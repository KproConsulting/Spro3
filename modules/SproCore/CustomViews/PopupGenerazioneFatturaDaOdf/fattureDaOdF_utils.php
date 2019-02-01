<?php

function GeneraInvoiceDaOdF($odfid, $data_fattura, $mod_pagamento=0){
    global $adb, $table_prefix, $current_user, $default_charset;
    //Recupero i dati dell'OdF

    require_once('modules/SproCore/SproUtils/spro_utils.php');
    include(__DIR__.'/config.php'); /* kpro@bid050920181625 */

    $debug = false;

    $risultato = array(
        'fatture_create' => 0,
        'righe_create' => 0
    );

    $data_corrente = date("d/m/Y");
	
    $q_odf = "SELECT
                o.tipo_odf tipo_odf,
                o.cliente_fatt cliente_fatt,
                o.related_to related_to,
                o.data_related_to data_related_to,
                o.rif_related_to rif_related_to,
                o.prezzo_unitario prezzo_unitario,
                o.qta_eseguita qta_eseguita,
                o.qta_fatturata qta_fatturata,
                o.prezzo_totale prezzo_totale,
                o.servizio servizio,
                o.data_odf data_odf,
                o.service_usageunit service_usageunit,
                o.kp_business_unit kp_business_unit,
                o.kp_agente kp_agente,
                o.so_line_id so_line_id,
                o.discount_percent discount_percent,
                o.discount_amount discount_amount,
                o.total_notaxes total_notaxes,
                o.comment_line comment_line,
                o.commessa commessa,
                o.kp_mod_pagamento kp_mod_pagamento,
                o.kp_contatto kp_contatto,
                o.kp_conto_corrente kp_conto_corrente,
                o.kp_banca_cliente kp_banca_cliente,
                o.description description,
                o.kp_id_tassa kp_id_tassa,
                o.kp_nome_tassa kp_nome_tassa,
                o.kp_rif_ordine_cli kp_rif_ordine_cli,
                o.kp_data_ord_cli kp_data_ord_cli,
                o.kp_codice_cup kp_codice_cup,
                o.kp_codice_cig kp_codice_cig
                FROM {$table_prefix}_odf o
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = o.odfid
                WHERE ent.deleted = 0 AND o.odfid=".$odfid;

    $res_odf = $adb->query($q_odf);
    if($adb->num_rows($res_odf)>0){
        $tipo_odf = $adb->query_result($res_odf,0,'tipo_odf');
        $tipo_odf = html_entity_decode(strip_tags($tipo_odf), ENT_QUOTES,$default_charset);

        $cliente_fatt = $adb->query_result($res_odf,0,'cliente_fatt');
        $cliente_fatt = html_entity_decode(strip_tags($cliente_fatt), ENT_QUOTES,$default_charset);
        $cliente_fatt = ControlloAziendaPadre($cliente_fatt);

        $related_to = $adb->query_result($res_odf,0,'related_to');
        $related_to = html_entity_decode(strip_tags($related_to), ENT_QUOTES,$default_charset);

        $data_related_to = $adb->query_result($res_odf,0,'data_related_to');
        $data_related_to = html_entity_decode(strip_tags($data_related_to), ENT_QUOTES,$default_charset);

        $rif_related_to = $adb->query_result($res_odf,0,'rif_related_to');
        $rif_related_to = html_entity_decode(strip_tags($rif_related_to), ENT_QUOTES,$default_charset);

        $prezzo_unitario = $adb->query_result($res_odf,0,'prezzo_unitario');
        $prezzo_unitario = html_entity_decode(strip_tags($prezzo_unitario), ENT_QUOTES,$default_charset);

        $qta_eseguita = $adb->query_result($res_odf,0,'qta_eseguita');
        $qta_eseguita = html_entity_decode(strip_tags($qta_eseguita), ENT_QUOTES,$default_charset);

        $qta_fatturata = $adb->query_result($res_odf,0,'qta_fatturata');
        $qta_fatturata = html_entity_decode(strip_tags($qta_fatturata), ENT_QUOTES,$default_charset);

        $prezzo_totale = $adb->query_result($res_odf,0,'prezzo_totale');
        $prezzo_totale = html_entity_decode(strip_tags($prezzo_totale), ENT_QUOTES,$default_charset);

        $servizio = $adb->query_result($res_odf,0,'servizio');
        $servizio = html_entity_decode(strip_tags($servizio), ENT_QUOTES,$default_charset);

        $data_odf = $adb->query_result($res_odf,0,'data_odf');
        $data_odf = html_entity_decode(strip_tags($data_odf), ENT_QUOTES,$default_charset);

        $service_usageunit = $adb->query_result($res_odf,0,'service_usageunit');
        $service_usageunit = html_entity_decode(strip_tags($service_usageunit), ENT_QUOTES,$default_charset);

        $businessunit = $adb->query_result($res_odf,0,'kp_business_unit');
        $businessunit = html_entity_decode(strip_tags($businessunit), ENT_QUOTES,$default_charset);
        if($businessunit == '' || $businessunit == null){
            $businessunit = 0;
        }

        $agente = $adb->query_result($res_odf,0,'kp_agente');
        $agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
        if($agente == '' || $agente == null){
            $agente = 0;
        }

        if($mod_pagamento == 0){
            $mod_pagamento = $adb->query_result($res_odf,0,'kp_mod_pagamento');
            $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
            if($mod_pagamento == '' || $mod_pagamento == null){
                $mod_pagamento = 0;
            }
        }

        $contatto = $adb->query_result($res_odf,0,'kp_contatto');
        $contatto = html_entity_decode(strip_tags($contatto), ENT_QUOTES,$default_charset);
        if($contatto == '' || $contatto == null){
            $contatto = 0;
        }

        $conto_corrente = $adb->query_result($res_odf,0,'kp_conto_corrente');
        $conto_corrente = html_entity_decode(strip_tags($conto_corrente), ENT_QUOTES,$default_charset);
        if($conto_corrente == '' || $conto_corrente == null){
            $conto_corrente = 0;
        }

        $banca_cliente = $adb->query_result($res_odf,0,'kp_banca_cliente');
        $banca_cliente = html_entity_decode(strip_tags($banca_cliente), ENT_QUOTES,$default_charset);

        $so_line_id = $adb->query_result($res_odf,0,'so_line_id');
        $so_line_id = html_entity_decode(strip_tags($so_line_id), ENT_QUOTES,$default_charset);

        $discount_percent = $adb->query_result($res_odf,0,'discount_percent');
        $discount_percent = html_entity_decode(strip_tags($discount_percent), ENT_QUOTES,$default_charset);
        if($discount_percent == '' || $discount_percent == null){
            $discount_percent = 0;
        }

        $discount_amount = $adb->query_result($res_odf,0,'discount_amount');
        $discount_amount = html_entity_decode(strip_tags($discount_amount), ENT_QUOTES,$default_charset);
        if($discount_amount == '' || $discount_amount == null){
            $discount_amount = 0;
        }

        $total_notaxes = $adb->query_result($res_odf,0,'total_notaxes');
        $total_notaxes = html_entity_decode(strip_tags($total_notaxes), ENT_QUOTES,$default_charset);

        $comment_line = $adb->query_result($res_odf,0,'comment_line');
        $comment_line = html_entity_decode(strip_tags($comment_line), ENT_QUOTES,$default_charset);
        if($comment_line == null){
            $comment_line = '';
        }

        $commessa = $adb->query_result($res_odf,0,'commessa');
        $commessa = html_entity_decode(strip_tags($commessa), ENT_QUOTES,$default_charset);

        $description = $adb->query_result($res_odf,0,'description');
        $description = html_entity_decode(strip_tags($description), ENT_QUOTES,$default_charset);

        $id_tassa = $adb->query_result($res_odf,0,'kp_id_tassa');
        $id_tassa = html_entity_decode(strip_tags($id_tassa), ENT_QUOTES,$default_charset);
        if($id_tassa == null){
            $id_tassa = '';
        }

        $nome_tassa = $adb->query_result($res_odf,0,'kp_nome_tassa');
        $nome_tassa = html_entity_decode(strip_tags($nome_tassa), ENT_QUOTES,$default_charset);
        if($nome_tassa == null){
            $nome_tassa = '';
        }

        $rif_ordine_cli = $adb->query_result($res_odf,0,'kp_rif_ordine_cli');
        $rif_ordine_cli = html_entity_decode(strip_tags($rif_ordine_cli), ENT_QUOTES,$default_charset);
        if($rif_ordine_cli == null){
            $rif_ordine_cli = '';
        }

        $data_ord_cli = $adb->query_result($res_odf,0,'kp_data_ord_cli');
        $data_ord_cli = html_entity_decode(strip_tags($data_ord_cli), ENT_QUOTES,$default_charset);
        if($data_ord_cli == null){
            $data_ord_cli = '';
        }

        $codice_cup = $adb->query_result($res_odf,0,'kp_codice_cup');
        $codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES,$default_charset);
        if($codice_cup == null){
            $codice_cup = '';
        }

        $codice_cig = $adb->query_result($res_odf,0,'kp_codice_cig');
        $codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES,$default_charset);
        if($codice_cig == null){
            $codice_cig = '';
        }

        $q_cliente = "SELECT acc.kp_business_unit kp_business_unit,
                acc.kp_agente_rel kp_agente_rel,
                acc.mod_pagamento mod_pagamento,
                acc.banca_pagamento banca_pagamento,
                acc.kp_split_payment kp_split_payment,
                acc.kp_ritenuta_acconto kp_ritenuta_acconto,
                acc.kp_spese_riba kp_spese_riba,
                acc.kp_tasse kp_tasse,
                acc.kp_applica_ritenuta applica_ritenuta,
                acc.kp_tipo_ritenuta tipo_ritenuta,
                acc.kp_causale_pag_rite causale_pag_rite,
                acc.kp_aliquota_ritenuta aliquota_ritenuta,
                billad.bill_city bill_city,
                billad.bill_code bill_code,
                billad.bill_country bill_country,
                billad.bill_state bill_state,
                billad.bill_street bill_street,
                shipad.ship_city ship_city,
                shipad.ship_code ship_code,
                shipad.ship_country ship_country,
                shipad.ship_state ship_state,
                shipad.ship_street ship_street
                FROM {$table_prefix}_account acc
                INNER JOIN {$table_prefix}_accountbillads billad ON billad.accountaddressid = acc.accountid
                INNER JOIN {$table_prefix}_accountshipads shipad ON shipad.accountaddressid = acc.accountid
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
                WHERE ent.deleted = 0 AND acc.accountid = ".$cliente_fatt; //kpro@tom240120191400

        //print_r($q_cliente);die;        

        $res_cliente = $adb->query($q_cliente);

        if($adb->num_rows($res_cliente)>0){
            //se la business unit dell'odf è vuota, prendo quella del cliente
            if($businessunit == 0){
                $businessunit= $adb->query_result($res_cliente,0,'kp_business_unit');
                $businessunit = html_entity_decode(strip_tags($businessunit), ENT_QUOTES,$default_charset);
                if($businessunit == null || $businessunit == ""){
                    $businessunit = 0;
                }
            }
            //se l'agente dell'odf è vuoto, prendo quello del cliente
            if($agente == 0){
                $agente= $adb->query_result($res_cliente,0,'kp_agente_rel');
                $agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
                if($agente == null || $agente == ""){
                    $agente = 0;
                }
            }
            //se la modalita di pagamento dell'odf è vuota, prendo quella del cliente
            if($mod_pagamento == 0){
                $mod_pagamento= $adb->query_result($res_cliente,0,'mod_pagamento');
                $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
                if($mod_pagamento == null || $mod_pagamento == ""){
                    $mod_pagamento = 0;
                }
            }

            $split_payment= $adb->query_result($res_cliente,0,'kp_split_payment');
            $split_payment = html_entity_decode(strip_tags($split_payment), ENT_QUOTES,$default_charset);
            if($split_payment == null || $split_payment == ""){
                $split_payment = '0';
            }

            $ritenuta_acconto= $adb->query_result($res_cliente,0,'kp_ritenuta_acconto');
            $ritenuta_acconto = html_entity_decode(strip_tags($ritenuta_acconto), ENT_QUOTES,$default_charset);
            if($ritenuta_acconto == null || $ritenuta_acconto == ""){
                $ritenuta_acconto = '0';
            }

            $spese_riba = $adb->query_result($res_cliente,0,'kp_spese_riba');
            $spese_riba = html_entity_decode(strip_tags($spese_riba), ENT_QUOTES,$default_charset);
            if($spese_riba == null || $spese_riba == ""){
                $spese_riba = '0';
            }

            $banca_pagamento = $adb->query_result($res_cliente,0,'banca_pagamento');
            $banca_pagamento = html_entity_decode(strip_tags($banca_pagamento), ENT_QUOTES,$default_charset);

            $kp_tasse = $adb->query_result($res_cliente,0,'kp_tasse');
            $kp_tasse = html_entity_decode(strip_tags($kp_tasse), ENT_QUOTES,$default_charset);

            $bill_city = $adb->query_result($res_cliente,0,'bill_city');
            $bill_city = html_entity_decode(strip_tags($bill_city), ENT_QUOTES,$default_charset);

            $bill_code = $adb->query_result($res_cliente,0,'bill_code');
            $bill_code = html_entity_decode(strip_tags($bill_code), ENT_QUOTES,$default_charset);

            $bill_country = $adb->query_result($res_cliente,0,'bill_country');
            $bill_country = html_entity_decode(strip_tags($bill_country), ENT_QUOTES,$default_charset);

            $bill_state = $adb->query_result($res_cliente,0,'bill_state');
            $bill_state = html_entity_decode(strip_tags($bill_state), ENT_QUOTES,$default_charset);

            $bill_street = $adb->query_result($res_cliente,0,'bill_street');
            $bill_street = html_entity_decode(strip_tags($bill_street), ENT_QUOTES,$default_charset);

            $ship_city = $adb->query_result($res_cliente,0,'ship_city');
            $ship_city = html_entity_decode(strip_tags($ship_city), ENT_QUOTES,$default_charset);

            $ship_code = $adb->query_result($res_cliente,0,'ship_code');
            $ship_code = html_entity_decode(strip_tags($ship_code), ENT_QUOTES,$default_charset);

            $ship_country = $adb->query_result($res_cliente,0,'ship_country');
            $ship_country = html_entity_decode(strip_tags($ship_country), ENT_QUOTES,$default_charset);

            $ship_state = $adb->query_result($res_cliente,0,'ship_state');
            $ship_state = html_entity_decode(strip_tags($ship_state), ENT_QUOTES,$default_charset);

            $ship_street = $adb->query_result($res_cliente,0,'ship_street');
            $ship_street = html_entity_decode(strip_tags($ship_street), ENT_QUOTES,$default_charset);

            /* kpro@tom240120191400 */
            $applica_ritenuta = $adb->query_result($res_cliente, 0, 'applica_ritenuta');
            $applica_ritenuta = html_entity_decode(strip_tags($applica_ritenuta), ENT_QUOTES, $default_charset);
            if($applica_ritenuta == "1" || $applica_ritenuta == 1 ){
                $applica_ritenuta = true;
            }
            else{
                $applica_ritenuta = false;
            }

            $tipo_ritenuta = $adb->query_result($res_cliente, 0, 'tipo_ritenuta');
            $tipo_ritenuta = html_entity_decode(strip_tags($tipo_ritenuta), ENT_QUOTES, $default_charset);
            if($tipo_ritenuta == null || $tipo_ritenuta == ""){
                $tipo_ritenuta = '';
            }

            $causale_pag_rite = $adb->query_result($res_cliente, 0, 'causale_pag_rite');
            $causale_pag_rite = html_entity_decode(strip_tags($causale_pag_rite), ENT_QUOTES, $default_charset);
            if($causale_pag_rite == null || $causale_pag_rite == ""){
                $causale_pag_rite = '';
            }

            $aliquota_ritenuta = $adb->query_result($res_cliente, 0, 'aliquota_ritenuta');
            $aliquota_ritenuta = html_entity_decode(strip_tags($aliquota_ritenuta), ENT_QUOTES, $default_charset);
            if($aliquota_ritenuta == null || $aliquota_ritenuta == ""){
                $aliquota_ritenuta = 0;
            }
            /* kpro@tom240120191400 end */

        }
        else{
            $split_payment = '0';
            $ritenuta_acconto = '0';
            $spese_riba = '0';
            $banca_pagamento = "";
            $bill_city = "";
            $bill_code = "";
            $bill_country = "";
            $bill_state = "";
            $bill_street = "";
            $ship_city = "";
            $ship_code = "";
            $ship_country = "";
            $ship_state = "";
            $ship_street = "";
            $applica_ritenuta = "0"; //kpro@tom240120191400
            $tipo_ritenuta = ""; //kpro@tom240120191400
            $causale_pag_rite = ""; //kpro@tom240120191400
            $aliquota_ritenuta = ""; //kpro@tom240120191400
        }
        //se il contatto dell'odf è vuoto, prendo il contatto dell'azienda con Riferimento Fatturazione = si
        if($contatto == 0){
            $q_contatto = "SELECT cont.contactid FROM {$table_prefix}_contactdetails cont
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cont.contactid
                    WHERE ent.deleted = 0 AND cont.kp_rif_fatturazione = '1' 
                    AND cont.accountid = ".$cliente_fatt; 
            $res_contatto = $adb->query($q_contatto);
            if($adb->num_rows($res_contatto)>0){
                $contatto = $adb->query_result($res_contatto,0,'contactid');
                $contatto = html_entity_decode(strip_tags($contatto), ENT_QUOTES,$default_charset);
            }
        }
        //se la business unit è compilata, prendo i relativi dati
        if($businessunit != 0){
            $q_dati_bu = "SELECT bu.kp_mod_tassazione,
                        bu.kp_conf_tassazione,
                        bu.kp_avviso_fattura,
                        bu.kp_ritenuta_acconto,
                        cur.id
                        FROM {$table_prefix}_kpbusinessunit bu
                        LEFT JOIN {$table_prefix}_currency_info cur ON cur.currency_code = bu.kp_valuta
                            AND cur.currency_status = 'Active' AND cur.deleted = 0
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = bu.kpbusinessunitid
                        WHERE ent.deleted = 0 AND bu.kpbusinessunitid = ".$businessunit;
            $res_dati_bu = $adb->query($q_dati_bu);
            if($adb->num_rows($res_dati_bu) > 0){
                $mod_tassazione = $adb->query_result($res_dati_bu,0,'kp_mod_tassazione');
                $mod_tassazione = html_entity_decode(strip_tags($mod_tassazione), ENT_QUOTES,$default_charset);
                if($mod_tassazione == '' || $mod_tassazione == null){
                    $mod_tassazione = 'individual';
                }

                $conf_tassazione = $adb->query_result($res_dati_bu,0,'kp_conf_tassazione');
                $conf_tassazione = html_entity_decode(strip_tags($conf_tassazione), ENT_QUOTES,$default_charset);
                if($conf_tassazione == '' || $conf_tassazione == null){
                    $conf_tassazione = '0';
                }

                $avviso_fattura = $adb->query_result($res_dati_bu,0,'kp_avviso_fattura');
                $avviso_fattura = html_entity_decode(strip_tags($avviso_fattura), ENT_QUOTES,$default_charset);
                if($avviso_fattura == '' || $avviso_fattura == null){
                    $avviso_fattura = '0';
                }

                $ritenuta_acconto_bu= $adb->query_result($res_dati_bu,0,'kp_ritenuta_acconto');
                $ritenuta_acconto_bu = html_entity_decode(strip_tags($ritenuta_acconto_bu), ENT_QUOTES,$default_charset);
                if($ritenuta_acconto_bu == null || $ritenuta_acconto_bu == ""){
                    $ritenuta_acconto_bu = '0';
                }

                $id_valuta = $adb->query_result($res_dati_bu,0,'id');
                $id_valuta = html_entity_decode(strip_tags($id_valuta), ENT_QUOTES,$default_charset);
                if($id_valuta == '' || $id_valuta == null || $id_valuta == 0){
                    $id_valuta = 1;
                }

                $mod_tassazione = 'individual';
                $conf_tassazione = '0';
                $ritenuta_acconto_bu = '0';

            }
            else{
                $mod_tassazione = 'individual';
                $conf_tassazione = '0';
                $avviso_fattura = '0';
                $ritenuta_acconto_bu = '0';
                $id_valuta = 1;
            }
        }
        else{
            $mod_tassazione = 'individual';
            $conf_tassazione = '0';
            $avviso_fattura = '0';
            $ritenuta_acconto_bu = '0';
            $id_valuta = 1;
        }

        //se il conto corrente dell'odf è vuoto, prendo i dati del conto corrente di default relazionato all'azienda
        if($conto_corrente == 0){
            $q_dati_conto_corrente = "SELECT cc.kpconticorrentiid,
                                bc.kp_nome_banca,
                                bc.kp_nome_agenzia
                                FROM {$table_prefix}_kpconticorrenti cc
                                INNER JOIN {$table_prefix}_kpbanche bc ON bc.kpbancheid = cc.kp_banca
                                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cc.kpconticorrentiid
                                INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = bc.kpbancheid
                                WHERE ent.deleted = 0 AND ent1.deleted = 0
                                AND cc.kp_default = '1' AND cc.kp_azienda = ".$cliente_fatt;
            $res_dati_conto_corrente = $adb->query($q_dati_conto_corrente);
            if($adb->num_rows($res_dati_conto_corrente) > 0){
                $conto_corrente = $adb->query_result($res_dati_conto_corrente,0,'kpconticorrentiid');
                $conto_corrente = html_entity_decode(strip_tags($conto_corrente), ENT_QUOTES,$default_charset);
                if($conto_corrente == '' || $conto_corrente == null){
                    $conto_corrente = 0;
                }

                $nome_banca = $adb->query_result($res_dati_conto_corrente,0,'kp_nome_banca');
                $nome_banca = html_entity_decode(strip_tags($nome_banca), ENT_QUOTES,$default_charset);

                $nome_agenzia = $adb->query_result($res_dati_conto_corrente,0,'kp_nome_agenzia');
                $nome_agenzia = html_entity_decode(strip_tags($nome_agenzia), ENT_QUOTES,$default_charset);

                $banca_cliente = $nome_banca.' '.$nome_agenzia;
            }
        }
        
        if($ritenuta_acconto_bu == '0'){
            $ritenuta_acconto = '0';
        }

        if($mod_pagamento != 0){
            $q_mod_pagamento = "SELECT modp.condizioni_pagamento
                            FROM {$table_prefix}_modpagamento modp
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = modp.modpagamentoid
                            WHERE ent.deleted = 0 AND modp.modpagamentoid = ".$mod_pagamento;
            $res_mod_pagamento = $adb->query($q_mod_pagamento);
            if($adb->num_rows($res_mod_pagamento) > 0){
                $condizioni_pagamento= $adb->query_result($res_mod_pagamento,0,'condizioni_pagamento');
                $condizioni_pagamento = html_entity_decode(strip_tags($condizioni_pagamento), ENT_QUOTES,$default_charset);
                if($condizioni_pagamento == null || $condizioni_pagamento == ""){
                    $condizioni_pagamento = '';
                }
            }
            else{
                $condizioni_pagamento = '';
            }
        }
        else{
            $condizioni_pagamento = '';
        }

        if($debug){
            $log_content = "

CONF. TASSE: split payment ".$split_payment.", ritenuta acconto ".$ritenuta_acconto.", proforma ".$avviso_fattura;
            $log_file = fopen(__DIR__."/log.txt", "a+");
            fwrite($log_file, $log_content);
            fclose($log_file);
        }
        
        //Verifico se c'è già una fattura in stato 'AutoCreated' per cliente, business unit, commessa e modalita di pagamento
        //Se non trova alcuna fattura in stato 'AutoCreated' per quel cliente crea la testata altrimenti aggiungo solo la riga e aggiorno il totale
        $q_invoice = "SELECT inv.invoiceid invoiceid,
                    inv.subtotal subtotal, 
                    inv.total total,
                    inv.adjustment adjustment
                    FROM {$table_prefix}_invoice inv
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = inv.invoiceid
                    WHERE ent.deleted = 0 AND inv.invoicestatus = 'AutoCreated' AND inv.invoicedate = '".$data_fattura."'
                    AND inv.accountid=".$cliente_fatt." AND inv.kp_business_unit = ".$businessunit." 
                    AND inv.mod_pagamento = ".$mod_pagamento;
        
        /* kpro@bid050920181625 */
        if($raggruppa_per_commessa){
            $q_invoice .= " AND inv.commessa=".$commessa;
        }
        /* kpro@bid050920181625 end */
        
        $res_invoice = $adb->query($q_invoice);		
        if($adb->num_rows($res_invoice) == 0){

            $subtotal = 0;
            $total = 0;
            $adjustment = 0;

            $data_fattura_dt = new DateTime($data_fattura);
            $data_fattura_inv = $data_fattura_dt->format('d-m-Y');
			
            $invoice = CRMEntity::getInstance('Invoice');
            $invoice->column_fields['subject'] = 'Fattura del '.$data_fattura_inv;
            $invoice->column_fields['account_id'] = $cliente_fatt;
            $invoice->column_fields['contact_id'] = $contatto;
            $invoice->column_fields['invoicedate'] = $data_fattura;
            $invoice->column_fields['invoicestatus'] = 'AutoCreated';
            $invoice->column_fields['kp_business_unit'] = $businessunit;
            $invoice->column_fields['kp_avviso_fattura'] = $avviso_fattura;
            /* kpro@bid050920181625 */
            if($raggruppa_per_commessa){
                $invoice->column_fields['commessa'] = $commessa;
            }
            /* kpro@bid050920181625 end */
            $invoice->column_fields['mod_pagamento'] = $mod_pagamento;
            $invoice->column_fields['banca_pagamento'] = $banca_pagamento;
            $invoice->column_fields['kp_conto_corrente'] = $conto_corrente;
            $invoice->column_fields['kp_banca_cliente'] = $banca_cliente;
            $invoice->column_fields['kp_tasse'] = $kp_tasse;
            $invoice->column_fields['kp_tipo_documento'] = 'Fattura';
            $invoice->column_fields['assigned_user_id'] = $current_user->id;
            $invoice->column_fields['bill_street'] = $bill_street;
            $invoice->column_fields['bill_city'] = $bill_city;
            $invoice->column_fields['bill_state'] = $bill_state;
            $invoice->column_fields['bill_code'] = $bill_code;
            $invoice->column_fields['bill_country'] = $bill_country;
            $invoice->column_fields['ship_street'] = $ship_street;
            $invoice->column_fields['ship_city'] = $ship_city;
            $invoice->column_fields['ship_state'] = $ship_state;
            $invoice->column_fields['ship_code'] = $ship_code;
            $invoice->column_fields['ship_country'] = $ship_country;
            $invoice->column_fields['hdnTaxType'] = $mod_tassazione;
            $invoice->column_fields['hdnSubTotal'] = $subtotal;
            $invoice->column_fields['hdnGrandTotal'] = $total;
            $invoice->column_fields['currency_id'] = $id_valuta;
            $invoice->column_fields['conversion_rate'] = 1;
            $invoice->column_fields['hdnDiscountPercent'] = '0';
            $invoice->column_fields['hdnDiscountAmount'] = 0;
            $invoice->column_fields['hdnS_H_Amount'] = 0;

            if($split_payment == '1'){
                $invoice->column_fields['kp_split_payment'] = '1';
            }

            /* kpro@tom240120191400 */
            if( $applica_ritenuta ){
                $invoice->column_fields['kp_applica_ritenuta'] = '1';
                $invoice->column_fields['kp_tipo_ritenuta'] = $tipo_ritenuta;
                $invoice->column_fields['kp_causale_pag_rite'] = $causale_pag_rite;
                $invoice->column_fields['kp_aliquota_ritenuta'] = $aliquota_ritenuta;
            }
            /* kpro@tom240120191400 end */

            $invoice->save('Invoice', $longdesc=true, $offline_update=false, $triggerEvent=false); 

            $invoiceid = $invoice->id;

            $risultato['fatture_create']++;
        }
        else{
            
            $invoiceid = $adb->query_result($res_invoice,0,'invoiceid');
            $invoiceid = html_entity_decode(strip_tags($invoiceid), ENT_QUOTES,$default_charset);

            $subtotal = $adb->query_result($res_invoice,0,'subtotal');
            $subtotal = html_entity_decode(strip_tags($subtotal), ENT_QUOTES,$default_charset);
            
            $total = $adb->query_result($res_invoice,0,'total');
            $total = html_entity_decode(strip_tags($total), ENT_QUOTES,$default_charset);

            $adjustment = $adb->query_result($res_invoice,0,'adjustment');
            $adjustment = html_entity_decode(strip_tags($adjustment), ENT_QUOTES,$default_charset);
        }

        /* kpro@bid250920181215 */
        $commento_riga_fattura = $tipo_odf.": ".$rif_related_to." del ".$data_related_to;
        if($comment_line != ''){
            if($commento_riga_custom == 'tutti'){                
                $commento_riga_fattura .= ' - '.$comment_line;
            }
            else if($commento_riga_custom == 'standard' && $prezzo_unitario >= 0){
                $commento_riga_fattura .= ' - '.$comment_line;
            }
            else if($commento_riga_custom == 'fatture di acconto' && $prezzo_unitario < 0){
                $commento_riga_fattura .= ' - '.$comment_line;
            }
        }
        /* kpro@bid250920181215 end */
		
        //Verifico che tale report visita non sia già presente tra le righe della fattura tramite il campo commento della riga
        //per fare ciò devo comporre il commento con "$tipo_odf $rif_related_to del $data_related_to"
        if($tipo_odf != "Ordini di Vendita"){
            $q_riga_invoice = "SELECT lineitem_id FROM {$table_prefix}_inventoryproductrel
                                WHERE id =".$invoiceid." AND comment = '".$commento_riga_fattura."' AND productid = ".$servizio; 
            $res_riga_invoice = $adb->query($q_riga_invoice);
            if($adb->num_rows($res_riga_invoice)==0){
                $riga_gia_esistente = "No";
            }
            else{
                $riga_gia_esistente = "Si";
            }

        }
        else{
            $riga_gia_esistente = "No";
        }	
        
        if($riga_gia_esistente == "No"){

            if($spese_riba != '0' && $condizioni_pagamento == 'RIBA'){

                $id_statici = getConfigurazioniIdStatici();
                $id_statico = $id_statici["Programmi Custom - Generazione Fatture da ODF - Servizio per addebito spese RIBA"];
                if( $id_statico["valore"] == "" && $id_statico["valore"] == 0){
                    $servizio_spese_riba = 0;
                }
                else{
                    $servizio_spese_riba = $id_statico["valore"];
                }

                if($servizio_spese_riba != 0){

                    $q_riga_invoice = "SELECT lineitem_id FROM {$table_prefix}_inventoryproductrel
                                        WHERE id =".$invoiceid." AND productid = ".$servizio_spese_riba; 
                    $res_riga_invoice = $adb->query($q_riga_invoice);
                    if($adb->num_rows($res_riga_invoice)==0){
                        
                        $q_dati_servizio = "SELECT ser.unit_price,
                                        ser.description
                                        FROM {$table_prefix}_service ser
                                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = ser.serviceid
                                        WHERE ent.deleted = 0 AND ser.serviceid = ".$servizio_spese_riba;
                        $res_dati_servizio = $adb->query($q_dati_servizio);
                        if($adb->num_rows($res_dati_servizio) > 0){
                            $prezzo_unitario_spese_riba = $adb->query_result($res_dati_servizio,0,'unit_price');
                            $prezzo_unitario_spese_riba = html_entity_decode(strip_tags($prezzo_unitario_spese_riba), ENT_QUOTES,$default_charset);
                            if($prezzo_unitario_spese_riba == '' || $prezzo_unitario_spese_riba == null){
                                $prezzo_unitario_spese_riba = 0;
                            }

                            $descrizione_spese_riba = $adb->query_result($res_dati_servizio,0,'description');
                            $descrizione_spese_riba = html_entity_decode(strip_tags($descrizione_spese_riba), ENT_QUOTES,$default_charset);
                            if($descrizione_spese_riba == null){
                                $descrizione_spese_riba = '';
                            }

                            $dati_riga = array(
                                'odfid' => $odfid,
                                'invoiceid' => $invoiceid,
                                'mod_tassazione' => $mod_tassazione,
                                'total_notaxes' => $prezzo_unitario_spese_riba,
                                'subtotal' => $subtotal,
                                'total' => $total,
                                'adjustment' => $adjustment,
                                'servizio' => $servizio_spese_riba,
                                'description' => $descrizione_spese_riba,
                                'qta_fatturata' => 1,
                                'prezzo_unitario' => $prezzo_unitario_spese_riba,
                                'discount_percent' => 0,
                                'discount_amount' => 0,
                                'commento_riga_fattura' => '',
                                'conf_tassazione' => $conf_tassazione,
                                'risultato' => $risultato,
                                'ritenuta_acconto' => $ritenuta_acconto,
                                'split_payment' => $split_payment,
                                'data_fattura' => $data_fattura,
                                'id_tassa' => '', //kpro@tom101220181102
                                'nome_tassa' => '', //kpro@tom101220181102
                                'rif_ordine_cli' => $rif_ordine_cli, //kpro@tom101220181102
                                'data_ord_cli' => $data_ord_cli, //kpro@tom101220181102
                                'codice_cup' => $codice_cup, //kpro@tom101220181102
                                'codice_cig' => $codice_cig //kpro@tom101220181102
                            ); /* kpro@bid250720181030 */
                
                            $dati_fattura = AggiungiRigaFattura($dati_riga);

                            $total = $dati_fattura['total'];
                            $subtotal = $dati_fattura['subtotal'];
                            $adjustment = $dati_fattura['adjustment']; 
                            $risultato = $dati_fattura['risultato'];
                        }
                    }
                }
            }

            $dati_riga = array(
                'odfid' => $odfid,
                'invoiceid' => $invoiceid,
                'mod_tassazione' => $mod_tassazione,
                'total_notaxes' => $total_notaxes,
                'subtotal' => $subtotal,
                'total' => $total,
                'adjustment' => $adjustment,
                'servizio' => $servizio,
                'description' => $description,
                'qta_fatturata' => $qta_fatturata,
                'prezzo_unitario' => $prezzo_unitario,
                'discount_percent' => $discount_percent,
                'discount_amount' => $discount_amount,
                'commento_riga_fattura' => $commento_riga_fattura,
                'conf_tassazione' => $conf_tassazione,
                'risultato' => $risultato,
                'ritenuta_acconto' => $ritenuta_acconto,
                'split_payment' => $split_payment,
                'data_fattura' => $data_fattura,
                'id_tassa' => $id_tassa, //kpro@tom101220181102
                'nome_tassa' => $nome_tassa, //kpro@tom101220181102
                'rif_ordine_cli' => $rif_ordine_cli, //kpro@tom101220181102
                'data_ord_cli' => $data_ord_cli, //kpro@tom101220181102
                'codice_cup' => $codice_cup, //kpro@tom101220181102
                'codice_cig' => $codice_cig //kpro@tom101220181102
            ); /* kpro@bid250720181030 */

            $dati_fattura = AggiungiRigaFattura($dati_riga);
            
            $risultato = $dati_fattura['risultato'];
        }
		
    }	

    /* kpro@tom240120191400 */
    $focus_invoice = CRMEntity::getInstance('Invoice'); 
    $focus_invoice->retrieve_entity_info($invoiceid, "Invoice");
    $focus_invoice->setRitenuta();
    /* kpro@tom240120191400 end */

    return $risultato;
	
}

function AggiungiRigaFattura($dati_riga){
    global $adb, $table_prefix, $current_user, $default_charset;

    $debug = false;

    $mod_tassazione = 'individual';

    $odfid = $dati_riga['odfid'];
    $invoiceid = $dati_riga['invoiceid'];
    $mod_tassazione = $dati_riga['mod_tassazione'];
    $total_notaxes = $dati_riga['total_notaxes'];
    $subtotal = $dati_riga['subtotal'];
    $total = $dati_riga['total'];
    $adjustment = $dati_riga['adjustment'];
    $servizio = $dati_riga['servizio'];
    $description = $dati_riga['description'];
    $qta_fatturata = $dati_riga['qta_fatturata'];
    $prezzo_unitario = $dati_riga['prezzo_unitario'];
    $discount_percent = $dati_riga['discount_percent'];
    $discount_amount = $dati_riga['discount_amount'];
    $commento_riga_fattura = $dati_riga['commento_riga_fattura'];
    $conf_tassazione = $dati_riga['conf_tassazione'];
    $risultato = $dati_riga['risultato'];
    $ritenuta_acconto = $dati_riga['ritenuta_acconto']; /* kpro@bid250720181030 */
    $split_payment = $dati_riga['split_payment']; /* kpro@bid250720181030 */
    $data_fattura = $dati_riga['data_fattura'];

    $id_tassa = $dati_riga['id_tassa']; //kpro@tom101220181102
    $nome_tassa = $dati_riga['nome_tassa']; //kpro@tom101220181102
    $rif_ordine_cli = $dati_riga['rif_ordine_cli']; //kpro@tom101220181102
    $data_ord_cli = $dati_riga['data_ord_cli']; //kpro@tom101220181102
    $codice_cup = $dati_riga['codice_cup']; //kpro@tom101220181102
    $codice_cig = $dati_riga['codice_cig']; //kpro@tom101220181102

    if($discount_percent > 0){
        $total_notaxes = $total_notaxes - ($total_notaxes * $discount_percent / 100);
    }
    elseif($discount_amount > 0){
        $total_notaxes = $total_notaxes - $discount_amount;
    }

    //$post_tasse = DatiProdottoServizio($servizio);
    $post_tasse = false;

    $dati_tassa = getDatiTassaDaOdf($id_tassa, $servizio);

    $id_tassa = $dati_tassa["taxname"];
    $nome_tassa = $dati_tassa["taxlabel"];
    $tax = $dati_tassa["percentage"];

    $totale_tasse = $total_notaxes * $tax / 100;    //kpro@tom010220191210
    
    if($debug){
        $log_content = "
SERVIZIO ".$servizio." - POST TASSE ".$post_tasse." - MOD.TASSAZIONE ".$mod_tassazione."
POST-QUERY: subtotal = ".$subtotal.", total = ".$total.", adjustment = ".$adjustment;
        $log_file = fopen(__DIR__."/log.txt", "a+");
        fwrite($log_file, $log_content);
        fclose($log_file);
    }

    if($mod_tassazione == 'individual'){
        //Recupero la tassa da associare al servizio
        $prezzo_tot_tasse = $total_notaxes;
        if($post_tasse){
            $adjustment = $adjustment + $prezzo_tot_tasse;
        }
        else{
            $prezzo_tot_tasse = $total_notaxes + $totale_tasse; //kpro@tom010220191210
        }

        if(!$post_tasse){
            $subtotal = $subtotal + $prezzo_tot_tasse;
        }
        $total = $subtotal;
    }
    else{
        $prezzo_tot_tasse = $total_notaxes;
        if($post_tasse){
            $adjustment = $adjustment + $prezzo_tot_tasse;
        }
        else{
            $subtotal = $subtotal + $prezzo_tot_tasse;
        }
        $total = $subtotal;
    } 

    if($debug){
        $log_content = "
PRE-INSERIMENTO: subtotal  ".$subtotal.", total = ".$total.", adjustment = ".$adjustment;
        $log_file = fopen(__DIR__."/log.txt", "a+");
        fwrite($log_file, $log_content);
        fclose($log_file);
    }

    //Recupero il numero di sequenza della riga di quell'invoice
    $q_sequenza_riga = "SELECT COALESCE(MAX(sequence_no),0) sequence 
                        FROM {$table_prefix}_inventoryproductrel
                        WHERE id =".$invoiceid;
    $res_sequenza_riga = $adb->query($q_sequenza_riga);
    if($adb->num_rows($res_sequenza_riga)>0){
        $sequence = $adb->query_result($res_sequenza_riga,0,'sequence');
        $sequence++;
    }

    //Recupero l'ultimo seq usato, quindi poi dovrò incrementarlo di uno ed usarlo per la nuova riga fattura
    $q_riga_invoice_seq = "SELECT id FROM {$table_prefix}_inventoryproductrel_seq";
    $res_riga_invoice_seq = $adb->query($q_riga_invoice_seq);			
    if($adb->num_rows($res_riga_invoice_seq)>0){

        $lineitem_id = $adb->query_result($res_riga_invoice_seq,0,'id');
        $lineitem_id++;
        $upd_riga_seq = "UPDATE {$table_prefix}_inventoryproductrel_seq
                            SET id =".$lineitem_id;
        $adb->query($upd_riga_seq);

        //$description = preg_replace( "/\n/", " ", $description );
        //$description = preg_replace( "/\r/", " ", $description );
        //$description = preg_replace( "/<br>/", " ", $description );
        $description = addslashes($description);

        if($discount_percent > 0){
            //kpro@tom010220191210
            $insert_riga_inv = "INSERT INTO {$table_prefix}_inventoryproductrel 
                                (id, productid, relmodule, sequence_no, quantity, listprice, discount_percent, total_notaxes, comment, description, incrementondel, lineitem_id, linetotal, tax_total)
                                VALUES (".$invoiceid.", ".$servizio.", 'Invoice', ".$sequence.", ".$qta_fatturata.", ".$prezzo_unitario.", ".$discount_percent.", ".$total_notaxes.", '".$commento_riga_fattura."', '".$description."', 0, ".$lineitem_id.", ".$prezzo_tot_tasse.", ".$totale_tasse.")";
        }
        elseif($discount_amount > 0){
            //kpro@tom010220191210
            $insert_riga_inv = "INSERT INTO {$table_prefix}_inventoryproductrel 
                                (id, productid, relmodule, sequence_no, quantity, listprice, discount_amount, total_notaxes, comment, description, incrementondel, lineitem_id, linetotal, tax_total)
                                VALUES (".$invoiceid.", ".$servizio.", 'Invoice', ".$sequence.", ".$qta_fatturata.", ".$prezzo_unitario.", ".$discount_amount.", ".$total_notaxes.", '".$commento_riga_fattura."', '".$description."', 0, ".$lineitem_id.", ".$prezzo_tot_tasse.", ".$totale_tasse.")";   
        }
        else{
            //kpro@tom010220191210
            $insert_riga_inv = "INSERT INTO {$table_prefix}_inventoryproductrel 
                                (id, productid, relmodule, sequence_no, quantity, listprice, total_notaxes, comment, description, incrementondel, lineitem_id, linetotal, tax_total)
                                VALUES (".$invoiceid.", ".$servizio.", 'Invoice', ".$sequence.", ".$qta_fatturata.", ".$prezzo_unitario.", ".$total_notaxes.", '".$commento_riga_fattura."', '".$description."', 0, ".$lineitem_id.", ".$prezzo_tot_tasse.", ".$totale_tasse.")";
        }
        
        $adb->query($insert_riga_inv);

        /* kpro@tom101220181102 */
        $insert_riga_cutom = "INSERT INTO kp_inventoryproductrel 
                                (id, productid, relmodule, incrementondel, lineitem_id, rif_ord_cliente, data_ord_cliente, codice_cup, codice_cig, id_tassa, codice_tassa)
                                VALUES (".$invoiceid.", ".$servizio.", 'Invoice',  0, ".$lineitem_id.", '".$rif_ordine_cli."', '".$data_ord_cli."', '".$codice_cup."', '".$codice_cig."','".$id_tassa."', '".$nome_tassa."')";
        $adb->query($insert_riga_cutom);
        /* kpro@tom101220181102 end */

        if($mod_tassazione == 'individual'){

            $update = "UPDATE {$table_prefix}_inventoryproductrel
                        SET {$id_tassa} = {$tax}
                        WHERE lineitem_id = ".$lineitem_id;
            $adb->query($update);
            
        }
        else{
            $totale_netto_con_tasse = $total;
            $totale_tasse = 0;

            $q_tax_group = "SELECT tax.taxname,
                        tax.percentage,
                        kp.aggiungi_a_totale,
                        kp.calcola_su_totale_e_tasse,
                        kp.attivo
                        FROM {$table_prefix}_inventorytaxinfo tax
                        INNER JOIN kp_settings_tasse kp ON kp.id_tassa = tax.taxid
                        WHERE tax.deleted = 0 AND kp.id_configurazione = '{$conf_tassazione}'
                        ORDER BY kp.aggiungi_a_totale DESC, kp.calcola_su_totale_e_tasse";
            $res_tax_group = $adb->query($q_tax_group);
            $num_tax_group = $adb->num_rows($res_tax_group);
            if($num_tax_group > 0){
                for($i = 0; $i < $num_tax_group; $i++){
                    $importo_tassa = 0;

                    $taxname = $adb->query_result($res_tax_group, $i, 'taxname');
                    $taxname = html_entity_decode(strip_tags($taxname), ENT_QUOTES,$default_charset);

                    $attivo = $adb->query_result($res_tax_group, $i, 'attivo');
                    $attivo = html_entity_decode(strip_tags($attivo), ENT_QUOTES,$default_charset);
                    if($attivo == '1' || ($split_payment == '1' && $taxname == 'tax5') || ($ritenuta_acconto == '1' && $taxname == 'tax4')){
                        $percentage = $adb->query_result($res_tax_group, $i, 'percentage');
                        $percentage = html_entity_decode(strip_tags($percentage), ENT_QUOTES,$default_charset);

                        $aggiungi_a_totale = $adb->query_result($res_tax_group, $i, 'aggiungi_a_totale');
                        $aggiungi_a_totale = html_entity_decode(strip_tags($aggiungi_a_totale), ENT_QUOTES,$default_charset);

                        $calcola_su_totale_e_tasse = $adb->query_result($res_tax_group, $i, 'calcola_su_totale_e_tasse');
                        $calcola_su_totale_e_tasse = html_entity_decode(strip_tags($calcola_su_totale_e_tasse), ENT_QUOTES,$default_charset);

                        if($calcola_su_totale_e_tasse == '1' || $calcola_su_totale_e_tasse == 1){
                            $importo_tassa = $totale_netto_con_tasse * $percentage / 100;
                        }
                        else{
                            $importo_tassa = $total * $percentage / 100;
                        }

                        $totale_tasse += $importo_tassa;
                        
                        if($aggiungi_a_totale == '1' || $aggiungi_a_totale == 1){
                            $totale_netto_con_tasse += $totale_tasse;
                        }
                    }
                    else{
                        $percentage = 0.00;
                    }

                    $q_update_riga = "UPDATE {$table_prefix}_inventoryproductrel
                                    SET {$taxname} = {$percentage}
                                    WHERE lineitem_id = ".$lineitem_id;
                    $adb->query($q_update_riga);

                    $q_inventorytotals = "SELECT * 
                                        FROM {$table_prefix}_inventorytotals
                                        WHERE id = ".$invoiceid;
                    $res_inventorytotals = $adb->query($q_inventorytotals);
                    if($adb->num_rows($res_inventorytotals) == 0){
                        $q_insert_totals = "INSERT INTO {$table_prefix}_inventorytotals
                                        (id, {$taxname}, tax_total)
                                        VALUES ({$invoiceid}, {$importo_tassa}, {$totale_tasse})";
                        $adb->query($q_insert_totals);
                    }
                    else{
                        $q_update_totals = "UPDATE {$table_prefix}_inventorytotals SET
                                        {$taxname} = {$importo_tassa},
                                        tax_total = {$totale_tasse}
                                        WHERE id = ".$invoiceid;
                        $adb->query($q_update_totals);
                    }
                }
            }

            $total += $totale_tasse;
        }

        $risultato['righe_create']++;

        if($debug){
            $log_content = "
POST-INSERIMENTO: subtotal = ".$subtotal.", total = ".$total.", adjustment = ".$adjustment;
            $log_file = fopen(__DIR__."/log.txt", "a+");
            fwrite($log_file, $log_content);
            fclose($log_file);
        }

        //if($post_tasse){
            $total += $adjustment;
        //}

        if($debug){
            $log_content = "
POST-INSERIMENTO 2: subtotal = ".$subtotal.", total = ".$total.", adjustment = ".$adjustment;
            $log_file = fopen(__DIR__."/log.txt", "a+");
            fwrite($log_file, $log_content);
            fclose($log_file);
        }

        $upd_fattura = "UPDATE {$table_prefix}_invoice
                        SET subtotal =".$subtotal.", total =".$total.", taxtype = '".$mod_tassazione."', adjustment = ".$adjustment."
                        WHERE invoiceid =".$invoiceid;
        $adb->query($upd_fattura);
        
        $upd_odf = "UPDATE {$table_prefix}_odf
                    SET stato_odf = 'Fatturato', fattura =".$invoiceid.", kp_data_fattura = '".$data_fattura."'
                    WHERE odfid =".$odfid;
        $adb->query($upd_odf); /* kpro@bid130920181600 */

    }

    $dati_fattura = array(
        "total" => $total,
        "subtotal" => $subtotal,
        "adjustment" => $adjustment,
        "risultato" => $risultato
    );

    return $dati_fattura;
}

function DatiProdottoServizio($id){
    global $adb, $table_prefix, $current_user, $default_charset;

    $post_tasse = false;
    
    if($id != '' && $id != null){
        $q_post_tasse = "SELECT pro.kp_post_tasse AS post_tasse
                    FROM {$table_prefix}_products pro
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = pro.productid
                    WHERE ent.deleted = 0 AND pro.productid = {$id}
                    AND pro.kp_post_tasse = '1'
                    UNION
                    SELECT ser.kp_post_tasse AS post_tasse
                    FROM {$table_prefix}_service ser
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = ser.serviceid
                    WHERE ent.deleted = 0 AND ser.serviceid = {$id}
                    AND ser.kp_post_tasse = '1'";
        $res_post_tasse = $adb->query($q_post_tasse);
        if($adb->num_rows($res_post_tasse) > 0){
            $post_tasse = true;
        }
    }

    return $post_tasse;
}

function ControlloAziendaPadre($azienda){
    global $adb, $table_prefix, $current_user, $default_charset;

    $q = "SELECT acc.accountid
        FROM {$table_prefix}_account acc
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
        WHERE ent.deleted = 0 AND acc.accountid = (
            SELECT COALESCE(parentid,0) AS parentid 
            FROM {$table_prefix}_account acc
            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
            WHERE ent.deleted = 0 AND acc.accountid = {$azienda})";
    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $azienda_padre = $adb->query_result($res, 0, 'accountid');
        $azienda_padre = html_entity_decode(strip_tags($azienda_padre), ENT_QUOTES,$default_charset);
        if($azienda_padre != 0 && $azienda_padre != '' && $azienda_padre != null){
            $azienda = $azienda_padre;
        }
    }

    return $azienda;
}

function getDatiTassaDaOdf($tassa_id, $prodotto){
    global $adb, $table_prefix, $current_user, $default_charset;

    if( $tassa_id == "" || $tassa_id == null ){

        $dati_tassa_default = getDatiTassaDefaultDaOdf($prodotto);

        $tassa_id = $dati_tassa_default["taxname"];
        $percentage = $dati_tassa_default["percentage"];
        $taxlabel = $dati_tassa_default["taxlabel"];

    }
    else{

        $query = "SELECT 
                    percentage,
                    taxlabel,
                    kp_codice_iva,
                    kp_natura,
                    kp_norma
                    FROM {$table_prefix}_inventorytaxinfo 
                    WHERE taxname = '".$tassa_id."'";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $percentage = $adb->query_result($result_query, 0, 'percentage');
            $percentage = html_entity_decode(strip_tags($percentage), ENT_QUOTES, $default_charset);

            $taxlabel = $adb->query_result($result_query, 0, 'taxlabel');
            $taxlabel = html_entity_decode(strip_tags($taxlabel), ENT_QUOTES, $default_charset);

        }
        else{

            $dati_tassa_default = getDatiTassaDefaultDaOdf($prodotto);

            $tassa_id = $dati_tassa_default["taxname"];
            $percentage = $dati_tassa_default["percentage"];
            $taxlabel = $dati_tassa_default["taxlabel"];

        }

    }

    $result = array("taxname" => $tassa_id,
                    "taxlabel" => $taxlabel,
                    "percentage" => $percentage);
    
    return $result;

}

function getDatiTassaDefaultDaOdf($prodotto){
    global $adb, $table_prefix, $current_user, $default_charset;

    $query = "SELECT 
                tax.percentage percentage,
                tax.taxname taxname,
                tax.taxlabel taxlabel,
                tax.kp_codice_iva kp_codice_iva,
                tax.kp_natura kp_natura,
                tax.kp_norma kp_norma
                FROM {$table_prefix}_producttaxrel rel
                INNER JOIN {$table_prefix}_inventorytaxinfo tax ON tax.taxid = rel.taxid
                WHERE rel.productid = ".$prodotto."
                ORDER BY tax.percentage DESC";

    $result_query = $adb->query($query);
    $num_result = $adb->num_rows($result_query);

    if( $num_result > 0 ){

        $percentage = $adb->query_result($result_query, 0, 'percentage');
        $percentage = html_entity_decode(strip_tags($percentage), ENT_QUOTES, $default_charset);

        $taxlabel = $adb->query_result($result_query, 0, 'taxlabel');
        $taxlabel = html_entity_decode(strip_tags($taxlabel), ENT_QUOTES, $default_charset);

        $taxname = $adb->query_result($result_query, 0, 'taxname');
        $taxname = html_entity_decode(strip_tags($taxname), ENT_QUOTES, $default_charset);

    }
    else{

        $percentage = 0;
        $taxlabel = "";
        $taxname = "";

    }

    $result = array("taxname" => $taxname,
                    "taxlabel" => $taxlabel,
                    "percentage" => $percentage);

    return $result;

}