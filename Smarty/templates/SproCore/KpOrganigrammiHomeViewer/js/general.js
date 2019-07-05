/* kpro@tom18072017 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */

var altezza_schermo;
var larghezza_schermo;

var mProLayout;
var mProTree;
var diagram;

var organogramData = [];
var elemento_selezionato_albero = {};
var unita_organizzativa_selezionata = {};
var privilegio_modifica = false;
var in_salvataggio = false;

var organigramma_aperto = {};

var jlayoutObj;
var jgraphContainer;
var jdettagliContainer;
var jbottone_modifica_disegno;
var jbottone_zoom_piu;
var jbottone_zoom_meno;
var jreadonly_immagine_risorsa;
var jreadonly_risorsa;
var jreadonly_ruolo;
var jreadonly_staff;
var jbody_tabella_processi_unita;
var jbottone_genera_revisione;
var jbottone_stampa_html;
var jbottone_centra_disegno;
var jprocessi_unita;
var jinformazioni_unita;

var filtro_organigrammi = {};

var altezza_layoutObj;


jQuery(document).ready(function() {

    inizializzazione();

    inizializzazioneLayout();

});

function inizializzazione() {

    jlayoutObj = jQuery("#layoutObj");
    jgraphContainer = jQuery("#graphContainer");
    jdettagliContainer = jQuery("#dettagliContainer");

    jpopup_generico = jQuery("#popup_generico");
    jbottone_modifica_disegno = jQuery("#bottone_modifica_disegno");
    jbottone_zoom_piu = jQuery("#bottone_zoom_piu");
    jbottone_zoom_meno = jQuery("#bottone_zoom_meno");
    jbottone_centra_disegno = jQuery("#bottone_centra_disegno");

    window.addEventListener('resize', function() {
        reSize();
    }, false);

    reSize();

    getPrivilegiUtente();

    jbottone_modifica_disegno.click(function() {

        if (organigramma_aperto.id != "" && organigramma_aperto.id != 0) {

            window.open("modules/SproCore/CustomViews/KpOrganigrammaCreator/index.php?record=" + organigramma_aperto.id, "_blank");

        }

    });

    jbottone_zoom_piu.click(function() {

        diagram.config.scale = diagram.config.scale + 0.2;
        diagram.data.parse(organogramData);

    });

    jbottone_zoom_meno.click(function() {

        diagram.config.scale = diagram.config.scale - 0.2;
        diagram.data.parse(organogramData);

    });

    jbottone_centra_disegno.click(function() {

        adattaZoomOrganigramma();

    });

}

function reSize() {

    larghezza_schermo = window.innerWidth;
    altezza_schermo = window.innerHeight;

    if(window.current_theme == 'spro_next'){
        jlayoutObj.css("height", innerHeight - 80);
        altezza_layoutObj = altezza_schermo - 140;
    }
    else{
        jlayoutObj.css("height", innerHeight - 205);  
        altezza_layoutObj = altezza_schermo - 205;
    }

    jQuery(".dhx_diagram_wrapper").css("height", jgraphContainer.css("height"));
    jQuery(".dhx_diagram_wrapper").css("width", jgraphContainer.css("width"));

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
    mProLayout.cells("a").setHeight(250);
    mProLayout.cells("a").collapse();

    mProLayout.cells("b").setText("Lista Organigrammi");
    mProLayout.cells("b").setCollapsedText("Lista Organigrammi");
    mProLayout.cells("b").setWidth(300);

    mProLayout.cells("c").setText("Organigramma");
    mProLayout.cells("c").setCollapsedText("Organigramma");

    mProLayout.cells("d").setText("Dettagli");
    mProLayout.cells("d").setCollapsedText("Dettagli");
    mProLayout.cells("d").collapse();

    mProLayout.cells("a").attachObject("filtriContainer");

    mProLayout.cells("c").attachObject("graphContainer");

    mProLayout.cells("d").attachObject("dettagliContainer");

    inizializzazioneAlberoProcessi();

    mProLayout.attachEvent("onResizeFinish", function() {
        reSize();
    });

    mProLayout.attachEvent("onPanelResizeFinish", function(names) {
        reSize();
    });

    mProLayout.attachEvent("onCollapse", function(name) {
        reSize();
    });

    mProLayout.attachEvent("onExpand", function(name) {
        reSize();
    });

}

