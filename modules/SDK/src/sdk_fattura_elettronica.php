<?php 
include_once('../../../config.inc.php'); 
chdir($root_directory); 
require_once('include/utils/utils.php'); 
include_once('vtlib/Vtiger/Module.php'); 
require_once('modules/SproCore/SDK/KpSDK.php'); 
$Vtiger_Utils_Log = true; 
global $adb, $table_prefix;
session_start(); 

//KpSDK::registraPulsante($nome_modulo = "Invoice", $nome_pulsante = "Fatturazione Elettronica", $tipo_pulsante = "index", $funzione = 'kpFatturazioneElettronica();', $icona = "kpFatturaElettronica.png");
//KpSDK::registraPulsante($nome_modulo = "Invoice", $nome_pulsante = "Fatturazione Elettronica", $tipo_pulsante = "ListView", $funzione = 'kpFatturazioneElettronica();', $icona = "kpFatturaElettronica.png");

//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_codice_nazione', $label_campo = 'Codice Nazione', $uitype = '15', $columntype = 'varchar(255)', $typeofdata = 'V~M', $readonly = '1', $helpinfo = '', $picklist = array('IT','GB','CH','AT','DE'));

//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_partita_iva', $label_campo = 'Partita IVA', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~M', $readonly = '1', $helpinfo = '');

//KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "select_module", $codice = "ProgFatE", $valore = "Progressivo Invio Fatture Elettroniche");

//KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_codice_id_fe', $label_campo = 'Codice Identificativo (Fatturazione Elettronica)', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
/*
KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_regime_fiscale', $label_campo = 'Regime Fiscale', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF01", $valore = "Ordinario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF02", $valore = "Contribuenti minimi (art.1, c.96-117, L. 244/07)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF04", $valore = "Agricoltura e attività connesse e pesca (artt.34 e 34-bis, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF05", $valore = "Vendita sali e tabacchi (art.74, c.1, DPR. 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF06", $valore = "Commercio fiammiferi (art.74, c.1, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF07", $valore = "Editoria (art.74, c.1, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF08", $valore = "Gestione servizi telefonia pubblica (art.74, c.1, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF09", $valore = "Rivendita documenti di trasporto pubblico e di sosta (art.74, c.1, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF10", $valore = "Intrattenimenti, giochi e altre attività di cui alla tariffa allegata al DPR 640/72 (art.74, c.6, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF11", $valore = "Agenzie viaggi e turismo (art.74-ter, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF12", $valore = "Agriturismo (art.5, c.2, L. 413/91)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF13", $valore = "Vendite a domicilio (art.25-bis, c.6, DPR 600/73)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF14", $valore = "Rivendita beni usati, oggetti d’arte, d’antiquariato o da collezione (art.36, DL 41/95)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF15", $valore = "Agenzie di vendite all’asta di oggetti d’arte, antiquariato o da collezione (art.40-bis, DL 41/95)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF16", $valore = "IVA per cassa P.A. (art.6, c.5, DPR 633/72)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF17", $valore = "IVA per cassa (art. 32-bis, DL 83/2012)");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF18", $valore = "Altro");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_regime_fiscale", $codice = "RF19", $valore = "Regime forfettario (art.1, c.54-89, L. 190/2014)");
*/

//KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_pec', $label_campo = 'PEC', $uitype = '13', $columntype = 'varchar(100)', $typeofdata = 'E~O', $readonly = '1', $helpinfo = '');
/*
KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_formato_trasm', $label_campo = 'Formato Trasmissione', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_formato_trasm", $codice = "FPA12", $valore = "Pubblica Amministrazione");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_formato_trasm", $codice = "FPR12", $valore = "Privato");
*/

//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_codice_fiscale', $label_campo = 'Codice Fiscale', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~M', $readonly = '1', $helpinfo = '');

//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_indirizzo', $label_campo = 'Indirizzo', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_cap', $label_campo = 'CAP', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_comune', $label_campo = 'Comune', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_provincia', $label_campo = 'Provincia', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_nazione', $label_campo = 'Nazione', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

//KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_codice_nazione', $label_campo = 'Codice Nazione', $uitype = '15', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');
/*
//KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP01", $valore = "Contanti");
//KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP05", $valore = "Bonifico");
//KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP12", $valore = "RIBA");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP02", $valore = "Assegno");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP03", $valore = "Assegno circolare");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP04", $valore = "Contanti presso Tesoreria");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP06", $valore = "Vaglia cambiario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP07", $valore = "Bollettino bancario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP08", $valore = "Carta di pagamento");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP09", $valore = "RID");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP10", $valore = "RID utenze");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP11", $valore = "RID veloce");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP13", $valore = "MAV");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP14", $valore = "Quietanza erario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP15", $valore = "Giroconto su conti di contabilità speciale");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP16", $valore = "Domiciliazione bancaria");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP17", $valore = "Domiciliazione postale");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP18", $valore = "Bollettino di c/c postale");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP19", $valore = "SEPA Direct Debit");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP20", $valore = "SEPA Direct Debit CORE");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP21", $valore = "SEPA Direct Debit B2B");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "condizioni_pagamento", $codice = "MP22", $valore = "Trattenuta su somme già riscosse");
*/
/*
KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_codice_naz_trasm', $label_campo = 'Codice Nazione Trasmittente', $uitype = '15', $columntype = 'varchar(255)', $typeofdata = 'V~M', $readonly = '1', $helpinfo = '', $picklist = array('IT','GB','CH','AT','DE'));
KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_cod_fisc_trasm', $label_campo = 'Codice Identificativo Trasmittente', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~M', $readonly = '1', $helpinfo = '');
*/

//KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_prog_inv_fe', $label_campo = 'Progressivo Invio Fattura Elettronica', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '99', $helpinfo = '');
/*
KpSDK::registraCampo($nome_modulo = 'SalesOrder', $blocco = 'LBL_SO_INFORMATION', $nome_campo = 'kp_data_ord_cli', $label_campo = 'Data Ordine Cliente', $uitype = '5', $columntype = 'date', $typeofdata = 'D~O', $readonly = '1', $helpinfo = '', $picklist = array(), $generatedtype = 2);

KpSDK::registraCampo($nome_modulo = 'SalesOrder', $blocco = 'LBL_SO_INFORMATION', $nome_campo = 'kp_codice_cup', $label_campo = 'Codice CUP', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');

KpSDK::registraCampo($nome_modulo = 'SalesOrder', $blocco = 'LBL_SO_INFORMATION', $nome_campo = 'kp_codice_cig', $label_campo = 'Codice CIG', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');

KpSDK::registraCampo($nome_modulo = 'OdF', $blocco = 'LBL_ODF_INFORMATION', $nome_campo = 'kp_rif_ordine_cli', $label_campo = 'Rif. Ordine Cliente', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'OdF', $blocco = 'LBL_ODF_INFORMATION', $nome_campo = 'kp_data_ord_cli', $label_campo = 'Data Ordine Cliente', $uitype = '5', $columntype = 'date', $typeofdata = 'D~O', $readonly = '1', $helpinfo = '', $picklist = array(), $generatedtype = 2);

KpSDK::registraCampo($nome_modulo = 'OdF', $blocco = 'LBL_ODF_INFORMATION', $nome_campo = 'kp_codice_cup', $label_campo = 'Codice CUP', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');

KpSDK::registraCampo($nome_modulo = 'OdF', $blocco = 'LBL_ODF_INFORMATION', $nome_campo = 'kp_codice_cig', $label_campo = 'Codice CIG', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');
*/
/*
$query = "ALTER TABLE vte_inventorytaxinfo ADD kp_codice_iva varchar(255)";
KpSDK::eseguiQuerySDK($query);

$query = "ALTER TABLE vte_inventorytaxinfo ADD kp_natura varchar(255)";
KpSDK::eseguiQuerySDK($query);

$query = "ALTER TABLE vte_inventorytaxinfo ADD kp_norma varchar(255)";
KpSDK::eseguiQuerySDK($query);*/
/*
$query = "CREATE TABLE IF NOT EXISTS `kp_inventoryproductrel` (
    `id` INT(19),
    `productid` INT(19),
    `relmodule` VARCHAR(100),
    `incrementondel` INT(11),
    `lineitem_id` INT(11),
    `rif_ord_cliente` VARCHAR(255),
    `data_ord_cliente` VARCHAR(255),
    `codice_cup` VARCHAR(255),
    `codice_cig` VARCHAR(255),
    `id_tassa` VARCHAR(255),
    `codice_tassa` VARCHAR(255),
    `natura_tassa` VARCHAR(255),
    `normativa_tassa` VARCHAR(255)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8";
KpSDK::eseguiQuerySDK($query);*/

