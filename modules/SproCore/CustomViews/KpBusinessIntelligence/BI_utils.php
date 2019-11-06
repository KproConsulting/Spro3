<?php

function LogBI($log, $mode="a+", $filename="log"){

    $log_content = "
".$log;
    $log_file = fopen(__DIR__."/logs/".$filename.".txt", $mode);
    fwrite($log_file, $log_content);
    fclose($log_file);

}

function PrintArray($array){

    $string = "";

    foreach($array as $key => $value){
        
        if(is_array($value)){
            $string .= "
 '".$key."' => ";
            foreach($value as $el){
                $string .= "'".$el."', ";
            }
        }
        else{
            $string .= "
 '".$key."' => '".$value."'";
        }

    }

    return $string;
}

function getListaPicklist($nome_campo, $modulo, $lingua, $valore){
    global $adb, $table_prefix, $default_charset;

    $lista_picklist = array();

    $q_picking = "SELECT pk.".$nome_campo." AS value,
                (CASE WHEN sdk.trans_label IS NOT NULL
                THEN sdk.trans_label
                ELSE pk.".$nome_campo."
                END) AS value_trad
                FROM {$table_prefix}_".$nome_campo." pk
                LEFT JOIN sdk_language sdk ON sdk.label = pk.".$nome_campo."
                    AND sdk.module = '".$modulo."' AND sdk.language = '".$lingua."'";
    if($valore != '--Tutti--'){
        $q_picking .= " WHERE pk.".$nome_campo." = '".$valore."'";
    }
    $q_picking .= " GROUP BY value
                ORDER BY value";

    $res_picking = $adb->query($q_picking);
    $num_picking = $adb->num_rows($res_picking);

    for($i=0; $i<$num_picking; $i++){
        
        $valore = $adb->query_result($res_picking, $i, 'value');
        $valore = html_entity_decode(strip_tags($valore), ENT_QUOTES,$default_charset);

        $traduzione_valore = $adb->query_result($res_picking, $i, 'value_trad');
        $traduzione_valore = html_entity_decode(strip_tags($traduzione_valore), ENT_QUOTES,$default_charset);

        $lista_picklist[] = array(
            'valore' => $valore,
            'traduzione_valore' => $traduzione_valore
        );

    }

    return $lista_picklist;

}

function getListaPicklistMultilinguaggio($nome_campo, $valore, $lingua){
    global $adb, $table_prefix, $default_charset;

    $lista_picklist = array();

    $q_picking = "SELECT code,
                value
                FROM tbl_s_picklist_language 
                WHERE field = '{$nome_campo}' 
                AND language = '".$lingua."'";
    if($valore != '--Tutti--' && $valore != '' && $valore != null){
        $q_picking .= " AND code LIKE '{$valore}'";
    }
    $q_picking .= " ORDER BY value";
    $res_picking = $adb->query($q_picking);
    $num_picking = $adb->num_rows($res_picking);
    for($i = 0; $i < $num_picking; $i++){

        $codice_valore = $adb->query_result($res_picking, $i, 'code');
        $codice_valore = html_entity_decode(strip_tags($codice_valore), ENT_QUOTES,$default_charset);

        $nome_valore = $adb->query_result($res_picking, $i, 'value');
        $nome_valore = html_entity_decode(strip_tags($nome_valore), ENT_QUOTES,$default_charset);

        $lista_picklist[] = array(
            'codice' => $codice_valore,
            'nome' => $nome_valore
        );
    }

    return $lista_picklist;
}

function InArrayQuery($array){
    $query = "";
    for($i = 0; $i < count($array); $i++){
        if($i == 0){
            $query .= "".$array[$i]."";
        }
        else{
            $query .= ",".$array[$i]."";
        }
    }
    return $query;
}

function GetDescrizioneMese($numero_mese, $lingua){
    if($lingua == 'it_it'){
        switch($numero_mese){
            case 1: $descrizione_mese = "Gennaio"; break;
            case 2: $descrizione_mese = "Febbraio"; break;
            case 3: $descrizione_mese = "Marzo"; break;
            case 4: $descrizione_mese = "Aprile"; break;
            case 5: $descrizione_mese = "Maggio"; break;
            case 6: $descrizione_mese = "Giugno"; break;
            case 7: $descrizione_mese = "Luglio"; break;
            case 8: $descrizione_mese = "Agosto"; break;
            case 9: $descrizione_mese = "Settembre";break;
            case 10: $descrizione_mese = "Ottobre"; break;
            case 11: $descrizione_mese = "Novembre"; break;
            case 12: $descrizione_mese = "Dicembre"; break;
            default: $descrizione_mese = "";
        }
    }
    else{
        switch($numero_mese){
            case 1: $descrizione_mese = "January"; break;
            case 2: $descrizione_mese = "February"; break;
            case 3: $descrizione_mese = "March"; break;
            case 4: $descrizione_mese = "April"; break;
            case 5: $descrizione_mese = "May"; break;
            case 6: $descrizione_mese = "June"; break;
            case 7: $descrizione_mese = "July"; break;
            case 8: $descrizione_mese = "August"; break;
            case 9: $descrizione_mese = "September";break;
            case 10: $descrizione_mese = "October"; break;
            case 11: $descrizione_mese = "November"; break;
            case 12: $descrizione_mese = "December"; break;
            default: $descrizione_mese = "";
        }
    }

    return $descrizione_mese;
}

