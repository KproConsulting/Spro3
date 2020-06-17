<?php
// crmv@39106
require_once("modules/PDFMaker/PDFMaker.php");

// INPUT
$relmodule = vtlib_purify($_REQUEST["relmodule"]);

if (isset($_REQUEST["idslist"]) && $_REQUEST["idslist"]!="") {  //generating from listview
    $Records = explode(";", rtrim($_REQUEST["idslist"],";"));
} elseif (isset($_REQUEST['record'])) {
    $Records = array($_REQUEST["record"]);
}

$commontemplateids = trim($_REQUEST["commontemplateid"],";");
$Templateids = explode(";",$commontemplateids);

$language = $_REQUEST["language"];

// OUTPUT
$pdfmaker = new PDFMaker();
$name = $pdfmaker->generatePDFForEmail($Records, $relmodule, $Templateids, $language);

/* kpro@tom082820180913 */
//echo $name;

if($relmodule == "Quotes"){

    /* kpro@tom030320201455 */
    /*function endsWith($string, $endString) { 
        $len = strlen($endString); 
        if ($len == 0) { 
            return true; 
        } 
        return (substr($string, -$len) === $endString); 
    } 

    if( endsWith( $name, ".pdf" ) ){
        echo $name;
    }
    else{
        echo $name.".pdf";
    }*/
    echo $name;
    /* kpro@tom030320201455 end */

    $Records_length = count($Records);
    
	for($i = 0; $i < $Records_length; $i++){

        $array_allegati_trovati = array();

        $q_trova_allegato = "SELECT att.attachmentsid,note.filename
                                FROM {$table_prefix}_inventoryproductrel inv
                                INNER JOIN {$table_prefix}_senotesrel snote ON snote.crmid = inv.productid
                                INNER JOIN {$table_prefix}_notes note ON note.notesid = snote.notesid
                                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = note.notesid
                                INNER JOIN {$table_prefix}_seattachmentsrel seatt ON seatt.crmid = note.notesid
                                INNER JOIN {$table_prefix}_attachments att ON att.attachmentsid = seatt.attachmentsid
                                INNER JOIN {$table_prefix}_crmentity ent1 ON ent1.crmid = att.attachmentsid
                                WHERE ent.deleted = 0 AND ent1.deleted = 0 AND note.filestatus = 1 AND note.kp_allega_automat = 1 AND inv.id = ".$Records[$i];
        
        $res_trova_allegato = $adb->query($q_trova_allegato);
        $num_trova_allegato = $adb->num_rows($res_trova_allegato);

        if($num_trova_allegato>0){

            for($j  =0; $j < $num_trova_allegato; $j++){

                $allegato_trovato = $adb->query_result($res_trova_allegato,$j,'filename');
                $allegato_trovato = html_entity_decode(strip_tags($allegato_trovato), ENT_QUOTES,$default_charset);
                        
                $id_allegato = $adb->query_result($res_trova_allegato,$j,'attachmentsid');
                $id_allegato = html_entity_decode(strip_tags($id_allegato), ENT_QUOTES,$default_charset);
                        
                $filename_completo = $id_allegato."_".$allegato_trovato;
                
                if(count($array_allegati_trovati) > 0){
                    if(!in_array($filename_completo,$array_allegati_trovati)){
                        $array_allegati_trovati[$j] = $filename_completo;
                    }
                }
                else{
                    $array_allegati_trovati[$j] = $filename_completo;
                }
            }
            if(count($array_allegati_trovati) > 0){
                for($k = 0; $k < count($array_allegati_trovati); $k++){
                    echo "--SPLIT--".$array_allegati_trovati[$k];
                }
            }
        }
	}
}
else{
    echo $name;
}

/* kpro@tom082820180913 end */

// EXIT
exit();
?>