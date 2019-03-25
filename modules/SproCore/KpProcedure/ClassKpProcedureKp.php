<?php 

/* kpro@20170628091424 */ 

/** 
 * @copyright (c) 2017, Kpro Consulting Srl 
 * 
 * Estensione classe KpProcedure 
 */ 

require_once('modules/KpProcedure/KpProcedure.php'); 

require_once('modules/SproCore/CustomViews/KpBPMNcreator/KpBPMN.php'); 

class KpProcedureKp extends KpProcedure { 

    public $kpshowProcessGraphTab;
    private $sequenza_stampa;

    var $list_fields = Array();

	var $list_fields_name = Array(
		'Nome Procedura'=>'kp_nome_procedura',
		'Tipo Procedura'=>'kp_tipo_procedura',
		'Data Procedura'=>'kp_data_procedura',
		'Scadenza Procedura'=>'kp_scadenza_procedura',
		'Assigned To'=>'assigned_user_id'	
	);

	function KpProcedureKp(){
		global $table_prefix;
		parent::__construct();
		$this->list_fields = Array(
			'Nome Procedura'=>Array($table_prefix.'_kpprocedure'=>'kp_nome_procedura'),
			'Tipo Procedura'=>Array($table_prefix.'_kpprocedure'=>'kp_tipo_procedura'),
			'Data Procedura'=>Array($table_prefix.'_kpprocedure'=>'kp_data_procedura'),
			'Scadenza Procedura'=>Array($table_prefix.'_kpprocedure'=>'kp_scadenza_procedura'),
			'Assigned To'=>Array($table_prefix.'_crmentity'=>'smownerid')
		);

	}

	function save_module($module){

        global $table_prefix, $adb, $current_user;

		parent::save_module($module);
		
		if($this->column_fields['kp_disegnato_da'] == ''){

			$disegnato_da = $current_user->last_name." ".$current_user->first_name;

			$update = "UPDATE {$table_prefix}_kpprocedure SET
                            kp_disegnato_da = '".$disegnato_da."'
                            WHERE kpprocedureid = ".$this->id;
            $adb->query($update);

		}

	}

    function getExtraDetailBlock($selectionProcesses=false) {
        global $mod_strings, $app_strings;
        require_once('Smarty_setup.php');
        $smarty = new vtigerCRM_Smarty();
        $smarty->assign('MOD',$mod_strings);
        $smarty->assign('APP',$app_strings);
  
        $this->kpshowProcessGraphTab = true;	/* kpro@20170628091424 */ 

		$PMUtils = ProcessMakerUtils::getInstance();

        return $smarty->fetch('SproCore/KpProcessGraph.tpl');   /* kpro@20170628091424 */ 
    }

    function getExtraDetailTabs() {
		global $adb, $table_prefix, $app_strings, $currentModule;
		
		if ($this->modulename == 'Activity') {
			$moduleName = ($this->column_fields['activitytype'] == 'Task' ? 'Calendar' : 'Events');
		} else {
			$moduleName	= $this->modulename;
		}
		
		$return = array();
		if ($this->has_detail_charts && vtlib_isModuleActive('Charts')) {
			$return[] = array('label'=>getTranslatedString('Charts','Charts'),'href'=>'','onclick'=>"kpChangeDetailTab('{$moduleName}', '{$this->id}', 'detailCharts', this)");
		}
		if ($this->showProcessGraphTab) {
			$return[] = array('label'=>getTranslatedString('Process Graph','Processes'),'href'=>'','onclick'=>"kpChangeDetailTab('{$moduleName}', '{$this->id}', 'ProcessGraph', this)");
		}

		if (vtlib_isModuleActive('ChangeLog')) {
			// if module ChangeLog is active and linked to the current add tab History
			$relationManager = RelationManager::getInstance();
			$relation = $relationManager->getRelations('ChangeLog',ModuleRelation::$TYPE_NTO1,$currentModule);
			if (!empty($relation)) {
				$return[] = array('label'=>getTranslatedString('LBL_HISTORY'),'href'=>'','onclick'=>"kpChangeDetailTab('{$moduleName}', '{$this->id}', 'HistoryTab', this)");
			}
		}

        /* kpro@20170628091424 */ 
        if ($this->kpshowProcessGraphTab) {
			$return[] = array('label'=>getTranslatedString('Processi (BPMN)','KpProcessGraphTab'),'href'=>'','onclick'=>"kpChangeDetailTab('{$moduleName}', '{$this->id}', 'KpProcessGraphTab', this)");
		}
        /* kpro@20170628091424 end */ 

		return $return;
    }
    