function GetValorePicklistMultilinguaggio($nome_campo, $codice, $lingua){
    global $adb, $table_prefix, $default_charset;

    if($codice != '--Tutti--'){
        $nome_valore = "";    
        $q_picking = "SELECT value
                    FROM tbl_s_picklist_language 
                    WHERE field = '{$nome_campo}' 
                    AND language = '".$lingua."'
                    AND code = '".$codice."'";
        $res_picking = $adb->query($q_picking);
        if($adb->num_rows($res_picking) > 0){

            $nome_valore = $adb->query_result($res_picking, 0, 'value');
            $nome_valore = html_entity_decode(strip_tags($nome_valore), ENT_QUOTES,$default_charset);
            if($nome_valore == null){
                $nome_valore = "";
            }
        }
    }
    else{
        $nome_valore = $codice;
    }

    return $nome_valore;
}

function GetValorePicklist($nome_campo, $modulo, $lingua, $valore){
    global $adb, $table_prefix, $default_charset;

    if($valore != '--Tutti--'){
        $traduzione_valore = '';
        $q_picking = "SELECT pk.".$nome_campo." AS value,
                    (CASE WHEN sdk.trans_label IS NOT NULL
                    THEN sdk.trans_label
                    ELSE pk.".$nome_campo."
                    END) AS value_trad
                    FROM {$table_prefix}_".$nome_campo." pk
                    LEFT JOIN sdk_language sdk ON sdk.label = pk.".$nome_campo."
                        AND sdk.module = '".$modulo."' AND sdk.language = '".$lingua."'
                    WHERE pk.".$nome_campo." = '".$valore."'";

        $res_picking = $adb->query($q_picking);
        if($adb->num_rows($res_picking) > 0){        
            $traduzione_valore = $adb->query_result($res_picking, 0, 'value_trad');
            $traduzione_valore = html_entity_decode(strip_tags($traduzione_valore), ENT_QUOTES,$default_charset);
            if($traduzione_valore == null){
                $traduzione_valore = "";
            }

        }
    }
    else{
        $traduzione_valore = '-';
    }

    return $traduzione_valore;
}

function GetNomeUtente($utente){
    global $adb, $table_prefix, $default_charset;

    if($utente != 'Tutti'){
        $nome_utente = "";

        $q = "SELECT user_name
                    FROM {$table_prefix}_users
                    WHERE id = ".$utente;
        $res = $adb->query($q);
        if($adb->num_rows($res) > 0){

            $nome_utente = $adb->query_result($res, 0, 'user_name');
            $nome_utente = html_entity_decode(strip_tags($nome_utente), ENT_QUOTES,$default_charset);
            if($nome_utente == null){
                $nome_utente = "";
            }
        }
    }
    else{
        $nome_utente = $utente;
    }

    return $nome_utente;
}

function GetNomiClienti($clienti){
    global $adb, $table_prefix, $default_charset;

    $nomi_clienti = "";

    if(!empty($clienti)){

        for($i = 0; $i < count($clienti); $i++){
            $nome_cliente = "";
            $q = "SELECT accountname
                FROM {$table_prefix}_account
                WHERE accountid = ".$clienti[$i];
            $res = $adb->query($q);
            if($adb->num_rows($res) > 0){
                $nome_cliente = $adb->query_result($res, 0, 'accountname');
                $nome_cliente = html_entity_decode(strip_tags($nome_cliente), ENT_QUOTES,$default_charset);
                if($nome_cliente == null){
                    $nome_cliente = "";
                }
            }
            if($nome_cliente != ""){
                if($i == 0){
                    $nomi_clienti .= $nome_cliente;
                }
                else{
                    $nomi_clienti .= ",".$nome_cliente;
                }
            }
        }
    }

    return $nomi_clienti;
}

function GetNomiServizi($servizi){
    global $adb, $table_prefix, $default_charset;

    $nomi_servizi = "";

    if(!empty($servizi)){

        for($i = 0; $i < count($servizi); $i++){
            $nome_servizio = "";
            $q = "SELECT servicename
                FROM {$table_prefix}_service
                WHERE serviceid = ".$servizi[$i];
            $res = $adb->query($q);
            if($adb->num_rows($res) > 0){
                $nome_servizio = $adb->query_result($res, 0, 'servicename');
                $nome_servizio = html_entity_decode(strip_tags($nome_servizio), ENT_QUOTES,$default_charset);
                if($nome_servizio == null){
                    $nome_servizio = "";
                }
            }
            if($nome_servizio != ""){
                if($i == 0){
                    $nomi_servizi .= $nome_servizio;
                }
                else{
                    $nomi_servizi .= ",".$nome_servizio;
                }
            }
        }
    }

    return $nomi_servizi;
}

function GetNomiBusinessUnit($business_unit){
    global $adb, $table_prefix, $default_charset;

    $nomi_business_unit = "";

    if(!empty($business_unit)){

        for($i = 0; $i < count($business_unit); $i++){
            $nome_business_unit = "";
            $q = "SELECT kp_nome_business_un
                FROM {$table_prefix}_kpbusinessunit
                WHERE kpbusinessunitid = ".$business_unit[$i];
            $res = $adb->query($q);
            if($adb->num_rows($res) > 0){
                $nome_business_unit = $adb->query_result($res, 0, 'kp_nome_business_un');
                $nome_business_unit = html_entity_decode(strip_tags($nome_business_unit), ENT_QUOTES,$default_charset);
                if($nome_business_unit == null){
                    $nome_business_unit = "";
                }
            }
            if($nome_business_unit != ""){
                if($i == 0){
                    $nomi_business_unit .= $nome_business_unit;
                }
                else{
                    $nomi_business_unit .= ",".$nome_business_unit;
                }
            }
        }
    }

    return $nomi_business_unit;
}

