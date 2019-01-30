/* kpro@tom18072017 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */

var altezza_schermo;
var larghezza_schermo;

var record;
var mProLayout;
var mProTree;
var bpmnViewer;
var canvas;
var overlays;
var elementRegistry;

var processo_aperto = {};
var elemento_selezionato = {}
var tab_selezionato = "";
var documento = [];
var azienda = [];
var stabilimento = [];
var azienda_selezionata = 0;
var azienda_selezionata_nome = "";
var stabilimento_selezionato = 0;
var stabilimento_selezionato_nome = "";
var elemento_by_id = [];
var array_bpmn_id_elementi = [];
var privilegio_modifica_bpmn = false;
var in_salvataggio = false;
var array_processi_padri = [];
var processi_navigati = [];
var chiudi_nodi_non_correnti = true;
var albero_id;

var jlayoutObj;
var jgraphContainer;
var jdettagliContainer;
var jreadonly_nome_attivita;
var jreadonly_valore_aggiunto;
var jreadonly_descrizione_attivita;
var jreadonly_nome_processo;
var jreadonly_descrizione_processo;
var jbody_tabella_ruoli_attivita;
var jbody_tabella_documenti;
var jbottone_download_documento;
var jbottone_cerca_azienda;
var jbottone_cerca_stabilimento;
var jbody_tabella_aziende;
var jbody_tabella_stabilimenti;
var jbottone_seleziona_azienda;
var jsearch_azienda;
var jpopup_aziende;
var jsearch_nome_azienda_stabilimento;
var jpopup_stabilimenti;
var jsearch_stabilimento;
var jbottone_filtra;
var jbottone_pulisci_cerca_azienda;
var jbottone_pulisci_cerca_stabilimento;
var jbody_tabella_workflow;
var jbottone_visualizza_workflow;
var jbody_tabella_risorse_attivita;
var jsearch_evidenzia;
var jbody_tabella_rischi;
var jbottone_modifica_disegno_processo;
var jbottone_genera_non_conformita;
var jpopup_generico;
var jbottone_centra_disegno;
var jbottone_processo_precedente;
var jbottone_genera_revisione;
var jnavmenu_processi;
var jbottone_goto_documento;
var jbottone_stampa_svg;
var jbottone_stampa_png;
var jbottone_stampa_pdf;
var jbottone_stampa_pdf_all;
var jbottone_processo_padre;
var jsearch_nome_azienda;
var jsearch_citta_azienda;

var filtro_documenti = {};
var filtro_ruoli = {};
var filtro_documenti = {};
var filtro_aziende = {};
var filtro_stabilimenti = {};
var filtro_processi = {};
var filtro_workflow = {};
var filtro_risorse = {};
var filtro_rischi = {};

jQuery(document).ready(function() {

    //record = getObj('record').value;

    inizializzazione();

    inizializzazioneBPMN();

    inizializzazioneLayout();

});

function inizializzazione() {

    jlayoutObj = jQuery("#layoutObj");
    jgraphContainer = jQuery("#graphContainer");
    jdettagliContainer = jQuery("#dettagliContainer");

    jbottone_cerca_azienda = jQuery("#bottone_cerca_azienda");
    jbottone_cerca_stabilimento = jQuery("#bottone_cerca_stabilimento");
    jbody_tabella_aziende = jQuery("#body_tabella_aziende");
    jbody_tabella_stabilimenti = jQuery("#body_tabella_stabilimenti");
    jsearch_azienda = jQuery("#search_azienda");
    jpopup_aziende = jQuery("#popup_aziende");
    jsearch_nome_azienda = jQuery("#search_nome_azienda");
    jsearch_citta_azienda = jQuery("#search_citta_azienda");
    jsearch_nome_azienda_stabilimento = jQuery("#search_nome_azienda_stabilimento");
    jpopup_stabilimenti = jQuery("#popup_stabilimenti");
    jsearch_stabilimento = jQuery("#search_stabilimento");
    jbottone_filtra = jQuery("#bottone_filtra");
    jbottone_pulisci_cerca_azienda = jQuery("#bottone_pulisci_cerca_azienda");
    jbottone_pulisci_cerca_stabilimento = jQuery("#bottone_pulisci_cerca_stabilimento");
    jsearch_evidenzia = jQuery("#search_evidenzia");
    jbottone_modifica_disegno_processo = jQuery("#bottone_modifica_disegno_processo");
    jpopup_generico = jQuery("#popup_generico");
    jbottone_centra_disegno = jQuery("#bottone_centra_disegno");
    jbottone_processo_precedente = jQuery("#bottone_processo_precedente");
    jbottone_processo_padre = jQuery("#bottone_processo_padre");

    window.addEventListener('resize', function() {
        reSize();
    }, false);

    reSize();

    getPrivilegiUtente();

    filtro_aziende = {
        nome: "",
        citta: ""
    };

    getListaAziende(filtro_aziende);

    jsearch_nome_azienda.keyup(function(ev){

        var temp = jsearch_nome_azienda.val();
        var code = ev.which;
        if (code == 13 || temp == "") {
            filtro_aziende.nome = temp;
            getListaAziende(filtro_aziende);
        }

    });

    jsearch_citta_azienda.keyup(function(ev){

        var temp = jsearch_citta_azienda.val();
        var code = ev.which;
        if (code == 13 || temp == "") {
            filtro_aziende.citta = temp;
            getListaAziende(filtro_aziende);
        }

    });

    filtro_stabilimenti = {
        nome: "",
        azienda: "",
        citta: ""
    };

    getListaStabilimenti(filtro_stabilimenti);

    jbottone_filtra.click(function() {

        setFiltroProcessi();

    });

    jbottone_pulisci_cerca_azienda.click(function() {

        azienda_selezionata = 0;
        azienda_selezionata_nome = "";
        stabilimento_selezionato = 0;
        stabilimento_selezionato_nome = "";

        filtro_stabilimenti.azienda = "";
        jsearch_nome_azienda_stabilimento.val(azienda_selezionata_nome);
        getListaStabilimenti(filtro_stabilimenti);

        jsearch_azienda.val(azienda_selezionata_nome);
        jsearch_stabilimento.val(stabilimento_selezionato_nome);

        setFiltroProcessi();

    });

    jbottone_pulisci_cerca_stabilimento.click(function() {

        stabilimento_selezionato = 0;
        stabilimento_selezionato_nome = "";

        jsearch_stabilimento.val(stabilimento_selezionato_nome);

        setFiltroProcessi();

    });

    jbottone_cerca_azienda.click(function() {

    });

    jbottone_cerca_stabilimento.click(function() {

    });

    jsearch_evidenzia.change(function() {

        evidenziaTask(jsearch_evidenzia.val());

    });

    jbottone_modifica_disegno_processo.click(function() {

        if (processo_aperto.id != "" && processo_aperto.id != 0) {

            window.open("modules/SproCore/CustomViews/KpBPMNcreator/index.php?record=" + processo_aperto.id, "_blank");

        }

    });

    jbottone_centra_disegno.click(function() {
        canvas.zoom('fit-viewport', {});
        canvas.zoom('fit-viewport', {});
    });

    jbottone_processo_precedente.click(function() {

        if (processi_navigati.length > 1) {

            var processo_corrente = processi_navigati.pop();

            /*console.log("Processo corrente");
            console.log(processo_corrente);*/

            var processo_precedente = processi_navigati.pop();

            /*console.log("Processo precedente");
            console.log(processo_precedente);*/

            mProTree.selectItem(processo_precedente);

            processi_navigati.push(processo_precedente);
			
			albero_id = processo_precedente;
            
            //console.log("jbottone_processo_precedente: " + processo_precedente);
            var processo_precedente_array = processo_precedente.split('_');
            var temp_id = processo_precedente_array.pop();
            //console.log("jbottone_processo_precedente: " + temp_id);

            array_processi_padri = processo_precedente_array;

            elemento_selezionato.id = temp_id;
            elemento_selezionato.bpmn_id = "";
            elemento_selezionato.nome = mProTree.getItemText(processo_precedente);
            elemento_selezionato.descrizione = "";
            elemento_selezionato.albero_id = processo_precedente;
            
            getBPMNxml(elemento_selezionato);

        }

    });

    jbottone_processo_padre.click(function(){

        if (array_processi_padri.length > 1) {

            var elemento_selezionato_temp = getPadreByArray(array_processi_padri);

            processi_navigati.push(elemento_selezionato_temp);

            albero_id = elemento_selezionato_temp;

            var temp_id = array_processi_padri.pop();

            elemento_selezionato.id = temp_id;
            elemento_selezionato.bpmn_id = "";
            elemento_selezionato.nome = mProTree.getItemText(albero_id);
            elemento_selezionato.descrizione = "";
            elemento_selezionato.albero_id = albero_id;

            getBPMNxml(elemento_selezionato);

            mProTree.selectItem(elemento_selezionato.albero_id);
        
        }

    });

}

