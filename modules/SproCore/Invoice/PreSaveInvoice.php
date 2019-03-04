<?php 

/* kpro@tom26012018*/

/**
 * @author Tomiello Marco
 * @copyright (c) 2018, Kpro Consulting Srl
 */

require_once(__DIR__.'/Invoice_utils.php');

$record_id = $values['record'];

if($values['invoicestatus'] == "Approved"){

    if($values['kp_tipo_documento'] != "Fattura di acconto"){ /* kpro@bid250920181215 */
        $messaggio= "";

        $focus = CRMEntity::getInstance('Invoice');
        $focus->retrieve_entity_info($record_id, "Invoice", $dieOnError=false); 

        $business_unit = $focus->column_fields["kp_business_unit"];

        /* kpro@tom040320190954 */
        if( $values['invoice_number'] == '' ){
            $messaggio = ControlloDataFattura($record_id, $values['invoicedate'], $values['kp_tipo_documento'], $business_unit);
        }
        else{
            $messaggio = "";
        }
        /* kpro@tom040320190954 end */

        if($messaggio == ""){
            $tasse_testata = $values['kp_tasse'];   

            $i = 1;
            $nome_tassa = "tax1_percentage".$i;
            while($values[$nome_tassa] != null && $values[$nome_tassa] != ""){

                if(strpos($tasse_testata, 'E') !== false && (float)$values[$nome_tassa] != 0){
                    $messaggio .= "Errore nell'imputazione delle tasse";
                    break;
                }
                
                $i += 1;
                $nome_tassa = "tax1_percentage".$i;
                
            }

            $nome_tassa_gruppo = "tax1_group_percentage";
            if($values[$nome_tassa_gruppo] != null && $values[$nome_tassa_gruppo] != ""){
                if(strpos($tasse_testata, 'E') !== false && (float)$values[$nome_tassa_gruppo] != 0){
                    if($messaggio == ""){
                        $messaggio .= "Errore nell'imputazione delle tasse";
                    }
                }
            }
        }
        
        if($messaggio != ""){
            $message = $messaggio;
            $status = false;
            $focus = "invoicestatus";
        }
        else{
            $status = true;
            $message = '';
        }
    /* kpro@bid250920181215 */
    }
    else{
        if( $type == 'EditView' ){
            $ordine_di_vendita = $values['salesorder_id'];        
        }
        else{        
            $focus = CRMEntity::getInstance('Invoice');
            $focus->retrieve_entity_info($record_id, "Invoice", $dieOnError=false); 
        
            $ordine_di_vendita = $focus->column_fields["salesorder_id"];        
        }

        if($ordine_di_vendita == 0 || $ordine_di_vendita == '' || $ordine_di_vendita == null){
            $message = 'Ordine di vendita obbligatorio per le fatture di acconto';
            $status = false;
            $focus = "salesorder_id";
        }
        else{
            $status = true;
            $message = '';
        }
    }
    /* kpro@bid250920181215 end */
}
else{
    $status = true;
    $message = '';
}

?>