function GetNomiAgenti($agenti){
    global $adb, $table_prefix, $default_charset;

    $nomi_agenti = "";

    if(!empty($agenti)){

        for($i = 0; $i < count($agenti); $i++){
            $nome_agente = "";
            $q = "SELECT kp_nome_agente
                FROM {$table_prefix}_kpagenti
                WHERE kpagentiid = ".$agenti[$i];
            $res = $adb->query($q);
            if($adb->num_rows($res) > 0){
                $nome_agente = $adb->query_result($res, 0, 'kp_nome_agente');
                $nome_agente = html_entity_decode(strip_tags($nome_agente), ENT_QUOTES,$default_charset);
                if($nome_agente == null){
                    $nome_agente = "";
                }
            }
            if($nome_agente != ""){
                if($i == 0){
                    $nomi_agenti .= $nome_agente;
                }
                else{
                    $nomi_agenti .= ",".$nome_agente;
                }
            }
        }
    }

    return $nomi_agenti;
}

function GetNomiTipologieFatturato($tipologie_fatturato){

    $nomi_tipologie_fatturato = '';

    for($i = 0; $i < count($tipologie_fatturato); $i++){
        if($nomi_tipologie_fatturato != ''){
            $nomi_tipologie_fatturato .= ',';
        }
        $nomi_tipologie_fatturato .= $tipologie_fatturato[$i];
    }

    return $nomi_tipologie_fatturato;
}

function GetDatiValuta($id_valuta){
    global $adb, $table_prefix, $default_charset;

    $dati_valuta = array();
    
    $q = "SELECT currency_name,
        currency_code,
        currency_symbol,
        conversion_rate
        FROM {$table_prefix}_currency_info
        WHERE id = ".$id_valuta;
    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $nome_valuta = $adb->query_result($res, 0, 'currency_name');
        $nome_valuta = html_entity_decode(strip_tags($nome_valuta), ENT_QUOTES,$default_charset);

        $codice_valuta = $adb->query_result($res, 0, 'currency_code');
        $codice_valuta = html_entity_decode(strip_tags($codice_valuta), ENT_QUOTES,$default_charset);

        $simbolo_valuta = $adb->query_result($res, 0, 'currency_symbol');
        $simbolo_valuta = html_entity_decode(strip_tags($simbolo_valuta), ENT_QUOTES,$default_charset);

        $tasso_conversione = $adb->query_result($res, 0, 'conversion_rate');
        $tasso_conversione = html_entity_decode(strip_tags($tasso_conversione), ENT_QUOTES,$default_charset);
    }
    else{
        $nome_valuta = '';
        $codice_valuta = '';
        $simbolo_valuta = '';
        $tasso_conversione = 0;
    }

    $dati_valuta = array(
        'nome' => $nome_valuta,
        'codice' => $codice_valuta,
        'simbolo' => $simbolo_valuta,
        'tasso' => $tasso_conversione
    );

    return $dati_valuta;
}

function getListaServizi($servizi, $area_aziendale, $categoria, $codice, $nome, $ordinamento, $lingua, $numero_record = '', $anno = '', $mese_da = '', $mese_a = ''){ /* kpro@bid040120191730 */
    global $adb, $table_prefix, $default_charset;

    $lista_servizi = array();

    $q = "SELECT ser.serviceid AS id,
        ser.service_no AS codice,
        ser.servicename AS nome,
        ser.area_aziendale AS area_aziendale,
        ser.servicecategory AS categoria
        FROM {$table_prefix}_service ser
        INNER JOIN {$table_prefix}_odf odf ON odf.servizio = ser.serviceid
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = ser.serviceid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = odf.odfid
        WHERE ent.deleted = 0 AND ent1.deleted = 0";
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '%".$area_aziendale."%'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '%".$categoria."%'";
    }
    if($codice != null && $codice != '' && $codice != '--Tutti--'){
        $q .= " AND ser.service_no LIKE '%".$codice."%'";
    }
    if($nome != null && $nome != '' && $nome != '--Tutti--'){
        $q .= " AND ser.servicename LIKE '%".$nome."%'";
    }

    if( $anno != '' ){
        $q .= " AND YEAR(odf.kp_data_fattura) = ".$anno;
    }

    if( $mese_da != '' ){
        $q .= " AND MONTH(odf.kp_data_fattura) >= ".$mese_da;
    }

    if( $mese_a != '' ){
        $q .= " AND MONTH(odf.kp_data_fattura) <= ".$mese_a;
    }

    /* kpro@bid040120191730 */
    $q .= " GROUP BY ser.serviceid
        ORDER BY COUNT(*) ".$ordinamento;

    if($numero_record != ''){
        $q .= " LIMIT ".$numero_record;
    }
    /* kpro@bid040120191730 end */

    //print_r($q);die;

    $res = $adb->query($q);
    $num = $adb->num_rows($res);

    for($i=0; $i<$num; $i++){
        
        $id = $adb->query_result($res, $i, 'id');
        $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

        $codice = $adb->query_result($res, $i, 'codice');
        $codice = html_entity_decode(strip_tags($codice), ENT_QUOTES,$default_charset);

        $nome = $adb->query_result($res, $i, 'nome');
        $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

        $area_aziendale = $adb->query_result($res, $i, 'area_aziendale');
        $area_aziendale = html_entity_decode(strip_tags($area_aziendale), ENT_QUOTES,$default_charset);

        $trad_area_aziendale = getListaPicklist('area_aziendale', 'Services', $lingua, $area_aziendale);

        $categoria = $adb->query_result($res, $i, 'categoria');
        $categoria = html_entity_decode(strip_tags($categoria), ENT_QUOTES,$default_charset);

        $trad_categoria = getListaPicklist('servicecategory', 'Services', $lingua, $categoria);

        $lista_servizi[] = array(
            'id' => $id,
            'codice' => $codice,
            'nome' => $nome,
            'area_aziendale' => $trad_area_aziendale[0]['traduzione_valore'],
            'categoria' => $trad_categoria[0]['traduzione_valore']
        );

    }

    return $lista_servizi;
}

