<?php

/* kpro@bid26012018 */

if($type == 'MassEditSave'){
    $status = true;
    $message = '';
} 
else{

    if($values['kp_attiv_eseguita'] != 'Dal cliente' && $values['kp_tipo_rimborso'] != 'No addebito'){
        $message = "Non e' possibile impostare un rimborso spese per attivita' in ufficio.";
        $status = false;
        $focus = "kp_tipo_rimborso";
    }
    else{
        $status = true;
        $message = '';
    }

}

?>