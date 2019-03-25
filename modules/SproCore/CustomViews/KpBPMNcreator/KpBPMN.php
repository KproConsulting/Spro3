<?php

/* kpro@tom12072017 */
		
/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */

class KpBPMN {

    /**

        static function: 
            - converBPMNxmlToArray($bpmn_xml)
            - aggiornaBPMNcrm($id, $bpmn_xml)
            - getElementoProcedura($id_procedura, $id_bpmn)
            - getElementoRelazionato($id)
            - getDocumentiElementoProcedura($id, $filtro)
            - getDocumentiAssociabiliAElementoProcedura($id, $filtro)
            - getDocumentoElementoProcedura($id, $documento)
            - setLinkDocumentoProcedura($id, $documento)
            - setLinkDocumentoElementoProcedura($id, $documento)
            - removeLinkDocumentoElementoProcedura($id, $documento)
            - getProcessi($filtro)
            - getAlberoProcessi($filtro)
            - getTipiProcedure($azienda = "", $stabilimento = "")
            - getProcesso($id)
            - setLinkProcessoElementoProcedura($elemento_id, $processo_id)
            - unsetLinkProcessoElementoProcedura($elemento_id)
            - getRuoliElementoProcedura($id, $filtro)
            - getRuoliAssociabiliAElementoProcedura($id, $filtro)
            - setLinkRuoloElementoProcedura($id, $ruolo)
            - getRuoloElementoProcedura($id, $ruolo)
            - unsetRuoloElementoProcedura($id, $ruolo)
            - getElementiProcedura($id, $filtro)
            - getElementoProceduraById($id)
            - proceduraAbilitataPerAzienda($id, $azienda)
            - presentiAziendeRelazionate($id)
            - relazionataAdAzienda($id, $azienda)
            - proceduraAbilitataPerStabilimento($id, $stabilimento)
            - presentiStabilimentiRelazionati($id)
            - relazionataAStabilimento($id, $stabilimento)
            - getSettingsProcedure()
            - creaRevisioneBPMN($procedura, $descrizione)
            - getRevisioneProceduraById($id)
            - gestioneNotificheRevisione($id)
            - getAziendeRelazionateAllaProcedura($procedura)
            - getStabilimentiRelazionatiAllaProcedura($procedura)
            - getRisorseRuolo($ruolo, $array_aziende, $array_stabilimenti)
            - generaNotificheRevisione($id, $risorse_da_notificare)
            - getUtenteRisorsa($risorsa)
            - generaNotificaRevisione($revisione, $risorsa)
            - getNotificaRevisioneRisorsa($revisione, $risorsa)
            - getAlberoNotifiche($filtro)
            - getDateNotificheUtente($filtro)
            - getNotificheUtentePerData($filtro)
            - getNotificaRevisioneById($id)
            - setVisioneNotifica($id)
            - getWorkflowProcedura($id, $filtro)
            - getWorkflowAssociabiliAProcedura($id, $filtro)
            - setLinkWorkflowProcedura($id, $workflow)
            - getWorkflowElementoProcedura($id, $workflow)
            - unsetWorkflowElementoProcedura($id, $workflow)
            - getRisorseElementoProcedura($id, $filtro)
            - getRisorsa($id)
            - setDescrizioneElementoProcedura($id, $descrizione)
            - setValoreAggiuntoElementoProcedura($id, $valore)
            - creaSottoprocesso($id_elemento)
            - setLinkAziendaProcedura($procedura, $azienda)
            - setLinkStabilimentoProcedura($procedura, $stabilimento)

    */

    static function converBPMNxmlToArray($bpmn_xml){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $bpmn_xml = str_replace("bpmn:", "", $bpmn_xml);
        $bpmn_xml = str_replace("bpmn2:", "", $bpmn_xml);
        $bpmn_xml = str_replace("bpmndi:", "", $bpmn_xml);   //kpro@tom05062018
        $bpmn_xml = str_replace("dc:", "", $bpmn_xml);   //kpro@tom05062018
    
        $xml = simplexml_load_string($bpmn_xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        //print_r($xml);

        $process = $xml->process;
        //print_r($process);

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

            $elements_array_key[$elemento["id"]]["order"] = self::calcolaOrdineElemento($elemento["id"], $elements_array_key, $sequence_array_key, 0);
            
        }

        //print_r($elements_array_key);

        $dupplicati = self::checkIfOrderDuplicati($elements_array_key, 0);
        $i = 0;

        while( $dupplicati ){

            $elements_array_key = self::sistemaElementiConStessoOrdine($elements_array_key, 0);
            $dupplicati = self::checkIfOrderDuplicati($elements_array_key, 0);

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

    static function calcolaOrdineElemento($bpmn_id, $array_elementi, $array_link, $start, $gia_passati = array()){
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
                $start = self::calcolaOrdineElemento($source, $array_elementi, $array_link, $start, $gia_passati);
            }
 
        }

        return $start;

    }

    static function sistemaElementiConStessoOrdine($elements_array, $order){
        global $adb, $table_prefix, $default_charset, $current_user;

        $first_element = self::getIdOfFirstTaskOfOrder($elements_array, $order);
        //print_r("Il primo elemento di order ".$order." è ".$first_element);

        if( $first_element != "" ){

            $elements_array[$first_element]["order"] = $order;

            $next_order = $order + 1;
            
            foreach( $elements_array as $elemento ){

                if( $elemento['order'] == $order && $elemento['id'] != $first_element ){

                    $elements_array[ $elemento['id'] ]["order"] = $next_order;

                }

            }

            $elements_array = self::sistemaElementiConStessoOrdine($elements_array, $next_order);

        }

        return $elements_array;

    }

