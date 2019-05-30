/* kpro@tom18072017 */

/**
 * @author Tomiello Marco
 * @copyright (c) 2017, Kpro Consulting Srl
 */

var altezza_schermo;
var larghezza_schermo;
var readonly = false;

var record;

var jform;
var jbottone_modifica;
var jbottone_salva;
var jbottone_annulla;

var jbody_tabella_registro_presenze;
var jform_select_all;
var jtr_registro_presenze;
var jform_select_partecipante;

jQuery(document).ready(function() {

    record = getObj('record').value;

    inizializzazione();

    inizializzazioneExtra();

});

function inizializzazione() {

    jbottone_modifica = jQuery("#bottone_modifica");
    jbottone_salva = jQuery("#bottone_salva");
    jbottone_annulla = jQuery("#bottone_annulla");

    window.addEventListener('resize', function() {
        reSize();
    }, false);

    reSize();

    jbottone_modifica.click(function() {

        sbloccaForm();

    });

    jbottone_annulla.click(function() {

        getRegistroPresenze(record);

    });

    jbottone_salva.click(function() {

        if (!readonly) {

            bloccaForm();

            leggiForm();

        }

    });

}

function reSize() {

    larghezza_schermo = window.innerWidth;
    altezza_schermo = window.innerHeight;

}

function bloccaForm() {

    jform = jQuery('[id*="form_"]');
    readonly = true;
    jform.prop("disabled", true);

    jbottone_salva.hide();
    jbottone_annulla.hide();
    jbottone_modifica.show();

}

function sbloccaForm() {

    jform = jQuery('[id*="form_"]');
    readonly = false;
    jform.prop("disabled", false);

    jbottone_modifica.hide();
    jbottone_salva.show();
    jbottone_annulla.show();

}

function inizializzazioneExtra() {

    jbody_tabella_registro_presenze = jQuery("#body_tabella_registro_presenze");
    jform_select_all = jQuery("#form_select_all");

    jform_select_all.change(function() {

        if (jform_select_all.prop("checked")) {

            selectAll();

        } else {

            unselectAll();

        }

    });

    getRegistroPresenze(record);

}

function getRegistroPresenze(id) {

    var dati = {
        id: id
    };

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpGiorniFormazione/GetListaPartecipanti.php',
        dataType: 'json',
        async: true,
        data: dati,
        beforeSend: function() {

        },
        success: function(data) {

            //console.table(data);

            var lista_presenze_temp = "";;

            for (var i = 0; i < data.length; i++) {

                lista_presenze_temp += "<tr class='tr_registro_presenze' id='" + data[i].id + "'>";

                lista_presenze_temp += "<td style='width: 40px; text-align: center; margin: 0px; padding: 0px; vertical-align: middle;'>";
                lista_presenze_temp += "<div class='checkbox'>";
                lista_presenze_temp += "<label>";

                if (data[i].presente == "true") {
                    lista_presenze_temp += "<input type='checkbox' class='form_select_partecipante' id='form_select_" + data[i].id + "' checked>";
                } else {
                    lista_presenze_temp += "<input type='checkbox' class='form_select_partecipante' id='form_select_" + data[i].id + "'>";
                }

                lista_presenze_temp += "<span></span>";
                lista_presenze_temp += "</label>";
                lista_presenze_temp += "</div>";
                lista_presenze_temp += "</td>";

                lista_presenze_temp += "<td style='vertical-align: middle;'>";
                lista_presenze_temp += data[i].cognome + " " + data[i].nome;
                lista_presenze_temp += "</td>";

                lista_presenze_temp += "<td style='vertical-align: middle;'>";
                lista_presenze_temp += data[i].nome_azienda;
                lista_presenze_temp += "</td>";

                lista_presenze_temp += "<td style='vertical-align: middle; text-align: right;'>";
                lista_presenze_temp += "<div class='form-group'>";
                lista_presenze_temp += "<input type='number' style='text-align: right;' class='form-control' id='form_ore_eff_" + data[i].id + "' value=" + data[i].ore_partecipazione + ">";
                lista_presenze_temp += "</div>";
                lista_presenze_temp += "</td>";

                lista_presenze_temp += "</tr>";

            }

            jbody_tabella_registro_presenze.empty();
            jbody_tabella_registro_presenze.append(lista_presenze_temp);

            jform_select_partecipante = jQuery(".form_select_partecipante");

            bloccaForm();

            setStyleRighe();

            jform_select_partecipante.change(function() {

                var elemento_temp = jQuery(this).prop("id");
                elemento_temp = elemento_temp.substring(12, elemento_temp.length);

                if (jQuery("#form_select_" + elemento_temp).prop("checked")) {

                    jQuery("#" + elemento_temp).css("color", "black");
                    jQuery("#form_ore_eff_" + elemento_temp).show();

                } else {

                    jQuery("#" + elemento_temp).css("color", "silver");
                    jQuery("#form_ore_eff_" + elemento_temp).hide();

                }

            })

        },
        fail: function() {

        }
    });

}

function leggiForm() {

    jform_select_partecipante = jQuery(".form_select_partecipante");

    var array_result = [];

    jform_select_partecipante.each(function() {

        var elemento_temp = jQuery(this).prop("id");
        elemento_temp = elemento_temp.substring(12, elemento_temp.length);

        var presente_temp = "0";

        if (jQuery(this).prop("checked")) {
            presente_temp = "1";
        }

        var ore_effettuate_temp = jQuery("#form_ore_eff_" + elemento_temp).val();

        array_result.push({
            partecipazione: elemento_temp,
            presente: presente_temp,
            ore_effettuate: ore_effettuate_temp
        });

    });

    //console.table(array_result);

    var array_json = JSON.stringify(array_result);

    setValoriNelDatabase(array_json);

}

function setValoriNelDatabase(dati_json) {

    var dati = {
        id: record,
        dati: dati_json
    };

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/KpGiorniFormazione/SetListaPartecipanti.php',
        dataType: 'json',
        async: true,
        method: 'POST',
        data: dati,
        beforeSend: function() {


        },
        success: function(data) {

            getRegistroPresenze(record);

        },
        fail: function() {

            console.error("Errore nel salvataggio");
            sbloccaForm();
            location.reload();

        }
    });

}

function selectAll() {

    jQuery(".form_select_partecipante").prop("checked", true);

    setStyleRighe();

}

function unselectAll() {

    jQuery(".form_select_partecipante").prop("checked", false);

    setStyleRighe();

}

function setStyleRighe() {

    jtr_registro_presenze = jQuery(".tr_registro_presenze");

    jtr_registro_presenze.each(function() {

        var elemento_temp = jQuery(this).prop("id");

        if (jQuery("#form_select_" + elemento_temp).prop("checked")) {

            jQuery(this).css("color", "black");
            jQuery("#form_ore_eff_" + elemento_temp).show();

        } else {

            jQuery(this).css("color", "silver");
            jQuery("#form_ore_eff_" + elemento_temp).hide();

        }

    });

}