function reSize() {

    larghezza_schermo = window.innerWidth;
    altezza_schermo = window.innerHeight;

    if(window.current_theme == 'spro_next'){
        jlayoutObj.css("height", innerHeight - 80);
    }
    else{
        jlayoutObj.css("height", innerHeight - 205);  
    }

}

function inizializzazioneLayout() {

    window.dhx4.skin = 'material';

    mProLayout = new dhtmlXLayoutObject({
        parent: "layoutObj",
        pattern: "4A",
        skin: "material"
    });

    mProLayout.setOffsets({
        top: 0,
        right: 0,
        bottom: 0,
        left: 0
    });

    mProLayout.cells("a").setText("Filtri");
    mProLayout.cells("a").setCollapsedText("Filtri");
    mProLayout.cells("a").setHeight(310);
    mProLayout.cells("a").collapse();

    mProLayout.cells("b").setText("Lista Processi");
    mProLayout.cells("b").setCollapsedText("Lista Processi");
    mProLayout.cells("b").setWidth(300);

    mProLayout.cells("c").setText("Flow Chart");
    mProLayout.cells("c").setCollapsedText("Flow Chart");

    mProLayout.cells("d").setText("Dettagli");
    mProLayout.cells("d").setCollapsedText("Dettagli");
    //mProLayout.cells("d").collapse();

    mProLayout.cells("a").attachObject("filtriContainer");

    mProLayout.cells("c").attachObject("graphContainer");

    mProLayout.cells("d").attachObject("dettagliContainer");

    inizializzazioneAlberoProcessi();

    mProLayout.attachEvent("onResizeFinish", function() {
        canvas.zoom('fit-viewport', {});
        canvas.zoom('fit-viewport', {});
    });

    mProLayout.attachEvent("onPanelResizeFinish", function(names) {
        canvas.zoom('fit-viewport', {});
        canvas.zoom('fit-viewport', {});
    });

    mProLayout.attachEvent("onCollapse", function(name) {
        canvas.zoom('fit-viewport', {});
        canvas.zoom('fit-viewport', {});
    });

    mProLayout.attachEvent("onExpand", function(name) {
        canvas.zoom('fit-viewport', {});
        canvas.zoom('fit-viewport', {});
    });

}

function inizializzazioneAlberoProcessi() {

    processi_navigati = [];
    array_processi_padri = [];

    mProTree = mProLayout.cells("b").attachTree();
    mProTree.setImagesPath("Smarty/templates/SproCore/KpProcessGraphHomeViewer/dhtmlx_codebase/imgs/dhxtree_material/");

    mProTree.setOnClickHandler(function(id) {

        var elemento_selezionato_temp = id.substring(0, 3);

        if (elemento_selezionato_temp != "tp_") {

            elemento_selezionato_temp = id;
            //console.log(" mProTree.setOnClickHandler: " + elemento_selezionato_temp);

            processi_navigati.push(elemento_selezionato_temp);

            if( chiudi_nodi_non_correnti ){
                closeAllRoots(elemento_selezionato_temp);
            }

            elemento_selezionato_temp = elemento_selezionato_temp.split('_');

            albero_id = id;

            var temp_id = elemento_selezionato_temp.pop();
            //console.log(" mProTree.setOnClickHandler: " + temp_id);

            elemento_selezionato.id = temp_id;
            elemento_selezionato.bpmn_id = "";
            elemento_selezionato.nome = mProTree.getItemText(id);
            elemento_selezionato.descrizione = "";
            elemento_selezionato.albero_id = albero_id;

            //console.table(elemento_selezionato);

            array_processi_padri = elemento_selezionato_temp;
            //console.log(array_processi_padri);

            getBPMNxml(elemento_selezionato);

        } else {

            //console.table(elemento_selezionato);
            mProTree.selectItem(elemento_selezionato.albero_id);

        }

    });

    filtro_processi = {
        azienda: azienda_selezionata,
        stabilimento: stabilimento_selezionato
    };

    getListaProcessi(filtro_processi);

}