function GetFatturato($anno, $numero_mese, $array_dati){
    global $debug;

    $tipologie_fatturato = $array_dati['tipologie_fatturato'];

    $fatturato = 0;

    //$fatturato_fatture = GetFatturatoFatture($anno, $numero_mese, 'Fattura', $array_dati);

    //$note_di_credito = GetFatturatoFatture($anno, $numero_mese, 'Nota di credito', $array_dati);

    //$fatturato += $fatturato_fatture - $note_di_credito;

    if($debug){
        LogBI('ANNO: '.$anno.' - MESE: '.$numero_mese);
    }
    
    if (in_array("report_attivita", $tipologie_fatturato)){

        if($debug){
            LogBI('- Report Attività ');
        }

        $fatturato_report_attivita = GetFatturatoReportAttivita($anno, $numero_mese, $array_dati);

        $fatturato += $fatturato_report_attivita;
    }
    
    if (in_array("ticket", $tipologie_fatturato)){

        if($debug){
            LogBI('- Ticket ');
        }

        $fatturato_ticket = GetFatturatoTicket($anno, $numero_mese, $array_dati);

        $fatturato += $fatturato_ticket;
    }
    
    if (in_array("canone", $tipologie_fatturato)){

        if($debug){
            LogBI('- Canoni ');
        }

        $fatturato_canoni = GetFatturatoCanoni($anno, $numero_mese, $array_dati);

        $fatturato += $fatturato_canoni;
    }

    if (in_array("formazione", $tipologie_fatturato)){

        if($debug){
            LogBI('- Formazione ');
        }

        $fatturato_formazione = GetFatturatoFormazione($anno, $numero_mese, $array_dati);

        $fatturato += $fatturato_formazione;
    }
    
    if (in_array("ordini_di_vendita", $tipologie_fatturato)){

        if($debug){
            LogBI('- Ordini di vendita ');
        }

        $fatturato_ordini = GetFatturatoOrdini($anno, $numero_mese, $array_dati);

        $fatturato += $fatturato_ordini;
    }

    if (in_array("nota_di_credito", $tipologie_fatturato)){

        if($debug){
            LogBI('- Nota di credito ');
        }

        $fatturato_note_di_credito = GetFatturatoNoteDiCredito($anno, $numero_mese, $array_dati);

        $fatturato -= $fatturato_note_di_credito;
    }

    if (in_array("fattura_di_acconto", $tipologie_fatturato)){

        if($debug){
            LogBI('- Fattura di acconto ');
        }

        $fatturato_fatture_di_acconto = GetFatturatoFattureDiAcconto($anno, $numero_mese, $array_dati);

        $fatturato += $fatturato_fatture_di_acconto;
    }

    if($debug){
        LogBI('- TOT: '.$fatturato);
    }

    return $fatturato;
}

/*function GetFatturatoFatture($anno, $numero_mese, $tipo_documento, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    if($quantita_valore == 'valore'){
        $q = "SELECT SUM((righe.total_notaxes * cur.conversion_rate)/{$tasso_valuta}) AS fatturato";
    }
    else{
        $q = "SELECT SUM(righe.quantity) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_invoice inv
        INNER JOIN {$table_prefix}_currency_info cur ON cur.id = inv.currency_id
        INNER JOIN {$table_prefix}_inventoryproductrel righe ON righe.id = inv.invoiceid
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = righe.productid
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = inv.accountid
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = inv.kp_business_unit
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = inv.invoiceid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = ser.serviceid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 
        AND inv.kp_tipo_documento = '{$tipo_documento}'
        AND YEAR(inv.invoicedate) = {$anno}
        AND MONTH(inv.invoicedate) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $fatturato = $adb->query_result($res, 0, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }
    }
    else{
        $fatturato = 0;
    }

    return $fatturato;
}*/

function GetFatturatoReportAttivita($anno, $numero_mese, $array_dati){
    global $debug;

    $fatturato = 0;

    $fatturato_odf = GetOdfReportAttivita($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetOdfReportAttivita: '.$fatturato_odf);
    }

    $fatturato += $fatturato_odf;

    $fatturato_previsione = GetPrevisioneReportAttivita($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetPrevisioneReportAttivita: '.$fatturato_previsione);
    }

    $fatturato += $fatturato_previsione;

    return $fatturato;
}

