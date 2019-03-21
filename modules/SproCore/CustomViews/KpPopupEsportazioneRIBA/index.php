<!DOCTYPE html>

<?php
/* kpro@bid18062018 */
/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 * @package Esportazione RIBA in formato CBI
 * @version 1.0
 *
 */

require_once('CBI_utils.php');

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
include('themes/SmallHeader.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $default_charset, $site_URL;
global $small_page_title;
session_start();

$html = '';
$html_popup = '';
if (isset($_REQUEST["ids"])) {
    $ids = array_filter(Zend_Json::decode($_REQUEST['ids']));

    $array_corrette = array();
    $array_banche = array();
    $array_flussi_cbi = array();
    $array_righe_cbi = array();
    $array_banche_controllo = array();
    $num_selezionate = 0;
    $num_testate = 0;
    $num_righe = 0;
    $num_errori = 0;
    $html_corretti = '';
    $html_errori = '';

    $html_caricamento = '<div class="spinner-layer spinner-blue">
                            <div class="circle-clipper left">
                            <div class="circle"></div>
                            </div><div class="gap-patch">
                            <div class="circle"></div>
                            </div><div class="circle-clipper right">
                            <div class="circle"></div>
                            </div>
                        </div>

                        <div class="spinner-layer spinner-red">
                            <div class="circle-clipper left">
                            <div class="circle"></div>
                            </div><div class="gap-patch">
                            <div class="circle"></div>
                            </div><div class="circle-clipper right">
                            <div class="circle"></div>
                            </div>
                        </div>

                        <div class="spinner-layer spinner-yellow">
                            <div class="circle-clipper left">
                            <div class="circle"></div>
                            </div><div class="gap-patch">
                            <div class="circle"></div>
                            </div><div class="circle-clipper right">
                            <div class="circle"></div>
                            </div>
                        </div>

                        <div class="spinner-layer spinner-green">
                            <div class="circle-clipper left">
                            <div class="circle"></div>
                            </div><div class="gap-patch">
                            <div class="circle"></div>
                            </div><div class="circle-clipper right">
                            <div class="circle"></div>
                            </div>
                        </div>';

    $dati_societa = GetDatiSocieta();

    if($dati_societa['ragione_sociale'] != '' && $dati_societa['partita_iva'] != ''){

        if($dati_societa['codice_sia'] != ''){

            $dati_banche_presentazione = GetDatiBanchePresentazione();

            foreach ($ids as $id) {
                $q = "SELECT riba.kp_numero_riba numero,
                    riba.banca_pagamento banca,
                    riba.kp_importo importo,
                    riba.kp_stato_riba stato,
                    cc.kp_banca banca_cliente,
                    inv.invoiceid id_fattura,
                    inv.invoice_number numero_fattura
                    FROM {$table_prefix}_kpriba riba
                    INNER JOIN {$table_prefix}_scadenziario scad ON scad.scadenziarioid = riba.kp_scadenza
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = riba.kp_azienda
                    INNER JOIN {$table_prefix}_invoice inv ON inv.invoiceid = riba.kp_fattura
                    LEFT JOIN {$table_prefix}_kpconticorrenti cc ON cc.kpconticorrentiid = inv.kp_conto_corrente
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = riba.kpribaid
                    INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = scad.scadenziarioid
                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
                    INNER JOIN {$table_prefix}_crmentity ent3 ON ent3.crmid = inv.invoiceid
                    WHERE ent.deleted = 0 AND ent1.deleted = 0 
                    AND ent2.deleted = 0 AND ent3.deleted = 0
                    AND riba.kpribaid = ".$id;
                
                $res = $adb->query($q);
                if ($adb->num_rows($res) > 0) {
                    $numero = $adb->query_result($res, 0, 'numero');
                    $numero = html_entity_decode(strip_tags($numero), ENT_QUOTES, $default_charset);

                    $banca = $adb->query_result($res, 0, 'banca');
                    $banca = html_entity_decode(strip_tags($banca), ENT_QUOTES, $default_charset);
                    if($banca == null || $banca == '--Nessuno--'){
                        $banca = '';
                    }

                    $importo = $adb->query_result($res, 0, 'importo');
                    $importo = html_entity_decode(strip_tags($importo), ENT_QUOTES, $default_charset);
                    if($importo == '' || $importo == null){
                        $importo = 0;
                    }

                    $stato = $adb->query_result($res, 0, 'stato');
                    $stato = html_entity_decode(strip_tags($stato), ENT_QUOTES, $default_charset);

                    $banca_cliente = $adb->query_result($res, 0, 'banca_cliente');
                    $banca_cliente = html_entity_decode(strip_tags($banca_cliente), ENT_QUOTES, $default_charset);
                    if($banca_cliente == null || $banca_cliente == ''){
                        $banca_cliente = 0;
                    }

                    $id_fattura = $adb->query_result($res, 0, 'id_fattura');
                    $id_fattura = html_entity_decode(strip_tags($id_fattura), ENT_QUOTES, $default_charset);

                    $numero_fattura = $adb->query_result($res, 0, 'numero_fattura');
                    $numero_fattura = html_entity_decode(strip_tags($numero_fattura), ENT_QUOTES, $default_charset);

                    if($stato == 'Approvata'){

                        if($banca_cliente != 0){

                            if($banca != ''){

                                if(array_key_exists($banca, $dati_banche_presentazione)){

                                    $id_univoco = $dati_banche_presentazione[$banca]['iban'];
                                    $nome_flusso_cbi = 'Flusso CBI '.$banca;
                                    $nome_supporto = 'KPCBIRB'.date('dmyHis');
                                    $nome_file = 'KPCBIRB_'.date('YmdHis').'_'.$id_univoco.'.txt';

                                    if (empty($array_banche)) {
                                        $array_banche[] = $id_univoco;
                                        $array_flussi_cbi[$id_univoco] = array(
                                            "id" => $id_univoco,
                                            "nome" => $nome_flusso_cbi,
                                            "nome_supporto" => $nome_supporto,
                                            "nome_file" => $nome_file
                                        );
                                        $array_righe_cbi[$id_univoco] = array(
                                            "id" => $id_univoco,
                                            "righe" => 1,
                                            "importo" => $importo
                                        );
                                        $num_testate++;
                                    } else {
                                        if (in_array($id_univoco, $array_banche)) {
                                            $array_righe_cbi[$id_univoco]["righe"]++;
                                            $array_righe_cbi[$id_univoco]["importo"] += $importo;
                                        } else {
                                            $array_banche[] = $id_univoco;
                                            $array_flussi_cbi[$id_univoco] = array(
                                                "id" => $id_univoco,
                                                "nome" => $nome_flusso_cbi,
                                                "nome_supporto" => $nome_supporto,
                                                "nome_file" => $nome_file
                                            );
                                            $array_righe_cbi[$id_univoco] = array(
                                                "id" => $id_univoco,
                                                "righe" => 1,
                                                "importo" => $importo
                                            );
                                            $num_testate++;
                                        }
                                    }

                                    $num_righe++;
                                    $array_corrette[] = array(
                                        "id" => $id,
                                        "id_univoco" => $id_univoco
                                    );

                                }
                                else{

                                    if (empty($array_banche_controllo)) {
                                        $array_banche_controllo[] = $banca;
                                        $num_errori++;
                                    } else {
                                        if (in_array($banca, $array_banche_controllo)) {
                                            
                                        } else {
                                            $array_banche_controllo[] = $banca;
                                            $num_errori++;
                                        }
                                    }

                                }

                            }
                            else{

                                if($html_errori == ""){
                                    $html_errori .= "<h3>Errori:</h3>";
                                }
                
                                $html_errori .= "<p style='color:red;'> - La RIBA <a href='index.php?module=KpRIBA&action=DetailView&record=".$id."' target='_blank'>".$numero."</a> non ha la Banca di Presentazione compilata.</p>";

                            }
                        }
                        else{
                            
                            if($html_errori == ""){
                                $html_errori .= "<h3>Errori:</h3>";
                            }
            
                            $html_errori .= "<p style='color:red;'> - La fattura n. <a href='index.php?module=Invoice&action=DetailView&record=".$id_fattura."' target='_blank'>".$numero_fattura."</a> non ha il conto corrente del cliente compilato.</p>";

                        }
                    }
                    else{

                        if($html_errori == ""){
                            $html_errori .= "<h3>Errori:</h3>";
                        }

                        $html_errori .= "<p style='color:red;'> - La RIBA <a href='index.php?module=KpRIBA&action=DetailView&record=".$id."' target='_blank'>".$numero."</a> non è in stato Approvata.</p>";

                    }

                }
                $num_selezionate++;
            }

            foreach($array_flussi_cbi as $flusso_cbi){

                if($html_corretti == ""){
                    $html_corretti .= "<h3>Da esportare:</h3>";
                }

                $html_corretti .= "<p id='p_record_".$flusso_cbi["id"]."' class='p_record' style='color:green;display:inline;'> - ".$flusso_cbi['nome']." (n° righe ".$array_righe_cbi[$flusso_cbi["id"]]["righe"].") (importo ".number_format($array_righe_cbi[$flusso_cbi["id"]]["importo"],2,',','.')." €)</p>
                                <div style='vertical-align:middle;' id='load_testata_".$flusso_cbi["id"]."' class='caricamento preloader-wrapper small active'>".$html_caricamento."</div>
                                <p style='color:green;display:inline;' id='p_numero_righe_".$flusso_cbi["id"]."'></p>
                                <div style='vertical-align:middle;' id='load_righe_".$flusso_cbi["id"]."' class='caricamento preloader-wrapper small active'>".$html_caricamento."</div>
                                <a href='#' style='display:inline;' class='download_file' id='download_file_".$flusso_cbi["id"]."'></a><br/><br/>";

            }

            foreach($array_banche_controllo as $banca_controllo){

                if($html_errori == ""){
                    $html_errori .= "<h3>Errori:</h3>";
                }

                $html_errori .= "<p style='color:red;'> - Il conto ".$banca_controllo." non risulta essere presente nel sistema o non è stato codificato correttamente (verificare che la picklist della banca di presentazioni contenga l'IBAN della banca e che tale codice IBAN non presenti spaziature; una codifica corretta è ad esempio 'Banco Desio IBAN: IT13E0344060790000000456123').</p>";

            }

        }
        else{

            if($html_errori == ""){
                $html_errori .= "<h3>Errori:</h3>";
            }

            $html_errori .= "<p style='color:red;'> - Codice SIA non corretto <a href='index.php?module=Settings&action=KpCompanyInfo&parenttab=Settings&reset_session_menu=true' target='_blank'>CLICCA QUI</a>.</p>";

        }

    }
    else{

        if($html_errori == ""){
            $html_errori .= "<h3>Errori:</h3>";
        }

        $html_errori .= "<p style='color:red;'> - Ragione sociale o partita IVA non corretti <a href='index.php?module=Settings&action=OrganizationConfig&parenttab=Settings&reset_session_menu=true' target='_blank'>CLICCA QUI</a>.</p>";

    }

    $html .= "<div class='card' style='width: 98%; margin-right: 1%; margin-left: 1%; padding: 0px 10px;'>";
    $html .= "<h1>Esportazione RIBA in flusso CBI</h1></br>";
    $html .= "<h5><b>Selezionate ".$num_selezionate.", <span style='color:green;'>flussi ".$num_testate."</span>, <span style='color:green;'>righe ".$num_righe."</span>, <span style='color:red;'>errori ".$num_errori."</span></b></h5></br>";
    $html .= $html_corretti."<br/>";
    $html .= $html_errori."<br/>";
    $html .= "</div><br/><br/><br/><br/>";
    $html .= "<div id='sticky_footer_custom'>";
    $html .= '<button id="bottone_esporta" style="float:right;" class="btn waves-effect waves-light green" type="submit">Esporta<i class="material-icons right">arrow_downward</i></button>';
    $html .= "</div>";

    $html_popup .= "<p>Confermi di procedere con l'esportazione di ".$num_testate." flussi CBI?</p>";

    $json = json_encode($array_corrette);
    $json2 = json_encode($array_righe_cbi);
    $json3 = json_encode($array_flussi_cbi);
    
}
else{
    $html .= "error";
}

?>

<html>
    <head>
        <title>Esportazione RIBA in flusso CBI</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
        
        <link rel="stylesheet" type="text/css" href="modules/SproCore/CustomViews/KpPopupEsportazioneRIBA/css/style.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link type="text/css" rel="stylesheet" href="modules/SproCore/CustomViews/KpPopupEsportazioneRIBA/css/materialize.min.css"  media="screen,projection"/>

        <script src="modules/SproCore/CustomViews/KpPopupEsportazioneRIBA/js/jquery-2.1.4.min.js"></script>  
		<script type="text/javascript" src="modules/SproCore/CustomViews/KpPopupEsportazioneRIBA/js/materialize.min.js"></script>
		<script src="modules/SproCore/CustomViews/KpPopupEsportazioneRIBA/js/general.js"></script>

        <script type="text/JavaScript">
            var num_righe = "<?php echo($num_righe); ?>";   
            var array_corrette = '<?php print $json; ?>'; 
            var array_righe_cbi = '<?php print $json2; ?>'; 
            var array_flussi_cbi = '<?php print $json3; ?>'; 
        </script>
    </head>
    <body>

        <div id="general" style="position: relative;">
            <?php echo($html); ?>
        </div>

        <!-- POPUP -->

        <div id="popup_conferma_esportazione" class="modal">
            <div class="modal-content">               
                <?php echo($html_popup); ?>
            </div>
            <div class="modal-footer">
                <button id="chiudi_popup_esportazione" style="float:left;" class="btn waves-effect waves-light red" type="submit">Annulla<i class="material-icons left">close</i></button>
                <button id="conferma_popup_esportazione" class="btn waves-effect waves-light green" type="submit">Conferma<i class="material-icons right">done</i></button>
            </div>
        </div>

    </body>
</html>