function getListaProcessi(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetListaProcessi.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            //console.table(data);

            var lista_procedure_temp = "";
            var first_element_temp = "";
            var first_element_text_temp = "";

            var xml = '<?xml version="1.0" encoding="iso-8859-1"?>';
            xml += '<tree id="0">';

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    xml += '<item text="' + data[i].tipo_procedura + '" id="tp_' + data[i].tipo_procedura + '" open="1">';

                    var albero_procedure = getXMLAlberoProcedure(data[i].lista_procedure, "m" + i);

                    if (i == 0) {

                        first_element_temp = albero_procedure.primo_elemento_id;
                        first_element_text_temp = albero_procedure.primo_elemento_nome;

                    }

                    xml += albero_procedure.xml;

                    xml += '</item>';

                }

            }

            xml += '</tree>';

            //console.log("getListaProcessi");
            //console.log(xml);

            mProTree.parse(xml);

            if (first_element_temp != "" && first_element_temp != 0) {

                albero_id = first_element_temp;

                mProTree.selectItem(first_element_temp);

                processi_navigati.push(first_element_temp);

                first_element_temp = first_element_temp.split('_');

                var temp_id = first_element_temp.pop();

                array_processi_padri = first_element_temp;

                elemento_selezionato = {};

                elemento_selezionato.id = temp_id;
                elemento_selezionato.bpmn_id = "";
                elemento_selezionato.nome = first_element_text_temp;
                elemento_selezionato.descrizione = data[0].descrizione;
                elemento_selezionato.albero_id = albero_id;

                getBPMNxml(elemento_selezionato);

                if ( privilegio_modifica_bpmn ){
                    jbottone_modifica_disegno_processo.show();
                }

            }
            else{

                jbottone_modifica_disegno_processo.hide();

            }

        },
        fail: function() {

        }
    });

}

function getXMLAlberoProcedure(dati, padre) {

    var result = {
        xml: "",
        primo_elemento_id: 0,
        primo_elemento_nome: ""
    };

    for (var i = 0; i < dati.length; i++) {

        var lista_sottoprocessi_temp = dati[i].lista_sottoprocessi;

        if (lista_sottoprocessi_temp.length > 0) {

            result.xml += '<item text="' + dati[i].nome + '" id="' + padre + '_' + dati[i].id +'">';

            var new_padre = padre + "_" + dati[i].id;

            result.xml += getXMLAlberoSottoprocesso(dati[i].lista_sottoprocessi, new_padre);

            result.xml += '</item>';

        } else {

            result.xml += '<item text="' + dati[i].nome + '" id="' + padre + '_' + dati[i].id +'" />';

        }

        if (i == 0) {
            result.primo_elemento_id = padre + "_" + dati[i].id;
            result.primo_elemento_nome = dati[i].nome;
        }

    }

    return result;

}

function getXMLAlberoSottoprocesso(dati, padre) {

    //console.log(dati);

    var result = "";

    for (var i = 0; i < dati.length; i++) {

        var lista_sottoprocessi_temp = dati[i].lista_sottoprocessi;

        if (lista_sottoprocessi_temp.length > 0) {

            result += '<item text="' + dati[i].nome + '" id="' + padre + '_' + dati[i].procedureid +'">';

            var new_padre = padre + "_" + dati[i].procedureid;

            result += getXMLAlberoSottoprocesso(dati[i].lista_sottoprocessi, new_padre);

            result += '</item>';

        } else {

            result += '<item text="' + dati[i].nome + '" id="' + padre + '_' + dati[i].procedureid +'" />';

        }

    }

    return result;

}

function inizializzazioneBPMN() {

    (function(BpmnJS) {

        //create viewer
        bpmnViewer = new BpmnJS({
            container: '#graphContainer'
        });

    })(window.BpmnJS);

    bpmnViewer.on('element.click', function(event) {
        var element = event.element;
        //console.log("element.click");
        //console.log(element);

        switch (element.type) {
            case "bpmn:Task":
                setTaskSelezionata(element);
                break;
            case "bpmn:SendTask":
                setTaskSelezionata(element);
                break;
            case "bpmn:ReceiveTask":
                setTaskSelezionata(element);
                break;
            case "bpmn:UserTask":
                setTaskSelezionata(element);
                break;
            case "bpmn:ManualTask":
                setTaskSelezionata(element);
                break;
            case "bpmn:ServiceTask":
                setTaskSelezionata(element);
                break;
            case "bpmn:ScriptTask":
                setTaskSelezionata(element);
                break;
            case "bpmn:CallActivity":
                setTaskSelezionata(element);
                break;
            case "bpmn:Transaction":
                setTaskSelezionata(element);
                break;
            case "bpmn:SubProcess":
                setProcessoSelezionato(element, false);
                break;
            case "bpmn:Process":
                setProcessoSelezionato(element, true);
                break;
        }

    });

    bpmnViewer.on('element.dblclick', function(event) {
        var element = event.element;
        //console.log("element.click");
        //console.log(element);

        switch (element.type) {
            case "bpmn:Task":
                break;
            case "bpmn:SubProcess":
                apriProcessoDaBPMN(element);
                break;
        }

    });

}

function importBPMNxml(xml, lista_elementi) {

    bpmnViewer.importXML(xml, function(err) {

        if (err) {
            //return console.error('could not import BPMN 2.0 diagram', err);
        }

        canvas = bpmnViewer.get('canvas');
        overlays = bpmnViewer.get('overlays');
        elementRegistry = bpmnViewer.get('elementRegistry');

        //console.log(elementRegistry);

        //zoom to fit full viewport
        //canvas.zoom('fit-viewport');
        canvas.zoom('fit-viewport', {});

        jQuery.each(elementRegistry.getAll(), function(index, object) {

            if (object.constructor.name == 'Shape') {

                var id = object.id;
                var type = object.type;
                var dom_obj = jQuery('[data-element-id=' + id + ']');
                var subType = '';

                if (typeof(object.businessObject.cancelActivity) == 'boolean') {
                    var cancelActivity = object.businessObject.cancelActivity;
                }

                if (typeof(elementRegistry.get(id + '_label')) == 'object') {
                    var text = jQuery('[data-element-id=' + id + '_label]').find('text').text();
                } else {
                    var text = dom_obj.find('text').text();
                }

                dom_obj.css('cursor', 'pointer');

                dom_obj.hover(function() {
                    canvas.toggleMarker(id, 'highlights-shape');
                }, function() {
                    canvas.toggleMarker(id, 'highlights-shape');
                });

                //console.log(object);

                applicaStileShape(object);

                dom_obj.click(function() {

                });

            }

        });

        setArrayListaElementiById(lista_elementi);

        applicaStileAggiuntivoShape(lista_elementi);

        evidenziaTask(jsearch_evidenzia.val());

    });

}

