<?php

/* kpro@bid19062018 */

/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 */

include_once('../../../../../config.inc.php');
chdir($root_directory);
require_once('include/utils/utils.php');
include_once('vtlib/Vtiger/Module.php');
$Vtiger_Utils_Log = true;
global $adb, $table_prefix, $current_user, $site_URL, $default_charset;
session_start();

$rows = array();

if (!isset($_SESSION['authenticated_user_id'])) {

    $json = json_encode($rows);
    print $json;
    die;

    header("Location: ". $site_URL."/index.php");
	die; 
}
$current_user->id = $_SESSION['authenticated_user_id'];

if(isset($_GET['colonna']) && isset($_GET['valore'])){
    $colonna = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['colonna']), ENT_QUOTES,$default_charset)), ENT_QUOTES, $default_charset);

    $valore = htmlspecialchars(addslashes(html_entity_decode(strip_tags($_GET['valore']), ENT_QUOTES,$default_charset)), ENT_QUOTES, $default_charset);
    if($valore == null){
        $valore = '';
    }

    $valore = addslashes($valore);

    $query = "SELECT * FROM kp_settings_company_info";

    $result_query = $adb->query($query);
    $num_result = $adb->num_rows($result_query);

    if( $num_result > 0 ){

        $update = "UPDATE kp_settings_company_info SET
                    ".$colonna." = '".$valore."'";
    
        $adb->query($update);

    }
    else{

        $insert = "INSERT INTO kp_settings_company_info 
                    (".$colonna.")
                    VALUES ('".$valore."')";
                    
        $adb->query($insert);

    }

}

$rows[] = array("result" => "ok");

$json = json_encode($rows);
print $json;

?>