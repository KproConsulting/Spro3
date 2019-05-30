<?php 

/* kpro@20171117155432 */ 

/** 
 * @copyright (c) 2017, Kpro Consulting Srl 
 * 
 * Estensione classe KpContiCorrenti 
 */ 

require_once('modules/KpContiCorrenti/KpContiCorrenti.php'); 

class KpContiCorrentiKp extends KpContiCorrenti { 

    var $list_fields = Array();
    
    var $list_fields_name = Array(
        'Numero Conto Corrente'=>'kp_numero_conto',
        'Azienda'=>'kp_azienda',
        'Banca'=>'kp_banca',
        'Nome Banca'=>'kp_nome_banca',
        'IBAN'=>'kp_iban',
        'Default'=>'kp_default',
        'Assigned To'=>'assigned_user_id'
    );

    function KpContiCorrentiKp(){
        global $table_prefix;
        parent::__construct();
        $this->list_fields = Array(
            'Numero Conto Corrente'=>Array($table_prefix.'_kpconticorrenti'=>'kp_numero_conto'),
            'Azienda'=>Array($table_prefix.'_kpconticorrenti'=>'kp_azienda'),
            'Banca'=>Array($table_prefix.'_kpconticorrenti'=>'kp_banca'),
            'Nome Banca'=>Array($table_prefix.'_kpconticorrenti'=>'kp_nome_banca'),
            'IBAN'=>Array($table_prefix.'_kpconticorrenti'=>'kp_iban'),
            'Default'=>Array($table_prefix.'_kpconticorrenti'=>'kp_default'),
            'Assigned To'=>Array($table_prefix.'_crmentity'=>'smownerid')
        );

    }

    function save_module($module){
        
        global $table_prefix, $adb;

        parent::save_module($module);

        if($this->column_fields['kp_banca'] != 0 && $this->column_fields['kp_banca'] != '' && $this->column_fields['kp_banca'] != null){

            $q_nome_banca = "SELECT bc.kp_nome_banca,
                            bc.kp_nome_agenzia
                            FROM {$table_prefix}_kpbanche bc
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = bc.kpbancheid
                            WHERE ent.deleted = 0 AND bc.kpbancheid = ".$this->column_fields['kp_banca'];
            $res_nome_banca = $adb->query($q_nome_banca);
            if($adb->num_rows($res_nome_banca) > 0){

                $nome_banca = $adb->query_result($res_nome_banca, 0, 'kp_nome_banca');
                $nome_banca = html_entity_decode(strip_tags($nome_banca), ENT_QUOTES,$default_charset);

                $nome_agenzia = $adb->query_result($res_nome_banca, 0, 'kp_nome_agenzia');
                $nome_agenzia = html_entity_decode(strip_tags($nome_agenzia), ENT_QUOTES,$default_charset);

                $nome_banca_conto = $nome_banca." ".$nome_agenzia;
                $nome_banca_conto = addslashes($nome_banca_conto);

                $update = "UPDATE {$table_prefix}_kpconticorrenti
                        SET kp_nome_banca = '{$nome_banca_conto}'
                        WHERE kpconticorrentiid = ".$this->id;
                $adb->query($update);

            }

        }

    }

} 

?>