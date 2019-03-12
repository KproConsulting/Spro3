<?php

/* kpro@tom300316 */

function generaTicketDaSalesOrder($salesorder_id){
    global $adb, $table_prefix,$current_user;

    /**
     * @author Tomiello Marco
     * @copyright (c) 2016, Kpro Consulting Srl
     * @package fatturazioneConOdf
     * @version 1.0
     * 
     * Questo script genera eventuali Ticket da un sales order
     */
    
    $q_salesorder = "SELECT so.accountid accountid,
                        so.kp_business_unit kp_business_unit, 
                        so.commessa commessa,
                        so.data_ordine data_ordine,
                        so.kp_agente kp_agente,
                        so.kp_tipologia_ordine kp_tipologia_ordine,
                        so.kp_rif_ordine_cli kp_rif_ordine_cli,
                        ent.smownerid assegnatario,
                        acc.accountname accountname,
                        so.kp_data_ord_cli kp_data_ord_cli,
                        so.kp_codice_cup kp_codice_cup,
                        so.kp_codice_cig kp_codice_cig
                        FROM {$table_prefix}_salesorder so
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
                        INNER JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
			            WHERE salesorderid = ".$salesorder_id;
    $res_salesorder = $adb->query($q_salesorder);
    if($adb->num_rows($res_salesorder)>0){

        $accountid = $adb->query_result($res_salesorder,0,'accountid');
        $accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);
        
        $business_unit = $adb->query_result($res_salesorder,0,'kp_business_unit');
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);
        if($business_unit == '' || $business_unit == null){
            $business_unit = 0;
        }

        $agente = $adb->query_result($res_salesorder,0,'kp_agente');
        $agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
        if($agente == '' || $agente == null){
            $agente = 0;
        }
        
        $commessa = $adb->query_result($res_salesorder,0,'commessa');
        $commessa = html_entity_decode(strip_tags($commessa), ENT_QUOTES,$default_charset);
        if($commessa == null || $commessa == ""){
            $commessa = 0;
        }
        
        $data_ordine = $adb->query_result($res_salesorder, 0, 'data_ordine');
        $data_ordine = html_entity_decode(strip_tags($data_ordine), ENT_QUOTES,$default_charset);
        if($data_ordine == null){
            $data_ordine = "";
        }

        /* kpro@bid24112017 */
        $tipologia_ordine = $adb->query_result($res_salesorder, 0, 'kp_tipologia_ordine');
        $tipologia_ordine = html_entity_decode(strip_tags($tipologia_ordine), ENT_QUOTES,$default_charset);
        if($tipologia_ordine == "A consuntivo"){
            $da_fatturare = '1';
        }
        else{
            $da_fatturare = '0';
        }
        /* kpro@bid24112017 end */
        $assegnatario = $adb->query_result($res_salesorder,0,'assegnatario');
        $assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);
        
        $accountname = $adb->query_result($res_salesorder,0,'accountname');
        $accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);

        $riferimento = $adb->query_result($res_salesorder, 0, 'kp_rif_ordine_cli');
        $riferimento = html_entity_decode(strip_tags($riferimento), ENT_QUOTES,$default_charset);
        if($riferimento == null){
            $riferimento = "";
        }

        $data_ord_cli = $adb->query_result($res_salesorder, 0, 'kp_data_ord_cli');
        $data_ord_cli = html_entity_decode(strip_tags($data_ord_cli), ENT_QUOTES,$default_charset);
        if($data_ord_cli == null){
            $data_ord_cli = "";
        }

        $codice_cup = $adb->query_result($res_salesorder, 0, 'kp_codice_cup');
        $codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES,$default_charset);
        if($codice_cup == null){
            $codice_cup = "";
        }

        $codice_cig = $adb->query_result($res_salesorder, 0, 'kp_codice_cig');
        $codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES,$default_charset);
        if($codice_cig == null){
            $codice_cig = "";
        }
        
        $q_righe_ordine = "SELECT 
                            rel.lineitem_id lineitem_id, 
                            rel.productid productid, 
                            rel.quantity quantity, 
                            rel.listprice listprice, 
                            rel.discount_percent discount_percent, 
                            rel.discount_amount discount_amount, 
                            rel.total_notaxes total_notaxes, 
                            rel.comment comment, 
                            rel.linetotal linetotal, 
                            rel.description description,
                            serv.servicename servicename, 
                            serv.area_aziendale area_aziendale, 
                            ent.smownerid handler,
                            serv.kp_costo_orario kp_costo_orario,
                            custominvrel.id_tassa id_tassa,
                            custominvrel.rif_ord_cliente rif_ord_cliente,
                            custominvrel.data_ord_cliente data_ord_cliente,
                            custominvrel.codice_cup codice_cup,
                            custominvrel.codice_cig codice_cig,
                            taxinfo.taxlabel taxlabel,
                            taxinfo.kp_natura natura,
                            taxinfo.kp_codice_iva codice_iva,
                            taxinfo.kp_norma norma
                            FROM {$table_prefix}_inventoryproductrel rel
                            INNER JOIN {$table_prefix}_service serv ON serv.serviceid = rel.productid
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = serv.serviceid
                            LEFT JOIN kp_inventoryproductrel custominvrel ON custominvrel.lineitem_id = rel.lineitem_id AND custominvrel.id = rel.id
                            LEFT JOIN {$table_prefix}_inventorytaxinfo taxinfo ON taxinfo.taxname = custominvrel.id_tassa
                            WHERE rel.relmodule = 'SalesOrder' AND serv.genera_ticket = '1' 
                            AND serv.servizio_a_canone != '1' AND rel.id = ".$salesorder_id;
        
        $res_righe_ordine = $adb->query($q_righe_ordine);
        $num_righe_ordine = $adb->num_rows($res_righe_ordine);
        
        for($i=0; $i<$num_righe_ordine; $i++){
            $productid = $adb->query_result($res_righe_ordine,$i,'productid');
            $productid = html_entity_decode(strip_tags($productid), ENT_QUOTES,$default_charset);
            
            $quantity = $adb->query_result($res_righe_ordine,$i,'quantity');
            $quantity = html_entity_decode(strip_tags($quantity), ENT_QUOTES,$default_charset);
            
            $listprice = $adb->query_result($res_righe_ordine,$i,'listprice');
            $listprice = html_entity_decode(strip_tags($listprice), ENT_QUOTES,$default_charset);
            
            $lineitem_id = $adb->query_result($res_righe_ordine,$i,'lineitem_id');
            $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES,$default_charset);
            
            $discount_percent = $adb->query_result($res_righe_ordine,$i,'discount_percent');
            $discount_percent = html_entity_decode(strip_tags($discount_percent), ENT_QUOTES,$default_charset);
            
            $discount_amount = $adb->query_result($res_righe_ordine,$i,'discount_amount');
            $discount_amount = html_entity_decode(strip_tags($discount_amount), ENT_QUOTES,$default_charset);
            
            $total_notaxes = $adb->query_result($res_righe_ordine,$i,'total_notaxes');
            $total_notaxes = html_entity_decode(strip_tags($total_notaxes), ENT_QUOTES,$default_charset);
            
            $comment = $adb->query_result($res_righe_ordine,$i,'comment');
            $comment = html_entity_decode(strip_tags($comment), ENT_QUOTES,$default_charset);

            $linetotal = $adb->query_result($res_righe_ordine,$i,'linetotal');
            $linetotal = html_entity_decode(strip_tags($linetotal), ENT_QUOTES,$default_charset);
            
            $description_line = $adb->query_result($res_righe_ordine,$i,'description');
            $description_line = html_entity_decode(strip_tags($description_line), ENT_QUOTES,$default_charset);
            if($description_line == null){
                $description_line = "";
            }
    
            if($description_line != "" && $riferimento != ""){
                if($description_line != ""){
                    $description_line = "Rif. ".$riferimento."
".$description_line;
                }
                else{
                    $description_line = "Rif. ".$riferimento;
                }
            }
            
            $handler = $adb->query_result($res_righe_ordine,$i,'handler');
            $handler = html_entity_decode(strip_tags($handler), ENT_QUOTES,$default_charset);
            if( $handler  == null || $handler  == "" ){
                $handler = 1;
            }

            $servicename = $adb->query_result($res_righe_ordine,$i,'servicename');
            $servicename = html_entity_decode(strip_tags($servicename), ENT_QUOTES,$default_charset);

            $area_aziendale = $adb->query_result($res_righe_ordine,$i,'area_aziendale');
            $area_aziendale = html_entity_decode(strip_tags($area_aziendale), ENT_QUOTES,$default_charset);

            $costo_orario = $adb->query_result($res_righe_ordine,$i,'kp_costo_orario');
            $costo_orario = html_entity_decode(strip_tags($costo_orario), ENT_QUOTES,$default_charset);
            if($costo_orario == '' || $costo_orario == null || $costo_orario < 0){
                $costo_orario = 0;
            }

            if($costo_orario > 0){
                $tempo_previsto = round($total_notaxes / $costo_orario);
            }
            else{
                $tempo_previsto = 0;
            }

            $id_tassa = $adb->query_result($res_righe_ordine, $i, 'id_tassa');
            $id_tassa = html_entity_decode(strip_tags($id_tassa), ENT_QUOTES,$default_charset);
            if( $id_tassa == null ){
                $id_tassa = "";
            }

            $taxlabel = $adb->query_result($res_righe_ordine, $i, 'taxlabel');
            $taxlabel = html_entity_decode(strip_tags($taxlabel), ENT_QUOTES,$default_charset);
            if( $taxlabel == null ){
                $taxlabel = "";
            }
            
            //Verifico cse esiste gi� un ticket in stato aperto per quel servizio collegato a tale ordine
            $q_ticket = "SELECT tick.ticketid 
                            FROM {$table_prefix}_troubletickets tick
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = tick.ticketid
                            WHERE ent.deleted = 0 AND tick.servizio = ".$productid." AND tick.salesorder = ".$salesorder_id;                
            $res_ticket = $adb->query($q_ticket);
            
            if($adb->num_rows($res_ticket)==0){
        
                //Se non trovo alcun ticket aperto per quel servizio e quell'ordine devo crearlo
                
                $ticket = CRMEntity::getInstance('HelpDesk');
                $ticket->column_fields['assigned_user_id'] = $handler;
                $ticket->column_fields['ticket_title'] = $servicename;
                $ticket->column_fields['description'] = $description_line;
                $ticket->column_fields['servizio'] = $productid;
                $ticket->column_fields['area_aziendale'] = $area_aziendale;
                $ticket->column_fields['ticketstatus'] = 'Open';
                $ticket->column_fields['parent_id'] = $accountid;
                $ticket->column_fields['salesorder'] = $salesorder_id;
                $ticket->column_fields['kp_business_unit'] = $business_unit;
                $ticket->column_fields['kp_agente'] = $agente;
                $ticket->column_fields['commessa'] = $commessa;
                $ticket->column_fields['prezzo'] = $linetotal;
                $ticket->column_fields['so_line_id'] = $lineitem_id;
                $ticket->column_fields['listprice'] = $listprice;
                $ticket->column_fields['quantity'] = $quantity;
                $ticket->column_fields['discount_percent'] = $discount_percent;
                $ticket->column_fields['discount_amount'] = $discount_amount;
                $ticket->column_fields['total_notaxes'] = $total_notaxes;
                $ticket->column_fields['comment_line'] = $comment;
                $ticket->column_fields['kp_data_elem_rif'] = $data_ordine;
                $ticket->column_fields['da_fatturare'] = $da_fatturare; /* kpro@bid24112017 */

                $ticket->column_fields['kp_rif_ordine_cli'] = $riferimento; /* kpro@tom101220181102 */
                $ticket->column_fields['kp_data_ord_cli'] = $data_ord_cli; /* kpro@tom101220181102 */
                $ticket->column_fields['kp_codice_cup'] = $codice_cup; /* kpro@tom101220181102 */
                $ticket->column_fields['kp_codice_cig'] = $codice_cig; /* kpro@tom101220181102 */
                $ticket->column_fields['kp_id_tassa'] = $id_tassa; /* kpro@tom101220181102 */
                $ticket->column_fields['kp_nome_tassa'] = $taxlabel; /* kpro@tom101220181102 */

                if($tempo_previsto > 0){
                    $ticket->column_fields['kp_tempo_previsto'] = $tempo_previsto; /* kpro@bid04122017 */
                }
                $ticket->save('HelpDesk', $longdesc=true, $offline_update=false, $triggerEvent=false);

            }
                        
        }
                        
    }
	
}

