/* kpro@tom30112017 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */


var jbody_tabella_prodotti_servizi = '';
var jbottone_genera;
var jcaricamento;
var jform_percentuale;

var in_salvataggio = false;
var prodotto = [];
var array_prodotti = [];
var prodotti_selezionati = [];
var percentuale = 100;
var parametri_calcolo = 'Residuo';
var form_parametri_calcolo;
var jbottone_indietro;
var jform_modpagamento;
var jdata_fattura;
var jbottone_genera_fattura;

var array_odf = [];

jQuery(document).ready(function() {

    inizializza();

    inizializzazioneBootstrap();

    inizializzazioneExtra();

});

function inizializza() {

    jbody_tabella_prodotti_servizi = jQuery("#body_tabella_prodotti_servizi");
    jbottone_genera = jQuery("#bottone_genera");
    jcaricamento = jQuery("#caricamento");
    jform_percentuale = jQuery("#form_percentuale");
    jform_parametri_calcolo = jQuery("#form_parametri_calcolo");
    jbottone_indietro = jQuery("#bottone_indietro");
    jform_modpagamento = jQuery("#form_modpagamento");
    jdata_fattura = jQuery("#data_fattura");
    jbottone_genera_fattura = jQuery("#bottone_genera_fattura");

    caricaRigheOrdine(record);

    jform_percentuale.change(function() {

        percentuale = jform_percentuale.val();

        parametri_calcolo = jform_parametri_calcolo.val();

        aggiornaQuantitaDaFatturare(percentuale, parametri_calcolo);

    });

    jform_parametri_calcolo.change(function() {

        percentuale = jform_percentuale.val();

        parametri_calcolo = jform_parametri_calcolo.val();

        aggiornaQuantitaDaFatturare(percentuale, parametri_calcolo);

    });

    jbottone_genera.click(function() {

        if (check()) {
            if (!in_salvataggio) {
                leggiForm("Odf");
            }
        } else {
            alert('Errore nel Salvataggio!');
        }

    });

    jbottone_genera_fattura.click(function() {

        if (check()) {
            if (!in_salvataggio) {
                leggiForm("Fattura");
            }
        } else {
            alert('Errore nel Salvataggio!');
        }

    });

    jbottone_indietro.click(function(){
        parent.postMessage("kp-indietro","*");
    });

}

function inizializzazioneBootstrap() {

    jQuery('.campo_data_bootstrap').each(function() {

        var temp_id = jQuery(this).prop("id");

        //Se a fronte del campo con classe "campo_data_bootstrap" esiste un bulsante di tipo "trigger_" allora la funzion imposta
        //il triger su tale pulsante, altrimenti il trigger sarà impostato sul campo data stesso
        if (jQuery("#trigger_" + temp_id).length) {

            setupDatePicker(temp_id, {
                trigger: "trigger_" + temp_id,
                date_format: "%D/%M/%Y".replace('%Y', 'YYYY').replace('%M', 'MM').replace('%D', 'DD'),
                language: "it",
                time: false,
                weekStart: 1,
                cancelText: 'ANNULLA',
                clearText: 'PULISCI',
                clearButton: true,
                nowButton: false,
                switchOnClick: false
            });

        } else {

            jQuery("#" + temp_id).bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                lang: 'it',
                time: false,
                weekStart: 1,
                cancelText: 'ANNULLA',
                clearText: 'PULISCI',
                clearButton: true,
                nowButton: false,
                switchOnClick: false
            });

        }

    });

}

function inizializzazioneExtra(){

    PicklistModalitaPagamento();

}

function chiudiPopUp() {

    //closePopup();
    parent.location.reload();

}

function PicklistModalitaPagamento(){

    jQuery.ajax({
        url: 'modules/SproCore/CustomViews/PopupGenerazioneOdfDaOrdine/PicklistModalitaPagamento.php',
        dataType: 'json',
        async: true,
        beforeSend: function() {

            //jcaricamento.show();

        },
        success: function(data) {

            var lista = "<option value='--Nessuno--' selected='selected'>--Impostazioni predefinite--</option>";

            if(data.length > 0){
                for(var i = 0; i < data.length; i++){
                    lista += "<option value='"+data[i].codice+"'>"+data[i].valore+"</option>";
                }
            }

            jform_modpagamento.empty();
            jform_modpagamento.append(lista);

        },
        fail: function() {

            console.error("Errore");

        }
    });

}

