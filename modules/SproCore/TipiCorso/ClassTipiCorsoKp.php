<?php 
require_once('modules/TipiCorso/TipiCorso.php');

class TipiCorsoKp extends TipiCorso {

	var $list_fields = Array();

	var $list_fields_name = Array(
		'Tipi Corso Name' => 'tipicorso_name',
		'Durata corso' => 'durata_corso',
		'Validita Tipo Corso' => 'validita_tipi_corso'
	);

	function TipiCorsoKp(){
		global $table_prefix;
		parent::__construct();
		$this->list_fields = Array(
			'Tipi Corso Name'=>Array($table_prefix.'_tipicorso'=>'tipicorso_name'),
			'Durata corso'=>Array($table_prefix.'_tipicorso'=>'durata_corso'),
			'Validita Tipo Corso'=>Array($table_prefix.'_tipicorso'=>'validita_tipi_corso')
		);
    }
    
    //Script modifica Funtion Save
	function save_module($module){

		global $table_prefix, $adb;

        parent::save_module($module);
        
        $this->controlliAutomatici();
		
    }
    
    function controlliAutomatici(){
        global $adb, $table_prefix, $default_charset;

        //kpro@tom201220181646 evita che un utente inserisca un tipo corso come aggiornamento di se stesso
        if( $this->column_fields['aggiornamento_di'] == $this->id ){
            $update = "UPDATE {$table_prefix}_tipicorso SET
                        aggiornamento_di = 0
                        WHERE tipicorsoid = ".$this->id;
            $adb->query($update);
        }
        
    }

	/* kpro@tom270720170938 */

	function get_aggiornamento_di($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view, $currentModule, $current_user;
		global $table_prefix;
		$log->debug("Entering get_aggiornamento_di(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);
		
		$parenttab = getParentTab();

		if($singlepane_view == 'true'){
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		}
		else{
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		}

		$button = '';
		if($actions) {
			$button .= $this->get_related_buttons($this_module, $id, $related_module, $actions);
		}

		$query = "SELECT 
					".$table_prefix."_tipicorso.tipicorsoid,
					".$table_prefix."_tipicorso.tipicorso_name,
					".$table_prefix."_tipicorso.durata_corso,
					".$table_prefix."_tipicorso.aggiornamento_di,
					".$table_prefix."_tipicorso.formaz_scaglionata,
					".$table_prefix."_tipicorso.anni_rinnovo,
					".$table_prefix."_tipicorso.validita_tipi_corso,
					".$table_prefix."_crmentity.smownerid
					FROM ".$table_prefix."_tipicorso
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_tipicorso.tipicorsoid
					WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_tipicorso.aggiornamento_di = $id ";
		
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
		
		if($return_value == null) {
			$return_value = Array();
		}
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_aggiornamento_di method ...");

		return $return_value;

	}

	function get_aggiorna_anche_tc($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view, $currentModule, $current_user;
		global $table_prefix;
		$log->debug("Entering get_aggiorna_anche_tc(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true'){
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		}
		else{
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		}

		$button = '';
		if($actions) {
			$button .= $this->get_related_buttons($this_module, $id, $related_module, $actions);
		}

		$query = "SELECT 
					".$table_prefix."_tipicorso.tipicorsoid,
					".$table_prefix."_tipicorso.tipicorso_name,
					".$table_prefix."_tipicorso.durata_corso,
					".$table_prefix."_tipicorso.aggiornamento_di,
					".$table_prefix."_tipicorso.formaz_scaglionata,
					".$table_prefix."_tipicorso.anni_rinnovo,
					".$table_prefix."_tipicorso.validita_tipi_corso,
					".$table_prefix."_crmentity.smownerid
					FROM ".$table_prefix."_crmentityrel
					INNER JOIN ".$table_prefix."_tipicorso ON ".$table_prefix."_tipicorso.tipicorsoid = ".$table_prefix."_crmentityrel.relcrmid
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_tipicorso.tipicorsoid
					WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_crmentityrel.module = 'TipiCorso' AND ".$table_prefix."_crmentityrel.relmodule = 'TipiCorso' AND ".$table_prefix."_crmentityrel.crmid = $id ";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) {
			$return_value = Array();
		}
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_aggiorna_anche_tc method ...");
		
		return $return_value;

	}