function inizializzazioneAlberoProcessi() {

    mProTree = mProLayout.cells("b").attachTree();
    mProTree.setImagesPath("Smarty/templates/SproCore/KpOrganigrammiHomeViewer/dhtmlx_codebase/imgs/dhxtree_material/");

    mProTree.setOnClickHandler(function(id) {

        var elemento_selezionato_albero_temp = id.substring(0, 3);

        if (elemento_selezionato_albero_temp != "tp_") {

            elemento_selezionato_albero.id = id;
            elemento_selezionato_albero.nome = mProTree.getItemText(id);

            mProLayout.cells("c").setText(elemento_selezionato_albero.nome);
            mProLayout.cells("c").setCollapsedText(elemento_selezionato_albero.nome);

            getOrganigramma(elemento_selezionato_albero.id);

            organigramma_aperto.id = id;
            organigramma_aperto.nome = mProTree.getItemText(id);

        } else {

            mProTree.selectItem(elemento_selezionato_albero.id);

        }

    });

    filtro_organigrammi = {
        azienda: "",
        stabilimento: ""
    };

    inizializzazioneOrganigramma();

    getListaOrganigrammi(filtro_organigrammi);

}

function getListaOrganigrammi(filtro) {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpOrganigrammiHomeViewer/php/getListaOrganigrammi.php',
        dataType: 'json',
        async: true,
        data: filtro,
        beforeSend: function() {

        },
        success: function(data) {

            //console.table(data);

            var first_element_temp = "";
            var first_element_text_temp = "";

            var xml = '<?xml version="1.0" encoding="iso-8859-1"?>';
            xml += '<tree id="0">';

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    xml += '<item text="' + data[i].nome + '" id="tp_' + data[i].id + '" open="1">';

                    //console.log(data[i].lista_organigrammi);

                    if(data[i].lista_organigrammi.length > 0){

                        var albero_organigrammi = getXMLAlberoOrganigrammi(data[i].lista_organigrammi);

                        if (i == 0) {

                            first_element_temp = albero_organigrammi.primo_elemento_id;
                            first_element_text_temp = albero_organigrammi.primo_elemento_nome;

                            organigramma_aperto.id = albero_organigrammi.primo_elemento_id;
                            organigramma_aperto.nome = albero_organigrammi.primo_elemento_nome;

                        }

                        xml += albero_organigrammi.xml;

                    }

                    xml += '</item>';

                }

            }

            xml += '</tree>';

            //console.log(xml);

            mProTree.parse(xml);

            if (first_element_temp != "" && first_element_temp != 0) {

                elemento_selezionato_albero = {};

                elemento_selezionato_albero.id = first_element_temp;
                elemento_selezionato_albero.nome = first_element_text_temp;

                mProTree.selectItem(elemento_selezionato_albero.id);

                mProLayout.cells("c").setText(elemento_selezionato_albero.nome);
                mProLayout.cells("c").setCollapsedText(elemento_selezionato_albero.nome);

                getOrganigramma(elemento_selezionato_albero.id);

                if ( privilegio_modifica ){
                    jbottone_modifica_disegno.show();
                }

            }
            else{

                jbottone_modifica_disegno.hide();

            }

        },
        fail: function() {

        }
    });

}

function getXMLAlberoOrganigrammi(dati) {

    var result = {
        xml: "",
        primo_elemento_id: 0,
        primo_elemento_nome: ""
    };

    for (var i = 0; i < dati.length; i++) {

        result.xml += '<item text="' + dati[i].nome + '" id="' + dati[i].id + '" />';

        if (i == 0) {
            result.primo_elemento_id = dati[i].id;
            result.primo_elemento_nome = dati[i].nome;
        }

    }

    return result;

}

function inizializzazioneOrganigramma() {

    diagram = new dhx.Diagram("graphContainer", {
        type: "org",
        defaultShapeType: "img-card",
        select: true,
        scroll: true,
        dragMode: false,
        showGrid: false,
        scale: 1
    });

    reSize();

    diagram.events.on("AfterSelect", function(id) {
        //console.log("onAfterSelect");

        unita_organizzativa_selezionata = diagram.data.getItem(id);

        //console.log(unita_organizzativa_selezionata);

        diagram.showItem(unita_organizzativa_selezionata.id);

        getTemplateUnitaOrganizzativa(unita_organizzativa_selezionata);

    });

}

