<?php
global $table_prefix;

$crmid = $_REQUEST['crmid'];

if( $crmid != '' && $crmid != 'undefined'){
	$query .= " AND ".$table_prefix."_tipicorso.tipicorsoid != ".$crmid." ";
}

?>