	function get_aggiornato_anche_da_tc($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view, $currentModule, $current_user;
		global $table_prefix;
		$log->debug("Entering get_aggiornato_anche_da_tc(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$other = CRMEntity::getInstance($related_module);
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true'){
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		}
		else{
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
		}

		$button = '';
		if($actions) {
			$button .= $this->get_related_buttons($this_module, $id, $related_module, $actions);
		}

		$query = "SELECT 
					".$table_prefix."_tipicorso.tipicorsoid,
					".$table_prefix."_tipicorso.tipicorso_name,
					".$table_prefix."_tipicorso.durata_corso,
					".$table_prefix."_tipicorso.aggiornamento_di,
					".$table_prefix."_tipicorso.formaz_scaglionata,
					".$table_prefix."_tipicorso.anni_rinnovo,
					".$table_prefix."_tipicorso.validita_tipi_corso,
					".$table_prefix."_crmentity.smownerid
					FROM ".$table_prefix."_crmentityrel
					INNER JOIN ".$table_prefix."_tipicorso ON ".$table_prefix."_tipicorso.tipicorsoid = ".$table_prefix."_crmentityrel.crmid
					INNER JOIN ".$table_prefix."_crmentity ON ".$table_prefix."_crmentity.crmid = ".$table_prefix."_tipicorso.tipicorsoid
					WHERE ".$table_prefix."_crmentity.deleted = 0 AND ".$table_prefix."_crmentityrel.relmodule = 'TipiCorso' AND ".$table_prefix."_crmentityrel.module = 'TipiCorso' AND ".$table_prefix."_crmentityrel.relcrmid = $id ";
		
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) {
			$return_value = Array();
		}
		$return_value['CUSTOM_BUTTON'] = $button;
		
		$log->debug("Exiting get_aggiornato_anche_da_tc method ...");
		
