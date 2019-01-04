<?php

date_default_timezone_set('Europe/London');

require_once(__DIR__.'/BI_utils.php');
require_once(__DIR__.'/phpexcel/PHPExcel.php');

include_once('../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $default_charset, $site_URL;
session_start();

$data_corrente_inv = date('d-m-Y');

if(!isset($_SESSION['authenticated_user_id'])){
    header("Location: ".$site_URL."/index.php?module=Accounts&action=index");
    die;
}
$current_user->id = $_SESSION['authenticated_user_id'];

$is_admin = 'off';

$q = "SELECT us.is_admin
    FROM {$table_prefix}_users us
    WHERE us.id = ".$current_user->id;
$res = $adb->query($q);
if($adb->num_rows($res) > 0){
    $is_admin = $adb->query_result($res, 0, 'is_admin');
    $is_admin = html_entity_decode(strip_tags($is_admin), ENT_QUOTES,$default_charset);
    if($is_admin == '' || $is_admin == null){
        $is_admin = 'off';
    }
}

if($is_admin == 'on'){
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
            
        $objPHPExcel = new PHPExcel();
        
        $objPHPExcel->getProperties()->setCreator("Spro")
                ->setLastModifiedBy("Spro")
                ->setTitle("Excel Dettaglio Servizi ".$data_corrente_inv)
                ->setSubject("Excel Dettaglio Servizi ".$data_corrente_inv)
                ->setDescription("Excel Dettaglio Servizi ".$data_corrente_inv." for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Excel Dettaglio Servizi ".$data_corrente_inv);

        // FOGLIO FILTRI
        $objPHPExcel->createSheet(1);

        $objPHPExcel->setActiveSheetIndex(1);

        $objPHPExcel->getActiveSheet()->mergeCells('A1:B1');
        
        if($lingua == 'it_it'){
            $objPHPExcel->getActiveSheet()->setTitle("FILTRI");

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1', 'FILTRI')
                ->setCellValue('A2', 'Anno')
                ->setCellValue('A3', 'Anno di Confronto')
                ->setCellValue('A4', 'Anno Budget')
                ->setCellValue('A5', 'Mese Da')
                ->setCellValue('A6', 'Mese A')
                ->setCellValue('A7', 'Clienti')
                ->setCellValue('A8', 'Servizi')
                ->setCellValue('A9', 'Business Unit')
                ->setCellValue('A10', 'Agenti')
                ->setCellValue('A11', 'Tipologie di Fatturato')
                ->setCellValue('A12', 'Area Aziendale')
                ->setCellValue('A13', 'Categoria')
                ->setCellValue('A14', 'Ordinato/Fatturato/Budget')
                ->setCellValue('A15', 'Valore/Quantità')
                ->setCellValue('A16', 'Numero Servizio')
                ->setCellValue('A17', 'Nome Servizio');
        }
        else{
            $objPHPExcel->getActiveSheet()->setTitle("FILTERS");

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1', 'FILTERS')
                ->setCellValue('A2', 'Year')
                ->setCellValue('A3', 'Comparison Year')
                ->setCellValue('A4', 'Budget Year')
                ->setCellValue('A5', 'From Month')
                ->setCellValue('A6', 'To Month')
                ->setCellValue('A7', 'Customers')
                ->setCellValue('A8', 'Services')
                ->setCellValue('A9', 'Business Unit')
                ->setCellValue('A10', 'Agents')
                ->setCellValue('A11', 'Revenue Types')
                ->setCellValue('A12', 'Corporate Segment')
                ->setCellValue('A13', 'Category')
                ->setCellValue('A14', 'Orders/Revenue/Budget')
                ->setCellValue('A15', 'Value/Quantity')
                ->setCellValue('A16', 'Service Number')
                ->setCellValue('A17', 'Service Name');
        }
        
        $objPHPExcel->getActiveSheet()
            ->setCellValue('B2', $anno)
            ->setCellValue('B3', $anno_confr)
            ->setCellValue('B4', $anno_budget)
            ->setCellValue('B5', GetDescrizioneMese($mese_da, $lingua))
            ->setCellValue('B6', GetDescrizioneMese($mese_a, $lingua))
            ->setCellValue('B7', GetNomiClienti($clienti))
            ->setCellValue('B8', GetNomiServizi($servizi))
            ->setCellValue('B9', GetNomiBusinessUnit($business_unit))
            ->setCellValue('B10', GetNomiAgenti($agenti))
            ->setCellValue('B11', GetNomiTipologieFatturato($tipologie_fatturato))
            ->setCellValue('B12', GetValorePicklist('area_aziendale', 'Services', $lingua, $area_aziendale))
            ->setCellValue('B13', GetValorePicklist('servicecategory', 'Services', $lingua, $categoria))
            ->setCellValue('B14', $ordinato_fatturato)
            ->setCellValue('B15', $quantita_valore)
            ->setCellValue('B16', $codice)
            ->setCellValue('B17', $nome);

        $objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle("A2:A17")->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle("A2:B17")->getAlignment()->setWrapText(true);

        $style_alignment_horizontal_center = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle("A1")->applyFromArray($style_alignment_horizontal_center);

        $style_alignment_horizontal_left = array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            )
        );
        $objPHPExcel->getActiveSheet()->getStyle("B2:B17")->applyFromArray($style_alignment_horizontal_left);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);

        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        // FOGLIO DATI
        $objPHPExcel->setActiveSheetIndex(0);

        if($lingua == 'it_it'){
            $objPHPExcel->getActiveSheet()->setTitle("DATI");

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1', 'Numero')
                ->setCellValue('B1', 'Nome Servizio')
                ->setCellValue('C1', 'Area Aziendale')
                ->setCellValue('D1', 'Categoria')
                ->setCellValue('G1', 'Perc.');
        }
        else{
            $objPHPExcel->getActiveSheet()->setTitle("DATA");

            $objPHPExcel->getActiveSheet()
                ->setCellValue('A1', 'Number')
                ->setCellValue('B1', 'Service Name')
                ->setCellValue('C1', 'Corporate Segment')
                ->setCellValue('D1', 'Category')
                ->setCellValue('G1', 'Perc.');
        }

        if($ordinato_fatturato == 'fatturato'){
            if($lingua == 'it_it'){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('E1', 'Fatturato Confr.')
                    ->setCellValue('F1', 'Fatturato');
            }
            else{
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('E1', 'Revenue Comp.')
                    ->setCellValue('F1', 'Revenue');
            }
        }
        else if($ordinato_fatturato == 'budget'){
            if($lingua == 'it_it'){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('E1', 'Fatturato')
                    ->setCellValue('F1', 'Budget');
            }
            else{
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('E1', 'Revenue')
                    ->setCellValue('F1', 'Budget');
            }
        }
        else if($ordinato_fatturato == 'ordinato'){
            if($lingua == 'it_it'){
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('E1', 'Ordinato Confr.')
                    ->setCellValue('F1', 'Ordinato');
            }
            else{
                $objPHPExcel->getActiveSheet()
                    ->setCellValue('E1', 'Orders Comp.')
                    ->setCellValue('F1', 'Orders');
            }
        }
        
        $numero_riga = 2;
        
        $lista_servizi = getListaServizi($servizi, $area_aziendale, $categoria, $codice, $nome, $ordinamento, $lingua);
        
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
            
                $fatturato_tot_testo = number_format($fatturato_tot,2,',','.');
                $fatturato_confr_tot_testo = number_format($fatturato_confr_tot,2,',','.');
                $percentuale_testo = number_format($percentuale,2,',','.')." %";

                $column = 'A';

                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $codice_servizio);
                $column++;
                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $nome_servizio);
                $column++;
                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $area_aziendale_servizio);
                $column++;
                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $categoria_servizio);
                $column++;
                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $fatturato_confr_tot);
                $column++;
                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $fatturato_tot);
                $column++;
                $objPHPExcel->getActiveSheet()->setCellValue($column.$numero_riga, $percentuale_testo);

                $numero_riga++;

            }
        }
        
        $numero_riga--;

        /* kpro@bid040120191730 */
        $excel_number_format = $dati_valuta['simbolo'].' #,##0.00';

        $objPHPExcel->getActiveSheet()
            ->getStyle('E2:E'.$numero_riga)
            ->getNumberFormat()
            ->setFormatCode($excel_number_format);
        $objPHPExcel->getActiveSheet()
            ->getStyle('F2:F'.$numero_riga)
            ->getNumberFormat()
            ->setFormatCode($excel_number_format);
        /* kpro@bid040120191730 end */

        $objPHPExcel->getActiveSheet()->getStyle("A1:G1")->getFont()->setBold(true);

        $objPHPExcel->getActiveSheet()->getStyle("A1:".$column.$numero_riga)->getAlignment()->setWrapText(true);

        $column = 'A';
        for ($k = 0; $k < 7; $k++) {
            if($k == 1){
                $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(50);
            }
            else{
                $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setWidth(25);
            }
            $column++;
        }

        $objPHPExcel->getActiveSheet()
            ->getPageSetup()
            ->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
            
        $path_temp = __DIR__.'/temp/';
        if($lingua == 'it_it'){
            $filename = date("YmdHis")."_Excel_Dettaglio_Servizi";
        }
        else{
            $filename = date("YmdHis")."_Excel_Services_Details";
        }
        $excel_type = 'Excel5';
        $excel_ext = 'xls';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $excel_type);
        $objWriter->save($path_temp.$filename.'.'.$excel_ext);

        if(file_exists($path_temp.$filename.'.'.$excel_ext)){
            echo $filename.'.'.$excel_ext;
        }
    }
}