function caricaRigheOrdine(ordine) {

    var filtro_dati_ordine = {
        record: ordine
    };
    
    jQuery.ajax({
        url: 'modules/SproCore/CustomViews/PopupGenerazioneOdfDaOrdine/ListaProdotti.php',
        dataType: 'json',
        async: true,
        data: filtro_dati_ordine,
        beforeSend: function() {

            jcaricamento.show();

        },
        success: function(data) {

            //console.table(data);

            var lista_prodotti = '';
            for (var i = 0; i < data.length; i++) {

                array_prodotti.push(data[i].salesorderlineid);

                prodotto[data[i].salesorderlineid] = {
                    salesorderlineid: data[i].salesorderlineid,
                    prodotto: data[i].prodotto,
                    productname: data[i].productname,
                    amm_sconto_originale: parseFloat(data[i].amm_sconto_originale).toFixed(2),
                    amm_sconto_residuo: parseFloat(data[i].amm_sconto).toFixed(2),
                    amm_sconto: parseFloat(data[i].amm_sconto).toFixed(2),
                    valore_riga: parseFloat(data[i].valore_riga).toFixed(2),
                    valore_fatturato: parseFloat(data[i].valore_fatturato).toFixed(2),
                    valore_da_fat: parseFloat(data[i].valore_da_fat).toFixed(2),
                    descrizione: data[i].descrizione,
                    val_no_sconto: parseFloat(data[i].val_no_sconto).toFixed(2)
                }; /* kpro@bid250920181215 */

                lista_prodotti += "<tr id='tr_prod_" + data[i].salesorderlineid + "' style='vertical-align: middle;'>";
                lista_prodotti += "<td style='text-align: left; vertical-align: middle;'>" + data[i].productname;

                lista_prodotti += "<textarea class='form-control descrizione_riga' readonly>"+data[i].descrizione+"</textarea>";

                lista_prodotti += "</td>";
                lista_prodotti += "<td style='text-align: right; vertical-align: middle;'>" + parseFloat(data[i].val_no_sconto).toFixed(2) + "</td>";
                lista_prodotti += "<td style='text-align: right; vertical-align: middle;'>" + parseFloat(data[i].per_sconto).toFixed(2) + "</td>";
                lista_prodotti += "<td id='amm_sconto_"+data[i].salesorderlineid+"' style='text-align: right; vertical-align: middle;'>" + parseFloat(data[i].amm_sconto).toFixed(2) + "</td>";
                lista_prodotti += "<td style='text-align: right; vertical-align: middle;'>" + parseFloat(data[i].valore_riga).toFixed(2) + "</td>";
                lista_prodotti += "<td style='text-align: right; vertical-align: middle;'>" + parseFloat(data[i].valore_fatturato).toFixed(2) + "</td>";
                lista_prodotti += "<td style='text-align: center; vertical-align: middle;'>";
                if ((parseFloat(data[i].val_no_sconto).toFixed(2) < 0 && parseFloat(data[i].valore_riga).toFixed(2) - parseFloat(data[i].valore_fatturato).toFixed(2) < 0)
                || (parseFloat(data[i].val_no_sconto).toFixed(2) >= 0 && parseFloat(data[i].valore_riga).toFixed(2) - parseFloat(data[i].valore_fatturato).toFixed(2) > 0)) { /* kpro@bid250920181215 */

                    lista_prodotti += "<div class='checkbox'>";
                    lista_prodotti += "<label>";
                    lista_prodotti += "<input class='checkbox_includi' type='checkbox' id='includi_" + data[i].salesorderlineid + "' checked='checked'>";
                    lista_prodotti += "</label>";
                    lista_prodotti += "</div>";

                }
                lista_prodotti += "</td>";
                if ((parseFloat(data[i].val_no_sconto).toFixed(2) < 0 && parseFloat(data[i].valore_riga).toFixed(2) - parseFloat(data[i].valore_fatturato).toFixed(2) >= 0)
                || (parseFloat(data[i].val_no_sconto).toFixed(2) >= 0 && parseFloat(data[i].valore_riga).toFixed(2) - parseFloat(data[i].valore_fatturato).toFixed(2) <= 0)) { /* kpro@bid250920181215 */
                    lista_prodotti += "<td style='text-align: right;' id='td_qta_" + data[i].salesorderlineid + "'>0</td>";
                } else {
                    lista_prodotti += "<td style='text-align: right; vertical-align: middle;' id='td_val_" + data[i].salesorderlineid + "'>";
                    lista_prodotti += "<div class='form-group'>";
                    lista_prodotti += "<label for='val_" + data[i].salesorderlineid + "'></label>";
                    lista_prodotti += "<input type='number' class='form-control input_val' id='val_" + data[i].salesorderlineid + "' style='text-align: right;' value=" + parseFloat(data[i].valore_da_fat).toFixed(2) + ">";
                    lista_prodotti += "</div>";
                    lista_prodotti += "</td>";
                }

            }

            jbody_tabella_prodotti_servizi.empty();
            jbody_tabella_prodotti_servizi.append(lista_prodotti);

            jcaricamento.hide();

            jform_percentuale.show();
            jform_parametri_calcolo.show();

        },
        fail: function() {
            console.log("Errore caricamento dei prodotti!");
            jcaricamento.hide();
        }
    });

}