function GetOdfReportAttivita($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT odf.qta_fatturata AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_visitreport vr ON vr.visitreportid = odf.related_to
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = vr.visitreportid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Report Attivita','Nota Spesa')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }        
    }
    if($business_unit != null && !empty($business_unit)){
        $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetPrevisioneReportAttivita($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato = 0;

    $q = "SELECT vr.kp_ore_fatturate AS ore_confermate,
        acc.kp_listino AS listino_azienda,
        ser.serviceid AS servizio
        FROM {$table_prefix}_visitreport vr
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = vr.kp_servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = vr.accountid
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = vr.kp_business_unit
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = vr.visitreportid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0
        AND vr.kp_stato_attivita = 'Chiuso'
        AND YEAR(vr.visitdate) = {$anno} AND MONTH(vr.visitdate) = {$numero_mese}
        AND vr.kp_da_fatturare = '1'";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $ore_confermate = $adb->query_result($res, $i, 'ore_confermate');
        if($ore_confermate == null){
            $ore_confermate = 0;
        }

        if($quantita_valore == 'valore'){
            $listino_azienda = $adb->query_result($res, $i, 'listino_azienda');
            if($listino_azienda == null){
                $listino_azienda = 0;
            }

            $servizio = $adb->query_result($res, $i, 'servizio');
            if($servizio == null){
                $servizio = 0;
            }

            $prezzo_listino = recuperaPrezzoServizio($listino_azienda, $servizio);
            
            $fatturato += $ore_confermate * $prezzo_listino;
        }
        else{
            $fatturato += $ore_confermate;
        }
    }
    
    return $fatturato;
}

function GetFatturatoTicket($anno, $numero_mese, $array_dati){
    global $debug;

    $fatturato = 0;

    $fatturato_odf = GetOdfTicket($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetOdfTicket: '.$fatturato_odf);
    }

    $fatturato += $fatturato_odf;

    $fatturato_previsione = GetPrevisioneTicket($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetPrevisioneTicket: '.$fatturato_previsione);
    }

    $fatturato += $fatturato_previsione;

    return $fatturato;

}

function GetOdfTicket($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT SUM(odf.qta_fatturata) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_troubletickets tick ON tick.ticketid = odf.related_to
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = tick.ticketid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Ticket')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetPrevisioneTicket($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato = 0;
    //Considero i ticket chiusi con data esecuzione nel mese e anno che mi interessano e i ticket non ancora chiusi ma che hanno una data consegna
    $q = "SELECT tick.total_notaxes as prezzo,
        tick.status as stato,
        tick.kp_tempo_previsto as tempo_previsto,
        tick.hours as tempo_effettuato
        FROM {$table_prefix}_troubletickets tick
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = tick.parent_id
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = tick.servizio
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = tick.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = tick.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = tick.ticketid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0
        AND tick.da_fatturare = '1' 
        AND ((tick.status = 'Closed' AND YEAR(tick.data_esecuzione) = {$anno} AND MONTH(tick.data_esecuzione) = {$numero_mese})
        OR (tick.status NOT IN ('Closed','Emesso OdF') AND YEAR(tick.kp_data_consegna) = {$anno} AND MONTH(tick.kp_data_consegna) = {$numero_mese}))";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        if($quantita_valore == 'valore'){
            $prezzo = $adb->query_result($res, $i, 'prezzo');
            $prezzo = html_entity_decode(strip_tags($prezzo), ENT_QUOTES,$default_charset);
            if($prezzo == '' || $prezzo == null){
                $prezzo = 0;
            }
            
            $fatturato += $prezzo;
        }
        else{
            $stato = $adb->query_result($res, $i, 'stato');
            $stato = html_entity_decode(strip_tags($stato), ENT_QUOTES,$default_charset);
            if($stato == null){
                $stato = '';
            }

            $tempo_previsto = $adb->query_result($res, $i, 'tempo_previsto');
            $tempo_previsto = html_entity_decode(strip_tags($tempo_previsto), ENT_QUOTES,$default_charset);
            if($tempo_previsto == '' || $tempo_previsto == null){
                $tempo_previsto = 0;
            }

            $tempo_effettuato = $adb->query_result($res, $i, 'tempo_effettuato');
            $tempo_effettuato = html_entity_decode(strip_tags($tempo_effettuato), ENT_QUOTES,$default_charset);
            if($tempo_effettuato == '' || $tempo_effettuato == null){
                $tempo_effettuato = 0;
            }

            if($stato == 'Closed'){
                $fatturato += $tempo_effettuato;
            }
            else{
                $fatturato += $tempo_previsto;
            }
        }
    }
    
    return $fatturato;
}

function GetFatturatoCanoni($anno, $numero_mese, $array_dati){
    global $debug;

    $fatturato = 0;

    $fatturato_odf = GetOdfCanoni($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetOdfCanoni: '.$fatturato_odf);
    }

    $fatturato += $fatturato_odf;

    $fatturato_previsione = GetPrevisioneCanoni($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetPrevisioneCanoni: '.$fatturato_previsione);
    }

    $fatturato += $fatturato_previsione;

    return $fatturato;

}