function getPrivilegiUtente() {

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpOrganigrammiHomeViewer/php/getPrivilegiUtente.php',
        dataType: 'json',
        async: true,
        beforeSend: function() {

        },
        success: function(data) {

            if (data.length > 0) {

                if (data[0].privilegio_modifica == "yes") {

                    privilegio_modifica = true;
                    jbottone_modifica_disegno.show();

                } else {

                    privilegio_modifica = false;
                    jbottone_modifica_disegno.hide();

                }

            }

        },
        fail: function() {

            console.error("Errore");

            location.reload();

        }
    });

}

function getOrganigramma(record) {

    var dati = {
        record: record
    };

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpOrganigrammiHomeViewer/php/getOrganigramma.php',
        dataType: 'json',
        async: true,
        method: 'POST',
        data: dati,
        beforeSend: function() {


        },
        success: function(data) {

            organogramData = [];
            unita_organizzativa_selezionata = {};

            var entita_temp = {}
            var staff = false;
            var open = true;
            var dir = "";

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    if (data[i].in_staff == "1") {
                        staff = true;
                    } else {
                        staff = false;
                    }

                    if (data[i].chiuso == "1") {
                        open = false;
                    } else {
                        open = true;
                    }

                    if (data[i].verticale == "1") {
                        dir = "vertical";
                    } else {
                        dir = "";
                    }

                    entita_temp = {};

                    var temp_immagine = "";
					if( data[i].immagine != "" ){
						temp_immagine = data[i].immagine;
					}
					else{
						temp_immagine = "modules/SproCore/CustomViews/KpOrganigrammaCreator/img/risorsa.png";
					}

                    entita_temp = {
                        id: data[i].id_creator,
                        id_crm: data[i].id,
                        text: data[i].nome_ruolo,
                        title: data[i].cognome_risorsa + " " + data[i].nome_risorsa,
                        risorsa_id: data[i].risorsa,
                        ruolo_id: data[i].ruolo,
                        img: temp_immagine,
                        width: 300,
                        from_crm: true,
                        staff: staff,
                        open: open,
                        dir: dir
                    };

                    if (data[i].x == "") {
                        entita_temp.x = data[i].x;
                    }

                    if (data[i].y == "") {
                        entita_temp.y = data[i].y;
                    }

                    organogramData.push(entita_temp);

                    if (data[i].entita_figlie.length > 0) {

                        pushEntitaFiglie(data[i].entita_figlie);

                    }

                }

                //console.table(organogramData);

                diagram.data.parse(organogramData);

                if (organogramData.length > 0) {

                    unita_organizzativa_selezionata = organogramData[0];

                    diagram.showItem(unita_organizzativa_selezionata.id);

                    getTemplateUnitaOrganizzativa(unita_organizzativa_selezionata);

                }

            }
            else{
                diagram.data.parse(organogramData);
            }

        },
        fail: function() {

            console.error("Errore");

        }
    });

}

function pushEntitaFiglie(entita_figlie) {

    var entita_temp = {}
    var entita_link_temp = {}
    var staff = false;
    var open = true;
    var dir = "";

    for (var y = 0; y < entita_figlie.length; y++) {

        if (entita_figlie[y].in_staff == "1") {
            staff = true;
        } else {
            staff = false;
        }

        if (entita_figlie[y].chiuso == "1") {
            open = false;
        } else {
            open = true;
        }

        if (entita_figlie[y].verticale == "1") {
            dir = "vertical";
        } else {
            dir = "";
        }

        entita_temp = {};

        var temp_immagine = "";
        if( entita_figlie[y].immagine != "" ){
            temp_immagine = entita_figlie[y].immagine;
        }
        else{
            temp_immagine = "modules/SproCore/CustomViews/KpOrganigrammaCreator/img/risorsa.png";
        }

        entita_temp = {
            id: entita_figlie[y].id_creator,
            id_crm: entita_figlie[y].id,
            text: entita_figlie[y].nome_ruolo,
            title: entita_figlie[y].cognome_risorsa + " " + entita_figlie[y].nome_risorsa,
            risorsa_id: entita_figlie[y].risorsa,
            ruolo_id: entita_figlie[y].ruolo,
            img: temp_immagine,
            width: 300,
            from_crm: true,
            staff: staff,
            open: open,
            dir: dir
        };

        if (entita_figlie[y].x == "") {
            entita_temp.x = entita_figlie[y].x;
        }

        if (entita_figlie[y].y == "") {
            entita_temp.y = entita_figlie[y].y;
        }

        if (staff) {

            entita_link_temp = {
                id: entita_figlie[y].subordinato_a_org_id + "-" + entita_figlie[y].id_creator,
                from: entita_figlie[y].subordinato_a_org_id,
                to: entita_figlie[y].id_creator,
                css: "staffConnector",
                type: "line"
            }

        } else {

            entita_link_temp = {
                id: entita_figlie[y].subordinato_a_org_id + "-" + entita_figlie[y].id_creator,
                from: entita_figlie[y].subordinato_a_org_id,
                to: entita_figlie[y].id_creator,
                css: "standardConnector",
                type: "line"
            }

        }

        organogramData.push(entita_temp);
        organogramData.push(entita_link_temp);

        if (entita_figlie[y].entita_figlie.length > 0) {

            pushEntitaFiglie(entita_figlie[y].entita_figlie);

        }

    }

}

