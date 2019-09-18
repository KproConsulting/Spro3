<?php

include_once(__DIR__.'/../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
require_once('plugins/script_schedulati/funzioni_avvisi.php');
require_once('plugins/script_schedulati/aggiorna_impianti.php');
require_once('plugins/script_schedulati/aggiorna_formazione.php');
require_once('plugins/script_schedulati/aggiorna_visite_mediche.php');
require_once('plugins/script_schedulati/aggiorna_check_list.php');
require_once('modules/SproCore/SproUtils/spro_utils.php');
require_once("modules/SproCore/KpLettereNomina/ClassKpLettereNominaKp.php"); 
require_once('modules/SproCore/KpSitMinaccePrivacy/ClassKpSitMinaccePrivacyKp.php'); 
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user;
session_start();

$current_user->id = 1;

$debug = false;
$path_logs = __DIR__."/logs/";
$logs_file_name = "verifica_avvisi_log.txt";
$text_log = "
Calcolo start: ".date('d-m-Y H:i:s');
$log_file=fopen($path_logs.$logs_file_name,"a+");
fwrite($log_file, $text_log);
fclose($log_file);

$id_statici = getConfigurazioniIdStatici();
$id_statico = $id_statici["Programmi Custom - Gestione Avvisi - Giorni per In Scadenza standard"];
if( $id_statico["valore"] == "" && $id_statico["valore"] == 0){
    $giorni_in_scadenza = 0;
}
else{
    $giorni_in_scadenza = $id_statico["valore"];
}

if($debug){
    $text_log = "
- aggiornaStatoMansioniRisorseNonAttive -> ";
    $log_file=fopen($path_logs.'debug.txt',"w+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

/* kpro@tom010220170902 */

/**
* @author Tomiello Marco
* @copyright (c) 2017, Kpro Consulting Srl
*/

aggiornaStatoMansioniRisorseNonAttive();

/* kpro@tom010220170902 end */

if($debug){
    $text_log = "OK
- calcolaSituazioneFormazioneAzienda + AggiornaVisiteMediche -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

calcolaSituazioneFormazione();

$q_stabilimenti = "SELECT cont.accountid 
                FROM {$table_prefix}_mansionirisorsa mnsr
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = mnsr.mansionirisorsaid
                INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = mnsr.risorsa
                WHERE ent.deleted = 0 AND mnsr.stato_mansione = 'Attiva'
                GROUP BY cont.accountid";

$res_stabilimenti = $adb->query($q_stabilimenti);
$num_stabilimenti = $adb->num_rows($res_stabilimenti);

for($i=0; $i<$num_stabilimenti; $i++){		
		
    $azienda = $adb->query_result($res_stabilimenti,$i,'accountid');

    //calcolaSituazioneFormazioneAzienda($azienda,$giorni_in_scadenza);
    AggiornaVisiteMediche($azienda,$giorni_in_scadenza);
		
}

if($debug){
    $text_log = "OK
- AggiornaCheckList -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

$q_stabilimenti = "SELECT imp.azienda 
                FROM {$table_prefix}_impianti imp
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = imp.impiantiid
                WHERE ent.deleted = 0 AND imp.stato_impianto = 'Attivo'
                GROUP BY imp.azienda";

$res_stabilimenti = $adb->query($q_stabilimenti);
$num_stabilimenti = $adb->num_rows($res_stabilimenti);

for($i=0; $i<$num_stabilimenti; $i++){		

    $azienda = $adb->query_result($res_stabilimenti,$i,'azienda');

    AggiornaCheckList($azienda,$giorni_in_scadenza);
		
}

if($debug){
    $text_log = "OK
- AggiornaImpianti -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}
	
$q_stabilimenti = "SELECT imp.azienda 
                FROM {$table_prefix}_impianti imp
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = imp.impiantiid
                WHERE ent.deleted = 0 AND imp.stato_impianto = 'Attivo'
                GROUP BY imp.azienda";

$res_stabilimenti = $adb->query($q_stabilimenti);
$num_stabilimenti = $adb->num_rows($res_stabilimenti);

for($i=0; $i<$num_stabilimenti; $i++){		

    $azienda = $adb->query_result($res_stabilimenti,$i,'azienda');

    AggiornaImpianti($azienda,$giorni_in_scadenza);
		
}	

if($debug){
    $text_log = "OK
- calcolaSituazioneDocumenti -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

//Lancio il calcolo della situazione documenti aziende
calcolaSituazioneDocumenti();

if($debug){
    $text_log = "OK
- calcolaSituazioneDocumentiFornitori -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

//Lancio il calcolo della situazione documenti fornitori
calcolaSituazioneDocumentiFornitori();

if($debug){
    $text_log = "OK
- calcolaSituazioneDocumentiStandard -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

//Lancio il calcolo della situazione di tutti i documenti che non stati modificati dalle 2 funzioni precedenti
calcolaSituazioneDocumentiStandard();

if($debug){
    $text_log = "OK
- checkLettereDiNominaRisorse -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

KpLettereNominaKp::checkLettereDiNominaRisorse();

if($debug){
    $text_log = "OK
- aggiornaSituazioneMinaccePrivacy -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

KpSitMinaccePrivacyKp::aggiornaSituazioneMinaccePrivacy();

if($debug){
    $text_log = "OK
- Invio avvisi di aziende/fornitori specifici -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

//Avviso i Soggetti Avvisi specificati nei record di Gestione Avvisi
$q_avvisi = "SELECT avv.gestioneavvisiid gestioneavvisiid, 
            avv.tipo_avviso tipo_avviso, 
            acc.accountid soggetto_avviso, 
            avv.kp_tipo_sogg_avvisi tipo_soggetto_avviso,
            avv.giorni_tra_avvisi giorni_tra_avvisi, 
            avv.giorni_in_scadenza giorni_in_scadenza, 
            avv.data_ultimo_avviso data_ultimo_avviso, 
            avv.indirizzo_mittente indirizzo_mittente, 
            avv.nome_mittente nome_mittente,
            avv.frequenza_checklist frequenza_checklist
            FROM {$table_prefix}_gestioneavvisi avv
            INNER JOIN {$table_prefix}_account acc ON acc.accountid = avv.stabilimento
            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
            WHERE ent.deleted = 0
            UNION
            SELECT avv.gestioneavvisiid gestioneavvisiid, 
            avv.tipo_avviso tipo_avviso, 
            vend.vendorid soggetto_avviso, 
            avv.kp_tipo_sogg_avvisi tipo_soggetto_avviso,
            avv.giorni_tra_avvisi giorni_tra_avvisi, 
            avv.giorni_in_scadenza giorni_in_scadenza, 
            avv.data_ultimo_avviso data_ultimo_avviso, 
            avv.indirizzo_mittente indirizzo_mittente, 
            avv.nome_mittente nome_mittente,
            avv.frequenza_checklist frequenza_checklist
            FROM {$table_prefix}_gestioneavvisi avv
            INNER JOIN {$table_prefix}_vendor vend ON vend.vendorid = avv.stabilimento
            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
            WHERE ent.deleted = 0";

$res_avvisi = $adb->query($q_avvisi);			
$num_avvisi = $adb->num_rows($res_avvisi);		

if($num_avvisi > 0){
    for($i=0; $i<$num_avvisi; $i++){

        $gestioneavvisiid = $adb->query_result($res_avvisi,$i,'gestioneavvisiid');
        $gestioneavvisiid = html_entity_decode(strip_tags($gestioneavvisiid), ENT_QUOTES,$default_charset);
        
        $tipo_avviso = $adb->query_result($res_avvisi,$i,'tipo_avviso');
        $tipo_avviso = html_entity_decode(strip_tags($tipo_avviso), ENT_QUOTES,$default_charset);
        
        $soggetto_avviso = $adb->query_result($res_avvisi,$i,'soggetto_avviso');
        $soggetto_avviso = html_entity_decode(strip_tags($soggetto_avviso), ENT_QUOTES,$default_charset);

        $tipo_soggetto_avviso = $adb->query_result($res_avvisi,$i,'tipo_soggetto_avviso');
        $tipo_soggetto_avviso = html_entity_decode(strip_tags($tipo_soggetto_avviso), ENT_QUOTES,$default_charset);
        
        $giorni_tra_avvisi = $adb->query_result($res_avvisi,$i,'giorni_tra_avvisi');
        $giorni_tra_avvisi = html_entity_decode(strip_tags($giorni_tra_avvisi), ENT_QUOTES,$default_charset);
        
        $giorni_in_scadenza = $adb->query_result($res_avvisi,$i,'giorni_in_scadenza');
        $giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
         
        $data_ultimo_avviso = $adb->query_result($res_avvisi,$i,'data_ultimo_avviso');
        $data_ultimo_avviso = html_entity_decode(strip_tags($data_ultimo_avviso), ENT_QUOTES,$default_charset);
        
        $indirizzo_mittente = $adb->query_result($res_avvisi,$i,'indirizzo_mittente');
        $indirizzo_mittente = html_entity_decode(strip_tags($indirizzo_mittente), ENT_QUOTES,$default_charset);
        
        $nome_mittente = $adb->query_result($res_avvisi,$i,'nome_mittente');
        $nome_mittente = html_entity_decode(strip_tags($nome_mittente), ENT_QUOTES,$default_charset);
		
		$frequenza_checklist = $adb->query_result($res_avvisi,$i,'frequenza_checklist');
        $frequenza_checklist = html_entity_decode(strip_tags($frequenza_checklist), ENT_QUOTES,$default_charset);
        
        if($tipo_soggetto_avviso == 'Aziende'){
            InvioAvvisi($gestioneavvisiid, $tipo_avviso, $soggetto_avviso, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente, $data_ultimo_avviso, $giorni_tra_avvisi);
        }
        elseif($tipo_soggetto_avviso == 'Fornitori'){
            InvioAvvisiFornitori($gestioneavvisiid, $tipo_avviso, $soggetto_avviso, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente, $data_ultimo_avviso, $giorni_tra_avvisi);
        }
    } 
}

if($debug){
    $text_log = "OK
- Invio avvisi di aziende/fornitori globali -> ";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

//Avviso tutte gli altri Soggetti Avvisi per i record di Gestione Avvisi senza azienda escludendo le aziende con una Gestione Avvisi specifica
$q_avvisi = "SELECT avv.gestioneavvisiid gestioneavvisiid, 
            avv.tipo_avviso tipo_avviso, 
            avv.kp_tipo_sogg_avvisi tipo_soggetto_avviso,
            avv.giorni_tra_avvisi giorni_tra_avvisi, 
            avv.giorni_in_scadenza giorni_in_scadenza, 
            avv.data_ultimo_avviso data_ultimo_avviso, 
            avv.indirizzo_mittente indirizzo_mittente, 
            avv.nome_mittente nome_mittente,
            avv.frequenza_checklist frequenza_checklist
            FROM {$table_prefix}_gestioneavvisi avv
            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
            WHERE ent.deleted = 0 AND (avv.stabilimento IS NULL 
            OR avv.stabilimento = '' OR avv.stabilimento = 0)";

$res_avvisi = $adb->query($q_avvisi);			
$num_avvisi = $adb->num_rows($res_avvisi);		

if($num_avvisi > 0){
    for($i=0; $i<$num_avvisi; $i++){

        $gestioneavvisiid = $adb->query_result($res_avvisi,$i,'gestioneavvisiid');
        $gestioneavvisiid = html_entity_decode(strip_tags($gestioneavvisiid), ENT_QUOTES,$default_charset);
        
        $tipo_avviso = $adb->query_result($res_avvisi,$i,'tipo_avviso');
        $tipo_avviso = html_entity_decode(strip_tags($tipo_avviso), ENT_QUOTES,$default_charset);

        $tipo_soggetto_avviso = $adb->query_result($res_avvisi,$i,'tipo_soggetto_avviso');
        $tipo_soggetto_avviso = html_entity_decode(strip_tags($tipo_soggetto_avviso), ENT_QUOTES,$default_charset);
        
        $giorni_tra_avvisi = $adb->query_result($res_avvisi,$i,'giorni_tra_avvisi');
        $giorni_tra_avvisi = html_entity_decode(strip_tags($giorni_tra_avvisi), ENT_QUOTES,$default_charset);
        
        $giorni_in_scadenza = $adb->query_result($res_avvisi,$i,'giorni_in_scadenza');
        $giorni_in_scadenza = html_entity_decode(strip_tags($giorni_in_scadenza), ENT_QUOTES,$default_charset);
         
        $data_ultimo_avviso = $adb->query_result($res_avvisi,$i,'data_ultimo_avviso');
        $data_ultimo_avviso = html_entity_decode(strip_tags($data_ultimo_avviso), ENT_QUOTES,$default_charset);
        
        $indirizzo_mittente = $adb->query_result($res_avvisi,$i,'indirizzo_mittente');
        $indirizzo_mittente = html_entity_decode(strip_tags($indirizzo_mittente), ENT_QUOTES,$default_charset);
        
        $nome_mittente = $adb->query_result($res_avvisi,$i,'nome_mittente');
        $nome_mittente = html_entity_decode(strip_tags($nome_mittente), ENT_QUOTES,$default_charset);
		
		$frequenza_checklist = $adb->query_result($res_avvisi,$i,'frequenza_checklist');
        $frequenza_checklist = html_entity_decode(strip_tags($frequenza_checklist), ENT_QUOTES,$default_charset);

        if($tipo_soggetto_avviso == 'Aziende'){
            $q_aziende = "SELECT acc.accountid
                        FROM {$table_prefix}_account acc
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
                        WHERE ent.deleted = 0 AND acc.accountid NOT IN (
                            SELECT avv.stabilimento
                            FROM {$table_prefix}_gestioneavvisi avv
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
                            WHERE ent.deleted = 0 AND avv.tipo_avviso = '{$tipo_avviso}'
                            GROUP BY avv.stabilimento
                        )";
            $res_aziende = $adb->query($q_aziende);
            $num_aziende = $adb->num_rows($res_aziende);
            if($num_aziende > 0){
                for($j = 0; $j < $num_aziende; $j++){

                    $azienda = $adb->query_result($res_aziende,$j,'accountid');
                    $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES,$default_charset);

                    InvioAvvisi($gestioneavvisiid, $tipo_avviso, $azienda, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente, $data_ultimo_avviso, $giorni_tra_avvisi);
                }
            }
        }
        elseif($tipo_soggetto_avviso == 'Fornitori'){
            $q_fornitori = "SELECT vend.vendorid
                        FROM {$table_prefix}_vendor vend
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = vend.vendorid
                        WHERE ent.deleted = 0 AND vend.vendorid NOT IN (
                            SELECT avv.stabilimento
                            FROM {$table_prefix}_gestioneavvisi avv
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = avv.gestioneavvisiid
                            WHERE ent.deleted = 0 AND avv.tipo_avviso = '{$tipo_avviso}'
                            GROUP BY avv.stabilimento
                        )";
            $res_fornitori = $adb->query($q_fornitori);
            $num_fornitori = $adb->num_rows($res_fornitori);
            if($num_fornitori > 0){
                for($j = 0; $j < $num_fornitori; $j++){

                    $fornitore = $adb->query_result($res_fornitori,$j,'vendorid');
                    $fornitore = html_entity_decode(strip_tags($fornitore), ENT_QUOTES,$default_charset);

                    InvioAvvisiFornitori($gestioneavvisiid, $tipo_avviso, $fornitore, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente, $data_ultimo_avviso, $giorni_tra_avvisi);
                }
            }
        }

    } 
}

if($debug){
    $text_log = "OK";
    $log_file=fopen($path_logs.'debug.txt',"a+");
    fwrite($log_file, $text_log);
    fclose($log_file);
}

$text_log = " Calcolo end: ".date('d-m-Y H:i:s');
$log_file=fopen($path_logs.$logs_file_name,"a+");
fwrite($log_file, $text_log);
fclose($log_file);

function InvioAvvisi($gestioneavvisiid, $tipo_avviso, $soggetto_avviso, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente, $data_ultimo_avviso, $giorni_tra_avvisi){
    global $adb, $table_prefix, $current_user;

    $data_corrente = date('Y-m-d');

    $control = 'no';
    $eseguire_avviso = 'no';

    if($data_ultimo_avviso != '' && $data_ultimo_avviso != '0'){
        list($anno_avviso,$mese_avviso,$giorno_avviso) = explode("-",$data_ultimo_avviso);
        $data_prossimo_avviso = date("Y-m-d",mktime(0,0,0,$mese_avviso,$giorno_avviso+$giorni_tra_avvisi,$anno_avviso));
        if($data_prossimo_avviso <= $data_corrente){
            $eseguire_avviso = 'si';
        }
    }
    else{
        $eseguire_avviso = 'si';
    }

    if($eseguire_avviso == 'si' && $tipo_avviso == 'Formazione'){
        $control = AvvisiFormazione($gestioneavvisiid,$soggetto_avviso,$giorni_in_scadenza,$indirizzo_mittente,$nome_mittente);
    }
    elseif($eseguire_avviso != 'si' && $tipo_avviso == 'Formazione'){
        calcolaSituazioneFormazioneAzienda($soggetto_avviso,$giorni_in_scadenza);
    }

    if($eseguire_avviso == 'si' && $tipo_avviso == 'Visite Mediche'){
        $control = AvvisiVisiteMediche($gestioneavvisiid,$soggetto_avviso,$giorni_in_scadenza,$indirizzo_mittente,$nome_mittente);
    }
    elseif($eseguire_avviso != 'si' && $tipo_avviso == 'Visite Mediche'){
        AggiornaVisiteMediche($soggetto_avviso,$giorni_in_scadenza);
    }	

    if($eseguire_avviso == 'si' && $tipo_avviso == 'Impianti'){
        $control = AvvisiImpianti($gestioneavvisiid,$soggetto_avviso,$giorni_in_scadenza,$indirizzo_mittente,$nome_mittente);
    }
    elseif($eseguire_avviso != 'si' && $tipo_avviso == 'Impianti'){
        AggiornaImpianti($soggetto_avviso,$giorni_in_scadenza);
    }

    if($eseguire_avviso == 'si' && $tipo_avviso == 'Check List'){
        $control = AvvisiCheckList($gestioneavvisiid,$soggetto_avviso,$giorni_in_scadenza,$indirizzo_mittente,$nome_mittente);
    }
    
    if($eseguire_avviso == 'si' && $tipo_avviso == 'Documenti'){
        $control = AvvisiDocumenti($gestioneavvisiid,$soggetto_avviso,$giorni_in_scadenza,$indirizzo_mittente,$nome_mittente);
    }

    if($eseguire_avviso == 'si' && $tipo_avviso == 'Lettere di Nomina'){
        $control = AvvisiLettereDiNomina($gestioneavvisiid, $soggetto_avviso, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente);
    }

    if($control == 'si'){
        $q_upd_avviso = "UPDATE {$table_prefix}_gestioneavvisi 
                        SET data_ultimo_avviso = '".$data_corrente."'
                        WHERE gestioneavvisiid =".$gestioneavvisiid;
        $adb->query($q_upd_avviso);
    } 
}

function InvioAvvisiFornitori($gestioneavvisiid, $tipo_avviso, $soggetto_avviso, $giorni_in_scadenza, $indirizzo_mittente, $nome_mittente, $data_ultimo_avviso, $giorni_tra_avvisi){
    global $adb, $table_prefix, $current_user;

    $data_corrente = date('Y-m-d');

    $control = 'no';
    $eseguire_avviso = 'no';

    if($data_ultimo_avviso != '' && $data_ultimo_avviso != '0'){
        list($anno_avviso,$mese_avviso,$giorno_avviso) = explode("-",$data_ultimo_avviso);
        $data_prossimo_avviso = date("Y-m-d",mktime(0,0,0,$mese_avviso,$giorno_avviso+$giorni_tra_avvisi,$anno_avviso));
        if($data_prossimo_avviso <= $data_corrente){
            $eseguire_avviso = 'si';
        }
    }
    else{
        $eseguire_avviso = 'si';
    }
    
    if($eseguire_avviso == 'si' && $tipo_avviso == 'Documenti'){
        $control = AvvisiDocumentiFornitori($gestioneavvisiid,$soggetto_avviso,$giorni_in_scadenza,$indirizzo_mittente,$nome_mittente);
    }

    if($control == 'si'){
        $q_upd_avviso = "UPDATE {$table_prefix}_gestioneavvisi 
                        SET data_ultimo_avviso = '".$data_corrente."'
                        WHERE gestioneavvisiid =".$gestioneavvisiid;
        $adb->query($q_upd_avviso);
    } 
}

?>