function GetOdfCanoni($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT SUM(odf.qta_fatturata) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_canoni can ON can.canoniid = odf.related_to
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = can.canoniid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Canone')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetPrevisioneCanoni($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    require_once('modules/SproCore/SproUtils/spro_utils.php');

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato = 0;

    $data_analisi = $anno.'-'.str_pad($numero_mese, 2, '0', STR_PAD_LEFT).'-01';
    //Considero i canoni attivi con l'anno e mese compresi fra la data inizio canone e la data fine canone
    $q = "SELECT can.kp_anno_fatt,
        can.mese_fatturazione,
        can.frequenza_fatturazione,
        can.data_fine,
        can.prezzo
        FROM {$table_prefix}_canoni can
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = can.account
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = can.servizio
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = can.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = can.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = can.canoniid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0
        AND can.stato_canone = 'Attivo'
        AND (YEAR(can.data_inizio) < {$anno}
        OR (YEAR(can.data_inizio) = {$anno}
        AND MONTH(can.data_inizio) <= {$numero_mese}))
        AND ((can.data_fine IS NULL OR can.data_fine = '')
        OR ((can.data_fine IS NOT NULL AND can.data_fine <> '' 
        AND (YEAR(can.data_fine) > {$anno}
        OR (YEAR(can.data_fine) = {$anno}
        AND MONTH(can.data_fine) >= {$numero_mese})))))";

    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }


    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $anno_fatturazione = $adb->query_result($res, $i, 'kp_anno_fatt');
        $anno_fatturazione = html_entity_decode(strip_tags($anno_fatturazione), ENT_QUOTES, $default_charset);
        if($anno_fatturazione == null){
            $anno_fatturazione = "";
        }

        $mese_fatturazione = $adb->query_result($res, $i, 'mese_fatturazione');
        $mese_fatturazione = html_entity_decode(strip_tags($mese_fatturazione), ENT_QUOTES, $default_charset);
        if($mese_fatturazione == null){
            $mese_fatturazione = "";
        }
        $mese_fatturazione_trim = ltrim($mese_fatturazione,0);

        if($mese_fatturazione != "" && $anno_fatturazione != ""){

            $frequenza_fatturazione = $adb->query_result($res, $i, 'frequenza_fatturazione');
            $frequenza_fatturazione = html_entity_decode(strip_tags($frequenza_fatturazione), ENT_QUOTES, $default_charset);

            $data_fine = $adb->query_result($res, $i, 'data_fine');
            $data_fine = html_entity_decode(strip_tags($data_fine), ENT_QUOTES, $default_charset);
            if($data_fine == null || $data_fine == "0000-00-00"){
                $data_fine = "";
            }

            $prezzo = $adb->query_result($res, $i, 'prezzo');
            if($prezzo == null){
                $prezzo = 0;
            }

            $trovato = false;
            $numeroMesiIncremento = calcolaNumeroMesiIncremento($frequenza_fatturazione);
            $data_fatturazione_temp = $anno_fatturazione."-".str_pad($mese_fatturazione, 2, '0', STR_PAD_LEFT)."-01";
            
            while(!$trovato && $data_fatturazione_temp <= $data_analisi
                && ($data_fine == '' || ($data_fine != '' && $data_fatturazione_temp <= $data_fine))){

                $data_temp_dt = new DateTime($data_fatturazione_temp);
                $anno_fatturazione = $data_temp_dt->format("Y");
                $mese_fatturazione = ltrim($data_temp_dt->format("m"),0);

                if($anno == $anno_fatturazione && $numero_mese == $mese_fatturazione_trim){
                    $trovato = true;
                }

                $data_fatturazione_temp = date_create($data_fatturazione_temp);
                date_add($data_fatturazione_temp,date_interval_create_from_date_string($numeroMesiIncremento." months"));
                $data_fatturazione_temp = date_format($data_fatturazione_temp,"Y-m-d");
                
            }

            if($trovato){
                if($quantita_valore == 'valore'){
                    $fatturato += $prezzo;
                }
                else{
                    $fatturato++;
                }
            }

        }
        
    }
    
    return $fatturato;
}

function GetFatturatoFormazione($anno, $numero_mese, $array_dati){
    global $debug;

    $fatturato = 0;

    $fatturato_odf = GetOdfFormazione($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetOdfFormazione: '.$fatturato_odf);
    }

    $fatturato += $fatturato_odf;

    $fatturato_previsione = GetPrevisioneFormazione($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetPrevisioneFormazione: '.$fatturato_previsione);
    }

    $fatturato += $fatturato_previsione;

    return $fatturato;

}

function GetOdfFormazione($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT SUM(odf.qta_fatturata) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_kppartecipformaz part ON part.kppartecipformazid = odf.related_to
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = part.kppartecipformazid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Formazione')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetPrevisioneFormazione($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    if($quantita_valore == 'valore'){
        $q = "SELECT SUM(IFNULL(part.kp_costo_formazione,0)-IFNULL(part.kp_anticipo,0)) AS fatturato";
    }
    else{
        $q = "SELECT COUNT(*) as fatturato";
    }    
    $q .= " FROM {$table_prefix}_kppartecipformaz part
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = part.kp_azienda
        INNER JOIN {$table_prefix}_tipicorso tc ON tc.tipicorsoid = part.kp_tipo_corso
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = tc.kp_servizio
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = acc.kp_business_unit
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = part.kppartecipformazid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = tc.tipicorsoid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND part.kp_stato_fat_partec IN ('Da fatturare') 
        AND YEAR(part.kp_data_formazione) = {$anno} AND MONTH(part.kp_data_formazione) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $fatturato = $adb->query_result($res, 0, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }
    }
    else{
        $fatturato = 0;
    }
    
    return $fatturato;
}