function getTemplateUnitaOrganizzativa(elemento) {

    mProLayout.cells("d").setText("Dettagli: " + elemento.title + " (" + elemento.text + ")");
    mProLayout.cells("d").setCollapsedText("Dettagli: " + elemento.title + " (" + elemento.text + ")");

    jQuery.get("Smarty/templates/SproCore/KpOrganigrammiHomeViewer/templates/dettagli_unita_organizzativa.html", function(data) {

        //console.table(data);

        jdettagliContainer.empty();
        jdettagliContainer.append(data);

        jreadonly_immagine_risorsa = jQuery("#readonly_immagine_risorsa");
        jreadonly_risorsa = jQuery("#readonly_risorsa");
        jreadonly_ruolo = jQuery("#readonly_ruolo");
        jreadonly_staff = jQuery("#readonly_staff");
        jbody_tabella_processi_unita = jQuery("#body_tabella_processi_unita");
        jbottone_genera_revisione = jQuery("#bottone_genera_revisione");
        jbottone_stampa_html = jQuery("#bottone_stampa_html");
        jprocessi_unita = jQuery("#processi_unita");
        jinformazioni_unita = jQuery("#informazioni_unita");
        jprocessi_unita.css("height", altezza_layoutObj - 100);
        jinformazioni_unita.css("height", altezza_layoutObj - 100);

        jreadonly_immagine_risorsa.attr("src", elemento.img);
        jreadonly_risorsa.val(elemento.title);
        jreadonly_ruolo.val(elemento.text);

        if (elemento.staff) {
            jreadonly_staff.prop("checked", true);
        } else {
            jreadonly_staff.prop("checked", false);
        }

        if(privilegio_modifica){
            jbottone_genera_revisione.show();
        }
        else{
            jbottone_genera_revisione.hide();
        }

        jbottone_genera_revisione.click(function(){
            if( privilegio_modifica && !in_salvataggio && organigramma_aperto.id != "" && organigramma_aperto.id != 0){
                in_salvataggio = true;
                generaRevisione();
            }
        });

        jbottone_stampa_html.click(function(){

            getStampa("HTML");

        });

        getListaProcessiUnitaOrganizzativa(elemento);

    });

}

function getListaProcessiUnitaOrganizzativa(elemento) {

    var dati = {
        id: elemento.id_crm
    };

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpOrganigrammiHomeViewer/php/getListaProcessi.php',
        dataType: 'json',
        async: true,
        method: 'POST',
        data: dati,
        beforeSend: function() {


        },
        success: function(data) {

            var lista_record_temp = "";

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {

                    lista_record_temp += "<tr style='border-top: 1px solid silver;' >";

                    lista_record_temp += "<td rowspan='" + data[i].lista_attivita.length + "' style='text-align: left; vertical-align: top;'>";
                    lista_record_temp += data[i].nome_processo;
                    lista_record_temp += "</td>";

                    for (var y = 0; y < data[i].lista_attivita.length; y++) {

                        if (y == 0) {
                            lista_record_temp += "<td style='text-align: left;'>";
                            lista_record_temp += data[i].lista_attivita[y].nome;
                            lista_record_temp += "</td>";
                        } else {
                            lista_record_temp += "<tr>";
                            lista_record_temp += "<td style='text-align: left;'>";
                            lista_record_temp += data[i].lista_attivita[y].nome;
                            lista_record_temp += "</td>";
                            lista_record_temp += "</tr>";
                        }

                    }

                    lista_record_temp += "</tr>";

                }

            } else {

                lista_record_temp += "<tr><td colspan='5' style='text-align: center;'><em>Nessun processo trovato!</em></td></tr>";

            }

            //console.table(lista_record_temp);

            jbody_tabella_processi_unita.empty();
            jbody_tabella_processi_unita.append(lista_record_temp);

        },
        fail: function() {

            console.error("Errore");

        }
    });

}