function getBPMNxml(elemento) {

    var dati = {
        record: elemento.id
    };

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetProcesso.php',
        dataType: 'json',
        async: true,
        data: dati,
        beforeSend: function() {

        },
        success: function(data) {

            //console.table(data);

            if (data.length > 0 && data[0]["processo"] != null) {

                processo_aperto = {};
                elemento_selezionato = {};
                elemento_by_id = [];
                array_bpmn_id_elementi = [];

                processo_aperto.id = data[0]["processo"].id;
                processo_aperto.nome = HtmlEntities.decode(data[0]["processo"].nome);
                processo_aperto.descrizione = HtmlEntities.decode(data[0]["processo"].descrizione);
                processo_aperto.bpmn_xml = data[0]["processo"].bpmn_xml;

                elemento_selezionato.id = data[0]["processo"].id;
                elemento_selezionato.bpmn_id = "";
                elemento_selezionato.nome = HtmlEntities.decode(data[0]["processo"].nome);
                elemento_selezionato.descrizione = HtmlEntities.decode(data[0]["processo"].descrizione);
                elemento_selezionato.albero_id = albero_id;
                
                setProcessoSelezionatoDaAlbero(processo_aperto);

                var diagramXML = processo_aperto.bpmn_xml;

                if (diagramXML != "") {

                    importBPMNxml(diagramXML, data[0]["elementi"]);

                } else {

                    newBPMNDiagram();

                }

            }

        },
        fail: function() {

        }
    });

}

function newBPMNDiagram() {

    jQuery.get('Smarty/templates/SproCore/KpProcessGraphHomeViewer/resources/newDiagram.bpmn', function(data) {
        importBPMNxml(data, []);
    });

}

function scaricaDocumento(id) {

    location.href = "Smarty/templates/SproCore/KpProcessGraphHomeViewer/DownloadDocumentoProcedura.php?fileid=" + documento[id].attachmentsid + "&entityid=" + id;

}

function setSelezioneShape(id) {

    jQuery.each(elementRegistry.getAll(), function(index, object) {

        canvas.removeMarker(object.id, 'selected-shape');

    });

    canvas.toggleMarker(id, 'selected-shape');

}

function setTaskSelezionata(elemento) {

    elemento_selezionato.id = 0;
    elemento_selezionato.bpmn_id = elemento.id;
    elemento_selezionato.nome = HtmlEntities.decode(elemento.businessObject.name);
    elemento_selezionato.descrizione = "";

    setSelezioneShape(elemento_selezionato.bpmn_id);

    mProLayout.cells("d").setText("Dettagli: " + elemento_selezionato.nome);
    mProLayout.cells("d").setCollapsedText("Dettagli: " + elemento_selezionato.nome);

    getTemplateTask(elemento_selezionato);

}

function setProcessoSelezionato(elemento, click_su_vuoto) {

    if (click_su_vuoto) {

        elemento_selezionato.id = processo_aperto.id;
        elemento_selezionato.bpmn_id = "";
        elemento_selezionato.nome = processo_aperto.nome;
        elemento_selezionato.descrizione = processo_aperto.descrizione;

        jQuery.each(elementRegistry.getAll(), function(index, object) {

            canvas.removeMarker(object.id, 'selected-shape');

        });

        mProLayout.cells("d").setText("Dettagli: " + elemento_selezionato.nome);
        mProLayout.cells("d").setCollapsedText("Dettagli: " + elemento_selezionato.nome);

        getTemplateProcesso(elemento_selezionato, true, false);

    } else {

        elemento_selezionato.id = 0;
        elemento_selezionato.bpmn_id = elemento.id;
        elemento_selezionato.nome = HtmlEntities.decode(elemento.businessObject.name);
        elemento_selezionato.descrizione = "";

        setSelezioneShape(elemento_selezionato.bpmn_id);

        mProLayout.cells("d").setText("Dettagli: " + elemento_selezionato.nome);
        mProLayout.cells("d").setCollapsedText("Dettagli: " + elemento_selezionato.nome);

        getTemplateProcesso(elemento_selezionato, false, false);

    }

}

function apriProcessoDaBPMN(elemento) {

    elemento_selezionato.id = 0;
    elemento_selezionato.bpmn_id = elemento.id;
    elemento_selezionato.nome = HtmlEntities.decode(elemento.businessObject.name);
    elemento_selezionato.descrizione = "";

    setSelezioneShape(elemento_selezionato.bpmn_id);

    mProLayout.cells("d").setText("Dettagli: " + elemento_selezionato.nome);
    mProLayout.cells("d").setCollapsedText("Dettagli: " + elemento_selezionato.nome);

    getTemplateProcesso(elemento_selezionato, false, true);

}

function setProcessoSelezionatoDaAlbero(elemento) {

    //console.table(elemento);

    mProLayout.cells("c").setText(elemento.nome);
    mProLayout.cells("c").setCollapsedText(elemento.nome);

    mProLayout.cells("d").setText("Dettagli: " + elemento.nome);
    mProLayout.cells("d").setCollapsedText("Dettagli: " + elemento.nome);

    getTemplateProcesso(elemento, true, false);

}