function leggiForm(funzione) {

    if (jQuery(".checkbox_includi:checked").length != 0) {

        in_salvataggio = true;

        jcaricamento.show();

        prodotti_selezionati = [];
        array_odf = [];

        var ultimo_check = jQuery(".checkbox_includi:checked:last").attr("id");
        ultimo_check = ultimo_check.substring(8, ultimo_check.length);

        jQuery(".checkbox_includi:checked").each(function() {

            var check_esaminato = jQuery(this).attr("id");
            check_esaminato = check_esaminato.substring(8, check_esaminato.length);
            prodotti_selezionati.push(check_esaminato);

            var valore_da_fatturare = 0;
            if (jQuery("#val_" + check_esaminato)) {
                valore_da_fatturare = jQuery("#val_" + check_esaminato).val();
            } else {
                valore_da_fatturare = 0;
            }

            var ammontare_sconto = 0;
            if (jQuery("#amm_sconto_" + check_esaminato)) {
                ammontare_sconto = jQuery("#amm_sconto_" + check_esaminato).text();
            } else {
                ammontare_sconto = 0;
            }

            var ultima_riga = 'false';
            if (check_esaminato == ultimo_check) {
                ultima_riga = 'true';
            } else {
                ultima_riga = 'false';
            }

            var elemento = {
                ordine: record,
                salesorderlineid: check_esaminato,
                prodotto: prodotto[check_esaminato].prodotto,
                amm_sconto: ammontare_sconto,
                valore: valore_da_fatturare,
                ultima_riga: ultima_riga
            };

            //console.table(elemento);

            jQuery.ajax({
                url: 'modules/SproCore/CustomViews/PopupGenerazioneOdfDaOrdine/CreaOdF.php',
                dataType: 'json',
                async: false,
                data: elemento,
                success: function(data) {
                    if (check_esaminato == ultimo_check) {
                        if(funzione == 'Odf'){
                            if(mode == 'iframe'){
                                parent.postMessage("kp-generato-"+record,"*");
                            }
                            else{
                                parent.location.reload();
                            }
                        }
                    }
                    if(funzione == 'Fattura' && data.length > 0){
                        
                        if(jQuery.inArray(data[0].odfid, array_odf) === -1){
                            array_odf.push(data[0].odfid);
                        }
                    }
                },
                fail: function() {
                    console.log("Errore nella creazione degli OdF!");
                }
            });

        });
        
        if(funzione == 'Fattura' && array_odf.length > 0){

            var dati = {
                ids: array_odf,
                modpagamento: jform_modpagamento.val(),
                data_fattura: jdata_fattura.val()
            };

            //console.table(dati);

            jQuery.ajax({
                url: 'modules/SproCore/CustomViews/PopupGenerazioneOdfDaOrdine/CreaFattura.php',
                dataType: 'json',
                async: false,
                data: dati,
                success: function(data) {
                    
                    parent.location.reload();
                },
                fail: function() {
                    console.log("Errore nella creazione della fattura!");
                }
            });
        }

    } else {
        alert("Nessuno prodotto/ servizio selezionato!");
    }
}

