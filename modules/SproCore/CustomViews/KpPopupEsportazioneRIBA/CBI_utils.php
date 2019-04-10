<?php

/* kpro@bid18062018 */
/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 * @package Esportazione RIBA in formato CBI
 * @version 1.0
 *
 */

function GetDatiSocieta(){

    $dati_societa_standard = GetDatiSocietaStandard();

    $dati_societa_aggiuntivi = GetDatiSocietaAggiuntivi();
    
    $dati_societa = array_merge($dati_societa_standard, $dati_societa_aggiuntivi);

    return $dati_societa;
}

function GetDatiSocietaStandard(){
    global $adb, $table_prefix, $default_charset;

    $result = array();

    $q = "SELECT organizationname ragione_sociale,
        address indirizzo,
        city citta,
        state provincia,
        code cap,
        crmv_vat_registration_number partita_iva
        FROM {$table_prefix}_organizationdetails";
    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $ragione_sociale = $adb->query_result($res, 0, 'ragione_sociale');
        $ragione_sociale = html_entity_decode(strip_tags($ragione_sociale), ENT_QUOTES, $default_charset);
        if($ragione_sociale == null){
            $ragione_sociale == '';
        }

        $indirizzo = $adb->query_result($res, 0, 'indirizzo');
        $indirizzo = html_entity_decode(strip_tags($indirizzo), ENT_QUOTES, $default_charset);
        if($indirizzo == null){
            $indirizzo == '';
        }

        $citta = $adb->query_result($res, 0, 'citta');
        $citta = html_entity_decode(strip_tags($citta), ENT_QUOTES, $default_charset);
        if($citta == null){
            $citta == '';
        }

        $provincia = $adb->query_result($res, 0, 'provincia');
        $provincia = html_entity_decode(strip_tags($provincia), ENT_QUOTES, $default_charset);
        if($provincia == null){
            $provincia == '';
        }

        $cap = $adb->query_result($res, 0, 'cap');
        $cap = html_entity_decode(strip_tags($cap), ENT_QUOTES, $default_charset);
        if($cap == null){
            $cap == '';
        }

        $partita_iva = $adb->query_result($res, 0, 'partita_iva');
        $partita_iva = html_entity_decode(strip_tags($partita_iva), ENT_QUOTES, $default_charset);
        if($partita_iva == null){
            $partita_iva == '';
        }

        $result = array(
            "ragione_sociale" => $ragione_sociale,
            "indirizzo" => $indirizzo,
            "citta" => $citta,
            "provincia" => $provincia,
            "cap" => $cap,
            "partita_iva" => $partita_iva
        );
    }

    return $result;
}

function GetDatiSocietaAggiuntivi(){
    global $adb, $table_prefix, $default_charset;

    $result = array();

    $q = "SELECT *
        FROM kp_settings_company_info";
    $res = $adb->query($q);
    if($adb->num_rows($res) > 0){
        $codice_sia = $adb->query_result($res, 0, 'codice_sia');
        $codice_sia = html_entity_decode(strip_tags($codice_sia), ENT_QUOTES, $default_charset);
        if($codice_sia == null){
            $codice_sia == '';
        }

        $result = array(
            "codice_sia" => $codice_sia
        );
    }

    return $result;
}