function getTemplateTask(elemento) {

    jQuery.get("Smarty/templates/SproCore/KpProcessGraphHomeViewer/templates/dettagli_task.html", function(data) {

        //console.table(data);

        jdettagliContainer.empty();
        jdettagliContainer.append(data);

        if (tab_selezionato != "") {

            //jQuery(".nav-tabs a[href='#" + tab_selezionato + "']").tab('show');

        }

        jreadonly_nome_attivita = jQuery("#readonly_nome_attivita");
        jreadonly_descrizione_attivita = jQuery("#readonly_descrizione_attivita");
        jbody_tabella_ruoli_attivita = jQuery("#body_tabella_ruoli_attivita");
        jbody_tabella_documenti = jQuery("#body_tabella_documenti");
        jbody_tabella_risorse_attivita = jQuery("#body_tabella_risorse_attivita");
        jbody_tabella_rischi = jQuery("#body_tabella_rischi");
        jreadonly_valore_aggiunto = jQuery("#readonly_valore_aggiunto");
        jbottone_genera_non_conformita = jQuery("#bottone_genera_non_conformita");

        jbottone_genera_non_conformita.click(function() {

            apriPopupAggiuntaNonConformita()
            //getWizardNonConformita();

        });

        var dati = {
            processo: processo_aperto.id,
            elemento_bpmn_id: elemento.bpmn_id
        };

        jQuery.ajax({
            url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetElemento.php',
            dataType: 'json',
            async: true,
            data: dati,
            beforeSend: function() {

            },
            success: function(data) {

                //console.table(data);

                elemento_selezionato.id = data[0].id;
                elemento_selezionato.bpmn_id = elemento.bpmn_id;
                elemento_selezionato.nome = HtmlEntities.decode(data[0].nome);
                elemento_selezionato.descrizione = HtmlEntities.decode(data[0].descrizione);

                jreadonly_nome_attivita.val(elemento_selezionato.nome);
                jreadonly_valore_aggiunto.val(data[0].valore_aggiunto);
                jreadonly_descrizione_attivita.val(elemento_selezionato.descrizione);

                filtro_ruoli.record = elemento_selezionato.id;
                filtro_ruoli.nome_ruolo = "";

                getRuoliElemento(filtro_ruoli);

                filtro_documenti.record = elemento_selezionato.id;
                filtro_documenti.nome_documento = "";

                getDocumentiElemento(filtro_documenti);

                filtro_risorse.record = elemento_selezionato.id;
                filtro_risorse.nome_risorsa = "";
                filtro_risorse.azienda = azienda_selezionata;
                filtro_risorse.stabilimento = stabilimento_selezionato;
                filtro_risorse.ruolo = "";

                getRisorseElemento(filtro_risorse);

                filtro_rischi.record = elemento_selezionato.id;
                filtro_rischi.nome_rischio = "";

                getRischiElemento(filtro_rischi);

            },
            fail: function() {

            }
        });

    });

}

function getTemplateProcesso(elemento, da_albero, apri) {

    jQuery.get("Smarty/templates/SproCore/KpProcessGraphHomeViewer/templates/dettagli_sottoprocesso.html", function(data) {

        //console.table(data);

        jdettagliContainer.empty();
        jdettagliContainer.append(data);

        jreadonly_nome_processo = jQuery("#readonly_nome_processo");
        jreadonly_descrizione_processo = jQuery("#readonly_descrizione_processo");
        jbody_tabella_documenti = jQuery("#body_tabella_documenti");
        jbody_tabella_workflow = jQuery("#body_tabella_workflow");
        jbottone_genera_revisione = jQuery("#bottone_genera_revisione");
        jnavmenu_processi = jQuery("#navmenu_processi");
        jbottone_stampa_svg = jQuery("#bottone_stampa_svg");
        jbottone_stampa_png = jQuery("#bottone_stampa_png");
        jbottone_stampa_pdf = jQuery("#bottone_stampa_pdf");
        jbottone_stampa_pdf_all = jQuery("#bottone_stampa_pdf_all");

        jreadonly_nome_processo.val(elemento.nome);

        jreadonly_descrizione_processo.val(elemento_selezionato.descrizione);

        jbottone_genera_revisione.click(function(){
            if( privilegio_modifica_bpmn && !in_salvataggio && processo_aperto.id != "" && processo_aperto.id != 0){
                in_salvataggio = true;
                generaRevisione();
            }
        });

        jbottone_stampa_svg.click(function(){

            getStampa("SVG");

        });

        jbottone_stampa_png.click(function(){

            getStampa("PNG");

        });

        jbottone_stampa_pdf.click(function(){

            getStampa("PDF");

        });

        jbottone_stampa_pdf_all.click(function(){

            getStampaAll("PDFall");

        });

        if (da_albero) {

            filtro_documenti.record = elemento_selezionato.id;
            filtro_documenti.nome_documento = "";

            getDocumentiElemento(filtro_documenti);

            filtro_workflow.record = elemento_selezionato.id;
            filtro_workflow.nome_workflow = "";

            getWorkflowElemento(filtro_workflow);

            if(privilegio_modifica_bpmn){
                jbottone_genera_revisione.show();
            }
            else{
                jbottone_genera_revisione.hide();
            }

        } else {

            var dati = {
                processo: processo_aperto.id,
                elemento_bpmn_id: elemento.bpmn_id
            };

            jQuery.ajax({
                url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetElemento.php',
                dataType: 'json',
                async: true,
                data: dati,
                beforeSend: function() {

                },
                success: function(data) {

                    //console.table(data);

                    if (apri) {

                        array_processi_padri.push(processo_aperto.id);

                        albero_id = getPadreByArray(array_processi_padri) + "_" + data[0].relazionato_a_id;
                        //console.log("getTemplateProcesso: " + albero_id);

                        elemento_selezionato.albero_id = albero_id;

                        mProTree.selectItem(elemento_selezionato.albero_id);

                        processi_navigati.push(elemento_selezionato.albero_id);

                        var elemento_temp = {
                            id: data[0].relazionato_a_id
                        };

                        elemento_selezionato.id = data[0].id;

                        getBPMNxml(elemento_temp);

                        filtro_documenti.record = elemento_selezionato.id;
                        filtro_documenti.nome_documento = "";

                        getDocumentiElemento(filtro_documenti);

                        filtro_workflow.record = elemento_selezionato.id;
                        filtro_workflow.nome_workflow = "";

                        getWorkflowElemento(filtro_workflow);

                        if(privilegio_modifica_bpmn){
                            jbottone_genera_revisione.show();
                        }
                        else{
                            jbottone_genera_revisione.hide();
                        }


                    } else {

                        filtro_documenti.record = data[0].relazionato_a_id;
                        filtro_documenti.nome_documento = "";

                        getDocumentiElemento(filtro_documenti);

                        filtro_workflow.record = data[0].relazionato_a_id;
                        filtro_workflow.nome_workflow = "";

                        getWorkflowElemento(filtro_workflow);

                        if(privilegio_modifica_bpmn && elemento_selezionato.id == processo_aperto.id){
                            jbottone_genera_revisione.show();
                        }
                        else{
                            jbottone_genera_revisione.hide();
                        }

                    }

                },
                fail: function() {

                }
            });

        }

    });

}