/*
KpSDK::registraCampo($nome_modulo = 'KpSalesOrderLine', $blocco = 'LBL_KPSALESORDERLINE_INFORMATION', $nome_campo = 'kp_id_tassa', $label_campo = 'ID Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '100', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpSalesOrderLine', $blocco = 'LBL_KPSALESORDERLINE_INFORMATION', $nome_campo = 'kp_nome_tassa', $label_campo = 'Nome Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'OdF', $blocco = 'LBL_ODF_INFORMATION', $nome_campo = 'kp_id_tassa', $label_campo = 'ID Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '100', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'OdF', $blocco = 'LBL_ODF_INFORMATION', $nome_campo = 'kp_nome_tassa', $label_campo = 'Nome Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '99', $helpinfo = '');
*/
/*
$query = "UPDATE vte_field SET presence = 1, readonly = 100 WHERE tablename = 'vte_kpbusinessunit' AND fieldname = 'kp_ritenuta_acconto'";
KpSDK::eseguiQuerySDK($query);

$query = "UPDATE vte_field SET presence = 1, readonly = 100 WHERE tablename = 'vte_kpbusinessunit' AND fieldname = 'kp_mod_tassazione'";
KpSDK::eseguiQuerySDK($query);

$query = "UPDATE vte_field SET presence = 1, readonly = 100 WHERE tablename = 'vte_kpbusinessunit' AND fieldname = 'kp_conf_tassazione'";
KpSDK::eseguiQuerySDK($query);

$query = "UPDATE vte_field SET presence = 1, readonly = 100 WHERE tablename = 'vte_service' AND fieldname = 'kp_post_tasse'";
KpSDK::eseguiQuerySDK($query);

$query = "UPDATE vte_settings_field SET active = 1 WHERE name = 'Configurazione Tassazione'";
KpSDK::eseguiQuerySDK($query);

$query = "UPDATE vte_settings_field SET linkto = 'index.php?module=Settings&action=KpTaxConfig&parenttab=Settings' WHERE name = 'LBL_TAX_SETTINGS'";
KpSDK::eseguiQuerySDK($query);
*/

//KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_split_payment', $label_campo = 'Applica Split Payment', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '1', $helpinfo = '');

/*
KpSDK::registraCampo($nome_modulo = 'HelpDesk', $blocco = 'LBL_TICKET_INFORMATION', $nome_campo = 'kp_id_tassa', $label_campo = 'ID Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '100', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'HelpDesk', $blocco = 'LBL_TICKET_INFORMATION', $nome_campo = 'kp_nome_tassa', $label_campo = 'Nome Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'HelpDesk', $blocco = 'LBL_TICKET_INFORMATION', $nome_campo = 'kp_rif_ordine_cli', $label_campo = 'Rif. Ordine Cliente', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'HelpDesk', $blocco = 'LBL_TICKET_INFORMATION', $nome_campo = 'kp_data_ord_cli', $label_campo = 'Data Ordine Cliente', $uitype = '5', $columntype = 'date', $typeofdata = 'D~O', $readonly = '1', $helpinfo = '', $picklist = array(), $generatedtype = 2);

KpSDK::registraCampo($nome_modulo = 'HelpDesk', $blocco = 'LBL_TICKET_INFORMATION', $nome_campo = 'kp_codice_cup', $label_campo = 'Codice CUP', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');

KpSDK::registraCampo($nome_modulo = 'HelpDesk', $blocco = 'LBL_TICKET_INFORMATION', $nome_campo = 'kp_codice_cig', $label_campo = 'Codice CIG', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');

KpSDK::registraCampo($nome_modulo = 'Canoni', $blocco = 'LBL_CANONI_INFORMATION', $nome_campo = 'kp_id_tassa', $label_campo = 'ID Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '100', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Canoni', $blocco = 'LBL_CANONI_INFORMATION', $nome_campo = 'kp_nome_tassa', $label_campo = 'Nome Tassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Canoni', $blocco = 'LBL_CANONI_INFORMATION', $nome_campo = 'kp_rif_ordine_cli', $label_campo = 'Rif. Ordine Cliente', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Canoni', $blocco = 'LBL_CANONI_INFORMATION', $nome_campo = 'kp_data_ord_cli', $label_campo = 'Data Ordine Cliente', $uitype = '5', $columntype = 'date', $typeofdata = 'D~O', $readonly = '1', $helpinfo = '', $picklist = array(), $generatedtype = 2);

KpSDK::registraCampo($nome_modulo = 'Canoni', $blocco = 'LBL_CANONI_INFORMATION', $nome_campo = 'kp_codice_cup', $label_campo = 'Codice CUP', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');

KpSDK::registraCampo($nome_modulo = 'Canoni', $blocco = 'LBL_CANONI_INFORMATION', $nome_campo = 'kp_codice_cig', $label_campo = 'Codice CIG', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = 'Se si compila tale campo è necessario compilare anche il riferimento ordine cliente');
*/

//KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_ragione_sociale', $label_campo = 'Ragione Sociale', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~M', $readonly = '1', $helpinfo = '');

/* kpro@tom240120191400 */
//Aggiunte del 24/01/2019 
/*
KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tot_imponibile', $label_campo = 'Totale Imponibile', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tot_fattura', $label_campo = 'Totale Fattura', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tot_da_pagare', $label_campo = 'Totale Da Pagare', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_applica_ritenuta', $label_campo = 'Applica Ritenuta', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tipo_ritenuta', $label_campo = 'Tipo Ritenuta', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_causale_pag_rite', $label_campo = 'Causale Pagamento Ritenuta', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_aliquota_ritenuta', $label_campo = 'Aliquota Ritenuta', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_importo_ritenuta', $label_campo = 'Importo Ritenuta', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_applica_ritenuta', $label_campo = 'Applica Ritenuta', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_tipo_ritenuta', $label_campo = 'Tipo Ritenuta', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_causale_pag_rite', $label_campo = 'Causale Pagamento Ritenuta', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Accounts', $blocco = 'LBL_ACCOUNT_INFORMATION', $nome_campo = 'kp_aliquota_ritenuta', $label_campo = 'Aliquota Ritenuta', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');

KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_ritenuta", $codice = "RT01", $valore = "Ritenuta Persone Fisiche");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_ritenuta", $codice = "RT02", $valore = "Ritenuta Persone Giuridiche");

KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "A", $valore = "Prestazioni di lavoro autonomo rientranti nell’esercizio di arte o professione abituale");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "B", $valore = "Utilizzazione economica di opere dell’ingegno");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "C", $valore = "Utili derivanti da contratti di associazione in partecipazione e da contratti di cointeressenza");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "D", $valore = "Utili spettanti ai soci promotori ed ai soci fondatori delle società di capitali");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "E", $valore = "Levata di protesti cambiari da parte dei segretari comunali");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "G", $valore = "Indennità corrisposte per la cessazione di attività sportiva professionale");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "H", $valore = "Indennità corrisposte per la cessazione dei rapporti di agenzia delle persone fisiche");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "I", $valore = "Indennità corrisposte per la cessazione da funzioni notarili");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "L", $valore = "Redditi derivanti dall’utilizzazione economica di opere dell’ingegno");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "L1", $valore = "Redditi derivanti dall’utilizzazione economica di opere dell’ingegno");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "M", $valore = "Prestazioni di lavoro autonomo non esercitate abitualmente");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "M1", $valore = "Redditi derivanti dall’assunzione di obblighi di fare, di non fare o permettere");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "M2", $valore = "Prestazioni di lavoro autonomo non esercitate abitualmente per le quali sussiste l’obbligo di iscrizione alla Gestione Separata ENPAPI");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "N", $valore = "Indennità di trasferta, rimborso forfetario di spese, premi e compensi erogati");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "O", $valore = "Prestazioni di lavoro autonomo non esercitate abitualmente, per le quali non sussiste l’obbligo di iscrizione alla gestione separata");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "O1", $valore = "redditi derivanti dall’assunzione di obblighi di fare, di non fare o permettere, per le quali non sussiste l’obbligo di iscrizione alla gestione separata");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "P", $valore = "Compensi corrisposti a soggetti non residenti privi di stabile organizzazione per l’uso o la concessione in uso di attrezzature industriali");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "Q", $valore = "Provvigioni corrisposte ad agente o rappresentante di commercio monomandatario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "R", $valore = "Provvigioni corrisposte ad agente o rappresentante di commercio plurimandatario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "S", $valore = "Provvigioni corrisposte a commissionario");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "T", $valore = "Provvigioni corrisposte a mediatore");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "U", $valore = "Provvigioni corrisposte a procacciatore di affari");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "V", $valore = "Provvigioni corrisposte a incaricato per le vendite a domicilio");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "V1", $valore = "Redditi derivanti da attività commerciali non esercitate abitualmente");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "V2", $valore = "Redditi derivanti dalle prestazioni non esercitate abitualmente rese dagli incaricati alla vendita diretta a domicilio");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "W", $valore = "Corrispettivi erogati nel 2015 per prestazioni relative a contratti d’appalto");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "X", $valore = "Canoni corrisposti nel 2004 da società o enti residenti ovvero da stabili organizzazioni di società estere");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "Y", $valore = "Canoni corrisposti dal 1° gennaio 2005 al 26 luglio 2005 da società o enti residenti");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_causale_pag_rite", $codice = "Z", $valore = "Titolo diverso dai precedenti");
*/
/* kpro@tom240120191400 end */