    public function converBPMNxmlToArray($bpmn_xml){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $bpmn_xml = str_replace("bpmn:", "", $bpmn_xml);
        $bpmn_xml = str_replace("bpmn2:", "", $bpmn_xml);
        $bpmn_xml = str_replace("bpmndi:", "", $bpmn_xml);   //kpro@tom05062018
        $bpmn_xml = str_replace("dc:", "", $bpmn_xml);   //kpro@tom05062018
    
        $xml = simplexml_load_string($bpmn_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        //print_r($xml);die;

        $process = $xml->process;

        $elements_all = array();    //kpro@tom05062018
        
        $elements_array = array();

        $elements_array_key = array();

        $startEvent_array = array();

        $sequence_array_key = array();

        foreach($process->startEvent as $startEvent){

            //print_r($task);

            $id = (string)$startEvent->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$startEvent->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $startEvent_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "startEvent");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "startEvent",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $endEvent_array = array();

        foreach($process->endEvent as $endEvent){

            $id = (string)$endEvent->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$endEvent->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $endEvent_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "endEvent");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "endEvent",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $task_array = array();

        foreach($process->task as $task){

            $id = (string)$task->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$task->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $task_array[] = array("id" => $id,
                                    "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "task");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "task",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $subprocess_array = array();

        foreach($process->subProcess as $subProcess){

            $id = (string)$subProcess->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$subProcess->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $subprocess_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "subProcess");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "subProcess",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $sendTask_array = array();

        foreach($process->sendTask as $sendTask){

            $id = (string)$sendTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$sendTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $sendTask_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "sendTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "sendTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $receiveTask_array = array();

        foreach($process->receiveTask as $receiveTask){

            $id = (string)$receiveTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$receiveTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $receiveTask_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "receiveTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "receiveTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $userTask_array = array();

        foreach($process->userTask as $userTask){

            $id = (string)$userTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$userTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $userTask_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "userTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "userTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $manualTask_array = array();

        foreach($process->manualTask as $manualTask){

            $id = (string)$manualTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$manualTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $manualTask_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "manualTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "manualTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $businessRuleTask_array = array();

        foreach($process->businessRuleTask as $businessRuleTask){

            $id = (string)$businessRuleTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$businessRuleTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $businessRuleTask_array[] = array("id" => $id,
                                            "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "businessRuleTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "businessRuleTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $serviceTask_array = array();

        foreach($process->serviceTask as $serviceTask){

            $id = (string)$serviceTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$serviceTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $serviceTask_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "serviceTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "serviceTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $scriptTask_array = array();

        foreach($process->scriptTask as $scriptTask){

            $id = (string)$scriptTask->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$scriptTask->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $scriptTask_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "scriptTask");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "scriptTask",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $callActivity_array = array();

        foreach($process->callActivity as $callActivity){

            $id = (string)$callActivity->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$callActivity->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $callActivity_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "callActivity");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "callActivity",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $transaction_array = array();

        foreach($process->transaction as $transaction){

            $id = (string)$transaction->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$transaction->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $transaction_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "transaction");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "transaction",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $exclusiveGateway_array = array();

        foreach($process->exclusiveGateway as $exclusiveGateway){

            $id = (string)$exclusiveGateway->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$exclusiveGateway->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $exclusiveGateway_array[] = array("id" => $id,
                                                "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "exclusiveGateway");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "exclusiveGateway",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $inclusiveGateway_array = array();

        foreach($process->inclusiveGateway as $inclusiveGateway){

            $id = (string)$inclusiveGateway->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$inclusiveGateway->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $inclusiveGateway_array[] = array("id" => $id,
                                        "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "inclusiveGateway");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "inclusiveGateway",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $complexGateway_array = array();

        foreach($process->complexGateway as $complexGateway){

            $id = (string)$complexGateway->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$complexGateway->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $complexGateway_array[] = array("id" => $id,
                                            "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "complexGateway");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "complexGateway",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $eventBasedGateway_array = array();

        foreach($process->eventBasedGateway as $eventBasedGateway){

            $id = (string)$eventBasedGateway->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $nome = (string)$eventBasedGateway->attributes()->name;
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            $nome = trim($nome);

            $elements_all[] = $id;

            $eventBasedGateway_array[] = array("id" => $id,
                                                "nome" => $nome);

            $elements_array[] = array("id" => $id,
                                        "nome" => $nome,
                                        "type" => "eventBasedGateway");

            $elements_array_key[$id] = array("id" => $id,
                                            "nome" => $nome,
                                            "order"=> 0,
                                            "type" => "eventBasedGateway",
                                            "x" => 0,
                                            "y" => 0);
                            
        }

        $sequence_array = array();

        $sequence_order = 0;

        foreach($process->sequenceFlow as $sequence){

            $id = (string)$sequence->attributes()->id;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            $sourceRef = (string)$sequence->attributes()->sourceRef;
            $sourceRef = html_entity_decode(strip_tags($sourceRef), ENT_QUOTES,$default_charset);
            $sourceRef = trim($sourceRef);

            $targetRef = (string)$sequence->attributes()->targetRef;
            $targetRef = html_entity_decode(strip_tags($targetRef), ENT_QUOTES,$default_charset);
            $targetRef = trim($targetRef);

            $sequence_array[] = array("id" => $id,
                                        "sourceRef" => $sourceRef,
                                        "targetRef" => $targetRef,
                                        "sequence_order" => $sequence_order);
            
            $sequence_array_key[$targetRef] = array("id" => $id,
                                                    "sourceRef" => $sourceRef,
                                                    "targetRef" => $targetRef,
                                                    "sequence_order" => $sequence_order);

            $sequence_order++;

        }

        //kpro@tom05062018

        $bpmnPlane = $xml->BPMNDiagram->BPMNPlane; 

        foreach($bpmnPlane->BPMNShape as $shape){

            $id = (string)$shape->attributes()->bpmnElement;
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            $id = trim($id);

            if( in_array($id, $elements_all) ){

                $bound = $shape->Bounds;
                
                $x = (string)$bound->attributes()->x;
                $x = html_entity_decode(strip_tags($x), ENT_QUOTES,$default_charset);
                $x = trim($x);

                $y = (string)$bound->attributes()->y;
                $y = html_entity_decode(strip_tags($y), ENT_QUOTES,$default_charset);
                $y = trim($y);

                $elements_array_key[$id]["x"] = $x;
                $elements_array_key[$id]["y"] = $y;

            }

        }

        //kpro@tom05062018 end 

        //Questo metodo calcola l'ordine degli elementi seguento i link
        foreach($elements_array as $elemento){

            $elements_array_key[$elemento["id"]]["order"] = $this->calcolaOrdineElemento($elemento["id"], $elements_array_key, $sequence_array_key, 0);
            
        }

        $dupplicati = $this->checkIfOrderDuplicati($elements_array_key, 0);
        $i = 0;

        while( $dupplicati ){

            $elements_array_key = $this->sistemaElementiConStessoOrdine($elements_array_key, 0);
            $dupplicati = $this->checkIfOrderDuplicati($elements_array_key, 0);

            $i++;
            if( $i == ( count($elements_all) * 3 ) ){
                $dupplicati = false;
            }

        }

        $result = array("elements" => $elements_array,
                        "elements_key" => $elements_array_key,
                        "startEvents" => $startEvent_array,
                        "endEvents" => $endEvent_array,
                        "tasks" => $task_array,
                        "subprocess" => $subprocess_array,
                        "sendTasks" => $sendTask_array,
                        "receiveTasks" => $receiveTask_array,
                        "userTasks" => $userTask_array,
                        "manualTasks" => $manualTask_array,
                        "businessRuleTasks" => $businessRuleTask_array,
                        "serviceTasks" => $serviceTask_array,
                        "scriptTasks" => $scriptTask_array,
                        "callActivities" => $callActivity_array,
                        "transactions" => $transaction_array,
                        "exclusiveGateways" => $exclusiveGateway_array,
                        "inclusiveGateways" => $inclusiveGateway_array,
                        "complexGateways" => $complexGateway_array,
                        "eventBasedGateways" => $eventBasedGateway_array,
                        "sequences" => $sequence_array);
        
        return $result;

    }

    private function sistemaElementiConStessoOrdine($elements_array, $order){
        global $adb, $table_prefix, $default_charset, $current_user;

        $first_element = $this->getIdOfFirstTaskOfOrder($elements_array, $order);
        //print_r("Il primo elemento di order ".$order." è ".$first_element);

        if( $first_element != "" ){

            $elements_array[$first_element]["order"] = $order;

            $next_order = $order + 1;
            
            foreach( $elements_array as $elemento ){

                if( $elemento['order'] == $order && $elemento['id'] != $first_element ){

                    $elements_array[ $elemento['id'] ]["order"] = $next_order;

                }

            }

            $elements_array = $this->sistemaElementiConStessoOrdine($elements_array, $next_order);

        }

        return $elements_array;

    }

    private function getIdOfFirstTaskOfOrder($elements_array, $order){
        global $adb, $table_prefix, $default_charset, $current_user;

        $id = "";
        $x = 0;

        foreach( $elements_array as $elemento ){

            if( $elemento['order'] == $order && ( $elemento['x'] < $x || $x == 0) ){

                $id = $elemento['id'];
                $x = $elemento['x'];

            }

        }

        return $id;

    }

    private function calcolaOrdineElemento($bpmn_id, $array_elementi, $array_link, $start, $gia_passati = array()){
        global $adb, $table_prefix, $default_charset, $current_user;

        //Questo metodo calcola l'ordine degli elementi seguendo i link

        $source_found = false;

        $x_corrente = $array_elementi[$bpmn_id]['x'];

        foreach( $array_link as $link ){

            if($link["targetRef"] == $bpmn_id){

                $source_found = true;

            }

        }

        //print_r($array_link);

        if( $source_found ){

            $source = $array_link[$bpmn_id]["sourceRef"];
             
            if( !in_array($source, $gia_passati) && $array_elementi[$source]['x'] <= $x_corrente ){
                $start++;
                $gia_passati[] = $source;
                $start = $this->calcolaOrdineElemento($source, $array_elementi, $array_link, $start, $gia_passati);
            }
 
        }

        return $start;

    }

    private function checkIfOrderDuplicati($elements_array, $order_start){
        global $adb, $table_prefix, $default_charset, $current_user;

        $max_order = $this->getMaxOrder($elements_array);

        if( $max_order > $order_start){

            for($i = $order_start; $i <= $max_order; $i++ ){

                $duplicato = $this->checkIfOrderDuplicato($elements_array, $i);

                if( $duplicato ){
                    return true;
                }

            }

            return false;

        }
        else{
            return false;
        }

    }

    private function getMaxOrder($elements_array){
        global $adb, $table_prefix, $default_charset, $current_user;

        $order = 0;
        $id = "";

        foreach( $elements_array as $elemento ){

            if( $elemento['order'] >= $order ){

                $order = $elemento['order'];
                $id = $elemento['id'];

            }

        }

        $result = array("max_order" => $order,
                        "task_id" => $id);

        return $result;

    }

    private function checkIfOrderDuplicato($elements_array, $order){
        global $adb, $table_prefix, $default_charset, $current_user;

        $elementi = array();

        foreach( $elements_array as $elemento ){

            if( $elemento['order'] == $order ){

                $elementi[] = $elemento['id'];

            }

        }

        if( count($elementi) == 0){
            return false;
        }
        else{
            return true;
        }

    }

    public function aggiornaBPMNcrm($id, $bpmn_xml){
        global $adb, $table_prefix, $default_charset, $current_user;

        if( $bpmn_xml != "" ){

            $bpmn_json = $this->converBPMNxmlToArray($bpmn_xml);
           
            if( count($bpmn_json["startEvents"]) > 0 ){
                
                $bpmn_xml = addslashes($bpmn_xml);
                
                $update = "UPDATE {$table_prefix}_kpprocedure SET
                            kp_bpmn_xml = '".$bpmn_xml."'
                            WHERE kpprocedureid = ".$id;

                $adb->query($update); 

                $order_array = $bpmn_json["elements_key"];

                $elementi_da_escludere = array("startEvent", "endEvent", "exclusiveGateway", "inclusiveGateway", "complexGateway", "eventBasedGateway");

                $update = "UPDATE {$table_prefix}_kpentitaprocedure entproc
                            INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                            SET
                            entproc.kp_aggiornato = '0'
                            WHERE ent.deleted = 0 AND entproc.kp_procedura = ".$id;

                $adb->query($update);

                foreach($bpmn_json["elements"] as $element){
        
                    if( !in_array($element["type"], $elementi_da_escludere) ){

                        $bpmn_id = $element["id"];
                        
                        $verifica_esistenza_elemento = $this->getElementoProcedura($id, $bpmn_id);

                        if($element["nome"] == "" || $element["nome"] == null){
                            $element["nome"] = $bpmn_id;
                        }

                        $order = $order_array[$bpmn_id]["order"];

                        if( $order == "" ){
                            $order = 0;
                        }

                        if( $verifica_esistenza_elemento["esiste"] ){

                            $focus = CRMEntity::getInstance('KpEntitaProcedure');
                            $focus->retrieve_entity_info($verifica_esistenza_elemento["id"], "KpEntitaProcedure");

                            foreach($focus->column_fields as $fieldname => $value) {
                                $focus->column_fields[$fieldname] = decode_html($value);
                            }

                            $focus->column_fields['kp_nome_entita'] = $element["nome"];
                            $focus->column_fields['kp_tipo_entita_bpmn'] = $element["type"]; 
                            $focus->column_fields['kp_order'] = $order; 
                            $focus->column_fields['kp_aggiornato'] = '1'; 
                            $focus->mode = 'edit';
                            $focus->id = $verifica_esistenza_elemento["id"];
                            $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

                        }
                        else{

                            $focus = CRMEntity::getInstance('KpEntitaProcedure');
                            $focus->column_fields['assigned_user_id'] = $current_user->id;
                            $focus->column_fields['kp_procedura'] = $id;
                            $focus->column_fields['kp_bpmn_id'] = $bpmn_id;
                            $focus->column_fields['kp_nome_entita'] = $element["nome"];
                            $focus->column_fields['kp_tipo_entita_bpmn'] = $element["type"];
                            $focus->column_fields['kp_order'] = $order; 
                            $focus->column_fields['kp_valore_aggiunto'] = "Non definito"; 
                            $focus->column_fields['kp_aggiornato'] = '1'; 
                            $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

                        }

                    }

                }

                $this->deleteElementiNonAggiornatiprocedura($id);

            }

        }

    }

    private function deleteElementiNonAggiornatiprocedura($procedura){
        global $adb, $table_prefix, $default_charset;

        $query = "SELECT 
                    entproc.kpentitaprocedureid id
                    FROM {$table_prefix}_kpentitaprocedure entproc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                    WHERE ent.deleted = 0 AND entproc.kp_aggiornato = '0' AND entproc.kp_procedura = ".$procedura;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $lista_ruoli = $this->getRuoliElementoProcedura($id_origine, array());

            foreach($lista_ruoli as $ruolo){

                $update = "UPDATE {$table_prefix}_crmentity SET
                            deleted  = 1
                            WHERE setype = 'KpRuoliAttivita' AND crmid = ".$ruolo["ruoliattivita_id"];
                $adb->query($update);

            }

            $update = "UPDATE {$table_prefix}_crmentity SET
                        deleted = 1
                        WHERE setype = 'KpEntitaProcedure' AND crmid = ".$id;
            $adb->query($update);
            
        }

    }

    public function getRuoliAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $lista_elementi_gia_relazionati = "(";

        $query = "SELECT 
                    ruolat.kp_ruolo ruolo
                    FROM {$table_prefix}_kpruoliattivita ruolat
                    INNER JOIN {$table_prefix}_crmentity entruolat ON entruolat.crmid = ruolat.kpruoliattivitaid
                    INNER JOIN {$table_prefix}_kpruoli ruol ON ruol.kpruoliid = ruolat.kp_ruolo
                    WHERE entruolat.deleted = 0 AND ruolat.kp_attivita = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $ruolo = $adb->query_result($result_query, $i, 'ruolo');
            $ruolo = html_entity_decode(strip_tags($ruolo), ENT_QUOTES,$default_charset);

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $ruolo;

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$ruolo;

            }

        }

        $lista_elementi_gia_relazionati .= ")";

        $query = "SELECT
                    ruol.kpruoliid kpruoliid,
                    ruol.kp_nome_ruolo kp_nome_ruolo 
                    FROM {$table_prefix}_kpruoli ruol
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = ruol.kpruoliid
                    WHERE ent.deleted = 0";

        if($lista_elementi_gia_relazionati != "()"){

            $query .= " AND ruol.kpruoliid NOT IN ".$lista_elementi_gia_relazionati;

        }
                        
        if($filtro["nome_ruolo"] != ""){
            $query .= " AND ruol.kp_nome_ruolo like '%".$filtro["nome_ruolo"]."%'";
        }

        $query .= " ORDER BY ruol.kp_nome_ruolo ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'kpruoliid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'kp_nome_ruolo');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome);
            
        }

        return $result;

    }

    public function setSVG($crmid, $svg){
        global $adb, $table_prefix, $default_charset, $current_user;

        $svg = addslashes($svg);

        $update = "UPDATE {$table_prefix}_kpprocedure SET
                    kp_bpmn_svg = '".$svg."'
                    WHERE kpprocedureid = ".$crmid;
        $adb->query($update);

    }

    public function getRuoloElementoProcedura($id, $ruolo){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    ruolat.kpruoliattivitaid id,
                    ruolat.kp_ruolo ruolo,
                    ruol.kp_nome_ruolo nome_ruolo,
                    ruolat.kp_resp_ruolo responsabilita,
                    ruolat.kp_soggetto nome_ruolo_attivita
                    FROM {$table_prefix}_kpruoliattivita ruolat
                    INNER JOIN {$table_prefix}_crmentity entruolat ON entruolat.crmid = ruolat.kpruoliattivitaid
                    INNER JOIN {$table_prefix}_kpruoli ruol ON ruol.kpruoliid = ruolat.kp_ruolo
                    WHERE entruolat.deleted = 0 AND ruolat.kp_attivita = ".$id." AND ruolat.kp_ruolo = ".$ruolo;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

            $ruolo_id = $adb->query_result($result_query, 0, 'ruolo');
            $ruolo_id = html_entity_decode(strip_tags($ruolo_id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'nome_ruolo');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $responsabilita = $adb->query_result($result_query, 0, 'responsabilita');
            $responsabilita = html_entity_decode(strip_tags($responsabilita), ENT_QUOTES,$default_charset);

            $nome_ruolo_attivita = $adb->query_result($result_query, 0, 'nome_ruolo_attivita');
            $nome_ruolo_attivita = html_entity_decode(strip_tags($nome_ruolo_attivita), ENT_QUOTES,$default_charset);
            
        }
        else{

            $esiste = false;

            $id = 0;
            $ruolo_id = 0;
            $nome = "";
            $responsabilita = "";
            $nome_ruolo_attivita = "";

        }

        $result = array('esiste' => $esiste,
                        'id' => $id,
                        'ruolo_id' => $ruolo_id,
                        'nome' => $nome,
                        'nome_ruolo_attivita' => $nome_ruolo_attivita,
                        'responsabilita' => $responsabilita);

        return $result;

    }

    public function getElementoProceduraById($id){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    entproc.kpentitaprocedureid id,
                    entproc.kp_nome_entita kp_nome_entita,
                    entproc.kp_bpmn_id kp_bpmn_id,
                    entproc.kp_tipo_entita_bpmn kp_tipo_entita_bpmn,
                    entproc.kp_relazionato_a_id kp_relazionato_a_id,
                    entproc.kp_procedura kp_procedura,
                    entproc.kp_order kp_order,
                    entproc.kp_valore_aggiunto kp_valore_aggiunto,
                    entproc.kp_attivita_dvr kp_attivita_dvr,
                    entproc.description description
                    FROM {$table_prefix}_kpentitaprocedure entproc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                    WHERE ent.deleted = 0 AND entproc.kpentitaprocedureid = ".$id;
        //print_r($query);die;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;
            
            $id = $adb->query_result($result_query, 0, 'id');
			$id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, 0, 'kp_nome_entita');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $nome = $this->encodeString($nome);

            $tipo_entita_bpmn = $adb->query_result($result_query, 0, 'kp_tipo_entita_bpmn');
            $tipo_entita_bpmn = html_entity_decode(strip_tags($tipo_entita_bpmn), ENT_QUOTES, $default_charset);
            
            $bpmn_id = $adb->query_result($result_query, 0, 'kp_bpmn_id');
			$bpmn_id = html_entity_decode(strip_tags($bpmn_id), ENT_QUOTES, $default_charset);

            $relazionato_a_id = $adb->query_result($result_query, 0, 'kp_relazionato_a_id');
            $relazionato_a_id = html_entity_decode(strip_tags($relazionato_a_id), ENT_QUOTES, $default_charset);
            
            $order = $adb->query_result($result_query, 0, 'kp_order');
            $order = html_entity_decode(strip_tags($order), ENT_QUOTES, $default_charset);
            
            $valore_aggiunto = $adb->query_result($result_query, 0, 'kp_valore_aggiunto');
			$valore_aggiunto = html_entity_decode(strip_tags($valore_aggiunto), ENT_QUOTES, $default_charset);

            if($relazionato_a_id != null && $relazionato_a_id != "" && $relazionato_a_id != 0){
                $dati_elemento_relazionato = $this->getElementoRelazionato($relazionato_a_id);
                //print_r($dati_elemento_relazionato);die;

                if( $dati_elemento_relazionato["esiste"] ){
                    $relazionato_a_nome = $dati_elemento_relazionato["nome"];
                }
                else{
                    $dati_elemento_relazionato = "";
                    $relazionato_a_id = 0;
                }
                
            }
            else{
                $relazionato_a_nome = "";
                $relazionato_a_id = 0;
            }

            $descrizione = $adb->query_result($result_query, 0, 'description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);
            
            $descrizione = $this->encodeString($descrizione);

            $procedura = $adb->query_result($result_query, 0, 'kp_procedura');
			$procedura = html_entity_decode(strip_tags($procedura), ENT_QUOTES, $default_charset);
            if($procedura == "" || $procedura == null){
                $procedura = 0;
            }

            $attivita_dvr = $adb->query_result($result_query, 0, 'kp_attivita_dvr');
			$attivita_dvr = html_entity_decode(strip_tags($attivita_dvr), ENT_QUOTES, $default_charset);
            if($attivita_dvr == "" || $attivita_dvr == null){
                $attivita_dvr = 0;
            }
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";
            $tipo_entita_bpmn = "";
            $relazionato_a_id = "";
            $descrizione = "";
            $relazionato_a_nome = "";
            $procedura = 0;
            $bpmn_id = "";
            $order = 0;
            $valore_aggiunto = "";
            $attivita_dvr = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $id,
                        "nome" => $nome,
                        "tipo_entita_bpmn" => $tipo_entita_bpmn,
                        "bpmn_id" => $bpmn_id,
                        "order" => $order,
                        "valore_aggiunto" => $valore_aggiunto,
                        "relazionato_a_id" => $relazionato_a_id,
                        "relazionato_a_nome" => $relazionato_a_nome,
                        "procedura" => $procedura,
                        "attivita_dvr" => $attivita_dvr,
                        "descrizione" => $descrizione);
        
        return $result;

    }

    public function setLinkRuoloElementoProcedura($id, $ruolo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $dati_ruolo = $this->getRuoloElementoProcedura($id, $ruolo);

        if( !$dati_ruolo["esiste"] ){

            $focus_ruolo = CRMEntity::getInstance('KpRuoli');
            $focus_ruolo->retrieve_entity_info($ruolo, "KpRuoli", $dieOnError=false); 

            $nome_ruolo = $focus_ruolo->column_fields["kp_nome_ruolo"];
            $nome_ruolo = html_entity_decode(strip_tags($nome_ruolo), ENT_QUOTES, $default_charset);

            $dati_elemento = $this->getElementoProceduraById($id);

            $soggetto = $nome_ruolo." - ".$dati_elemento["nome"];

            $focus = CRMEntity::getInstance('KpRuoliAttivita');
            $focus->column_fields['assigned_user_id'] = $current_user->id;
            $focus->column_fields['kp_soggetto'] = $soggetto;
            $focus->column_fields['kp_attivita'] = $id;
            $focus->column_fields['kp_ruolo'] = $ruolo;
            $focus->column_fields['kp_resp_ruolo'] = "Esecutore"; 
            $focus->save('KpRuoliAttivita', $longdesc = true, $offline_update = false, $triggerEvent = false);

            $text = "Assegnata attivita' ".$dati_elemento["nome"]." al ruolo ".$nome_ruolo;

            $this->setLogRevisione($dati_elemento["procedura"], $text);

        }

    }

    public function setLogRevisione($id, $testo){
        global $adb, $table_prefix, $default_charset;

        $testo = date("d/m/Y H:i:s")." - ".$testo;

        $old_log = $this->getLogRevisione($id);

        if($old_log != ""){
            $testo = sprintf("%s\n%s", $old_log, $testo);
        }

        $testo = addslashes($testo);

        $update = "UPDATE {$table_prefix}_kpprocedure SET
                    kp_log_revisione = '".$testo."'
                    WHERE kpprocedureid = ".$id;
        $adb->query($update);

    }

    public function getLogRevisione($id){
        global $adb, $table_prefix, $default_charset;

        $query = "SELECT 
                    kp_log_revisione
                    FROM {$table_prefix}_kpprocedure 
                    WHERE kpprocedureid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);
        
        if( $num_result > 0 ){

            $log_revisione = $adb->query_result($result_query, 0, 'kp_log_revisione');
            $log_revisione = html_entity_decode(strip_tags($log_revisione), ENT_QUOTES, $default_charset);

        }
        else{
            $log_revisione = "";
        }

        return $log_revisione;

    }

    public function getLogRevisioneNoDate($id){
        global $adb, $table_prefix, $default_charset;

        $log = "";

        $log_old = $this->getLogRevisione($id);

        if($log_old != ""){

            $log_line = explode("\n", $log_old);

            foreach($log_line as $line){

                $pos = strpos($line, '-');

                $line = substr($line, $pos);

                if($log == ""){
                    $log = $line;
                }
                else{
                    $log = sprintf("%s\n%s", $log, $line);
                }

            }
        
        }

        return $log;

    }

    public function getRuoliElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT 
                    ruolat.kp_ruolo ruolo,
                    ruol.kp_nome_ruolo nome_ruolo,
                    ruolat.kp_resp_ruolo responsabilita,
                    ruolat.kp_soggetto nome_ruolo_attivita,
                    ruolat.kpruoliattivitaid ruoliattivita_id
                    FROM {$table_prefix}_kpruoliattivita ruolat
                    INNER JOIN {$table_prefix}_crmentity entruolat ON entruolat.crmid = ruolat.kpruoliattivitaid
                    INNER JOIN {$table_prefix}_kpruoli ruol ON ruol.kpruoliid = ruolat.kp_ruolo
                    WHERE entruolat.deleted = 0 AND ruolat.kp_attivita = ".$id;

        if($filtro["nome_ruolo"] != ""){
            $query .= " AND ruol.kp_nome_ruolo like '%".$filtro["nome_ruolo"]."%'";  
        }

        $query .= " ORDER BY ruol.kp_nome_ruolo ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'ruolo');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome_ruolo');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $responsabilita = $adb->query_result($result_query, $i, 'responsabilita');
            $responsabilita = html_entity_decode(strip_tags($responsabilita), ENT_QUOTES,$default_charset);

            $nome_ruolo_attivita = $adb->query_result($result_query, $i, 'nome_ruolo_attivita');
            $nome_ruolo_attivita = html_entity_decode(strip_tags($nome_ruolo_attivita), ENT_QUOTES,$default_charset);

            $ruoliattivita_id = $adb->query_result($result_query, $i, 'ruoliattivita_id');
            $ruoliattivita_id = html_entity_decode(strip_tags($ruoliattivita_id), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'responsabilita' => $responsabilita,
                                'nome_ruolo_attivita' => $nome_ruolo_attivita,
                                'ruoliattivita_id' => $ruoliattivita_id);
            
        }

        return $result;

    }

    public function getElementoProcedura($id_procedura, $id_bpmn){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    entproc.kpentitaprocedureid id,
                    entproc.kp_nome_entita kp_nome_entita,
                    entproc.kp_bpmn_id kp_bpmn_id,
                    entproc.kp_tipo_entita_bpmn kp_tipo_entita_bpmn,
                    entproc.kp_relazionato_a_id kp_relazionato_a_id,
                    entproc.kp_valore_aggiunto kp_valore_aggiunto,
                    entproc.kp_attivita_dvr kp_attivita_dvr,
                    entproc.description description
                    FROM {$table_prefix}_kpentitaprocedure entproc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                    WHERE ent.deleted = 0 AND entproc.kp_bpmn_id = '".$id_bpmn."' AND entproc.kp_procedura = ".$id_procedura;
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;
            
            $id = $adb->query_result($result_query, 0, 'id');
			$id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, 0, 'kp_nome_entita');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $nome = $this->encodeString($nome);

            $tipo_entita_bpmn = $adb->query_result($result_query, 0, 'kp_tipo_entita_bpmn');
			$tipo_entita_bpmn = html_entity_decode(strip_tags($tipo_entita_bpmn), ENT_QUOTES, $default_charset);

            $relazionato_a_id = $adb->query_result($result_query, 0, 'kp_relazionato_a_id');
			$relazionato_a_id = html_entity_decode(strip_tags($relazionato_a_id), ENT_QUOTES, $default_charset);

            if($relazionato_a_id != null && $relazionato_a_id != "" && $relazionato_a_id != 0){
                $dati_elemento_relazionato = $this->getElementoRelazionato($relazionato_a_id);

                if( $dati_elemento_relazionato["esiste"] ){
                    $relazionato_a_nome = $dati_elemento_relazionato["nome"];
                }
                else{
                    $dati_elemento_relazionato = "";
                    $relazionato_a_id = 0;
                }

            }
            else{
                $relazionato_a_nome = "";
                $relazionato_a_id = 0;
            }

            $tipo_attivita_id = $adb->query_result($result_query, 0, 'kp_attivita_dvr');
            $tipo_attivita_id = html_entity_decode(strip_tags($tipo_attivita_id), ENT_QUOTES, $default_charset);

            if($tipo_attivita_id != null && $tipo_attivita_id != "" && $tipo_attivita_id != 0){

                $focus_tipo_attivita = CRMEntity::getInstance('KpAttivitaDVR');
                $focus_tipo_attivita->retrieve_entity_info($tipo_attivita_id, "KpAttivitaDVR", $dieOnError=false); 

                $nome_tipo_attivita = $focus_tipo_attivita->column_fields["kp_nome_attivita"];
                $nome_tipo_attivita = html_entity_decode(strip_tags($nome_tipo_attivita), ENT_QUOTES, $default_charset);
                
                $nome_tipo_attivita = $this->encodeString($nome_tipo_attivita);

            }
            else{
                $nome_tipo_attivita = "";
                $tipo_attivita_id = 0;
            }

            $descrizione = $adb->query_result($result_query, 0, 'description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);

            $descrizione = $this->encodeString($descrizione);
            
            $valore_aggiunto = $adb->query_result($result_query, 0, 'kp_valore_aggiunto');
            $valore_aggiunto = html_entity_decode(strip_tags($valore_aggiunto), ENT_QUOTES, $default_charset);
            if($valore_aggiunto == null){
                $valore_aggiunto = "";
            }
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";
            $tipo_entita_bpmn = "";
            $relazionato_a_id = "";
            $descrizione = "";
            $relazionato_a_nome = "";
            $valore_aggiunto = "";
            $nome_tipo_attivita = "";
            $tipo_attivita_id = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $id,
                        "nome" => $nome,
                        "tipo_entita_bpmn" => $tipo_entita_bpmn,
                        "relazionato_a_id" => $relazionato_a_id,
                        "relazionato_a_nome" => $relazionato_a_nome,
                        "tipo_attivita_id" => $tipo_attivita_id,
                        "nome_tipo_attivita" => $nome_tipo_attivita,
                        "valore_aggiunto" => $valore_aggiunto,
                        "descrizione" => $descrizione);

        return $result;

    }

    public function getElementoRelazionato($id){
        global $adb, $table_prefix, $default_charset;

        $result = "";
        
        $query = "SELECT
                    proc.kpprocedureid kpprocedureid,
                    proc.kp_nome_procedura kp_nome_procedura
                    FROM {$table_prefix}_kpprocedure proc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = proc.kpprocedureid
                    WHERE ent.deleted = 0 AND proc.kp_stato_procedura = 'Attivo' AND proc.kpprocedureid = ".$id;
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;
            
            $id = $adb->query_result($result_query, 0, 'kpprocedureid');
			$id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, 0, 'kp_nome_procedura');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $nome = $this->encodeString($nome);

        }
        else{

            $esiste = false;
            
            $id = 0;
            $nome = "";

        }

        $result = array("esiste" => $esiste,
                        "id" => $id,
                        "nome" => $nome);

        return $result;

    }

	private function convertSVGtoPNG($file_svg, $percorso_salvataggio){
        global $adb, $table_prefix, $default_charset, $current_user;
		
        if( file_exists($file_svg) ){ 

            $svg = "";

            $file_svg = fopen($file_svg, "r");

            while( !feof($file_svg) ){

                $svg .= fgets($file_svg);

            }

            fclose($file_svg);

            $image = new IMagick();  
            $image->setBackgroundColor(new ImagickPixel('transparent'));  
            $image->readImageBlob($svg);  
            $image->setImageFormat("png32");

            $file_png = $percorso_salvataggio;

            if( file_exists($file_png) ){ 
                @unlink($file_png);
            }
			
            $image->writeImage($file_png);
            $image->clear();
            $image->destroy();
			
        }

	}

	private function setFileSVG($crmid){
		global $adb, $table_prefix, $default_charset, $current_user;
		
		$focus_processo = CRMEntity::getInstance('KpProcedure');
		$focus_processo->retrieve_entity_info($crmid, "KpProcedure", $dieOnError=false); 

		$svg = $focus_processo->column_fields["kp_bpmn_svg"];

        if( $svg != "" ){

            if( !is_dir(__DIR__."/../CustomViews/KpBPMNcreator/svg/") ){
                mkdir(__DIR__."/../CustomViews/KpBPMNcreator/svg/", 0777);
                chown(__DIR__."/../CustomViews/KpBPMNcreator/svg/", "www-data");
                chgrp(__DIR__."/../CustomViews/KpBPMNcreator/svg/", "www-data");
			}
			else{
				chmod(__DIR__."/../CustomViews/KpBPMNcreator/svg/", 0777);
				chown(__DIR__."/../CustomViews/KpBPMNcreator/svg/", "www-data");
                chgrp(__DIR__."/../CustomViews/KpBPMNcreator/svg/", "www-data");
			}

            $filename = __DIR__."/../CustomViews/KpBPMNcreator/svg/".$crmid.".svg";

            if(file_exists($filename)){ 
                @unlink($filename);
            } 

            file_put_contents($filename, $svg);

        }

	}
	
	public function getConfigurazioniIdStatici(){
        global $adb, $table_prefix, $default_charset, $current_user, $dbconfig;

        $result = array();

        $query = "SELECT 
                    * 
                    FROM information_schema.tables
                    WHERE table_schema = '".$dbconfig['db_name']."' AND table_name = 'kp_settings_config_id_statici'";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);
            
        if( $num_result > 0 ){

            $query = "SELECT 
                        * 
                        FROM kp_settings_config_id_statici";

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);

            for( $i=0; $i < $num_result; $i++ ){

                $id_configurazione = $adb->query_result($result_query, $i, 'id_configurazione');
                $id_configurazione = html_entity_decode(strip_tags($id_configurazione), ENT_QUOTES,$default_charset);
                
                $nome_area_configurazione = $adb->query_result($result_query, $i, 'nome_area_configurazione');
                $nome_area_configurazione = html_entity_decode(strip_tags($nome_area_configurazione), ENT_QUOTES,$default_charset);

                $nome_configurazione = $adb->query_result($result_query, $i, 'nome_configurazione');
                $nome_configurazione = html_entity_decode(strip_tags($nome_configurazione), ENT_QUOTES,$default_charset);

                $valore = $adb->query_result($result_query, $i, 'valore');
                $valore = html_entity_decode(strip_tags($valore), ENT_QUOTES,$default_charset);

                $chiave = $nome_area_configurazione." - ".$nome_configurazione;

                $result[$chiave] = array("nome_area_configurazione" => $nome_area_configurazione,
										"nome_configurazione" => $nome_configurazione,
										"valore" => $valore);

            }

        }

        return $result;

    }
	
	public function getPdfProcedura($record, $id_pdf = 0){
		global $adb, $table_prefix, $default_charset, $current_user;

		require_once(__DIR__."/../../../modules/PDFMaker/InventoryPDF.php");
        require_once(__DIR__."/../../../include/mpdf/mpdf.php"); 

        //Tolto perche eseguito automaticamente dal PDFMaker
        //$svg = $this->getImmagineProcesso($record);

		if( $id_pdf == 0 ){
			$id_statici = $this->getConfigurazioniIdStatici();

			$id_statico_templateid = $id_statici["PDF Maker - Template Stampa Processi"];
			if( $id_statico_templateid["valore"] == "" && $id_statico_templateid["valore"] == 0 ){
				return;
			}

			$id_pdf = $id_statico_templateid["valore"];
		}
		
		$templateid = $id_pdf;
        $relmodule = 'KpProcedure';
        $language = 'it_it';
		$record = $record;
		
		$focus = CRMEntity::getInstance($relmodule);
        $focus->retrieve_entity_info($record,$relmodule);
        $focus->id = $record;

        $PDFContents = array();
        $TemplateContent = array();

        $PDFContent = PDFContent::getInstance($templateid, $relmodule, $focus, $language); 
        $pdf_content = $PDFContent->getContent();    

        $header_html = $pdf_content["header"];
        $body_html = $pdf_content["body"];
		$footer_html = $pdf_content["footer"];
		
        /*//Tolto perche eseguito automaticamente dal PDFMaker
        $tabella_rischi = $this->getTemplateRischiProcessoPDF($record);
        $tabella_documenti = $this->getTemplateDocumentiAttiviProcessoPDF($record);
        
        $body_html = str_replace("#SVG#", $svg, $body_html);
        $body_html = str_replace("#TABELLA_RISCHI#", $tabella_rischi, $body_html);
        $body_html = str_replace("#TABELLA_DOCUMENTI#", $tabella_documenti, $body_html);*/
	
        $Settings = $PDFContent->getSettings();
        if($name==""){    
            $name = $PDFContent->getFilename();
        }
                    
        if ($Settings["orientation"] == "landscape"){
            $format = $Settings["format"]."-L";
        }
        else{
            $format = $Settings["format"];
        }

        $ListViewBlocks = array();
        if(strpos($body_html,"#LISTVIEWBLOCK_START#") !== false && strpos($body_html,"#LISTVIEWBLOCK_END#") !== false){
            preg_match_all("|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU", $body_html, $ListViewBlocks, PREG_PATTERN_ORDER);
        }		
        
        if (count($ListViewBlocks) > 0){
                        
            $TemplateContent[$templateid] = $pdf_content;
            $TemplateSettings[$templateid] = $Settings;
                        
            $num_listview_blocks = count($ListViewBlocks[0]);
            for($i=0; $i<$num_listview_blocks; $i++){
                $ListViewBlock[$templateid][$i] = $ListViewBlocks[0][$i];
                $ListViewBlockContent[$templateid][$i][$record][] = $ListViewBlocks[1][$i];
            }   
        }
        else{
            if (!isset($mpdf)){           
                $mpdf=new mPDF('',$format,'','Arial',$Settings["margin_left"],$Settings["margin_right"],0,0,$Settings["margin_top"],$Settings["margin_bottom"]);  
                $mpdf->SetAutoFont();
                @$mpdf->SetHTMLHeader($header_html);
            }
            else{
                @$mpdf->SetHTMLHeader($header_html);
                @$mpdf->WriteHTML('<pagebreak sheet-size="'.$format.'" margin-left="'.$Settings["margin_left"].'mm" margin-right="'.$Settings["margin_right"].'mm" margin-top="0mm" margin-bottom="0mm" margin-header="'.$Settings["margin_top"].'mm" margin-footer="'.$Settings["margin_bottom"].'mm" />');
            }     
            @$mpdf->SetHTMLFooter($footer_html);
            @$mpdf->WriteHTML($body_html);
        }
                
        if (count($TemplateContent)> 0){
            
            foreach($TemplateContent AS $templateid => $TContent){
                $header_html = $TContent["header"];
                $body_html = $TContent["body"];
                $footer_html = $TContent["footer"];
                    
                $Settings = $TemplateSettings[$templateid];
                    
                foreach($ListViewBlock[$templateid] AS $id => $text){
                    $replace = "";
                    foreach($Records as $record){  
                        $replace .= implode("",$ListViewBlockContent[$templateid][$id][$record]);   
                    }
                        
                    $body_html = str_replace($text,$replace,$body_html);
                }
                    
                if ($Settings["orientation"] == "landscape"){
                    $format = $Settings["format"]."-L";
                }
                else{
                    $format = $Settings["format"];
                }
                    
                    
                if (!isset($mpdf)){           
                    $mpdf=new mPDF('',$format,'','Arial',$Settings["margin_left"],$Settings["margin_right"],0,0,$Settings["margin_top"],$Settings["margin_bottom"]);  
                    $mpdf->SetAutoFont();
                    @$mpdf->SetHTMLHeader($header_html);
                }
                else{
                    @$mpdf->SetHTMLHeader($header_html);
                    @$mpdf->WriteHTML('<pagebreak sheet-size="'.$format.'" margin-left="'.$Settings["margin_left"].'mm" margin-right="'.$Settings["margin_right"].'mm" margin-top="0mm" margin-bottom="0mm" margin-header="'.$Settings["margin_top"].'mm" margin-footer="'.$Settings["margin_bottom"].'mm" />');
                }     
                @$mpdf->SetHTMLFooter($footer_html);
                @$mpdf->WriteHTML($body_html);
            }
		}
		
		$mpdf->Output('cache/'.$name.'.pdf');

		@ob_clean();
		header('Content-Type: application/pdf');
		header("Content-length: ".filesize("./cache/$name.pdf"));
		header("Cache-Control: private");
		header("Content-Disposition: attachment; filename=$name.pdf");
		header("Content-Description: PHP Generated Data");
		echo fread(fopen("./cache/$name.pdf", "r"),filesize("./cache/$name.pdf"));
					
		@unlink("cache/$name.pdf");

		@unlink($file_png);
		
	}

	public function getTemplateRischiProcessoPDF($processo){
		global $adb, $table_prefix, $default_charset, $current_user;

		$righe = 0;

		$table = "<table border='1' cellpadding='3' cellspacing='0' style='width:100%; border-collapse:collapse; font-size: 12px; margin: 0;'>";
        $table .= "<thead>";
        $table .= "<tr style='background-color: #DBEDFF;'>";

        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Attivita'</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Descrizione Rischi</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Tip. Rischi</th>";

        $table .= "</tr>";
        $table .= "</thead>";

		$table .= "<tbody>";

		$lista_task = $this->getElementiProcedura($processo, array("only_task" => true));

		//print_r($lista_task);die;

        foreach( $lista_task as $task ){

			$prima_riga = true;
		
			$lista_rischi_qualita = $this->getRischiQualitaElementoProcedura($task["id"], array());

			$lista_rischi_privacy = $this->getRischiPrivacyElementoProcedura($task["id"], array());

			$lista_rischi_ricurezza = $this->getRischiSicurezzaElementoProcedura($task["id"], array());

			$lista_rischi = array_merge($lista_rischi_qualita, $lista_rischi_privacy, $lista_rischi_ricurezza);
			
			//print_r($lista_rischi); die;

			$tot_rischi = count($lista_rischi);

			foreach( $lista_rischi as $rischio ){

				$righe++;

				$table .= "<tr id='".$task["id"]."_".$rischio["id"]."'>";

				if($prima_riga){

					$prima_riga = false;

					$table .= "<td rowspan='".count($lista_rischi)."' style='vertical-align: top; padding-left: 10px; padding-right: 10px;'>";
                    $table .= "<b><span style='vertical-align: top;'>".$task["nome"]."</span></b>";
					$table .= "</td>";
						
				}

				$table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<b><span style='vertical-align: middle;' >".$rischio["nome"]."</span></b>";
				$table .= "</td>";

				$table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<span style='vertical-align: middle;' >".$rischio["tipo"]."</span>";
				$table .= "</td>";

				$table .= "</tr>";

			}

		}

		$table .= "</tbody>";

        $table .= "</table>";

		if( $righe == 0 ){
            $table = "<p>Nessun rischio relazionato<p>";
		}
		
		//print_r($table);die;

        return $table;

	}

	public function getTemplateDocumentiAttiviProcessoPDF($processo){
		global $adb, $table_prefix, $default_charset, $current_user;

		$righe = 0;

		$table = "<table border='1' cellpadding='3' cellspacing='0' style='width:100%; border-collapse:collapse; font-size: 12px; margin: 0;'>";
        $table .= "<thead>";
        $table .= "<tr style='background-color: #DBEDFF;'>";

        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Attivita'</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Codice Doc.</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Nome Documento</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Data Creazione</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Numero Rev.</th>";
        $table .= "<th style='text-align: left; padding-left: 10px; padding-right: 10px;'>Data Rev.</th>";

        $table .= "</tr>";
        $table .= "</thead>";

		$table .= "<tbody>";

		$lista_task = $this->getElementiProcedura($processo, array("only_task" => true));

		//print_r($lista_task);die;

        foreach( $lista_task as $task ){

			$prima_riga = true;

			$lista_documento = $this->getDocumentiElementoProcedura($task["id"], array("only_approvati" => false));

			foreach( $lista_documento as $documento ){

				$righe++;

				$table .= "<tr id='".$task["id"]."_".$documento["notesid"]."'>";

				if($prima_riga){

					$prima_riga = false;

					$table .= "<td rowspan='".count($lista_documento)."' style='vertical-align: top; padding-left: 10px; padding-right: 10px;'>";
                    $table .= "<b><span style='vertical-align: top;'>".$task["nome"]."</span></b>";
					$table .= "</td>";
						
                }
                
                $table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<b><span style='vertical-align: middle;' >".$documento["codice_documento"]."</span></b>";
				$table .= "</td>";

				$table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<b><span style='vertical-align: middle;' >".$documento["title"]."</span></b>";
				$table .= "</td>";

				$table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<span style='vertical-align: middle;' >".$documento["data_documento"]."</span>";
				$table .= "</td>";

				$table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<span style='vertical-align: middle;' >".$documento["num_revisione"]."</span>";
                $table .= "</td>";
                
                $table .= "<td style='vertical-align: middle; padding-left: 10px; padding-right: 10px;'>";
				$table .= "<span style='vertical-align: middle;' >".$documento["data_revisione"]."</span>";
                $table .= "</td>";
                
				$table .= "</tr>";

			}

		}

		$table .= "</tbody>";

        $table .= "</table>";

		if( $righe == 0 ){
            $table = "<p>Nessun documento relazionato<p>";
		}
		
		//print_r($table);die;

        return $table;

	}

	public function getElementiProcedura($id, $filtro){
		global $adb, $table_prefix, $default_charset, $current_user;

		$result = array();

        $query = "SELECT 
                    entproc.kpentitaprocedureid kpentitaprocedureid,
                    entproc.kp_nome_entita kp_nome_entita,
                    entproc.kp_procedura kp_procedura,
                    entproc.kp_bpmn_id kp_bpmn_id,
                    entproc.kp_tipo_entita_bpmn kp_tipo_entita_bpmn,
                    entproc.kp_relazionato_a_id kp_relazionato_a_id,
                    entproc.kp_valore_aggiunto kp_valore_aggiunto,
                    entproc.description description,
                    proc.kpprocedureid kpprocedureid,
                    proc.kp_numero_procedura kp_numero_procedura,
                    proc.kp_nome_procedura kp_nome_procedura,
                    proc.kp_stato_procedura kp_stato_procedura
                    FROM {$table_prefix}_kpentitaprocedure entproc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                    LEFT JOIN {$table_prefix}_kpprocedure proc ON proc.kpprocedureid = entproc.kp_relazionato_a_id
                    LEFT JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = proc.kpprocedureid AND ent2.deleted = 0
                    WHERE ent.deleted = 0 AND entproc.kp_procedura = ".$id;

        if( $filtro["only_sottoprocesso"] ){

            $query .= " AND proc.kpprocedureid IS NOT NULL AND proc.kpprocedureid != 0 AND proc.kpprocedureid != '' AND proc.kp_stato_procedura = 'Attivo'";

        }

        if( $filtro["only_task"] ){

            $query .= " AND entproc.kp_tipo_entita_bpmn IN ('task', 'sendTask', 'receiveTask', 'userTask', 'manualTask', 'businessRuleTask', 'serviceTask', 'scriptTask', 'callActivity')";

        }

        $query .= " ORDER BY entproc.kp_order ASC, entproc.kpentitaprocedureid ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'kpentitaprocedureid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'kp_nome_entita');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $nome = $this->encodeString($nome);

            $bpmn_id = $adb->query_result($result_query, $i, 'kp_bpmn_id');
            $bpmn_id = html_entity_decode(strip_tags($bpmn_id), ENT_QUOTES, $default_charset);

            $tipo_entita_bpmn = $adb->query_result($result_query, $i, 'kp_tipo_entita_bpmn');
            $tipo_entita_bpmn = html_entity_decode(strip_tags($tipo_entita_bpmn), ENT_QUOTES, $default_charset);

            $lista_sottoprocessi = array();

            $stato_procedura = $adb->query_result($result_query, $i, 'kp_stato_procedura');
            $stato_procedura = html_entity_decode(strip_tags($stato_procedura), ENT_QUOTES, $default_charset);

            $numero_procedura = $adb->query_result($result_query, $i, 'kp_numero_procedura');
            $numero_procedura = html_entity_decode(strip_tags($numero_procedura), ENT_QUOTES,$default_charset);
            if($numero_procedura == null){
                $numero_procedura = "";
            }
            $numero_procedura = trim($numero_procedura);

            $nome_procedura = $adb->query_result($result_query, $i, 'kp_nome_procedura');
            $nome_procedura = html_entity_decode(strip_tags($nome_procedura), ENT_QUOTES, $default_charset);
            if($nome_procedura == null){
                $nome_procedura = "";
            }

            if( $numero_procedura != null && $numero_procedura != '' ){
                $nome_procedura = $numero_procedura." - ".$nome_procedura;
            } 

            $procedureid = $adb->query_result($result_query, $i, 'kpprocedureid');
            $procedureid = html_entity_decode(strip_tags($procedureid), ENT_QUOTES, $default_charset);
            if($procedureid == null || procedureid == ""){
                $procedureid = 0;      
            }
            else{

                $lista_sottoprocessi = $this->getElementiProcedura($procedureid, array("only_sottoprocesso" => true));  

            }

            if( $procedureid != 0 && $numero_procedura != null && $numero_procedura != '' ){
                $nome = $numero_procedura." - ".$nome;
            }

            $nome = $this->encodeString($nome);
            $nome_procedura = $this->encodeString($nome_procedura);

            $valore_aggiunto = $adb->query_result($result_query, $i, 'kp_valore_aggiunto');
            $valore_aggiunto = html_entity_decode(strip_tags($valore_aggiunto), ENT_QUOTES, $default_charset);
            if($valore_aggiunto == null){
                $valore_aggiunto = "";
            }

            $description = $adb->query_result($result_query, $i, 'description');
            $description = html_entity_decode(strip_tags($description), ENT_QUOTES, $default_charset);

            $description = $this->encodeString($description);

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "bpmn_id" => $bpmn_id,
                                "tipo_entita_bpmn" => $tipo_entita_bpmn,
                                "valore_aggiunto" => $valore_aggiunto,
                                "procedureid" => $procedureid,
                                "nome_procedura" => $nome_procedura,
                                "description" => $description,
                                "lista_sottoprocessi" => $lista_sottoprocessi);

        }

        return $result;

	}

	private function encodeString($string){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $string = str_replace("è", "e", $string);
        $string = str_replace("é", "e", $string);
        $string = str_replace("à", "a", $string);
        $string = str_replace("ò", "o", $string);
        $string = str_replace("ù", "u", $string);
        $string = str_replace("ì", "i", $string);
        
        $string = str_replace("&agrave;", "a", $string);
        $string = str_replace("&Agrave;", "A", $string);
        $string = str_replace("&acute;", "a", $string);
        $string = str_replace("&Aacute;", "A", $string);
        $string = str_replace("&Egrave;", "E", $string);
        $string = str_replace("&egrave;", "e", $string);
        $string = str_replace("&Eacute;", "E", $string);
        $string = str_replace("&eacute;", "e", $string);
        $string = str_replace("&ograve;", "o", $string);
        $string = str_replace("&Ograve;", "O", $string);
        $string = str_replace("&Ugrave;", "U", $string);
        $string = str_replace("&ugrave;", "u", $string);
        $string = str_replace("&Igrave;", "I", $string);
        $string = str_replace("&igrave;", "i", $string);
        $string = str_replace("&", "e", $string);

        return $string;

	}
	
	public function getRischiQualitaElementoProcedura($id, $filtro = array()){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischi.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kprischiqualita rischi ON rischi.kprischiqualitaid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpRischiQualita' AND rel.relmodule = 'KpEntitaProcedure' AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischi.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischiqualita rischi ON rischi.kprischiqualitaid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpRischiQualita' AND rel.module = 'KpEntitaProcedure' AND rel.crmid = ".$id.")) AS t";
        