function getRuoliElemento(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetRuoliElemento.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_ruoli_temp = "";

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_ruoli_temp += "<tr>";
                    lista_ruoli_temp += "<td>" + data[i].nome + "</td>";
                    lista_ruoli_temp += "</tr>";

                }

            } else {

                lista_ruoli_temp += "<tr><td colspan='5' style='text-align: center;'><em>Nessun ruolo trovato!</em></td></tr>";

            }

            jbody_tabella_ruoli_attivita.empty();
            jbody_tabella_ruoli_attivita.append(lista_ruoli_temp);

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function getDocumentiElemento(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetDocumentiElemento.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_documenti_temp = "";
            documento = [];

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_documenti_temp += "<tr>";
                    lista_documenti_temp += "<td style='width: 80px; text-align: center;'>";
                    lista_documenti_temp += "<button id='down_" + data[i].notesid + "' class='bottone_download_documento mdl-button mdl-js-button mdl-button--icon'>";
                    lista_documenti_temp += "<i class='material-icons'>file_download</i>";
                    lista_documenti_temp += "</button>";
                    lista_documenti_temp += "</td>";
                    lista_documenti_temp += "<td id='td_doc_" + data[i].notesid + "' style='cursor: pointer;' class='bottone_goto_documento' >" + data[i].title + "</td>";
                    lista_documenti_temp += "</tr>";

                    documento[data[i].notesid] = {
                        notesid: data[i].notesid,
                        attachmentsid: data[i].attachmentsid,
                        title: data[i].title
                    };

                }

            } else {

                lista_documenti_temp += "<tr><td colspan='5' style='text-align: center;'><em>Nessun documento trovato!</em></td></tr>";

            }

            jbody_tabella_documenti.empty();
            jbody_tabella_documenti.append(lista_documenti_temp);

            jbottone_download_documento = jQuery(".bottone_download_documento");
            jbottone_goto_documento = jQuery(".bottone_goto_documento");

            jbottone_download_documento.click(function() {

                var documento_selezionato = jQuery(this).attr("id");
                documento_selezionato = documento_selezionato.substring(5, documento_selezionato.length);

                location.href = "Smarty/templates/SproCore/KpProcessGraphHomeViewer/DownloadDocumentoProcedura.php?fileid=" + documento[documento_selezionato].attachmentsid + "&entityid=" + documento_selezionato;

            });

            jbottone_goto_documento.click(function() {

                var documento_selezionato = jQuery(this).attr("id");
                documento_selezionato = documento_selezionato.substring(7, documento_selezionato.length);

                window.open("index.php?module=Documents&parenttab=Support&action=DetailView&record=" + documento_selezionato, "_blank");

            });

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function tabSelezionato(tab) {

    tab_selezionato = tab;

}

function getListaAziende(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetListaAziende.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_aziende_temp = "";
            azienda = [];

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_aziende_temp += "<tr style='vertica-align: middle;'>";
                    lista_aziende_temp += "<td style='width: 80px; text-align: center;'>";
                    lista_aziende_temp += "<button id='sel_" + data[i].id + "' class='bottone_seleziona_azienda mdl-button mdl-js-button mdl-button--icon'>";
                    lista_aziende_temp += "<i class='material-icons'>add</i>";
                    lista_aziende_temp += "</button>";
                    lista_aziende_temp += "</td>";
                    lista_aziende_temp += "<td style='padding-top: 15px;'>" + data[i].nome + "</td>";
                    lista_aziende_temp += "<td style='padding-top: 15px;'>" + data[i].citta + "</td>";
                    lista_aziende_temp += "</tr>";

                    azienda[data[i].id] = {
                        id: data[i].id,
                        nome: data[i].nome,
                        citta: data[i].citta
                    };

                }

            } else {

                lista_aziende_temp += "<tr><td colspan='10' style='text-align: center;'><em>Nessuna azienda trovata!</em></td></tr>";

            }

            jbody_tabella_aziende.empty();
            jbody_tabella_aziende.append(lista_aziende_temp);

            jbottone_seleziona_azienda = jQuery(".bottone_seleziona_azienda");

            jbottone_seleziona_azienda.click(function() {

                var selezionato_temp = jQuery(this).attr("id");
                selezionato_temp = selezionato_temp.substring(4, selezionato_temp.length);

                azienda_selezionata = selezionato_temp;
                azienda_selezionata_nome = azienda[azienda_selezionata].nome;

                jsearch_azienda.val(azienda_selezionata_nome);

                filtro_stabilimenti.azienda = azienda_selezionata_nome;
                jsearch_nome_azienda_stabilimento.val(azienda_selezionata_nome);

                stabilimento_selezionato = 0;
                stabilimento_selezionato_nome = "";

                jsearch_stabilimento.val(stabilimento_selezionato_nome);

                jpopup_aziende.modal("hide");

                getListaStabilimenti(filtro_stabilimenti);

                setFiltroProcessi();

            });

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function getListaStabilimenti(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetListaStabilimenti.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_stabilimenti_temp = "";
            stabilimento = [];

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_stabilimenti_temp += "<tr style='vertica-align: middle;'>";
                    lista_stabilimenti_temp += "<td style='width: 80px; text-align: center;'>";
                    lista_stabilimenti_temp += "<button id='sel_" + data[i].id + "' class='bottone_seleziona_stabilimento mdl-button mdl-js-button mdl-button--icon'>";
                    lista_stabilimenti_temp += "<i class='material-icons'>add</i>";
                    lista_stabilimenti_temp += "</button>";
                    lista_stabilimenti_temp += "</td>";
                    lista_stabilimenti_temp += "<td style='padding-top: 15px;'>" + data[i].nome + "</td>";
                    lista_stabilimenti_temp += "<td style='padding-top: 15px;'>" + data[i].azienda + "</td>";
                    lista_stabilimenti_temp += "<td style='padding-top: 15px;'>" + data[i].citta + "</td>";
                    lista_stabilimenti_temp += "</tr>";

                    stabilimento[data[i].id] = {
                        id: data[i].id,
                        nome: data[i].nome,
                        azienda_id: data[i].azienda_id,
                        azienda: data[i].azienda,
                        citta: data[i].citta
                    };

                }

            } else {

                lista_stabilimenti_temp += "<tr><td colspan='10' style='text-align: center;'><em>Nessun stabilimento trovato!</em></td></tr>";

            }

            jbody_tabella_stabilimenti.empty();
            jbody_tabella_stabilimenti.append(lista_stabilimenti_temp);

            jbottone_seleziona_stabilimento = jQuery(".bottone_seleziona_stabilimento");

            jbottone_seleziona_stabilimento.click(function() {

                var selezionato_temp = jQuery(this).attr("id");
                selezionato_temp = selezionato_temp.substring(4, selezionato_temp.length);

                stabilimento_selezionato = selezionato_temp;
                stabilimento_selezionato_nome = stabilimento[stabilimento_selezionato].nome;

                azienda_selezionata = stabilimento[stabilimento_selezionato].azienda_id;
                azienda_selezionata_nome = stabilimento[stabilimento_selezionato].azienda;

                jsearch_azienda.val(azienda_selezionata_nome);
                jsearch_stabilimento.val(stabilimento_selezionato_nome);

                filtro_stabilimenti.azienda = azienda_selezionata_nome;
                jsearch_nome_azienda_stabilimento.val(azienda_selezionata_nome);

                jpopup_stabilimenti.modal("hide");

                getListaStabilimenti(filtro_stabilimenti);

                setFiltroProcessi();

            });

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function setFiltroProcessi() {

    inizializzazioneAlberoProcessi();

}

