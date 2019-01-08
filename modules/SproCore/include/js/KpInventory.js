/* kpro@tom101220181102 */
/**
 * Funzioni custom Kpro per la gestione dei codici iva per riga
 */

/* kpro@tom101220181102 */
function kpCalcDefaultTax(curr_row){

	//Se nella riga non è ancora impostata una tassa inserisce come default la prima del popup
	if( jQuery("#kpTaxName" + curr_row).val() == "" ){

		var prima_tassa = "";
		var tassa_selezionata = false;
		
		jQuery('.' + curr_row + '_check').each(function() {
			
			if( prima_tassa == "" ){
				prima_tassa = jQuery(this).attr("id");
				var temp = prima_tassa.split("_");
				if( temp.length >= 2 ){
					prima_tassa = temp[0] + "_" + temp[1];
				}
			}

			if( jQuery(this).is(':checked') ){
				tassa_selezionata = true;
			}

		});

		if( !tassa_selezionata && prima_tassa != "" ){
			
			jQuery("#"+ prima_tassa + "_check").prop('checked', true);
			var taxlabel = jQuery('#' + prima_tassa + '_nomeTassa').html();
			kpCalcCurrentTax(prima_tassa, curr_row, 0, taxlabel);

		}

	}

}

function kpCalcCurrentTax(tax_name, curr_row, tax_row, taxlabel) {
	if (checkJSOverride(arguments)) return callJSOverride(arguments); //crmv@35654
	if (checkJSExtension(arguments)) if (!callJSExtension(arguments)) return false; // crmv@65067
	
	//console.log(tax_name + " - " + curr_row + " - " + tax_row);

	var product_total = parseUserNumber(jQuery("#totalAfterDiscount"+curr_row).html());

	//Pulisco tutte le righe di tassa in quanto attiverò solo la riga flaggata
	jQuery('#tax_table'+curr_row+' input[id^=popup_tax_row'+curr_row+']').val(formatUserNumber(0));
	jQuery('.tax_row_' + curr_row).val(formatUserNumber(0));

	if( jQuery('#' + tax_name + '_check').is(':checked') ){

		new_tax_percent = jQuery('#' + tax_name + '_percentuale').val();
		new_tax_percent = parseUserNumber(new_tax_percent);

		jQuery('#'+tax_name).val(formatUserNumber(new_tax_percent));
		
	}
	else{
		jQuery('#'+tax_name).val(formatUserNumber(0));
		new_tax_percent = 0;
	}

	//console.log(new_tax_percent);
	
	new_amount_lbl = jQuery("#popup_tax_row"+curr_row+'_'+tax_row);

	//calculate the new tax amount
	new_tax_amount = product_total * new_tax_percent / 100.0;
	tax_total = 0.00;

	//console.log(new_tax_amount);
		
	// assign the new tax amount in the corresponding text box
	new_amount_lbl.val(formatUserNumber(new_tax_amount));

	// recalculate total
	jQuery('#tax_table'+curr_row+' input[id^=popup_tax_row'+curr_row+']').each(function(index, item) {
		tax_total += parseUserNumber(item.value);
	});

	//console.log(tax_total)

	jQuery("#taxTotal"+curr_row).html(formatUserNumber(tax_total));

	jQuery("#kpTaxName" + curr_row).val( tax_name );
	jQuery("#kpTaxLabel" + curr_row).val( taxlabel );
	
}
/* kpro@tom101220181102 end */