/* kpro@tom310120191640 */
//Aggiunte del 31/01/2019 
/*
KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_applica_cassa', $label_campo = 'Applica Cassa', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tipo_cassa', $label_campo = 'Tipo Cassa', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_aliquota_cassa', $label_campo = 'Aliquota Cassa', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_aliq_iva_cassa', $label_campo = 'Aliquota IVA Cassa', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_natura_iva_cassa', $label_campo = 'Natura IVA Cassa', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_rif_amm_cassa', $label_campo = 'Rif. Amministrazione Cassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_ap_rit_cassa', $label_campo = 'Applica Ritenuta Cassa', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '100', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_imponibile_cassa', $label_campo = 'Imponibile Cassa', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tot_iva_cassa', $label_campo = 'Totale IVA Cassa', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_importo_cassa', $label_campo = 'Importo Contributo Cassa', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'Invoice', $blocco = 'LBL_INVOICE_INFORMATION', $nome_campo = 'kp_tot_iva_fat', $label_campo = 'Totale IVA', $uitype = '71', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '99', $helpinfo = '');


KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC01", $valore = "Cassa nazionale previdenza e assistenza avvocati e procuratori legali");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC02", $valore = "Cassa previdenza dottori commercialisti");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC03", $valore = "Cassa previdenza e assistenza geometri");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC04", $valore = "Cassa nazionale previdenza e assistenza ingegneri e architetti liberi professionisti");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC05", $valore = "Cassa nazionale del notariato");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC06", $valore = "Cassa nazionale previdenza e assistenza ragionieri e periti commerciali");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC07", $valore = "ENASARCO");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC08", $valore = "ENPACL");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC09", $valore = "ENPAM");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC10", $valore = "ENPAF");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC11", $valore = "ENPAV");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC12", $valore = "ENPAIA");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC13", $valore = "Fondo previdenza impiegati imprese di spedizione e agenzie marittime");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC14", $valore = "INPGI");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC15", $valore = "ONAOSI");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC16", $valore = "CASAGIT");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC17", $valore = "EPPI");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC18", $valore = "EPAP");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC19", $valore = "ENPAB");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC20", $valore = "ENPAPI");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC21", $valore = "ENPAP");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_tipo_cassa", $codice = "TC22", $valore = "INPS");

KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N1", $valore = "Escluse ex art. 15");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N2", $valore = "Non soggette");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N3", $valore = "Non imponibili");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N4", $valore = "Esenti");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N5", $valore = "Regime del margine / IVA non esposta in fattura");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N6", $valore = "Inversione contabile");
KpSDK::aggiungiAPickingListMultilinguaggio($nome_campo = "kp_natura_iva_cassa", $codice = "N7", $valore = "IVA assolta in altro stato UE");

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_applica_cassa', $label_campo = 'Applica Cassa', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_tipo_cassa', $label_campo = 'Tipo Cassa', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_aliquota_cassa', $label_campo = 'Aliquota Cassa', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_aliq_iva_cassa', $label_campo = 'Aliquota IVA Cassa', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_natura_iva_cassa', $label_campo = 'Natura IVA Cassa', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_rif_amm_cassa', $label_campo = 'Rif. Amministrazione Cassa', $uitype = '1', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_ap_rit_cassa', $label_campo = 'Applica Ritenuta Cassa', $uitype = '56', $columntype = 'varchar(3)', $typeofdata = 'C~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_tipo_ritenuta', $label_campo = 'Tipo Ritenuta', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_causale_pag_rite', $label_campo = 'Causale Pagamento Ritenuta', $uitype = '1015', $columntype = 'varchar(255)', $typeofdata = 'V~O', $readonly = '1', $helpinfo = '');

KpSDK::registraCampo($nome_modulo = 'KpBusinessUnit', $blocco = 'LBL_KPBUSINESSUNIT_INFORMATION', $nome_campo = 'kp_aliquota_ritenuta', $label_campo = 'Aliquota Ritenuta', $uitype = '9', $columntype = 'decimal(15,2)', $typeofdata = 'NN~O~15,2', $readonly = '1', $helpinfo = '');
*/
/* kpro@tom310120191640 end */



?>