function getWorkflowElemento(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetWorkflowElemento.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_workflow_temp = "";

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_workflow_temp += "<tr>";
                    lista_workflow_temp += "<td style='width: 80px; text-align: center;'>";
                    lista_workflow_temp += "<button id='view_" + data[i].id + "' class='bottone_visualizza_workflow mdl-button mdl-js-button mdl-button--icon'>";
                    lista_workflow_temp += "<i class='material-icons'>visibility</i>";
                    lista_workflow_temp += "</button>";
                    lista_workflow_temp += "</td>";
                    lista_workflow_temp += "<td>" + data[i].nome + "</td>";
                    lista_workflow_temp += "</tr>";

                }

            } else {

                lista_workflow_temp += "<tr><td colspan='5' style='text-align: center;'><em>Nessun workflow trovato!</em></td></tr>";

            }

            jbody_tabella_workflow.empty();
            jbody_tabella_workflow.append(lista_workflow_temp);

            jbottone_visualizza_workflow = jQuery(".bottone_visualizza_workflow");

            jbottone_visualizza_workflow.click(function() {

                var workflow_selezionato = jQuery(this).attr("id");
                workflow_selezionato = workflow_selezionato.substring(5, workflow_selezionato.length);

                window.open("index.php?module=Settings&action=SettingsAjax&file=ProcessMaker&parenttab=Settings&mode=detail&id=" + workflow_selezionato, "_blank");

            });

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function getRisorseElemento(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetRisorseElemento.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_risorse_temp = "";

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_risorse_temp += "<tr>";
                    lista_risorse_temp += "<td>" + data[i].cognome + " " + data[i].nome + "</td>";
                    lista_risorse_temp += "<td>" + data[i].nome_azienda + "</td>";
                    lista_risorse_temp += "<td>" + data[i].nome_stabilimento + "</td>";
                    lista_risorse_temp += "</tr>";

                }

            } else {

                lista_risorse_temp += "<tr><td colspan='5' style='text-align: center;'><em>Nessuna risorsa trovata!</em></td></tr>";

            }

            jbody_tabella_risorse_attivita.empty();
            jbody_tabella_risorse_attivita.append(lista_risorse_temp);

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function applicaStileShape(object) {

    if (object.type == "bpmn:SubProcess") {

        canvas.toggleMarker(object.id, 'SubProcess-shape');

    } else if (object.type == "bpmn:StartEvent") {

        canvas.toggleMarker(object.id, 'StartEvent-shape');

    } else if (object.type == "bpmn:EndEvent") {

        canvas.toggleMarker(object.id, 'EndEvent-shape');

    } else if (object.type == "bpmn:Task") {

        canvas.toggleMarker(object.id, 'Task-shape');

    }

}

function applicaStileAggiuntivoShape(lista_elementi) {

    //console.table(lista_elementi);

    for (var i = 0; i < lista_elementi.length; i++) {

        if (lista_elementi[i].tipo_entita_bpmn == "subProcess" && lista_elementi[i].procedureid == "0") {

            canvas.toggleMarker(lista_elementi[i].bpmn_id, 'SubProcessNonCollegato-shape');

        }

        applicaStileValoreAggiuntoNonValoreAggiunto(lista_elementi[i]);

    }

}

function applicaStileValoreAggiuntoNonValoreAggiunto(elemento) {

    switch (elemento.valore_aggiunto) {
        case "A valore aggiunto":
            canvas.toggleMarker(elemento.bpmn_id, 'ValoreAggiunto-shape');
            break;
        case "Non a valore ma necessaria":
            canvas.toggleMarker(elemento.bpmn_id, 'NonValoreAggiuntoNecessaria-shape');
            break;
        case "Non a valore e non necessaria":
            canvas.toggleMarker(elemento.bpmn_id, 'NonValoreAggiuntoNonNecessaria-shape');
            break;
    }

}

function evidenziaTask(valore) {

    //console.table(elemento_by_id);

    jQuery.each(elementRegistry.getAll(), function(index, object) {

        //console.log(object.id);
        //console.log(jQuery.inArray(object.id, array_bpmn_id_elementi));

        if (jQuery.inArray(String(object.id), array_bpmn_id_elementi) != -1 && object.type != "bpmn:SubProcess" && object.type != "bpmn:StartEvent" && object.type != "bpmn:EndEvent") {

            if (elemento_by_id[object.id].valore_aggiunto == valore && valore != "") {

                canvas.toggleMarker(object.id, 'Evidenziate-shape');

            } else {

                canvas.removeMarker(object.id, 'Evidenziate-shape');

            }

        }

    });

}

function setArrayListaElementiById(lista_elementi) {

    elemento_by_id = [];
    array_bpmn_id_elementi = [];

    for (var i = 0; i < lista_elementi.length; i++) {

        //console.log(lista_elementi[i]);

        elemento_by_id[lista_elementi[i].bpmn_id] = {
            bpmn_id: lista_elementi[i].bpmn_id,
            crm_id: lista_elementi[i].id,
            valore_aggiunto: lista_elementi[i].valore_aggiunto,
            tipo_entita_bpmn: lista_elementi[i].tipo_entita_bpmn,
        };

        array_bpmn_id_elementi.push(lista_elementi[i].bpmn_id);

    }

    //console.table(elemento_by_id);

}