function generaCanoniDaSalesOrder($sales_order){
    global $adb, $table_prefix,$current_user;
        
    /**
     * @author Tomiello Marco
     * @copyright (c) 2015, Kpro Consulting Srl
     * @package fatturazioneConOdf
     * @version 1.0
     * 
     * Questo script genera eventuali canoni da un sales order
     */
	
    $q_sales_order = "SELECT so.accountid accountid,
                        so.data_ordine data_ordine,
                        so.kp_business_unit kp_business_unit, 
                        so.kp_agente kp_agente,
                        so.commessa commessa,
                        so.kp_data_inizio_fatt kp_data_inizio_fatt,
                        so.kp_data_fine_fatt kp_data_fine_fatt,
                        so.frequenza_fatturazione frequenza_fatturazione,
                        so.kp_rif_ordine_cli kp_rif_ordine_cli,
                        ent.smownerid assegnatario,
                        acc.accountname accountname,
                        so.kp_data_ord_cli kp_data_ord_cli,
                        so.kp_codice_cup kp_codice_cup,
                        so.kp_codice_cig kp_codice_cig
                        FROM {$table_prefix}_salesorder so
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
                        INNER JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
                        WHERE so.salesorderid = ".$sales_order;
    $res_sales_order = $adb->query($q_sales_order); 							

    if($adb->num_rows($res_sales_order)>0){						 
        $accountid = $adb->query_result($res_sales_order, 0, 'accountid'); 
        $accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);

        $data_ordine = $adb->query_result($res_sales_order, 0, 'data_ordine');
        $data_ordine = html_entity_decode(strip_tags($data_ordine), ENT_QUOTES,$default_charset);
        if($data_ordine == null){
            $data_ordine = "";
        }

        $assegnatario = $adb->query_result($res_sales_order, 0, 'assegnatario');
        $assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);

        $accountname = $adb->query_result($res_sales_order, 0, 'accountname');
        $accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
        
        $business_unit = $adb->query_result($res_sales_order,0,'kp_business_unit');
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);
        if($business_unit == '' || $business_unit == null){
            $business_unit = 0;
        }

        $agente = $adb->query_result($res_sales_order,0,'kp_agente');
        $agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
        if($agente == '' || $agente == null){
            $agente = 0;
        }
        
        $commessa = $adb->query_result($res_sales_order,0,'commessa');
        $commessa = html_entity_decode(strip_tags($commessa), ENT_QUOTES,$default_charset);
        if($commessa == null || $commessa == ""){
            $commessa = 0;
        }

        $data_inizio_fatt = $adb->query_result($res_sales_order,0,'kp_data_inizio_fatt');
        $data_inizio_fatt = html_entity_decode(strip_tags($data_inizio_fatt), ENT_QUOTES,$default_charset);
        if($data_inizio_fatt == null || $data_inizio_fatt == "" || $data_inizio_fatt == "0000-00-00"){
            $data_inizio_fatt = "";
        }

        $data_fine_fatt = $adb->query_result($res_sales_order,0,'kp_data_fine_fatt');
        $data_fine_fatt = html_entity_decode(strip_tags($data_fine_fatt), ENT_QUOTES,$default_charset);
        if($data_fine_fatt == null || $data_fine_fatt == "" || $data_fine_fatt == "0000-00-00"){
            $data_fine_fatt = "";
        }

        $frequenza_fatturazione = $adb->query_result($res_sales_order,0,'frequenza_fatturazione');
        $frequenza_fatturazione = html_entity_decode(strip_tags($frequenza_fatturazione), ENT_QUOTES,$default_charset);
        if($frequenza_fatturazione == null || $frequenza_fatturazione == ""){
            $frequenza_fatturazione = "Annuale";
        }

        $riferimento = $adb->query_result($res_sales_order, 0, 'kp_rif_ordine_cli');
        $riferimento = html_entity_decode(strip_tags($riferimento), ENT_QUOTES,$default_charset);
        if($riferimento == null){
            $riferimento = "";
        }

        $data_ord_cli = $adb->query_result($res_sales_order, 0, 'kp_data_ord_cli');
        $data_ord_cli = html_entity_decode(strip_tags($data_ord_cli), ENT_QUOTES,$default_charset);
        if($data_ord_cli == null){
            $data_ord_cli = "";
        }

        $codice_cup = $adb->query_result($res_sales_order, 0, 'kp_codice_cup');
        $codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES,$default_charset);
        if($codice_cup == null){
            $codice_cup = "";
        }

        $codice_cig = $adb->query_result($res_sales_order, 0, 'kp_codice_cig');
        $codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES,$default_charset);
        if($codice_cig == null){
            $codice_cig = "";
        }

    }
	
    $q_ver_servizi_a_canone = "SELECT 
                                prodrel.total_notaxes totale_linea,
                                prodrel.quantity quantity,
                                prodrel.listprice listprice,
                                prodrel.discount_percent discount_percent,
                                prodrel.discount_amount discount_amount,
                                prodrel.lineitem_id lineitem_id,
                                prodrel.sequence_no sequence_no,
                                prodrel.description description,
                                serv.serviceid serviceid,
                                serv.servicename servicename,
                                custominvrel.id_tassa id_tassa,
                                custominvrel.rif_ord_cliente rif_ord_cliente,
                                custominvrel.data_ord_cliente data_ord_cliente,
                                custominvrel.codice_cup codice_cup,
                                custominvrel.codice_cig codice_cig,
                                taxinfo.taxlabel taxlabel,
                                taxinfo.kp_natura natura,
                                taxinfo.kp_codice_iva codice_iva,
                                taxinfo.kp_norma norma
                                FROM {$table_prefix}_inventoryproductrel prodrel
                                INNER JOIN {$table_prefix}_service serv ON serv.serviceid = prodrel.productid
                                LEFT JOIN kp_inventoryproductrel custominvrel ON custominvrel.lineitem_id = prodrel.lineitem_id AND custominvrel.id = prodrel.id
                                LEFT JOIN {$table_prefix}_inventorytaxinfo taxinfo ON taxinfo.taxname = custominvrel.id_tassa
                                WHERE serv.servizio_a_canone = '1' AND prodrel.id = ".$sales_order;
    $res_ver_servizi_a_canone = $adb->query($q_ver_servizi_a_canone);
    $num_servizi_a_canone = $adb->num_rows($res_ver_servizi_a_canone);
    for($i=0; $i<$num_servizi_a_canone; $i++){
        
        $lineitem_id = $adb->query_result($res_ver_servizi_a_canone, $i, 'lineitem_id');
        $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES,$default_charset);
        
        $sequence_no = $adb->query_result($res_ver_servizi_a_canone, $i, 'sequence_no');
        $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES,$default_charset);
				
        $servizio = $adb->query_result($res_ver_servizi_a_canone, $i, 'serviceid');
        $servizio = html_entity_decode(strip_tags($servizio), ENT_QUOTES,$default_charset);

        $totale_linea = $adb->query_result($res_ver_servizi_a_canone, $i, 'totale_linea');
        $totale_linea = html_entity_decode(strip_tags($totale_linea), ENT_QUOTES,$default_charset);
        
        $servicename = $adb->query_result($res_ver_servizi_a_canone, $i, 'servicename');
        $servicename = html_entity_decode(strip_tags($servicename), ENT_QUOTES,$default_charset);
        $servicename = addslashes($servicename);

        $fornitore = $adb->query_result($res_ver_servizi_a_canone, $i, 'fornitore');
        $fornitore = html_entity_decode(strip_tags($fornitore), ENT_QUOTES,$default_charset);

        $quantity = $adb->query_result($res_ver_servizi_a_canone, $i, 'quantity');
        $quantity = html_entity_decode(strip_tags($quantity), ENT_QUOTES,$default_charset);
        if($quantity == '' || $quantity == null){
            $quantity = 0;
        }

        $listprice = $adb->query_result($res_ver_servizi_a_canone, $i, 'listprice');
        $listprice = html_entity_decode(strip_tags($listprice), ENT_QUOTES,$default_charset);
        if($listprice == '' || $listprice == null){
            $listprice = 0;
        }

        $discount_percent = $adb->query_result($res_ver_servizi_a_canone, $i, 'discount_percent');
        $discount_percent = html_entity_decode(strip_tags($discount_percent), ENT_QUOTES,$default_charset);
        if($discount_percent == '' || $discount_percent == null){
            $discount_percent = 0;
        }

        $discount_amount = $adb->query_result($res_ver_servizi_a_canone, $i, 'discount_amount');
        $discount_amount = html_entity_decode(strip_tags($discount_amount), ENT_QUOTES,$default_charset);
        if($discount_amount == '' || $discount_amount == null){
            $discount_amount = 0;
        }
        if($discount_amount != 0){
            $discount_amount = $discount_amount / $quantity;
        }

        $description_line = $adb->query_result($res_ver_servizi_a_canone, $i, 'description');
        $description_line = html_entity_decode(strip_tags($description_line), ENT_QUOTES,$default_charset);
        if($description_line == null){
            $description_line = "";
        }

        $id_tassa = $adb->query_result($res_ver_servizi_a_canone, $i, 'id_tassa');
        $id_tassa = html_entity_decode(strip_tags($id_tassa), ENT_QUOTES,$default_charset);
        if( $id_tassa == null ){
            $id_tassa = "";
        }

        $taxlabel = $adb->query_result($res_ver_servizi_a_canone, $i, 'taxlabel');
        $taxlabel = html_entity_decode(strip_tags($taxlabel), ENT_QUOTES,$default_charset);
        if( $taxlabel == null ){
            $taxlabel = "";
        }

        if($riferimento != ""){
            if($description_line != ""){
                $description_line = "Rif. ".$riferimento."
".$description_line;
            }
            else{
                $description_line = "Rif. ".$riferimento;
            }
        }

        if($data_inizio_fatt != ""){
            $data_mese_inizio_canone = $data_inizio_fatt;
        }
        else{
            $data_mese_inizio_canone = $data_ordine;
        }

        if($data_mese_inizio_canone != '' && $data_mese_inizio_canone != null){
            list($anno_inizio_canone,$mese_inizio_canone,$giorno_inizio_canone) = explode("-",$data_mese_inizio_canone);
        }
        else{
            $mese_inizio_canone = '1';
            $anno_inizio_canone = date('Y');
        }
		
        $mese_fatturazione = ltrim($mese_inizio_canone, '0');
        $anno_fatturazione = $anno_inizio_canone;
		
        $q_ver_canone = "SELECT can.canoniid canoniid,
                            can.stato_canone stato_canone
                            FROM {$table_prefix}_canoni can
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = can.canoniid
                            WHERE ent.deleted = 0 AND can.sales_order = ".$sales_order." AND can.servizio = ".$servizio." 
                            AND can.salesorder_line_id = '".$sequence_no."'";
        
        $res_ver_canone = $adb->query($q_ver_canone);
        if($adb->num_rows($res_ver_canone)>0){	
            $canoniid = $adb->query_result($res_ver_canone, 0, 'canoniid');
            $canoniid = html_entity_decode(strip_tags($canoniid), ENT_QUOTES,$default_charset);	

            $stato_canone = $adb->query_result($res_ver_canone, 0, 'stato_canone');
            $stato_canone = html_entity_decode(strip_tags($stato_canone), ENT_QUOTES,$default_charset);				

            if($stato_canone == "Autocreato"){

                $mese_fatturazione = addslashes($mese_fatturazione);
                $description_line = addslashes($description_line);

                $upd_canone = "UPDATE {$table_prefix}_canoni SET 
                                account = ".$accountid.",
                                data_inizio = '".$data_ordine."',";
                if($data_fine_fatt != ""){
                    $upd_canone .= " data_fine = '".$data_fine_fatt."',";
                }
                if($data_inizio_fatt != ""){
                    $upd_canone .= " data_inizio_fatt = '".$data_inizio_fatt."',";
                }
                $upd_canone .= " prezzo = ".$totale_linea.",
                                mese_fatturazione = '".$mese_fatturazione."',
                                kp_anno_fatt = '".$anno_fatturazione."',
                                kp_business_unit = ".$business_unit.",
                                kp_agente = ".$agente.",
                                description = '".$description_line."'
                                WHERE canoniid = ".$canoniid;  
                $adb->query($upd_canone);

            }

        }
        else{
            $new_canone = CRMEntity::getInstance('Canoni'); 
            $new_canone->column_fields['assigned_user_id'] = $assegnatario;
            $new_canone->column_fields['canone_name'] = 'Canone '.$servicename.' '.$accountname;
            $new_canone->column_fields['account'] = $accountid;
            $new_canone->column_fields['data_inizio'] = $data_ordine;
            if($data_fine_fatt != ""){
                $new_canone->column_fields['data_fine'] = $data_fine_fatt;
            }
            if($data_inizio_fatt != ""){
                $new_canone->column_fields['data_inizio_fatt'] = $data_inizio_fatt;
                $new_canone->column_fields['stato_canone'] = 'Attivo';
            }
            else{
                $new_canone->column_fields['stato_canone'] = 'Autocreato';
            }
            $new_canone->column_fields['servizio'] = $servizio;
            $new_canone->column_fields['prezzo'] = $totale_linea;
            $new_canone->column_fields['mese_fatturazione'] = $mese_fatturazione;
            $new_canone->column_fields['kp_anno_fatt'] = $anno_fatturazione;
            $new_canone->column_fields['commessa'] = $commessa;
            $new_canone->column_fields['kp_business_unit'] = $business_unit;
            $new_canone->column_fields['kp_agente'] = $agente;
            $new_canone->column_fields['frequenza_fatturazione'] = $frequenza_fatturazione;
            $new_canone->column_fields['sales_order'] = $sales_order;
            $new_canone->column_fields['salesorder_line_id'] = $sequence_no;
            $new_canone->column_fields['description'] = $description_line;

            $new_canone->column_fields['kp_rif_ordine_cli'] = $riferimento; /* kpro@tom101220181102 */
            $new_canone->column_fields['kp_data_ord_cli'] = $data_ord_cli; /* kpro@tom101220181102 */
            $new_canone->column_fields['kp_codice_cup'] = $codice_cup; /* kpro@tom101220181102 */
            $new_canone->column_fields['kp_codice_cig'] = $codice_cig; /* kpro@tom101220181102 */
            $new_canone->column_fields['kp_id_tassa'] = $id_tassa; /* kpro@tom101220181102 */
            $new_canone->column_fields['kp_nome_tassa'] = $taxlabel; /* kpro@tom101220181102 */

            $new_canone->save('Canoni', $longdesc=true, $offline_update=false, $triggerEvent=false); 
        }
				
    }
	
}

