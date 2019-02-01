<?php

/* kpro@bid19062018 */

/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 */

require_once('include/utils/utils.php');
require_once('Smarty_setup.php');
global $app_strings;
global $mod_strings;
global $currentModule;
global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
global $current_language, $adb, $table_prefix;

$smarty = new vtigerCRM_Smarty;

$smarty->assign("MOD", return_module_language($current_language,'Settings'));
$smarty->assign("CMOD", $mod_strings);
$smarty->assign("APP", $app_strings);
$smarty->assign("IMAGE_PATH",$image_path);
$smarty->assign("THEME", $theme);

$check_table = "SHOW TABLES LIKE 'kp_settings_company_info'";

$result_check_table = $adb->query($check_table);
$num_check_table = $adb->num_rows($result_check_table);

if( $num_check_table == 0 ){

    $create_table = "CREATE TABLE IF NOT EXISTS `kp_settings_company_info` (
                        `codice_sia` varchar (15)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $adb->query($create_table);
}

$html_tabella = "";

/* kpro@tom290120190943 */
$html_riga = "<tr>
<td width='20%' class='small cellLabel'><strong>#label#</strong></td>
<td width='80%' class='small cellText'><div class='form-group'>
<input type='text' class='form-control form_dettagli' id='form_#id#' value='#valore#' disabled> 
</div></td>
</tr>";
/* kpro@tom290120190943 end */

$array_colonne = array(
    'Codice SIA' => 'codice_sia'
);

$q = "SELECT * from kp_settings_company_info";
$res = $adb->query($q);

foreach($array_colonne as $label => $colonna){
    $valore_colonna = $adb->query_result($res, 0, $colonna);
    if($valore_colonna == null){
        $valore_colonna == '';
    }

    $html_riga_temp = $html_riga;
    $html_riga_temp = str_replace("#id#", $colonna, $html_riga_temp);
    $html_riga_temp = str_replace("#label#", $label, $html_riga_temp);
    $html_riga_temp = str_replace("#valore#", $valore_colonna, $html_riga_temp);

    $html_tabella .= $html_riga_temp;
}

$smarty->assign("tabella_dettagli_societa", $html_tabella);


/* kpro@tom290120190943 */

$check_table = "SHOW TABLES LIKE 'kp_banche_company'";

$result_check_table = $adb->query($check_table);
$num_check_table = $adb->num_rows($result_check_table);

if( $num_check_table == 0 ){

    $create_table = "CREATE TABLE IF NOT EXISTS `kp_banche_company` (
                        `id` int(19),
                        `banca` varchar(255),
                        `nome_istituto` varchar(255),
                        `iban` varchar(255),
                        `abi` varchar(100),
                        `cab` varchar(100),
                        `bic` varchar(255),
                        `aggiornato` varchar(1)) 
                    ENGINE=InnoDB DEFAULT CHARSET=utf8";
    $adb->query($create_table);

}

$lista_banche = GetListaBanche();

$html_tabella_banche = "<thead>
                            <tr>
                                <th>Banca</th>
                                <th>Nome Istituto</th>
                                <th>IBAN</th>
                                <th>ABI</th>
                                <th>CAB</th>
                                <th>BIC</th>
                            </tr>
                        </thead>
                        <tbody>";

$template_riga_banca = "<tr class='tr_banca' id='banca_#id#'>
                            <td width='20%' class='small cellLabel'>
                                <strong id='nome_banca_#id#'>#banca#</strong>
                            </td>
                            <td width='20%' class='small cellText'>
                                <div class='form-group'>
                                    <input type='text' class='form-control form_banca' id='form_nome_istituto_#id#' value='#nome_istituto#' disabled>
                                </div>
                            </td>
                            <td width='20%' class='small cellText'>
                                <div class='form-group'>
                                    <input type='text' class='form-control form_banca' id='form_iban_#id#' value='#iban#' disabled>
                                </div>
                            </td>
                            <td width='10%' class='small cellText'>
                                <div class='form-group'>
                                    <input type='text' class='form-control form_banca' id='form_abi_#id#' value='#abi#' disabled>
                                </div>
                            </td>
                            <td width='10%' class='small cellText'>
                                <div class='form-group'>
                                    <input type='text' class='form-control form_banca' id='form_cab_#id#' value='#cab#' disabled>
                                </div>
                            </td>
                            <td width='20%' class='small cellText'>
                                <div class='form-group'>
                                    <input type='text' class='form-control form_banca' id='form_bic_#id#' value='#bic#' disabled>
                                </div>
                            </td>
                        </tr>";