		return $return_value;

	}

	/* kpro@tom270720170938 end */

	/* kpro@bid051020181500 */
	function getAlboFormatori($filtro){
        global $adb, $table_prefix, $default_charset, $current_user;

        $debug = false;

        $result = array();

        $lista_docenti = $this->getListaDocenti($filtro);

        foreach($lista_docenti as $docente){
                
            $lista_unita_formative = $this->getUnitaFormative($filtro, $docente["id"]);

            foreach($lista_unita_formative as $unita_formativa){

                $lista_tipi_corso = $this->getTipiCorso($filtro, $unita_formativa["id"]);

                foreach($lista_tipi_corso as $tipo_corso){

                    $dati_servizio = $this->getDatiServizio($tipo_corso['id_servizio']);

                    $prezzo_listino = $this->getPrezzoListino($docente['listino'], $tipo_corso['id_servizio']);

                    $prezzo_servizio = number_format($dati_servizio['prezzo_servizio'], 2, ',', '.')." €";
                    $prezzo_listino = number_format($prezzo_listino, 2, ',', '.')." €";

                    $result[] = array(
                        "fornitore" => $docente["nome_fornitore"],
                        "unita_formativa" => $unita_formativa["nome_unita_formativa"],
                        "tipo_corso" => $tipo_corso["nome_tipo_corso"],
                        "servizio" => $dati_servizio["nome_servizio"],
                        "prezzo_listino" => $prezzo_listino,
                        "prezzo_servizio" => $prezzo_servizio
                    );

                }

            }

        }

        if( $debug ){
            print_r( array("Lista Docenti" => $lista_docenti) ); die;
        }

        return $result;

    }

    function getListaDocenti($filtro){
        global $adb, $table_prefix, $default_charset, $current_user;

        $debug = false;

        $result = array();

        $select  = "SELECT ven.vendorid,
                ven.vendorname,
                ven.kp_listino
                FROM {$table_prefix}_vendor ven
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = ven.vendorid";

        $where = "WHERE ent.deleted = 0 AND ven.tipo_fornitore = 'Docente'";

        if($filtro["fornitore"] != null && $filtro["fornitore"] != ""){

            $where .= " AND ven.vendorname LIKE '%".$filtro["fornitore"]."%'";
        }

        $order = "ORDER BY ven.vendorname";

        $query = $select."\n".$where."\n".$order;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'vendorid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome_fornitore = $adb->query_result($result_query, $i, 'vendorname');
            $nome_fornitore = html_entity_decode(strip_tags($nome_fornitore), ENT_QUOTES, $default_charset);

            $listino = $adb->query_result($result_query, $i, 'kp_listino');
            $listino = html_entity_decode(strip_tags($listino), ENT_QUOTES, $default_charset);
            if($listino == null || $listino == "" ){
                $listino = 0;
            }

            $result[] = array(
                "id" => $id,
                "nome_fornitore" => $nome_fornitore,
                "listino" => $listino
            );

        }

        if( $debug ){
            print_r( array("Query" => $query, "Result" => $result) ); die;
        }

        return $result;

    }

    function getUnitaFormative($filtro, $fornitore){
        global $adb, $table_prefix, $default_charset, $current_user;

        $debug = false;

        $result = array();

        $select  = "SELECT * 
                FROM (
                SELECT un.kpmoduliformazioneid,
                un.kp_nome_modulo
                FROM {$table_prefix}_crmentityrel entrel
                INNER JOIN {$table_prefix}_kpmoduliformazione un ON un.kpmoduliformazioneid = entrel.relcrmid
                WHERE entrel.crmid = {$fornitore}
                AND entrel.relmodule = 'KpModuliFormazione'
                UNION
                SELECT un.kpmoduliformazioneid,
                un.kp_nome_modulo
                FROM {$table_prefix}_crmentityrel entrel
                INNER JOIN {$table_prefix}_kpmoduliformazione un ON un.kpmoduliformazioneid = entrel.crmid
                WHERE entrel.relcrmid = {$fornitore}
                AND entrel.module = 'KpModuliFormazione' ) AS i";

        $where = "";

        if($filtro["unita_formativa"] != null && $filtro["unita_formativa"] != ""){

            $where .= " WHERE i.kp_nome_modulo LIKE '%".$filtro["unita_formativa"]."%'";
        }

        $order = "ORDER BY i.kp_nome_modulo";

        $query = $select."\n".$where."\n".$order;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'kpmoduliformazioneid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome_unita_formativa = $adb->query_result($result_query, $i, 'kp_nome_modulo');
            $nome_unita_formativa = html_entity_decode(strip_tags($nome_unita_formativa), ENT_QUOTES, $default_charset);

            $result[] = array(
                "id" => $id,
                "nome_unita_formativa" => $nome_unita_formativa
            );

        }

        if( $debug ){
            print_r( array("Query" => $query, "Result" => $result) ); die;
        }

        return $result;

    }

    function getTipiCorso($filtro, $unita_formativa){
        global $adb, $table_prefix, $default_charset, $current_user;

        $debug = false;

        $result = array();

        $select  = "SELECT * 
                FROM (
                SELECT tp.tipicorsoid,
                tp.tipicorso_name,
                tp.kp_servizio
                FROM {$table_prefix}_crmentityrel entrel
                INNER JOIN {$table_prefix}_tipicorso tp ON tp.tipicorsoid = entrel.relcrmid
                WHERE entrel.crmid = {$unita_formativa}
                AND entrel.relmodule = 'TipiCorso'
                UNION
                SELECT tp.tipicorsoid,
                tp.tipicorso_name,
                tp.kp_servizio
                FROM {$table_prefix}_crmentityrel entrel
                INNER JOIN {$table_prefix}_tipicorso tp ON tp.tipicorsoid = entrel.crmid
                WHERE entrel.relcrmid = {$unita_formativa}
                AND entrel.module = 'TipiCorso' ) AS i";

        $where = "";

        if($filtro["tipo_corso"] != null && $filtro["tipo_corso"] != ""){

            $where .= " WHERE i.tipicorso_name LIKE '%".$filtro["tipo_corso"]."%'";
        }

        $order = "ORDER BY i.tipicorso_name";

        $query = $select."\n".$where."\n".$order;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'tipicorsoid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome_tipo_corso = $adb->query_result($result_query, $i, 'tipicorso_name');
            $nome_tipo_corso = html_entity_decode(strip_tags($nome_tipo_corso), ENT_QUOTES, $default_charset);

            $id_servizio = $adb->query_result($result_query, $i, 'kp_servizio');
            $id_servizio = html_entity_decode(strip_tags($id_servizio), ENT_QUOTES, $default_charset);
            if($id_servizio == "" || $id_servizio == null){
                $id_servizio = 0;
            }

            $result[] = array(
                "id" => $id,
                "nome_tipo_corso" => $nome_tipo_corso,
                "id_servizio" => $id_servizio
            );

        }

        if( $debug ){
            print_r( array("Query" => $query, "Result" => $result) ); die;
        }

        return $result;
    }

    function getDatiServizio($servizio){
        global $adb, $table_prefix, $default_charset, $current_user;

        $debug = false;

        $result = array();

        $query  = "SELECT ser.servicename,
                ser.unit_price
                FROM {$table_prefix}_service ser
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = ser.serviceid
                WHERE ent.deleted = 0 AND ser.serviceid = {$servizio}";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $nome_servizio = $adb->query_result($result_query, 0, 'servicename');
            $nome_servizio = html_entity_decode(strip_tags($nome_servizio), ENT_QUOTES, $default_charset);

            $prezzo_servizio = $adb->query_result($result_query, 0, 'unit_price');
            $prezzo_servizio = html_entity_decode(strip_tags($prezzo_servizio), ENT_QUOTES, $default_charset);
            if($prezzo_servizio == "" || $prezzo_servizio == null){
                $prezzo_servizio = 0;
            }

        }
        else{
            $nome_servizio = "-";
            $prezzo_servizio = 0;
        }

        $result = array(
            "nome_servizio" => $nome_servizio,
            "prezzo_servizio" => $prezzo_servizio
        );

        if( $debug ){
            print_r( array("Query" => $query, "Result" => $result) ); die;
        }

        return $result;
    }

    function getPrezzoListino($listino, $servizio){
        global $adb, $table_prefix, $default_charset, $current_user;

        $debug = false;

        $query  = "SELECT prel.listprice 
                FROM {$table_prefix}_pricebook pr
                INNER JOIN {$table_prefix}_pricebookproductrel prel ON prel.pricebookid = pr.pricebookid
                INNER JOIN {$table_prefix}_service ser ON ser.serviceid = prel.productid
                INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = pr.pricebookid
                WHERE ent.deleted = 0 AND pr.pricebookid = {$listino}
                AND ser.serviceid = {$servizio}";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $prezzo_listino = $adb->query_result($result_query, 0, 'listprice');
            $prezzo_listino = html_entity_decode(strip_tags($prezzo_listino), ENT_QUOTES, $default_charset);
            if($prezzo_listino == "" || $prezzo_listino == null){
                $prezzo_listino = 0;
            }

        }
        else{
            $prezzo_listino = 0;
        }

        if( $debug ){
            print_r( array("Query" => $query, "Result" => $prezzo_listino) ); die;
        }

        return $prezzo_listino;
	}
	/* kpro@bid051020181500 end */

}
?>