function generaPurchaseOrderDaSalesOrder($sales_order){
    global $adb, $table_prefix,$current_user;

    /**
     * @author Tomiello Marco
     * @copyright (c) 2015, Kpro Consulting Srl
     * @package fatturazioneConOdf
     * @version 1.0
     * 
     * Questo script genera eventuali puschase order da un sales order
     */
	
    require_once('classe_line.php');

    $q_sales_order = "SELECT so.accountid accountid,
                        so.data_ordine data_ordine,
                        so.kp_business_unit kp_business_unit,
                        ent.smownerid assegnatario,
                        acc.accountname accountname
                        FROM {$table_prefix}_salesorder so
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
                        INNER JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
                        WHERE so.salesorderid = ".$sales_order;
    $res_sales_order = $adb->query($q_sales_order); 							

    if($adb->num_rows($res_sales_order)>0){						 
        $accountid = $adb->query_result($res_sales_order, 0, 'accountid'); 
        $accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);

        $data_ordine = $adb->query_result($res_sales_order, 0, 'data_ordine');
        $data_ordine = html_entity_decode(strip_tags($data_ordine), ENT_QUOTES,$default_charset);
        if($data_ordine == null){
                $data_ordine = "";
        }

        $assegnatario = $adb->query_result($res_sales_order, 0, 'assegnatario');
        $assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);

        $accountname = $adb->query_result($res_sales_order, 0, 'accountname');
        $accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);

        $business_unit = $adb->query_result($res_sales_order, 0, 'kp_business_unit');
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);
        if($business_unit == '' || $business_unit == null){
            $business_unit = 0;
        }
    }

    $q_ver_servizi_con_po = "SELECT 
                                prodrel.total_notaxes totale_linea,
                                prodrel.quantity quantity,
                                prodrel.lineitem_id lineitem_id,
                                prodrel.sequence_no sequence_no,
                                serv.serviceid serviceid,
                                serv.servicename servicename,
                                serv.servicecategory servicecategory,
                                serv.fornitore fornitore,
                                serv.unit_price service_unit_price,
                                serv.per_sconto_acquisto per_sconto_acquisto,
                                serv.costo costo,
                                serv.frequenza_fatturazione frequenza_fatturazione
                                FROM {$table_prefix}_inventoryproductrel prodrel
                                INNER JOIN {$table_prefix}_service serv ON serv.serviceid = prodrel.productid
                                WHERE prodrel.id = ".$sales_order." AND serv.fornitore != '' 
                                AND serv.fornitore != 0 AND serv.fornitore IS NOT NULL";

    $res_ver_servizi_con_po = $adb->query($q_ver_servizi_con_po);
    $num_servizi_con_po = $adb->num_rows($res_ver_servizi_con_po);
    for($i=0; $i<$num_servizi_con_po; $i++){
        
        $lineitem_id = $adb->query_result($res_ver_servizi_con_po, $i, 'lineitem_id');
        $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES,$default_charset);
        
        $sequence_no = $adb->query_result($res_ver_servizi_con_po, $i, 'sequence_no');
        $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES,$default_charset);

        $servizio = $adb->query_result($res_ver_servizi_con_po, $i, 'serviceid');
        $servizio = html_entity_decode(strip_tags($servizio), ENT_QUOTES,$default_charset);

        $totale_linea = $adb->query_result($res_ver_servizi_con_po, $i, 'totale_linea');
        $totale_linea = html_entity_decode(strip_tags($totale_linea), ENT_QUOTES,$default_charset);

        $servicecategory = $adb->query_result($res_ver_servizi_con_po, $i, 'servicecategory');
        $servicecategory = html_entity_decode(strip_tags($servicecategory), ENT_QUOTES,$default_charset);

        $servicename = $adb->query_result($res_ver_servizi_con_po, $i, 'servicename');
        $servicename = html_entity_decode(strip_tags($servicename), ENT_QUOTES,$default_charset);

        $fornitore = $adb->query_result($res_ver_servizi_con_po, $i, 'fornitore');
        $fornitore = html_entity_decode(strip_tags($fornitore), ENT_QUOTES,$default_charset);

        $quantity = $adb->query_result($res_ver_servizi_con_po, $i, 'quantity');
        $quantity = html_entity_decode(strip_tags($quantity), ENT_QUOTES,$default_charset);

        $service_unit_price = $adb->query_result($res_ver_servizi_con_po, $i, 'service_unit_price');
        $service_unit_price = html_entity_decode(strip_tags($service_unit_price), ENT_QUOTES,$default_charset);

        $per_sconto_acquisto = $adb->query_result($res_ver_servizi_con_po, $i, 'per_sconto_acquisto');
        $per_sconto_acquisto = html_entity_decode(strip_tags($per_sconto_acquisto), ENT_QUOTES,$default_charset);

        $costo = $adb->query_result($res_ver_servizi_con_po, $i, 'costo');
        $costo = html_entity_decode(strip_tags($costo), ENT_QUOTES,$default_charset);

        $frequenza_fatturazione = $adb->query_result($res_ver_servizi_con_po, $i, 'frequenza_fatturazione');
        $frequenza_fatturazione = html_entity_decode(strip_tags($frequenza_fatturazione), ENT_QUOTES,$default_charset);
        if($frequenza_fatturazione == null || $frequenza_fatturazione == ""){
            $frequenza_fatturazione = "12";
        }

        $costo_servizio = $costo * (1 - ($per_sconto_acquisto / 100));

        //Verifico se esiste gi�� un ordine per quel fornitore collegato a questo sales order
        $q_ver_po = "SELECT po.purchaseorderid purchaseorderid FROM {$table_prefix}_purchaseorder po
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = po.purchaseorderid
                        WHERE ent.deleted = 0 AND po.vendorid = ".$fornitore." AND po.ordine_vendita = ".$sales_order;

        $res_ver_po = $adb->query($q_ver_po); 	
        if($adb->num_rows($res_ver_po)>0){						 
            $purchaseorderid = $adb->query_result($res_ver_po, 0, 'purchaseorderid'); 
        }
        else{

            $q_fornitore = "SELECT street,
                            city,
                            state,
                            postalcode,
                            country,
                            mod_pagamento
                            FROM {$table_prefix}_vendor
                            WHERE vendorid = ".$fornitore;
            $res_fornitore = $adb->query($q_fornitore);

            if($adb->num_rows($res_fornitore)>0){						 
                $street = $adb->query_result($res_fornitore, 0, 'street'); 
                $street = html_entity_decode(strip_tags($street), ENT_QUOTES,$default_charset);

                $city = $adb->query_result($res_fornitore, 0, 'city'); 
                $city = html_entity_decode(strip_tags($city), ENT_QUOTES,$default_charset);

                $state = $adb->query_result($res_fornitore, 0, 'state'); 
                $state = html_entity_decode(strip_tags($state), ENT_QUOTES,$default_charset);

                $postalcode = $adb->query_result($res_fornitore, 0, 'postalcode'); 
                $postalcode = html_entity_decode(strip_tags($postalcode), ENT_QUOTES,$default_charset);

                $country = $adb->query_result($res_fornitore, 0, 'country'); 
                $country = html_entity_decode(strip_tags($country), ENT_QUOTES,$default_charset);

                $mod_pagamento = $adb->query_result($res_fornitore, 0, 'mod_pagamento'); 
                $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
                if($mod_pagamento == '' || $mod_pagamento == null){
                    $mod_pagamento = 0;
                }
            }

            $new_po = CRMEntity::getInstance('PurchaseOrder');
            $new_po->column_fields['assigned_user_id'] = $assegnatario;
            $new_po->column_fields['subject'] = 'Aquisto '.$servicename.' '.$accountname;
            $new_po->column_fields['vendor_id'] = $fornitore;
            $new_po->column_fields['postatus'] = 'Autocreato';
            $new_po->column_fields['mod_pagamento'] = $mod_pagamento;
            //$new_po->column_fields['kp_business_unit'] = $business_unit;

            $new_po->column_fields['bill_street'] = $street;
            $new_po->column_fields['bill_city'] = $city;
            $new_po->column_fields['bill_state'] = $state;
            $new_po->column_fields['bill_code'] = $postalcode;
            $new_po->column_fields['bill_country'] = $country;
            $new_po->column_fields['ship_street'] = $street;
            $new_po->column_fields['ship_city'] = $city;
            $new_po->column_fields['ship_state'] = $state;
            $new_po->column_fields['ship_code'] = $postalcode;
            $new_po->column_fields['ship_country'] = $country;

            $new_po->column_fields['data_ordine'] = $data_ordine;
            //$new_po->column_fields['data_pagamento1'] = $data_ordine;
            //$new_po->column_fields['perc_pagamento1'] = 100;
            $new_po->column_fields['ordine_vendita'] = $sales_order;

            $new_po->column_fields['currency_id'] = 1;
            $new_po->column_fields['hdnTaxType'] = 'individual';
            $new_po->column_fields['hdnSubTotal'] = 0;
            $new_po->column_fields['hdnGrandTotal'] = 0;
            $new_po->column_fields['currency_id'] = 1;
            $new_po->column_fields['conversion_rate'] = 1;
            $new_po->column_fields['hdnDiscountPercent'] = '0';
            $new_po->column_fields['hdnDiscountAmount'] = 0;
            $new_po->column_fields['hdnS_H_Amount'] = 0;
            $new_po->save('PurchaseOrder', $longdesc=true, $offline_update=false, $triggerEvent=false); 
            $purchaseorderid = $new_po->id;

            $del_righe = "DELETE FROM {$table_prefix}_inventoryproductrel
                                            WHERE id = ".$purchaseorderid;
            $adb->query($del_righe);

            $upd_po = "UPDATE {$table_prefix}_purchaseorder
                                    SET subtotal = 0, total = 0, taxtype = 'individual'
                                    WHERE purchaseorderid =".$purchaseorderid;
            $adb->query($upd_po);

        }

        //Verifico se esiste gi�� una riga per quel servizio	
        $q_ver_riga = "SELECT lineitem_id FROM {$table_prefix}_inventoryproductrel
                        WHERE id = ".$purchaseorderid." AND productid = ".$servizio." AND comment = 'Rif. Riga SalesOrder N. ".$sequence_no."'";
           
        $res_ver_riga = $adb->query($q_ver_riga); 
        if($adb->num_rows($res_ver_riga)>0){
            $lineitem_id = $adb->query_result($res_ver_riga, 0, 'lineitem_id');

            $q_line = "SELECT lineitem_id, 
                        discount_amount, 
                        discount_percent, 
                        tax1 
                        FROM {$table_prefix}_inventoryproductrel 
                        WHERE lineitem_id = ".$lineitem_id;
            $res_line = $adb->query($q_line);
            if($adb->num_rows($res_line)>0){
                $discount_amount = $adb->query_result($res_line,0,'discount_amount');
                if($discount_amount == null || $discount_amount == ''){
                    $discount_amount = 0;
                }

                $discount_percent = $adb->query_result($res_line,0,'discount_percent');
                if($discount_percent == null || $discount_percent == ''){
                    $discount_percent = 0;
                }

                $tax1 = $adb->query_result($res_line,0,'tax1');
                if($tax1 == null || $tax1 == ''){
                    $tax1 = 0;
                }

                if($discount_percent > 0){
                    $total_notaxes = ($costo * $quantity) * (1 - $discount_percent / 100);
                }
                elseif($discount_amount > 0){
                    $total_notaxes = ($costo * $quantity) - $discount_amount;
                }
                else{
                    $total_notaxes = $costo * $quantity;
                }

                if($tax1 > 0){
                    $linetotal = $total_notaxes + (($total_notaxes*$tax1)/100);
                }
                else{
                    $linetotal = $total_notaxes;
                }

            }
            else{
                $total_notaxes = 0;
                $linetotal = 0;
            }

            $upd_riga = "UPDATE {$table_prefix}_inventoryproductrel SET
                            quantity = ".$quantity.",
                            listprice = ".$costo.",
                            linetotal = ".$linetotal.",
                            discount_percent = ".$per_sconto_acquisto.",
                            total_notaxes = ".$total_notaxes."
                            WHERE lineitem_id = ".$lineitem_id;
            $adb->query($upd_riga);

            $q_ordine = "SELECT COALESCE(SUM(linetotal), 0) sub_total_ordine FROM {$table_prefix}_inventoryproductrel 
                            WHERE id = ".$purchaseorderid;
            $res_ordine = $adb->query($q_ordine);

            if($adb->num_rows($res_ordine)>0){

                $sub_total_ordine = $adb->query_result($res_ordine,0,'sub_total_ordine');

                $upd_po = "UPDATE {$table_prefix}_purchaseorder
                            SET subtotal =".$sub_total_ordine.", total =".$sub_total_ordine.", taxtype = 'individual'
                            WHERE purchaseorderid =".$purchaseorderid;
                $adb->query($upd_po);
            }

        }
        else{
            $nuova_riga = new LineKP();
            $nuova_riga->id = $purchaseorderid;
            $nuova_riga->productid = $servizio;
            $nuova_riga->quantity = $quantity;
            $nuova_riga->listprice = $costo;
            $nuova_riga->discount_percent = $per_sconto_acquisto;
            $nuova_riga->comment = 'Rif. Riga SalesOrder N. '.$sequence_no;
            $nuova_riga->description = '';
            $nuova_riga->relmodule = 'PurchaseOrder';
            $nuova_riga->total_notaxes = $quantity * $costo_servizio;
            $nuova_riga->salva();
        }

    }
	
}	