function GetFatturatoOrdini($anno, $numero_mese, $array_dati){
    global $debug;

    $fatturato = 0;

    $fatturato_odf = GetOdfOrdini($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetOdfOrdini: '.$fatturato_odf);
    }

    $fatturato += $fatturato_odf;

    $fatturato_residuo = GetResiduoOrdini($anno, $numero_mese, $array_dati);

    if($debug){
        LogBI('--- GetResiduoOrdini: '.$fatturato_residuo);
    }

    $fatturato += $fatturato_residuo;

    return $fatturato;

}

function GetOdfOrdini($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT SUM(odf.qta_fatturata) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_kpsalesorderline righe ON righe.kpsalesorderlineid = odf.related_to
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = righe.kpsalesorderlineid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Ordini di Vendita')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetResiduoOrdini($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    if($quantita_valore == 'valore'){
        $q = "SELECT SUM((righe.kp_valore_da_fat * cur.conversion_rate)/{$tasso_valuta}) AS fatturato";
    }
    else{
        $q = "SELECT SUM(righe.kp_quantita_ordine) AS fatturato";
    }

    $q .= " FROM {$table_prefix}_salesorder so
        INNER JOIN {$table_prefix}_currency_info cur ON cur.id = so.currency_id
        INNER JOIN {$table_prefix}_kpsalesorderline righe ON righe.kp_salesorder = so.salesorderid
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = righe.kp_prodotto
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = so.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = so.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = ser.serviceid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = righe.kpsalesorderlineid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND so.kp_anno_fatt = {$anno} AND so.mese_fatturazione = {$numero_mese}
        AND so.kp_tipologia_ordine IN ('A progetto','A progetto con canone')";
    if($quantita_valore == 'valore'){
        $q .= " AND so.sostatus IN ('Approved','Delivered','Emessa fattura acconto') ";
    }
    else{
        $q .= " AND so.sostatus IN ('Approved','Delivered') ";
    }
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $fatturato = $adb->query_result($res, 0, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }
    }
    else{
        $fatturato = 0;
    }

    return $fatturato;
}

function GetFatturatoNoteDiCredito($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT SUM(odf.qta_fatturata) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_invoice inv ON inv.invoiceid = odf.fattura
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = inv.invoiceid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Nota di credito')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetFatturatoFattureDiAcconto($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    $fatturato_tot = 0;

    if($quantita_valore == 'valore'){
        $q = "SELECT (odf.total_notaxes * 1)/{$tasso_valuta} AS fatturato,
            odf.discount_amount AS sconto_diretto,
            odf.discount_percent AS sconto_percentuale";
    }
    else{
        $q = "SELECT SUM(odf.qta_fatturata) AS fatturato";
    }
    $q .= " FROM {$table_prefix}_odf odf
        INNER JOIN {$table_prefix}_invoice inv ON inv.invoiceid = odf.fattura
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = odf.servizio
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = odf.cliente_fatt
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = odf.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = odf.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = odf.odfid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = inv.invoiceid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = ser.serviceid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0 AND ent3.deleted = 0
        AND odf.tipo_odf IN ('Fattura di acconto')
        AND YEAR(odf.kp_data_fattura) = {$anno} AND MONTH(odf.kp_data_fattura) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    for($i = 0; $i < $num; $i++){
        $fatturato = $adb->query_result($res, $i, 'fatturato');
        $fatturato = html_entity_decode(strip_tags($fatturato), ENT_QUOTES,$default_charset);
        if($fatturato == "" || $fatturato == null){
            $fatturato = 0;
        }

        if($quantita_valore == 'valore'){
            $sconto_diretto = $adb->query_result($res, $i, 'sconto_diretto');
            $sconto_diretto = html_entity_decode(strip_tags($sconto_diretto), ENT_QUOTES,$default_charset);
            if($sconto_diretto == "" || $sconto_diretto == null){
                $sconto_diretto = 0;
            }

            $sconto_percentuale = $adb->query_result($res, $i, 'sconto_percentuale');
            $sconto_percentuale = html_entity_decode(strip_tags($sconto_percentuale), ENT_QUOTES,$default_charset);
            if($sconto_percentuale == "" || $sconto_percentuale == null){
                $sconto_percentuale = 0;
            }

            if($sconto_percentuale != 0){
                $fatturato = $fatturato - ($fatturato * ($sconto_percentuale / 100));
            }
            else if($sconto_diretto != 0){
                $fatturato = $fatturato - $sconto_diretto;
            }
        }

        $fatturato_tot += $fatturato;
    }
    
    return $fatturato_tot;
}