    static function getIdOfFirstTaskOfOrder($elements_array, $order){
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

    static function checkIfOrderDuplicati($elements_array, $order_start){
        global $adb, $table_prefix, $default_charset, $current_user;

        $max_order = self::getMaxOrder($elements_array);

        if( $max_order > $order_start){

            for($i = $order_start; $i <= $max_order; $i++ ){

                $duplicato = self::checkIfOrderDuplicato($elements_array, $i);

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

    static function getMaxOrder($elements_array){
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

    static function checkIfOrderDuplicato($elements_array, $order){
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

    static function aggiornaBPMNcrm($id, $bpmn_xml){
        global $adb, $table_prefix, $default_charset, $current_user;

        if( $bpmn_xml != "" ){

            $bpmn_json = self::converBPMNxmlToArray($bpmn_xml);

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
                        
                        $verifica_esistenza_elemento = self::getElementoProcedura($id, $bpmn_id);

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

                self::deleteElementiNonAggiornatiprocedura($id);

            }

        }

    }

    static function deleteElementiNonAggiornatiprocedura($procedura){
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

            $lista_ruoli = self::getRuoliElementoProcedura($id_origine, array());

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

    static function getElementoProcedura($id_procedura, $id_bpmn){
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
            
            $nome = self::encodeString($nome);

            $tipo_entita_bpmn = $adb->query_result($result_query, 0, 'kp_tipo_entita_bpmn');
			$tipo_entita_bpmn = html_entity_decode(strip_tags($tipo_entita_bpmn), ENT_QUOTES, $default_charset);

            $relazionato_a_id = $adb->query_result($result_query, 0, 'kp_relazionato_a_id');
			$relazionato_a_id = html_entity_decode(strip_tags($relazionato_a_id), ENT_QUOTES, $default_charset);

            if($relazionato_a_id != null && $relazionato_a_id != "" && $relazionato_a_id != 0){
                $dati_elemento_relazionato = self::getElementoRelazionato($relazionato_a_id);

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
                
                $nome_tipo_attivita = self::encodeString($nome_tipo_attivita);

            }
            else{
                $nome_tipo_attivita = "";
                $tipo_attivita_id = 0;
            }

            $descrizione = $adb->query_result($result_query, 0, 'description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);

            $descrizione = self::encodeString($descrizione);
            
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

    static function getElementoProceduraById($id){
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
            
            $nome = self::encodeString($nome);

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
                $dati_elemento_relazionato = self::getElementoRelazionato($relazionato_a_id);
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
            
            $descrizione = self::encodeString($descrizione);

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

    static function getElementoRelazionato($id){
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
            
            $nome = self::encodeString($nome);

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

    static function getDocumentiElementoProcedura($id, $filtro){
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
                    notes.filelocationtype filelocationtype
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
                continue;
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
            
            $result[] = array('notesid' => $notesid,
                                'attachmentsid' => $attachmentsid,
                                'title' => $title,
                                'createdtime' => $createdtime,
                                'modifiedtime' => $modifiedtime,
                                'data_documento' => $data_documento_inv,
                                'tipo' => $tipo_download);
            
        }

        return $result;

    }

    static function getDocumentiAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $lista_elementi_gia_relazionati = "(";

        $query = "SELECT attac.attachmentsid attachmentsid, 
                    attac.name name, 
                    attac.path path, 
                    notes.title title, 
                    notes.notesid notesid, 
                    notes.folderid cartella_id,
                    date(ent.createdtime) data_documento,
                    ent.createdtime createdtime, 
                    ent.modifiedtime modifiedtime 
                    FROM {$table_prefix}_notes notes 
                    INNER JOIN {$table_prefix}_notescf notescf ON notescf.notesid = notes.notesid 
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_senotesrel senote ON senote.notesid = notes.notesid
                    LEFT JOIN {$table_prefix}_seattachmentsrel seattac ON seattac.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_attachments attac ON attac.attachmentsid = seattac.attachmentsid 
                    WHERE ent.deleted = 0 AND senote.crmid = ".$id."
                    GROUP BY notes.notesid";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $notesid = $adb->query_result($result_query, $i, 'notesid');
            $notesid = html_entity_decode(strip_tags($notesid), ENT_QUOTES,$default_charset);

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $notesid;

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$notesid;

            }

        }

        $lista_elementi_gia_relazionati .= ")";
        
        $query = "SELECT attac.attachmentsid attachmentsid, 
                    attac.name name, 
                    attac.path path, 
                    notes.title title, 
                    notes.notesid notesid, 
                    notes.folderid cartella_id,
                    date(ent.createdtime) data_documento,
                    ent.createdtime createdtime, 
                    ent.modifiedtime modifiedtime 
                    FROM {$table_prefix}_notes notes 
                    INNER JOIN {$table_prefix}_notescf notescf ON notescf.notesid = notes.notesid 
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_senotesrel senote ON senote.notesid = notes.notesid
                    LEFT JOIN {$table_prefix}_seattachmentsrel seattac ON seattac.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_attachments attac ON attac.attachmentsid = seattac.attachmentsid 
                    WHERE ent.deleted = 0 AND notes.folderid = 41";

        if($lista_elementi_gia_relazionati != "()"){

            $query .= " AND notes.notesid NOT IN ".$lista_elementi_gia_relazionati;

        }
                        
        if($filtro["nome_documento"] != ""){
            $query .= " AND notes.title like '%".$filtro["nome_documento"]."%'";
        }

        $query .= " GROUP BY notes.notesid
                    ORDER BY ent.createdtime DESC";

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
                continue;
            }
            
            $attachmentsid = $adb->query_result($result_query, $i, 'attachmentsid');
            $attachmentsid = html_entity_decode(strip_tags($attachmentsid), ENT_QUOTES,$default_charset);
            
            $createdtime = $adb->query_result($result_query, $i, 'createdtime');
            $createdtime = html_entity_decode(strip_tags($createdtime), ENT_QUOTES,$default_charset);
            
            $modifiedtime = $adb->query_result($result_query, $i, 'modifiedtime');
            $modifiedtime = html_entity_decode(strip_tags($modifiedtime), ENT_QUOTES,$default_charset);
            
            $data_documento = $adb->query_result($result_query, $i, 'data_documento');
            $data_documento = html_entity_decode(strip_tags($data_documento), ENT_QUOTES,$default_charset);
            if($data_documento != null && $data_documento != ""){
                list($anno, $mese, $giorno) = explode("-", $data_documento);
                $data_documento = date("d/m/Y", mktime(0, 0, 0, $mese, $giorno, $anno));
            }
            
            $result[] = array('notesid' => $notesid,
                                'attachmentsid' => $attachmentsid,
                                'title' => $title,
                                'createdtime' => $createdtime,
                                'modifiedtime' => $modifiedtime,
                                'data_documento' => $data_documento);
            
        }

        return $result;

    }

    static function getDocumentoElementoProcedura($id, $documento){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    notes.notesid notesid
                    FROM {$table_prefix}_notes notes 
                    INNER JOIN {$table_prefix}_notescf notescf ON notescf.notesid = notes.notesid 
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_senotesrel senote ON senote.notesid = notes.notesid
                    LEFT JOIN {$table_prefix}_seattachmentsrel seattac ON seattac.crmid = notes.notesid 
                    LEFT JOIN {$table_prefix}_attachments attac ON attac.attachmentsid = seattac.attachmentsid 
                    WHERE ent.deleted = 0 AND senote.crmid = ".$id." AND notes.notesid = ".$documento;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $esiste = true;

            $notesid = $adb->query_result($result_query, 0, 'notesid');
            $notesid = html_entity_decode(strip_tags($notesid), ENT_QUOTES,$default_charset);

        }
        else{

            $esiste = false;

            $notesid = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $notesid);

        return $result;

    }

    static function setLinkDocumentoElementoProcedura($id, $documento){
        global $adb, $table_prefix, $default_charset;

        $dati_documento = self::getDocumentoElementoProcedura($id, $documento);

        if( !$dati_documento["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_senotesrel (crmid, notesid, relmodule) VALUES
						(".$id.", ".$documento.", 'KpEntitaProcedure')";
            $adb->query($insert);

            $focus_documento = CRMEntity::getInstance('Documents');
            $focus_documento->retrieve_entity_info($documento, "Documents", $dieOnError=false); 

            $nome_documento = $focus_documento->column_fields["notes_title"];
            $nome_documento = html_entity_decode(strip_tags($nome_documento), ENT_QUOTES, $default_charset);

            $dati_elemento = self::getElementoProceduraById($id);

            $text = "Aggiunto documento ".$nome_documento." a ".$dati_elemento["nome"];

            self::setLogRevisione($dati_elemento["procedura"], $text);

        }

    }

    static function setLinkDocumentoProcedura($id, $documento){
        global $adb, $table_prefix, $default_charset;

        $dati_documento = self::getDocumentoElementoProcedura($id, $documento);

        if( !$dati_documento["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_senotesrel (crmid, notesid, relmodule) VALUES
						(".$id.", ".$documento.", 'KpProcedure')";
            $adb->query($insert);
            
            $focus_documento = CRMEntity::getInstance('Documents');
            $focus_documento->retrieve_entity_info($documento, "Documents", $dieOnError=false); 

            $nome_documento = $focus_documento->column_fields["notes_title"];
            $nome_documento = html_entity_decode(strip_tags($nome_documento), ENT_QUOTES, $default_charset);

            $text = "Aggiunto documento ".$nome_documento." al processo in questione";

            self::setLogRevisione($id, $text);

        }

    }

    static function removeLinkDocumentoElementoProcedura($id, $documento){
        global $adb, $table_prefix, $default_charset;

        $dati_documento = self::getDocumentoElementoProcedura($id, $documento);

        if( $dati_documento["esiste"] ){
            
            $delete = "DELETE FROM {$table_prefix}_senotesrel
                        WHERE crmid = ".$id." AND notesid = ".$documento;
            $adb->query($delete);

        }

    }

    static function getProcessi($filtro){
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

            $nome = self::encodeString($nome);

            if( self::proceduraAbilitataPerAzienda($id, $filtro["azienda"]) || $filtro["azienda"] == 0 ){

                if( self::proceduraAbilitataPerStabilimento($id, $filtro["stabilimento"]) || $filtro["stabilimento"] == 0 ){

                    $lista_sottoprocessi = array();

                    $lista_sottoprocessi = self::getElementiProcedura($id, array("only_sottoprocesso" => true));

                    $result[] = array("id" => $id,
                                        "nome" => $nome,
                                        "lista_sottoprocessi" => $lista_sottoprocessi);

                }

            }

        }

        return $result;

    }

    static function encodeString($string){
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

        return $string;

    }

    static function proceduraAbilitataPerAzienda($id, $azienda){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        if( self::presentiAziendeRelazionate($id) ){

            if( self::relazionataAdAzienda($id, $azienda)){

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

    static function proceduraAbilitataPerStabilimento($id, $stabilimento){
        global $adb, $table_prefix, $current_user, $site_URL, $default_charset;

        $result = false;

        if( self::presentiStabilimentiRelazionati($id) ){

            if( self::relazionataAStabilimento($id, $stabilimento)){

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

    static function presentiAziendeRelazionate($id){
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

    static function presentiStabilimentiRelazionati($id){
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

    static function relazionataAdAzienda($id, $azienda){
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

    static function relazionataAStabilimento($id, $stabilimento){
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

    static function getAlberoProcessi($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $lista_tipi_procedure = self::getTipiProcedure($filtro["azienda"], $filtro["stabilimento"]);

        foreach( $lista_tipi_procedure as $tipo_procedura ){

            $filtro_processi = array("nome_processo" => "",
                                    "tipo_procedura" => $tipo_procedura["tipo_procedura"],
                                    "primario" => true,
                                    "azienda" => $filtro["azienda"],
                                    "stabilimento" => $filtro["stabilimento"]);

            $lista_procedure = self::getProcessi($filtro_processi);

            $result[] = array("tipo_procedura" => $tipo_procedura["tipo_procedura"],
                                "lista_procedure" => $lista_procedure);

        }

        return $result;

    }

    static function getElementiProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

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

        $query .= " ORDER BY entproc.kp_order ASC";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'kpentitaprocedureid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'kp_nome_entita');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $nome = self::encodeString($nome);

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

                $lista_sottoprocessi = self::getElementiProcedura($procedureid, array("only_sottoprocesso" => true));  

            }

            if( $procedureid != 0 && $numero_procedura != null && $numero_procedura != '' ){
                $nome = $numero_procedura." - ".$nome;
            }

            $nome = self::encodeString($nome);
            $nome_procedura = self::encodeString($nome_procedura);

            $valore_aggiunto = $adb->query_result($result_query, $i, 'kp_valore_aggiunto');
            $valore_aggiunto = html_entity_decode(strip_tags($valore_aggiunto), ENT_QUOTES, $default_charset);
            if($valore_aggiunto == null){
                $valore_aggiunto = "";
            }

            $description = $adb->query_result($result_query, $i, 'description');
            $description = html_entity_decode(strip_tags($description), ENT_QUOTES, $default_charset);

            $description = self::encodeString($description);

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

    static function getTipiProcedure($azienda = "", $stabilimento = ""){
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

            if( $azienda != "" || $stabilimento != "" ){

                $filtro_processi = array("nome_processo" => "",
                                        "tipo_procedura" => $tipo_procedura,
                                        "primario" => true,
                                        "azienda" => $azienda,
                                        "stabilimento" => $stabilimento);

                $lista_procedure = self::getProcessi($filtro_processi);

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

    static function getProcesso($id){
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

            $settings_procedure = self::getSettingsProcedure();

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

    static function setLinkProcessoElementoProcedura($elemento_id, $processo_id){
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

    static function unsetLinkProcessoElementoProcedura($elemento_id){
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

    static function getRuoliElementoProcedura($id, $filtro){
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

    static function getRuoliAssociabiliAElementoProcedura($id, $filtro){
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

    static function setLinkRuoloElementoProcedura($id, $ruolo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $dati_ruolo = self::getRuoloElementoProcedura($id, $ruolo);

        if( !$dati_ruolo["esiste"] ){

            $focus_ruolo = CRMEntity::getInstance('KpRuoli');
            $focus_ruolo->retrieve_entity_info($ruolo, "KpRuoli", $dieOnError=false); 

            $nome_ruolo = $focus_ruolo->column_fields["kp_nome_ruolo"];
            $nome_ruolo = html_entity_decode(strip_tags($nome_ruolo), ENT_QUOTES, $default_charset);

            $dati_elemento = self::getElementoProceduraById($id);

            $soggetto = $nome_ruolo." - ".$dati_elemento["nome"];

            $focus = CRMEntity::getInstance('KpRuoliAttivita');
            $focus->column_fields['assigned_user_id'] = $current_user->id;
            $focus->column_fields['kp_soggetto'] = $soggetto;
            $focus->column_fields['kp_attivita'] = $id;
            $focus->column_fields['kp_ruolo'] = $ruolo;
            $focus->column_fields['kp_resp_ruolo'] = "Esecutore"; 
            $focus->save('KpRuoliAttivita', $longdesc = true, $offline_update = false, $triggerEvent = false);

            $text = "Assegnata attivita' ".$dati_elemento["nome"]." al ruolo ".$nome_ruolo;

            self::setLogRevisione($dati_elemento["procedura"], $text);

        }

    }

    static function getRuoloElementoProcedura($id, $ruolo){
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

    static function unsetRuoloElementoProcedura($id, $ruolo){
        global $adb, $table_prefix, $default_charset;
        
        $dati_ruolo = self::getRuoloElementoProcedura($id, $ruolo);
        
        if( $dati_ruolo["esiste"] ){

            $update = "UPDATE {$table_prefix}_crmentity SET
                        deleted  = 1
                        WHERE setype = 'KpRuoliAttivita' AND crmid = ".$dati_ruolo["id"];
            $adb->query($update);
            
            $focus_ruolo = CRMEntity::getInstance('KpRuoli');
            $focus_ruolo->retrieve_entity_info($ruolo, "KpRuoli", $dieOnError=false); 

            $nome_ruolo = $focus_ruolo->column_fields["kp_nome_ruolo"];
            $nome_ruolo = html_entity_decode(strip_tags($nome_ruolo), ENT_QUOTES, $default_charset);

            $dati_elemento = self::getElementoProceduraById($id);

            $text = "Rimosso ruolo ".$nome_ruolo." dall'attivita' ".$dati_elemento["nome"];

            self::setLogRevisione($dati_elemento["procedura"], $text);

        }

    }

    static function setDescrizioneElementoProcedura($id, $descrizione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->retrieve_entity_info($id, "KpEntitaProcedure");

        foreach($focus->column_fields as $fieldname => $value) {
            $focus->column_fields[$fieldname] = decode_html($value);
        }

        $focus->column_fields['description'] = $descrizione;
        $focus->mode = 'edit';
        $focus->id = $id;
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

        $nome_entita = $focus->column_fields["kp_nome_entita"];
        $procedura = $focus->column_fields["kp_procedura"];

        $text = "Modificata descrizione dell'attivita' ".$nome_entita;

        //self::setLogRevisione($procedura, $text);

    }

    static function setValoreAggiuntoElementoProcedura($id, $valore){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->retrieve_entity_info($id, "KpEntitaProcedure");

        foreach($focus->column_fields as $fieldname => $value) {
            $focus->column_fields[$fieldname] = decode_html($value);
        }

        $focus->column_fields['kp_valore_aggiunto'] = $valore;
        $focus->mode = 'edit';
        $focus->id = $id;
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

    }

    static function getListaAziende($filtro){
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

    static function getListaStabilimenti($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT
                    stab.stabilimentiid stabilimentiid,
                    stab.nome_stabilimento nome_stabilimento,
                    acc.accountid accountid,
                    acc.accountname accountname,
                    stab.citta citta
                    FROM {$table_prefix}_stabilimenti stab
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = stab.azienda
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = stab.stabilimentiid
                    INNER JOIN {$table_prefix}_crmentity ent2 ON ent2.crmid = acc.accountid
                    WHERE ent.deleted = 0 AND ent2.deleted = 0";

        if($filtro["nome"] != ""){
            $query .= " AND stab.nome_stabilimento LIKE '%".$filtro["nome"]."%'";
        }

        if($filtro["azienda"] != ""){
            $query .= " AND acc.accountname LIKE '%".$filtro["azienda"]."%'";
        }

        if($filtro["citta"] != ""){
            $query .= " AND stab.citta LIKE '%".$filtro["citta"]."%'";
        }

        $query .= " ORDER BY stab.nome_stabilimento ASC
                    LIMIT 0, 100";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'stabilimentiid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

            $nome = $adb->query_result($result_query, $i, 'nome_stabilimento');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $azienda_id = $adb->query_result($result_query, $i, 'accountid');
            $azienda_id = html_entity_decode(strip_tags($azienda_id), ENT_QUOTES,$default_charset);

            $azienda = $adb->query_result($result_query, $i, 'accountname');
            $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES,$default_charset);

            $citta = $adb->query_result($result_query, $i, 'citta');
            $citta = html_entity_decode(strip_tags($citta), ENT_QUOTES,$default_charset);

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "azienda_id" => $azienda_id,
                                "azienda" => $azienda,
                                "citta" => $citta); 

        }

        return $result;

    }

    static function getSettingsProcedure(){
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

    static function creaRevisioneBPMN($procedura, $descrizione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $data_corrente = date("Y-m-d");

        $data_formattata = new DateTime($data_corrente);
        $data_formattata = $data_formattata->format('d/m/Y');

        $dati_nuova_procedura = self::getProcesso($procedura);

        $numero_revisione = $dati_nuova_procedura["numero_revisione"];
        $nuovo_bpmn_xml = $dati_nuova_procedura["bpmn_xml"];
        
        if( $dati_nuova_procedura["revisione_di"] != 0 && $dati_nuova_procedura["revisione_di"] != "" ){

            $dati_vecchia_procedura = self::getProcesso($dati_nuova_procedura["revisione_di"]);
            $vecchio_bpmn_xml = $dati_vecchia_procedura["bpmn_xml"];
            
            $update = "UPDATE {$table_prefix}_kpprocedure SET
                        kp_stato_procedura = 'Revisionato',
                        kp_rev_in_data = '".$data_corrente."'
                        WHERE kpprocedureid = ".$dati_nuova_procedura["revisione_di"];
            $adb->query($update);

            $update = "UPDATE {$table_prefix}_kpentitaprocedure SET
                        kp_relazionato_a_id = ".$procedura."
                        WHERE kp_relazionato_a_id = ".$dati_nuova_procedura["revisione_di"];
            
            $adb->query($update);

            self::riportaDatiRilevazioni($dati_nuova_procedura["revisione_di"], $procedura);
            self::riportaMisureMigliorative($dati_nuova_procedura["revisione_di"], $procedura);
            self::riportaNonConformita($dati_nuova_procedura["revisione_di"], $procedura);

        }
        else{

            $vecchio_bpmn_xml = "";

        }

        $update = "UPDATE {$table_prefix}_kpprocedure SET
                    kp_stato_procedura = 'Attivo',
                    kp_data_procedura = '".$data_corrente."',
                    kp_data_revisione = '".$data_corrente."'
                    WHERE kpprocedureid = ".$procedura;
        $adb->query($update);

        $numero_revisione_str = str_pad($numero_revisione, 3, "0", STR_PAD_LEFT);

		$nome_revisione = "Rev. ".$numero_revisione_str." del ".$data_formattata;

        $focus = CRMEntity::getInstance('KpRevisioniProcedure');
        $focus->column_fields['assigned_user_id'] = $current_user->id;
        $focus->column_fields['kp_nome_revisione'] = $nome_revisione;
        $focus->column_fields['kp_procedura'] = $procedura;
        $focus->column_fields['description'] = $descrizione; 
        $focus->column_fields['kp_data_revisione'] = $data_corrente; 
        $focus->column_fields['kp_numero_revisione'] = $numero_revisione;
        $focus->column_fields['kp_vecchio_bpmn_xml'] = $vecchio_bpmn_xml; 
        $focus->column_fields['kp_nuovo_bpmn_xml'] = $nuovo_bpmn_xml; 
        $focus->save('KpRevisioniProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

    }

    static function getRevisioneProceduraById($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = "";

        $query = "SELECT 
                    rev.kp_nome_revisione kp_nome_revisione,
                    rev.kp_numero_revisione kp_numero_revisione,
                    rev.kp_data_revisione kp_data_revisione,
                    rev.kp_procedura kp_procedura,
                    rev.kp_vecchio_bpmn_xml kp_vecchio_bpmn_xml,
                    rev.kp_nuovo_bpmn_xml kp_nuovo_bpmn_xml,
                    rev.description description
                    FROM {$table_prefix}_kprevisioniprocedure rev
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rev.kprevisioniprocedureid
                    WHERE ent.deleted = 0 AND rev.kprevisioniprocedureid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $nome = $adb->query_result($result_query, 0, 'kp_nome_revisione');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset); 

            $numero = $adb->query_result($result_query, 0, 'kp_numero_revisione');
            $numero = html_entity_decode(strip_tags($numero), ENT_QUOTES,$default_charset); 

            $data = $adb->query_result($result_query, 0, 'kp_data_revisione');
            $data = html_entity_decode(strip_tags($data), ENT_QUOTES,$default_charset); 

            $procedura = $adb->query_result($result_query, 0, 'kp_procedura');
            $procedura = html_entity_decode(strip_tags($procedura), ENT_QUOTES,$default_charset); 

            $vecchio_bpmn_xml = $adb->query_result($result_query, 0, 'kp_vecchio_bpmn_xml');
            $vecchio_bpmn_xml = html_entity_decode(strip_tags($vecchio_bpmn_xml), ENT_QUOTES,$default_charset);   
            if($vecchio_bpmn_xml == null){
                $vecchio_bpmn_xml = "";
            }

            $nuovo_bpmn_xml = $adb->query_result($result_query, 0, 'kp_nuovo_bpmn_xml');
            $nuovo_bpmn_xml = html_entity_decode(strip_tags($nuovo_bpmn_xml), ENT_QUOTES,$default_charset);   
            if($nuovo_bpmn_xml == null){
                $nuovo_bpmn_xml = "";
            }

            $descrizione = $adb->query_result($result_query, 0, 'description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES,$default_charset); 

        }
        else{

            $nome = "";
            $numero = "";
            $data = "";
            $procedura = 0;
            $vecchio_bpmn_xml = "";
            $nuovo_bpmn_xml = "";
            $descrizione = "";

        }

        $result = array("id" => $id,
                        "nome" => $nome,
                        "numero" => $numero,
                        "data" => $data,
                        "procedura" => $procedura,
                        "descrizione" => $descrizione,
                        "vecchio_bpmn_xml" => $vecchio_bpmn_xml,
                        "nuovo_bpmn_xml" => $nuovo_bpmn_xml);

        return $result;

    }

    static function getAziendeRelazionateAllaProcedura($procedura){
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

    static function getStabilimentiRelazionatiAllaProcedura($procedura){
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

    static function getRisorseRuolo($ruolo, $array_aziende, $array_stabilimenti){
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

        /*$query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid risorsa,
                    cont.firstname firstname,
                    cont.lastname lastname,
                    cont.accountid accountid,
                    cont.stabilimento stabilimento,
                    ent.deleted deleted
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = rel.relcrmid
                    WHERE rel.crmid = ".$ruolo." AND rel.module = 'KpRuoli' AND rel.relmodule = 'Contacts')
                    UNION 
                    (SELECT 
                    rel.crmid risorsa,
                    cont.firstname firstname,
                    cont.lastname lastname,
                    cont.accountid accountid,
                    cont.stabilimento stabilimento,
                    ent.deleted deleted
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_contactdetails cont ON cont.contactid = rel.crmid
                    WHERE rel.relcrmid = ".$ruolo." AND rel.relmodule = 'KpRuoli' AND rel.module = 'Contacts')) AS t
                    WHERE t.deleted = 0";

        if( $where_aziende != "" ){

            $query .= " AND t.accountid IN ".$where_aziende;

        }

        if( $where_stabilimenti != "" ){

            $query .= " AND t.stabilimento IN ".$where_stabilimenti;

        }

        $query .= " GROUP BY t.risorsa";*/

        //print_r($query);die;

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

    static function gestioneNotificheRevisione($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $risorse_da_notificare = array();

        $dati_revisione = self::getRevisioneProceduraById($id);

        $lista_task = self::getElementiProcedura($dati_revisione["procedura"], array("only_task" => true));

        $lista_aziende_relazionate = self::getAziendeRelazionateAllaProcedura( $dati_revisione["procedura"] );
        $array_aziende = array();
        foreach( $lista_aziende_relazionate as $azienda ){
            $array_aziende[] = $azienda["id"];
        }
        
        $lista_stabilimenti_relazionati = self::getStabilimentiRelazionatiAllaProcedura( $dati_revisione["procedura"] );
        $array_stabilimenti = array();
        foreach( $lista_stabilimenti_relazionati as $stabilimento ){
            $array_stabilimenti[] = $stabilimento["id"];
        }

        //print_r($array_stabilimenti);die;

        foreach( $lista_task as $task ){

            $ruoli_task = self::getRuoliElementoProcedura( $task["id"], array() );

            if( count($ruoli_task) > 0){
                
                foreach( $ruoli_task as $ruolo ){

                    $lista_risorse_ruolo = self::getRisorseRuolo($ruolo["id"], $array_aziende, $array_stabilimenti);

                    //print_r($lista_risorse_ruolo);die;

                    foreach( $lista_risorse_ruolo as $risorsa ){

                        if( !in_array( $risorsa["id"], $risorse_da_notificare ) ){

                            $risorse_da_notificare[] = $risorsa["id"];

                        }

                    }
                    
                }

            }

        }

        //print_r($risorse_da_notificare); die;

        self::generaNotificheRevisione($id, $risorse_da_notificare);
        
    }

    static function getUtenteRisorsa($risorsa){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = "";

        $query = "SELECT 
                    id,
                    user_name,
                    first_name,
                    last_name
                    FROM {$table_prefix}_users
                    WHERE status = 'Active' AND risorsa_collegata = ".$risorsa;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0){

            $id = $adb->query_result($result_query, 0, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

        }
        else{

            $id = 0;

        }

        $result = array("id" => $id);

        return $result;

    }

    static function generaNotificheRevisione($id, $risorse_da_notificare){
        global $adb, $table_prefix, $default_charset, $current_user;

        foreach( $risorse_da_notificare as $risorsa ){

            self::generaNotificaRevisione($id, $risorsa);

        }

    }

    static function generaNotificaRevisione($revisione, $risorsa){
        global $adb, $table_prefix, $default_charset, $current_user;

        $dati_utente = self::getUtenteRisorsa($risorsa);
        if($dati_utente["id"] != 0 && $dati_utente["id"] != ""){

            $dati_notifica = self::getNotificaRevisioneRisorsa($revisione, $risorsa);
            if( !$dati_notifica["esiste"] ){

                $focus_revisione = CRMEntity::getInstance('KpRevisioniProcedure');
                $focus_revisione->retrieve_entity_info($revisione, "KpRevisioniProcedure"); 

                $focus_procedura = CRMEntity::getInstance('KpProcedure');
                $focus_procedura->retrieve_entity_info($focus_revisione->column_fields["kp_procedura"], "KpProcedure"); 

                $numero_revisione = $focus_revisione->column_fields["kp_numero_revisione"];
                $numero_revisione_str = str_pad($numero_revisione, 3, "0", STR_PAD_LEFT);

                $nome_notifica = "Rev. ".$numero_revisione_str." - Procedura: ".$focus_procedura->column_fields["kp_nome_procedura"];

                $focus = CRMEntity::getInstance('KpNotificheRevProc');
                $focus->column_fields['assigned_user_id'] = $dati_utente["id"];
                $focus->column_fields['kp_soggetto'] = $nome_notifica;
                $focus->column_fields['kp_rev_procedura'] = $revisione;
                $focus->column_fields['kp_risorsa'] = $risorsa;
                $focus->column_fields['description'] = $focus_revisione->column_fields["description"];
                $focus->column_fields['kp_stato_notifica_r'] = "Eseguita notifica";
                $focus->save('KpNotificheRevProc', $longdesc = true, $offline_update = false, $triggerEvent = true);

            }

        }

    }

    static function getNotificaRevisioneRisorsa($revisione, $risorsa){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = "";

        $query = "SELECT 
                    notf.kpnotificherevprocid kpnotificherevprocid,
                    notf.kp_soggetto kp_soggetto,
                    notf.kp_rev_procedura kp_rev_procedura,
                    notf.kp_procedura kp_procedura,
                    notf.kp_data_notifica kp_data_notifica,
                    notf.kp_data_visione kp_data_visione,
                    notf.kp_stato_notifica_r kp_stato_notifica_r,
                    notf.kp_risorsa kp_risorsa
                    FROM {$table_prefix}_kpnotificherevproc notf
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notf.kpnotificherevprocid
                    WHERE ent.deleted = 0 AND notf.kp_rev_procedura = ".$revisione." AND notf.kp_risorsa = ".$risorsa;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'kpnotificherevprocid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

        }
        else{

            $esiste = false;

            $id = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $id);

        return $result;

    }

    static function getAlberoNotifiche($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $lista_notifiche_eseguite = array();

        $lista_notifiche_visionate = array();

        $lista_date_notifiche_eseguite = self::getDateNotificheUtente($filtro["utente"], "Eseguita notifica");

        $lista_date_notifiche_visionate = self::getDateNotificheUtente($filtro["utente"], "Confermata visione");

        foreach( $lista_date_notifiche_eseguite as $data_notifica ){

            $filtro_notifiche = array("data_notifica" => $data_notifica,
                                        "stato" => "Eseguita notifica",
                                        "utente" => $filtro["utente"]);

            $lista_notifiche = self::getNotificheUtentePerData($filtro_notifiche);

            $data_notifica = new DateTime($data_notifica);
            $data_notifica = $data_notifica->format("d/m/Y");

            $lista_notifiche_eseguite[] = array("data_notifica" => $data_notifica,
                                                "lista_notifiche" => $lista_notifiche);


        }

        foreach( $lista_date_notifiche_visionate as $data_notifica ){

            $filtro_notifiche = array("data_notifica" => $data_notifica,
                                        "stato" => "Confermata visione",
                                        "utente" => $filtro["utente"]);

            $lista_notifiche = self::getNotificheUtentePerData($filtro_notifiche);

            $data_notifica = new DateTime($data_notifica);
            $data_notifica = $data_notifica->format("d/m/Y");

            $lista_notifiche_visionate[] = array("data_notifica" => $data_notifica,
                                                "lista_notifiche" => $lista_notifiche);
                                                

        }

        $result = array("eseguita_notifica" => $lista_notifiche_eseguite,
                        "confermata_visione" => $lista_notifiche_visionate);

        return $result;

    }

    static function getDateNotificheUtente($utente, $stato){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT 
                    notf.kp_data_notifica kp_data_notifica
                    FROM {$table_prefix}_kpnotificherevproc notf
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notf.kpnotificherevprocid
                    WHERE ent.deleted = 0";

        if($utente != ""){
            $query .= " AND ent.smownerid = ".$utente;
        }

        if($stato!= ""){
            $query .= " AND notf.kp_stato_notifica_r = '".$stato."'";
        }

        $query .= " GROUP BY notf.kp_data_notifica
                    ORDER BY notf.kp_data_notifica DESC";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $data_notifica = $adb->query_result($result_query, $i, 'kp_data_notifica');
            $data_notifica = html_entity_decode(strip_tags($data_notifica), ENT_QUOTES, $default_charset);

            $result[] = $data_notifica;

        }

        return $result;

    }

    static function getNotificheUtentePerData($filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT 
                    notf.kpnotificherevprocid kpnotificherevprocid,
                    notf.kp_soggetto kp_soggetto,
                    notf.kp_rev_procedura kp_rev_procedura,
                    notf.kp_procedura kp_procedura,
                    notf.kp_data_notifica kp_data_notifica,
                    notf.kp_data_visione kp_data_visione,
                    notf.kp_stato_notifica_r kp_stato_notifica_r,
                    notf.kp_risorsa kp_risorsa
                    FROM {$table_prefix}_kpnotificherevproc notf
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notf.kpnotificherevprocid
                    WHERE ent.deleted = 0";

        if($filtro["utente"] != ""){
            $query .= " AND ent.smownerid = ".$filtro["utente"];
        }

        if($filtro["stato"] != ""){
            $query .= " AND notf.kp_stato_notifica_r = '".$filtro["stato"]."'";
        }

        if($filtro["data_notifica"] != ""){
            $query .= " AND notf.kp_data_notifica = '".$filtro["data_notifica"]."'";
        }

        $query .= " ORDER BY notf.kpnotificherevprocid DESC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i = 0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'kpnotificherevprocid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'kp_soggetto');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $lista_sottoprocessi = array();

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "lista_sottoprocessi" => $lista_sottoprocessi);

        }

        return $result;

    }

    static function getNotificaRevisioneById($id){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    notf.kpnotificherevprocid kpnotificherevprocid,
                    notf.kp_soggetto kp_soggetto,
                    notf.kp_rev_procedura kp_rev_procedura,
                    notf.kp_procedura kp_procedura,
                    notf.kp_data_notifica kp_data_notifica,
                    notf.kp_data_visione kp_data_visione,
                    notf.kp_stato_notifica_r kp_stato_notifica_r,
                    notf.kp_risorsa kp_risorsa,
                    notf.description description
                    FROM {$table_prefix}_kpnotificherevproc notf
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notf.kpnotificherevprocid
                    WHERE notf.kpnotificherevprocid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $id = $adb->query_result($result_query, 0, 'kpnotificherevprocid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, 0, 'kp_soggetto');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $rev_procedura = $adb->query_result($result_query, 0, 'kp_rev_procedura');
            $rev_procedura = html_entity_decode(strip_tags($rev_procedura), ENT_QUOTES, $default_charset);

            $procedura = $adb->query_result($result_query, 0, 'kp_procedura');
            $procedura = html_entity_decode(strip_tags($procedura), ENT_QUOTES, $default_charset);

            $data_notifica = $adb->query_result($result_query, 0, 'kp_data_notifica');
            $data_notifica = html_entity_decode(strip_tags($data_notifica), ENT_QUOTES, $default_charset);

            $descrizione = $adb->query_result($result_query, 0, 'description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);

            $stato_notifica = $adb->query_result($result_query, 0, 'kp_stato_notifica_r');
            $stato_notifica = html_entity_decode(strip_tags($stato_notifica), ENT_QUOTES, $default_charset);

        }
        else{

            $id = 0;
            $nome = "";
            $rev_procedura = 0;
            $procedura = 0;
            $data_notifica = "";
            $descrizione = "";
            $stato_notifica = "";

        }

        $result = array("id" => $id,
                        "nome" => $nome,
                        "data_notifica" => $data_notifica,
                        "descrizione" => $descrizione,
                        "rev_procedura" => $rev_procedura,
                        "procedura" => $procedura,
                        "stato_notifica" => $stato_notifica);
        
        return $result;

    }

    static function setVisioneNotifica($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpNotificheRevProc');
        $focus->retrieve_entity_info($id, "KpNotificheRevProc");
        $focus->column_fields['kp_stato_notifica_r'] = 'Confermata visione';
        $focus->mode = 'edit';
        $focus->id = $id;
        $focus->save('KpNotificheRevProc', $longdesc = true, $offline_update = false, $triggerEvent = false);

    }

    static function getWorkflowProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT 
                    rel.relcrmid workflow,
                    proc.name workflow_name,
                    proc.description workflow_description,
                    proc.xml workflow_xml
                    FROM kp_relazioni rel
                    INNER JOIN {$table_prefix}_processmaker proc ON proc.id = rel.relcrmid
                    WHERE rel.module = 'KpProcedure' AND rel.relmodule = 'ProcessMaker' AND rel.crmid = ".$id;
                        
        if($filtro["nome_workflow"] != ""){
            $query .= " AND proc.name like '%".$filtro["nome_workflow"]."%'";
        }

        $query .= " GROUP BY rel.relcrmid
                    ORDER BY proc.name ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'workflow');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'workflow_name');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $descrizione = $adb->query_result($result_query, $i, 'workflow_description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);

            $xml = $adb->query_result($result_query, $i, 'workflow_xml');
            $xml = html_entity_decode(strip_tags($xml), ENT_QUOTES, $default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'descrizione' => $descrizione,
                                'xml' => $xml);
            
        }

        return $result;

    }

    static function getWorkflowAssociabiliAProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $lista_elementi_gia_relazionati = "(";

        $query = "SELECT 
                    rel.relcrmid workflow,
                    proc.name workflow_name,
                    proc.description workflow_description,
                    proc.xml workflow_xml
                    FROM kp_relazioni rel
                    INNER JOIN {$table_prefix}_processmaker proc ON proc.id = rel.relcrmid
                    WHERE rel.module = 'KpProcedure' AND rel.relmodule = 'ProcessMaker' AND rel.crmid = ".$id."
                    GROUP BY rel.relcrmid";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $workflow = $adb->query_result($result_query, $i, 'workflow');
            $workflow = html_entity_decode(strip_tags($workflow), ENT_QUOTES, $default_charset);

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $workflow;

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$workflow;

            }

        }

        $lista_elementi_gia_relazionati .= ")";
        
        $query = "SELECT 
                    proc.id workflow,
                    proc.name workflow_name,
                    proc.description workflow_description,
                    proc.xml workflow_xml
                    FROM {$table_prefix}_processmaker proc";

        $where = "";

        if($lista_elementi_gia_relazionati != "()"){

            $where .= " WHERE proc.id NOT IN ".$lista_elementi_gia_relazionati;

        }
                        
        if($filtro["nome_workflow"] != ""){

            if( $where == "" ){
                $where .= " WHERE proc.name like '%".$filtro["nome_workflow"]."%'";
            }
            else{
                $where .= " AND proc.name like '%".$filtro["nome_workflow"]."%'";
            }

        }

        if( $where != "" ){
            $query .= $where;
        }

        $query .= " GROUP BY proc.id
                    ORDER BY proc.name ASC";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'workflow');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'workflow_name');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $descrizione = $adb->query_result($result_query, $i, 'workflow_description');
            $descrizione = html_entity_decode(strip_tags($descrizione), ENT_QUOTES, $default_charset);

            $xml = $adb->query_result($result_query, $i, 'workflow_xml');
            $xml = html_entity_decode(strip_tags($xml), ENT_QUOTES, $default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'descrizione' => $descrizione,
                                'xml' => $xml);
            
        }

        return $result;

    }

    static function setLinkWorkflowProcedura($id, $workflow){
        global $adb, $table_prefix, $default_charset;

        $dati_workflow = self::getWorkflowElementoProcedura($id, $workflow);

        if( !$dati_workflow["esiste"] ){

            $insert = "INSERT INTO kp_relazioni (crmid, module, relcrmid, relmodule) VALUES
						(".$id.", 'KpProcedure', ".$workflow.", 'ProcessMaker')";
		    $adb->query($insert);

        }

    }

    static function getWorkflowElementoProcedura($id, $workflow){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT 
                    rel.relcrmid workflow
                    FROM kp_relazioni rel
                    INNER JOIN {$table_prefix}_processmaker proc ON proc.id = rel.relcrmid
                    WHERE rel.relmodule = 'ProcessMaker' AND rel.crmid = ".$id." AND rel.relcrmid = ".$workflow;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $esiste = true;

            $workflow = $adb->query_result($result_query, 0, 'workflow');
            $workflow = html_entity_decode(strip_tags($workflow), ENT_QUOTES,$default_charset);

        }
        else{

            $esiste = false;

            $workflow = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $workflow);

        return $result;

    }

    static function unsetWorkflowElementoProcedura($id, $workflow){
        global $adb, $table_prefix, $default_charset;
        
        $dati_workflow = self::getWorkflowElementoProcedura($id, $workflow);
        
        if( $dati_workflow["esiste"] ){

            $delete = "DELETE FROM kp_relazioni
                        WHERE  
                        (crmid = ".$id." AND relcrmid = ".$workflow." AND relmodule = 'ProcessMaker')";
		    $adb->query($delete);

        }

    }

    static function getRisorseElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $risorse_task = array();

        $dati_elemento = self::getElementoProceduraById($id);

        if( $filtro["azienda"] != 0 && $filtro["azienda"] != ""){

            $array_aziende = array();
            $array_aziende[] = $filtro["azienda"];

        }
        else{

            $lista_aziende_relazionate = self::getAziendeRelazionateAllaProcedura( $dati_elemento["procedura"] );
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
        
            $lista_stabilimenti_relazionati = self::getStabilimentiRelazionatiAllaProcedura( $dati_elemento["procedura"] );
            $array_stabilimenti = array();
            foreach( $lista_stabilimenti_relazionati as $stabilimento ){
                $array_stabilimenti[] = $stabilimento["id"];
            }

        }

        $ruoli_task = self::getRuoliElementoProcedura( $id, array() );

        if( count($ruoli_task) > 0 ){

            foreach( $ruoli_task as $ruolo ){

                $lista_risorse_ruolo = self::getRisorseRuolo($ruolo["id"], $array_aziende, $array_stabilimenti);

                //print_r($lista_risorse_ruolo);die;

                foreach( $lista_risorse_ruolo as $risorsa ){

                    if( !in_array( $risorsa["id"], $risorse_da_notificare ) ){

                        $risorse_task[] = $risorsa["id"];

                    }

                }

            }

        }
        
        return $risorse_task;

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

    static function getRischiQualitaAssociabiliAElementoProcedura($id, $filtro){
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

    static function getRischiPrivacyAssociabiliAElementoProcedura($id, $filtro){
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

    static function getRischiSicurezzaAssociabiliAElementoProcedura($id, $filtro){
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
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome);
            
        }

        return $result;

    }

    static function setLinkRischioQualitaElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;

        $dati_rischio = self::getRischioQualitaElementoProcedura($id, $rischio);

        if( !$dati_rischio["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$id.", 'KpEntitaProcedure', ".$rischio.", 'KpRischiQualita')";
		    $adb->query($insert);

        }

    }

    static function getRischioQualitaElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischio.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kprischiqualita rischio ON rischio.kprischiqualitaid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpRischiQualita' AND rel.relmodule = 'KpEntitaProcedure' AND rel.crmid = ".$rischio." AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischio.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischiqualita rischio ON rischio.kprischiqualitaid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpRischiQualita' AND rel.module = 'KpEntitaProcedure' AND rel.relcrmid = ".$rischio." AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";

        }

        $result = array('esiste' => $esiste,
                        'id' => $id,
                        'nome' => $nome);

        return $result;

    }

    static function setLinkRischioPrivacyElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;

        $dati_rischio = self::getRischioPrivacyElementoProcedura($id, $rischio);

        if( !$dati_rischio["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$id.", 'KpEntitaProcedure', ".$rischio.", 'KpMinaccePrivacy')";
		    $adb->query($insert);

        }

    }

    static function getRischioPrivacyElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischio.kp_nome_minaccia nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy rischio ON rischio.kpminacceprivacyid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpMinaccePrivacy' AND rel.relmodule = 'KpEntitaProcedure' AND rel.crmid = ".$rischio." AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischio.kp_nome_minaccia nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy rischio ON rischio.kpminacceprivacyid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpMinaccePrivacy' AND rel.module = 'KpEntitaProcedure' AND rel.relcrmid = ".$rischio." AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";

        }

        $result = array('esiste' => $esiste,
                        'id' => $id,
                        'nome' => $nome);

        return $result;

    }

    static function setLinkRischioSicurezzaElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;

        $dati_rischio = self::getRischioSicurezzaElementoProcedura($id, $rischio);

        if( !$dati_rischio["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$id.", 'KpEntitaProcedure', ".$rischio.", 'KpRischiDVR')";
		    $adb->query($insert);

        }

    }

    static function getRischioSicurezzaElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    rischio.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kprischidvr rischio ON rischio.kprischidvrid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpRischiDVR' AND rel.relmodule = 'KpEntitaProcedure' AND rel.crmid = ".$rischio." AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    rischio.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischidvr rischio ON rischio.kprischidvrid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpRischiDVR' AND rel.module = 'KpEntitaProcedure' AND rel.relcrmid = ".$rischio." AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";

        }

        $result = array('esiste' => $esiste,
                        'id' => $id,
                        'nome' => $nome);

        return $result;

    }

    static function getRischiQualitaElementoProcedura($id, $filtro){
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

    static function getRischiPrivacyElementoProcedura($id, $filtro){
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
                                'tipo' => 'Privacy');
            
        }

        return $result;

    }

    static function getRischiSicurezzaElementoProcedura($id, $filtro){
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

    static function unsetRischioQualitaElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;
        
        $dati_ruolo = self::getRischioQualitaElementoProcedura($id, $rischio);
        
        if( $dati_ruolo["esiste"] ){

            $delete = "DELETE FROM {$table_prefix}_crmentityrel
                        WHERE  
                        (crmid = ".$id." AND module = 'KpEntitaProcedure' AND relcrmid = ".$rischio." AND relmodule = 'KpRischiQualita')
                        OR
                        (crmid = ".$rischio." AND module = 'KpRischiQualita' AND relcrmid = ".$id." AND relmodule = 'KpEntitaProcedure')";
		    $adb->query($delete);

        }

    }

    static function unsetRischioPrivacyElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;
        
        $dati_ruolo = self::getRischioPrivacyElementoProcedura($id, $rischio);
        
        if( $dati_ruolo["esiste"] ){

            $delete = "DELETE FROM {$table_prefix}_crmentityrel
                        WHERE  
                        (crmid = ".$id." AND module = 'KpEntitaProcedure' AND relcrmid = ".$rischio." AND relmodule = 'KpMinaccePrivacy')
                        OR
                        (crmid = ".$rischio." AND module = 'KpMinaccePrivacy' AND relcrmid = ".$id." AND relmodule = 'KpEntitaProcedure')";
		    $adb->query($delete);

        }

    }

    static function unsetRischioSicurezzaElementoProcedura($id, $rischio){
        global $adb, $table_prefix, $default_charset;
        
        $dati_ruolo = self::getRischioSicurezzaElementoProcedura($id, $rischio);
        
        if( $dati_ruolo["esiste"] ){

            $delete = "DELETE FROM {$table_prefix}_crmentityrel
                        WHERE  
                        (crmid = ".$id." AND module = 'KpEntitaProcedure' AND relcrmid = ".$rischio." AND relmodule = 'KpRischiDVR')
                        OR
                        (crmid = ".$rischio." AND module = 'KpRischiDVR' AND relcrmid = ".$id." AND relmodule = 'KpEntitaProcedure')";
		    $adb->query($delete);

        }

    }

    static function creaSottoprocesso($id_elemento){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = 0;

        $dati_elemento = self::getElementoProceduraById($id_elemento);

        if( $dati_elemento["esiste"] ){

            $nome_sottoprocesso = $dati_elemento["nome"];

            $processo_padre = $dati_elemento["procedura"];

            $dati_processo_padre = self::getProcesso($processo_padre);

            $focus = CRMEntity::getInstance('KpProcedure');
            $focus->column_fields['assigned_user_id'] = $current_user->id;
            $focus->column_fields['kp_nome_procedura'] = $nome_sottoprocesso;
            $focus->column_fields['kp_tipo_procedura'] = $dati_processo_padre["tipo_procedura"];
            $focus->column_fields['kp_data_procedura'] = date("Y-m-d");
            $focus->column_fields['kp_primario'] = '0';
            $focus->column_fields['kp_numero_revisione'] = '0';
            $focus->column_fields['kp_stato_procedura'] = 'Attivo';
            $focus->save('KpProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

            self::setLinkProcessoElementoProcedura($id_elemento, $focus->id);

            $lista_aziende_procedura = self::getAziendeRelazionateAllaProcedura($processo_padre);

            foreach($lista_aziende_procedura  as $azienda){

                self::setLinkAziendaProcedura($focus->id, $azienda["id"]);

            }

            $lista_stabilimenti_procedura = self::getStabilimentiRelazionatiAllaProcedura($processo_padre);

            foreach($lista_stabilimenti_procedura  as $stabilimento){

                self::setLinkStabilimentoProcedura($focus->id, $stabilimento["id"]);

            }

            $result = $focus->id;

        }

        return $result;

    }

    static function setLinkAziendaProcedura($procedura, $azienda){
        global $adb, $table_prefix, $default_charset, $current_user;

        if( !self::relazionataAdAzienda($procedura, $azienda) ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$procedura.", 'KpProcedure', ".$azienda.", 'Accounts')";
            $adb->query($insert);
            
        }

    }

    static function setLinkStabilimentoProcedura($procedura, $stabilimento){
        global $adb, $table_prefix, $default_charset, $current_user;

        if( !self::relazionataAStabilimento($procedura, $stabilimento) ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
                        (".$procedura.", 'KpProcedure', ".$stabilimento.", 'Stabilimenti')";
            $adb->query($insert);

        }

    }

    static function getProcessiAzienda($azienda = 0, $solo_primari = false){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $data_corrente = date("Y-m-d");

        $query = "SELECT t.* FROM
                    ((SELECT 
                    proc.kpprocedureid id,
                    proc.kp_nome_procedura nome,
                    proc.kp_tipo_procedura tipo_procedura,
                    proc.kp_primario primario,
                    proc.kp_scadenza_procedura scadenza_procedura,
                    proc.kp_bpmn_xml bpmn_xml,
                    proc.kp_stato_procedura stato_procedura,
                    entrel.relcrmid azienda
                    FROM {$table_prefix}_crmentityrel entrel
                    INNER JOIN {$table_prefix}_kpprocedure proc ON proc.kpprocedureid = entrel.crmid
                    INNER JOIN {$table_prefix}_crmentity entproc ON entproc.crmid = proc.kpprocedureid
                    WHERE entproc.deleted = 0 AND entrel.module = 'KpProcedure' AND entrel.relmodule = 'Accounts')
                    UNION
                    (SELECT 
                    proc.kpprocedureid id,
                    proc.kp_nome_procedura nome,
                    proc.kp_tipo_procedura tipo_procedura,
                    proc.kp_primario primario,
                    proc.kp_scadenza_procedura scadenza_procedura,
                    proc.kp_bpmn_xml bpmn_xml,
                    proc.kp_stato_procedura stato_procedura,
                    entrel.crmid azienda 
                    FROM {$table_prefix}_crmentityrel entrel
                    INNER JOIN {$table_prefix}_kpprocedure proc ON proc.kpprocedureid = entrel.relcrmid
                    INNER JOIN {$table_prefix}_crmentity entproc ON entproc.crmid = proc.kpprocedureid
                    WHERE entproc.deleted = 0 AND entrel.relmodule = 'KpProcedure' AND entrel.module = 'Accounts')) AS t
                    WHERE (t.scadenza_procedura IS NULL OR t.scadenza_procedura = '' OR t.scadenza_procedura = '0000-00-00' OR t.scadenza_procedura >= '".$data_corrente."')";

        if( $azienda != 0 ){
            $query .= " AND (t.azienda = ".$azienda." OR t.azienda = 0 OR t.azienda = '' OR t.azienda IS NULL)";
        }

        if( $solo_primari ){

            $query .= " AND t.primario = '1'";

        }

        $query .= " AND t.stato_procedura = 'Attivo'";

        $query .= " GROUP BY t.id
                    ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);

            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $tipo_procedura = $adb->query_result($result_query, $i, 'tipo_procedura');
            $tipo_procedura = html_entity_decode(strip_tags($tipo_procedura), ENT_QUOTES,$default_charset);

            $bpmn_xml = $adb->query_result($result_query, $i, 'bpmn_xml');
            $bpmn_xml = html_entity_decode(strip_tags($bpmn_xml), ENT_QUOTES,$default_charset);

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "tipo_procedura" => $tipo_procedura,
                                "bpmn_xml" => $bpmn_xml);

        }