function generaServizioAContrattoDaSalesOrder($sales_order){
    global $adb, $table_prefix,$current_user;

    /**
     * @author Tomiello Marco
     * @copyright (c) 2015, Kpro Consulting Srl
     * @package fatturazioneConOdf
     * @version 1.0
     * 
     * Questo script genera eventuali Servizi a Contratto da un sales order
     */
	
    $q_sales_order = "SELECT so.accountid accountid,
                        so.data_ordine data_ordine,
                        ent.smownerid assegnatario,
                        acc.accountname accountname
                        FROM {$table_prefix}_salesorder so
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
                        INNER JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
                        WHERE so.salesorderid = ".$sales_order;
    $res_sales_order = $adb->query($q_sales_order); 							

    if($adb->num_rows($res_sales_order)>0){						 
        $accountid = $adb->query_result($res_sales_order, 0, 'accountid'); 
        $accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES,$default_charset);

        $data_ordine = $adb->query_result($res_sales_order, 0, 'data_ordine');
        $data_ordine = html_entity_decode(strip_tags($data_ordine), ENT_QUOTES,$default_charset);
        if($data_ordine == null){
                $data_ordine = "";
        }

        $assegnatario = $adb->query_result($res_sales_order, 0, 'assegnatario');
        $assegnatario = html_entity_decode(strip_tags($assegnatario), ENT_QUOTES,$default_charset);

        $accountname = $adb->query_result($res_sales_order, 0, 'accountname');
        $accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);

    }

    $q_ver_servizi_con_cotratto = "SELECT 
                                    prodrel.total_notaxes totale_linea,
                                    prodrel.quantity quantity,
                                    prodrel.description linedescription,
                                    prodrel.comment linecomment,
                                    prodrel.lineitem_id lineitem_id,
                                    prodrel.sequence_no sequence_no,
                                    serv.serviceid serviceid,
                                    serv.servicename servicename
                                    FROM {$table_prefix}_inventoryproductrel prodrel
                                    INNER JOIN {$table_prefix}_service serv ON serv.serviceid = prodrel.productid
                                    WHERE serv.genera_contratti = '1' AND prodrel.id = ".$sales_order;
    $res_ver_servizi_con_cotratto = $adb->query($q_ver_servizi_con_cotratto);
    $num_servizi_con_cotratto = $adb->num_rows($res_ver_servizi_con_cotratto);
    for($i=0; $i<$num_servizi_con_cotratto; $i++){
        
        $lineitem_id = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'lineitem_id');
        $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES,$default_charset);
        
        $sequence_no = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'sequence_no');
        $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES,$default_charset);
        
        $servizio = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'serviceid');
        $servizio = html_entity_decode(strip_tags($servizio), ENT_QUOTES,$default_charset);

        $totale_linea = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'totale_linea');
        $totale_linea = html_entity_decode(strip_tags($totale_linea), ENT_QUOTES,$default_charset);

        $linedescription = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'linedescription');
        $linedescription = html_entity_decode(strip_tags($linedescription), ENT_QUOTES,$default_charset);

        $linecomment = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'linecomment');
        $linecomment = html_entity_decode(strip_tags($linecomment), ENT_QUOTES,$default_charset);

        $servicename = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'servicename');
        $servicename = html_entity_decode(strip_tags($servicename), ENT_QUOTES,$default_charset);

        $quantity = $adb->query_result($res_ver_servizi_con_cotratto, $i, 'quantity');
        $quantity = html_entity_decode(strip_tags($quantity), ENT_QUOTES,$default_charset);

        $q_ver_servizi_a_contratto = "SELECT servc.servicecontractsid servicecontractsid,
                                        servc.contract_status contract_status
                                        FROM {$table_prefix}_servicecontracts servc
                                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = servc.servicecontractsid
                                        WHERE ent.deleted = 0 AND servc.sorder_id = ".$sales_order." 
                                        AND servc.service_id = ".$servizio;

        $res_ver_servizi_a_contratto = $adb->query($q_ver_servizi_a_contratto);
        if($adb->num_rows($res_ver_servizi_a_contratto)>0){	
            $servicecontractsid = $adb->query_result($res_ver_servizi_a_contratto, 0, 'servicecontractsid');
            $servicecontractsid = html_entity_decode(strip_tags($servicecontractsid), ENT_QUOTES,$default_charset);	

            $contract_status = $adb->query_result($res_ver_servizi_a_contratto, 0, 'contract_status');
            $contract_status = html_entity_decode(strip_tags($contract_status), ENT_QUOTES,$default_charset);

            if($contract_status == "Autocreato"){

                $q_upd_servizio_a_contratto = "UPDATE {$table_prefix}_servicecontracts SET
                                                subject = '".$servicename."',
                                                sc_related_to = ".$accountid.",
                                                service_id = ".$servizio.",
                                                tracking_unit = 'Hours',
                                                total_units = ".$quantity.",
                                                used_units = 0,
                                                residual_units = ".$quantity.",
                                                start_date = '".$data_ordine."',
                                                description = '".$linedescription."'
                                                WHERE servicecontractsid = ".$servicecontractsid;
                $res_upd_servizio_a_contratto = $adb->query($q_upd_servizio_a_contratto);

            }

        }
        else{

            $new_servizio_a_contratto = CRMEntity::getInstance('ServiceContracts'); 
            $new_servizio_a_contratto->column_fields['assigned_user_id'] = $assegnatario;
            $new_servizio_a_contratto->column_fields['subject'] = $servicename;
            $new_servizio_a_contratto->column_fields['sc_related_to'] = $accountid;
            $new_servizio_a_contratto->column_fields['description'] = $linedescription;
            $new_servizio_a_contratto->column_fields['tracking_unit'] = 'Hours';
            $new_servizio_a_contratto->column_fields['total_units'] = $quantity;
            $new_servizio_a_contratto->column_fields['used_units'] = 0;
            $new_servizio_a_contratto->column_fields['residual_units'] = $quantity;
            $new_servizio_a_contratto->column_fields['start_date'] = $data_ordine;
            $new_servizio_a_contratto->column_fields['contract_status'] = 'Autocreato';
            $new_servizio_a_contratto->column_fields['service_id'] = $servizio;
            $new_servizio_a_contratto->column_fields['sorder_id'] = $sales_order;
            $new_servizio_a_contratto->save('ServiceContracts', $longdesc=true, $offline_update=false, $triggerEvent=false); 

        }

    }

}	
	
?>