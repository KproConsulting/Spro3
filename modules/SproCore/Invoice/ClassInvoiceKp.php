<?php 
require_once('modules/Invoice/Invoice.php');
require_once('modules/SproCore/KpProvvigioni/ClassKpProvvigioniKp.php');

class InvoiceKp extends Invoice {

    //Script modifica Related List
    var $list_fields = Array();

    var $list_fields_name = Array(
        'Invoice Number'=>'invoice_number',
        'Subject'=>'subject',
        'Invoice Date'=>'invoicedate',
        'Tipo Documento'=>'kp_tipo_documento',
        'Account Name'=>'account_id',
        'Total'=>'hdnGrandTotal',
        'Business Unit'=>'kp_business_unit',
        'Assigned To'=>'assigned_user_id'
   );

    function InvoiceKp(){
        global $table_prefix;
        parent::__construct();
        $this->defaultInventoryEntityType = 'Services'; 
        $this->list_fields = Array(
           'Invoice Number'=>Array($table_prefix.'_invoice'=>'invoice_number'),
           'Subject'=>Array($table_prefix.'_invoice'=>'subject'),
           'Invoice Date'=>Array($table_prefix.'_invoice'=>'invoicedate'),
           'Tipo Documento'=>Array($table_prefix.'_invoice'=>'kp_tipo_documento'),
           'Account Name'=>Array($table_prefix.'_invoice'=>'accountid'),
           'Total'=>Array($table_prefix.'_invoice'=>'total'),
           'Business Unit'=>Array($table_prefix.'_invoice'=>'kp_business_unit'),
           'Assigned To'=>Array($table_prefix.'_crmentity'=>'smownerid') 
        );
    }
	
    function save_module($module){

        global $table_prefix, $adb;

        parent::save_module($module); 

        require_once('Invoice_utils.php');
        /* kpro@bid130920181600 */
        if($this->column_fields['invoicedate'] != null && $this->column_fields['invoicedate'] != null && $this->column_fields['invoicedate'] != '0000-00-00'){
            aggiornaDataFatturaOdF($this->id, $this->column_fields['invoicedate']); 
        }
        /* kpro@bid130920181600 end */
        if($this->column_fields['kp_avviso_fattura'] == 'on' || $this->column_fields['kp_avviso_fattura'] == '1' || $this->column_fields['kp_avviso_fattura'] == 1){
            $avviso_fattura = true;
        }
        else{
            $avviso_fattura = false;
        }

        if($this->column_fields['invoice_number'] == '' 
        && ($this->column_fields['invoicestatus'] == 'Approved' || $this->column_fields['invoicestatus'] == 'Sent') 
        && ($this->column_fields['kp_business_unit'] != '' && $this->column_fields['kp_business_unit'] != 0 && $this->column_fields['kp_business_unit'] != null)){ 

            recuperaNumeroFattura($this->id);

        }

        /* kpro@bid250920181215 */
        if( ($this->column_fields['kp_tipo_documento'] == 'Nota di credito' || $this->column_fields['kp_tipo_documento'] == 'Fattura di acconto')
            && ($this->column_fields['invoicestatus'] == 'Approved' || $this->column_fields['invoicestatus'] == 'Sent') ){ 

            generaOdFdaFattura($this->id);
        }
        /* kpro@bid250920181215 end */

        if( ($this->column_fields['kp_tipo_documento'] == 'Fattura' && $this->column_fields['invoicestatus'] == 'Approved')
        || ($this->column_fields['kp_tipo_documento'] == 'Fattura di acconto' && $this->column_fields['invoicestatus'] == 'Approved'
        && $this->column_fields['salesorder_id'] != '' && $this->column_fields['salesorder_id'] != null && $this->column_fields['salesorder_id'] != 0 ) ){ /* kpro@bid250920181215 */
            
            KpProvvigioniKp::generaProvvigioniDaFattura( $this->id );

        }

        if($this->column_fields['mod_pagamento'] != '' && $this->column_fields['mod_pagamento'] != null && $this->column_fields['mod_pagamento'] != 0 
        && ($this->column_fields['kp_tipo_documento'] == 'Fattura' || $this->column_fields['kp_tipo_documento'] == 'Fattura di acconto')
        && (($avviso_fattura && ($this->column_fields['invoicestatus'] == 'Approvata Proforma' || $this->column_fields['invoicestatus'] == 'Spedita Proforma'))
        || (!$avviso_fattura && ($this->column_fields['invoicestatus'] == 'Approved' || $this->column_fields['invoicestatus'] == 'Sent')))){ /* kpro@bid250920181215 */
        
            generaScadenzeFattura($this->id);

        }

        $this->setImponibileFattura(); //kpro@tom310120191640
        $this->setCassa();  //kpro@tom310120191640
        $this->setRitenuta(); //kpro@tom240120191400
        $this->setTotaleFattura();  //kpro@tom310120191640
        $this->setTotaleTasseFattura(); //kpro@tom310120191640

    }

    /* kpro@tom310120191640 */

    function setCassa(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_imponibile = $this->getImponibileFattura();

        $applica_cassa = $this->column_fields["kp_applica_cassa"];
        $applica_cassa = html_entity_decode(strip_tags($applica_cassa), ENT_QUOTES, $default_charset);
        if( $applica_cassa == 'on' || $applica_cassa == '1' ){
            $applica_cassa = true;
        }
        else{
            $applica_cassa = false;
        }

        $aliquota_cassa = $this->column_fields["kp_aliquota_cassa"];
        $aliquota_cassa = html_entity_decode(strip_tags($aliquota_cassa), ENT_QUOTES, $default_charset);
        if( $aliquota_cassa == null || $aliquota_cassa == '' ){
            $aliquota_cassa = 0;
        }

        $aliquota_iva_cassa = $this->column_fields["kp_aliq_iva_cassa"];
        $aliquota_iva_cassa = html_entity_decode(strip_tags($aliquota_iva_cassa), ENT_QUOTES, $default_charset);
        if( $aliquota_iva_cassa == null || $aliquota_iva_cassa == '' ){
            $aliquota_iva_cassa = 0;
        }

        if( $applica_cassa && $aliquota_cassa != 0 ){

            $imponibile_cassa = $total_imponibile * $aliquota_cassa / 100;

            if( $aliquota_iva_cassa != 0 ){
                $totale_iva_cassa = $imponibile_cassa * $aliquota_iva_cassa / 100;
                $importo_cassa = $imponibile_cassa + $totale_iva_cassa;
            }
            else{
                $totale_iva_cassa = 0;
                $importo_cassa = $imponibile_cassa;
            }

        }
        else{
            $totale_iva_cassa = 0;
            $importo_cassa = 0;
            $imponibile_cassa = 0;
        }

        $update = "UPDATE {$table_prefix}_invoice SET
                    kp_imponibile_cassa = ".$imponibile_cassa.",
                    kp_tot_iva_cassa = ".$totale_iva_cassa.",
                    kp_importo_cassa = ".$importo_cassa."
                    WHERE invoiceid = ".$this->id;

        $adb->query($update);

    }

    function setTotaleTasseFattura(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_tasse = $this->getTotaleTasseFattura();

        $update = "UPDATE {$table_prefix}_invoice SET
                    kp_tot_iva_fat = ".$total_tasse."
                    WHERE invoiceid = ".$this->id;

        $adb->query($update);

    }

    /* kpro@tom310120191640 end */

    /* kpro@tom240120191400 */
    function setRitenuta(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_imponibile = $this->getImponibileFattura();

        $applica_ritenuta = $this->column_fields["kp_applica_ritenuta"];
        $applica_ritenuta = html_entity_decode(strip_tags($applica_ritenuta), ENT_QUOTES, $default_charset);
        if( $applica_ritenuta == 'on' || $applica_ritenuta == '1' ){
            $applica_ritenuta = true;
        }
        else{
            $applica_ritenuta = false;
        }

        $aliquota_ritenuta = $this->column_fields["kp_aliquota_ritenuta"];
        $aliquota_ritenuta = html_entity_decode(strip_tags($aliquota_ritenuta), ENT_QUOTES, $default_charset);
        if( $aliquota_ritenuta == null || $aliquota_ritenuta == '' ){
            $aliquota_ritenuta = 0;
        }

        if( $applica_ritenuta && $aliquota_ritenuta != 0 ){
            $importo_ritenuta = $total_imponibile * $aliquota_ritenuta / 100;
        }
        else{
            $importo_ritenuta = 0;
        }

        $update = "UPDATE {$table_prefix}_invoice SET
                    kp_importo_ritenuta = ".$importo_ritenuta."
                    WHERE invoiceid = ".$this->id;

        $adb->query($update);

    }

    function setImponibileFattura(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_imponibile = $this->getImponibileFattura();

        $update = "UPDATE {$table_prefix}_invoice SET
                    kp_tot_imponibile = ".$total_imponibile."
                    WHERE invoiceid = ".$this->id;
        
        $adb->query($update);

    }

    function getImponibileFattura($where = "tax.percentage > 0"){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_imponibile = 0;

        $query = "SELECT 
                    COALESCE(SUM(rel.total_notaxes), 0) total_imponibile
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.productid
                    INNER JOIN kp_inventoryproductrel kprel ON kprel.lineitem_id = rel.lineitem_id AND kprel.id = rel.id
                    INNER JOIN {$table_prefix}_inventorytaxinfo tax ON tax.taxname = kprel.id_tassa
                    WHERE rel.id = ".$this->id;
        
        if( $where != "" ){
            $query .= " AND ".$where;
        }

        //file_put_contents( __DIR__."/kp_log.txt", $query );

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $total_imponibile = $adb->query_result($result_query, 0, 'total_imponibile');
            $total_imponibile = html_entity_decode(strip_tags($total_imponibile), ENT_QUOTES, $default_charset);

        }