        $condizione = "";

        if($filtro["nome_rischio"] != ""){
            if($condizione == ""){
                $condizione .= " WHERE t.nome like '%".$filtro["nome_rischio"]."%'";
            }
            else{
                $condizione .= " AND t.nome like '%".$filtro["nome_rischio"]."%'";
            }    
        }

        if($condizione != ""){
            $query .= $condizione;
        }

        $query .= " ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'tipo' => 'Qualita');
            
        }

        return $result;

	}
	
	public function getRischiPrivacyElementoProcedura($id, $filtro = array()){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischi.kp_nome_minaccia nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy rischi ON rischi.kpminacceprivacyid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpMinaccePrivacy' AND rel.relmodule = 'KpEntitaProcedure' AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischi.kp_nome_minaccia nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy rischi ON rischi.kpminacceprivacyid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpMinaccePrivacy' AND rel.module = 'KpEntitaProcedure' AND rel.crmid = ".$id.")) AS t";
        
        $condizione = "";

        if($filtro["nome_rischio"] != ""){
            if($condizione == ""){
                $condizione .= " WHERE t.nome like '%".$filtro["nome_rischio"]."%'";
            }
            else{
                $condizione .= " AND t.nome like '%".$filtro["nome_rischio"]."%'";
            }    
        }

        if($condizione != ""){
            $query .= $condizione;
        }

        $query .= " ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'tipo' => 'GDPR');
            
        }

        return $result;

	}
	
	public function getRischiSicurezzaElementoProcedura($id, $filtro = array()){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischi.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kprischidvr rischi ON rischi.kprischidvrid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpRischiDVR' AND rel.relmodule = 'KpEntitaProcedure' AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischi.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischidvr rischi ON rischi.kprischidvrid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpRischiDVR' AND rel.module = 'KpEntitaProcedure' AND rel.crmid = ".$id.")) AS t";
        
        $condizione = "";

        if($filtro["nome_rischio"] != ""){
            if($condizione == ""){
                $condizione .= " WHERE t.nome like '%".$filtro["nome_rischio"]."%'";
            }
            else{
                $condizione .= " AND t.nome like '%".$filtro["nome_rischio"]."%'";
            }    
        }

        if($condizione != ""){
            $query .= $condizione;
        }

        $query .= " ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'tipo' => 'Sicurezza');
            
        }

        return $result;

	}
	
	public function getDocumentiElementoProcedura($id, $filtro = array()){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT attac.attachmentsid attachmentsid, 
                    attac.name name, 
                    attac.path path, 
                    notes.title title, 
                    notes.notesid notesid, 
                    notes.folderid cartella_id,
                    notes.kp_data_documento data_documento,
				    date(ent.createdtime) data_creazione,
                    ent.createdtime createdtime, 
                    ent.modifiedtime modifiedtime,
                    notes.filelocationtype filelocationtype,
					notes.kp_num_revisione num_revisione,
                    notes.kp_codice_documento codice_documento
                    FROM {$table_prefix}_notes notes 
                    INNER JOIN {$table_prefix}_notescf notescf ON notescf.notesid = notes.notesid 
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_senotesrel senote ON senote.notesid = notes.notesid
                    LEFT JOIN {$table_prefix}_seattachmentsrel seattac ON seattac.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_attachments attac ON attac.attachmentsid = seattac.attachmentsid 
                    WHERE ent.deleted = 0 AND senote.crmid = ".$id;
                        
        if($filtro["nome_documento"] != ""){
            $query .= " and notes.title like '%".$filtro["nome_documento"]."%'";
        }

        $query .= " ORDER BY ent.createdtime DESC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $title = $adb->query_result($result_query, $i, 'title');
            $title = html_entity_decode(strip_tags($title), ENT_QUOTES,$default_charset);
            
            $notesid = $adb->query_result($result_query, $i, 'notesid');
            $notesid = html_entity_decode(strip_tags($notesid), ENT_QUOTES,$default_charset);

            $focus_documento = CRMEntity::getInstance('Documents');
            $focus_documento->retrieve_entity_info($notesid, "Documents", $dieOnError=false); 
            $dati_approvazione = $focus_documento->checkStatoApprovazione();
            if( $dati_approvazione["richiede_approvazione"] == '1' && $dati_approvazione["stato_avanzamento"] != "Approvato" ){

				if( array_key_exists('only_approvati', $filtro) && $filtro["only_approvati"] ){
					continue;
				}
				elseif( !array_key_exists('only_approvati', $filtro) ){
					continue;
				}

            }
            
            $filelocationtype = $adb->query_result($result_query, $i, 'filelocationtype');
			$filelocationtype = html_entity_decode(strip_tags($filelocationtype), ENT_QUOTES,$default_charset);
			if($filelocationtype == "E"){
				$attachmentsid = 0;

				$tipo_download = "Esterno";
			}
			else{
				$attachmentsid = $adb->query_result($result_query, $i, 'attachmentsid');
				$attachmentsid = html_entity_decode(strip_tags($attachmentsid), ENT_QUOTES,$default_charset);
				
				$tipo_download = "Interno";
			}
            
            $createdtime = $adb->query_result($result_query, $i, 'createdtime');
            $createdtime = html_entity_decode(strip_tags($createdtime), ENT_QUOTES,$default_charset);
            
            $modifiedtime = $adb->query_result($result_query, $i, 'modifiedtime');
            $modifiedtime = html_entity_decode(strip_tags($modifiedtime), ENT_QUOTES,$default_charset);

            $data_documento = $adb->query_result($result_query, $i, 'data_documento');
            $data_documento = html_entity_decode(strip_tags($data_documento), ENT_QUOTES,$default_charset);
            if($data_documento != null && $data_documento != "" && $data_documento != "0000-00-00"){
                list($anno, $mese, $giorno) = explode("-", $data_documento);
                $data_documento_inv = date("d/m/Y", mktime(0, 0, 0, $mese, $giorno, $anno));
            }
            else{
                $data_documento = $adb->query_result($result_query, $i, 'data_creazione');
                $data_documento = html_entity_decode(strip_tags($data_documento), ENT_QUOTES,$default_charset);
                if($data_documento != null && $data_documento != "" && $data_documento != "0000-00-00"){
                    list($anno, $mese, $giorno) = explode("-", $data_documento);
                    $data_documento_inv = date("d/m/Y", mktime(0, 0, 0, $mese, $giorno, $anno));
                }
			}
			
			$num_revisione = $adb->query_result($result_query, $i, 'num_revisione');
			$num_revisione = html_entity_decode(strip_tags($num_revisione), ENT_QUOTES,$default_charset);
			if( $num_revisione == null || $num_revisione == '' ){
				$num_revisione = 0;
			}

			if( $dati_approvazione["stato_avanzamento"] != "Approvato" ){
				$approvato = "No";
			}
			else{
				$approvato = "Sì";
            }
            
            $codice_documento = $adb->query_result($result_query, $i, 'codice_documento');
			$codice_documento = html_entity_decode(strip_tags($codice_documento), ENT_QUOTES,$default_charset);
			if( $codice_documento == null ){
				$codice_documento = "";
            }
            
            $focus_documento = CRMEntity::getInstance('Documents'); 
            $focus_documento->retrieve_entity_info($notesid, "Documents");
            $id_ultima_revisione = $focus_documento->getUltimaRevisione();

            if( $id_ultima_revisione["esiste"] ){

                $focus_revisione = CRMEntity::getInstance('KpRevisioniDocumenti'); 
                $focus_revisione->retrieve_entity_info($id_ultima_revisione["id"], "KpRevisioniDocumenti");

                $data_revisione = $focus_revisione->column_fields['kp_data_revisione'];
                if($data_revisione != null && $data_revisione != "" && $data_revisione != "0000-00-00"){
                    list($anno, $mese, $giorno) = explode("-", $data_revisione);
                    $data_revisione = date("d/m/Y", mktime(0, 0, 0, $mese, $giorno, $anno));
                }
                else{
                    $data_revisione = "";
                }

            }
            else{
                $data_revisione = "";
            }
            
            $result[] = array('notesid' => $notesid,
                                'attachmentsid' => $attachmentsid,
                                'title' => $title,
                                'createdtime' => $createdtime,
                                'modifiedtime' => $modifiedtime,
								'data_documento' => $data_documento_inv,
								'num_revisione' => $num_revisione,
                                'approvato' => $approvato,
                                'codice_documento' => $codice_documento,
                                'data_revisione' => $data_revisione,
                                'tipo' => $tipo_download);
            
        }

        return $result;

	}
	
	public function getRischiQualitaAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();
        
        $lista_elementi_gia_relazionati = "(";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischio.kp_nome_rischio nome_rischio
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kprischiqualita rischio ON rischio.kprischiqualitaid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpRischiQualita' AND rel.relmodule = 'KpEntitaProcedure' AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischio.kp_nome_rischio nome_rischio
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischiqualita rischio ON rischio.kprischiqualitaid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpRischiQualita' AND rel.module = 'KpEntitaProcedure' AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $rischio = $adb->query_result($result_query, $i, 'rischio');
            $rischio = html_entity_decode(strip_tags($rischio), ENT_QUOTES,$default_charset);

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $rischio;

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$rischio;

            }

        }

        $lista_elementi_gia_relazionati .= ")";
        
        $query = "SELECT
                    rischio.kprischiqualitaid id,
                    rischio.kp_nome_rischio nome
                    FROM {$table_prefix}_kprischiqualita rischio
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rischio.kprischiqualitaid
                    WHERE ent.deleted = 0";

        if($lista_elementi_gia_relazionati != "()"){

            $query .= " AND rischio.kprischiqualitaid NOT IN ".$lista_elementi_gia_relazionati;

        }
                        
        if($filtro["nome_rischio"] != ""){
            $query .= " AND rischio.kp_nome_rischio like '%".$filtro["nome_rischio"]."%'";
        }

        $query .= " ORDER BY rischio.kp_nome_rischio ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome);
            
        }

        return $result;

    }

    public function getRischiPrivacyAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();
        
        $lista_elementi_gia_relazionati = "(";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischio.kp_nome_minaccia nome_rischio
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy rischio ON rischio.kpminacceprivacyid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpMinaccePrivacy' AND rel.relmodule = 'KpEntitaProcedure' AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischio.kp_nome_minaccia nome_rischio
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy rischio ON rischio.kpminacceprivacyid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpMinaccePrivacy' AND rel.module = 'KpEntitaProcedure' AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $rischio = $adb->query_result($result_query, $i, 'rischio');
            $rischio = html_entity_decode(strip_tags($rischio), ENT_QUOTES,$default_charset);

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $rischio;

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$rischio;

            }

        }

        $lista_elementi_gia_relazionati .= ")";
        
        $query = "SELECT
                    rischio.kpminacceprivacyid id,
                    rischio.kp_nome_minaccia nome
                    FROM {$table_prefix}_kpminacceprivacy rischio
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rischio.kpminacceprivacyid
                    WHERE ent.deleted = 0";

        if($lista_elementi_gia_relazionati != "()"){

            $query .= " AND rischio.kpminacceprivacyid NOT IN ".$lista_elementi_gia_relazionati;

        }
                        
        if($filtro["nome_rischio"] != ""){
            $query .= " AND rischio.kp_nome_minaccia like '%".$filtro["nome_rischio"]."%'";
        }

        $query .= " ORDER BY rischio.kp_nome_minaccia ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome);
            
        }

        return $result;

    }

    public function getRischiSicurezzaAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();
        
        $lista_elementi_gia_relazionati = "(";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischio.kp_nome_rischio nome_rischio
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kprischidvr rischio ON rischio.kprischidvrid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpRischiDVR' AND rel.relmodule = 'KpEntitaProcedure' AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischio.kp_nome_rischio nome_rischio
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischidvr rischio ON rischio.kprischidvrid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpRischiDVR' AND rel.module = 'KpEntitaProcedure' AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $rischio = $adb->query_result($result_query, $i, 'rischio');
            $rischio = html_entity_decode(strip_tags($rischio), ENT_QUOTES,$default_charset);

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $rischio;

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$rischio;

            }

        }

        $lista_elementi_gia_relazionati .= ")";
        
        $query = "SELECT
                    rischio.kprischidvrid id,
                    rischio.kp_nome_rischio nome
                    FROM {$table_prefix}_kprischidvr rischio
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rischio.kprischidvrid
                    WHERE ent.deleted = 0";

        if($lista_elementi_gia_relazionati != "()"){

            $query .= " AND rischio.kprischidvrid NOT IN ".$lista_elementi_gia_relazionati;

        }
                        
        if($filtro["nome_rischio"] != ""){
            $query .= " AND rischio.kp_nome_rischio like '%".$filtro["nome_rischio"]."%'";
        }

        $query .= " ORDER BY rischio.kp_nome_rischio ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome);
            
        }

        return $result;

	}

	public function mergePDF($path_pdf_risultante, $nome_pdf_risultante, $path_temp){
        global $adb, $table_prefix, $default_charset, $current_user;
        
        if( $path_temp != "" ){

            $command = "python3 ".__DIR__."/KpMergePdf.py ".$path_pdf_risultante." ".$nome_pdf_risultante." ".$path_temp;  
            exec($command, $out, $status);
            //print_r($command);die;
       
        }

	}
	
	public function setNomeFile ($str = ''){
        global $adb, $table_prefix, $default_charset, $current_user;

        $str = strip_tags($str); 
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        //$str = strtolower($str);
        $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace(' ', '_', $str);
        $str = rawurlencode($str);
        $str = str_replace('%', '_', $str);

        return $str;

    }
	
	public function getPdfProceduraAll($record, $filtro_azienda, $filtro_stabilimento, $id_pdf = 0){
        global $adb, $table_prefix, $default_charset, $current_user;

        $path_temp = date("YmdHis")."_".rand(0 , 100000);

        $this->sequenza_stampa = "0000001";

        $nome_pdf_risultante = $this->setPDFProceduraRicorsivo($record, $path_temp, $id_pdf);

        $path_pdf_risultante = __DIR__."/../CustomViews/KpBPMNcreator/temp/";

        $nome_pdf_risultante = $this->setNomeFile( $nome_pdf_risultante );

        $this->mergePDF($path_pdf_risultante, $nome_pdf_risultante, $path_pdf_risultante.$path_temp."/");

        $result = array("url" => $path_pdf_risultante.$nome_pdf_risultante,
                        "name" => $nome_pdf_risultante);

        return $result;

	}
	
	private function setPDFProceduraRicorsivo($record, $path_temp, $id_pdf = 0){
        global $adb, $table_prefix, $default_charset, $current_user;

        $file_creato = $this->setPDFProceduraTemp( $record, $path_temp, $id_pdf);

        $lista_sottoprocessi = $this->getElementiProcedura($record, array("only_sottoprocesso" => true));

        foreach( $lista_sottoprocessi as $processo ){

            if( $processo["procedureid"] != 0 && $processo["procedureid"] != "" ){

                $this->setPDFProceduraRicorsivo( $processo["procedureid"], $path_temp, $id_pdf);

            }

        }

        $focus_processo = CRMEntity::getInstance('KpProcedure');
        $focus_processo->retrieve_entity_info($record, "KpProcedure", $dieOnError=false); 
        
        $numero_procedura = $focus_processo->column_fields["kp_numero_procedura"];
        $numero_procedura = html_entity_decode(strip_tags($numero_procedura), ENT_QUOTES, $default_charset);

        $nome_procedura = $focus_processo->column_fields["kp_nome_procedura"];
        $nome_procedura = html_entity_decode(strip_tags($nome_procedura), ENT_QUOTES, $default_charset);

        $file_name = $record."_".$nome_procedura;

        $file_name = $this->setNomeFile( $file_name );
        $file_name .= ".pdf";

        return $file_name;

	}
	
	public function setPDFProceduraTemp($record, $path_temp, $id_pdf = 0, $seq = 0){
		global $adb, $table_prefix, $default_charset, $current_user;

		require_once(__DIR__."/../../../modules/PDFMaker/InventoryPDF.php");
        require_once(__DIR__."/../../../include/mpdf/mpdf.php"); 

		//Tolto perche eseguito automaticamente dal PDFMaker
        //$svg = $this->getImmagineProcesso($record);
		
		if( $id_pdf == 0 ){
			$id_statici = $this->getConfigurazioniIdStatici();

			$id_statico_templateid = $id_statici["PDF Maker - Template Stampa Processi"];
			if( $id_statico_templateid["valore"] == "" && $id_statico_templateid["valore"] == 0 ){
				return;
			}

			$id_pdf = $id_statico_templateid["valore"];
		}
		
		$templateid = $id_pdf;
        $relmodule = 'KpProcedure';
        $language = 'it_it';
		$record = $record;
		
		$focus = CRMEntity::getInstance($relmodule);
        $focus->retrieve_entity_info($record,$relmodule);
        $focus->id = $record;

        $PDFContents = array();
        $TemplateContent = array();

        $PDFContent = PDFContent::getInstance($templateid, $relmodule, $focus, $language); 
        $pdf_content = $PDFContent->getContent();    

        $header_html = $pdf_content["header"];
        $body_html = $pdf_content["body"];
		$footer_html = $pdf_content["footer"];
		
		$tabella_rischi = $this->getTemplateRischiProcessoPDF($record);
        $tabella_documenti = $this->getTemplateDocumentiAttiviProcessoPDF($record);
        
        /*//Tolto perche eseguito automaticamente dal PDFMaker
        $body_html = str_replace("#SVG#", $svg, $body_html);
        $body_html = str_replace("#TABELLA_RISCHI#", $tabella_rischi, $body_html);
        $body_html = str_replace("#TABELLA_DOCUMENTI#", $tabella_documenti, $body_html);
        */
        
        $Settings = $PDFContent->getSettings();
        if($name==""){    
            $name = $PDFContent->getFilename();
        }

        $focus_processo = CRMEntity::getInstance('KpProcedure');
		$focus_processo->retrieve_entity_info($record, "KpProcedure", $dieOnError=false); 

        $numero_procedura = $focus_processo->column_fields["kp_numero_procedura"];
        $numero_procedura = html_entity_decode(strip_tags($numero_procedura), ENT_QUOTES, $default_charset);

        $nome_procedura = $focus_processo->column_fields["kp_nome_procedura"];
        $nome_procedura = html_entity_decode(strip_tags($nome_procedura), ENT_QUOTES, $default_charset);

        if( $this->sequenza_stampa != 0 && $this->sequenza_stampa != "" ){

            $name = $this->sequenza_stampa."_";

            $length_sequence = strlen($this->sequenza_stampa);			
            $this->sequenza_stampa = (int)$this->sequenza_stampa;

            $this->sequenza_stampa++;
            $this->sequenza_stampa = str_pad($this->sequenza_stampa, $length_sequence, "0", STR_PAD_LEFT);

        }
        else{

            $name = "";

        }

        if( $numero_procedura != "" ){
            $name .= $numero_procedura."_".$nome_procedura;
        }
        else{
            $name .= $nome_procedura;
        }

        $name = $this->setNomeFile( $name );
        $name .= ".pdf";
                    
        if ($Settings["orientation"] == "landscape"){
            $format = $Settings["format"]."-L";
        }
        else{
            $format = $Settings["format"];
        }

        $ListViewBlocks = array();
        if(strpos($body_html,"#LISTVIEWBLOCK_START#") !== false && strpos($body_html,"#LISTVIEWBLOCK_END#") !== false){
            preg_match_all("|#LISTVIEWBLOCK_START#(.*)#LISTVIEWBLOCK_END#|sU", $body_html, $ListViewBlocks, PREG_PATTERN_ORDER);
        }		
        
        if (count($ListViewBlocks) > 0){
                        
            $TemplateContent[$templateid] = $pdf_content;
            $TemplateSettings[$templateid] = $Settings;
                        
            $num_listview_blocks = count($ListViewBlocks[0]);
            for($i=0; $i<$num_listview_blocks; $i++){
                $ListViewBlock[$templateid][$i] = $ListViewBlocks[0][$i];
                $ListViewBlockContent[$templateid][$i][$record][] = $ListViewBlocks[1][$i];
            }   
        }
        else{
            if (!isset($mpdf)){           
                $mpdf=new mPDF('',$format,'','Arial',$Settings["margin_left"],$Settings["margin_right"],0,0,$Settings["margin_top"],$Settings["margin_bottom"]);  
                $mpdf->SetAutoFont();
                @$mpdf->SetHTMLHeader($header_html);
            }
            else{
                @$mpdf->SetHTMLHeader($header_html);
                @$mpdf->WriteHTML('<pagebreak sheet-size="'.$format.'" margin-left="'.$Settings["margin_left"].'mm" margin-right="'.$Settings["margin_right"].'mm" margin-top="0mm" margin-bottom="0mm" margin-header="'.$Settings["margin_top"].'mm" margin-footer="'.$Settings["margin_bottom"].'mm" />');
            }     
            @$mpdf->SetHTMLFooter($footer_html);
            @$mpdf->WriteHTML($body_html);
        }
                
        if (count($TemplateContent)> 0){
            
            foreach($TemplateContent AS $templateid => $TContent){
                $header_html = $TContent["header"];
                $body_html = $TContent["body"];
                $footer_html = $TContent["footer"];
                    
                $Settings = $TemplateSettings[$templateid];
                    
                foreach($ListViewBlock[$templateid] AS $id => $text){
                    $replace = "";
                    foreach($Records as $record){  
                        $replace .= implode("",$ListViewBlockContent[$templateid][$id][$record]);   
                    }
                        
                    $body_html = str_replace($text,$replace,$body_html);
                }
                    
                if ($Settings["orientation"] == "landscape"){
                    $format = $Settings["format"]."-L";
                }
                else{
                    $format = $Settings["format"];
                }
                    
                    
                if (!isset($mpdf)){           
                    $mpdf=new mPDF('',$format,'','Arial',$Settings["margin_left"],$Settings["margin_right"],0,0,$Settings["margin_top"],$Settings["margin_bottom"]);  
                    $mpdf->SetAutoFont();
                    @$mpdf->SetHTMLHeader($header_html);
                }
                else{
                    @$mpdf->SetHTMLHeader($header_html);
                    @$mpdf->WriteHTML('<pagebreak sheet-size="'.$format.'" margin-left="'.$Settings["margin_left"].'mm" margin-right="'.$Settings["margin_right"].'mm" margin-top="0mm" margin-bottom="0mm" margin-header="'.$Settings["margin_top"].'mm" margin-footer="'.$Settings["margin_bottom"].'mm" />');
                }     
                @$mpdf->SetHTMLFooter($footer_html);
                @$mpdf->WriteHTML($body_html);
            }
		}
		
		$upload_file_path = __DIR__."/../CustomViews/KpBPMNcreator/temp/".$path_temp."/";

        if ( !is_dir($upload_file_path) ) {
            mkdir($upload_file_path, 0777, true);
            chown($upload_file_path, "www-data");
            chgrp($upload_file_path, "www-data");
        }
        else{
            chown($upload_file_path, "www-data");
            chgrp($upload_file_path, "www-data");
            chmod($upload_file_path, 0777);
        }

        if($name!=""){
            $file_name = $name.".pdf";
        }

        if( file_exists($upload_file_path.$record."_".$file_name) ){ 
            @unlink($upload_file_path.$record."_".$file_name);
        }
    
        //$mpdf->Output($upload_file_path.$record."_".$file_name);
        $mpdf->Output($upload_file_path.$file_name);

        $file_png = __DIR__."/../CustomViews/KpBPMNcreator/svg/".$record.".png";
        if( file_exists($file_png) ){ 
            @unlink($file_png);
        }

        return $file_name;
		
    }

    public function creaSottoprocesso($id_elemento){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = 0;

        $dati_elemento = $this->getElementoProceduraById($id_elemento);

        if( $dati_elemento["esiste"] ){

            $nome_sottoprocesso = $dati_elemento["nome"];

            $processo_padre = $dati_elemento["procedura"];

            $dati_processo_padre = $this->getProcesso($processo_padre);

            $focus = CRMEntity::getInstance('KpProcedure');
            $focus->column_fields['assigned_user_id'] = $current_user->id;
            $focus->column_fields['kp_nome_procedura'] = $nome_sottoprocesso;
            $focus->column_fields['kp_tipo_procedura'] = $dati_processo_padre["tipo_procedura"];
            $focus->column_fields['kp_data_procedura'] = date("Y-m-d");
            $focus->column_fields['kp_primario'] = '0';
            $focus->column_fields['kp_numero_revisione'] = '0';
            $focus->column_fields['kp_stato_procedura'] = 'Attivo';
            $focus->save('KpProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

            $this->setLinkProcessoElementoProcedura($id_elemento, $focus->id);

            $lista_aziende_procedura = $this->getAziendeRelazionateAllaProcedura($processo_padre);

            foreach($lista_aziende_procedura  as $azienda){

                $this->setLinkAziendaProcedura($focus->id, $azienda["id"]);

            }

            $lista_stabilimenti_procedura = $this->getStabilimentiRelazionatiAllaProcedura($processo_padre);

            foreach($lista_stabilimenti_procedura  as $stabilimento){

                $this->setLinkStabilimentoProcedura($focus->id, $stabilimento["id"]);

            }

            $result = $focus->id;

        }

        return $result;

    }

    public function setLinkProcessoElementoProcedura($elemento_id, $processo_id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->retrieve_entity_info($elemento_id, "KpEntitaProcedure");

        foreach($focus->column_fields as $fieldname => $value) {
            $focus->column_fields[$fieldname] = decode_html($value);
        }

        $focus->column_fields['kp_relazionato_a_id'] = $processo_id;
        $focus->column_fields['kp_aggiornato'] = '1'; 
        $focus->mode = 'edit';
        $focus->id = $elemento_id;
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

    }

    public function unsetLinkProcessoElementoProcedura($elemento_id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->retrieve_entity_info($elemento_id, "KpEntitaProcedure");

        foreach($focus->column_fields as $fieldname => $value) {
            $focus->column_fields[$fieldname] = decode_html($value);
        }

        $focus->column_fields['kp_relazionato_a_id'] = "";
        $focus->column_fields['kp_aggiornato'] = '1'; 
        $focus->mode = 'edit';
        $focus->id = $elemento_id;
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

    }

    public function setLinkAziendaProcedura($procedura, $azienda){
        global $adb, $table_prefix, $default_charset, $current_user;

        if( !$this->relazionataAdAzienda($procedura, $azienda) ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$procedura.", 'KpProcedure', ".$azienda.", 'Accounts')";
            $adb->query($insert);
            
        }

    }

    public function setLinkStabilimentoProcedura($procedura, $stabilimento){
        global $adb, $table_prefix, $default_charset, $current_user;

        if( !$this->relazionataAStabilimento($procedura, $stabilimento) ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
                        (".$procedura.", 'KpProcedure', ".$stabilimento.", 'Stabilimenti')";
            $adb->query($insert);

        }

    }

    public function relazionataAdAzienda($id, $azienda){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        $query = "SELECT 
                    *
                    FROM {$table_prefix}_crmentityrel
                    WHERE (crmid = ".$id." AND module = 'KpProcedure' AND relcrmid = ".$azienda." AND relmodule = 'Accounts') OR (relcrmid = ".$id." AND relmodule = 'KpProcedure' AND crmid = ".$azienda." AND module = 'Accounts')";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $result = true;

        }

        return $result;

    }

    public function relazionataAStabilimento($id, $stabilimento){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        $query = "SELECT 
                    *
                    FROM {$table_prefix}_crmentityrel
                    WHERE (crmid = ".$id." AND module = 'KpProcedure' AND relcrmid = ".$stabilimento." AND relmodule = 'Stabilimenti') OR (relcrmid = ".$id." AND relmodule = 'KpProcedure' AND crmid = ".$stabilimento." AND module = 'Stabilimenti')";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $result = true;

        }

        return $result;

    }

    public function getAziendeRelazionateAllaProcedura($procedura){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid azienda
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.crmid = ".$procedura." AND rel.module = 'KpProcedure' AND rel.relmodule = 'Accounts')
                    UNION 
                    (SELECT 
                    rel.crmid azienda
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relcrmid = ".$procedura." AND rel.relmodule = 'KpProcedure' AND rel.module = 'Accounts')) AS t
                    GROUP BY t.azienda";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'azienda');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $result[] = array("id" => $id);

        }

        return $result;

    }

    public function getStabilimentiRelazionatiAllaProcedura($procedura){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.crmid = ".$procedura." AND rel.module = 'KpProcedure' AND rel.relmodule = 'Stabilimenti')
                    UNION 
                    (SELECT 
                    rel.crmid stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relcrmid = ".$procedura." AND rel.relmodule = 'KpProcedure' AND rel.module = 'Stabilimenti')) AS t
                    GROUP BY t.stabilimento";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'stabilimento');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $result[] = array("id" => $id);

        }

        return $result;

    }
    
    public function getProcesso($id){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT
                    proc.kpprocedureid kpprocedureid,
                    proc.kp_nome_procedura kp_nome_procedura,
                    proc.kp_numero_procedura kp_numero_procedura,
                    proc.kp_tipo_procedura kp_tipo_procedura,
                    proc.kp_data_procedura kp_data_procedura,
                    proc.kp_scadenza_procedura kp_scadenza_procedura,
                    proc.kp_bpmn_xml kp_bpmn_xml,
                    proc.kp_primario kp_primario,
                    proc.kp_revisione_di kp_revisione_di,
                    proc.kp_numero_revisione kp_numero_revisione,
                    proc.kp_data_revisione kp_data_revisione,
                    proc.kp_stato_procedura kp_stato_procedura,
                    proc.kp_rev_in_data kp_rev_in_data,
                    proc.kp_bpmn_svg kp_bpmn_svg,
                    proc.description description
                    FROM {$table_prefix}_kpprocedure proc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = proc.kpprocedureid
                    WHERE ent.deleted = 0 AND proc.kpprocedureid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $id = $adb->query_result($result_query, 0, 'kpprocedureid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, 0, 'kp_nome_procedura');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $descrizione = $adb->query_result($result_query, 0, 'description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);

            $tipo_procedura = $adb->query_result($result_query, 0, 'kp_tipo_procedura');
            $tipo_procedura = html_entity_decode(strip_tags($tipo_procedura), ENT_QUOTES, $default_charset);

            $bpmn_xml = $adb->query_result($result_query, 0, 'kp_bpmn_xml');
            $bpmn_xml = html_entity_decode(strip_tags($bpmn_xml), ENT_QUOTES, $default_charset);
            if($bpmn_xml == null){
                $bpmn_xml = "";
            }

            $primario = $adb->query_result($result_query, 0, 'kp_primario');
            $primario = html_entity_decode(strip_tags($primario), ENT_QUOTES, $default_charset);
            if( $primario == null || $primario == "" ){
                $primario = 0;
            }

            $revisione_di = $adb->query_result($result_query, 0, 'kp_revisione_di');
            $revisione_di = html_entity_decode(strip_tags($revisione_di), ENT_QUOTES, $default_charset);
            if( $revisione_di == null || $revisione_di == ""){
                $revisione_di = 0;
            }

            $numero_revisione = $adb->query_result($result_query, 0, 'kp_numero_revisione');
            $numero_revisione = html_entity_decode(strip_tags($numero_revisione), ENT_QUOTES, $default_charset);
            if( $numero_revisione == null || $numero_revisione == ""){
                $numero_revisione = 0;
            }

            $data_revisione = $adb->query_result($result_query, 0, 'kp_data_revisione');
            $data_revisione = html_entity_decode(strip_tags($data_revisione), ENT_QUOTES, $default_charset);
            if( $data_revisione == null || $data_revisione == "0000-00-00"){
                $data_revisione = "";
            }

            $rev_in_data = $adb->query_result($result_query, 0, 'kp_rev_in_data');
            $rev_in_data = html_entity_decode(strip_tags($rev_in_data), ENT_QUOTES, $default_charset);
            if( $rev_in_data == null || $rev_in_data == "0000-00-00"){
                $rev_in_data = "";
            }

            $numero_procedura = $adb->query_result($result_query, 0, 'kp_numero_procedura');
            $numero_procedura = html_entity_decode(strip_tags($numero_procedura), ENT_QUOTES, $default_charset);
            if( $numero_procedura == null ){
                $numero_procedura = "";
            }

            $stato_procedura = $adb->query_result($result_query, 0, 'kp_stato_procedura');
            $stato_procedura = html_entity_decode(strip_tags($stato_procedura), ENT_QUOTES, $default_charset);

            $bpmn_svg = $adb->query_result($result_query, 0, 'kp_bpmn_svg');
            $bpmn_svg = html_entity_decode(strip_tags($bpmn_svg), ENT_QUOTES, $default_charset);
            if($bpmn_svg == null){
                $bpmn_svg = "";
            }

            $settings_procedure = $this->getSettingsProcedure();

            $richiedi_approvazione = $settings_procedure["richiedi_approvazione"];

        }
        else{

            $id = 0;
            $nome = "";
            $descrizione = "";
            $tipo_procedura = "";
            $bpmn_xml = "";
            $richiedi_approvazione = "0";
            $numero_procedura = "";
            $primario = 0;
            $revisione_di = 0;
            $numero_revisione = 0;
            $data_revisione = "";
            $rev_in_data = "";
            $stato_procedura = "";
            $bpmn_svg = "";

        }

        $result = array("id" => $id,
                        "nome" => $nome,
                        "tipo_procedura" => $tipo_procedura,
                        "primario" => $primario,
                        "numero_procedura" => $numero_procedura,
                        "revisione_di" => $revisione_di,
                        "numero_revisione" => $numero_revisione,
                        "data_revisione" => $data_revisione,
                        "rev_in_data" => $rev_in_data,
                        "stato_procedura" => $stato_procedura,
                        "descrizione" => $descrizione,
                        "richiedi_approvazione" => $richiedi_approvazione,
                        "bpmn_xml" => $bpmn_xml,
                        "bpmn_svg" => $bpmn_svg);

        return $result;

    }

    public function getSettingsProcedure(){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $richiedi_approvazione = "0";

        $query_verifica = "SHOW TABLES LIKE 'kp_settings_procedure'";

        $result_query_verifica = $adb->query($query_verifica);
        $num_result_verifica = $adb->num_rows($result_query_verifica);

        if( $num_result_verifica > 0 ){

            $query = "SELECT 
                        richiedi_approvazione 
                        FROM kp_settings_procedure";

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);
            if( $num_result > 0 ){

                $richiedi_approvazione = $adb->query_result($result_query, $i, 'richiedi_approvazione');
                $richiedi_approvazione = html_entity_decode(strip_tags($richiedi_approvazione), ENT_QUOTES,$default_charset);   
                if($richiedi_approvazione == "" || $richiedi_approvazione == null){
                    $richiedi_approvazione = "0";
                }

            }

        }

        $result = array("richiedi_approvazione" => $richiedi_approvazione);
  
        return $result;

    }

    public function getAlberoProcessi($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $lista_tipi_procedure = $this->getTipiProcedure($filtro["azienda"], $filtro["stabilimento"]);

        foreach( $lista_tipi_procedure as $tipo_procedura ){

            $filtro_processi = array("nome_processo" => "",
                                    "tipo_procedura" => $tipo_procedura["tipo_procedura"],
                                    "primario" => true,
                                    "azienda" => $filtro["azienda"],
                                    "stabilimento" => $filtro["stabilimento"]);

            $lista_procedure = $this->getProcessi($filtro_processi);

            $result[] = array("tipo_procedura" => $tipo_procedura["tipo_procedura"],
                                "lista_procedure" => $lista_procedure);

        }

        return $result;

    }

    public function getTipiProcedure($azienda = "", $stabilimento = ""){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT 
                    proc.kp_tipo_procedura kp_tipo_procedura
                    FROM {$table_prefix}_kpprocedure proc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = proc.kpprocedureid
                    WHERE ent.deleted = 0 AND proc.kp_primario = '1'
                    GROUP BY proc.kp_tipo_procedura
                    ORDER BY proc.kp_tipo_procedura ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $tipo_procedura = $adb->query_result($result_query, $i, 'kp_tipo_procedura');
            $tipo_procedura = html_entity_decode(strip_tags($tipo_procedura), ENT_QUOTES, $default_charset);
            $tipo_procedura = $this->encodeString($tipo_procedura);

            if( $azienda != "" || $stabilimento != "" ){

                $filtro_processi = array("nome_processo" => "",
                                        "tipo_procedura" => $tipo_procedura,
                                        "primario" => true,
                                        "azienda" => $azienda,
                                        "stabilimento" => $stabilimento);

                $lista_procedure = $this->getProcessi($filtro_processi);

                if( count($lista_procedure) > 0 ){

                    $result[] = array("tipo_procedura" => $tipo_procedura);

                }

            }
            else{

                $result[] = array("tipo_procedura" => $tipo_procedura);

            }

        }

        return $result;

    }

    public function getProcessi($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT
                    proc.kpprocedureid kpprocedureid,
                    proc.kp_nome_procedura kp_nome_procedura,
                    proc.kp_numero_procedura kp_numero_procedura,
                    proc.kp_tipo_procedura kp_tipo_procedura,
                    proc.kp_data_procedura kp_data_procedura,
                    proc.kp_scadenza_procedura kp_scadenza_procedura,
                    proc.kp_bpmn_xml kp_bpmn_xml,
                    proc.kp_stato_procedura stato_procedura,
                    proc.description description
                    FROM {$table_prefix}_kpprocedure proc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = proc.kpprocedureid
                    WHERE ent.deleted = 0";

        if( $filtro["nome_processo"] != "" ){
            $query .= " AND proc.kp_nome_procedura like '%".$filtro["nome_processo"]."%'";
        }

        if( $filtro["tipo_procedura"] != "" ){
            $query .= " AND proc.kp_tipo_procedura = '".$filtro["tipo_procedura"]."'";
        }

        if( $filtro["primario"] ){
            $query .= " AND proc.kp_primario = '1'";
        }

        $query .= " AND proc.kp_stato_procedura = 'Attivo'";

        $query .= " ORDER BY proc.kp_nome_procedura ASC";

        //print_r($query);die;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'kpprocedureid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

            $numero_procedura = $adb->query_result($result_query, $i, 'kp_numero_procedura');
            $numero_procedura = html_entity_decode(strip_tags($numero_procedura), ENT_QUOTES,$default_charset);
            $numero_procedura = trim($numero_procedura);

            $nome = $adb->query_result($result_query, $i, 'kp_nome_procedura');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            if( $numero_procedura != null && $numero_procedura != '' ){
                $nome = $numero_procedura." - ".$nome;
            }

            $nome = $this->encodeString($nome);

            if( $this->proceduraAbilitataPerAzienda($id, $filtro["azienda"]) || $filtro["azienda"] == 0 ){

                if( $this->proceduraAbilitataPerStabilimento($id, $filtro["stabilimento"]) || $filtro["stabilimento"] == 0 ){

                    $lista_sottoprocessi = array();

                    $lista_sottoprocessi = $this->getElementiProcedura($id, array("only_sottoprocesso" => true));

                    $result[] = array("id" => $id,
                                        "nome" => $nome,
                                        "lista_sottoprocessi" => $lista_sottoprocessi);

                }

            }

        }

        return $result;

    }

    public function proceduraAbilitataPerAzienda($id, $azienda){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        if( $this->presentiAziendeRelazionate($id) ){

            if( $this->relazionataAdAzienda($id, $azienda)){

                //Se delle aziende relazionate deve essere visibile solo per tali aziende
                $result = true;

            }

        }
        else{

            //Se non ha alcuna azienda relazionata deve essere visibile per tutti
            $result = true;

        }

        return $result;

    }

    public function presentiAziendeRelazionate($id){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid azienda
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.crmid = ".$id." AND rel.module = 'KpProcedure' AND rel.relmodule = 'Accounts')
                    UNION 
                    (SELECT 
                    rel.crmid azienda
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relcrmid = ".$id." AND rel.relmodule = 'KpProcedure' AND rel.module = 'Accounts')) AS t
                    GROUP BY t.azienda";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $result = true;

        }

        return $result;

    }

    public function proceduraAbilitataPerStabilimento($id, $stabilimento){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        if( $this->presentiStabilimentiRelazionati($id) ){

            if( $this->relazionataAStabilimento($id, $stabilimento)){

                //Se ha dei stabilimenti relazionati deve essere visibile solo per tali stabilimenti
                $result = true;

            }

        }
        else{

            //Se non ha alcun stabilimento relazionato deve essere visibile per tutti
            $result = true;

        }

        return $result;

    }

    public function presentiStabilimentiRelazionati($id){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.crmid = ".$id." AND rel.module = 'KpProcedure' AND rel.relmodule = 'Stabilimenti')
                    UNION 
                    (SELECT 
                    rel.crmid stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relcrmid = ".$id." AND rel.relmodule = 'KpProcedure' AND rel.module = 'Stabilimenti')) AS t
                    GROUP BY t.stabilimento";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $result = true;

        }

        return $result;

    }

    public function getImmagineProcesso($record){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $file_svg = __DIR__."/../CustomViews/KpBPMNcreator/svg/".$record.".svg";
		
		if( !file_exists($file_svg) ){ 

            $this->setFileSVG($record);

		}

		if( file_exists($file_svg) ){ 

			$file_png = "cache/pdfmaker/".$record.".png";
			
			$this->convertSVGtoPNG($file_svg, $file_png);	

            if( file_exists($file_png) ){ 

                $svg = "<img src='".$file_png."' style='max-width: 100%; float: left; max-height: 100%;'/>";

            }
            else{
                $svg = "SVG Mancante!";
            }
			
        }
        else{
            $svg = "SVG Mancante";
        }
        
        return $svg;

    }

    public function getRisorseElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;
        
        $risorse_task = array();

        $dati_elemento = $this->getElementoProceduraById($id);

        if( $filtro["azienda"] != 0 && $filtro["azienda"] != ""){

            $array_aziende = array();
            $array_aziende[] = $filtro["azienda"];

        }
        else{

            $lista_aziende_relazionate = $this->getAziendeRelazionateAllaProcedura( $dati_elemento["procedura"] );
            $array_aziende = array();
            foreach( $lista_aziende_relazionate as $azienda ){
                $array_aziende[] = $azienda["id"];
            }

        }

        if( $filtro["stabilimento"] != 0 && $filtro["stabilimento"] != ""){

            $array_stabilimenti = array();
            $array_stabilimenti[] = $filtro["stabilimento"];

        }
        else{
        
            $lista_stabilimenti_relazionati = $this->getStabilimentiRelazionatiAllaProcedura( $dati_elemento["procedura"] );
            $array_stabilimenti = array();
            foreach( $lista_stabilimenti_relazionati as $stabilimento ){
                $array_stabilimenti[] = $stabilimento["id"];
            }

        }

        $ruoli_task = $this->getRuoliElementoProcedura( $id, array() );
 
        if( count($ruoli_task) > 0 ){

            foreach( $ruoli_task as $ruolo ){

                $lista_risorse_ruolo = $this->getRisorseRuolo($ruolo["id"], $array_aziende, $array_stabilimenti);

                foreach( $lista_risorse_ruolo as $risorsa ){

                    if( !in_array( $risorsa["id"], $risorse_da_notificare ) ){

                        $risorse_task[] = $risorsa["id"];

                    }

                }

            }

        }
       
        return $risorse_task;

    }

    public function getRisorseRuolo($ruolo, $array_aziende, $array_stabilimenti){
        global $adb, $table_prefix, $default_charset, $current_user;

        $where_aziende = "";
        if( count($array_aziende) > 0 ){

            $where_aziende = "(";
            foreach( $array_aziende as $azienda ){

                if( $where_aziende == "(" ){
                    $where_aziende .= $azienda;
                }
                else{
                    $where_aziende .= ", ".$azienda;
                }

            }
            $where_aziende .= ")";

        }

        $where_stabilimenti = "";
        if( count($array_stabilimenti) > 0 ){

            $where_stabilimenti = "(";
            foreach( $array_stabilimenti as $stabilimento ){

                if( $where_stabilimenti == "(" ){
                    $where_stabilimenti .= $stabilimento;
                }
                else{
                    $where_stabilimenti .= ", ".$stabilimento;
                }

            }
            $where_stabilimenti .= ")";

        }

        $data_corrente = date("Y-m-d");

        $query = "SELECT 
                    cont.contactid id,
                    cont.firstname firstname,
                    cont.lastname lastname,
                    cont.accountid accountid,
                    cont.stabilimento stabilimento
                    FROM {$table_prefix}_kpentitaorganigrammi entorg
                    INNER JOIN {$table_prefix}_crmentity entityentorg ON entityentorg.crmid = entorg.kpentitaorganigrammiid
                    INNER JOIN {$table_prefix}_kporganigrammi org ON org.kporganigrammiid = entorg.kp_organigramma
                    INNER JOIN {$table_prefix}_crmentity entityorg ON entityorg.crmid = org.kporganigrammiid
                    INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = entorg.kp_risorsa
                    INNER JOIN {$table_prefix}_crmentity entitycont ON entitycont.crmid = cont.contactid
                    WHERE entityentorg.deleted = 0 AND entityorg.deleted = 0 AND entitycont.deleted = 0 AND entorg.kp_ruolo = ".$ruolo;

        $query .= " AND (cont.data_fine_rap IS NULL || cont.data_fine_rap = '' || cont.data_fine_rap = '0000-00-00' || cont.data_fine_rap >= '".$data_corrente."')";

        $query .= " AND org.kp_stato_organigramma = 'Attivo'";

        if( $where_aziende != "" ){

            $query .= " AND cont.accountid IN ".$where_aziende;

        }

        if( $where_stabilimenti != "" ){

            $query .= " AND cont.stabilimento IN ".$where_stabilimenti;

        }

        $query .= " GROUP BY cont.contactid
                    ORDER BY cont.firstname ASC, cont.lastname ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'firstname');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $cognome = $adb->query_result($result_query, $i, 'lastname');
            $cognome = html_entity_decode(strip_tags($cognome), ENT_QUOTES, $default_charset);

            $azienda = $adb->query_result($result_query, $i, 'accountid');
            $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES, $default_charset);

            $stabilimento = $adb->query_result($result_query, $i, 'stabilimento');
            $stabilimento = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "cognome" => $cognome,
                                "azienda" => $azienda,
                                "stabilimento" => $stabilimento);

        }

        return $result;

    }

    public function getRisorsa($id){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    cont.firstname nome,
                    cont.lastname cognome,
                    acc.accountname nome_azienda,
                    stab.nome_stabilimento nome_stabilimento
                    FROM {$table_prefix}_contactdetails cont
                    LEFT JOIN {$table_prefix}_account acc ON acc.accountid = cont.accountid
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = cont.stabilimento
                    WHERE cont.contactid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $cognome = $adb->query_result($result_query, 0, 'cognome');
            $cognome = html_entity_decode(strip_tags($cognome), ENT_QUOTES, $default_charset);

            $nome_azienda = $adb->query_result($result_query, 0, 'nome_azienda');
            $nome_azienda = html_entity_decode(strip_tags($nome_azienda), ENT_QUOTES, $default_charset);
            if( $nome_azienda == null ){
                $nome_azienda = "";
            }

            $nome_stabilimento = $adb->query_result($result_query, 0, 'nome_stabilimento');
            $nome_stabilimento = html_entity_decode(strip_tags($nome_stabilimento), ENT_QUOTES, $default_charset);
            if( $nome_stabilimento == null ){
                $nome_stabilimento = "";
            }

        }
        else{

            $nome = "";
            $cognome = "";
            $nome_azienda = "";
            $nome_stabilimento = "";

        }

        $result = array("nome" => $nome,
                        "cognome" => $cognome,
                        "nome_azienda" => $nome_azienda,
                        "nome_stabilimento" => $nome_stabilimento);
        
        return $result;

    }

    public function getListaAziende($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT
                    acc.accountid accountid,
                    acc.accountname accountname,
                    accbill.bill_city bill_city
                    FROM {$table_prefix}_account acc 
                    INNER JOIN {$table_prefix}_accountbillads accbill ON accbill.accountaddressid = acc.accountid
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = acc.accountid
                    WHERE ent.deleted = 0";

        if($filtro["nome"] != ""){
            $query .= " AND acc.accountname LIKE '%".$filtro["nome"]."%'";
        }

        if($filtro["citta"] != ""){
            $query .= " AND accbill.bill_city LIKE '%".$filtro["citta"]."%'";
        }

        $query .= " ORDER BY acc.accountname ASC
                    LIMIT 0, 100";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'accountid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

            $nome = $adb->query_result($result_query, $i, 'accountname');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $citta = $adb->query_result($result_query, $i, 'bill_city');
            $citta = html_entity_decode(strip_tags($citta), ENT_QUOTES,$default_charset);

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "citta" => $citta); 

        }

        return $result;

    }


} 

?>