function generaRevisione(){

    var dati = {
        record: organigramma_aperto.id
    };
    
    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpOrganigrammiHomeViewer/php/setRevisioneOrganigramma.php',
        dataType: 'json',
        async: true,
        data: dati,
        beforeSend: function() {

        },
        success: function(data) {

            if( data.record != 0 ){
                //window.open("index.php?module=KpOrganigrammi&parenttab=Qualita&action=DetailView&record=" + data.record, "_blank");
                window.open("modules/SproCore/CustomViews/KpOrganigrammaCreator/index.php?record=" + data.record, "_blank");
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

function getStampa(tipo_stampa){

    getHTMLElementByClass("dhx_org_chart");

}

function printElemById(elem){
    var mywindow = window.open('', 'PRINT', 'height=700,width=700');

    mywindow.document.write('<html><head><title>' + document.title  + '</title>');
    mywindow.document.write('<head>');
    mywindow.document.write('<link rel="stylesheet" type="text/css" href="modules/SproCore/CustomViews/KpOrganigrammaCreator/dhtmlx_organigrammi/diagram.css"/>');
    mywindow.document.write('</head>');
    mywindow.document.write('</head><body >');
    mywindow.document.write('<h1>' + document.title  + '</h1>');
    mywindow.document.write(document.getElementById(elem).innerHTML);
    mywindow.document.write('</body></html>');

    mywindow.document.close(); // necessary for IE >= 10
    mywindow.focus(); // necessary for IE >= 10*/

    //console.log(mywindow);
    mywindow.print();
    mywindow.close();

    return true;
}

function getHTMLElementByClass(elem){

    if( document.getElementsByClassName(elem).length > 0 ){

        //var mywindow = window.open('', 'PRINT', 'height=700,width=700');

        var mywindow = window.open('', 'PRINT', '');

        mywindow.document.write('<html><head><title>' + organigramma_aperto.nome  + '</title>');
        mywindow.document.write('<head>');
        mywindow.document.write('<link rel="stylesheet" type="text/css" href="modules/SproCore/CustomViews/KpOrganigrammaCreator/dhtmlx_organigrammi/diagram.css"/>');
        mywindow.document.write('</head>');
        mywindow.document.write('</head><body >');
        mywindow.document.write('<h1>' + organigramma_aperto.nome  + '</h1>');
        mywindow.document.write(document.getElementsByClassName(elem)[0].innerHTML);
        mywindow.document.write('</body></html>');

        mywindow.document.close(); // necessary for IE >= 10
        mywindow.focus(); // necessary for IE >= 10*/

        mywindow.print();
        mywindow.close();

        return true;

    }
   
}

function adattaZoomOrganigramma(){

    var graphContainer_height_temp = jgraphContainer.height();
    var graphContainer_width_temp = jgraphContainer.width();

    var snap = Snap(".dhx_org_chart");

    var dhx_org_chart_width_temp = snap.node.clientWidth;
    var dhx_org_chart_height_temp = snap.node.clientHeight;

    console.log("Organigramma width: " + dhx_org_chart_width_temp + " Area width: " + graphContainer_width_temp);
    console.log("Organigramma height: " + dhx_org_chart_height_temp + " Area height: " + graphContainer_height_temp);

    var scale_x_temp = graphContainer_width_temp / dhx_org_chart_width_temp;

    scale_x_temp = 1 - scale_x_temp;

    console.log("scale x: " + scale_x_temp);

    diagram.config.scale = diagram.config.scale - scale_x_temp;
    diagram.data.parse(organogramData);

}

function tabSelezionato(tab){

}