function GetDatiBanchePresentazione(){
    global $adb, $table_prefix, $default_charset;

    $result = array();

    $q = "SELECT banca_pagamento
        FROM {$table_prefix}_banca_pagamento
        GROUP BY banca_pagamento";
    $res = $adb->query($q);
    $num = $adb->num_rows($res);
    if($num > 0){
        for($i = 0; $i < $num; $i++){
            $banca_pagamento = $adb->query_result($res, $i, 'banca_pagamento');
            $banca_pagamento = html_entity_decode(strip_tags($banca_pagamento), ENT_QUOTES, $default_charset);
            if($banca_pagamento == null){
                $banca_pagamento == '';
            }

            $controllo = checkBancaPagamento($banca_pagamento);
            if($controllo['res']){
                $iban = $controllo['conto'];

                $q_cc = "SELECT bc.kp_abi abi,
                    bc.kp_cab cab,
                    cc.kp_iban iban
                    FROM {$table_prefix}_kpconticorrenti cc
                    INNER JOIN {$table_prefix}_kpbanche bc ON bc.kpbancheid = cc.kp_banca
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = cc.kpconticorrentiid
                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = bc.kpbancheid
                    WHERE ent.deleted = 0 AND ent1.deleted = 0
                    AND cc.kp_iban LIKE '%".$iban."%'";
                $res_cc = $adb->query($q_cc);
                if($adb->num_rows($res_cc) > 0){
                    $abi = $adb->query_result($res_cc, 0, 'abi');
                    $abi = html_entity_decode(strip_tags($abi), ENT_QUOTES, $default_charset);

                    $cab = $adb->query_result($res_cc, 0, 'cab');
                    $cab = html_entity_decode(strip_tags($cab), ENT_QUOTES, $default_charset);

                    $iban = $adb->query_result($res_cc, 0, 'iban');
                    $iban = html_entity_decode(strip_tags($iban), ENT_QUOTES, $default_charset);
                }
                else{
                    $abi = substr($iban, 5, 5);
                    $cab = substr($iban, 10, 5);
                }

                $cc = substr($iban, -12);

                $result[$banca_pagamento] = array(
                    'abi' => $abi,
                    'cab' => $cab,
                    'iban' => $iban,
                    'cc' => $cc
                );
            }
        }
    }

    return $result;

}

function checkBancaPagamento($banca_pagamento){
    
    $result = array();
    
    $array_banca_pagamento = explode(' ', $banca_pagamento);
    
    $i = 0;
    while($i < count($array_banca_pagamento) && empty($result)){
           
        $check_iban = checkIBAN($array_banca_pagamento[$i]);
        if($check_iban){
            $result = array(
                'res' => true,
                'conto' => trim($array_banca_pagamento[$i])
            );
        }
            
        $i++;
    }
    
    if(empty($result)){
        $result = array(
            'res' => false
        );
    }
    
    return $result;
}

function checkIBAN($iban){
    $iban = strtolower(str_replace(' ','',$iban));
    $Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
    $Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);

    if(strlen($iban) == $Countries[substr($iban,0,2)]){

        $MovedChar = substr($iban, 4).substr($iban,0,4);
        $MovedCharArray = str_split($MovedChar);
        $NewString = "";

        foreach($MovedCharArray AS $key => $value){
            if(!is_numeric($MovedCharArray[$key])){
                $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
            }
            $NewString .= $MovedCharArray[$key];
        }

        if(my_bcmod($NewString, '97') == 1) //kpro@tom100420191137
        {
            return true;
        }
        else{
            return false;
        }
    }
    else{
        return false;
    }   
}

/* kpro@tom100420191137 */
function my_bcmod( $x, $y ) { 
    // how many numbers to take at once? carefull not to exceed (int) 
    $take = 5;     
    $mod = ''; 

    do 
    { 
        $a = (int)$mod.substr( $x, 0, $take ); 
        $x = substr( $x, $take ); 
        $mod = $a % $y;    
    } 
    while ( strlen($x) ); 

    return (int)$mod; 
} 
/* kpro@tom100420191137 end */

