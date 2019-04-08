<?php
global $default_charset,$adb,$table_prefix,$autocomplete_return_function;
$forfield = htmlspecialchars($_REQUEST['forfield'], ENT_QUOTES, $default_charset);
$list_result_count = $i-1;
$value = getValue($ui_col_array,$list_result,$fieldname,$focus,$module,$entity_id,$list_result_count,"search",$focus->popup_type);
if(isset($forfield) && $forfield != '' && $focus->popup_type != 'detailview') {
    $value1 = strip_tags($value);
    $value = htmlspecialchars(addslashes(html_entity_decode(strip_tags($value), ENT_QUOTES,$default_charset)), ENT_QUOTES,$default_charset); // Remove any previous html conversion
    $bill_str = '';
    $bill_pobox = '';
    $bill_city = '';
    $bill_code = '';
    $bill_country = '';
    $bill_state = '';
    $ship_str = '';
    $ship_pobox = '';
    $ship_city = '';
    $ship_code = '';
    $ship_country = '';
    $ship_state = '';
    $result = $adb->query('SELECT * FROM '.$table_prefix.'_accountbillads WHERE accountaddressid = '.$entity_id);
    if ($result && $adb->num_rows($result)>0) {
        $bill_str = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($adb->query_result($result,0,'bill_street'))); //crmv@47594
        $bill_pobox = $adb->query_result($result,0,'bill_pobox');
        $bill_city = $adb->query_result($result,0,'bill_city');
        $bill_code = $adb->query_result($result,0,'bill_code');
        $bill_country = $adb->query_result($result,0,'bill_country');
        $bill_state = $adb->query_result($result,0,'bill_state');
    }

    $result2 = $adb->query('SELECT * FROM '.$table_prefix.'_accountshipads WHERE accountaddressid = '.$entity_id);
    if ($result2 && $adb->num_rows($result2)>0) {
        $ship_str = str_replace(array("\r","\n"),array('\r','\n'), popup_decode_html($adb->query_result($result2,0,'ship_street'))); //crmv@47594
        $ship_pobox = $adb->query_result($result2,0,'ship_pobox');
        $ship_city = $adb->query_result($result2,0,'ship_city');
        $ship_code = $adb->query_result($result2,0,'ship_code');
        $ship_country = $adb->query_result($result2,0,'ship_country');
        $ship_state = $adb->query_result($result2,0,'ship_state');
    }

    $result3 = $adb->query('SELECT * FROM '.$table_prefix.'_account WHERE accountid = '.$entity_id);
    if ($result3 && $adb->num_rows($result3)>0) {
        /* kpro@bid24112017 */
        $business_unit = $adb->query_result($result3,0,'kp_business_unit');
        $business_unit = html_entity_decode(strip_tags($business_unit), ENT_QUOTES,$default_charset);

        if($business_unit != null && $business_unit != '' && $business_unit != 0){
            $q_business_unit = "SELECT bu.kp_nome_business_un
                                FROM {$table_prefix}_kpbusinessunit bu
                                WHERE bu.kpbusinessunitid = ".$business_unit;
            $res_business_unit = $adb->query($q_business_unit);
            if($adb->num_rows($res_business_unit)>0){

                $nome_business_unit = $adb->query_result($res_business_unit, 0, 'kp_nome_business_un');
                $nome_business_unit = html_entity_decode(strip_tags($nome_business_unit), ENT_QUOTES,$default_charset);
                $nome_business_unit = addslashes($nome_business_unit);
                
            }
            
        }
        else{
            $business_unit = 0;
            $nome_business_unit = "";
        }

        /* kpro@tom130920181516 */
        $listino = $adb->query_result($result3,0,'kp_listino');
        $listino = html_entity_decode(strip_tags($listino), ENT_QUOTES, $default_charset);

        if($listino == null && $listino == ''){
            $listino = 0;
        }
        $_SESSION['kp_listino'] = $listino;
        VteSession::set('kp_listino', $listino);

        if($listino != null && $listino != '' && $listino != 0){
            $q_business_unit = "SELECT bookname
                                FROM {$table_prefix}_pricebook
                                WHERE pricebookid = ".$listino;
            $res_business_unit = $adb->query($q_business_unit);
            if($adb->num_rows($res_business_unit)>0){

                $nome_listino = $adb->query_result($res_business_unit, 0, 'bookname');
                $nome_listino = html_entity_decode(strip_tags($nome_listino), ENT_QUOTES, $nome_listino);
                $nome_listino = addslashes($nome_listino);
                
            }
            
        }
        else{
            $listino = 0;
            $nome_listino = "";
        }
        /* kpro@tom130920181516 end */

        $agente = $adb->query_result($result3,0,'kp_agente_rel');
        $agente = html_entity_decode(strip_tags($agente), ENT_QUOTES,$default_charset);
        
        if($agente != null && $agente != '' && $agente != 0){
            $q_agente = "SELECT ag.kp_nome_agente
                        FROM {$table_prefix}_kpagenti ag
                        WHERE ag.kpagentiid = ".$agente;
            $res_agente = $adb->query($q_agente);
            if($adb->num_rows($res_agente)>0){

                $nome_agente = $adb->query_result($res_agente, 0, 'kp_nome_agente');
                $nome_agente = html_entity_decode(strip_tags($nome_agente), ENT_QUOTES,$default_charset);
                $nome_agente = addslashes($nome_agente);
                
            }
            
        }
        else{
            $agente = 0;
            $nome_agente = "";
        }
        /* kpro@bid24112017 end */
        $mod_pagamento = $adb->query_result($result3,0,'mod_pagamento');
        $mod_pagamento = html_entity_decode(strip_tags($mod_pagamento), ENT_QUOTES,$default_charset);
        $mod_pagamento = addslashes($mod_pagamento);
        
        if($mod_pagamento != null && $mod_pagamento != '' && $mod_pagamento != 0){
            $q_mod_pagamento = "SELECT 
                                modp.nome_mod_pag nome_mod_pag
                                FROM {$table_prefix}_modpagamento modp
                                WHERE modp.modpagamentoid = ".$mod_pagamento;
            $res_mod_pagamento = $adb->query($q_mod_pagamento);
            if($adb->num_rows($res_mod_pagamento)>0){

		$nome_mod_pag = $adb->query_result($res_mod_pagamento, 0, 'nome_mod_pag');
		$nome_mod_pag = html_entity_decode(strip_tags($nome_mod_pag), ENT_QUOTES,$default_charset);
                $nome_mod_pag = addslashes($nome_mod_pag);
                
            }
            
        }
        else{
            $mod_pagamento = 0;
            $nome_mod_pag = "";
        }

        $tasse = $adb->query_result($result3, 0, 'kp_tasse');
        $tasse = html_entity_decode(strip_tags($tasse), ENT_QUOTES,$default_charset);
        $tasse = addslashes($tasse);
        
    }

    $autocomplete_return_function[$entity_id] = "set_return_account_to_sales_order($entity_id, \"$value\", \"$forfield\", \"$bill_str\", \"$ship_str\", \"$bill_city\", \"$ship_city\", \"$bill_state\", \"$ship_state\", \"$bill_code\", \"$ship_code\", \"$bill_country\", \"$ship_country\", \"$mod_pagamento\", \"$nome_mod_pag\", \"$business_unit\", \"$nome_business_unit\", \"$agente\", \"$nome_agente\", \"$tasse\", \"$listino\", \"$nome_listino\");"; /* kpro@bid24112017 */ /* kpro@tom130920181516 */
    $value = "<a href='javascript:void(0);' onclick='{$autocomplete_return_function[$entity_id]}closePopup();'>$value1</a>"; //crmv@21048m
}
?>