        return $result;

    }

    static function getElementiProceduraRuolo($procedura, $ruolo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT 
                    entproc.kpentitaprocedureid id,
                    entproc.kp_nome_entita nome,
                    ruolat.kp_ruolo ruolo,
                    ruol.kp_nome_ruolo nome_ruolo,
                    ruolat.kp_resp_ruolo responsabilita,
                    entproc.kp_procedura procedura,
                    proc.kp_nome_procedura nome_procedura
                    FROM {$table_prefix}_kpruoliattivita ruolat
                    INNER JOIN {$table_prefix}_crmentity entruolat ON entruolat.crmid = ruolat.kpruoliattivitaid
                    INNER JOIN {$table_prefix}_kpruoli ruol ON ruol.kpruoliid = ruolat.kp_ruolo
                    INNER JOIN {$table_prefix}_kpentitaprocedure entproc ON entproc.kpentitaprocedureid = ruolat.kp_attivita
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                    INNER JOIN {$table_prefix}_kpprocedure proc ON proc.kpprocedureid = entproc.kp_procedura
                    WHERE entruolat.deleted = 0 AND ent.deleted = 0 AND ruol.kpruoliid = ".$ruolo." AND entproc.kp_procedura = ".$procedura;

        $query .= " GROUP BY entproc.kpentitaprocedureid
                    ORDER BY proc.kp_nome_procedura ASC, entproc.kp_order ASC, entproc.kp_nome_entita ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $responsabilita = $adb->query_result($result_query, $i, 'responsabilita');
            $responsabilita = html_entity_decode(strip_tags($responsabilita), ENT_QUOTES, $default_charset);

            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "responsabilita" => $responsabilita);

        }

        return $result;

    }

    static function setRevisione($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $dati_procedura = self::getProcesso($id);

        $focus = CRMEntity::getInstance('KpProcedure');
        $focus->column_fields['assigned_user_id'] = $current_user->id;
        $focus->column_fields['kp_nome_procedura'] = $dati_procedura["nome"];
        $focus->column_fields['kp_numero_procedura'] = $dati_procedura["numero_procedura"];
        $focus->column_fields['kp_tipo_procedura'] = $dati_procedura["tipo_procedura"];
        $focus->column_fields['kp_primario'] = $dati_procedura["primario"];
        $focus->column_fields['kp_numero_revisione'] = (int)$dati_procedura["numero_revisione"] + 1;
        $focus->column_fields['kp_revisione_di'] = $dati_procedura["id"];
        $focus->column_fields['kp_bpmn_xml'] = $dati_procedura["bpmn_xml"];
        if( $dati_procedura["descrizione"] != "" ){
            $focus->column_fields['description'] = $dati_procedura["descrizione"];
        }
        $focus->column_fields['kp_stato_procedura'] = 'In sviluppo';
        $focus->save('KpProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

        $lista_aziende_procedura = self::getAziendeRelazionateAllaProcedura($id);

        foreach($lista_aziende_procedura  as $azienda){

            self::setLinkAziendaProcedura($focus->id, $azienda["id"]);

        }

        $lista_stabilimenti_procedura = self::getStabilimentiRelazionatiAllaProcedura($id);

        foreach($lista_stabilimenti_procedura  as $stabilimento){

            self::setLinkStabilimentoProcedura($focus->id, $stabilimento["id"]);

        }

        self::riportaRelazioniDocumenti($id, $focus->id);

        $lista_task = self::getElementiProcedura($id, array());

        foreach($lista_task as $task){

            self::dupplicaElementoProcedura($task["id"], $focus->id);

        }

        $result = $focus->id;

        return $result;

    }

    static function riportaRelazioniDocumenti($id_origine, $id_destinazione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $lista_relazioni_documento = self::getListaDocumentiRelazionati($id_origine);

        foreach($lista_relazioni_documento as $relazione_documento){

            $query = "SELECT 
                        crmid
                        FROM {$table_prefix}_senotesrel
                        WHERE notesid = ".$relazione_documento['id']." AND crmid = ".$id_destinazione;

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);

            if($num_result == 0){

                $insert = "INSERT INTO {$table_prefix}_senotesrel (crmid, notesid, relmodule)
                            VALUES
                            (".$id_destinazione.", ".$relazione_documento['id'].", '".$relazione_documento['modulo']."')";

                $adb->query($insert);

            }

        }

    }

    static function getListaDocumentiRelazionati($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();
        
        $query = "SELECT 
                    notesid,
                    relmodule
                    FROM {$table_prefix}_senotesrel
                    WHERE crmid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);
        
        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'notesid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $modulo = $adb->query_result($result_query, $i, 'relmodule');
            $modulo = html_entity_decode(strip_tags($modulo), ENT_QUOTES, $default_charset);

            $result[] = array("id" => $id,
                                "modulo" => $modulo);

        }
        
        return $result;

    }

    static function dupplicaElementoProcedura($id, $processo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $dati_elemento = self::getElementoProceduraById($id);

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->column_fields['assigned_user_id'] = $current_user->id;
        $focus->column_fields['kp_procedura'] = $processo;
        $focus->column_fields['kp_bpmn_id'] = $dati_elemento["bpmn_id"];
        $focus->column_fields['kp_nome_entita'] = $dati_elemento["nome"];
        $focus->column_fields['kp_tipo_entita_bpmn'] = $dati_elemento["tipo_entita_bpmn"];
        $focus->column_fields['kp_order'] = $dati_elemento["order"]; 
        $focus->column_fields['valore_aggiunto'] = $dati_elemento["valore_aggiunto"]; 
        $focus->column_fields['kp_relazionato_a_id'] = $dati_elemento["relazionato_a_id"]; 
        $focus->column_fields['kp_revisione_di'] = $id; 
        if($dati_elemento["attivita_dvr"] != 0){
            $focus->column_fields['kp_attivita_dvr'] = $dati_elemento["attivita_dvr"];
        }
        if($dati_elemento["descrizione"] != ""){
            $focus->column_fields['description'] = $dati_elemento["descrizione"];
        }
        $focus->column_fields['kp_aggiornato'] = '1'; 
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

        self::riportaRelazioniDocumenti($id, $focus->id);

        self::riportaRuoliElemento($id, $focus->id);
        self::riportaRelated("KpEntitaProcedure", "KpRischiQualita", $id, $focus->id);
        self::riportaRelated("KpEntitaProcedure", "KpRischiDVR", $id, $focus->id);
        self::riportaRelated("KpEntitaProcedure", "KpMinaccePrivacy", $id, $focus->id);

    }

    static function riportaRuoliElemento($id_origine, $id_destinazione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $lista_ruoli = self::getRuoliElementoProcedura($id_origine, array());

        foreach($lista_ruoli as $ruolo){

            $focus = CRMEntity::getInstance('KpRuoliAttivita');
            $focus->column_fields['assigned_user_id'] = $current_user->id;
            $focus->column_fields['kp_soggetto'] = $ruolo["nome_ruolo_attivita"];
            $focus->column_fields['kp_attivita'] = $id_destinazione;
            $focus->column_fields['kp_ruolo'] = $ruolo["id"];
            $focus->column_fields['kp_resp_ruolo'] = $ruolo["responsabilita"]; 
            $focus->save('KpRuoliAttivita', $longdesc = true, $offline_update = false, $triggerEvent = false);

        }

    }

    static function riportaRelated($modulo_origine, $modulo_relazionato, $id_origine, $id_destinazione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $lista_relazioni = self::getListaRelazioni($modulo_relazionato, $id_origine, $modulo_origine);

        foreach($lista_relazioni as $relazione){

            $query = "(SELECT 
                        rel.relcrmid id
                        FROM {$table_prefix}_crmentityrel rel
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                        WHERE ent.deleted = 0 AND rel.module = '".$modulo_origine."' AND rel.relmodule = '".$modulo_relazionato."' AND rel.crmid = ".$id_destinazione." AND rel.relcrmid = ".$relazione.")
                        UNION
                        (SELECT 
                        rel.crmid id
                        FROM {$table_prefix}_crmentityrel rel
                        INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                        WHERE ent.deleted = 0 AND rel.relmodule = '".$modulo_origine."' AND rel.module = '".$modulo_relazionato."' AND rel.relcrmid = ".$id_destinazione." AND rel.crmid = ".$relazione.")";

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);

            if($num_result == 0){

                $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule)
                            VALUES
                            (".$id_destinazione.", '".$modulo_origine."', ".$relazione.", '".$modulo_relazionato."')";

                $adb->query($insert);

            }

        }

    }

    static function getListaRelazioni($modulo_relazionato, $id, $modulo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "(SELECT 
                    rel.relcrmid id
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.module = '".$modulo."' AND rel.relmodule = '".$modulo_relazionato."' AND rel.crmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.crmid id
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relmodule = '".$modulo."' AND rel.module = '".$modulo_relazionato."' AND rel.relcrmid = ".$id.")";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);
        
        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

            $result[] =  $id;

        }

        return $result;

    }

    static function setLogRevisione($id, $testo){
        global $adb, $table_prefix, $default_charset;

        $testo = date("d/m/Y H:i:s")." - ".$testo;

        $old_log = self::getLogRevisione($id);

        if($old_log != ""){
            $testo = sprintf("%s\n%s", $old_log, $testo);
        }

        $testo = addslashes($testo);

        $update = "UPDATE {$table_prefix}_kpprocedure SET
                    kp_log_revisione = '".$testo."'
                    WHERE kpprocedureid = ".$id;
        $adb->query($update);

    }

    static function getLogRevisione($id){
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

    static function getLogRevisioneNoDate($id){
        global $adb, $table_prefix, $default_charset;

        $log = "";

        $log_old = self::getLogRevisione($id);

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

    static function getTipiAttivitaAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT
                    att.kpattivitadvrid kpattivitadvrid,
                    att.kp_nome_attivita kp_nome_attivita 
                    FROM {$table_prefix}_kpattivitadvr att
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = att.kpattivitadvrid
                    WHERE ent.deleted = 0";
                        
        if($filtro["nome_tipo_attivita"] != ""){
            $query .= " AND att.kp_nome_attivita like '%".$filtro["nome_tipo_attivita"]."%'";
        }

        $query .= " ORDER BY att.kp_nome_attivita ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){
            $id = $adb->query_result($result_query, $i, 'kpattivitadvrid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'kp_nome_attivita');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result[] = array('id' => $id,
                                'nome' => $nome);
            
        }

        return $result;

    }

    static function setLinkTipoAttivitaElementoProcedura($elemento_id, $tipo_attivita_id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->retrieve_entity_info($elemento_id, "KpEntitaProcedure");

        foreach($focus->column_fields as $fieldname => $value) {
            $focus->column_fields[$fieldname] = decode_html($value);
        }

        $focus->column_fields['kp_attivita_dvr'] = $tipo_attivita_id;
        $focus->column_fields['kp_aggiornato'] = '1'; 
        $focus->mode = 'edit';
        $focus->id = $elemento_id;
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

        $lista_rischi_dvr = self::getPericoliDvrRelazionatiAttivita( $tipo_attivita_id );
        foreach( $lista_rischi_dvr as $rischio_dvr ){
            self::setLinkRischioSicurezzaElementoProcedura($elemento_id, $rischio_dvr["id"]);
        }

        $lista_rischi_qualita = self::getRischiQualitaRelazionatiAttivita( $tipo_attivita_id );
        foreach( $lista_rischi_qualita as $rischio_qualita ){
            self::setLinkRischioQualitaElementoProcedura($elemento_id, $rischio_qualita["id"]);
        }

        $lista_rischi_privacy = self::getRischiPrivacyRelazionatiAttivita( $tipo_attivita_id );
        foreach( $lista_rischi_privacy as $rischio_privacy ){
            self::setLinkRischioPrivacyElementoProcedura($elemento_id, $rischio_privacy["id"]);
        }

    }

    static function getPericoliDvrRelazionatiAttivita($attivita){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid id,
                    risch.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischidvr risch ON risch.kprischidvrid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpAttivitaDVR' AND rel.relmodule = 'KpRischiDVR' AND rel.crmid = ".$attivita.")
                    UNION
                    (SELECT 
                    rel.crmid id,
                    risch.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischidvr risch ON risch.kprischidvrid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpAttivitaDVR' AND rel.module = 'KpRischiDVR' AND rel.relcrmid = ".$attivita.")) AS t
                    ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $result[] = array("id" => $id,
                                "nome" => $nome);

        }

        return $result;

    }

    static function getRischiQualitaRelazionatiAttivita($attivita){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid id,
                    risch.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischiqualita risch ON risch.kprischiqualitaid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpAttivitaDVR' AND rel.relmodule = 'KpRischiQualita' AND rel.crmid = ".$attivita.")
                    UNION
                    (SELECT 
                    rel.crmid id,
                    risch.kp_nome_rischio nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kprischiqualita risch ON risch.kprischiqualitaid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpAttivitaDVR' AND rel.module = 'KpRischiQualita' AND rel.relcrmid = ".$attivita.")) AS t
                    ORDER BY t.nome ASC";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $result[] = array("id" => $id,
                                "nome" => $nome);

        }

        return $result;

    }

    static function getRischiPrivacyRelazionatiAttivita($attivita){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid id,
                    risch.kp_nome_minaccia nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy risch ON risch.kpminacceprivacyid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpAttivitaDVR' AND rel.relmodule = 'KpMinaccePrivacy' AND rel.crmid = ".$attivita.")
                    UNION
                    (SELECT 
                    rel.crmid id,
                    risch.kp_nome_minaccia nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpminacceprivacy risch ON risch.kpminacceprivacyid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpAttivitaDVR' AND rel.module = 'KpMinaccePrivacy' AND rel.relcrmid = ".$attivita.")) AS t
                    ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);
            
            $result[] = array("id" => $id,
                                "nome" => $nome);

        }

        return $result;

    }

    static function unsetLinkTipoAttivitaElementoProcedura($elemento_id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $focus = CRMEntity::getInstance('KpEntitaProcedure');
        $focus->retrieve_entity_info($elemento_id, "KpEntitaProcedure");

        foreach($focus->column_fields as $fieldname => $value) {
            $focus->column_fields[$fieldname] = decode_html($value);
        }

        $focus->column_fields['kp_attivita_dvr'] = "";
        $focus->column_fields['kp_aggiornato'] = '1'; 
        $focus->mode = 'edit';
        $focus->id = $elemento_id;
        $focus->save('KpEntitaProcedure', $longdesc = true, $offline_update = false, $triggerEvent = false);

    }

    static function getTipoAttivita($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = "";

        $query = "SELECT
                    att.kpattivitadvrid kpattivitadvrid,
                    att.kp_nome_attivita kp_nome_attivita 
                    FROM {$table_prefix}_kpattivitadvr att
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = att.kpattivitadvrid
                    WHERE ent.deleted = 0 AND att.kpattivitadvrid = ".$id;

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $id = $adb->query_result($result_query, 0, 'kpattivitadvrid');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'kp_nome_attivita');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
            $result = array('id' => $id,
                            'nome' => $nome);
            
        }

        return $result;

    }

    static function getAreeProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid id,
                    aree.kp_nome_area nome,
                    acc.accountname azienda,
                    stab.nome_stabilimento stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.relcrmid
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = aree.kp_azienda
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = aree.kp_stabilimento
                    WHERE ent.deleted = 0 AND rel.module = 'KpProcedure' AND rel.relmodule = 'KpAreeStabilimento' AND rel.crmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.crmid id,
                    aree.kp_nome_area nome,
                    acc.accountname azienda,
                    stab.nome_stabilimento stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.crmid
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = aree.kp_azienda
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = aree.kp_stabilimento
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpProcedure' AND rel.module = 'KpAreeStabilimento' AND rel.relcrmid = ".$id.")) AS t
                    ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $azienda = $adb->query_result($result_query, $i, 'azienda');
            $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES, $default_charset);
            if( $azienda == null ){
                $azienda = "";
            }

            $stabilimento = $adb->query_result($result_query, $i, 'stabilimento');
            $stabilimento = html_entity_decode(strip_tags($stabilimento), ENT_QUOTES, $default_charset);
            if( $stabilimento == null ){
                $stabilimento = "";
            }
            
            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "azienda" => $azienda,
                                "stabilimento" => $stabilimento);

        }

        return $result;

    }

    static function getAreeElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.relcrmid id,
                    aree.kp_nome_area nome,
                    acc.accountname azienda,
                    stab.nome_stabilimento stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.relcrmid
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = aree.kp_azienda
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = aree.kp_stabilimento
                    WHERE ent.deleted = 0 AND rel.module = 'KpEntitaProcedure' AND rel.relmodule = 'KpAreeStabilimento' AND rel.crmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.crmid id,
                    aree.kp_nome_area nome,
                    acc.accountname azienda,
                    stab.nome_stabilimento stabilimento
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.crmid
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = aree.kp_azienda
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = aree.kp_stabilimento
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpEntitaProcedure' AND rel.module = 'KpAreeStabilimento' AND rel.relcrmid = ".$id.")) AS t
                    ORDER BY t.nome ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $azienda = $adb->query_result($result_query, $i, 'azienda');
            $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES, $default_charset);
            if( $azienda == null ){
                $azienda = "";
            }

            $stabilimento = $adb->query_result($result_query, $i, 'stabilimento');
            $stabilimento = html_entity_decode(strip_tags($stabilimento), ENT_QUOTES, $default_charset);
            if( $stabilimento == null ){
                $stabilimento = "";
            }
            
            $result[] = array("id" => $id,
                                "nome" => $nome,
                                "azienda" => $azienda,
                                "stabilimento" => $stabilimento);

        }

        return $result;

    }

    static function unsetAreaProcedura($id, $area){
        global $adb, $table_prefix, $default_charset;
        
        $dati_area = self::getAreaProcedura($id, $area);
        
        if( $dati_area["esiste"] ){

            $delete = "DELETE FROM {$table_prefix}_crmentityrel
                        WHERE  
                        (crmid = ".$id." AND module = 'KpProcedure' AND relcrmid = ".$area." AND relmodule = 'KpAreeStabilimento')
                        OR
                        (crmid = ".$area." AND module = 'KpAreeStabilimento' AND relcrmid = ".$id." AND relmodule = 'KpProcedure')";
		    $adb->query($delete);

        }

    }

    static function unsetAreaElementoProcedura($id, $area){
        global $adb, $table_prefix, $default_charset;
        
        $dati_area = self::getAreaElementoProcedura($id, $area);
        
        if( $dati_area["esiste"] ){

            $delete = "DELETE FROM {$table_prefix}_crmentityrel
                        WHERE  
                        (crmid = ".$id." AND module = 'KpEntitaProcedure' AND relcrmid = ".$area." AND relmodule = 'KpAreeStabilimento')
                        OR
                        (crmid = ".$area." AND module = 'KpAreeStabilimento' AND relcrmid = ".$id." AND relmodule = 'KpEntitaProcedure')";
		    $adb->query($delete);

        }

    }

    static function getAreaProcedura($id, $area){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    aree.kp_nome_area nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpAreeStabilimento' AND rel.relmodule = 'KpProcedure' AND rel.crmid = ".$area." AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    aree.kp_nome_area nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpAreeStabilimento' AND rel.module = 'KpProcedure' AND rel.relcrmid = ".$area." AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";

        }

        $result = array('esiste' => $esiste,
                        'id' => $id,
                        'nome' => $nome);

        return $result;

    }

    static function getAreaElementoProcedura($id, $area){
        global $adb, $table_prefix, $default_charset;

        $result = "";

        $query = "SELECT t.* FROM
                    ((SELECT 
                    rel.crmid rischio,
                    aree.kp_nome_area nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.crmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.crmid
                    WHERE ent.deleted = 0 AND rel.module = 'KpAreeStabilimento' AND rel.relmodule = 'KpEntitaProcedure' AND rel.crmid = ".$area." AND rel.relcrmid = ".$id.")
                    UNION
                    (SELECT 
                    rel.relcrmid rischio,
                    aree.kp_nome_area nome
                    FROM {$table_prefix}_crmentityrel rel
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = rel.relcrmid
                    INNER JOIN {$table_prefix}_kpareestabilimento aree ON aree.kpareestabilimentoid = rel.relcrmid
                    WHERE ent.deleted = 0 AND rel.relmodule = 'KpAreeStabilimento' AND rel.module = 'KpEntitaProcedure' AND rel.relcrmid = ".$area." AND rel.crmid = ".$id.")) AS t";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if( $num_result > 0 ){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'rischio');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, 0, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);
            
        }
        else{

            $esiste = false;

            $id = 0;
            $nome = "";

        }

        $result = array('esiste' => $esiste,
                        'id' => $id,
                        'nome' => $nome);

        return $result;

    }

    static function creaAmbienteStandAlone($azienda, $stabilimento){
        global $adb, $table_prefix, $default_charset;

        $cartella_ambiente = self::creaDirectoryAmbienteStandAlone($azienda, $stabilimento);

        self::creaDBAmbienteStandAlone($cartella_ambiente);

    }

    static function creaDirectoryAmbienteStandAlone($azienda, $stabilimento){
        global $adb, $table_prefix, $default_charset;

        $ambiente_standard = __DIR__."/ambientiStandAlone/standard/";

        if( $stabilimento != ""){
            $cartella_destinazione = __DIR__."/ambientiStandAlone/".$azienda."_".$stabilimento;
        }
        else{
            $cartella_destinazione = __DIR__."/ambientiStandAlone/".$azienda;
        }
        
        self::copiaRicorsivaDirectory($ambiente_standard, $cartella_destinazione);

        return $cartella_destinazione;

    }

    static function copiaRicorsivaDirectory($src, $dest) {
        global $adb, $table_prefix, $default_charset;

        /* kpro@tom18052018 */

        /**
         * @author Tomiello Marco
         * @copyright (c) 2018, Kpro Consulting Srl
         *
         * Questa funzione effettua la copia ricorsiva dei file
         */

        $da_escludere = array(".", "..");

        if( is_dir($src) && !in_array($src, $da_escludere) ){

            if ( !is_dir($dest) ) {
                //Se nella destinazione non esiste la cartella la creo al momento
                mkdir($dest, 0755, true);
                chown($dest, "www-data");
                chgrp($dest, "www-data");
            }
            
            foreach( scandir($src) as $file ) {

                if ( !is_readable($src.'/'.$file) || in_array($file, $da_escludere) ) {

                    continue;

                }
                
                if ( is_dir($src."/".$file) ) {
            
                    if ( !is_dir($dest.'/'.$file) ) {
                        mkdir($dest.'/'.$file, 0755, true);
                        chown($dest.'/'.$file, "www-data");
                        chgrp($dest.'/'.$file, "www-data");
                    }
                    self::copiaRicorsivaDirectory($src.'/'.$file, $dest.'/'.$file);

                } else { 
                    
                    copy($src.'/'.$file, $dest.'/'.$file);
                    chown($dest.'/'.$file, "www-data");
                    chgrp($dest.'/'.$file, "www-data");
                    chmod($dest.'/'.$file, 0755);

                }

            }

        }
        else{
           
            if ( is_readable($src) || !in_array($src, $da_escludere) ) {

                if( !is_dir(dirname($dest)) ){
                    //Se nella destinazione non esiste la cartella in cui inserire il file, la creo al momento
                    mkdir(dirname($dest), 0755, true);
                    chown(dirname($dest), "www-data");
                    chgrp(dirname($dest), "www-data");
                }

                copy($src, $dest);
                chown($dest, "www-data");
                chgrp($dest, "www-data");
                chmod($dest, 0755);

            }

        }

    }

    static function creaDBAmbienteStandAlone($path_ambiente){
        global $adb, $table_prefix, $default_charset;

        $nome_db = "mpro.db";
        $database = $path_ambiente."/".$nome_db;

        if ( file_exists($database) ) {
            @unlink($database);
        }

        $db = new SQLite3($database);

        $list_query = array();

        $query_crmentityrel = "CREATE TABLE IF NOT EXISTS 
                                vte_crmentityrel 
                                (crmid INTEGER(19) PRIMARY KEY,
                                module VARCHAR(100),
                                relcrmid INTEGER(19),
                                relmodule VARCHAR(100))";

        $list_query[] = $query_crmentityrel;

        foreach( $list_query as $query ){
            $db->query($query);
        }

        $db->close();

        $lista_modulo = array();
        $lista_modulo[] = "Accounts";
        $lista_modulo[] = "Stabilimenti";
        $lista_modulo[] = "KpAreeStabilimento";
        $lista_modulo[] = "Contacts";
        $lista_modulo[] = "TipiDocumenti";
        $lista_modulo[] = "Documents";
        $lista_modulo[] = "KpProcedure";
        $lista_modulo[] = "KpEntitaProcedure";
        $lista_modulo[] = "KpRuoli";
        $lista_modulo[] = "KpRuoliAttivita";
        $lista_modulo[] = "KpOrganigrammi";
        $lista_modulo[] = "KpEntitaOrganigrammi";
        $lista_modulo[] = "KpRischiDVR";
        $lista_modulo[] = "KpMinaccePrivacy";
        $lista_modulo[] = "KpAttivitaDVR";
        $lista_modulo[] = "KpRischiQualita";

        foreach($lista_modulo as $modulo){
            self::creaTabellaDBAmbienteStandAlone($database, $modulo);
        }   

    }

    static function getTablesModulo($nome_modulo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $result = "";

        $query = "SELECT 
                    fiel.tablename tablename
                    FROM {$table_prefix}_field fiel
                    INNER JOIN {$table_prefix}_tab tab ON tab.tabid = fiel.tabid
                    WHERE tab.name = '".$nome_modulo."'
                    GROUP BY fiel.tablename";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for( $i = 0; $i < $num_result; $i++ ){
            
            $tablename = $adb->query_result($result_query, $i, 'tablename');
            $tablename = html_entity_decode(strip_tags($tablename), ENT_QUOTES, $default_charset);

            $result[] = $tablename;

        }

        return $result;

    }

    static function creaTabellaDBAmbienteStandAlone($database, $nome_modulo){
        global $adb, $table_prefix, $default_charset, $current_user;

        $db = new SQLite3($database);

        $tabelle_modulo = self::getTablesModulo($nome_modulo);

        foreach($tabelle_modulo as $tabella){

            $query = self::getDynamicQueryCreateTable($tabella);
            $db->query($query);

        }

        $db->close();

    }

    static function getDynamicQueryCreateTable($nome_tabella){
        global $adb, $table_prefix, $default_charset, $current_user, $dbconfig;

        $dati_tabella = self::getStrutturaDatabase($nome_tabella);
        //print_r($dati_tabella);die;

        $query = "CREATE TABLE IF NOT EXISTS ".$nome_tabella;

        $campi_tabella = "";

        foreach($dati_tabella as $campo){

            if($campi_tabella != ""){
                $campi_tabella .= ", ";
            }

            $campi_tabella .= $campo["nome"]." ".$campo["tipo_sqlite"];

            if( $campo["column_key"] == "PRI" ){
                $campi_tabella .= " PRIMARY KEY";
            }

        }

        if($campi_tabella == ""){
            return "";
        }
        else{

            $query = $query."(".$campi_tabella.")";
            return $query;

        }

    }

    static function getStrutturaDatabase($nome_tabella){
        global $adb, $table_prefix, $default_charset, $current_user, $dbconfig;

        $result = array();

        $query = "SELECT 
                    COLUMN_NAME nome,
                    COLUMN_TYPE tipo,
                    DATA_TYPE data_type,
                    CHARACTER_MAXIMUM_LENGTH max_character,
                    NUMERIC_PRECISION numeric_precision,
                    NUMERIC_SCALE numeric_scale,
                    COLUMN_KEY column_key
                    FROM INFORMATION_SCHEMA.COLUMNS 
                    WHERE TABLE_SCHEMA = '".$dbconfig['db_name']."' AND TABLE_NAME = '".$nome_tabella."'";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);
       
        for( $i = 0; $i < $num_result; $i++ ){

            $nome = $adb->query_result($result_query, $i, 'nome');
			$nome = html_entity_decode(strip_tags($nome), ENT_QUOTES, $default_charset);

            $tipo = $adb->query_result($result_query, $i, 'tipo');
            $tipo = html_entity_decode(strip_tags($tipo), ENT_QUOTES, $default_charset);

            $data_type = $adb->query_result($result_query, $i, 'data_type');
            $data_type = html_entity_decode(strip_tags($data_type), ENT_QUOTES, $default_charset);

            $max_character = $adb->query_result($result_query, $i, 'max_character');
            $max_character = html_entity_decode(strip_tags($max_character), ENT_QUOTES, $default_charset);

            $numeric_precision = $adb->query_result($result_query, $i, 'numeric_precision');
            $numeric_precision = html_entity_decode(strip_tags($numeric_precision), ENT_QUOTES, $default_charset);

            $numeric_scale = $adb->query_result($result_query, $i, 'numeric_scale');
            $numeric_scale = html_entity_decode(strip_tags($numeric_scale), ENT_QUOTES, $default_charset);

            $column_key = $adb->query_result($result_query, $i, 'column_key');
            $column_key = html_entity_decode(strip_tags($column_key), ENT_QUOTES, $default_charset);
            
            $tipo_sqlite = self::datatypeConversionSqlite($data_type, $max_character, $numeric_precision, $numeric_scale);

            $result[] = array("nome" => $nome,
                                "tipo" => $tipo,
                                "tipo_sqlite" => $tipo_sqlite,
                                "column_key" => $column_key);

        }

        return $result;

    }

    static function datatypeConversionSqlite($data_type, $max_character, $numeric_precision, $numeric_scale){
        global $adb, $table_prefix, $default_charset, $current_user;

        $integer = array('int', 'tinyint');

        $real = array('decimal');

        $text = array('date', 'datetime', 'text', 'timestamp', 'varchar');

        $blob = array('longblob', 'longtext', 'blob');

        if($max_character == null){
            $max_character = "255";
        }

        if($numeric_precision == null){
            $numeric_precision = "15";
        }

        if($numeric_scale == null){
            $numeric_scale = "0";
        }

        $result = "";

        if( in_array($data_type, $integer) ){

            $result = "INTEGER(".$numeric_precision.")";

        }
        elseif( in_array($data_type, $real) ){

            $result = "REAL(".$numeric_precision.",".$numeric_scale.")";

        }
        elseif( in_array($data_type, $text) ){

            $result = "VARCHAR(".$max_character.")";

        }
        elseif( in_array($data_type, $blob) ){

            $result = "BLOB";

        }

        return $result;

    }

    static function getAreeAssociabiliAProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();
        
        $lista_elementi_gia_relazionati = "(";

        $lista_elementi = self::getAreeProcedura($id, array());

        foreach($lista_elementi as $elemento){

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $elemento["id"];

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$elemento["id"];

            }

        }

        $lista_elementi_gia_relazionati .= ")";

        $query = "SELECT 
                    aree.kpareestabilimentoid id,
                    aree.kp_nome_area nome,
                    acc.accountname azienda,
                    stab.nome_stabilimento stabilimento
                    FROM {$table_prefix}_kpareestabilimento aree
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = aree.kpareestabilimentoid
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = aree.kp_azienda
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = aree.kp_stabilimento
                    WHERE ent.deleted = 0";

        if($lista_elementi_gia_relazionati != "()"){
            $query .= " AND aree.kpareestabilimentoid NOT IN ".$lista_elementi_gia_relazionati;
        }
                        
        if($filtro["nome_area"] != ""){
            $query .= " AND aree.kp_nome_area like '%".$filtro["nome_area"]."%'";
        }

        if($filtro["nome_azienda"] != ""){
            $query .= " AND acc.accountname like '%".$filtro["nome_azienda"]."%'";
        }

        if($filtro["nome_stabilimento"] != ""){
            $query .= " AND stab.nome_stabilimento like '%".$filtro["nome_stabilimento"]."%'";
        }

        $query .= " ORDER BY aree.kp_nome_area ASC, acc.accountname ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $azienda = $adb->query_result($result_query, $i, 'azienda');
            $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES,$default_charset);
            if($azienda == null){
                $azienda = "";
            }

            $stabilimento = $adb->query_result($result_query, $i, 'stabilimento');
            $stabilimento = html_entity_decode(strip_tags($stabilimento), ENT_QUOTES,$default_charset);
            if($stabilimento == null){
                $stabilimento = "";
            }
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'azienda' => $azienda,
                                'stabilimento' => $stabilimento);
            
        }

        return $result;

    }

    static function getAreeAssociabiliAElementoProcedura($id, $filtro){
        global $adb, $table_prefix, $default_charset;

        $result = array();
        
        $lista_elementi_gia_relazionati = "(";

        $lista_elementi = self::getAreeElementoProcedura($id, array());

        foreach($lista_elementi as $elemento){

            if( $lista_elementi_gia_relazionati == "(" ){

                $lista_elementi_gia_relazionati .= $elemento["id"];

            }
            else{

                $lista_elementi_gia_relazionati .= ", ".$elemento["id"];

            }

        }

        $lista_elementi_gia_relazionati .= ")";

        $query = "SELECT 
                    aree.kpareestabilimentoid id,
                    aree.kp_nome_area nome,
                    acc.accountname azienda,
                    stab.nome_stabilimento stabilimento
                    FROM {$table_prefix}_kpareestabilimento aree
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = aree.kpareestabilimentoid
                    INNER JOIN {$table_prefix}_account acc ON acc.accountid = aree.kp_azienda
                    LEFT JOIN {$table_prefix}_stabilimenti stab ON stab.stabilimentiid = aree.kp_stabilimento
                    WHERE ent.deleted = 0";

        if($lista_elementi_gia_relazionati != "()"){
            $query .= " AND aree.kpareestabilimentoid NOT IN ".$lista_elementi_gia_relazionati;
        }
                        
        if($filtro["nome_area"] != ""){
            $query .= " AND aree.kp_nome_area like '%".$filtro["nome_area"]."%'";
        }

        if($filtro["nome_azienda"] != ""){
            $query .= " AND acc.accountname like '%".$filtro["nome_azienda"]."%'";
        }

        if($filtro["nome_stabilimento"] != ""){
            $query .= " AND stab.nome_stabilimento like '%".$filtro["nome_stabilimento"]."%'";
        }

        $query .= " ORDER BY aree.kp_nome_area ASC, acc.accountname ASC";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        for($i=0; $i < $num_result; $i++){

            $id = $adb->query_result($result_query, $i, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES,$default_charset);
            
            $nome = $adb->query_result($result_query, $i, 'nome');
            $nome = html_entity_decode(strip_tags($nome), ENT_QUOTES,$default_charset);

            $azienda = $adb->query_result($result_query, $i, 'azienda');
            $azienda = html_entity_decode(strip_tags($azienda), ENT_QUOTES,$default_charset);
            if($azienda == null){
                $azienda = "";
            }

            $stabilimento = $adb->query_result($result_query, $i, 'stabilimento');
            $stabilimento = html_entity_decode(strip_tags($stabilimento), ENT_QUOTES,$default_charset);
            if($stabilimento == null){
                $stabilimento = "";
            }
            
            $result[] = array('id' => $id,
                                'nome' => $nome,
                                'azienda' => $azienda,
                                'stabilimento' => $stabilimento);
            
        }

        return $result;

    }

    static function setLinkAreaProcedura($id, $area){
        global $adb, $table_prefix, $default_charset;

        $dati_area = self::getAreaProcedura($id, $area);

        if( !$dati_area["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$id.", 'KpProcedure', ".$area.", 'KpAreeStabilimento')";
		    $adb->query($insert);

        }

    }

    static function setLinkAreaElementoProcedura($id, $area){
        global $adb, $table_prefix, $default_charset;

        $dati_area = self::getAreaElementoProcedura($id, $area);

        if( !$dati_area["esiste"] ){

            $insert = "INSERT INTO {$table_prefix}_crmentityrel (crmid, module, relcrmid, relmodule) VALUES
						(".$id.", 'KpEntitaProcedure', ".$area.", 'KpAreeStabilimento')";
		    $adb->query($insert);

        }

    }

    static function setPDFApprovazioneProcedura($record){
        global $adb, $table_prefix, $default_charset, $current_user;

        require_once(__DIR__."/../../../../modules/PDFMaker/InventoryPDF.php");
        require_once(__DIR__."/../../../../include/mpdf/mpdf.php"); 

        $file_firma_esecutore = __DIR__."/firme/".$record."_esecutore_jqScribbleImage.png";
        
        if( file_exists($file_firma_esecutore) ){ 
            $firma_esecutore = "<img src='".$file_firma_esecutore."' style='max-width: 100%; float: left; max-height: 150px;'/>";
        }
        else{
            $firma_esecutore = "";
        }

        $file_firma_approvatore = __DIR__."/firme/".$record."_approvatore_jqScribbleImage.png";

        if( file_exists($file_firma_approvatore) ){ 
            $firma_approvatore = "<img src='".$file_firma_approvatore."' style='max-width: 100%; float: left; max-height: 150px;'/>";
        }
        else{
            $firma_approvatore = "";
        }

        $file_svg = __DIR__."/svg/".$record.".svg";

        if( !file_exists($file_svg) ){ 

            self::setFileSVG($record);

        }

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

            $file_png = __DIR__."/svg/".$record.".png";

            $image->writeImage($file_png);
            $image->clear();
            $image->destroy();

            if( file_exists($file_png) ){ 

                $svg = "<img src='".$file_png."' style='max-width: 100%; float: left; max-height: 100%;'/>";

            }
            else{
                $file_png = "";
            }

        }
        else{
            $file_png = "";
        }

        $id_statici = self::getConfigurazioniIdStatici();

        $id_statico_templateid = $id_statici["PDF Maker - Template Approvazione Processi"];
        if( $id_statico_templateid["valore"] == "" && $id_statico_templateid["valore"] == 0 ){
            return;
        }

        $id_statico_cartella = $id_statici["Documenti - Cartella Processi Approvati"];
        if( $id_statico_cartella["valore"] == "" && $id_statico_cartella["valore"] == 0 ){
            return;
        }

        $templateid = $id_statico_templateid["valore"];
        $relmodule = 'KpProcedure';
        $language = 'it_it';
        $record = $record;
        $titolo_documento = "Approvazione Processo ".$record;
        $cartella_documenti = $id_statico_cartella["valore"];
        $description = "Approvazione Processo ".$record;

        $utente = $current_user->id;
        if($utente == null || $utente == "" || $utente == 0){
            $utente = 1;
        }

        $query = "SELECT 
                    notes.notesid notesid 
                    FROM {$table_prefix}_notes notes 
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid
                    INNER JOIN {$table_prefix}_senotesrel rel ON rel.notesid = notes.notesid
                    WHERE ent.deleted = 0 AND rel.crmid = ".$record." AND notes.title LIKE '%".$titolo_documento."%'";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $document_id = $adb->query_result($result_query, 0, 'notesid');
            $document_id = html_entity_decode(strip_tags($document_id), ENT_QUOTES,$default_charset);

            $file_name = "doc_".$document_id.date("ymdHi").".pdf";
            
            $query = "SELECT att.attachmentsid attachmentsid,
                                att.name name,
                                att.path path
                                FROM {$table_prefix}_seattachmentsrel serel
                                INNER JOIN {$table_prefix}_attachments att ON att.attachmentsid = serel.attachmentsid
                                WHERE serel.crmid = ".$document_id;

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);
                
            for( $i=0; $i < $num_result; $i++ ){

                $vecchio_attachmentsid = $adb->query_result($result_query, $i, 'attachmentsid');
                $vecchio_attachmentsid = html_entity_decode(strip_tags($vecchio_attachmentsid), ENT_QUOTES,$default_charset);

                $vecchio_name = $adb->query_result($result_query, $i, 'name');
                $vecchio_name = html_entity_decode(strip_tags($vecchio_name), ENT_QUOTES,$default_charset);

                $vecchio_path = $adb->query_result($result_query, $i, 'path');
                $vecchio_path = html_entity_decode(strip_tags($vecchio_path), ENT_QUOTES,$default_charset);
                
                $vecchio_file_name = $vecchio_attachmentsid."_".$vecchio_name;
                @unlink($root_directory.$vecchio_path.$vecchio_file_name);
                
                $delete = "DELETE FROM {$table_prefix}_seattachmentsrel 
                            WHERE crmid = ".$document_id." AND attachmentsid =".$vecchio_attachmentsid;
                $adb->query($delete);
                
            }
            
        }
        else{
            $document = CRMEntity::getInstance('Documents'); 
            $document->parentid = $record;
            
            $file_name = "doc_".$document->parentid.date("ymdHi").".pdf";
            
            $document->column_fields["notes_title"] = $titolo_documento;
            $document->column_fields["assigned_user_id"] = $utente;
            $document->column_fields["filename"] = $file_name;
            $document->column_fields["notecontent"] = $description; 
            $document->column_fields["filetype"] = "application/pdf"; 
            $document->column_fields["filesize"] = ""; 
            $document->column_fields["filelocationtype"] = "I"; 
            $document->column_fields["fileversion"] = '';
            $document->column_fields["filestatus"] = "on";
            $document->column_fields["folderid"] = $cartella_documenti;
            $document->column_fields["stato_documento"] = '';
            $document->column_fields["kp_data_documento"] = date('Y-m-d');
            $document->column_fields["data_scadenza"] = '2999-12-31';
            $document->column_fields["kp_stato_avanzament"] = 'Inserito documento';

            $document->save("Documents", $longdesc=true, $offline_update=false, $triggerEvent=false);
            $document_id = $document->id;

        }

        $date_var = date("Y-m-d H:i:s");
        //to get the owner id
        $ownerid = $document->column_fields["assigned_user_id"];
        if(!isset($ownerid) || $ownerid==""){
            $ownerid = $utente;
        }

        $current_id = $adb->getUniqueID($table_prefix."_crmentity");
	
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
        
        $body_html = str_replace("#SVG#", $svg, $body_html);
        $body_html = str_replace("#firma_esecutore#", $firma_esecutore, $body_html);
        $body_html = str_replace("#firma_approvatore#", $firma_approvatore, $body_html);

        $footer_html = str_replace("#SVG#", $svg, $footer_html);
        $footer_html = str_replace("#firma_esecutore#", $firma_esecutore, $footer_html);
        $footer_html = str_replace("#firma_approvatore#", $firma_approvatore, $footer_html);
	
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

        $upload_file_path = __DIR__."/processi_approvati/";

        if($name!=""){
            $file_name = $name.".pdf";
        }
    
        $mpdf->Output($upload_file_path.$current_id."_".$file_name);
    
        $filesize = filesize($upload_file_path.$current_id."_".$file_name);
        $filetype = "application/pdf";
        
        $sql1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
        $params1 = array($current_id, $utente, $ownerid, "Documents Attachment", $description, $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
    
        $adb->pquery($sql1, $params1);
    
        $sql2="insert into ".$table_prefix."_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
        $params2 = array($current_id, $file_name, $description, $filetype, $upload_file_path);
        $result=$adb->pquery($sql2, $params2);
    
        $sql3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
        $adb->pquery($sql3, array($document_id, $current_id));

        $sql4="UPDATE ".$table_prefix."_notes SET filesize=?, filename=? WHERE notesid=?";
        $adb->pquery($sql4,array($filesize,$file_name,$document_id));
        
        $result = $upload_file_path.$current_id."_".$file_name;

        $file_firma_esecutore = __DIR__."/firme/".$record."_esecutore_jqScribbleImage.png";
        if( file_exists($file_firma_esecutore) ){ 
            @unlink($file_firma_esecutore);
        }

        $file_firma_approvatore = __DIR__."/firme/".$record."_approvatore_jqScribbleImage.png";
        if( file_exists($file_firma_approvatore) ){ 
            @unlink($file_firma_approvatore);
        }

    }

    static function getConfigurazioniIdStatici(){
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

    static function convertSVGtoPNG($file_svg, $percorso_salvataggio){
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

            $image->writeImage($file_png);
            $image->clear();
            $image->destroy();

        }

    }

    static function setPDFProcedura($record){
        global $adb, $table_prefix, $default_charset, $current_user;

        require_once(__DIR__."/../../../../modules/PDFMaker/InventoryPDF.php");
        require_once(__DIR__."/../../../../include/mpdf/mpdf.php"); 

        $file_svg = __DIR__."/svg/".$record.".svg";

        if( !file_exists($file_svg) ){ 

            self::setFileSVG($record);

        }

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

            $file_png = __DIR__."/svg/".$record.".png";

            $image->writeImage($file_png);
            $image->clear();
            $image->destroy();

            if( file_exists($file_png) ){ 

                $svg = "<img src='".$file_png."' style='max-width: 100%; float: left; max-height: 100%;'/>";

            }
            else{
                $file_png = "";
            }

        }
        else{
            $file_png = "";
        }

        $id_statici = self::getConfigurazioniIdStatici();

        $id_statico_templateid = $id_statici["PDF Maker - Template Stampa Processi"];
        if( $id_statico_templateid["valore"] == "" && $id_statico_templateid["valore"] == 0 ){
            return;
        }

        $id_statico_cartella = $id_statici["Documenti - Cartella Stampa Processi"];
        if( $id_statico_cartella["valore"] == "" && $id_statico_cartella["valore"] == 0 ){
            return;
        }

        $templateid = $id_statico_templateid["valore"];
        $relmodule = 'KpProcedure';
        $language = 'it_it';
        $record = $record;
        $titolo_documento = "Stampa Processo ".$record;
        $cartella_documenti = $id_statico_cartella["valore"];
        $description = "Stampa Processo ".$record;

        $utente = $current_user->id;
        if($utente == null || $utente == "" || $utente == 0){
            $utente = 1;
        }

        $query = "SELECT 
                    notes.notesid notesid 
                    FROM {$table_prefix}_notes notes 
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = notes.notesid
                    INNER JOIN {$table_prefix}_senotesrel rel ON rel.notesid = notes.notesid
                    WHERE ent.deleted = 0 AND rel.crmid = ".$record." AND notes.title LIKE '%".$titolo_documento."%'";
        
        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $document_id = $adb->query_result($result_query, 0, 'notesid');
            $document_id = html_entity_decode(strip_tags($document_id), ENT_QUOTES,$default_charset);

            $file_name = "doc_".$document_id.date("ymdHi").".pdf";
            
            $query = "SELECT att.attachmentsid attachmentsid,
                                att.name name,
                                att.path path
                                FROM {$table_prefix}_seattachmentsrel serel
                                INNER JOIN {$table_prefix}_attachments att ON att.attachmentsid = serel.attachmentsid
                                WHERE serel.crmid = ".$document_id;

            $result_query = $adb->query($query);
            $num_result = $adb->num_rows($result_query);
                
            for( $i=0; $i < $num_result; $i++ ){

                $vecchio_attachmentsid = $adb->query_result($result_query, $i, 'attachmentsid');
                $vecchio_attachmentsid = html_entity_decode(strip_tags($vecchio_attachmentsid), ENT_QUOTES,$default_charset);

                $vecchio_name = $adb->query_result($result_query, $i, 'name');
                $vecchio_name = html_entity_decode(strip_tags($vecchio_name), ENT_QUOTES,$default_charset);

                $vecchio_path = $adb->query_result($result_query, $i, 'path');
                $vecchio_path = html_entity_decode(strip_tags($vecchio_path), ENT_QUOTES,$default_charset);
                
                $vecchio_file_name = $vecchio_attachmentsid."_".$vecchio_name;
                @unlink($root_directory.$vecchio_path.$vecchio_file_name);
                
                $delete = "DELETE FROM {$table_prefix}_seattachmentsrel 
                            WHERE crmid = ".$document_id." AND attachmentsid =".$vecchio_attachmentsid;
                $adb->query($delete);
                
            }
            
        }
        else{
            $document = CRMEntity::getInstance('Documents'); 
            $document->parentid = $record;
            
            $file_name = "doc_".$document->parentid.date("ymdHi").".pdf";
            
            $document->column_fields["notes_title"] = $titolo_documento;
            $document->column_fields["assigned_user_id"] = $utente;
            $document->column_fields["filename"] = $file_name;
            $document->column_fields["notecontent"] = $description; 
            $document->column_fields["filetype"] = "application/pdf"; 
            $document->column_fields["filesize"] = ""; 
            $document->column_fields["filelocationtype"] = "I"; 
            $document->column_fields["fileversion"] = '';
            $document->column_fields["filestatus"] = "on";
            $document->column_fields["folderid"] = $cartella_documenti;
            $document->column_fields["stato_documento"] = '';
            $document->column_fields["kp_data_documento"] = date('Y-m-d');
            $document->column_fields["data_scadenza"] = '2999-12-31';
            $document->column_fields["kp_stato_avanzament"] = 'Inserito documento';

            $document->save("Documents", $longdesc=true, $offline_update=false, $triggerEvent=false);
            $document_id = $document->id;

        }

        $date_var = date("Y-m-d H:i:s");
        //to get the owner id
        $ownerid = $document->column_fields["assigned_user_id"];
        if(!isset($ownerid) || $ownerid==""){
            $ownerid = $utente;
        }

        $current_id = $adb->getUniqueID($table_prefix."_crmentity");
	
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
        
        $body_html = str_replace("#SVG#", $svg, $body_html);
	
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

        $upload_file_path = __DIR__."/processi_approvati/";

        if($name!=""){
            $file_name = $name.".pdf";
        }
    
        $mpdf->Output($upload_file_path.$current_id."_".$file_name);
    
        $filesize = filesize($upload_file_path.$current_id."_".$file_name);
        $filetype = "application/pdf";
        
        $sql1 = "insert into ".$table_prefix."_crmentity (crmid,smcreatorid,smownerid,setype,description,createdtime,modifiedtime) values(?, ?, ?, ?, ?, ?, ?)";
        $params1 = array($current_id, $utente, $ownerid, "Documents Attachment", $description, $adb->formatDate($date_var, true), $adb->formatDate($date_var, true));
    
        $adb->pquery($sql1, $params1);
    
        $sql2="insert into ".$table_prefix."_attachments(attachmentsid, name, description, type, path) values(?, ?, ?, ?, ?)";
        $params2 = array($current_id, $file_name, $description, $filetype, $upload_file_path);
        $result=$adb->pquery($sql2, $params2);
    
        $sql3='insert into '.$table_prefix.'_seattachmentsrel values(?,?)';
        $adb->pquery($sql3, array($document_id, $current_id));

        $sql4="UPDATE ".$table_prefix."_notes SET filesize=?, filename=? WHERE notesid=?";
        $adb->pquery($sql4,array($filesize,$file_name,$document_id));
        
        $result = $upload_file_path.$current_id."_".$file_name;

        $file_png = __DIR__."/svg/".$record.".png";
        if( file_exists($file_png) ){ 
            @unlink($file_png);
        }

    }

    static function setPDFProceduraAll($record, $filtro_azienda, $filtro_stabilimento){
        global $adb, $table_prefix, $default_charset, $current_user;

        $path_temp = date("YmdHis")."_".rand(0 , 100000);

        $nome_pdf_risultante = self::setPDFProceduraRicorsivo($record, $path_temp);

        $path_pdf_risultante = __DIR__."/temp/";

        $nome_pdf_risultante = self::setNomeFile( $nome_pdf_risultante );

        self::mergePDF($path_pdf_risultante, $nome_pdf_risultante, $path_pdf_risultante.$path_temp."/");

        $result = array("url" => $path_pdf_risultante.$nome_pdf_risultante,
                        "name" => $nome_pdf_risultante);

        return $result;

    }

    static function setPDFProceduraRicorsivo($record, $path_temp){
        global $adb, $table_prefix, $default_charset, $current_user;

        $file_name = self::setPDFProceduraTemp( $record, $path_temp );

        $lista_sottoprocessi = self::getElementiProcedura($record, array("only_sottoprocesso" => true));

        foreach( $lista_sottoprocessi as $processo ){

            if( $processo["procedureid"] != 0 && $processo["procedureid"] != "" ){

                self::setPDFProceduraRicorsivo( $processo["procedureid"], $path_temp );

            }

        }

        return $file_name;

    }

    static function setPDFProceduraTemp($record, $path_temp){
        global $adb, $table_prefix, $default_charset, $current_user;

        require_once(__DIR__."/../../../../modules/PDFMaker/InventoryPDF.php");
        require_once(__DIR__."/../../../../include/mpdf/mpdf.php"); 

        $file_svg = __DIR__."/svg/".$record.".svg";

        if( !file_exists($file_svg) ){ 

            self::setFileSVG($record);

        }

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

            $file_png = __DIR__."/svg/".$record.".png";

            $image->writeImage($file_png);
            $image->clear();
            $image->destroy();

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

        $id_statici = self::getConfigurazioniIdStatici();

        $id_statico_templateid = $id_statici["PDF Maker - Template Stampa Processi"];
        if( $id_statico_templateid["valore"] == "" && $id_statico_templateid["valore"] == 0 ){
            return;
        }

        $id_statico_cartella = $id_statici["Documenti - Cartella Stampa Processi"];
        if( $id_statico_cartella["valore"] == "" && $id_statico_cartella["valore"] == 0 ){
            return;
        }

        $templateid = $id_statico_templateid["valore"];
        $relmodule = 'KpProcedure';
        $language = 'it_it';
        $record = $record;

        //$current_id = $adb->getUniqueID($table_prefix."_crmentity");
	
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
        
        $body_html = str_replace("#SVG#", $svg, $body_html);
	
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

        $upload_file_path = __DIR__."/temp/".$path_temp."/";

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
    
        $mpdf->Output($upload_file_path.$record."_".$file_name);

        $file_png = __DIR__."/svg/".$record.".png";
        if( file_exists($file_png) ){ 
            @unlink($file_png);
        }

        return $record."_".$file_name;

    }

    static function mergePDF($path_pdf_risultante, $nome_pdf_risultante, $path_temp){
        global $adb, $table_prefix, $default_charset, $current_user;
        
        if( $path_temp != "" ){

            $command = "python3 ".__DIR__."/KpMergePdf.py ".$path_pdf_risultante." ".$nome_pdf_risultante." ".$path_temp;  
            exec($command, $out, $status);
            //print_r($command);die;
       
        }

    }

    static function setNomeFile ($str = ''){
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

    static function setSVG($crmid, $svg){
        global $adb, $table_prefix, $default_charset, $current_user;

        $svg = addslashes($svg);

        $update = "UPDATE {$table_prefix}_kpprocedure SET
                    kp_bpmn_svg = '".$svg."'
                    WHERE kpprocedureid = ".$crmid;
        $adb->query($update);

    }

    static function setFileSVG($crmid){
        global $adb, $table_prefix, $default_charset, $current_user;

        $dati_processo = self::getProcesso($crmid);
        $svg = $dati_processo["bpmn_svg"];

        if( $svg != "" ){

            if ( !is_dir(__DIR__."/svg/") ) {
                mkdir(__DIR__."/svg/", 0777);
                chown(__DIR__."/svg/", "www-data");
                chgrp(__DIR__."/svg/", "www-data");
            }
            else{
				chmod(__DIR__."/svg/", 0777);
				chown(__DIR__."/svg/", "www-data");
                chgrp(__DIR__."/svg/", "www-data");
			}

            $filename = __DIR__."/svg/".$crmid.".svg";

            if(file_exists($filename)){ 
                @unlink($filename);
            } 

            file_put_contents($filename, $svg);

        }

    }

    static function riportaDatiRilevazioni($origine, $destinazione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $lista_task = self::getElementiProcedura($origine, array());

        foreach($lista_task as $task){

            $task_destinazione = self::getElementoProceduraRevisioneDi($task["id"]);

            if( $task_destinazione["esiste"] ){

                $update = "UPDATE {$table_prefix}_kprigherilrischiqual SET
                            kp_attivita = ".$task_destinazione["id"]."
                            WHERE kp_attivita = ".$task["id"];
                $adb->query($update);

            }

        }

        $update = "UPDATE {$table_prefix}_kprigherilrischiqual SET
                    kp_processo = ".$destinazione."
                    WHERE kp_processo = ".$origine;
        $adb->query($update);

    }

    static function riportaMisureMigliorative($origine, $destinazione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $lista_task = self::getElementiProcedura($origine, array());

        foreach($lista_task as $task){

            $task_destinazione = self::getElementoProceduraRevisioneDi($task["id"]);

            if( $task_destinazione["esiste"] ){

                $update = "UPDATE {$table_prefix}_kpmisuremigliorative SET
                            kp_attivita = ".$task_destinazione["id"]."
                            WHERE kp_attivita = ".$task["id"];
                $adb->query($update);

            }

        }

        $update = "UPDATE {$table_prefix}_kpmisuremigliorative SET
                    kp_processo = ".$destinazione."
                    WHERE kp_processo = ".$origine;
        $adb->query($update);

    }

    static function riportaNonConformita($origine, $destinazione){
        global $adb, $table_prefix, $default_charset, $current_user;

        $lista_task = self::getElementiProcedura($origine, array());

        foreach($lista_task as $task){

            $task_destinazione = self::getElementoProceduraRevisioneDi($task["id"]);

            if( $task_destinazione["esiste"] ){

                $update = "UPDATE {$table_prefix}_nonconformita SET
                            kp_attivita = ".$task_destinazione["id"]."
                            WHERE kp_attivita = ".$task["id"];
                $adb->query($update);

            }

        }

        $update = "UPDATE {$table_prefix}_nonconformita SET
                    kp_processo = ".$destinazione."
                    WHERE kp_processo = ".$origine;
        $adb->query($update);

    }

    static function getElementoProceduraRevisioneDi($id){
        global $adb, $table_prefix, $default_charset, $current_user;

        $query = "SELECT 
                    entproc.kpentitaprocedureid id
                    FROM {$table_prefix}_kpentitaprocedure entproc
                    INNER JOIN {$table_prefix}_crmentity ent ON ent.crmid = entproc.kpentitaprocedureid
                    WHERE ent.deleted = 0 AND entproc.kp_revisione_di = '".$id."'";

        $result_query = $adb->query($query);
        $num_result = $adb->num_rows($result_query);

        if($num_result > 0){

            $esiste = true;

            $id = $adb->query_result($result_query, 0, 'id');
            $id = html_entity_decode(strip_tags($id), ENT_QUOTES, $default_charset);

        }
        else{

            $esiste = false;
            $id = 0;

        }

        $result = array("esiste" => $esiste,
                        "id" => $id);

        return $result;

    }

    

}

?>