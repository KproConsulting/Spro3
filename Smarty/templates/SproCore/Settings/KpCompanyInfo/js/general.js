/* kpro@bid19062018 */
/* kpro@tom290120190943 */

/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 */


var readonly = false;
var readonly_banca = false;

var jform_dettagli;
var jform_banca;

var jbottone_modifica;
var jbottone_salva;
var jbottone_annulla;
var jbottone_modifica_banche;
var jbottone_salva_banche;
var jbottone_annulla_banche;

jQuery(document).ready(function() {

    inizializzazione();

});

function inizializzazione() {

    jform_dettagli = jQuery('.form_dettagli');
    jform_banca = jQuery('.form_banca');

    jbottone_modifica = jQuery("#bottone_modifica");
    jbottone_salva = jQuery("#bottone_salva");
    jbottone_annulla = jQuery("#bottone_annulla");
    jbottone_modifica_banche = jQuery("#bottone_modifica_banche");
    jbottone_salva_banche = jQuery("#bottone_salva_banche");
    jbottone_annulla_banche = jQuery("#bottone_annulla_banche");

    jform_probabilita_1 = jQuery("#form_probabilita_1");

    bloccaForm();
    bloccaFormBanca();

    jbottone_modifica.click(function() {

        sbloccaForm();

    });

    jbottone_modifica_banche.click(function() {

        sbloccaFormBanca();

    });

    jbottone_annulla.click(function() {

        location.reload();

    });

    jbottone_annulla_banche.click(function() {

        location.reload();

    });

    jbottone_salva.click(function() {

        if (!readonly) {

            bloccaForm();

            SetCompanyInfo();

        }

    });

    jbottone_salva_banche.click(function() {

        if (!readonly_banca) {

            bloccaFormBanca();

            SetBancheCompany();

        }

    });

}

function bloccaForm() {

    readonly = true;
    jform_dettagli.prop("disabled", true);

    jbottone_salva.hide();
    jbottone_annulla.hide();
    jbottone_modifica.show();

}

function bloccaFormBanca() {

    readonly_banca = true;
    jform_banca.prop("disabled", true);

    jbottone_salva_banche.hide();
    jbottone_annulla_banche.hide();
    jbottone_modifica_banche.show();

}


function sbloccaFormBanca() {

    readonly_banca = false;
    jform_banca.prop("disabled", false);

    jbottone_modifica_banche.hide();
    jbottone_salva_banche.show();
    jbottone_annulla_banche.show();

}

function sbloccaForm() {

    readonly = false;
    jform_dettagli.prop("disabled", false);

    jbottone_modifica.hide();
    jbottone_salva.show();
    jbottone_annulla.show();

}

function SetCompanyInfo() {

    readonly = true;

    jform_dettagli.each(function(){

        var id_campo = jQuery(this).prop("id");
        var valore_campo = jQuery(this).val();

        var nome_colonna = id_campo.substring(5);

        var dati = {
            colonna: nome_colonna,
            valore: valore_campo
        };
        //console.log(dati);
        jQuery.ajax({
            url: 'Smarty/templates/SproCore/Settings/KpCompanyInfo/SetCompanyInfo.php',
            dataType: 'json',
            async: true,
            data: dati,
            beforeSend: function() {

            },
            success: function(data) {

                if (data.length > 0) {

                    

                } else {

                    console.error("Errore");

                }

            },
            fail: function() {

                console.error("Errore");

            }
        });

    });

    bloccaForm();

}

function SetBancheCompany(){

    readonly_banca = true;

    var lista_banche = [];

    jQuery(".tr_banca").each(function(){

        var temp_id = this.id;
        temp_id = temp_id.substring(6);

        var temp = {
            id: temp_id,
            banca: (jQuery("#nome_banca_" + temp_id).html()).trim(),
            nome_istituto: (jQuery("#form_nome_istituto_" + temp_id).val()).trim(),
            iban: (jQuery("#form_iban_" + temp_id).val()).trim(),
            abi: (jQuery("#form_abi_" + temp_id).val()).trim(),
            cab: (jQuery("#form_cab_" + temp_id).val()).trim(),
            bic: (jQuery("#form_bic_" + temp_id).val()).trim(),
        };

        lista_banche.push( temp );
        
    });

    //console.table( lista_banche );

    var lista_banche_json = JSON.stringify(lista_banche);

    jQuery.ajax({
        url: 'Smarty/templates/SproCore/Settings/KpCompanyInfo/SetBancheCompany.php',
        dataType: 'json',
        async: true,
        method: 'POST',
        data: jQuery.param({ 'dati': lista_banche_json }),
        beforeSend: function() {
    
    
        },
        success: function(data) {
            
            if (data.length > 0) {

                    

            } else {

                console.error("Errore");

            }

            readonly_banca = false;
    
        },
        fail: function() {
            
            readonly_banca = false;
            console.error("Errore");
    
        }
    });

}