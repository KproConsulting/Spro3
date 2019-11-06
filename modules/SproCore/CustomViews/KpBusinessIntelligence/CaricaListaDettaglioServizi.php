<?php

require_once('BI_utils.php');

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $default_charset, $site_URL;
session_start();

$debug = false;
if($debug){
    LogBI(basename(__FILE__, '.php'), 'w+');
    LogBI(basename(__FILE__, '.php'), 'w+', 'log_query');
}

$rows = array();
if (isset($_POST['anno']) && isset($_POST['anno_confr']) && isset($_POST['anno_budget']) && isset($_POST['mese_da']) && isset($_POST['mese_a']) 
    && isset($_POST['clienti']) && isset($_POST['servizi']) && isset($_POST['business_unit']) && isset($_POST['agenti']) 
    && isset($_POST['utenti']) && isset($_POST['tipologie_fatturato'])  && isset($_POST['utenti']) && isset($_POST['area_aziendale']) && isset($_POST['categoria']) 
    && isset($_POST['lingua']) && isset($_POST['ordinato_fatturato']) && isset($_POST['quantita_valore']) && isset($_POST['id_valuta'])
    && isset($_POST['codice']) && isset($_POST['nome']) && isset($_POST['ordinamento'])) {

    $anno = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['anno']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $anno = substr($anno, 0, 100);

    $anno_confr = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['anno_confr']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $anno_confr = substr($anno_confr, 0, 100);

    $anno_budget = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['anno_budget']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $anno_budget = substr($anno_budget, 0, 100);

    $mese_da = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['mese_da']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $mese_da = substr($mese_da, 0, 100);
    $mese_da = ltrim($mese_da, '0');

    $mese_a = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['mese_a']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $mese_a = substr($mese_a, 0, 100);
    $mese_a = ltrim($mese_a, '0');

    $clienti = json_decode(stripslashes($_POST['clienti']));

    $servizi = json_decode(stripslashes($_POST['servizi']));

    $business_unit = json_decode(stripslashes($_POST['business_unit']));

    $agenti = json_decode(stripslashes($_POST['agenti']));

    $utenti = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['utenti']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $utenti = substr($utenti, 0, 100);

    $tipologie_fatturato = json_decode(stripslashes($_POST['tipologie_fatturato']));

    $area_aziendale = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['area_aziendale']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $area_aziendale = substr($area_aziendale, 0, 100);
    
    $categoria = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['categoria']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $categoria = substr($categoria, 0, 100);

    $ordinato_fatturato = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['ordinato_fatturato']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $ordinato_fatturato = substr($ordinato_fatturato, 0, 100);

    $quantita_valore = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['quantita_valore']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $quantita_valore = substr($quantita_valore, 0, 100);

    $lingua = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['lingua']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $lingua = substr($lingua, 0, 100);

    $id_valuta = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['id_valuta']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $id_valuta = substr($id_valuta, 0, 100);

    $codice = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['codice']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $codice = substr($codice, 0, 100);

    $nome = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['nome']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $nome = substr($nome, 0, 100);

    $ordinamento = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_POST['ordinamento']), ENT_QUOTES, $default_charset)), ENT_QUOTES, $default_charset);
    $ordinamento = substr($ordinamento, 0, 100);

    $dati_valuta = GetDatiValuta($id_valuta);

    $array_dati_generici = array(
        'quantita_valore' => $quantita_valore,
        'tasso_valuta' => $dati_valuta['tasso'],
        'utenti' => $utenti,
        'clienti' => $clienti,
        'business_unit' => $business_unit,
        'agenti' => $agenti,
        'tipologie_fatturato' => $tipologie_fatturato
    );
    
    if($debug){
        LogBI('FILTRI: '.PrintArray($array_dati_generici));
    }
    
    $lista_servizi = getListaServizi($servizi, $area_aziendale, $categoria, $codice, $nome, $ordinamento, $lingua, '0,20', $anno, $mese_da, $mese_a); /* kpro@bid040120191730 */

    foreach($lista_servizi as $servizio){
        
        $id_servizio = $servizio["id"];
        $codice_servizio = $servizio["codice"];
        $nome_servizio = $servizio["nome"];
        $area_aziendale_servizio = $servizio["area_aziendale"];
        $categoria_servizio = $servizio["categoria"];

        $array_dati_generici['servizi'] = array($id_servizio);

        $fatturato_tot = 0;
        $fatturato_confr_tot = 0;

        for($i = $mese_da; $i <= $mese_a; $i++){
            $numero_mese = $i;

            //SWITCH = Fatturato
            if($ordinato_fatturato == 'fatturato'){
                //Fatturato ANNO
                $fatturato = GetFatturato($anno, $numero_mese, $array_dati_generici);

                //Fatturato ANNO CONFR
                $fatturato_confr = GetFatturato($anno_confr, $numero_mese, $array_dati_generici);
            }
            //SWITCH = Budget
            else if($ordinato_fatturato == 'budget'){
                //Fatturato ANNO
                $fatturato = GetFatturato($anno, $numero_mese, $array_dati_generici);

                //Budget ANNO BUDGET
                $budget = GetBudget($anno_budget, $numero_mese, $array_dati_generici);
                
                $fatturato_confr = $budget;
            }
            //SWITCH = Ordinato
            else if($ordinato_fatturato == 'ordinato'){
                //Ordinato ANNO
                $ordinato = GetOrdinato($anno, $numero_mese, $array_dati_generici);

                $fatturato = $ordinato;
                //Ordinato ANNO CONFR
                $ordinato_confr = GetOrdinato($anno_confr, $numero_mese, $array_dati_generici);

                $fatturato_confr = $ordinato_confr;
            }

            $fatturato_tot += $fatturato;
            $fatturato_confr_tot += $fatturato_confr;
        }

        if($fatturato_tot != 0 && $fatturato_confr_tot != 0){

            if($fatturato_tot != 0){
                $percentuale = (($fatturato_tot - $fatturato_confr_tot)*100)/$fatturato_tot;
            }
            else{
                $percentuale = 0;
            }
        
            $fatturato_tot = number_format($fatturato_tot,2,',','.');
            $fatturato_confr_tot = number_format($fatturato_confr_tot,2,',','.');
            $percentuale_numero = number_format($percentuale,2,'.','');
            $percentuale_testo = number_format($percentuale,2,',','.')." %";
            
            if($quantita_valore == 'valore'){
                $fatturato_tot = $dati_valuta['simbolo']." ".$fatturato_tot;
                $fatturato_confr_tot = $dati_valuta['simbolo']." ".$fatturato_confr_tot;
            }

            $rows[] = array(
                "codice_servizio" => $codice_servizio,
                "nome_servizio" => $nome_servizio,
                "area_aziendale" => $area_aziendale_servizio,
                "categoria" => $categoria_servizio,
                "fatturato" => $fatturato_tot,
                "fatturato_confr" => $fatturato_confr_tot,
                "percentuale_numero"=> $percentuale_numero,
                "percentuale_testo"=>$percentuale_testo
            );

        }

    }

}

$json = json_encode($rows);
print $json;

?>