foreach( $lista_banche as $banca ){

    $html_riga_banca_temp = $template_riga_banca;
    $html_riga_banca_temp = str_replace("#id#", $banca["id"], $html_riga_banca_temp);
    $html_riga_banca_temp = str_replace("#banca#", $banca["banca_pagamento"], $html_riga_banca_temp);
    $html_riga_banca_temp = str_replace("#nome_istituto#", $banca["nome_istituto"], $html_riga_banca_temp);
    $html_riga_banca_temp = str_replace("#iban#", $banca["iban"], $html_riga_banca_temp);
    $html_riga_banca_temp = str_replace("#abi#", $banca["abi"], $html_riga_banca_temp);
    $html_riga_banca_temp = str_replace("#cab#", $banca["cab"], $html_riga_banca_temp);
    $html_riga_banca_temp = str_replace("#bic#", $banca["bic"], $html_riga_banca_temp);

    $html_tabella_banche .= $html_riga_banca_temp;

}

$html_tabella_banche .= "</tbody>";

$smarty->assign("tabella_banche_societa", $html_tabella_banche);

/* kpro@tom290120190943 end */

$smarty->display('SproCore/Settings/KpCompanyInfo.tpl');


/* kpro@tom290120190943 */

function CreateTabellaBanca($id, $nome){
    global $current_language, $adb, $table_prefix, $default_charset;

    $nome = addslashes($nome);

    $insert = "INSERT INTO kp_banche_company 
                (id, banca, nome_istituto, iban, abi, cab, bic, aggiornato)
                VALUES
                (".$id.", '".$nome."', '".$nome."', '', '', '', '', '1')";

    $adb->query($insert);

}

function GetListaBanche(){
    global $current_language, $adb, $table_prefix, $default_charset;

    $result = array();

    $query = "SELECT 
                *
                FROM {$table_prefix}_banca_pagamento banc
                LEFT JOIN kp_banche_company bancinfo ON bancinfo.id = banc.banca_pagamentoid
                WHERE banca_pagamento NOT LIKE '--Nessuno--'";

    $result_query = $adb->query($query);
    $num_result = $adb->num_rows($result_query);

    for( $i = 0; $i < $num_result; $i++ ){

        $banca_pagamentoid = $adb->query_result($result_query, $i, 'banca_pagamentoid');
        $banca_pagamentoid = html_entity_decode(strip_tags($banca_pagamentoid), ENT_QUOTES, $default_charset);

        $banca_pagamento = $adb->query_result($result_query, $i, 'banca_pagamento');
        $banca_pagamento = html_entity_decode(strip_tags($banca_pagamento), ENT_QUOTES, $default_charset);

        $id = $adb->query_result($result_query, $i, 'id');
        $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
        if( $id == null || $id == "" || $id == 0 ){
            
            CreateTabellaBanca($banca_pagamentoid, $banca_pagamento);

            $id = $banca_pagamentoid;
            $nome_istituto = $banca_pagamento;
            $iban = "";
            $abi = "";
            $cab = "";
            $bic = "";

        }
        else{

            $nome_istituto = $adb->query_result($result_query, $i, 'nome_istituto');
            $nome_istituto = html_entity_decode(strip_tags($nome_istituto), ENT_QUOTES, $default_charset);

            $iban = $adb->query_result($result_query, $i, 'iban');
            $iban = html_entity_decode(strip_tags($iban), ENT_QUOTES, $default_charset);

            $abi = $adb->query_result($result_query, $i, 'abi');
            $abi = html_entity_decode(strip_tags($abi), ENT_QUOTES, $default_charset);

            $cab = $adb->query_result($result_query, $i, 'cab');
            $cab = html_entity_decode(strip_tags($cab), ENT_QUOTES, $default_charset);

            $bic = $adb->query_result($result_query, $i, 'bic');
            $bic = html_entity_decode(strip_tags($bic), ENT_QUOTES, $default_charset);

        }

        $result[] = array("id" => $id,
                            "nome_istituto" => $nome_istituto,
                            "banca_pagamento" => $banca_pagamento,
                            "iban" => $iban,
                            "abi" => $abi,
                            "cab" => $cab,
                            "bic" => $bic);

    }

    return $result;

}

/* kpro@tom290120190943 end */

?>