function ComponiRecordIntestazione($dati_record, $nome_file){

    $config_record_intestazione = array();

    $filler = $dati_record['filler'];
    $codice_sia = $dati_record['codice_sia'];
    $abipres = $dati_record['abipres'];
    $data_creazione = $dati_record['data_creazione'];
    $nome_supporto = $dati_record['nome_supporto'];
    $disposizione = $dati_record['disposizione'];
    $lbero = $dati_record['lbero'];
    $qualificatore = $dati_record['qualificatore'];
    $codice_divisa = $dati_record['codice_divisa'];
    $non_disponibile = $dati_record['non_disponibile'];

    $config_record_intestazione[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_intestazione[] = array('valore' => 'IB','tipo' => 'string','lunghezza' => 2);
    $config_record_intestazione[] = array('valore' => $codice_sia,'tipo' => 'string','lunghezza' => 5);
    $config_record_intestazione[] = array('valore' => $abipres,'tipo' => 'string','lunghezza' => 5);
    $config_record_intestazione[] = array('valore' => $data_creazione,'tipo' => 'date','lunghezza' => 6);
    $config_record_intestazione[] = array('valore' => $nome_supporto,'tipo' => 'string','lunghezza' => 20);
    $config_record_intestazione[] = array('valore' => $disposizione,'tipo' => 'string','lunghezza' => 6);
    $config_record_intestazione[] = array('valore' => $lbero,'tipo' => 'string','lunghezza' => 59);
    $config_record_intestazione[] = array('valore' => $qualificatore,'tipo' => 'string','lunghezza' => 7);
    $config_record_intestazione[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 2);
    $config_record_intestazione[] = array('valore' => $codice_divisa,'tipo' => 'string','lunghezza' => 1);
    $config_record_intestazione[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_intestazione[] = array('valore' => $non_disponibile,'tipo' => 'string','lunghezza' => 5);

    $res = ScriviRecordCBI($config_record_intestazione, $nome_file);

    return $res;
}

function ComponiRecordRighe($dati_record, $nome_file){

    $array_nomi_record = array('14','20','30','40','50','51','70');
    $config_record_14 = array();
    $config_record_20 = array();
    $config_record_30 = array();
    $config_record_40 = array();
    $config_record_50 = array();
    $config_record_51 = array();
    $config_record_70 = array();

    $filler = $dati_record['filler'];
    $codice_divisa = $dati_record['codice_divisa'];
    $codice_sia = $dati_record['codice_sia'];
    $abipres = $dati_record['abipres'];
    $cabpres = $dati_record['cabpres'];
    $contopres = $dati_record['contopres'];
    $data_pagamento = $dati_record['data_pagamento'];
    $causale = $dati_record['causale'];
    $importo = $dati_record['importo'];
    $segno = $dati_record['segno'];
    $abi = $dati_record['abi'];
    $cab = $dati_record['cab'];
    $tipo_codice = $dati_record['tipo_codice'];
    $codice_cliente = $dati_record['codice_cliente'];
    $flag_debitore = $dati_record['flag_debitore'];
    $ragione_sociale_cliente = $dati_record['ragione_sociale_cliente'];
    $cf_piva_cliente = $dati_record['cf_piva_cliente'];
    $via_cliente = $dati_record['indirizzo_cliente'];
    $cap_cliente = $dati_record['cap_cliente'];
    $citta_cliente = $dati_record['citta_cliente'];
    $provincia_cliente = $dati_record['provincia_cliente'];
    $nome_fattura = $dati_record['nome_fattura'];
    $altra_riga = $dati_record['altra_riga'];
    $numero_progressivo = $dati_record['numero_progressivo'];
    $numero_ricevuta = $dati_record['numero_ricevuta'];
    $numero_autorizzazione = $dati_record['numero_autorizzazione'];
    $data_autorizzazione = $dati_record['data_autorizzazione'];
    $indicatore_circuito = $dati_record['indicatore_circuito'];
    $tipo_documento = $dati_record['tipo_documento'];
    $flag_esito = $dati_record['flag_esito'];
    $flag_stampa_avviso = $dati_record['flag_stampa_avviso'];
    $chiavi_controllo = $dati_record['chiavi_controllo'];
    $ragione_sociale_nostra_azienda = $dati_record['ragione_sociale'];
    $via_nostra_azienda = $dati_record['indirizzo'];
    $citta_nostra_azienda = $dati_record['citta'];
    $provincia_nostra_azienda = $dati_record['provincia'];
    $cap_nostra_azienda = $dati_record['cap'];
    $cf_piva_nostra_azienda = $dati_record['cf_piva'];

    $config_record_14[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_14[] = array('valore' => '14','tipo' => 'string','lunghezza' => 2);
    $config_record_14[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_14[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 12);
    $config_record_14[] = array('valore' => $data_pagamento,'tipo' => 'date','lunghezza' => 6);
    $config_record_14[] = array('valore' => $causale,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $importo,'tipo' => 'importo','lunghezza' => 13);
    $config_record_14[] = array('valore' => $segno,'tipo' => 'string','lunghezza' => 1);
    $config_record_14[] = array('valore' => $abipres,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $cabpres,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $contopres,'tipo' => 'cc','lunghezza' => 12);
    $config_record_14[] = array('valore' => $abi,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $cab,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 12);
    $config_record_14[] = array('valore' => $codice_sia,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $tipo_codice,'tipo' => 'string','lunghezza' => 1);
    $config_record_14[] = array('valore' => $codice_cliente,'tipo' => 'string','lunghezza' => 16);
    $config_record_14[] = array('valore' => $flag_debitore,'tipo' => 'string','lunghezza' => 1);
    $config_record_14[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 5);
    $config_record_14[] = array('valore' => $codice_divisa,'tipo' => 'string','lunghezza' => 1);

    $config_record_20[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_20[] = array('valore' => '20','tipo' => 'string','lunghezza' => 2);
    $config_record_20[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_20[] = array('valore' => $ragione_sociale_nostra_azienda,'tipo' => 'string','lunghezza' => 24);
    $config_record_20[] = array('valore' => $via_nostra_azienda,'tipo' => 'string','lunghezza' => 24);
    $config_record_20[] = array('valore' => $citta_nostra_azienda,'tipo' => 'string','lunghezza' => 27);
    $config_record_20[] = array('valore' => $cap_nostra_azienda,'tipo' => 'string','lunghezza' => 5);
    $config_record_20[] = array('valore' => $cf_piva_nostra_azienda,'tipo' => 'string','lunghezza' => 30);

    $config_record_30[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_30[] = array('valore' => '30','tipo' => 'string','lunghezza' => 2);
    $config_record_30[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_30[] = array('valore' => $ragione_sociale_cliente,'tipo' => 'string','lunghezza' => 60);
    $config_record_30[] = array('valore' => $cf_piva_cliente,'tipo' => 'string','lunghezza' => 50);

    $config_record_40[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_40[] = array('valore' => '40','tipo' => 'string','lunghezza' => 2);
    $config_record_40[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_40[] = array('valore' => $via_cliente,'tipo' => 'string','lunghezza' => 30);
    $config_record_40[] = array('valore' => $cap_cliente,'tipo' => 'string','lunghezza' => 5);
    $config_record_40[] = array('valore' => $citta_cliente,'tipo' => 'string','lunghezza' => 23);
    $config_record_40[] = array('valore' => $provincia_cliente,'tipo' => 'string','lunghezza' => 52);

    $config_record_50[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_50[] = array('valore' => '50','tipo' => 'string','lunghezza' => 2);
    $config_record_50[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_50[] = array('valore' => $nome_fattura,'tipo' => 'string','lunghezza' => 40);
    $config_record_50[] = array('valore' => $altra_riga,'tipo' => 'string','lunghezza' => 40);
    $config_record_50[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 10);
    $config_record_50[] = array('valore' => $cf_piva_nostra_azienda,'tipo' => 'string','lunghezza' => 16);
    $config_record_50[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 4);

    $config_record_51[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_51[] = array('valore' => '51','tipo' => 'string','lunghezza' => 2);
    $config_record_51[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_51[] = array('valore' => $numero_ricevuta,'tipo' => 'number','lunghezza' => 10);
    $config_record_51[] = array('valore' => $ragione_sociale_nostra_azienda,'tipo' => 'string','lunghezza' => 20);
    $config_record_51[] = array('valore' => $provincia_nostra_azienda,'tipo' => 'string','lunghezza' => 15);
    $config_record_51[] = array('valore' => $numero_autorizzazione,'tipo' => 'number','lunghezza' => 10);
    $config_record_51[] = array('valore' => $data_autorizzazione,'tipo' => 'date','lunghezza' => 6);
    $config_record_51[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 49);

    $config_record_70[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_70[] = array('valore' => '70','tipo' => 'string','lunghezza' => 2);
    $config_record_70[] = array('valore' => $numero_progressivo,'tipo' => 'number','lunghezza' => 7);
    $config_record_70[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 78);
    $config_record_70[] = array('valore' => $indicatore_circuito,'tipo' => 'string','lunghezza' => 12);
    $config_record_70[] = array('valore' => $tipo_documento,'tipo' => 'number','lunghezza' => 1);
    $config_record_70[] = array('valore' => $flag_esito,'tipo' => 'number','lunghezza' => 1);
    $config_record_70[] = array('valore' => $flag_stampa_avviso,'tipo' => 'number','lunghezza' => 1);
    $config_record_70[] = array('valore' => $chiavi_controllo,'tipo' => 'string','lunghezza' => 17);

    $res = true;
    foreach($array_nomi_record as $nome_record){
        $res_riga = ScriviRecordCBI(${'config_record_'.$nome_record}, $nome_file);
        if(!$res_riga){
            $res = $res_riga;
        }
    }

    return $res;
}

function ComponiRecordCoda($dati_record, $nome_file){

    $config_record_coda = array();

    $filler = $dati_record['filler'];
    $codice_sia = $dati_record['codice_sia'];
    $abipres = $dati_record['abipres'];
    $data_creazione = $dati_record['data_creazione'];
    $nome_supporto = $dati_record['nome_supporto'];
    $disposizione = $dati_record['disposizione'];
    $numero_disposizione = $dati_record['numero_disposizione'];
    $importi_negativi = $dati_record['importi_negativi'];
    $importi_positivi = $dati_record['importi_positivi'];
    $numero_record = $dati_record['numero_record'];
    $codice_divisa = $dati_record['codice_divisa'];
    $non_disponibile = $dati_record['non_disponibile'];

    $config_record_coda[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 1);
    $config_record_coda[] = array('valore' => 'EF','tipo' => 'string','lunghezza' => 2);
    $config_record_coda[] = array('valore' => $codice_sia,'tipo' => 'string','lunghezza' => 5);
    $config_record_coda[] = array('valore' => $abipres,'tipo' => 'string','lunghezza' => 5);
    $config_record_coda[] = array('valore' => $data_creazione,'tipo' => 'date','lunghezza' => 6);
    $config_record_coda[] = array('valore' => $nome_supporto,'tipo' => 'string','lunghezza' => 20);
    $config_record_coda[] = array('valore' => $disposizione,'tipo' => 'string','lunghezza' => 6);
    $config_record_coda[] = array('valore' => $numero_disposizione,'tipo' => 'number','lunghezza' => 7);
    $config_record_coda[] = array('valore' => $importi_negativi,'tipo' => 'importo','lunghezza' => 15);
    $config_record_coda[] = array('valore' => $importi_positivi,'tipo' => 'importo','lunghezza' => 15);
    $config_record_coda[] = array('valore' => $numero_record,'tipo' => 'number','lunghezza' => 7);
    $config_record_coda[] = array('valore' => $filler,'tipo' => 'string','lunghezza' => 24);
    $config_record_coda[] = array('valore' => $codice_divisa,'tipo' => 'string','lunghezza' => 1);
    $config_record_coda[] = array('valore' => $non_disponibile,'tipo' => 'string','lunghezza' => 6);

    $res = ScriviRecordCBI($config_record_coda, $nome_file);

    return $res;
}

function ScriviRecordCBI($dati_record_cbi, $nome_file){
    
    $debug = false;

    $result = false;

    if(!empty($dati_record_cbi)){

        $record_cbi = "";

        for($i = 0; $i < count($dati_record_cbi); $i++){

            $valore = $dati_record_cbi[$i]['valore'];
            $tipo = $dati_record_cbi[$i]['tipo'];
            $lunghezza = $dati_record_cbi[$i]['lunghezza'];

            $valore = preg_replace("~[^a-zA-Z0-9\s\.\,\;\'\-\_\\\/\&]~", "", $valore);

            switch($tipo){
                case 'string':
                    $pad_string = ' ';
                    $pad_type = STR_PAD_RIGHT;
                    $valore = substr($valore, 0, $lunghezza);
                    break;
                case 'date':
                    $pad_string = ' ';
                    $pad_type = STR_PAD_RIGHT;
                    if($valore != ''){
                        $valore = new DateTime($valore);
                        $valore = $valore->format('dmy');
                    }
                    break;
                case 'number':
                    $pad_string = 0;
                    $pad_type = STR_PAD_LEFT;
                    break;
                case 'importo':
                    $pad_string = 0;
                    $pad_type = STR_PAD_LEFT;
                    $valore = number_format($valore, 2, '', '');
                    break;
                default:
                    $pad_string = ' ';
                    $pad_type = STR_PAD_RIGHT;
                    $valore = substr($valore, 0, $lunghezza);        
            }

            $valore = str_pad($valore, $lunghezza, $pad_string, $pad_type);

            $record_cbi .= $valore;

        }

        if($debug){
            echo $record_cbi."   ->   ".strlen($record_cbi)."<br>";
        }

        $file_path = __DIR__."/temp/";

        $report_finale = $record_cbi."\r\n";
        $file_cbi = fopen($file_path.$nome_file, "a+");
        fwrite($file_cbi, $report_finale);
        fclose($file_cbi);

        if($record_cbi != ""){
            $result = true;
        }

    }

    return $result;
}