        return $total_imponibile;

    }

    function setTotaleFattura(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_fattura = $this->getTotaleFattura();

        /* kpro@tom310120191640 */
        $importo_ritenuta = $this->column_fields["kp_importo_ritenuta"];
        $importo_ritenuta = html_entity_decode(strip_tags($importo_ritenuta), ENT_QUOTES, $default_charset);
        if( $importo_ritenuta == null || $importo_ritenuta == '' ){
            $importo_ritenuta = 0;
        }

        $totale_da_pagare = $total_fattura - $importo_ritenuta;

        /* kpro@tom310120191640 end */

        $update = "UPDATE {$table_prefix}_invoice SET
                    kp_tot_fattura = ".$total_fattura.",
                    kp_tot_da_pagare = ".$totale_da_pagare."
                    WHERE invoiceid = ".$this->id;

        $adb->query($update);

    }

    function getTotaleFattura(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_fattura = 0;

        $total_fattura = $this->column_fields["hdnGrandTotal"];
        $total_fattura = html_entity_decode(strip_tags($total_fattura), ENT_QUOTES, $default_charset);

        /* kpro@tom310120191640 */
        $importo_cassa = $this->column_fields["kp_importo_cassa"];
        $importo_cassa = html_entity_decode(strip_tags($importo_cassa), ENT_QUOTES, $default_charset);
        if( $importo_cassa == null || $importo_cassa == '' ){
            $importo_cassa = 0;
        }

        $total_fattura = $total_fattura + $importo_cassa;
        /* kpro@tom310120191640 end */

        $split_payment = $this->column_fields["kp_split_payment"];
        $split_payment = html_entity_decode(strip_tags($split_payment), ENT_QUOTES, $default_charset);
        if( $split_payment == 'on' || $split_payment == '1' ){

            $total_tasse = $this->getTotaleTasseFattura();
            $total_fattura = $total_fattura - $total_tasse;

        }

        return $total_fattura;

    }

    function getTotaleTasseFattura($where = "tax.percentage > 0"){
        global $adb, $table_prefix, $current_user, $default_charset;

        $total_tasse = 0;

        $query = "SELECT 
                    COALESCE(SUM(rel.tax_total), 0) total_tasse
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.productid
                    INNER JOIN kp_inventoryproductrel kprel ON kprel.lineitem_id = rel.lineitem_id AND kprel.id = rel.id
                    INNER JOIN {$table_prefix}_inventorytaxinfo tax ON tax.taxname = kprel.id_tassa
                    WHERE rel.id = ".$this->id;
        
        if( $where != "" ){
            $query .= " AND ".$where;
        }

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $total_tasse = $adb->query_result($result_query, 0, 'total_tasse');
            $total_tasse = html_entity_decode(strip_tags($total_tasse), ENT_QUOTES, $default_charset);

        }

        /* kpro@tom310120191640 */

        $tot_iva_cassa = $this->column_fields["kp_tot_iva_cassa"];
        $tot_iva_cassa = html_entity_decode(strip_tags($tot_iva_cassa), ENT_QUOTES, $default_charset);
        if( $tot_iva_cassa == null || $tot_iva_cassa == '' ){
            $tot_iva_cassa = 0;
        }

        $total_tasse = $total_tasse + $tot_iva_cassa;

        /* kpro@tom310120191640 end */

        return $total_tasse;

    }

    /* kpro@tom240120191400 end */

    function getFattureElettroniche($array){
        global $adb, $table_prefix, $current_user, $default_charset;

        //print_r($array);die;

        $fatture_elaborate = array();

        $path_temp = __DIR__.'/../CustomViews/KpPopupFatturazioneElettronica/temp/'.date("YmdHis")."_".rand(0 , 100000).'/';

        foreach( $array as $id ){

            //printf("\n".$id);

            $check_xml = $this->checkEsisteDocumentoFatturaElettronica($id);

            if( !$check_xml["esiste"] && !in_array($id, $fatture_elaborate) ){

                //Il programma creerà un file XML per ogni fattura
                $this->getFatturaElettronica($id, $path_temp);
                $fatture_elaborate[] = $id;

            }

        }

        if( count($fatture_elaborate) > 0 ){

            $nome_zip = "Fatture_elettroniche_".date("YmdHis").".zip";

            $this->zipPath($nome_zip, $path_temp);

            $file_zip = $path_temp.$nome_zip;

            if (file_exists($file_zip)) {

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.basename($file_zip).'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: '.filesize($file_zip));
                readfile($file_zip);

                @unlink($file_zip);

            }

            $list_file = scandir($path_temp); 
            foreach ($list_file as $file){
                if( !in_array( $file, array(".","..") ) ){
                    @unlink($path_temp.$file);
                }
            }

            @rmdir($path_temp);

        }

    }

    /* kpro@tom240120191400 */
    function getListaFile($path){
        global $adb, $table_prefix, $default_charset, $current_user, $root_directory;

        $da_escludere = array(".", "..");

        $lista_file = array();

        if( is_dir($path) ){

            foreach( scandir($path) as $file ) {

                if( is_readable($path.$file) && is_file($path.$file) && !in_array($path.$file, $da_escludere) ){

                    $lista_file[] = $file;

                }

            }

        }

        return $lista_file;

    }

    function zipPath($nome_zip, $path){
        global $adb, $table_prefix, $default_charset, $current_user, $root_directory;

        /*$command = "python3 ".__DIR__."/kpZipArchive.py ".$nome_zip." ".$path;  
        exec($command, $out, $status);*/

        $zip = new ZipArchive();

        if( $zip->open($path.$nome_zip, ZipArchive::CREATE|ZipArchive::OVERWRITE) !== TRUE ) {
            exit("cannot open <$nome_zip>\n");
        }

        $lista_file = $this->getListaFile($path);
        
        foreach($lista_file as $file){

            $zip->addFile($path.$file, $file);

        }

        $zip->close();  

    }
    /* kpro@tom240120191400 end */

    function getFatturaElettronica($id, $save_path){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $cliente = $focus_fattura->column_fields["account_id"];
        $cliente = html_entity_decode(strip_tags($cliente), ENT_QUOTES, $default_charset);

        $focus_cliente = CRMEntity::getInstance('Accounts');
        $focus_cliente->retrieve_entity_info($cliente, "Accounts", $dieOnError=false); 

        $formato_trasmissione = $focus_cliente->column_fields["kp_formato_trasm"];
        $formato_trasmissione = html_entity_decode(strip_tags($formato_trasmissione), ENT_QUOTES, $default_charset);
        if( $formato_trasmissione == null ){
            $formato_trasmissione = "FPR12";
        }

        $domtree = new DOMDocument( '1.0', 'UTF-8' );

        $xslt = $domtree->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="fatturaordinaria_v1.2.1.xsl"');
        $xslt = $domtree->appendChild($xslt);

        $FatturaElettronica = $domtree->createElement( "p:FatturaElettronica" );
        $FatturaElettronica = $domtree->appendChild( $FatturaElettronica );

        $domAttribute_xmlns_ds = $domtree->createAttribute('xmlns:ds');
        $domAttribute_xmlns_p = $domtree->createAttribute('xmlns:p');
        $domAttribute_xmlns_xsi = $domtree->createAttribute('xmlns:xsi');
        $domAttribute_versione = $domtree->createAttribute('versione');

        $domAttribute_xmlns_ds->value = 'http://www.w3.org/2000/09/xmldsig#';
        $domAttribute_xmlns_p->value = 'http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2';
        $domAttribute_xmlns_xsi->value = 'http://www.w3.org/2001/XMLSchema-instance';
        $domAttribute_versione->value = $formato_trasmissione;

        $FatturaElettronica->appendChild($domAttribute_xmlns_ds);
        $FatturaElettronica->appendChild($domAttribute_xmlns_p);
        $FatturaElettronica->appendChild($domAttribute_xmlns_xsi);
        $FatturaElettronica->appendChild($domAttribute_versione);
 
        $domtree = $this->getHeaderFatturaElettronica($domtree, $id);

        $domtree = $this->getBodyFatturaElettronica($domtree, $id);

        $this->salvaFatturaElettronica($domtree, $id, $save_path);

    }

    function salvaFatturaElettronica(DOMDocument $domtree, $id, $save_path){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $business_unit = $focus_fattura->column_fields["kp_business_unit"];
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES, $default_charset);

        $progressivo_invio = $focus_fattura->column_fields["kp_prog_inv_fe"];
        $progressivo_invio = html_entity_decode(strip_tags($progressivo_invio), ENT_QUOTES, $default_charset);

        $cliente = $focus_fattura->column_fields["account_id"];
        $cliente = html_entity_decode(strip_tags($cliente), ENT_QUOTES, $default_charset);

        $focus_bu = CRMEntity::getInstance('KpBusinessUnit');
        $focus_bu->retrieve_entity_info($business_unit, "KpBusinessUnit", $dieOnError=false); 

        $codice_trasmittente = $focus_bu->column_fields["kp_cod_fisc_trasm"];
        $codice_trasmittente = html_entity_decode(strip_tags($codice_trasmittente), ENT_QUOTES, $default_charset);

        $codice_nazione_trasmittente = $focus_bu->column_fields["kp_codice_naz_trasm"];
        $codice_nazione_trasmittente = html_entity_decode(strip_tags($codice_nazione_trasmittente), ENT_QUOTES, $default_charset);

        $file_name = $codice_nazione_trasmittente.$codice_trasmittente.'_'.$progressivo_invio.'.xml';

        $file_ouput_temp = $save_path.$file_name;

        $path_storage = __DIR__.'/../../../storage/fatture_elettroniche/';

        $file_ouput_storage = $path_storage.$file_name;

        if ( !is_dir($save_path) ) {
            mkdir($save_path, 0777, true);
            chown($save_path, "www-data");
            chgrp($save_path, "www-data");
        }
        else{
            chown($save_path, "www-data");
            chgrp($save_path, "www-data");
            chmod($save_path, 0777);
        }

        if ( !is_dir($path_storage) ) {
            mkdir($path_storage, 0777, true);
            chown($path_storage, "www-data");
            chgrp($path_storage, "www-data");
        }
        else{
            chown($path_storage, "www-data");
            chgrp($path_storage, "www-data");
            chmod($path_storage, 0777);
        }

        $domtree->save($file_ouput_temp);

        $domtree->save($file_ouput_storage);

        $this->setFileInDocumentoFattura($id, $path_storage, $file_name);

    }

    function getHeaderFatturaElettronica($domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $xmlRoot = $domtree->getElementsByTagName( "p:FatturaElettronica" );

        if( $xmlRoot->length > 0 ){

            $xmlRoot = $xmlRoot->item(0);

            //1 <FatturaElettronicaHeader> * <1.1>
            $FatturaElettronicaHeader = $domtree->createElement( "FatturaElettronicaHeader" );
            $FatturaElettronicaHeader = $xmlRoot->appendChild( $FatturaElettronicaHeader );

                //1.1 <DatiTrasmissione> * <1.1>
                $FatturaElettronicaHeader->appendChild( $this->getDatiTrasmissione($domtree, $id) );

                //1.2 <CedentePrestatore> * <1.1>
                $FatturaElettronicaHeader->appendChild( $this->getCedentePrestatore($domtree, $id) );

                //1.3 <RappresentanteFiscale> <0.1>
                //$FatturaElettronicaHeader->appendChild( $this->getRappresentanteFiscale($domtree, $id) );

                //1.4 <CessionarioCommittente> <1.1>
                $FatturaElettronicaHeader->appendChild( $this->getCessionarioCommittente($domtree, $id) );

                //1.5 <TerzoIntermediarioOSoggettoEmittente> <0.1>
                //$FatturaElettronicaHeader->appendChild( $this->getTerzoIntermediarioOSoggettoEmittente($domtree, $id) );

                //1.6 <SoggettoEmittente> <0.1>
                //$FatturaElettronicaHeader->appendChild($domtree->createElement( 'SoggettoEmittente', '' ) );

            /* get the xml printed */
            //echo $domtree->saveXML();die;

        }

        return $domtree;

    }

    function getDatiTrasmissione(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $business_unit = $focus_fattura->column_fields["kp_business_unit"];
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES, $default_charset);

        $cliente = $focus_fattura->column_fields["account_id"];
        $cliente = html_entity_decode(strip_tags($cliente), ENT_QUOTES, $default_charset);

        $focus_bu = CRMEntity::getInstance('KpBusinessUnit');
        $focus_bu->retrieve_entity_info($business_unit, "KpBusinessUnit", $dieOnError=false); 

        $codice_nazione = $focus_bu->column_fields["kp_codice_nazione"];
        $codice_nazione = html_entity_decode(strip_tags($codice_nazione), ENT_QUOTES, $default_charset);

        $partita_iva = $focus_bu->column_fields["kp_partita_iva"];
        $partita_iva = html_entity_decode(strip_tags($partita_iva), ENT_QUOTES, $default_charset);

        $codice_trasmittente = $focus_bu->column_fields["kp_cod_fisc_trasm"];
        $codice_trasmittente = html_entity_decode(strip_tags($codice_trasmittente), ENT_QUOTES, $default_charset);

        $codice_nazione_trasmittente = $focus_bu->column_fields["kp_codice_naz_trasm"];
        $codice_nazione_trasmittente = html_entity_decode(strip_tags($codice_nazione_trasmittente), ENT_QUOTES, $default_charset);

        $focus_cliente = CRMEntity::getInstance('Accounts');
        $focus_cliente->retrieve_entity_info($cliente, "Accounts", $dieOnError=false); 

        $codice_identificativo_cliente = $focus_cliente->column_fields["kp_codice_id_fe"];
        $codice_identificativo_cliente = html_entity_decode(strip_tags($codice_identificativo_cliente), ENT_QUOTES, $default_charset);
        $codice_identificativo_cliente = trim($codice_identificativo_cliente);
        if( $codice_identificativo_cliente == null || $codice_identificativo_cliente == '0000000' ){
            $codice_identificativo_cliente = "";
        }

        $pec_cliente = $focus_cliente->column_fields["kp_pec"];
        $pec_cliente = html_entity_decode(strip_tags($pec_cliente), ENT_QUOTES, $default_charset);
        $pec_cliente = trim($pec_cliente);
        if( $pec_cliente == null ){
            $pec_cliente = "";
        }

        $formato_trasmissione = $focus_cliente->column_fields["kp_formato_trasm"];
        $formato_trasmissione = html_entity_decode(strip_tags($formato_trasmissione), ENT_QUOTES, $default_charset);
        if( $formato_trasmissione == null ){
            $formato_trasmissione = "";
        }

        //1.1 <DatiTrasmissione> * <1.1>
        $DatiTrasmissione = $domtree->createElement( "DatiTrasmissione" );

            //1.1.1 <IdTrasmittente> * <1.1>
            $IdTrasmittente = $domtree->createElement( "IdTrasmittente" );
            $IdTrasmittente = $DatiTrasmissione->appendChild( $IdTrasmittente );

                //1.1.1.1 <IdPaese> * <1.1>
                $IdTrasmittente->appendChild($domtree->createElement( 'IdPaese', $codice_nazione_trasmittente ) );
                
                //1.1.1.2 <IdCodice> * <1.1>
                $IdTrasmittente->appendChild($domtree->createElement( 'IdCodice', $codice_trasmittente ) );

            //1.1.2 <ProgressivoInvio> * <1.1>
            $DatiTrasmissione->appendChild( $domtree->createElement( 'ProgressivoInvio', $this->getProgressivoInvioFatturaElettronica($business_unit, $id) ) );
            
            //1.1.3 <FormatoTrasmissione> * <1.1>
            $DatiTrasmissione->appendChild( $domtree->createElement( 'FormatoTrasmissione', $formato_trasmissione ) );
            
            //1.1.4 <CodiceDestinatario> * <1.1>
            if( $codice_identificativo_cliente != "" ){
                $DatiTrasmissione->appendChild( $domtree->createElement( 'CodiceDestinatario', $codice_identificativo_cliente ) );
            }
            else{
                $DatiTrasmissione->appendChild( $domtree->createElement( 'CodiceDestinatario', '0000000' ) );
            }

            /*//1.1.5 <ContattiTrasmittente> <0.1>
            $ContattiTrasmittente = $domtree->createElement( "ContattiTrasmittente" );
            $ContattiTrasmittente = $DatiTrasmissione->appendChild( $ContattiTrasmittente );
        
                //1.1.5.1 <Telefono> <0.1>
                $ContattiTrasmittente->appendChild( $domtree->createElement( 'Telefono', '' ) );
                
                //1.1.5.2 <Email> <0.1>
                $ContattiTrasmittente->appendChild( $domtree->createElement( 'Email', '' ) );*/

            if( $codice_identificativo_cliente == "" && $pec_cliente != "" ){
                //1.1.6 <PECDestinatario> <0.1>
                $DatiTrasmissione->appendChild( $domtree->createElement( 'PECDestinatario', $pec_cliente ) );
            }

        return $DatiTrasmissione;

    }

    function getCedentePrestatore(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $business_unit = $focus_fattura->column_fields["kp_business_unit"];
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES, $default_charset);

        $focus_bu = CRMEntity::getInstance('KpBusinessUnit');
        $focus_bu->retrieve_entity_info($business_unit, "KpBusinessUnit", $dieOnError=false); 
        
        $codice_nazione = $focus_bu->column_fields["kp_codice_nazione"];
        $codice_nazione = html_entity_decode(strip_tags($codice_nazione), ENT_QUOTES, $default_charset);
        
        $partita_iva = $focus_bu->column_fields["kp_partita_iva"];
        $partita_iva = html_entity_decode(strip_tags($partita_iva), ENT_QUOTES, $default_charset);

        $codice_fiscale = $focus_bu->column_fields["kp_codice_fiscale"];
        $codice_fiscale = html_entity_decode(strip_tags($codice_fiscale), ENT_QUOTES, $default_charset);
        $codice_fiscale = trim($codice_fiscale);
        if( $codice_fiscale == null ){
            $codice_fiscale = "";
        }

        $nome_business_unit = $focus_bu->column_fields["kp_nome_business_un"];
        $nome_business_unit = html_entity_decode(strip_tags($nome_business_unit), ENT_QUOTES, $default_charset);
        $nome_business_unit = trim($nome_business_unit);
        if( $nome_business_unit == null ){
            $nome_business_unit = "";
        }

        $ragione_sociale = $focus_bu->column_fields["kp_ragione_sociale"];
        $ragione_sociale = html_entity_decode(strip_tags($ragione_sociale), ENT_QUOTES, $default_charset);
        $ragione_sociale = trim($ragione_sociale);
        if( $ragione_sociale == null ){
            $ragione_sociale = "";
        }
        $ragione_sociale = $this->replaceSpecialChart($ragione_sociale);

        $regime_fiscale = $focus_bu->column_fields["kp_regime_fiscale"];
        $regime_fiscale = html_entity_decode(strip_tags($regime_fiscale), ENT_QUOTES, $default_charset);
        if( $regime_fiscale == null ){
            $regime_fiscale = "";
        }

        $indirizzo = $focus_bu->column_fields["kp_indirizzo"];
        $indirizzo = html_entity_decode(strip_tags($indirizzo), ENT_QUOTES, $default_charset);
        if( $indirizzo == null ){
            $indirizzo = "";
        }
        $indirizzo = $this->replaceSpecialChart($indirizzo);

        $cap = $focus_bu->column_fields["kp_cap"];
        $cap = html_entity_decode(strip_tags($cap), ENT_QUOTES, $default_charset);
        if( $cap == null ){
            $cap = "";
        }
        $cap = $this->replaceSpecialChart($cap);

        $comune = $focus_bu->column_fields["kp_comune"];
        $comune = html_entity_decode(strip_tags($comune), ENT_QUOTES, $default_charset);
        if( $comune == null ){
            $comune = "";
        }
        $comune = $this->replaceSpecialChart($comune);

        $provincia = $focus_bu->column_fields["kp_provincia"];
        $provincia = html_entity_decode(strip_tags($provincia), ENT_QUOTES, $default_charset);
        if( $provincia == null ){
            $provincia = "";
        }
        $provincia = $this->replaceSpecialChart($provincia);

        $nazione = $focus_bu->column_fields["kp_nazione"];
        $nazione = html_entity_decode(strip_tags($nazione), ENT_QUOTES, $default_charset);
        if( $nazione == null ){
            $nazione = "";
        }
        else{
            $nazione = $this->setNazione($nazione);
        }

        //1.2 <CedentePrestatore> * <1.1>
        $CedentePrestatore = $domtree->createElement( "CedentePrestatore" );

            //1.2.1 <DatiAnagrafici> * <1.1>
            $DatiAnagrafici = $domtree->createElement( "DatiAnagrafici" );
            $DatiAnagrafici = $CedentePrestatore->appendChild( $DatiAnagrafici );

                //1.2.1.1 <IdFiscaleIVA> * <1.1>
                $IdFiscaleIVA = $domtree->createElement( "IdFiscaleIVA" );
                $IdFiscaleIVA = $DatiAnagrafici->appendChild( $IdFiscaleIVA );

                    //1.2.1.1.1 <IdPaese> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdPaese', $codice_nazione ) );
                    
                    //1.2.1.1.2 <IdCodice> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdCodice', $partita_iva ) );

                if( $codice_fiscale != "" ){
                    //1.2.1.2 <CodiceFiscale> <0.1>
                    $DatiAnagrafici->appendChild($domtree->createElement( 'CodiceFiscale', $codice_fiscale ) );
                }

                //1.2.1.3 <Anagrafica> * <1.1>
                $Anagrafica = $domtree->createElement( "Anagrafica" );
                $Anagrafica = $DatiAnagrafici->appendChild( $Anagrafica );

                    //1.2.1.3.1 <Denominazione> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Denominazione', $ragione_sociale ) );
                    
                    /*//1.2.1.3.2 <Nome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Nome', '' ) );
                    
                    //1.2.1.3.3 <Cognome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Cognome', '' ) );
                    
                    //1.2.1.3.4 <Titolo> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Titolo', '' ) );
                    
                    //1.2.1.3.5 <CodEORI> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'CodEORI', '' ) );*/

                //1.2.1.4 <AlboProfessionale> <0.1>
                //$DatiAnagrafici->appendChild($domtree->createElement( 'AlboProfessionale', '' ) );

                //1.2.1.5 <ProvinciaAlbo> <0.1>
                //$DatiAnagrafici->appendChild($domtree->createElement( 'ProvinciaAlbo', '' ) );

                //1.2.1.6 <NumeroIscrizioneAlbo> <0.1>
                //$DatiAnagrafici->appendChild($domtree->createElement( 'NumeroIscrizioneAlbo', '' ) );

                //1.2.1.7 <DataIscrizioneAlbo> <0.1>
                //$DatiAnagrafici->appendChild($domtree->createElement( 'DataIscrizioneAlbo', '' ) );

                //1.2.1.8 <RegimeFiscale> * <1.1>
                $DatiAnagrafici->appendChild($domtree->createElement( 'RegimeFiscale', $regime_fiscale ) );

            //1.2.2 <Sede> * <1.1>
            $Sede = $domtree->createElement( "Sede" );
            $Sede = $CedentePrestatore->appendChild( $Sede );

                //1.2.2.1 <Indirizzo> * <1.1>
                $Sede->appendChild($domtree->createElement( 'Indirizzo', $indirizzo ) );

                //1.2.2.2 <NumeroCivico> <0.1>
                //$Sede->appendChild($domtree->createElement( 'NumeroCivico', '' ) );

                //1.2.2.3 <CAP> * <1.1>
                $Sede->appendChild($domtree->createElement( 'CAP', $cap ) );

                //1.2.2.4 <Comune> * <1.1>
                $Sede->appendChild($domtree->createElement( 'Comune', $comune ) );

                //1.2.2.5 <Provincia> <0.1>
                $Sede->appendChild($domtree->createElement( 'Provincia', $provincia ) );

                //1.2.2.6 <Nazione> * <1.1>
                $Sede->appendChild($domtree->createElement( 'Nazione', $nazione ) );

            /*//1.2.3 <StabileOrganizzazione> <0.1>
            $StabileOrganizzazione = $domtree->createElement( "StabileOrganizzazione" );
            $StabileOrganizzazione = $CedentePrestatore->appendChild( $StabileOrganizzazione );

                //1.2.3.1 <Indirizzo> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Indirizzo', '' ) );

                //1.2.3.2 <NumeroCivico> <0.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'NumeroCivico', '' ) );

                //1.2.3.3 <CAP> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'CAP', '' ) );

                //1.2.3.4 <Comune> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Comune', '' ) );

                //1.2.3.5 <Provincia> <0.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Provincia', '' ) );

                //1.2.3.6 <Nazione> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Nazione', '' ) );*/

            /*//1.2.4 <IscrizioneREA> <0.1>
            $IscrizioneREA = $domtree->createElement( "IscrizioneREA" );
            $IscrizioneREA = $CedentePrestatore->appendChild( $IscrizioneREA );

                //1.2.4.1 <Ufficio> * <1.1>
                $IscrizioneREA->appendChild($domtree->createElement( 'Ufficio', '' ) );

                //1.2.4.2 <NumeroREA> * <1.1>
                $IscrizioneREA->appendChild($domtree->createElement( 'NumeroREA', '' ) );

                //1.2.4.3 <CapitaleSociale> <0.1>
                $IscrizioneREA->appendChild($domtree->createElement( 'CapitaleSociale', '' ) );

                //1.2.4.4 <SocioUnico> <0.1>
                $IscrizioneREA->appendChild($domtree->createElement( 'SocioUnico', '' ) );

                //1.2.4.5 <StatoLiquidazione> * <1.1>
                $IscrizioneREA->appendChild($domtree->createElement( 'StatoLiquidazione', '' ) );*/

            /*//1.2.5 <Contatti> <0.1>
            $Contatti = $domtree->createElement( "Contatti" );
            $Contatti = $CedentePrestatore->appendChild( $Contatti );

                //1.2.5.1 <Telefono> <0.1>
                $Contatti->appendChild($domtree->createElement( 'Telefono', '' ) );

                //1.2.5.2 <Fax> <0.1>
                $Contatti->appendChild($domtree->createElement( 'Fax', '' ) );

                //1.2.5.3 <Email> <0.1>
                $Contatti->appendChild($domtree->createElement( 'Email', '' ) );

            //1.2.6 <RiferimentoAmministrazione> <0.1>
            $CedentePrestatore->appendChild($domtree->createElement( 'RiferimentoAmministrazione', '' ) );*/
        
        return $CedentePrestatore;

    }

    function getRappresentanteFiscale(DOMDocument $domtree , $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //1.3 <RappresentanteFiscale> <0.1>
        $RappresentanteFiscale = $domtree->createElement( "RappresentanteFiscale" );

            //1.3.1 <DatiAnagrafici> * <1.1>
            $DatiAnagrafici = $domtree->createElement( "DatiAnagrafici" );
            $DatiAnagrafici = $RappresentanteFiscale->appendChild( $DatiAnagrafici );

                //1.3.1.1 <IdFiscaleIVA> * <1.1>
                $IdFiscaleIVA = $domtree->createElement( "IdFiscaleIVA" );
                $IdFiscaleIVA = $DatiAnagrafici->appendChild( $IdFiscaleIVA );

                    //1.3.1.1.1 <IdPaese> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdPaese', '' ) );
                    
                    //1.3.1.1.2 <IdCodice> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdCodice', '' ) );

                //1.3.1.2 <CodiceFiscale> <0.1>
                $DatiAnagrafici->appendChild($domtree->createElement( 'CodiceFiscale', '' ) );

                //1.3.1.3 <Anagrafica> * <1.1>
                $Anagrafica = $domtree->createElement( "Anagrafica" );
                $Anagrafica = $DatiAnagrafici->appendChild( $Anagrafica );

                    //1.3.1.3.1 <Denominazione> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Denominazione', '' ) );
                    
                    //1.3.1.3.2 <Nome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Nome', '' ) );
                    
                    //1.3.1.3.3 <Cognome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Cognome', '' ) );
                    
                    //1.3.1.3.4 <Titolo> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Titolo', '' ) );
                    
                    //1.3.1.3.5 <CodEORI> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'CodEORI', '' ) );

        return $RappresentanteFiscale;

    }

    function getCessionarioCommittente(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $accountid = $focus_fattura->column_fields["account_id"];
        $accountid = html_entity_decode(strip_tags($accountid), ENT_QUOTES, $default_charset);

        $focus_account = CRMEntity::getInstance('Accounts');
        $focus_account->retrieve_entity_info($accountid, "Accounts", $dieOnError=false); 

        $codice_nazione = $focus_account->column_fields["kp_codice_nazione"];
        $codice_nazione = html_entity_decode(strip_tags($codice_nazione), ENT_QUOTES, $default_charset);
        if( $codice_nazione == null || $codice_nazione == "" ){
            $codice_nazione = "IT";
        }

        $partita_iva = $focus_account->column_fields["crmv_vat_registration_number"];
        $partita_iva = html_entity_decode(strip_tags($partita_iva), ENT_QUOTES, $default_charset);
        if( $partita_iva == null ){
            $partita_iva = "";
        }
        $partita_iva = trim($partita_iva);

        $codice_fiscale = $focus_account->column_fields["crmv_social_security_number"];
        $codice_fiscale = html_entity_decode(strip_tags($codice_fiscale), ENT_QUOTES, $default_charset);
        $codice_fiscale = trim($codice_fiscale);

        $accountname = $focus_account->column_fields["accountname"];
        $accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES, $default_charset);
        $accountname = $this->replaceSpecialChart($accountname);

        $indirizzo = $focus_account->column_fields["bill_street"];
        $indirizzo = html_entity_decode(strip_tags($indirizzo), ENT_QUOTES, $default_charset);
        $indirizzo = $this->replaceSpecialChart($indirizzo);

        $citta = $focus_account->column_fields["bill_city"];
        $citta = html_entity_decode(strip_tags($citta), ENT_QUOTES, $default_charset);
        $citta = $this->replaceSpecialChart($citta);

        $provincia = $focus_account->column_fields["bill_state"];
        $provincia = html_entity_decode(strip_tags($provincia), ENT_QUOTES, $default_charset);
        $provincia = $this->replaceSpecialChart($provincia);

        $nazione = $focus_account->column_fields["bill_country"];
        $nazione = html_entity_decode(strip_tags($nazione), ENT_QUOTES, $default_charset);
        if( $nazione == null ){
            $nazione = "";
        }
        else{
            $nazione = $this->setNazione($nazione);
        }

        $cap = $focus_account->column_fields["bill_code"];
        $cap = html_entity_decode(strip_tags($cap), ENT_QUOTES, $default_charset);
        $cap = $this->replaceSpecialChart($cap);

        //1.4 <CessionarioCommittente> <1.1>
        $CessionarioCommittente = $domtree->createElement( "CessionarioCommittente" );

            //1.4.1 <DatiAnagrafici> * <1.1>
            $DatiAnagrafici = $domtree->createElement( "DatiAnagrafici" );
            $DatiAnagrafici = $CessionarioCommittente->appendChild( $DatiAnagrafici );

                if( $partita_iva != "" ){
                    //Per i privati la partita iva va lasciata vuota e quindi il blocco 1.4.1.1 <IdFiscaleIVA> va omesso

                    //1.4.1.1 <IdFiscaleIVA> * <1.1>
                    $IdFiscaleIVA = $domtree->createElement( "IdFiscaleIVA" );
                    $IdFiscaleIVA = $DatiAnagrafici->appendChild( $IdFiscaleIVA );

                        //1.4.1.1.1 <IdPaese> * <1.1>
                        $IdFiscaleIVA->appendChild($domtree->createElement( 'IdPaese', $codice_nazione ) );
                        
                        //1.4.1.1.2 <IdCodice> * <1.1>
                        $IdFiscaleIVA->appendChild($domtree->createElement( 'IdCodice', $partita_iva ) );

                }

                //1.4.1.2 <CodiceFiscale> <0.1>
                if( $codice_fiscale != "" ){
                    $DatiAnagrafici->appendChild($domtree->createElement( 'CodiceFiscale', $codice_fiscale ) );
                }

                //1.4.1.3 <Anagrafica> * <1.1>
                $Anagrafica = $domtree->createElement( "Anagrafica" );
                $Anagrafica = $DatiAnagrafici->appendChild( $Anagrafica );

                    //1.4.1.3.1 <Denominazione> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Denominazione', $accountname ) );
                    
                    /*//1.4.1.3.2 <Nome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Nome', '' ) );
                    
                    //1.4.1.3.3 <Cognome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Cognome', '' ) );
                    
                    //1.4.1.3.4 <Titolo> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Titolo', '' ) );
                    
                    //1.4.1.3.5 <CodEORI> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'CodEORI', '' ) );*/

            //1.4.2 <Sede> * <1.1>
            $Sede = $domtree->createElement( "Sede" );
            $Sede = $CessionarioCommittente->appendChild( $Sede );

                //1.4.2.1 <Indirizzo> * <1.1>
                $Sede->appendChild($domtree->createElement( 'Indirizzo', $indirizzo ) );

                //1.4.2.2 <NumeroCivico> <0.1>
                //$Sede->appendChild($domtree->createElement( 'NumeroCivico', '' ) );

                //1.4.2.3 <CAP> * <1.1>
                $Sede->appendChild($domtree->createElement( 'CAP', $cap ) );

                //1.4.2.4 <Comune> * <1.1>
                $Sede->appendChild($domtree->createElement( 'Comune', $citta ) );

                //1.4.2.5 <Provincia> <0.1>
                $Sede->appendChild($domtree->createElement( 'Provincia', $provincia ) );

                //1.4.2.6 <Nazione> * <1.1>
                $Sede->appendChild($domtree->createElement( 'Nazione', $nazione ) );

            /*//1.4.3 <StabileOrganizzazione> <0.1>
            $StabileOrganizzazione = $domtree->createElement( "StabileOrganizzazione" );
            $StabileOrganizzazione = $CessionarioCommittente->appendChild( $StabileOrganizzazione );

                //1.4.3.1 <Indirizzo> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Indirizzo', '' ) );

                //1.4.3.2 <NumeroCivico> <0.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'NumeroCivico', '' ) );

                //1.4.3.3 <CAP> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'CAP', '' ) );

                //1.4.3.4 <Comune> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Comune', '' ) );

                //1.4.3.5 <Provincia> <0.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Provincia', '' ) );

                //1.4.3.6 <Nazione> * <1.1>
                $StabileOrganizzazione->appendChild($domtree->createElement( 'Nazione', '' ) );*/

            /*//1.4.4 <RappresentanteFiscale> <0.1>
            $RappresentanteFiscale = $domtree->createElement( "RappresentanteFiscale" );
            $RappresentanteFiscale = $CessionarioCommittente->appendChild( $RappresentanteFiscale );

                //1.4.4.1 <IdFiscaleIVA> * <1.1>
                $IdFiscaleIVA = $domtree->createElement( "IdFiscaleIVA" );
                $IdFiscaleIVA = $RappresentanteFiscale->appendChild( $IdFiscaleIVA );

                    //1.4.4.1.1 <IdPaese> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdPaese', '' ) );
                    
                    //1.4.4.1.2 <IdCodice> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdCodice', '' ) );

                //1.4.4.2 <Denominazione> <0.1>
                $RappresentanteFiscale->appendChild($domtree->createElement( 'Denominazione', '' ) );

                //1.4.4.3 <Nome> <0.1>
                $RappresentanteFiscale->appendChild($domtree->createElement( 'Nome', '' ) );

                //1.4.4.4 <Cognome> <0.1>
                $RappresentanteFiscale->appendChild($domtree->createElement( 'Cognome', '' ) );*/

        return $CessionarioCommittente;

    }

    function getTerzoIntermediarioOSoggettoEmittente(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //1.5 <TerzoIntermediarioOSoggettoEmittente> <0.1>
        $TerzoIntermediarioOSoggettoEmittente = $domtree->createElement( "TerzoIntermediarioOSoggettoEmittente" );

            //1.5.1 <DatiAnagrafici> * <1.1>
            $DatiAnagrafici = $domtree->createElement( "DatiAnagrafici" );
            $DatiAnagrafici = $TerzoIntermediarioOSoggettoEmittente->appendChild( $DatiAnagrafici );

                //1.5.1.1 <IdFiscaleIVA> <0.1>
                $IdFiscaleIVA = $domtree->createElement( "IdFiscaleIVA" );
                $IdFiscaleIVA = $DatiAnagrafici->appendChild( $IdFiscaleIVA );

                    //1.5.1.1.1 <IdPaese> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdPaese', '' ) );
                    
                    //1.5.1.1.2 <IdCodice> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdCodice', '' ) );

                //1.5.1.2 <CodiceFiscale> <0.1>
                $DatiAnagrafici->appendChild($domtree->createElement( 'CodiceFiscale', '' ) );

                //1.5.1.3 <Anagrafica> * <1.1>
                $Anagrafica = $domtree->createElement( "Anagrafica" );
                $Anagrafica = $DatiAnagrafici->appendChild( $Anagrafica );

                    //1.5.1.3.1 <Denominazione> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Denominazione', '' ) );
                    
                    //1.5.1.3.2 <Nome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Nome', '' ) );
                    
                    //1.5.1.3.3 <Cognome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Cognome', '' ) );
                    
                    //1.5.1.3.4 <Titolo> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Titolo', '' ) );
                    
                    //1.5.1.3.5 <CodEORI> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'CodEORI', '' ) );
    
        return $TerzoIntermediarioOSoggettoEmittente;

    }

    function getDatiRitenuta(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $tipo_ritenuta = $focus_fattura->column_fields["kp_tipo_ritenuta"];
        $tipo_ritenuta = html_entity_decode(strip_tags($tipo_ritenuta), ENT_QUOTES, $default_charset);

        $aliquota_ritenuta = $focus_fattura->column_fields["kp_aliquota_ritenuta"];
        $aliquota_ritenuta = html_entity_decode(strip_tags($aliquota_ritenuta), ENT_QUOTES, $default_charset);
        if( $aliquota_ritenuta == null || $aliquota_ritenuta == "" ){
            $aliquota_ritenuta = 0;
        }
        $aliquota_ritenuta = number_format($aliquota_ritenuta, 2, ".", "");

        $causale_pag_rite = $focus_fattura->column_fields["kp_causale_pag_rite"];
        $causale_pag_rite = html_entity_decode(strip_tags($causale_pag_rite), ENT_QUOTES, $default_charset);

        $importo_ritenuta = $focus_fattura->column_fields["kp_importo_ritenuta"];
        $importo_ritenuta = html_entity_decode(strip_tags($importo_ritenuta), ENT_QUOTES, $default_charset);
        if( $importo_ritenuta == null || $importo_ritenuta == "" ){
            $importo_ritenuta = 0;
        }
        $importo_ritenuta = number_format($importo_ritenuta, 2, ".", "");

        //2.1.1.5 <DatiRitenuta> <0.1>
        $DatiRitenuta = $domtree->createElement( "DatiRitenuta" );

            //2.1.1.5.1 <TipoRitenuta> * <1.1>
            $DatiRitenuta->appendChild($domtree->createElement( 'TipoRitenuta', $tipo_ritenuta ) );

            //2.1.1.5.2 <ImportoRitenuta> * <1.1>
            $DatiRitenuta->appendChild($domtree->createElement( 'ImportoRitenuta', $importo_ritenuta ) );

            //2.1.1.5.3 <AliquotaRitenuta> * <1.1>
            $DatiRitenuta->appendChild($domtree->createElement( 'AliquotaRitenuta', $aliquota_ritenuta ) );

            //2.1.1.5.4 <CausalePagamento> * <1.1>
            $DatiRitenuta->appendChild($domtree->createElement( 'CausalePagamento', $causale_pag_rite ) );

        return $DatiRitenuta;

    }

    function getDatiBollo(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.1.6 <DatiBollo> <0.1>
        $DatiBollo = $domtree->createElement( "DatiBollo" );

            //2.1.1.6.1 <BolloVirtuale> * <1.1>
            $DatiBollo->appendChild($domtree->createElement( 'BolloVirtuale', '' ) );

            //2.1.1.6.2 <ImportoBollo> * <1.1>
            $DatiBollo->appendChild($domtree->createElement( 'ImportoBollo', '' ) );

        return $DatiBollo;

    }

    function getCassaPrevidenziale(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.1.7 <DatiCassaPrevidenziale> <0.N>
        $DatiCassaPrevidenziale = $domtree->createElement( "DatiCassaPrevidenziale" );

            //2.1.1.7.1 <BolloVirtuale> * <1.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'TipoCassa', '' ) );

            //2.1.1.7.2 <AlCassa> * <1.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'AlCassa', '' ) );

            //2.1.1.7.3 <ImportoContributoCassa> * <1.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'ImportoContributoCassa', '' ) );

            //2.1.1.7.4 <ImponibileCassa> <0.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'ImponibileCassa', '' ) );

            //2.1.1.7.5 <AliquotaIVA> * <1.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'AliquotaIVA', '' ) );

            //2.1.1.7.6 <Ritenuta> <0.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'Ritenuta', '' ) );

            //2.1.1.7.7 <Natura> <0.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'Natura', '' ) );

            //2.1.1.7.8 <RiferimentoAmministrazione> <0.1>
            $DatiCassaPrevidenziale->appendChild($domtree->createElement( 'RiferimentoAmministrazione', '' ) );

        return $DatiCassaPrevidenziale;

    }

    function getScontoMaggiorazione(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.1.8 <ScontoMaggiorazione> <0.N>
        $ScontoMaggiorazione = $domtree->createElement( "ScontoMaggiorazione" );

            //2.1.1.8.1 <Tipo> * <1.1>
            $ScontoMaggiorazione->appendChild($domtree->createElement( 'Tipo', '' ) );

            //2.1.1.8.2 <Percentuale> <0.1>
            $ScontoMaggiorazione->appendChild($domtree->createElement( 'Percentuale', '' ) );

            //2.1.1.8.3 <Importo> <0.1>
            $ScontoMaggiorazione->appendChild($domtree->createElement( 'Importo', '' ) );

        return $ScontoMaggiorazione;

    }

    function getDatiOrdiniDiAcquisto(DOMDocument $domtree, $id, $dati_ordine_acquisto){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.2 <DatiOrdineAcquisto> <0.N>
        $DatiOrdineAcquisto = $domtree->createElement( "DatiOrdineAcquisto" );

            $righe = $dati_ordine_acquisto["righe"];

            foreach($righe as $riga){
                //2.1.2.1 <RiferimentoNumeroLinea> <0.N>
                $DatiOrdineAcquisto->appendChild($domtree->createElement( 'RiferimentoNumeroLinea', $riga ) );
            }

            //2.1.2.2 <IdDocumento> * <1.1>
            $DatiOrdineAcquisto->appendChild($domtree->createElement( 'IdDocumento', $dati_ordine_acquisto["rif_ord_cliente"] ) );

            if( $dati_ordine_acquisto["data_ord_cliente"] != "" && $dati_ordine_acquisto["data_ord_cliente"] != "0000-00-00" ){
                //2.1.2.3 <Data> <0.1>
                $DatiOrdineAcquisto->appendChild($domtree->createElement( 'Data', $dati_ordine_acquisto["data_ord_cliente"] ) );
            }

            //2.1.2.4 <NumItem> <0.1>
            //$DatiOrdineAcquisto->appendChild($domtree->createElement( 'NumItem', '' ) );

            //2.1.2.5 <CodiceCommessaConvenzione> <0.1>
            //$DatiOrdineAcquisto->appendChild($domtree->createElement( 'CodiceCommessaConvenzione', '' ) );

            if( $dati_ordine_acquisto["codice_cup"] != "" ){
                //2.1.2.6 <CodiceCUP> <0.1>
                $DatiOrdineAcquisto->appendChild($domtree->createElement( 'CodiceCUP', $dati_ordine_acquisto["codice_cup"] ) );
            }

            if( $dati_ordine_acquisto["codice_cig"] != "" ){
                //2.1.2.7 <CodiceCIG> <0.1>
                $DatiOrdineAcquisto->appendChild($domtree->createElement( 'CodiceCIG', $dati_ordine_acquisto["codice_cig"] ) );
            }

        return $DatiOrdineAcquisto;

    }

    function getDatiSAL(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.7 <DatiSAL> <0.N>
        $DatiSAL = $domtree->createElement( "DatiSAL" );

            //2.1.7.1 <RiferimentoFase> * <1.1>
            $DatiSAL->appendChild($domtree->createElement( 'RiferimentoFase', '' ) );

        return $DatiSAL;

    }

    function getDatiDDT(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.8 <DatiDDT> <0.N>
        $DatiDDT = $domtree->createElement( "DatiDDT" );

            //2.1.8.1 <RiferimentoFase> * <1.1>
            $DatiDDT->appendChild($domtree->createElement( 'NumeroDDT', '' ) );

            //2.1.8.2 <DataDDT> * <1.1>
            $DatiDDT->appendChild($domtree->createElement( 'DataDDT', '' ) );

            //2.1.8.3 <RiferimentoNumeroLinea> <0.N>
            $DatiDDT->appendChild($domtree->createElement( 'RiferimentoNumeroLinea', '' ) );

        return $DatiDDT;

    }

    function getDatiTrasporto(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.9 <DatiTrasporto> <0.1>
        $DatiTrasporto = $domtree->createElement( "DatiTrasporto" );

            //2.1.9.1 <DatiAnagraficiVettore> <0.1>
            $DatiAnagraficiVettore = $domtree->createElement( "DatiAnagraficiVettore" );
            $DatiAnagraficiVettore = $DatiTrasporto->appendChild( $DatiAnagraficiVettore );

                //2.1.9.1.1 <IdFiscaleIVA> * <1.1>
                $IdFiscaleIVA = $domtree->createElement( "IdFiscaleIVA" );
                $IdFiscaleIVA = $DatiAnagraficiVettore->appendChild( $IdFiscaleIVA );

                    //2.1.9.1.1.1 <IdPaese> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdPaese', '' ) );

                    //2.1.9.1.1.2 <IdCodice> * <1.1>
                    $IdFiscaleIVA->appendChild($domtree->createElement( 'IdCodice', '' ) );

                //2.1.9.1.2 <CodiceFiscale> <0.1>
                $DatiAnagraficiVettore->appendChild($domtree->createElement( 'CodiceFiscale', '' ) );

                //2.1.9.1.3 <Anagrafica> * <1.1>
                $Anagrafica = $domtree->createElement( "Anagrafica" );
                $Anagrafica = $DatiAnagraficiVettore->appendChild( $Anagrafica );

                    //2.1.9.1.3.1 <Denominazione> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Denominazione', '' ) );
                    
                    //2.1.9.1.3.2 <Nome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Nome', '' ) );
                    
                    //2.1.9.1.3.3 <Cognome> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Cognome', '' ) );
                    
                    //2.1.9.1.3.4 <Titolo> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'Titolo', '' ) );
                    
                    //2.1.9.1.3.5 <CodEORI> <0.1>
                    $Anagrafica->appendChild($domtree->createElement( 'CodEORI', '' ) );

                //2.1.9.1.4 <NumeroLicenzaGuida> <0.1>
                $DatiAnagraficiVettore->appendChild($domtree->createElement( 'NumeroLicenzaGuida', '' ) );

            //2.1.9.2 <MezzoTrasporto> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'MezzoTrasporto', '' ) );

            //2.1.9.3 <CausaleTrasporto> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'CausaleTrasporto', '' ) );

            //2.1.9.4 <NumeroColli> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'NumeroColli', '' ) );

            //2.1.9.5 <Descrizione> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'Descrizione', '' ) );

            //2.1.9.6 <UnitaMisuraPeso> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'UnitaMisuraPeso', '' ) );

            //2.1.9.7 <PesoLordo> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'PesoLordo', '' ) );

            //2.1.9.8 <PesoNetto> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'PesoNetto', '' ) );

            //2.1.9.9 <DataOraRitiro> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'DataOraRitiro', '' ) );

            //2.1.9.10 <DataInizioTrasporto> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'DataInizioTrasporto', '' ) );

            //2.1.9.11 <TipoResa> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'TipoResa', '' ) );

            //2.1.9.12 <IndirizzoResa> <0.1>
            $IndirizzoResa = $domtree->createElement( "IndirizzoResa" );
            $IndirizzoResa = $DatiTrasporto->appendChild( $IndirizzoResa );

                //2.1.9.12.1 <Indirizzo> * <1.1>
                $IndirizzoResa->appendChild($domtree->createElement( 'Indirizzo', '' ) );

                //2.1.9.12.2 <NumeroCivico> <0.1>
                $IndirizzoResa->appendChild($domtree->createElement( 'NumeroCivico', '' ) );

                //2.1.9.12.3 <CAP> * <1.1>
                $IndirizzoResa->appendChild($domtree->createElement( 'CAP', '' ) );

                //2.1.9.12.4 <Comune> * <1.1>
                $IndirizzoResa->appendChild($domtree->createElement( 'Comune', '' ) );

                //2.1.9.12.5 <Provincia> <0.1>
                $IndirizzoResa->appendChild($domtree->createElement( 'Provincia', '' ) );

                //2.1.9.12.6 <Nazione> * <1.1>
                $IndirizzoResa->appendChild($domtree->createElement( 'Nazione', '' ) );

            //2.1.9.13 <DataOraConsegna> <0.1>
            $DatiTrasporto->appendChild($domtree->createElement( 'DataOraConsegna', '' ) );

        return $DatiTrasporto;

    }

    function getDivisa($currency_id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $query = "SELECT 
                    currency_code 
                    FROM {$table_prefix}_currency_info 
                    WHERE id = ".$currency_id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $currency_code = $adb->query_result($result_query, 0, 'currency_code');
            $currency_code = html_entity_decode(strip_tags($currency_code), ENT_QUOTES, $default_charset);

        }
        else{

            $currency_code = "EUR";

        }

        return $currency_code;

    }

    function getDatiGeneraliDocumento(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false); 

        $tipo_documento = $focus_fattura->column_fields["kp_tipo_documento"];
        $tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES, $default_charset);
        if( $tipo_documento == "Fattura" ){
            $tipo_documento = "TD01";
        }
        elseif( $tipo_documento == "Fattura di acconto" ){
            $tipo_documento = "TD02";
        }
        elseif( $tipo_documento == "Nota di credito" ){
            $tipo_documento = "TD04";
        }
        else{
            $tipo_documento = "TD01";
        }

        $currency_id = $focus_fattura->column_fields["currency_id"];
        $currency_id = html_entity_decode(strip_tags($currency_id), ENT_QUOTES, $default_charset);
        $divisa = $this->getDivisa($currency_id);

        $invoicedate = $focus_fattura->column_fields["invoicedate"];
        $invoicedate = html_entity_decode(strip_tags($invoicedate), ENT_QUOTES, $default_charset);

        $invoice_number = $focus_fattura->column_fields["invoice_number"];
        $invoice_number = html_entity_decode(strip_tags($invoice_number), ENT_QUOTES, $default_charset);

        $hdnGrandTotal = $focus_fattura->column_fields["hdnGrandTotal"];
        $hdnGrandTotal = html_entity_decode(strip_tags($hdnGrandTotal), ENT_QUOTES, $default_charset);
        $hdnGrandTotal = number_format($hdnGrandTotal, 2, ".", "");

        $txtAdjustment = $focus_fattura->column_fields["txtAdjustment"];
        $txtAdjustment = html_entity_decode(strip_tags($txtAdjustment), ENT_QUOTES, $default_charset);
        $txtAdjustment = trim($txtAdjustment);
        if( $txtAdjustment == null || $txtAdjustment == "" ){
            $txtAdjustment = 0;
        }
        $txtAdjustment = number_format($txtAdjustment, 2, ".", "");

        /* kpro@tom240120191400 */
        $applica_ritenuta = $focus_fattura->column_fields["kp_applica_ritenuta"];
        $applica_ritenuta = html_entity_decode(strip_tags($applica_ritenuta), ENT_QUOTES, $default_charset);
        if( $applica_ritenuta == 'on' || $applica_ritenuta == '1' ){
            $applica_ritenuta = true;
        }
        else{
            $applica_ritenuta = false;
        }

        $importo_ritenuta = $focus_fattura->column_fields["kp_importo_ritenuta"];
        $importo_ritenuta = html_entity_decode(strip_tags($importo_ritenuta), ENT_QUOTES, $default_charset);
        if( $importo_ritenuta == null || $importo_ritenuta == "" ){
            $importo_ritenuta = 0;
        }
        /* kpro@tom240120191400 end */

        //2.1.1 <DatiGeneraliDocumento> * <1.1>
        $DatiGeneraliDocumento = $domtree->createElement( "DatiGeneraliDocumento" );

            //2.1.1.1 <TipoDocumento> * <1.1>
            $DatiGeneraliDocumento->appendChild($domtree->createElement( 'TipoDocumento', $tipo_documento ) );

            //2.1.1.2 <Divisa> * <1.1>
            $DatiGeneraliDocumento->appendChild($domtree->createElement( 'Divisa', $divisa ) );

            //2.1.1.3 <Data> * <1.1>
            $DatiGeneraliDocumento->appendChild($domtree->createElement( 'Data', $invoicedate ) );

            //2.1.1.4 <Numero> * <1.1>
            $DatiGeneraliDocumento->appendChild($domtree->createElement( 'Numero', $invoice_number ) );

            /* kpro@tom240120191400 */
            if( $applica_ritenuta && $importo_ritenuta != 0 ){
                //2.1.1.5 <DatiRitenuta> <0.1>
                $DatiGeneraliDocumento->appendChild( $this->getDatiRitenuta($domtree, $id) );
            }
            /* kpro@tom240120191400 end */

            //2.1.1.6 <DatiBollo> <0.1>
            //$DatiGeneraliDocumento->appendChild( $this->getDatiBollo($domtree, $id) );

            //2.1.1.7 <DatiCassaPrevidenziale> <0.N>
            //$DatiGeneraliDocumento->appendChild( $this->getCassaPrevidenziale($domtree, $id) );

            //2.1.1.8 <ScontoMaggiorazione> <0.N>
            //$DatiGeneraliDocumento->appendChild( $this->getScontoMaggiorazione($domtree, $id) );

            //2.1.1.9 <ImportoTotaleDocumento> <0.1>
            $DatiGeneraliDocumento->appendChild($domtree->createElement( 'ImportoTotaleDocumento', $hdnGrandTotal ) );

            if( $txtAdjustment != null && $txtAdjustment != "" && $txtAdjustment != 0  && $txtAdjustment != 0.00 ){
                //2.1.1.10 <Arrotondamento> <0.1>
                $DatiGeneraliDocumento->appendChild($domtree->createElement( 'Arrotondamento', $txtAdjustment ) );
            }

            //2.1.1.11 <Causale> <0.N>
            //$DatiGeneraliDocumento->appendChild($domtree->createElement( 'Causale', '' ) );

            //2.1.1.12 <Art73> <0.1>
            //$DatiGeneraliDocumento->appendChild($domtree->createElement( 'Art73', '' ) );

        return $DatiGeneraliDocumento;

    }

    function getFatturaPrincipale(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1.10 <FatturaPrincipale> <0.1>
        $FatturaPrincipale = $domtree->createElement( "FatturaPrincipale" );

            //2.1.10.1 <NumeroFatturaPrincipale> * <1.1>
            $FatturaPrincipale->appendChild($domtree->createElement( 'NumeroFatturaPrincipale', '' ) );

            //2.1.10.2 <DataFatturaPrincipale> * <1.1>
            $FatturaPrincipale->appendChild($domtree->createElement( 'DataFatturaPrincipale', '' ) );

        return $FatturaPrincipale;

    }

    function getValoriRagruppatoRifOrdineCliente($fattura){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        $query = "SELECT 
                    relcustom.rif_ord_cliente rif_ord_cliente,
                    relcustom.data_ord_cliente data_ord_cliente,
                    relcustom.codice_cup codice_cup,
                    relcustom.codice_cig codice_cig
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN kp_inventoryproductrel relcustom ON relcustom.id = rel.id AND relcustom.lineitem_id = rel.lineitem_id
                    WHERE rel.id = ".$fattura." AND relcustom.rif_ord_cliente != ''
                    GROUP BY relcustom.rif_ord_cliente";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $rif_ord_cliente = $adb->query_result($result_query, $i, 'rif_ord_cliente');
            $rif_ord_cliente = html_entity_decode(strip_tags($rif_ord_cliente), ENT_QUOTES, $default_charset);
            $rif_ord_cliente = $this->replaceSpecialChart($rif_ord_cliente);

            $data_ord_cliente = $adb->query_result($result_query, $i, 'data_ord_cliente');
            $data_ord_cliente = html_entity_decode(strip_tags($data_ord_cliente), ENT_QUOTES, $default_charset);
            if( $data_ord_cliente == null || $data_ord_cliente == '0000-00-00' ){
                $data_ord_cliente = "";
            }

            $codice_cup = $adb->query_result($result_query, $i, 'codice_cup');
            $codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES, $default_charset);
            $codice_cup = $this->replaceSpecialChart($codice_cup);

            $codice_cig = $adb->query_result($result_query, $i, 'codice_cig');
            $codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES, $default_charset);
            $codice_cig = $this->replaceSpecialChart($codice_cig);

            $righe = array();

            $query_2 = "SELECT 
                        rel.lineitem_id lineitem_id,
                        rel.sequence_no sequence_no
                        FROM {$table_prefix}_inventoryproductrel rel
                        INNER JOIN kp_inventoryproductrel relcustom ON relcustom.id = rel.id AND relcustom.lineitem_id = rel.lineitem_id
                        WHERE rel.id = ".$fattura." AND relcustom.rif_ord_cliente = '".$rif_ord_cliente."'
                        ORDER BY rel.sequence_no ASC";
            
            $result_query_2 = $adb->query($query_2);
            $num_result_2 = $adb->num_rows($result_query_2);
    
            for($y=0; $y < $num_result_2; $y++){

                $lineitem_id = $adb->query_result($result_query_2, $y, 'lineitem_id');
                $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES, $default_charset);

                $sequence_no = $adb->query_result($result_query_2, $y, 'sequence_no');
                $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES, $default_charset);

                $righe[] = $sequence_no;

            }

            $result[] = array("rif_ord_cliente" => $rif_ord_cliente,
                                "data_ord_cliente" => $data_ord_cliente,
                                "codice_cup" => $codice_cup,
                                "codice_cig" => $codice_cig,
                                "righe" => $righe);

        }

        return $result;

    }

    function getValoriRagruppatoCig($fattura){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        $query = "SELECT 
                    relcustom.rif_ord_cliente rif_ord_cliente,
                    relcustom.data_ord_cliente data_ord_cliente,
                    relcustom.codice_cup codice_cup,
                    relcustom.codice_cig codice_cig
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN kp_inventoryproductrel relcustom ON relcustom.id = rel.id AND relcustom.lineitem_id = rel.lineitem_id
                    WHERE rel.id = ".$fattura." AND relcustom.codice_cig != ''
                    GROUP BY relcustom.codice_cig";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $rif_ord_cliente = $adb->query_result($result_query, $i, 'rif_ord_cliente');
            $rif_ord_cliente = html_entity_decode(strip_tags($rif_ord_cliente), ENT_QUOTES, $default_charset);
            $rif_ord_cliente = $this->replaceSpecialChart($rif_ord_cliente);

            $data_ord_cliente = $adb->query_result($result_query, $i, 'data_ord_cliente');
            $data_ord_cliente = html_entity_decode(strip_tags($data_ord_cliente), ENT_QUOTES, $default_charset);
            if( $data_ord_cliente == null || $data_ord_cliente == '0000-00-00' ){
                $data_ord_cliente = "";
            }

            $codice_cup = $adb->query_result($result_query, $i, 'codice_cup');
            $codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES, $default_charset);
            $codice_cup = $this->replaceSpecialChart($codice_cup);

            $codice_cig = $adb->query_result($result_query, $i, 'codice_cig');
            $codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES, $default_charset);
            $codice_cig = $this->replaceSpecialChart($codice_cig);

            if( $rif_ord_cliente == "" ){
                $rif_ord_cliente = $codice_cig;
            }

            $righe = array();

            $query_2 = "SELECT 
                        rel.lineitem_id lineitem_id,
                        rel.sequence_no sequence_no
                        FROM {$table_prefix}_inventoryproductrel rel
                        INNER JOIN kp_inventoryproductrel relcustom ON relcustom.id = rel.id AND relcustom.lineitem_id = rel.lineitem_id
                        WHERE rel.id = ".$fattura." AND relcustom.codice_cig = '".$codice_cig."'
                        ORDER BY rel.sequence_no ASC";
            
            $result_query_2 = $adb->query($query_2);
            $num_result_2 = $adb->num_rows($result_query_2);
    
            for($y=0; $y < $num_result_2; $y++){

                $lineitem_id = $adb->query_result($result_query_2, $y, 'lineitem_id');
                $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES, $default_charset);

                $sequence_no = $adb->query_result($result_query_2, $y, 'sequence_no');
                $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES, $default_charset);

                $righe[] = $sequence_no;

            }

            $result[] = array("rif_ord_cliente" => $rif_ord_cliente,
                                "data_ord_cliente" => $data_ord_cliente,
                                "codice_cup" => $codice_cup,
                                "codice_cig" => $codice_cig,
                                "righe" => $righe);

        }

        return $result;

    }

    function getValoriRagruppatoCup($fattura){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        $query = "SELECT 
                    relcustom.rif_ord_cliente rif_ord_cliente,
                    relcustom.data_ord_cliente data_ord_cliente,
                    relcustom.codice_cup codice_cup,
                    relcustom.codice_cig codice_cig
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN kp_inventoryproductrel relcustom ON relcustom.id = rel.id AND relcustom.lineitem_id = rel.lineitem_id
                    WHERE rel.id = ".$fattura." AND relcustom.codice_cup != ''
                    GROUP BY relcustom.codice_cup";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $rif_ord_cliente = $adb->query_result($result_query, $i, 'rif_ord_cliente');
            $rif_ord_cliente = html_entity_decode(strip_tags($rif_ord_cliente), ENT_QUOTES, $default_charset);
            $rif_ord_cliente = $this->replaceSpecialChart($rif_ord_cliente);

            $data_ord_cliente = $adb->query_result($result_query, $i, 'data_ord_cliente');
            $data_ord_cliente = html_entity_decode(strip_tags($data_ord_cliente), ENT_QUOTES, $default_charset);
            if( $data_ord_cliente == null || $data_ord_cliente == '0000-00-00' ){
                $data_ord_cliente = "";
            }

            $codice_cup = $adb->query_result($result_query, $i, 'codice_cup');
            $codice_cup = html_entity_decode(strip_tags($codice_cup), ENT_QUOTES, $default_charset);
            $codice_cup = $this->replaceSpecialChart($codice_cup);

            $codice_cig = $adb->query_result($result_query, $i, 'codice_cig');
            $codice_cig = html_entity_decode(strip_tags($codice_cig), ENT_QUOTES, $default_charset);
            $codice_cig = $this->replaceSpecialChart($codice_cig);

            if( $rif_ord_cliente == "" ){
                $rif_ord_cliente = $codice_cup;
            }

            $righe = array();

            $query_2 = "SELECT 
                        rel.lineitem_id lineitem_id,
                        rel.sequence_no sequence_no
                        FROM {$table_prefix}_inventoryproductrel rel
                        INNER JOIN kp_inventoryproductrel relcustom ON relcustom.id = rel.id AND relcustom.lineitem_id = rel.lineitem_id
                        WHERE rel.id = ".$fattura." AND relcustom.codice_cup = '".$codice_cup."'
                        ORDER BY rel.sequence_no ASC";
            
            $result_query_2 = $adb->query($query_2);
            $num_result_2 = $adb->num_rows($result_query_2);
    
            for($y=0; $y < $num_result_2; $y++){

                $lineitem_id = $adb->query_result($result_query_2, $y, 'lineitem_id');
                $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES, $default_charset);

                $sequence_no = $adb->query_result($result_query_2, $y, 'sequence_no');
                $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES, $default_charset);

                $righe[] = $sequence_no;

            }

            $result[] = array("rif_ord_cliente" => $rif_ord_cliente,
                                "data_ord_cliente" => $data_ord_cliente,
                                "codice_cup" => $codice_cup,
                                "codice_cig" => $codice_cig,
                                "righe" => $righe);

        }

        return $result;

    }

    function getValoriDatiOrdiniDiAcquisto($fattura){
        global $adb, $table_prefix, $current_user, $default_charset;

        $rag_ordine_cliente = $this->getValoriRagruppatoRifOrdineCliente($fattura);

        $rag_cig = $this->getValoriRagruppatoCig($fattura);

        $rag_cup = $this->getValoriRagruppatoCup($fattura);

        $result = array("rag_ordine_cliente" => $rag_ordine_cliente,
                        "rag_cup" => $rag_cup,
                        "rag_cig" => $rag_cig);
        
        return $result;

    }

    function getDatiGenerali(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.1 <DatiGenerali> * <1.1>
        $DatiGenerali = $domtree->createElement( "DatiGenerali" );

            //2.1.1 <DatiGeneraliDocumento> * <1.1>
            $DatiGenerali->appendChild( $this->getDatiGeneraliDocumento($domtree, $id) );

            $dati_ordini_acquisto = $this->getValoriDatiOrdiniDiAcquisto($id);
            
            $rag_ordine_cliente = $dati_ordini_acquisto["rag_ordine_cliente"];
            foreach($rag_ordine_cliente as $record){
                //2.1.2 <DatiOrdineAcquisto> <0.N>
                $DatiGenerali->appendChild( $this->getDatiOrdiniDiAcquisto($domtree, $id, $record) );
            }

            /*$rag_cup = $dati_ordini_acquisto["rag_cup"];
            foreach($rag_cup as $record){
                //2.1.2 <DatiOrdineAcquisto> <0.N>
                $DatiGenerali->appendChild( $this->getDatiOrdiniDiAcquisto($domtree, $id, $record) );
            }

            $rag_cig = $dati_ordini_acquisto["rag_cig"];
            foreach($rag_cig as $record){
                //2.1.2 <DatiOrdineAcquisto> <0.N>
                $DatiGenerali->appendChild( $this->getDatiOrdiniDiAcquisto($domtree, $id, $record) );
            }*/

            //2.1.3 <DatiContratto> <0.N>
            //$DatiGenerali->appendChild($domtree->createElement( 'DatiContratto', '' ) );

            //2.1.4 <DatiConvenzione> <0.N>
            //$DatiGenerali->appendChild($domtree->createElement( 'DatiConvenzione', '' ) );

            //2.1.5 <DatiRicezione> <0.N>
            //$DatiGenerali->appendChild($domtree->createElement( 'DatiRicezione', '' ) );

            //2.1.6 <DatiFattureCollegate> <0.N>
            //$DatiGenerali->appendChild($domtree->createElement( 'DatiFattureCollegate', '' ) );

            //2.1.7 <DatiSAL> <0.N>
            //$DatiGenerali->appendChild( $this->getDatiSAL($domtree, $id) );

            //2.1.8 <DatiDDT> <0.N>
            //$DatiGenerali->appendChild( $this->getDatiDDT($domtree, $id) );

            //2.1.9 <DatiTrasporto> <0.1>
            //$DatiGenerali->appendChild( $this->getDatiTrasporto($domtree, $id) );

            //2.1.10 <FatturaPrincipale> <0.1>
            //$DatiGenerali->appendChild( $this->getFatturaPrincipale($domtree, $id) );

        return $DatiGenerali;

    }

    function getAltriDatiGestionaliLinee(DOMDocument $domtree, $id, $linea){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.2.1.16 <AltriDatiGestionali> <0.N>
        $AltriDatiGestionali = $domtree->createElement( "AltriDatiGestionali" );

            //2.2.1.16.1 <TipoDato> * <1.1>
            $AltriDatiGestionali->appendChild($domtree->createElement( 'TipoDato', '' ) );

            //2.2.1.16.2 <RiferimentoTesto> <0.1>
            $AltriDatiGestionali->appendChild($domtree->createElement( 'RiferimentoTesto', '' ) );

            //2.2.1.16.3 <RiferimentoNumero> <0.1>
            $AltriDatiGestionali->appendChild($domtree->createElement( 'RiferimentoNumero', '' ) );

            //2.2.1.16.4 <RiferimentoData> <0.1>
            $AltriDatiGestionali->appendChild($domtree->createElement( 'RiferimentoData', '' ) );

        return $AltriDatiGestionali;

    }

    function getDettaglioLinee(DOMDocument $domtree, $id, $linea){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false); 

        /* kpro@tom240120191400 */
        $applica_ritenuta = $focus_fattura->column_fields["kp_applica_ritenuta"];
        $applica_ritenuta = html_entity_decode(strip_tags($applica_ritenuta), ENT_QUOTES, $default_charset);
        if( $applica_ritenuta == 'on' || $applica_ritenuta == '1' ){
            $applica_ritenuta = true;
        }
        else{
            $applica_ritenuta = false;
        }

        $importo_ritenuta = $focus_fattura->column_fields["kp_importo_ritenuta"];
        $importo_ritenuta = html_entity_decode(strip_tags($importo_ritenuta), ENT_QUOTES, $default_charset);
        if( $importo_ritenuta == null || $importo_ritenuta == "" ){
            $importo_ritenuta = 0;
        }
        /* kpro@tom240120191400 end */

        //2.2.1 <DettaglioLinee> * <1.N>
        $DettaglioLinee = $domtree->createElement( "DettaglioLinee" );

            //2.2.1.1 <NumeroLinea> * <1.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'NumeroLinea', $linea["sequence_no"] ) );

            //2.2.1.2 <TipoCessionePrestazione> <0.1>
            //$DettaglioLinee->appendChild($domtree->createElement( 'TipoCessionePrestazione', '' ) );

            //2.2.1.3 <CodiceArticolo> <0.N>
            /*$CodiceArticolo = $domtree->createElement( "CodiceArticolo" );
            $CodiceArticolo = $DettaglioLinee->appendChild( $CodiceArticolo );

                //2.2.1.3.1 <CodiceTipo> * <1.1>
                $CodiceArticolo->appendChild($domtree->createElement( 'CodiceTipo', '' ) );

                //2.2.1.3.2 <CodiceValore> * <1.1>
                $CodiceArticolo->appendChild($domtree->createElement( 'CodiceValore', '' ) );*/

            //2.2.1.4 <Descrizione> * <1.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'Descrizione', $linea["description"] ) );

            //2.2.1.5 <Quantita> <0.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'Quantita', $linea["quantity"] ) );

            //2.2.1.6 <UnitaMisura> <0.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'UnitaMisura', $linea["unita_di_misura"] ) );

            //2.2.1.7 <DataInizioPeriodo> <0.1>
            //$DettaglioLinee->appendChild($domtree->createElement( 'DataInizioPeriodo', '' ) );

            //2.2.1.8 <DataFinePeriodo> <0.1>
            //$DettaglioLinee->appendChild($domtree->createElement( 'DataFinePeriodo', '' ) );

            //2.2.1.9 <PrezzoUnitario> * <1.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'PrezzoUnitario', $linea["listprice"] ) );

            //2.2.1.10 <ScontoMaggiorazione> <0.N>
            if( $linea["discount_percent"] != 0 ){

                $ScontoMaggiorazione = $domtree->createElement( "ScontoMaggiorazione" );
                $ScontoMaggiorazione = $DettaglioLinee->appendChild( $ScontoMaggiorazione );

                    //2.2.1.10.1 <Tipo> * <1.1>
                    $ScontoMaggiorazione->appendChild($domtree->createElement( 'Tipo', 'SC' ) );

                    //2.2.1.10.2 <Percentuale> <0.1>
                    $ScontoMaggiorazione->appendChild($domtree->createElement( 'Percentuale', $linea["discount_percent"] ) );

            }

            if( $linea["discount_amount"] != 0 ){

                $ScontoMaggiorazione = $domtree->createElement( "ScontoMaggiorazione" );
                $ScontoMaggiorazione = $DettaglioLinee->appendChild( $ScontoMaggiorazione );

                    //2.2.1.10.1 <Tipo> * <1.1>
                    $ScontoMaggiorazione->appendChild($domtree->createElement( 'Tipo', 'SC' ) );

                    //2.2.1.10.3 <Importo> <0.1>
                    $ScontoMaggiorazione->appendChild($domtree->createElement( 'Importo', $linea["discount_amount"] ) );

            }

            //2.2.1.11 <PrezzoTotale> * <1.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'PrezzoTotale', $linea["total_notaxes"] ) );

            //2.2.1.12 <AliquotaIVA> * <1.1>
            $DettaglioLinee->appendChild($domtree->createElement( 'AliquotaIVA', $linea["percentage"] ) );

            /* kpro@tom240120191400 */
            if( $applica_ritenuta && $importo_ritenuta != 0 ){
                //2.2.1.13 <Ritenuta> <0.1>
                $DettaglioLinee->appendChild($domtree->createElement( 'Ritenuta', 'SI' ) );
            }
            /* kpro@tom240120191400 end */

            //2.2.1.14 <Natura> <0.1>
            if( $linea["percentage"] == 0.00 ){
                $DettaglioLinee->appendChild($domtree->createElement( 'Natura', $linea["natura"] ) );
            }

            //2.2.1.15 <RiferimentoAmministrazione> <0.1>
            //$DettaglioLinee->appendChild($domtree->createElement( 'RiferimentoAmministrazione', '' ) );

            //2.2.1.16 <AltriDatiGestionali> <0.N>
            //$DettaglioLinee->appendChild( $this->getAltriDatiGestionaliLinee($domtree, $id, $linea) );

        return $DettaglioLinee;

    }

    function getDatiTasse($tassa){
        global $adb, $table_prefix, $current_user, $default_charset;

        switch ($tassa) {
            case "22":
                $tasse = "22.00";
                $natura = "";
                $normativa = "";
                break;
            case "E1":
                $tasse = "0.00";
                $natura = "N1";
                $normativa = "ART.15 DPR 633/72";
                break;
            case "E2":
                $tasse = "0.00";
                $natura = "N3";
                $normativa = "ART.8 DPR 633/72";
                break;
            default:
                $tasse = "22.00";
                $natura = "";
                $normativa = "";
        }

        $result = array("tasse" => $tasse,
                        "natura" => $natura,
                        "normativa" => $normativa);
            
        return $result;

    }

    function getDatiRiepilogo(DOMDocument $domtree, $id, $valore_dati_riepilogo){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false); 

        $split_payment = $focus_fattura->column_fields["kp_split_payment"];
        $split_payment = html_entity_decode(strip_tags($split_payment), ENT_QUOTES, $default_charset);
        if($split_payment == '1'){
            $split_payment = true;
        }
        else{
            $split_payment = false;
        }

        //2.2.2 <DatiRiepilogo> * <1.N>
        $DatiRiepilogo = $domtree->createElement( "DatiRiepilogo" );

            //2.2.2.1 <AliquotaIVA> * <1.1>
            $DatiRiepilogo->appendChild($domtree->createElement( 'AliquotaIVA', $valore_dati_riepilogo["aliquota_iva"] ) );

            if( $valore_dati_riepilogo["aliquota_iva"] == 0 ){
                //2.2.2.2 <Natura> <0.1>
                $DatiRiepilogo->appendChild($domtree->createElement( 'Natura', $valore_dati_riepilogo["natura"] ) );
            }

            //2.2.2.3 <SpeseAccessorie> <0.1>
            //$DatiRiepilogo->appendChild($domtree->createElement( 'SpeseAccessorie', '' ) );

            //2.2.2.4 <Arrotondamento> <0.1>
            //$DatiRiepilogo->appendChild($domtree->createElement( 'Arrotondamento', '' ) );

            //2.2.2.5 <ImponibileImporto> * <1.1>
            $DatiRiepilogo->appendChild($domtree->createElement( 'ImponibileImporto', $valore_dati_riepilogo["totale_imponibile"] ) );

            //2.2.2.6 <Imposta> * <1.1>
            $DatiRiepilogo->appendChild($domtree->createElement( 'Imposta', $valore_dati_riepilogo["totale_imposta"] ) );

            if( $valore_dati_riepilogo["aliquota_iva"] != 0.00 && $split_payment ){
                //2.2.2.7 <EsigibilitaIVA> <0.1>
                $DatiRiepilogo->appendChild($domtree->createElement( 'EsigibilitaIVA', 'S' ) );
            }

            if( $valore_dati_riepilogo["aliquota_iva"] == 0.00 ){
                //2.2.2.8 <RiferimentoNormativo> <0.1>
                $DatiRiepilogo->appendChild($domtree->createElement( 'RiferimentoNormativo', $valore_dati_riepilogo["normativa"] ) );
            }

        return $DatiRiepilogo;

    }

    function getDatiBeniServizi(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.2 <DatiBeniServizi> * <1.1>
        $DatiBeniServizi = $domtree->createElement( "DatiBeniServizi" );

            $linee = $this->getRigheFattura($id);

            foreach($linee as $linea){

                //2.2.1 <DettaglioLinee> * <1.N>
                $DatiBeniServizi->appendChild( $this->getDettaglioLinee($domtree, $id, $linea) );

            }

            $valori_dati_riepilogo = $this->getValoriDatiRiepilogoFattura($id);

            foreach($valori_dati_riepilogo as $valore_dati_riepilogo){

                //2.2.2 <DatiRiepilogo> * <1.N>
                $DatiBeniServizi->appendChild( $this->getDatiRiepilogo($domtree, $id, $valore_dati_riepilogo) );

            }

        return $DatiBeniServizi;

    }

    function getDatiVeicoli(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.3 <DatiVeicoli> <0.1>
        $DatiVeicoli = $domtree->createElement( "DatiVeicoli" );

            //2.3.1 <Data> * <1.1>
            $DatiVeicoli->appendChild($domtree->createElement( 'Data', '' ) );

            //2.3.2 <TotalePercorso> * <1.1>
            $DatiVeicoli->appendChild($domtree->createElement( 'TotalePercorso', '' ) );

        return $DatiVeicoli;

    }

    function esisteTabellaBanche(){
        global $adb, $table_prefix, $current_user, $default_charset;

        $query = "SHOW TABLES LIKE 'kp_banche_company'";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){
            return true;
        }
        else{
            return false;
        }

    }

    function getDatiBancaPagamento($banca_pagamento){
        global $adb, $table_prefix, $current_user, $default_charset;

        $nome_istituto = "";
        $iban = "";
        $abi = "";
        $cab = "";
        $bic = "";

        if( $this->esisteTabellaBanche() ){

            $query = "SELECT 
                        nome_istituto,
                        iban,
                        abi,
                        cab,
                        bic
                        FROM kp_banche_company
                        WHERE banca = '".$banca_pagamento."'";

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);

            if( $num_result > 0 ){

                $nome_istituto = $adb->query_result($result_query, 0, 'nome_istituto');
                $nome_istituto = html_entity_decode(strip_tags($nome_istituto), ENT_QUOTES, $default_charset);
                $nome_istituto = trim($nome_istituto);
                $nome_istituto = $this->replaceSpecialChart($nome_istituto);

                $iban = $adb->query_result($result_query, 0, 'iban');
                $iban = html_entity_decode(strip_tags($iban), ENT_QUOTES, $default_charset);
                $iban = trim($iban);
                $iban = str_replace(' ', '', $iban);

                $abi = $adb->query_result($result_query, 0, 'abi');
                $abi = html_entity_decode(strip_tags($abi), ENT_QUOTES, $default_charset);
                $abi = trim($abi);
                $abi = str_replace(' ', '', $abi);

                $cab = $adb->query_result($result_query, 0, 'cab');
                $cab = html_entity_decode(strip_tags($cab), ENT_QUOTES, $default_charset);
                $cab = trim($cab);
                $cab = str_replace(' ', '', $cab);

                $bic = $adb->query_result($result_query, 0, 'bic');
                $bic = html_entity_decode(strip_tags($bic), ENT_QUOTES, $default_charset);
                $bic = trim($bic);
                $bic = str_replace(' ', '', $bic);

            }

        }

        $result = array("nome_istituto" => $nome_istituto,
                        "iban" => $iban,
                        "abi" => $abi,
                        "cab" => $cab,
                        "bic" => $bic);

        return $result;

    }

    function getDettaglioPagamento(DOMDocument $domtree, $id, $scadenza){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);
        
        $banca_pagamento = $focus_fattura->column_fields["banca_pagamento"];
        $banca_pagamento = html_entity_decode(strip_tags($banca_pagamento), ENT_QUOTES, $default_charset);

        $dati_banca_pagamento = $this->getDatiBancaPagamento($banca_pagamento);

        $focus_scadenziario = CRMEntity::getInstance('Scadenziario');
        $focus_scadenziario->retrieve_entity_info($scadenza, "Scadenziario", $dieOnError=false);

        $condizioni_pagamento = $focus_scadenziario->column_fields["condizioni_pagamento"];
        $condizioni_pagamento = html_entity_decode(strip_tags($condizioni_pagamento), ENT_QUOTES, $default_charset);

        $data_scadenza = $focus_scadenziario->column_fields["data_scadenza"];
        $data_scadenza = html_entity_decode(strip_tags($data_scadenza), ENT_QUOTES, $default_charset);
        if( $data_scadenza == null || $data_scadenza == '0000-00-00' ){
            $data_scadenza = "";
        }

        $importo = $focus_scadenziario->column_fields["import"];
        $importo = html_entity_decode(strip_tags($importo), ENT_QUOTES, $default_charset);

        switch ($condizioni_pagamento) {
            case "BB":
                $condizioni_pagamento = "MP05";
                break;
            case "CN":
                $condizioni_pagamento = "MP01";
                break;
            case "RIBA":
                $condizioni_pagamento = "MP12";
                break;
        }

        //2.4.2 <DettaglioPagamento> <1.N>
        $DettaglioPagamento = $domtree->createElement( "DettaglioPagamento" );

            //2.4.2.1 <Tipo> <0.1>
            //$DettaglioPagamento->appendChild($domtree->createElement( 'Beneficiario', '' ) );

            //2.4.2.2 <ModalitaPagamento> * <1.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'ModalitaPagamento', $condizioni_pagamento ) );

            //2.4.2.3 <DataRiferimentoTerminiPagamento> <0.1>
            //$DettaglioPagamento->appendChild($domtree->createElement( 'DataRiferimentoTerminiPagamento', '' ) );

            //2.4.2.4 <GiorniTerminiPagamento> <0.1>
            //$DettaglioPagamento->appendChild($domtree->createElement( 'GiorniTerminiPagamento', '' ) );

            //2.4.2.5 <DataScadenzaPagamento> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'DataScadenzaPagamento', $data_scadenza ) );

            //2.4.2.6 <ImportoPagamento> * <1.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'ImportoPagamento', $importo ) );

            /*//2.4.2.7 <CodUfficioPostale> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'CodUfficioPostale', '' ) );

            //2.4.2.8 <CognomeQuietanzante> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'CognomeQuietanzante', '' ) );

            //2.4.2.9 <NomeQuietanzante> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'NomeQuietanzante', '' ) );

            //2.4.2.10 <CFQuietanzante> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'CFQuietanzante', '' ) );

            //2.4.2.11 <TitoloQuietanzante> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'TitoloQuietanzante', '' ) );*/

            if( $dati_banca_pagamento["nome_istituto"] != "" ){
                //2.4.2.12 <IstitutoFinanziario> <0.1>
                $DettaglioPagamento->appendChild($domtree->createElement( 'IstitutoFinanziario', $dati_banca_pagamento["nome_istituto"] ) );
            }

            if( $dati_banca_pagamento["nome_istituto"] != "" && $dati_banca_pagamento["iban"] ){
                //2.4.2.13 <IBAN> <0.1>
                $DettaglioPagamento->appendChild($domtree->createElement( 'IBAN', $dati_banca_pagamento["iban"] ) );
            }

            if( $dati_banca_pagamento["nome_istituto"] != "" && $dati_banca_pagamento["abi"] ){
                //2.4.2.14 <ABI> <0.1>
                $DettaglioPagamento->appendChild($domtree->createElement( 'ABI', $dati_banca_pagamento["abi"] ) );
            }

            if( $dati_banca_pagamento["nome_istituto"] != "" && $dati_banca_pagamento["cab"] ){
                //2.4.2.15 <CAB> <0.1>
                $DettaglioPagamento->appendChild($domtree->createElement( 'CAB', $dati_banca_pagamento["cab"] ) );
            }

            if( $dati_banca_pagamento["nome_istituto"] != "" && $dati_banca_pagamento["bic"] ){
                //2.4.2.16 <BIC> <0.1>
                $DettaglioPagamento->appendChild($domtree->createElement( 'BIC', $dati_banca_pagamento["bic"] ) );
            }

            /*//2.4.2.17 <ScontoPagamentoAnticipato> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'ScontoPagamentoAnticipato', '' ) );

            //2.4.2.18 <DataLimitePagamentoAnticipato> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'DataLimitePagamentoAnticipato', '' ) );

            //2.4.2.19 <PenalitaPagamentiRitardati> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'PenalitaPagamentiRitardati', '' ) );

            //2.4.2.20 <DataDecorrenzaPenale> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'DataDecorrenzaPenale', '' ) );

            //2.4.2.21 <CodicePagamento> <0.1>
            $DettaglioPagamento->appendChild($domtree->createElement( 'CodicePagamento', '' ) );*/

        return $DettaglioPagamento;

    }

    function getDatiPagamento(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $lista_scadenze = $this->getScadenzeFattura($id);

        //2.4 <DatiPagamento> <0.N>
        $DatiPagamento = $domtree->createElement( "DatiPagamento" );

            if( count($lista_scadenze) > 1 ){
                //2.4.1 <CondizioniPagamento> * <1.1>
                $DatiPagamento->appendChild($domtree->createElement( 'CondizioniPagamento', 'TP01' ) );
            }
            else{
                $DatiPagamento->appendChild($domtree->createElement( 'CondizioniPagamento', 'TP02' ) );
            }

            foreach( $lista_scadenze as $scadenza ){
                //2.4.2 <DettaglioPagamento> <1.N>
                $DatiPagamento->appendChild( $this->getDettaglioPagamento($domtree, $id, $scadenza) );
            }

        return $DatiPagamento;

    }

    function getAllegati(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        //2.5 <Allegati> <0.N>
        $Allegati = $domtree->createElement( "Allegati" );

            //2.5.1 <NomeAttachment> * <1.1>
            $Allegati->appendChild($domtree->createElement( 'NomeAttachment', '' ) );

            //2.5.2 <AlgoritmoCompressione> <0.1>
            $Allegati->appendChild($domtree->createElement( 'AlgoritmoCompressione', '' ) );

            //2.5.3 <FormatoAttachment> <0.1>
            $Allegati->appendChild($domtree->createElement( 'FormatoAttachment', '' ) );

            //2.5.4 <DescrizioneAttachment> <0.1>
            $Allegati->appendChild($domtree->createElement( 'DescrizioneAttachment', '' ) );

            //2.5.5 <Attachment> * <1.1>
            $Allegati->appendChild($domtree->createElement( 'Attachment', '' ) );

        return $Allegati;

    }

    function getBodyFatturaElettronica(DOMDocument $domtree, $id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false);

        $tipo_documento = $focus_fattura->column_fields["kp_tipo_documento"];
        $tipo_documento = html_entity_decode(strip_tags($tipo_documento), ENT_QUOTES, $default_charset);

        $lista_scadenze = $this->getScadenzeFattura($id);

        $xmlRoot = $domtree->getElementsByTagName( "p:FatturaElettronica" );

        if( $xmlRoot->length > 0 ){

            $xmlRoot = $xmlRoot->item(0);

            //2 <FatturaElettronicaBody> * <1.N>
            $FatturaElettronicaBody = $domtree->createElement( "FatturaElettronicaBody" );
            $FatturaElettronicaBody = $xmlRoot->appendChild( $FatturaElettronicaBody );

                //2.1 <DatiGenerali> * <1.1>
                $FatturaElettronicaBody->appendChild( $this->getDatiGenerali($domtree, $id) );

                //2.2 <DatiBeniServizi> * <1.1>
                $FatturaElettronicaBody->appendChild( $this->getDatiBeniServizi($domtree, $id) );
                
                //2.3 <DatiVeicoli> <0.1>
                //$FatturaElettronicaBody->appendChild( $this->getDatiVeicoli($domtree, $id) );

                if( $tipo_documento != "Nota di credito" && count($lista_scadenze) > 0 ){
                    //2.4 <DatiPagamento> <0.N>
                    $FatturaElettronicaBody->appendChild( $this->getDatiPagamento($domtree, $id) );
                }

                //2.5 <Allegati> <0.N>
                //$FatturaElettronicaBody->appendChild( $this->getAllegati($domtree, $id) );

        }

        return $domtree;

    }

    function getRigheFattura($id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        $query = "SELECT 
                    rel.sequence_no sequence_no,
                    rel.productid productid,
                    rel.quantity quantity,
                    rel.listprice listprice,
                    rel.discount_percent discount_percent,
                    rel.discount_amount discount_amount,
                    rel.total_notaxes total_notaxes,
                    rel.comment comment,
                    rel.description description,
                    rel.lineitem_id lineitem_id,
                    rel.linetotal linetotal,
                    rel.tax1 tax1,
                    rel.tax_total tax_total,
                    ent.setype setype,
                    kprel.id_tassa id_tassa, 
                    tax.kp_codice_iva codice_iva, 
                    tax.percentage percentage, 
                    tax.kp_natura natura, 
                    tax.kp_norma norma 
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.productid
                    INNER JOIN kp_inventoryproductrel kprel ON kprel.lineitem_id = rel.lineitem_id AND kprel.id = rel.id
                    INNER JOIN {$table_prefix}_inventorytaxinfo tax ON tax.taxname = kprel.id_tassa
                    WHERE rel.id = ".$id."
                    ORDER BY rel.sequence_no ASC";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $sequence_no = $adb->query_result($result_query, $i, 'sequence_no');
            $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES, $default_charset);

            $productid = $adb->query_result($result_query, $i, 'productid');
            $productid = html_entity_decode(strip_tags($productid), ENT_QUOTES, $default_charset);

            $quantity = $adb->query_result($result_query, $i, 'quantity');
            $quantity = html_entity_decode(strip_tags($quantity), ENT_QUOTES, $default_charset);
            $quantity = number_format($quantity, 2, ".", "");

            $listprice = $adb->query_result($result_query, $i, 'listprice');
            $listprice = html_entity_decode(strip_tags($listprice), ENT_QUOTES, $default_charset);
            $listprice = number_format($listprice, 2, ".", "");

            $discount_percent = $adb->query_result($result_query, $i, 'discount_percent');
            $discount_percent = html_entity_decode(strip_tags($discount_percent), ENT_QUOTES, $default_charset);
            if( $discount_percent == null || $discount_percent == 0 ){
                $discount_percent = 0;
            }
            $discount_percent = number_format($discount_percent, 2, ".", "");

            $discount_amount = $adb->query_result($result_query, $i, 'discount_amount');
            $discount_amount = html_entity_decode(strip_tags($discount_amount), ENT_QUOTES, $default_charset);
            if( $discount_amount == null || $discount_amount == 0 ){
                $discount_amount = 0;
            }
            $discount_amount = number_format($discount_amount, 2, ".", "");

            $total_notaxes = $adb->query_result($result_query, $i, 'total_notaxes');
            $total_notaxes = html_entity_decode(strip_tags($total_notaxes), ENT_QUOTES, $default_charset);
            $total_notaxes = number_format($total_notaxes, 2, ".", "");

            $comment = $adb->query_result($result_query, $i, 'comment');
            $comment = html_entity_decode(strip_tags($comment), ENT_QUOTES, $default_charset);
            $comment = $this->replaceSpecialChart($comment);

            $description = $adb->query_result($result_query, $i, 'description');
            $description = html_entity_decode(strip_tags($description), ENT_QUOTES, $default_charset);

            $lineitem_id = $adb->query_result($result_query, $i, 'lineitem_id');
            $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES, $default_charset);

            $linetotal = $adb->query_result($result_query, $i, 'linetotal');
            $linetotal = html_entity_decode(strip_tags($linetotal), ENT_QUOTES, $default_charset);
            $linetotal = number_format($linetotal, 2, ".", "");

            $tax_total = $adb->query_result($result_query, $i, 'tax_total');
            $tax_total = html_entity_decode(strip_tags($tax_total), ENT_QUOTES, $default_charset);
            $tax_total = number_format($tax_total, 2, ".", "");

            $tax1 = $adb->query_result($result_query, $i, 'tax1');
            $tax1 = html_entity_decode(strip_tags($tax1), ENT_QUOTES, $default_charset);
            if( $tax1 == null || $tax1 == '' ){
                $tax1 = 0;
            }

            $tax1 = number_format($tax1, 2, ".", "");

            $setype = $adb->query_result($result_query, $i, 'setype');
            $setype = html_entity_decode(strip_tags($setype), ENT_QUOTES, $default_charset);

            if( $setype == 'Services' ){

                $focus_servizio = CRMEntity::getInstance('Services');
                $focus_servizio->retrieve_entity_info($productid, "Services", $dieOnError=false); 

                $nome_prodotto = $focus_servizio->column_fields["servicename"];
                $nome_prodotto = html_entity_decode(strip_tags($nome_prodotto), ENT_QUOTES, $default_charset);

                $numero_prodotto = $focus_servizio->column_fields["service_no"];
                $numero_prodotto = html_entity_decode(strip_tags($numero_prodotto), ENT_QUOTES, $default_charset);

                $unita_di_misura = $focus_servizio->column_fields["service_usageunit"];
                $unita_di_misura = html_entity_decode(strip_tags($unita_di_misura), ENT_QUOTES, $default_charset);

                $codice_erp = $focus_servizio->column_fields["kp_codice_erp"];
                $codice_erp = html_entity_decode(strip_tags($codice_erp), ENT_QUOTES, $default_charset);
                
                if( $codice_erp == null || $codice_erp == "" ){
                    $codice_prodotto = $numero_prodotto;
                }
                else{
                    $codice_prodotto = $codice_erp;
                }

            }
            else{

                $focus_prodotto = CRMEntity::getInstance('Products');
                $focus_prodotto->retrieve_entity_info($productid, "Products", $dieOnError=false); 

                $nome_prodotto = $focus_prodotto->column_fields["productname"];
                $nome_prodotto = html_entity_decode(strip_tags($nome_prodotto), ENT_QUOTES, $default_charset);

                $numero_prodotto = $focus_prodotto->column_fields["product_no"];
                $numero_prodotto = html_entity_decode(strip_tags($numero_prodotto), ENT_QUOTES, $default_charset);

                $unita_di_misura = $focus_prodotto->column_fields["usageunit"];
                $unita_di_misura = html_entity_decode(strip_tags($unita_di_misura), ENT_QUOTES, $default_charset);

                $codice_erp = $focus_prodotto->column_fields["kp_codice_erp"];
                $codice_erp = html_entity_decode(strip_tags($codice_erp), ENT_QUOTES, $default_charset);
                
                $codice_prodotto = $numero_prodotto;

            }

            if( $description == null || $description == '' ){
                $description = $nome_prodotto;
            }
            else{
                $description = $nome_prodotto." - ".$description;
            }
            $description = $this->replaceSpecialChart($description);

            $id_tassa = $adb->query_result($result_query, $i, 'id_tassa');
            $id_tassa = html_entity_decode(strip_tags($id_tassa), ENT_QUOTES, $default_charset);

            $codice_iva = $adb->query_result($result_query, $i, 'codice_iva');
            $codice_iva = html_entity_decode(strip_tags($codice_iva), ENT_QUOTES, $default_charset);

            $percentage = $adb->query_result($result_query, $i, 'percentage');
            $percentage = html_entity_decode(strip_tags($percentage), ENT_QUOTES, $default_charset);
            if( $percentage == "" ){
                $percentage = 0;
            }
            $percentage = number_format($percentage, 2, ".", "");

            $natura = $adb->query_result($result_query, $i, 'natura');
            $natura = html_entity_decode(strip_tags($natura), ENT_QUOTES, $default_charset);
            if( $natura == "" ){
                $natura = "";
            }

            $norma = $adb->query_result($result_query, $i, 'norma');
            $norma = html_entity_decode(strip_tags($norma), ENT_QUOTES, $default_charset);
            if( $norma == "" ){
                $norma = "";
            }
            $norma = $this->replaceSpecialChart($norma);

            $result[] = array("sequence_no" => $sequence_no,
                            "productid" => $productid,
                            "quantity" => $quantity,
                            "listprice" => $listprice,
                            "discount_percent" => $discount_percent,
                            "discount_amount" => $discount_amount,
                            "total_notaxes" => $total_notaxes,
                            "comment" => $comment,
                            "description" => $description,
                            "lineitem_id" => $lineitem_id,
                            "linetotal" => $linetotal,
                            "tax_total" => $tax_total,
                            "tax1" => $tax1,
                            "nome_prodotto" => $nome_prodotto,
                            "codice_prodotto" => $codice_prodotto,
                            "unita_di_misura" => $unita_di_misura,
                            "id_tassa" => $id_tassa,
                            "codice_iva" => $codice_iva,
                            "percentage" => $percentage,
                            "natura" => $natura,
                            "norma" => $norma);

        }

        return $result;

    }

    function getValoriDatiRiepilogoFattura($id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        $query = "SELECT 
                    rel.sequence_no sequence_no,
                    rel.productid productid,
                    rel.quantity quantity,
                    rel.listprice listprice,
                    rel.discount_percent discount_percent,
                    rel.discount_amount discount_amount,
                    rel.total_notaxes total_notaxes,
                    rel.comment comment,
                    rel.description description,
                    rel.lineitem_id lineitem_id,
                    rel.linetotal linetotal,
                    rel.tax1 tax1,
                    rel.tax_total tax_total,
                    ent.setype setype,
                    kprel.id_tassa id_tassa, 
                    tax.kp_codice_iva codice_iva, 
                    tax.percentage percentage, 
                    tax.kp_natura natura, 
                    tax.kp_norma norma 
                    FROM {$table_prefix}_inventoryproductrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.productid
                    INNER JOIN kp_inventoryproductrel kprel ON kprel.lineitem_id = rel.lineitem_id AND kprel.id = rel.id
                    INNER JOIN {$table_prefix}_inventorytaxinfo tax ON tax.taxname = kprel.id_tassa
                    WHERE rel.id = ".$id."
                    ORDER BY rel.sequence_no ASC";
                    
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $sequence_no = $adb->query_result($result_query, $i, 'sequence_no');
            $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES, $default_charset);

            $lineitem_id = $adb->query_result($result_query, $i, 'lineitem_id');
            $lineitem_id = html_entity_decode(strip_tags($lineitem_id), ENT_QUOTES, $default_charset);

            $total_notaxes = $adb->query_result($result_query, $i, 'total_notaxes');
            $total_notaxes = html_entity_decode(strip_tags($total_notaxes), ENT_QUOTES, $default_charset);
            if( $total_notaxes == null || $total_notaxes == 0 ){
                $total_notaxes = 0;
            }
            $total_notaxes = number_format($total_notaxes, 2, ".", "");

            $tax1 = $adb->query_result($result_query, $i, 'tax1');
            $tax1 = html_entity_decode(strip_tags($tax1), ENT_QUOTES, $default_charset);
            if( $tax1 == null || $tax1 == '' ){
                $tax1 = 0;
            }

            $tax_total = $adb->query_result($result_query, $i, 'tax_total');
            $tax_total = html_entity_decode(strip_tags($tax_total), ENT_QUOTES, $default_charset);
            if( $tax_total == null || $tax_total == '' ){
                $tax_total = 0;
            }
            $tax_total = number_format($tax_total, 2, ".", "");

            $linetotal = $adb->query_result($result_query, $i, 'linetotal');
            $linetotal = html_entity_decode(strip_tags($linetotal), ENT_QUOTES, $default_charset);
            if( $linetotal == null || $linetotal == '' ){
                $linetotal = 0;
            }
            $linetotal = number_format($linetotal, 2, ".", "");

            $id_tassa = $adb->query_result($result_query, $i, 'id_tassa');
            $id_tassa = html_entity_decode(strip_tags($id_tassa), ENT_QUOTES, $default_charset);

            $codice_iva = $adb->query_result($result_query, $i, 'codice_iva');
            $codice_iva = html_entity_decode(strip_tags($codice_iva), ENT_QUOTES, $default_charset);

            $percentage = $adb->query_result($result_query, $i, 'percentage');
            $percentage = html_entity_decode(strip_tags($percentage), ENT_QUOTES, $default_charset);
            if( $percentage == "" ){
                $percentage = 0;
            }

            $natura = $adb->query_result($result_query, $i, 'natura');
            $natura = html_entity_decode(strip_tags($natura), ENT_QUOTES, $default_charset);
            if( $natura == "" ){
                $natura = "";
            }

            $norma = $adb->query_result($result_query, $i, 'norma');
            $norma = html_entity_decode(strip_tags($norma), ENT_QUOTES, $default_charset);
            if( $norma == "" ){
                $norma = "";
            }
            $norma = $this->replaceSpecialChart($norma);

            $totale_imponibile = 0;
            $totale_imposta = 0;

            $tax1 = number_format($tax1, 2, ".", "");
            $percentage = number_format($percentage, 2, ".", "");

            if( array_key_exists($id_tassa, $result) ) {
                $totale_imponibile = $result[$id_tassa]["totale_imponibile"] + $total_notaxes;
                $totale_imposta = $result[$id_tassa]["totale_imposta"] + $tax_total;
            }
            else{
                $totale_imponibile = $total_notaxes;
                $totale_imposta = $tax_total;
            }

            $totale_imponibile = number_format($totale_imponibile, 2, ".", "");
            $totale_imposta = number_format($totale_imposta, 2, ".", "");

            $result[$id_tassa] = array("aliquota_iva" => $tax1,
                                        "totale_imponibile" => $totale_imponibile,
                                        "totale_imposta" => $totale_imposta,
                                        "natura" => $natura,
                                        "normativa" => $norma);

        }

        return $result;

    }

    function getScadenzeFattura($id){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        $query = "SELECT
                    scad.scadenziarioid id
                    FROM {$table_prefix}_scadenziario scad
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = scad.scadenziarioid
                    WHERE ent.deleted = 0 AND scad.invoice = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $result[] = $id;

        }

        return $result;

    }

    function getProgressivoInvioFatturaElettronica($business_unit, $fattura = 0){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = "";

        $query = "(SELECT 
                    num.modulenumberingid id,
                    num.use_prefix use_prefix,
                    num.start_sequence start_sequence
                    FROM {$table_prefix}_modulenumbering num
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
                    INNER JOIN {$table_prefix}_crmentityrel rel ON rel.crmid = num.modulenumberingid
                    WHERE ent.deleted = 0 AND num.select_module = 'ProgFatE' AND rel.module = 'ModuleNumbering' AND rel.relmodule = 'KpBusinessUnit' AND rel.relcrmid = ".$business_unit.")
                    UNION
                    (SELECT 
                    num.modulenumberingid id,
                    num.use_prefix use_prefix,
                    num.start_sequence start_sequence
                    FROM {$table_prefix}_modulenumbering num
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
                    INNER JOIN {$table_prefix}_crmentityrel rel ON rel.relcrmid = num.modulenumberingid
                    WHERE ent.deleted = 0 AND num.select_module = 'ProgFatE' AND rel.relmodule = 'ModuleNumbering' AND rel.module = 'KpBusinessUnit' AND rel.crmid = ".$business_unit.")";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $id = $adb->query_result($result_query, 0, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $use_prefix = $adb->query_result($result_query, 0, 'use_prefix');
            $use_prefix = html_entity_decode(strip_tags($use_prefix), ENT_QUOTES, $default_charset);

            $start_sequence = $adb->query_result($result_query, 0, 'start_sequence');
            $start_sequence = html_entity_decode(strip_tags($start_sequence), ENT_QUOTES, $default_charset);

            $result = $use_prefix.$start_sequence;

            $length_sequence = strlen($start_sequence);			
            $start_sequence = (int)$start_sequence;

            if( $start_sequence == 9999 ){
                $start_sequence = 1;
            }

            $start_sequence++;
            $start_sequence = str_pad($start_sequence, $length_sequence, "0", STR_PAD_LEFT);
            
            $update = "UPDATE {$table_prefix}_modulenumbering
                        SET start_sequence ='".$start_sequence."'
                        WHERE modulenumberingid =".$id;
            $adb->query($update);

            if( $fattura != 0 ){
                $update = "UPDATE {$table_prefix}_invoice
                            SET kp_prog_inv_fe = '".$result."'
                            WHERE invoiceid =".$fattura;
                $adb->query($update);
            }

        }

        return $result;

    }

    function setFileInDocumentoFattura($id, $file_path, $file_name){
        global $adb, $table_prefix, $default_charset, $current_user, $dbconfig;

        $focus_fattura = CRMEntity::getInstance('Invoice');
        $focus_fattura->retrieve_entity_info($id, "Invoice", $dieOnError=false); 

        $invoice_number = $focus_fattura->column_fields["invoice_number"];
        $invoice_number = html_entity_decode(strip_tags($invoice_number), ENT_QUOTES, $default_charset);

        $nome_documento = "Fattura Elettronica ".$id." ".$invoice_number;

        $id_statici = $this->getConfigurazioniIdStatici();
        $id_statico_cartella = $id_statici["Documenti - Cartella Fatture Elettroniche"];
        if( $id_statico_cartella["valore"] == "" && $id_statico_cartella["valore"] == 0 ){
            $cartella_documenti = 1;
        }
        else{
            $cartella_documenti = $id_statico_cartella["valore"];
        }

        $utente = $current_user->id;
        if($utente == null || $utente == "" || $utente == 0){
            $utente = 1;
        }

        $document = CRMEntity::getInstance('Documents'); 
        $document->parentid = $id;
        
        $document->column_fields["notes_title"] = $nome_documento;
        $document->column_fields["assigned_user_id"] = $utente;
        $document->column_fields["filename"] = $file_name;
        $document->column_fields["filetype"] = "text/xml"; 
        $document->column_fields["filesize"] = filesize($file_path.$file_name); 
        $document->column_fields["filelocationtype"] = "I"; 
        $document->column_fields["fileversion"] = '';
        $document->column_fields["filestatus"] = "on";
        $document->column_fields["folderid"] = $cartella_documenti;
        $document->column_fields["stato_documento"] = '';
        $document->column_fields["kp_data_documento"] = date('Y-m-d');

        $document->save("Documents", $longdesc=true, $offline_update=false, $triggerEvent=false);
        $document_id = $document->id;

        $current_id = $adb->getUniqueID($table_prefix."_crmentity");

        $filesize = filesize($file_path.$current_id."_".$file_name);
        $filetype = "text/xml";

        $query = "INSERT INTO ".$table_prefix."_crmentity (crmid, smcreatorid, smownerid, setype, description, createdtime, modifiedtime) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $params = array($current_id, $utente, $utente, "Documents Attachment", "", $adb->formatDate(date("Y-m-d H:i:s"), true), $adb->formatDate(date("Y-m-d H:i:s"), true));
        $adb->pquery($query, $params);
    
        $query = "INSERT INTO ".$table_prefix."_attachments (attachmentsid, name, description, type, path) VALUES (?, ?, ?, ?, ?)";
        $params = array($current_id, $file_name, "", $filetype, $file_path);
        $result=$adb->pquery($query, $params);
    
        $query = 'INSERT INTO '.$table_prefix.'_seattachmentsrel VALUES (?, ?)';
        $adb->pquery($query, array($document_id, $current_id));

        $update = "UPDATE ".$table_prefix."_notes SET filesize=?, filename=? WHERE notesid=?";
        $adb->pquery($update, array($filesize, $file_name, $document_id));

        @rename($file_path.$file_name, $file_path.$current_id."_".$file_name);

    }

    function getConfigurazioniIdStatici(){
        global $adb, $table_prefix, $default_charset, $current_user, $dbconfig;

        $result = array();

        $query = "SELECT 
                    * 
                    FROM information_schema.tables
                    WHERE table_schema = '".$dbconfig['db_name']."' AND table_name = 'kp_settings_config_id_statici'";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);
            
        if( $num_result > 0 ){

            $query = "SELECT 
                        * 
                        FROM kp_settings_config_id_statici";

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);

            for( $i=0; $i < $num_result; $i++ ){

                $id_configurazione = $adb->query_result($result_query, $i, 'id_configurazione');
                $id_configurazione = html_entity_decode(strip_tags($id_configurazione), ENT_QUOTES,$default_charset);
                
                $nome_area_configurazione = $adb->query_result($result_query, $i, 'nome_area_configurazione');
                $nome_area_configurazione = html_entity_decode(strip_tags($nome_area_configurazione), ENT_QUOTES,$default_charset);

                $nome_configurazione = $adb->query_result($result_query, $i, 'nome_configurazione');
                $nome_configurazione = html_entity_decode(strip_tags($nome_configurazione), ENT_QUOTES,$default_charset);

                $valore = $adb->query_result($result_query, $i, 'valore');
                $valore = html_entity_decode(strip_tags($valore), ENT_QUOTES,$default_charset);

                $chiave = $nome_area_configurazione." - ".$nome_configurazione;

                $result[$chiave] = array("nome_area_configurazione" => $nome_area_configurazione,
                                        "nome_configurazione" => $nome_configurazione,
                                        "valore" => $valore);

            }

        }

        return $result;

    }

    function controlliPreliminariFatturaPerFattureElettroniche($lista_fatture){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();

        foreach($lista_fatture as $fattura){

            $check = 1;

            $focus_fattura = CRMEntity::getInstance('Invoice');
            $focus_fattura->retrieve_entity_info($fattura, "Invoice", $dieOnError=false); 

            $invoice_number = $focus_fattura->column_fields["invoice_number"];
            $invoice_number = html_entity_decode(strip_tags($invoice_number), ENT_QUOTES, $default_charset);
            $invoice_number = trim($invoice_number);
            if( $invoice_number != null && $invoice_number != "" && $invoice_number !== 0 ){
                $invoice_number_check = 1;
            }
            else{
                $invoice_number_check = "Fattura priva di numerazione.";
                $check = 0;
            }
            
            $account_id = $focus_fattura->column_fields["account_id"];
            $account_id = html_entity_decode(strip_tags($account_id), ENT_QUOTES, $default_charset);
            if( $account_id != null && $account_id != "" && $account_id !== 0 ){
                
                $controllo_cliente = $this->controlliPreliminariClientePerFatturaElettronica($account_id);
                if( $controllo_cliente["check"] == 1 ){
                    $account_id_check = 1;
                }
                else{
                    $account_id_check = "Anomalia presente nel cliente: ".$controllo_cliente["accountname"];
                    $check = 0;
                }

            }
            else{
                $account_id_check = "Fattura priva di cliente.";
                $check = 0;
            }

            $business_unit = $focus_fattura->column_fields["kp_business_unit"];
            $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES, $default_charset);
            if( $business_unit != null && $business_unit != "" && $business_unit !== 0 ){

                $controllo_bu = $this->controlliPreliminariBusinessUnitPerFatturaElettronica($business_unit);
                if( $controllo_bu["check"] == 1 ){
                    $business_unit_check = 1;
                }
                else{
                    $business_unit_check = "Anomalia presente nella business unit: ".$controllo_bu["nome"];
                    $check = 0;
                }

            }
            else{
                $business_unit_check = "Fattura priva di business unit.";
                $check = 0;
            }

            $mod_pagamento = $focus_fattura->column_fields["mod_pagamento"];
            $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES, $default_charset);
            if( $mod_pagamento != null && $mod_pagamento != "" && $mod_pagamento !== 0 ){
                $mod_pagamento_check = 1;
            }
            else{
                $mod_pagamento_check = "Fattura priva di modalita di pagamento.";
                $check = 0;
            }

            $invoicedate = $focus_fattura->column_fields["invoicedate"];
            $invoicedate = html_entity_decode(strip_tags($invoicedate), ENT_QUOTES, $default_charset);
            if( $invoicedate != null && $invoicedate != "" && $invoicedate !== 0 && $invoicedate != "0000-00-00" ){
                $invoicedate_check = 1;
            }
            else{
                $invoicedate_check = "Fattura priva di data.";
                $check = 0;
            }

            $check_xml = $this->checkEsisteDocumentoFatturaElettronica($fattura);
            
            if( $check_xml["esiste"] ){
                $esistenza_xml_check = "Esiste già un documento XML generato per questa fattura.";
                $check = 0;
            }
            else{
                $esistenza_xml_check = 1;
            }

            $check_righe_prive_di_iva = $this->getRigheTassazioneNonCoerente($fattura);
            //print_r($check_righe_prive_di_iva);die;
            if( $check_righe_prive_di_iva["esiste"] ){
                $check = 0;
                $tasse_righe_check = "Sono presenti righe con tassazione non coerente";
            }
            else{
                $tasse_righe_check = 1;
            }

            $result[] = array("id" => $fattura,
                            "invoice_number" => $invoice_number,
                            "invoice_number_check" => $invoice_number_check,
                            "account_id_check" => $account_id_check,
                            "business_unit_check" => $business_unit_check,
                            "mod_pagamento_check" => $mod_pagamento_check,
                            "invoicedate_check" => $invoicedate_check,
                            "esistenza_xml_check" => $esistenza_xml_check,
                            "tasse_righe_check" => $tasse_righe_check,
                            "check" => $check);

        }

        return $result;

    }

    function getTabellaControlliFatturaPerFattureElettroniche($lista){
        global $adb, $table_prefix, $current_user, $default_charset;

        $html = "<table class='table table-striped'>";
        $html .= "<thead>";
        $html .= "<th>Numero Fattura</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Data Fattura</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Cliente</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Business Unit</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Modalità di Pagamento</th>";
        $html .= "<th style='text-align: center; width: 10%;'>XML</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Tasse Righe</th>";
        $html .= "<th style='text-align: center; width: 80px;'></th>";
        $html .= "</thead>";
        $html .= "<tbody>";

        foreach($lista as $record){

            $html .= "<tr>";

            if( $record["invoice_number_check"] == 1 ){
                $html .= "<td>".$record["invoice_number"]."</td>";
            }
            else{
                $html .= "<td style='text-align: left; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["invoice_number_check"]."'>clear</i></td>";
            }

            if( $record["invoicedate_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["invoicedate_check"]."'>clear</i></td>";
            }

            if( $record["account_id_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["account_id_check"]."'>clear</i></td>";
            }

            if( $record["business_unit_check"] == 1 ){
                $html .= "<td style='text-align: center; '><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["business_unit_check"]."'>clear</i></td>";
            }

            if( $record["mod_pagamento_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["mod_pagamento_check"]."'>clear</i></td>";
            }

            if( $record["esistenza_xml_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["esistenza_xml_check"]."'>clear</i></td>";
            }

            if( $record["tasse_righe_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["tasse_righe_check"]."'>clear</i></td>";
            }

            $html .= "<td style='text-align: center;'><i style='cursor: pointer;' class='material-icons select_invoice' id='".$record["id"]."' >input</i></td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        return $html;

    }

    function getRigheTassazioneNonCoerente($fattura){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = "";
        $esiste = false;
        $array_righe = array();

        $query = "SELECT 
                    rel.sequence_no sequence_no,
                    rel.productid productid,
                    rel.lineitem_id lineitem_id,
                    rel.total_notaxes total_notaxes,
                    rel.linetotal linetotal,
                    rel.tax_total tax_total,
                    rel.tax1 tax1,
                    kprel.id_tassa id_tassa,
                    tax.kp_codice_iva codice_iva,
                    tax.percentage percentage,
                    tax.kp_natura natura,
                    tax.kp_norma norma
                    FROM {$table_prefix}_inventoryproductrel rel
                    LEFT JOIN kp_inventoryproductrel kprel ON kprel.lineitem_id = rel.lineitem_id AND kprel.id = rel.id
                    LEFT JOIN {$table_prefix}_inventorytaxinfo tax ON tax.taxname = kprel.id_tassa
                    WHERE rel.id = ".$fattura;
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i=0; $i < $num_result; $i++ ){

            $sequence_no = $adb->query_result($result_query, $i, 'sequence_no');
            $sequence_no = html_entity_decode(strip_tags($sequence_no), ENT_QUOTES,$default_charset);

            $total_notaxes = $adb->query_result($result_query, $i, 'total_notaxes');
            $total_notaxes = html_entity_decode(strip_tags($total_notaxes), ENT_QUOTES,$default_charset);

            $percentage = $adb->query_result($result_query, $i, 'percentage');
            $percentage = html_entity_decode(strip_tags($percentage), ENT_QUOTES,$default_charset);
            if( $percentage == null ){
                $percentage = "";
            }

            $natura = $adb->query_result($result_query, $i, 'natura');
            $natura = html_entity_decode(strip_tags($natura), ENT_QUOTES,$default_charset);
            if( $natura == null ){
                $natura = "";
            }

            $norma = $adb->query_result($result_query, $i, 'norma');
            $norma = html_entity_decode(strip_tags($norma), ENT_QUOTES,$default_charset);
            if( $norma == null ){
                $norma = "";
            }

            $tax_total = $adb->query_result($result_query, $i, 'tax_total');
            $tax_total = html_entity_decode(strip_tags($tax_total), ENT_QUOTES,$default_charset);
            if( $tax_total == null || $tax_total == "" ){
                $tax_total = 0;
            }
            
            if($percentage == ""){
                $esiste = true;
                $array_righe[] = $sequence_no;
            }
            elseif( $percentage == 0 && ($natura == "" || $norma == "") ){
                $esiste = true;
                $array_righe[] = $sequence_no;
            }
            else{

                $tassa_calcolata = ( intval($percentage) * $total_notaxes ) / 100;
                $tassa_calcolata_arrotondata = round($tassa_calcolata, 2);

                $tassa_totale_arrotondata = round($tax_total, 2);

                if( $tassa_calcolata_arrotondata != $tassa_totale_arrotondata ){

                    $esiste = true;
                    $array_righe[] = $sequence_no;

                }

            }

        }

        $result = array("esiste" => $esiste,
                        "righe" => $array_righe);

        return $result;

    }

    function checkEsisteDocumentoFatturaElettronica($fattura){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = "";

        $query = "SELECT 
                    notes.notesid id
                    FROM {$table_prefix}_notes notes
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid
                    INNER JOIN {$table_prefix}_senotesrel rel ON rel.notesid = notes.notesid
                    WHERE ent.deleted = 0 AND notes.title LIKE 'Fattura Elettronica ".$fattura." %' AND rel.crmid = ".$fattura;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

        }
        else{

            $esiste = false;
            $id = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $id);
        
        return $result;

    }

    function controlliPreliminariClientePerFatturaElettronica($cliente){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = "";

        $check = 1;

        $query = "SELECT 
                    acc.accountid id,
                    acc.accountname accountname,
                    acc.kp_pec pec,
                    acc.kp_formato_trasm formato_trasmissione,
                    acc.kp_codice_nazione codice_nazione,
                    acc.kp_codice_id_fe codice_identificativo,
                    acc.crmv_vat_registration_number partita_iva,
                    acc.crmv_social_security_number codice_fiscale,
                    billadd.bill_city citta,
                    billadd.bill_code cap,
                    billadd.bill_country nazione,
                    billadd.bill_state provincia,
                    billadd.bill_street indirizzo
                    FROM {$table_prefix}_account acc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
                    INNER JOIN {$table_prefix}_accountbillads billadd ON billadd.accountaddressid = acc.accountid
                    WHERE ent.deleted = 0 AND acc.accountid = ".$cliente;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $accountname = $adb->query_result($result_query, 0, 'accountname');
            $accountname = html_entity_decode(strip_tags($accountname), ENT_QUOTES,$default_charset);
            $accountname = trim($accountname);
            if( $accountname != null && $accountname != "" ){
                $accountname_check = 1;
            }
            else{
                $accountname_check = "Azienda priva di ragione sociale.";
                $check = 0;
            }

            $formato_trasmissione = $adb->query_result($result_query, 0, 'formato_trasmissione');
            $formato_trasmissione = html_entity_decode(strip_tags($formato_trasmissione), ENT_QUOTES,$default_charset);
            $formato_trasmissione = trim($formato_trasmissione);
            if( $formato_trasmissione != null && $formato_trasmissione != "" ){
                $formato_trasmissione_check = 1;
            }
            else{
                $formato_trasmissione_check = "Azienda priva di formato di trasmissione.";
                $check = 0;
            }

            $codice_nazione = $adb->query_result($result_query, 0, 'codice_nazione');
            $codice_nazione = html_entity_decode(strip_tags($codice_nazione), ENT_QUOTES,$default_charset);
            $codice_nazione = trim($codice_nazione);
            if( $codice_nazione != null && $codice_nazione != "" ){
                $codice_nazione_check = 1;
            }
            else{
                $codice_nazione_check = "Azienda priva di codice nazione.";
                $check = 0;
            }

            /*$codice_identificativo = $adb->query_result($result_query, 0, 'codice_identificativo');
            $codice_identificativo = html_entity_decode(strip_tags($codice_identificativo), ENT_QUOTES,$default_charset);
            $codice_identificativo = trim($codice_identificativo);
            if( $codice_identificativo != null && $codice_identificativo != "" && $codice_identificativo !== 0 ){
                $codice_identificativo_check = 1;
            }
            else{

                $pec = $adb->query_result($result_query, 0, 'pec');
                $pec = html_entity_decode(strip_tags($pec), ENT_QUOTES,$default_charset);
                $pec = trim($pec);
                if( $pec != null && $pec != "" && $pec !== 0 ){
                    $codice_identificativo_check = 1;
                }
                else{
                    $codice_identificativo_check = "Azienda priva di codice identificativo e pec.";
                    $check = 0;
                }

            }*/

            $partita_iva = $adb->query_result($result_query, 0, 'partita_iva');
            $partita_iva = html_entity_decode(strip_tags($partita_iva), ENT_QUOTES,$default_charset);
            $partita_iva = trim($partita_iva);
            if( $partita_iva != null && $partita_iva != ""){
                $partita_iva_check = 1;
            }
            else{
                $codice_fiscale = $adb->query_result($result_query, 0, 'codice_fiscale');
                $codice_fiscale = html_entity_decode(strip_tags($codice_fiscale), ENT_QUOTES, $default_charset);
                $codice_fiscale = trim($codice_fiscale);
                if( $codice_fiscale != null && $codice_fiscale != ""){
                    $partita_iva_check = 1;
                }
                else{
                    $partita_iva_check = "Azienda priva di partita IVA e codice fiscale (per i privati è obbligatorio indicare almeno il codice fiscale).";
                    $check = 0;
                }
            }

            $citta = $adb->query_result($result_query, 0, 'citta');
            $citta = html_entity_decode(strip_tags($citta), ENT_QUOTES,$default_charset);
            $citta = trim($citta);
            if( $citta != null && $citta != "" ){
                $citta_check = 1;
            }
            else{
                $citta_check = "Azienda priva di citta.";
                $check = 0;
            }

            $cap = $adb->query_result($result_query, 0, 'cap');
            $cap = html_entity_decode(strip_tags($cap), ENT_QUOTES,$default_charset);
            $cap = trim($cap);
            if( $cap != null && $cap != "" ){
                $cap_check = 1;
            }
            else{
                $cap_check = "Azienda priva di CAP.";
                $check = 0;
            }

            $nazione = $adb->query_result($result_query, 0, 'nazione');
            $nazione = html_entity_decode(strip_tags($nazione), ENT_QUOTES,$default_charset);
            $nazione = trim($nazione);
            if( $nazione != null && $nazione != "" ){
                $nazione_check = 1;
            }
            else{
                $nazione_check = "Azienda priva di nazione.";
                $check = 0;
            }

            $indirizzo = $adb->query_result($result_query, 0, 'indirizzo');
            $indirizzo = html_entity_decode(strip_tags($indirizzo), ENT_QUOTES,$default_charset);
            $naziindirizzoone = trim($indirizzo);
            if( $indirizzo != null && $indirizzo != "" ){
                $indirizzo_check = 1;
            }
            else{
                $indirizzo_check = "Azienda priva di indirizzo.";
                $check = 0;
            }

        }
        else{

            $check = 0;
            $accountname = "";
            $accountname_check = "Azienda non più presente.";
            $formato_trasmissione_check = "Azienda non più presente.";
            $codice_nazione_check = "Azienda non più presente.";
            //$codice_identificativo_check = "Azienda non più presente.";
            $partita_iva_check = "Azienda non più presente.";
            $citta_check = "Azienda non più presente.";
            $cap_check = "Azienda non più presente.";
            $nazione_check = "Azienda non più presente.";
            $indirizzo_check = "Azienda non più presente.";
                
        }

        $result = array("id" => $cliente,
                        "accountname" => $accountname,
                        "accountname_check" => $accountname_check,
                        "formato_trasmissione_check" => $formato_trasmissione_check,
                        "codice_nazione_check" => $codice_nazione_check,
                        //"codice_identificativo_check" => $codice_identificativo_check,
                        "partita_iva_check" => $partita_iva_check,
                        "citta_check" => $citta_check,
                        "cap_check" => $cap_check,
                        "nazione_check" => $nazione_check,
                        "indirizzo_check" => $indirizzo_check,
                        "check" => $check);

        return $result;

    }

    function controlliPreliminariClientiPerFattureElettroniche($lista_fatture){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();
        $lista_clienti = array();

        foreach($lista_fatture as $fattura){

            $focus_fattura = CRMEntity::getInstance('Invoice');
            $focus_fattura->retrieve_entity_info($fattura, "Invoice", $dieOnError=false); 

            $account_id = $focus_fattura->column_fields["account_id"];
            $account_id = html_entity_decode(strip_tags($account_id), ENT_QUOTES, $default_charset);

            if( !in_array($account_id, $lista_clienti) ){
                array_push($lista_clienti, $account_id);
            }

        }

        foreach($lista_clienti as $cliente){

            $result[] = $this->controlliPreliminariClientePerFatturaElettronica($cliente);

        }

        return $result;

    }

    function getTabellaControlliClientePerFattureElettroniche($lista){
        global $adb, $table_prefix, $current_user, $default_charset;

        $html = "<table class='table table-striped'>";
        $html .= "<thead>";
        $html .= "<th>Ragione Sociale</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Partita Iva</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Formato Trasmissione</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Codice Nazione</th>";
        //$html .= "<th style='text-align: center; width: 10%;'>Codice Identificativo</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Nazione</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Comune</th>";
        $html .= "<th style='text-align: center; width: 10%;'>Indirizzo</th>";
        $html .= "<th style='text-align: center; width: 10%;'>CAP</th>";
        $html .= "<th style='text-align: center; width: 80px;'></th>";
        $html .= "</thead>";
        $html .= "<tbody>";

        foreach($lista as $record){

            $html .= "<tr>";

            if( $record["accountname_check"] == 1 ){
                $html .= "<td>".$record["accountname"]."</td>";
            }
            else{
                $html .= "<td style='text-align: left; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["accountname_check"]."'>clear</i></td>";
            }

            if( $record["partita_iva_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["partita_iva_check"]."'>clear</i></td>";
            }

            if( $record["formato_trasmissione_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["formato_trasmissione_check"]."'>clear</i></td>";
            }

            if( $record["codice_nazione_check"] == 1 ){
                $html .= "<td style='text-align: center; '><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["codice_nazione_check"]."'>clear</i></td>";
            }

            /*if( $record["codice_identificativo_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: yellow;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["codice_identificativo_check"]."'>warning</i></td>";
            }*/

            if( $record["nazione_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["nazione_check"]."'>clear</i></td>";
            }

            if( $record["citta_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["citta_check"]."'>clear</i></td>";
            }

            if( $record["indirizzo_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["indirizzo_check"]."'>clear</i></td>";
            }

            if( $record["cap_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["cap_check"]."'>clear</i></td>";
            }

            $html .= "<td style='text-align: center;'><i style='cursor: pointer;' class='material-icons select_account' id='".$record["id"]."' >input</i></td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        return $html;

    }

    function controlliPreliminariBUPerFattureElettroniche($lista_fatture){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = array();
        $lista_business_unit = array();

        foreach($lista_fatture as $fattura){

            $focus_fattura = CRMEntity::getInstance('Invoice');
            $focus_fattura->retrieve_entity_info($fattura, "Invoice", $dieOnError=false); 

            $business_unit = $focus_fattura->column_fields["kp_business_unit"];
            $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES, $default_charset);

            if( !in_array($business_unit, $lista_business_unit) ){
                array_push($lista_business_unit, $business_unit);
            }

        }

        foreach($lista_business_unit as $business_unit){

            $result[] = $this->controlliPreliminariBusinessUnitPerFatturaElettronica($business_unit);

        }

        return $result;

    }

    function controlliPreliminariBusinessUnitPerFatturaElettronica($business_unit){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = "";

        $check = 1;

        $query = "SELECT 
                    bu.kpbusinessunitid id,
                    bu.kp_ragione_sociale nome,
                    bu.kp_codice_nazione codice_nazione,
                    bu.kp_partita_iva partita_iva,
                    bu.kp_codice_fiscale codice_fiscale,
                    bu.kp_regime_fiscale regime_fiscale,
                    bu.kp_codice_naz_trasm codice_nazione_trasmittente,
                    bu.kp_cod_fisc_trasm codice_identificativo_trasmittente,
                    bu.kp_comune citta,
                    bu.kp_cap cap,
                    bu.kp_nazione nazione,
                    bu.kp_provincia provincia,
                    bu.kp_indirizzo indirizzo
                    FROM {$table_prefix}_kpbusinessunit bu
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = bu.kpbusinessunitid
                    WHERE ent.deleted = 0 AND bu.kpbusinessunitid = ".$business_unit;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);
            if( $nome != null && $nome != "" && $nome !== 0 ){
                $nome_check = 1;
            }
            else{
                $nome_check = "Business Unit priva di ragione sociale.";
                $check = 0;
            }

            $codice_nazione = $adb->query_result($result_query, 0, 'codice_nazione');
            $codice_nazione = html_entity_decode(strip_tags($codice_nazione), ENT_QUOTES,$default_charset);
            $codice_nazione = trim($codice_nazione);
            if( $codice_nazione != null && $codice_nazione != "" && $codice_nazione !== 0 ){
                $codice_nazione_check = 1;
            }
            else{
                $codice_nazione_check = "Business Unit di formato di codice nazione.";
                $check = 0;
            }

            $partita_iva = $adb->query_result($result_query, 0, 'partita_iva');
            $partita_iva = html_entity_decode(strip_tags($partita_iva), ENT_QUOTES,$default_charset);
            $partita_iva = trim($partita_iva);
            if( $partita_iva != null && $partita_iva != "" && $partita_iva !== 0 ){
                $partita_iva_check = 1;
            }
            else{
                $partita_iva_check = "Business Unit priva di codice partita IVA.";
                $check = 0;
            }

            $codice_fiscale = $adb->query_result($result_query, 0, 'codice_fiscale');
            $codice_fiscale = html_entity_decode(strip_tags($codice_fiscale), ENT_QUOTES,$default_charset);
            $codice_fiscale = trim($codice_fiscale);
            if( $codice_fiscale != null && $codice_fiscale != "" && $codice_fiscale !== 0 ){
                $codice_fiscale_check = 1;
            }
            else{

                $codice_fiscale_check = "Business Unit priva di codice fiscale.";
                $check = 0;

            }

            $regime_fiscale = $adb->query_result($result_query, 0, 'regime_fiscale');
            $regime_fiscale = html_entity_decode(strip_tags($regime_fiscale), ENT_QUOTES,$default_charset);
            $regime_fiscale = trim($regime_fiscale);
            if( $regime_fiscale != null && $regime_fiscale != "" && $regime_fiscale !== 0 ){
                $regime_fiscale_check = 1;
            }
            else{
                $regime_fiscale_check = "Business Unit priva di regime fiscale.";
                $check = 0;
            }

            $codice_nazione_trasmittente = $adb->query_result($result_query, 0, 'codice_nazione_trasmittente');
            $codice_nazione_trasmittente = html_entity_decode(strip_tags($codice_nazione_trasmittente), ENT_QUOTES,$default_charset);
            $codice_nazione_trasmittente = trim($codice_nazione_trasmittente);
            if( $codice_nazione_trasmittente != null && $codice_nazione_trasmittente != "" && $codice_nazione_trasmittente !== 0 ){
                $codice_nazione_trasmittente_check = 1;
            }
            else{
                $codice_nazione_trasmittente_check = "Business Unit priva di codice nazione trasmittente.";
                $check = 0;
            }

            $codice_identificativo_trasmittente = $adb->query_result($result_query, 0, 'codice_identificativo_trasmittente');
            $codice_identificativo_trasmittente = html_entity_decode(strip_tags($codice_identificativo_trasmittente), ENT_QUOTES,$default_charset);
            $codice_identificativo_trasmittente = trim($codice_identificativo_trasmittente);
            if( $codice_identificativo_trasmittente != null && $codice_identificativo_trasmittente != "" && $codice_identificativo_trasmittente !== 0 ){
                $codice_identificativo_trasmittente_check = 1;
            }
            else{
                $codice_identificativo_trasmittente_check = "Business Unit priva di codice identificativo trasmittente.";
                $check = 0;
            }

            $citta = $adb->query_result($result_query, 0, 'citta');
            $citta = html_entity_decode(strip_tags($citta), ENT_QUOTES,$default_charset);
            $citta = trim($citta);
            if( $citta != null && $citta != "" && $citta !== 0 ){
                $citta_check = 1;
            }
            else{
                $citta_check = "Business Unit priva di citta.";
                $check = 0;
            }

            $cap = $adb->query_result($result_query, 0, 'cap');
            $cap = html_entity_decode(strip_tags($cap), ENT_QUOTES,$default_charset);
            $cap = trim($cap);
            if( $cap != null && $cap != "" && $cap !== 0 ){
                $cap_check = 1;
            }
            else{
                $cap_check = "Business Unit priva di CAP.";
                $check = 0;
            }

            $nazione = $adb->query_result($result_query, 0, 'nazione');
            $nazione = html_entity_decode(strip_tags($nazione), ENT_QUOTES,$default_charset);
            $nazione = trim($nazione);
            if( $nazione != null && $nazione != "" && $nazione !== 0 ){
                $nazione_check = 1;
            }
            else{
                $nazione_check = "Business Unit priva di nazione.";
                $check = 0;
            }

            $indirizzo = $adb->query_result($result_query, 0, 'indirizzo');
            $indirizzo = html_entity_decode(strip_tags($indirizzo), ENT_QUOTES,$default_charset);
            $naziindirizzoone = trim($indirizzo);
            if( $indirizzo != null && $indirizzo != "" && $indirizzo !== 0 ){
                $indirizzo_check = 1;
            }
            else{
                $indirizzo_check = "Business Unit priva di indirizzo.";
                $check = 0;
            }

            $dati_numeratore = $this->checkCorrettezzaProgressivoInvioFatturaElettronica($business_unit);
            if( $dati_numeratore["esiste"] && $dati_numeratore["lunghezza_numeratore"] >= 1 && $dati_numeratore["lunghezza_numeratore"] <= 5 ){
                $numeratore_check = 1;
            }
            elseif( !$dati_numeratore["esiste"] ){
                $numeratore_check = "Business Unit priva di progressivo invio fatture elettroniche.";
                $check = 0;
            }
            elseif( $dati_numeratore["lunghezza_numeratore"] < 1){
                $numeratore_check = "Progressivo invio fatture elettroniche troppo corto.";
                $check = 0;
            }
            elseif( $dati_numeratore["lunghezza_numeratore"] > 5){
                $numeratore_check = "Progressivo invio fatture elettroniche troppo lungo (lunghezza massima 5).";
                $check = 0;
            }
            else{
                $numeratore_check = "Errore nel progressivo invio fatture elettroniche.";
                $check = 0;
            }

        }
        else{

            $check = 0;
            $nome = "";
            $nome_check = "Business Unit non più presente.";
            $codice_nazione_check = "Business Unit non più presente.";
            $partita_iva_check = "Business Unit non più presente.";
            $codice_fiscale_check = "Business Unit non più presente.";
            $regime_fiscale_check = "Business Unit non più presente.";
            $codice_nazione_trasmittente_check = "Business Unit non più presente.";
            $codice_identificativo_trasmittente_check = "Business Unit non più presente.";
            $citta_check = "Business Unit non più presente.";
            $cap_check = "Business Unit non più presente.";
            $nazione_check = "Business Unit non più presente.";
            $indirizzo_check = "Business Unit non più presente.";
            $numeratore_check = "Business Unit non più presente.";
                
        }

        $result = array("id" => $business_unit,
                        "nome" => $nome,
                        "nome_check" => $nome_check,
                        "codice_nazione_check" => $codice_nazione_check,
                        "partita_iva_check" => $partita_iva_check,
                        "codice_fiscale_check" => $codice_fiscale_check,
                        "regime_fiscale_check" => $regime_fiscale_check,
                        "codice_nazione_trasmittente_check" => $codice_nazione_trasmittente_check,
                        "codice_identificativo_trasmittente_check" => $codice_identificativo_trasmittente_check,
                        "citta_check" => $citta_check,
                        "cap_check" => $cap_check,
                        "nazione_check" => $nazione_check,
                        "indirizzo_check" => $indirizzo_check,
                        "numeratore_check" => $numeratore_check,
                        "check" => $check);

        return $result;

    }

    function checkCorrettezzaProgressivoInvioFatturaElettronica($business_unit){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = "";

        $query = "(SELECT 
                    num.modulenumberingid id,
                    num.use_prefix use_prefix,
                    num.start_sequence start_sequence
                    FROM {$table_prefix}_modulenumbering num
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
                    INNER JOIN {$table_prefix}_crmentityrel rel ON rel.crmid = num.modulenumberingid
                    WHERE ent.deleted = 0 AND num.select_module = 'ProgFatE' AND rel.module = 'ModuleNumbering' AND rel.relmodule = 'KpBusinessUnit' AND rel.relcrmid = ".$business_unit.")
                    UNION
                    (SELECT 
                    num.modulenumberingid id,
                    num.use_prefix use_prefix,
                    num.start_sequence start_sequence
                    FROM {$table_prefix}_modulenumbering num
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = num.modulenumberingid
                    INNER JOIN {$table_prefix}_crmentityrel rel ON rel.relcrmid = num.modulenumberingid
                    WHERE ent.deleted = 0 AND num.select_module = 'ProgFatE' AND rel.relmodule = 'ModuleNumbering' AND rel.module = 'KpBusinessUnit' AND rel.crmid = ".$business_unit.")";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $use_prefix = $adb->query_result($result_query, 0, 'use_prefix');
            $use_prefix = html_entity_decode(strip_tags($use_prefix), ENT_QUOTES, $default_charset);

            $start_sequence = $adb->query_result($result_query, 0, 'start_sequence');
            $start_sequence = html_entity_decode(strip_tags($start_sequence), ENT_QUOTES, $default_charset);

            $temp = $use_prefix.$start_sequence;

            $lunghezza_numeratore = strlen($temp);     

        }
        else{

            $esiste = false;
            $lunghezza_numeratore = 0;

        }

        $result = array("esiste" => $esiste,
                        "lunghezza_numeratore" => $lunghezza_numeratore);

        return $result;

    }

    function getTabellaControlliBUPerFattureElettroniche($lista){
        global $adb, $table_prefix, $current_user, $default_charset;

        $html = "<table class='table table-striped'>";
        $html .= "<thead>";
        $html .= "<th>Ragione Sociale</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Codice Nazione</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Partita Iva</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Codice Fiscale</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Regime Fiscale</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Codice Nazione Trasmittente</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Codice Identificativo Trasmittente</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Nazione</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Comune</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Indirizzo</th>";
        $html .= "<th style='text-align: center; width: 7%;'>CAP</th>";
        $html .= "<th style='text-align: center; width: 7%;'>Progressivo Invio</th>";
        $html .= "<th style='text-align: center; width: 80px;'></th>";
        $html .= "</thead>";
        $html .= "<tbody>";

        foreach($lista as $record){

            $html .= "<tr>";

            if( $record["nome_check"] == 1 ){
                $html .= "<td>".$record["nome"]."</td>";
            }
            else{
                $html .= "<td style='text-align: left; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["nome_check"]."'>clear</i></td>";
            }

            if( $record["codice_nazione_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["codice_nazione_check"]."'>clear</i></td>";
            }

            if( $record["partita_iva_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["partita_iva_check"]."'>clear</i></td>";
            }

            if( $record["codice_fiscale_check"] == 1 ){
                $html .= "<td style='text-align: center; '><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["codice_fiscale_check"]."'>clear</i></td>";
            }

            if( $record["regime_fiscale_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["regime_fiscale_check"]."'>clear</i></td>";
            }

            if( $record["codice_nazione_trasmittente_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["codice_nazione_trasmittente_check"]."'>clear</i></td>";
            }

            if( $record["codice_identificativo_trasmittente_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["codice_identificativo_trasmittente_check"]."'>clear</i></td>";
            }

            if( $record["nazione_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["nazione_check"]."'>clear</i></td>";
            }

            if( $record["citta_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["citta_check"]."'>clear</i></td>";
            }

            if( $record["indirizzo_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["indirizzo_check"]."'>clear</i></td>";
            }

            if( $record["cap_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["cap_check"]."'>clear</i></td>";
            }

            if( $record["numeratore_check"] == 1 ){
                $html .= "<td style='text-align: center;'><i class='material-icons'>done</i></td>";
            }
            else{
                $html .= "<td style='text-align: center; color: red;'><i class='material-icons' data-toggle='tooltip' data-placement='bottom' title='".$record["numeratore_check"]."'>clear</i></td>";
            }

            $html .= "<td style='text-align: center;'><i style='cursor: pointer;' class='material-icons select_business_unit' id='".$record["id"]."' >input</i></td>";

            $html .= "</tr>";

        }

        $html .= "</tbody>";
        $html .= "</table>";

        return $html;

    }

    function setNazione($nazione){
        global $adb, $table_prefix, $current_user, $default_charset;

        $result = trim($nazione);

        if( strtoupper($nazione) == "ITALIA" || strtoupper($nazione) == "ITALY" ){
            $result = "IT";
        }

        return $result;

    }

    function replaceSpecialChart($stringa){
        global $adb, $table_prefix, $current_user, $default_charset;

        $stringa = trim($stringa);

        $stringa = $this->sanitizeXML($stringa);

        $stringa = preg_replace('/\s+/', ' ', $stringa);
        $stringa = str_replace('\n', " ", $stringa);
        $stringa = str_replace('"', " ", $stringa);
        $stringa = str_replace('&', " ", $stringa);
        $stringa = str_replace("'", " ", $stringa);
        $stringa = str_replace('<', " ", $stringa);
        $stringa = str_replace('>', " ", $stringa);

        return $stringa;

    }

    function sanitizeXML($string){
        global $adb, $table_prefix, $current_user, $default_charset;

        if (!empty($string)) {
            // remove EOT+NOREP+EOX|EOT+<char> sequence (FatturaPA)
            $string = preg_replace('/(\x{0004}(?:\x{201A}|\x{FFFD})(?:\x{0003}|\x{0004}).)/u', '', $string);
    
            $regex = '/(
                [\xC0-\xC1] # Invalid UTF-8 Bytes
                | [\xF5-\xFF] # Invalid UTF-8 Bytes
                | \xE0[\x80-\x9F] # Overlong encoding of prior code point
                | \xF0[\x80-\x8F] # Overlong encoding of prior code point
                | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
                | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
                | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
                | (?<=[\x0-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
                | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
                | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
                | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
                | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
            )/x';
            $string = preg_replace($regex, '', $string);
    
            $result = "";
            $current;
            $length = strlen($string);
            for ($i=0; $i < $length; $i++) {
                $current = ord($string{$i});
                if (($current == 0x9) ||
                    ($current == 0xA) ||
                    ($current == 0xD) ||
                    (($current >= 0x20) && ($current <= 0xD7FF)) ||
                    (($current >= 0xE000) && ($current <= 0xFFFD)) ||
                    (($current >= 0x10000) && ($current <= 0x10FFFF)))
                {
                    $result .= chr($current);
                }
                else{
                    $ret;    // use this to strip invalid character(s)
                    // $ret .= " ";    // use this to replace them with spaces
                }
            }
            $string = $result;
        }
        return $string;
    }



}
?>