function getRischiElemento(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetRischiElemento.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            var lista_temp = "";

            if (data.lista_rischi_qualita.length + data.lista_rischi_privacy.length + data.lista_rischi_sicurezza.length > 0) {

                for (var i = 0; i < data.lista_rischi_qualita.length; i++) {

                    lista_temp += "<tr>";
                    lista_temp += "<td>" + data.lista_rischi_qualita[i].nome + "</td>";
                    lista_temp += "<td style='width: 100px;'>" + data.lista_rischi_qualita[i].tipo + "</td>";
                    lista_temp += "</tr>";

                }

                for (var y = 0; y < data.lista_rischi_privacy.length; y++) {

                    lista_temp += "<tr>";
                    lista_temp += "<td>" + data.lista_rischi_privacy[y].nome + "</td>";
                    lista_temp += "<td style='width: 100px;'>" + data.lista_rischi_privacy[y].tipo + "</td>";
                    lista_temp += "</tr>";
                }

                for (var z = 0; z < data.lista_rischi_sicurezza.length; z++) {

                    lista_temp += "<tr>";
                    lista_temp += "<td>" + data.lista_rischi_sicurezza[z].nome + "</td>";
                    lista_temp += "<td style='width: 100px;'>" + data.lista_rischi_sicurezza[z].tipo + "</td>";
                    lista_temp += "</tr>";
                }

            } else {

                lista_temp += "<tr><td colspan='5' style='text-align: center;'><em>Nessun rischio trovato!</em></td></tr>";

            }

            jbody_tabella_rischi.empty();
            jbody_tabella_rischi.append(lista_temp);

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function getWizardNonConformita() {

    window.open("index.php?module=NonConformita&action=index&parenttab=Qualita&kp_action=wizardNonConformitaDaEntitaProcesso&entita_processo=" + elemento_selezionato.id, "_blank");

}

function getPrivilegiUtente() {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetPrivilegiUtente.php',
        dataType: 'json',
        async: true,
        beforeSend: function() {

        },
        success: function(data) {

            if (data.length > 0) {

                if (data[0].privilegio_modifica == "yes") {

                    privilegio_modifica_bpmn = true;
                    jbottone_modifica_disegno_processo.show();

                } else {

                    privilegio_modifica_bpmn = false;
                    jbottone_modifica_disegno_processo.hide();

                }

            }

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function generaRevisione(){

    var dati = {
        record: processo_aperto.id
    };
    
    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/SetRevisioneProcedura.php',
        dataType: 'json',
        async: true,
        data: dati,
        beforeSend: function() {

        },
        success: function(data) {

            if( data.record != 0 ){
                //window.open("index.php?module=KpProcedure&parenttab=Qualita&action=DetailView&record=" + data.record, "_blank");
                window.open("modules/SproCore/CustomViews/KpBPMNcreator/index.php?record=" + data.record, "_blank");
            }

            in_salvataggio = false;

        },
        fail: function() {

            console.error("Errore");
            in_salvataggio = false;
            location.reload();

        }
    });

}

function getBPMNioSVG(){

    bpmnViewer.saveSVG({}, function(err, svg) {

        //console.log(err);
        if (svg) {

            //console.log(svg);
            salvaSVG(svg);

        }

    });

}

function getStampa(tipo_stampa){

    bpmnViewer.saveSVG({}, function(err, svg) {

        //console.log(err);
        if (svg) {

            var dati = {
                svg: svg,
                crmid: processo_aperto.id,
            };
        
            jQuery.ajax({
                url: 'Smarty/templates/SproCore/KpProcessGraphHomeViewer/SalvaSVG.php',
                dataType: 'json',
                type: "POST",
                async: true,
                data: dati,
                beforeSend: function() {
        
                    dhtmlx.message({
                        id: "dhtmlx_salvataggio_in_corso",
                        type: "error",
                        text: "Salvataggio in corso!"
                    });
        
                },
                success: function(data) {
        
                    //console.table(data);
        
                    dhtmlx.message.hide("dhtmlx_salvataggio_in_corso");

                    if( tipo_stampa == "SVG" ){
                        window.open("Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetStampaSVG.php?record=" + processo_aperto.id, "_blank");
                    }
                    else if( tipo_stampa == "PNG" ){
                        window.open("Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetStampaPNG.php?record=" + processo_aperto.id, "_blank");
                    }
                    else if( tipo_stampa == "PDF" ){
                        window.open("Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetStampaPDF.php?record=" + processo_aperto.id, "_blank");
                    }
        
                },
                fail: function() {
        
                    dhtmlx.message.hide("dhtmlx_salvataggio_in_corso");
                    console.error("Errore nel salvataggio SVG");
        
                }
            });
            
        }

    });

}

function getStampaAll(tipo_stampa){

    if( tipo_stampa == "PDFall" ){
        window.open("Smarty/templates/SproCore/KpProcessGraphHomeViewer/GetStampaPDFall.php?record=" + processo_aperto.id + "&azienda=" + azienda_selezionata + "&stabilimento=" + stabilimento_selezionato, "_blank");
    }

}

function getPadreByArray(array){

    padre = array.join('_');

    return padre;

}

function closeAllRoots(escludi){
    
    var rootsAr = mProTree.getSubItems(0).split(",");
    
    chiusuraRicorsivaNodiAlbero(rootsAr, escludi);
}

function chiusuraRicorsivaNodiAlbero(items, escludi){

    //console.log("chiusuraRicorsivaNodiAlbero: " + items);
    //console.log("chiusuraRicorsivaNodiAlbero: escludi " + escludi);

    var figli = [];
    var item_percorso = [];

    for (var i = 0; i < items.length; i++){

        if(items[i] != escludi && !escludi.startsWith(items[i]) ){

            item_percorso = items[i].split("_");

            if(item_percorso[0] != "tp"){   //In questo modo non chiudo il primissimo livello
                //console.log("chiusuraRicorsivaNodiAlbero: Chiusura Item " + items[i] + " " + mProTree.getItemText(items[i]));
                mProTree.closeAllItems(items[i])
            }

            figli = mProTree.getSubItems(items[i]);

            if( figli != "" ){

                figli = figli.split(",");
                chiusuraRicorsivaNodiAlbero(figli, escludi);

            }

        }

    }

}

function apriPopupAggiuntaNonConformita(){

    var url = "index.php?module=NonConformita&action=EditView&return_action=DetailView&folderid=0&parenttab=Qualita";

    url += "&programma=KpProcedure";
    //url += "&azienda=" + azienda_selezionata;
    //url += "&stabilimento=" + stabilimento_selezionato;
    url += "&processo=" + processo_aperto.id;
    url += "&attivita=" + elemento_selezionato.id;
    //url += "&rischio=" + rischio_selezionato;

    window.open(url, '_blank');

}