function GetOrdinato($anno, $numero_mese, $array_dati){
    global $adb, $table_prefix, $default_charset, $debug;

    $quantita_valore = $array_dati['quantita_valore'];
    $tasso_valuta = $array_dati['tasso_valuta'];
    $utenti = $array_dati['utenti'];
    $clienti = $array_dati['clienti'];
    $servizi = $array_dati['servizi'];
    $business_unit = $array_dati['business_unit'];
    $agenti = $array_dati['agenti'];
    $area_aziendale = $array_dati['area_aziendale'];
    $categoria = $array_dati['categoria'];

    if($quantita_valore == 'valore'){
        $q = "SELECT SUM((righe.total_notaxes * cur.conversion_rate)/{$tasso_valuta}) AS ordinato";
    }
    else{
        $q = "SELECT SUM(righe.quantity) AS ordinato";
    }
    $q .= " FROM {$table_prefix}_salesorder so
        INNER JOIN {$table_prefix}_currency_info cur ON cur.id = so.currency_id
        INNER JOIN {$table_prefix}_inventoryproductrel righe ON righe.id = so.salesorderid
        INNER JOIN {$table_prefix}_service ser ON ser.serviceid = righe.productid
        INNER JOIN {$table_prefix}_account acc ON acc.accountid = so.accountid
        LEFT JOIN {$table_prefix}_kpbusinessunit bu ON bu.kpbusinessunitid = so.kp_business_unit
        LEFT JOIN {$table_prefix}_kpagenti ag ON ag.kpagentiid = so.kp_agente
        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = so.salesorderid
        INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = ser.serviceid
        INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
        WHERE ent.deleted = 0 AND ent1.deleted = 0 AND ent2.deleted = 0
        AND so.sostatus NOT IN ('Cancelled') 
        AND YEAR(so.data_ordine) = {$anno}
        AND MONTH(so.data_ordine) = {$numero_mese}";
    if($utenti != null && $utenti != '' && $utenti != 'Tutti'){
        $q .= " AND ent.smownerid = ".$utenti;
    }
    if($clienti != null && !empty($clienti)){
        if(count($clienti) === 1){
            $q .= " AND acc.accountid = ".$clienti[0];
        }
        else{
            $q .= " AND acc.accountid IN (".InArrayQuery($clienti).")";
        }
    }
    if($business_unit != null && !empty($business_unit)){
        if(count($business_unit) === 1){
            $q .= " AND bu.kpbusinessunitid = ".$business_unit[0];
        }
        else{
            $q .= " AND bu.kpbusinessunitid IN (".InArrayQuery($business_unit).")";
        }
    }
    if($servizi != null && !empty($servizi)){
        if(count($servizi) === 1){
            $q .= " AND ser.serviceid = ".$servizi[0];
        }
        else{
            $q .= " AND ser.serviceid IN (".InArrayQuery($servizi).")";
        }
    }
    if($agenti != null && !empty($agenti)){
        if(count($agenti) === 1){
            $q .= " AND ag.kpagentiid = ".$agenti[0];
        }
        else{
            $q .= " AND ag.kpagentiid IN (".InArrayQuery($agenti).")";
        }
    }
    if($area_aziendale != null && $area_aziendale != '' && $area_aziendale != '--Tutti--'){
        $q .= " AND ser.area_aziendale LIKE '".$area_aziendale."'";
    }
    if($categoria != null && $categoria != '' && $categoria != '--Tutti--'){
        $q .= " AND ser.servicecategory LIKE '".$categoria."'";
    }

    if($debug){
        LogBI("", "a+", "log_query");
        LogBI(__FUNCTION__.': ANNO '.$anno.' MESE '.$numero_mese, "a+", "log_query");
        LogBI($q, "a+", "log_query");
    }

    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $ordinato = $adb->query_result($res, 0, 'ordinato');
        $ordinato = html_entity_decode(strip_tags($ordinato), ENT_QUOTES,$default_charset);
        if($ordinato == "" || $ordinato == null){
            $ordinato = 0;
        }
    }
    else{
        $ordinato = 0;
    }

    return $ordinato;
}

function GetBudget($anno_confr, $numero_mese, $array_dati_generici){
    global $debug;

    return 0;

}

function recuperaPrezzoServizio($listino_azienda, $servizio){
    global $adb, $table_prefix, $current_user;
    
    require_once("modules/SproCore/SproUtils/spro_utils.php");
    
    $prezzo_listino = 0;

    if($listino_azienda != 0){
        $q_prezzo_listino = "SELECT pricerel.listprice AS prezzo_listino
                        FROM {$table_prefix}_pricebook price
                        INNER JOIN {$table_prefix}_pricebookproductrel pricerel ON pricerel.pricebookid = price.pricebookid
                            AND pricerel.productid = {$servizio}
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = price.pricebookid
                        WHERE ent.deleted = 0 AND price.pricebookid = {$listino_azienda}";
        $res_prezzo_listino = $adb->query($q_prezzo_listino);
        if($adb->num_rows($res_prezzo_listino) > 0){
            $prezzo_listino = $adb->query_result($res_prezzo_listino,0,'prezzo_listino');
            if($prezzo_listino == "" || $prezzo_listino == null){
                $prezzo_listino = 0;
            }
        }
    }
    
    if($prezzo_listino == 0){
        $id_statici = getConfigurazioniIdStatici();
        $id_statico = $id_statici["Generale - Listino standard (da utilizzare nel caso il cliente non ne abbia uno)"];
        if( $id_statico["valore"] == "" && $id_statico["valore"] == 0){
            return;
        }
        else{
            $listino_standard = $id_statico["valore"];
        }

        $q_prezzo_listino = "SELECT pricerel.listprice AS prezzo_listino
                        FROM {$table_prefix}_pricebook price
                        INNER JOIN {$table_prefix}_pricebookproductrel pricerel ON pricerel.pricebookid = price.pricebookid
                            AND pricerel.productid = {$servizio}
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = price.pricebookid
                        WHERE ent.deleted = 0 AND price.pricebookid = ".$listino_standard;
        $res_prezzo_listino = $adb->query($q_prezzo_listino);
        if($adb->num_rows($res_prezzo_listino) > 0){
            $prezzo_listino = $adb->query_result($res_prezzo_listino,0,'prezzo_listino');
            if($prezzo_listino == "" || $prezzo_listino == null){
                $prezzo_listino = 0;
            }
        }
    }
	
	return $prezzo_listino;	
}