function aggiornaQuantitaDaFatturare(percentuale_da_fatturare, parametri_calcolo) {

    for (var i = 0; i < array_prodotti.length; i++) {

        if(parametri_calcolo == 'Residuo'){

            prodotto[array_prodotti[i]].valore_da_fat = (prodotto[array_prodotti[i]].valore_riga - prodotto[array_prodotti[i]].valore_fatturato) * parseFloat(percentuale_da_fatturare) / 100;

            prodotto[array_prodotti[i]].amm_sconto = (prodotto[array_prodotti[i]].amm_sconto_residuo * parseFloat(percentuale_da_fatturare)) / 100;
        }
        else{

            var valore_da_fatturare = (prodotto[array_prodotti[i]].valore_riga * parseFloat(percentuale_da_fatturare)) / 100;
            var valore_residuo = prodotto[array_prodotti[i]].valore_riga - prodotto[array_prodotti[i]].valore_fatturato;
            if(valore_da_fatturare > valore_residuo){
                valore_da_fatturare = valore_residuo;
            }

            var sconto_da_impostare = (prodotto[array_prodotti[i]].amm_sconto_originale * parseFloat(percentuale_da_fatturare)) / 100;
            if(sconto_da_impostare > prodotto[array_prodotti[i]].amm_sconto_residuo){
                sconto_da_impostare = prodotto[array_prodotti[i]].amm_sconto_residuo;
            }

            prodotto[array_prodotti[i]].valore_da_fat = valore_da_fatturare;

            prodotto[array_prodotti[i]].amm_sconto = sconto_da_impostare;
        }

        jQuery("#val_" + prodotto[array_prodotti[i]].salesorderlineid).val(parseFloat(prodotto[array_prodotti[i]].valore_da_fat).toFixed(2));

        jQuery("#amm_sconto_" + prodotto[array_prodotti[i]].salesorderlineid).text(parseFloat(prodotto[array_prodotti[i]].amm_sconto).toFixed(2));

    }

}

function check() {

    result = true;

    jQuery(".checkbox_includi:checked").each(function() {

        var check_esaminato = jQuery(this).attr("id");
        check_esaminato = check_esaminato.substring(8, check_esaminato.length);

        //console.log("Esaminato: " + check_esaminato + " Qty: " + jQuery("#qta_" + check_esaminato) + " Qty da spedire: " + prodotto[check_esaminato].quantita_da_sped);

        /* kpro@tom070320190925 */
        var valore_fatturato = prodotto[check_esaminato].valore_fatturato;
        valore_fatturato = parseFloat(valore_fatturato);
        valore_fatturato = valore_fatturato.toFixed(2);

        var valore_riga = prodotto[check_esaminato].valore_riga;
        valore_riga = parseFloat(valore_riga);
        valore_riga = valore_riga.toFixed(2);

        var valore_da_fatturare = jQuery("#val_" + check_esaminato).val();
        valore_da_fatturare = parseFloat(valore_da_fatturare);
        valore_da_fatturare = valore_da_fatturare.toFixed(2);

        var residuo = valore_riga - valore_fatturato;
        residuo = residuo.toFixed(2);
        /* kpro@tom070320190925 end */

        if(prodotto[check_esaminato].val_no_sconto >= 0){ /* kpro@bid250920181215 */
            if (valore_da_fatturare > residuo) { //kpro@tom070320190925
                jQuery("#val_" + check_esaminato).css("background-color", "red");
                result = false;
            } else if (valore_da_fatturare == 0) { //kpro@tom070320190925
                jQuery("#val_" + check_esaminato).css("background-color", "red");
                result = false;
            } else {
                jQuery("#val_" + check_esaminato).css("background-color", "white");
            }
        /* kpro@bid250920181215 */
        }
        else{
            if (valore_da_fatturare < residuo) { //kpro@tom070320190925
                jQuery("#val_" + check_esaminato).css("background-color", "red");
                result = false;
            } else if (valore_da_fatturare == 0) { //kpro@tom070320190925
                jQuery("#val_" + check_esaminato).css("background-color", "red");
                result = false;
            } else {
                jQuery("#val_" + check_esaminato).css("background-color", "white");
            }
        }
        /* kpro@bid250920181215 end */
    });

    return result;
}