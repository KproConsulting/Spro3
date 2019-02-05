/* kpro@tom101220181102 */
/**
 * Funzioni custom Kpro per la gestione dei codici iva per riga
 */

/* kpro@tom101220181102 */
function kpCalcDefaultTax(curr_row){

	//Se nella riga non è ancora impostata una tassa inserisce come default la prima del popup
	if( jQuery("#kpTaxName" + curr_row).html() == "" ){

		var prima_tassa = "";
		var tassa_selezionata = false;
		
		jQuery('.' + curr_row + '_check').each(function() {
			
			if( prima_tassa == "" ){
				prima_tassa = this.id;
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
			jQuery("#"+ prima_tassa + "_check").val("attivo");

			var taxlabel = jQuery('#' + prima_tassa + '_nomeTassa').html();

			kpCalcCurrentTax(prima_tassa, 'kprow_' + curr_row, 0, taxlabel);

		}

	}

}

function kpCalcCurrentTax(tax_name, curr_row, tax_row, taxlabel) {

	if (checkJSOverride(arguments)) return callJSOverride(arguments); //crmv@35654
	if (checkJSExtension(arguments)) if (!callJSExtension(arguments)) return false; // crmv@65067

	//console.log("kpCalcCurrentTax row " + curr_row);

	if( curr_row.includes("_") ){
		var curr_row_array = curr_row.split("_");
		curr_row = curr_row_array[1];
	}

	var product_total = parseUserNumber(jQuery("#totalAfterDiscount"+curr_row).html());

	//Pulisco tutte le righe di tassa in quanto attiverò solo la riga flaggata
	jQuery('#tax_table'+curr_row+' input[id^=popup_tax_row'+curr_row+']').val(formatUserNumber(0));
	jQuery('.tax_row_' + curr_row).val(formatUserNumber(0));

	//console.log(tax_name);

	if( jQuery('#' + tax_name + '_check').is(':checked') ){

		jQuery('#' + tax_name + '_check').prop("checked", true);
		jQuery("#"+ tax_name + "_check").val("attivo");

		new_tax_percent = jQuery('#' + tax_name + '_percentuale').val();
		new_tax_percent = parseUserNumber(new_tax_percent);

		jQuery('#'+tax_name).val(formatUserNumber(new_tax_percent));
		
	}
	else{
		jQuery('#'+tax_name).val(formatUserNumber(0));
		new_tax_percent = 0;
	}

	jQuery('.' + curr_row + '_check').each(function() {

		if( !jQuery(this).is(':checked') ){
			jQuery(this).prop("checked", false);
			jQuery(this).val("non_attivo");
		}
		else{
			jQuery(this).prop("checked", true);
			jQuery(this).val("attivo");
		}

	});

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
	jQuery("#kpTaxLabel" + curr_row).html( taxlabel );

	kpRicalcolaTutteLeTasseTasse();
	
}

function kpRicalcolaTutteLeTasseTasse(){
	
	jQuery("input[class$='_check']").each(function() {
		//console.log(this.id);
		
		if( jQuery(this).is(':checked') || jQuery(this).val() == "attivo" ){

			jQuery(this).prop("checked", true);
			jQuery(this).val("attivo");

			var id = this.id;
			var percentuale_temp = id.replace('_check', '');
			var percentuale_default_temp = id.replace('_check', '_percentuale');

			var valore_percentuale_temp = jQuery('#' + percentuale_temp).val();
			var valore_percentuale_default_temp = jQuery('#' + percentuale_default_temp).val();

			if( valore_percentuale_temp != valore_percentuale_default_temp ){

				//console.log("kpRicalcolaTutteLeTasseTasse: " + this.id);
				jQuery('#' + percentuale_temp).val( formatUserNumber(valore_percentuale_default_temp) );
				jQuery(this).change();

			}

		}

	});

}

function kpRicalcolaCheckTasse(){
	
	jQuery("input[class$='_check']").each(function() {
		//console.log(this.id);
		
		if( jQuery(this).is(':checked') || jQuery(this).val() == "attivo" ){
			jQuery(this).prop("checked", true);
			jQuery(this).val("attivo");
			//console.log(this.id);
		}

	});

}

function kpMoveUpDown(sType,oModule,iIndex) {

	if (checkJSOverride(arguments)) return callJSOverride(arguments); //crmv@35654
	if (checkJSExtension(arguments)) if (!callJSExtension(arguments)) return; // crmv@65067

	/* crmv@16267 crmv@29686 crmv@55232 */
	var aFieldIds = Array('hidtax_row_no','productName','subproduct_ids','comment','qty','listPrice','discount_type','discount_percentage','discount_amount','popup_tax_row','lineItemType','productDescription','hdnProductcode','netPriceInput','unit_cost','hdnProductId'); // crmv@102215 - hdnProductId va spostato in fondo
	var aContentIds = Array('qtyInStock','netPrice','subprod_names');
	var aOnClickHandlerIds = Array('searchIcon');

	iIndex = eval(iIndex) + 1;
	var oTable = document.getElementById('proTab');
	iMax = oTable.rows.length;
	iSwapIndex = 1;
	if(sType == 'UP')
	{
		for(iCount=iIndex-2;iCount>=1;iCount--)
		{
			if(document.getElementById("row"+iCount))
			{
				if(document.getElementById("row"+iCount).style.display != 'none' && document.getElementById('deleted'+iCount).value == 0)
				{
					iSwapIndex = iCount+1;
					break;
				}
			}
		}
	}
	else
	{
		for(iCount=iIndex;iCount<=iMax-2;iCount++)
		{
			if(document.getElementById("row"+iCount) && document.getElementById("row"+iCount).style.display != 'none' && document.getElementById('deleted'+iCount).value == 0)
			{
				iSwapIndex = iCount;
				break;
			}
		}
		iSwapIndex += 1;
	}

	var oCurTr = oTable.rows[iIndex];
	var oSwapRow = oTable.rows[iSwapIndex];

	iMaxCols = oCurTr.cells.length;
	iIndex -= 1;
	iSwapIndex -= 1;

	iCheckIndex = 0;
	iSwapCheckIndex = 0;
	for(j=0;j<=2;j++)
	{
		if(eval('document.getElementById(\'frmEditView\').discount'+iIndex+'['+j+']'))
		{
			sFormElement = eval('document.getElementById(\'frmEditView\').discount'+iIndex+'['+j+']');
			if(sFormElement.checked)
			{
				iCheckIndex = j;
				break;
			}
		}
	}

	for(j=0;j<=2;j++)
	{
		if(eval('document.getElementById(\'frmEditView\').discount'+iSwapIndex+'['+j+']'))
		{
			sFormElement = eval('document.getElementById(\'frmEditView\').discount'+iSwapIndex+'['+j+']');
			if(sFormElement.checked)
			{
				iSwapCheckIndex = j;
				break;
			}
		}
	}
	if(eval('document.getElementById(\'frmEditView\').discount'+iIndex+'['+iSwapCheckIndex+']'))
	{
		oElement = eval('document.getElementById(\'frmEditView\').discount'+iIndex+'['+iSwapCheckIndex+']');
		oElement.checked = true;
	}
	if(eval('document.getElementById(\'frmEditView\').discount'+iSwapIndex+'['+iCheckIndex+']'))
	{
		oSwapElement = eval('document.getElementById(\'frmEditView\').discount'+iSwapIndex+'['+iCheckIndex+']');
		oSwapElement.checked = true;
	}

	iMaxElement = aFieldIds.length;
	for(iCt=0;iCt<iMaxElement;iCt++)
	{
		sId = aFieldIds[iCt] + iIndex;
		sSwapId = aFieldIds[iCt] + iSwapIndex;
		if(document.getElementById(sId) && document.getElementById(sSwapId))
		{
			sTemp = document.getElementById(sId).value;
			document.getElementById(sId).value = document.getElementById(sSwapId).value;
			document.getElementById(sSwapId).value = sTemp;
			//crmv@30721
			if (aFieldIds[iCt] == 'hdnProductId') {
				if (document.getElementById(sId).value != '') {
					disableReferenceField(document.getElementById('productName'+iIndex));
				} else {
					resetReferenceField(document.getElementById('productName'+iIndex));
				}
				initAutocompleteInventoryRow(document.getElementById('lineItemType'+iIndex).value,'hdnProductId'+iIndex,'productName'+iIndex,getObj('searchIcon'+iIndex),oModule,iIndex,'yes'); //crmv@102215
				if (document.getElementById(sSwapId).value != '') {
					disableReferenceField(document.getElementById('productName'+iSwapIndex));
				} else {
					resetReferenceField(document.getElementById('productName'+iSwapIndex));
				}
				initAutocompleteInventoryRow(document.getElementById('lineItemType'+iSwapIndex).value,'hdnProductId'+iSwapIndex,'productName'+iSwapIndex,getObj('searchIcon'+iSwapIndex),oModule,iSwapIndex,'yes'); //crmv@102215
			}
			//crmv@30721e
		}
		//oCurTr.cells[iCt].innerHTML;
	}
	
	iMaxElement = aContentIds.length;
	for(iCt=0;iCt<iMaxElement;iCt++)
	{
		sId = aContentIds[iCt] + iIndex;
		sSwapId = aContentIds[iCt] + iSwapIndex;
		if(document.getElementById(sId) && document.getElementById(sSwapId))
		{
			sTemp = document.getElementById(sId).innerHTML;
			document.getElementById(sId).innerHTML = document.getElementById(sSwapId).innerHTML;
			document.getElementById(sSwapId).innerHTML = sTemp;
		}
	}
	
	iMaxElement = aOnClickHandlerIds.length;
	for(iCt=0;iCt<iMaxElement;iCt++)
	{
		sId = aOnClickHandlerIds[iCt] + iIndex;
		sSwapId = aOnClickHandlerIds[iCt] + iSwapIndex;
		if(document.getElementById(sId) && document.getElementById(sSwapId))
		{
			sTemp = document.getElementById(sId).onclick;
			document.getElementById(sId).onclick = document.getElementById(sSwapId).onclick;
			document.getElementById(sSwapId).onclick = sTemp;

			sTemp = document.getElementById(sId).src;
			document.getElementById(sId).src = document.getElementById(sSwapId).src;
			document.getElementById(sSwapId).src = sTemp;

			sTemp = document.getElementById(sId).title;
			document.getElementById(sId).title = document.getElementById(sSwapId).title;
			document.getElementById(sSwapId).title = sTemp;
		}
	}
	
	kpMoveUpDownTaxes(iIndex,iSwapIndex); //kpro@tom101220181102

	kpMoveUpRiferimentiOrdine(iIndex,iSwapIndex); //kpro@tom101220181102

	settotalnoofrows();
	
	// this has to stay here, or the discounts won't be calculated correctly
	calcTotal(); // crmv@144058

	//loadTaxes_Ajax(iIndex);
	//loadTaxes_Ajax(iSwapIndex);
	//callTaxCalc(iIndex);
	//callTaxCalc(iSwapIndex);
	setDiscount(this,iIndex);
	setDiscount(this,iSwapIndex);

	//sId = 'tax1_percentage' + iIndex;
	sTaxRowId = 'hidtax_row_no' + iIndex;
	if(document.getElementById(sTaxRowId))
	{
		if(!(iTaxVal = document.getElementById(sTaxRowId).value))
			iTaxVal = 0;
		//calcCurrentTax(sId,iIndex,iTaxVal);
	}
	//sSwapId = 'tax1_percentage' + iSwapIndex;
	sSwapTaxRowId = 'hidtax_row_no' + iSwapIndex;
	if(document.getElementById(sSwapTaxRowId))
	{
		if(!(iSwapTaxVal = document.getElementById(sSwapTaxRowId).value))
			iSwapTaxVal = 0;
		//calcCurrentTax(sSwapId,iSwapIndex,iSwapTaxVal);
	}

	kpRicalcolaCheckTasse(); //kpro@tom101220181102

	calcTotal();
}

//crmv@55228	crmv@55232
function kpMoveUpDownTaxes(iIndex,iSwapIndex) {
	var tax_percentage = {};
	var div1 = jQuery('#tax_div'+iIndex).html();
	var div2 = jQuery('#tax_div'+iSwapIndex).html();

	var divTaxResume1 = jQuery('#kp_tax_resume_div'+iIndex).html(); //kpro@tom101220181102
	var divTaxResume2 = jQuery('#kp_tax_resume_div'+iSwapIndex).html(); //kpro@tom101220181102
	
	for(j = 0; j < 2; j++) {
		if (j == 0) {
			var div = div1;
			var oldIndex = iIndex;
			var newIndex = iSwapIndex;
			var divTaxResume = divTaxResume1; //kpro@tom101220181102
		} else {
			var div = div2;
			var oldIndex = iSwapIndex;
			var newIndex = iIndex;
			var divTaxResume = divTaxResume2; //kpro@tom101220181102
		}

		/* kpro@tom101220181102 */
		divTaxResume = divTaxResume
			.replace(new RegExp('kp_tax_resume_div'+oldIndex,'g'), 'kp_tax_resume_div'+newIndex)
			.replace(new RegExp('kpTaxName'+oldIndex,'g'), 'kpTaxName'+newIndex)
			.replace(new RegExp('kpTaxLabel'+oldIndex,'g'), 'kpTaxLabel'+newIndex)
			.replace(new RegExp(taxname+'_percentage'+oldIndex,'g'), taxname+'_percentage'+newIndex)
			.replace(new RegExp('taxTotal'+oldIndex,'g'), 'taxTotal'+newIndex);
		/* kpro@tom101220181102 end*/

		div = div
			.replace(new RegExp('tax_div'+oldIndex,'g'), 'tax_div'+newIndex)
			.replace(new RegExp('tax_table'+oldIndex,'g'), 'tax_table'+newIndex)
			.replace(new RegExp('tax_div_title'+oldIndex,'g'), 'tax_div_title'+newIndex)
			.replace(new RegExp('hdnTaxTotal'+oldIndex,'g'), 'hdnTaxTotal'+newIndex);
		var rowsTaxes = document.getElementById('tax_table'+oldIndex).rows.length - 1;
		if (rowsTaxes > 0) {
			for(i = 0; i < rowsTaxes; i++) {
				var tmp = jQuery('#hidden_tax'+(i+1)+'_percentage'+oldIndex).val();
				tmp = tmp.split('_');
				var taxname = tmp[0];
				div = div
					.replace(new RegExp('hidden_tax'+(i+1)+'_percentage'+oldIndex,'g'), 'hidden_tax'+(i+1)+'_percentage'+newIndex)
					.replace("kpCalcCurrentTax('"+taxname+"_percentage"+oldIndex+"',"+oldIndex, "kpCalcCurrentTax('"+taxname+"_percentage"+newIndex+"',"+newIndex) //kpro@tom101220181102
					.replace(new RegExp(oldIndex+'_check','g'), newIndex+'_check') //kpro@tom101220181102
					.replace(new RegExp(taxname+"_percentage"+oldIndex,'g'), taxname+"_percentage"+newIndex)
					.replace(new RegExp("kprow_"+oldIndex,'g'), "kprow_"+newIndex) //kpro@tom101220181102
					.replace(new RegExp('popup_tax_row'+oldIndex+'_'+i,'g'), 'popup_tax_row'+newIndex+'_'+i);
				tax_percentage[taxname+'_percentage'+newIndex] = jQuery('#'+taxname+'_percentage'+oldIndex).val();
			}
		}
		if (j == 0) {
			var div1 = div;
			var divTaxResume1 = divTaxResume; //kpro@tom101220181102

		} else {
			var div2 = div;
			var divTaxResume2 = divTaxResume; //kpro@tom101220181102
		}
	}

	jQuery('#kp_tax_resume_div'+iIndex).html(divTaxResume2);	//kpro@tom101220181102
	jQuery('#kp_tax_resume_div'+iSwapIndex).html(divTaxResume1); //kpro@tom101220181102
	
	jQuery('#tax_div'+iIndex).html(div2);
	jQuery('#tax_div'+iSwapIndex).html(div1);
	jQuery("#taxTotal"+iIndex).html(formatUserNumber(jQuery('#hdnTaxTotal'+iIndex).val()));
	jQuery("#taxTotal"+iSwapIndex).html(formatUserNumber(jQuery('#hdnTaxTotal'+iSwapIndex).val()));
	
	for (k in tax_percentage) {
		jQuery('#'+k).val(tax_percentage[k]);
	}
}

function kpMoveUpRiferimentiOrdine(iIndex,iSwapIndex){

	var div1 = jQuery('#kpRifOrdineCliente_row_'+iIndex).html();
	var div2 = jQuery('#kpRifOrdineCliente_row_'+iSwapIndex).html();

	for(j = 0; j < 2; j++) {
		if (j == 0) {
			var div = div1;
			var oldIndex = iIndex;
			var newIndex = iSwapIndex;
		} else {
			var div = div2;
			var oldIndex = iSwapIndex;
			var newIndex = iIndex;
		}

		div = div
			.replace(new RegExp('kpRifOrdineCliente_row_'+oldIndex,'g'), 'kpRifOrdineCliente_row_'+newIndex)
			.replace(new RegExp('kpRifOrdineCliente'+oldIndex,'g'), 'kpRifOrdineCliente'+newIndex)
			.replace(new RegExp('kpDataOrdineCliente'+oldIndex,'g'), 'kpDataOrdineCliente'+newIndex)
			.replace(new RegExp('kpCodiceCup'+oldIndex,'g'), 'kpCodiceCup'+newIndex)
			.replace(new RegExp('kpCodiceCig'+oldIndex,'g'), 'kpCodiceCig'+newIndex);

		if (j == 0) {
			var div1 = div;

		} else {
			var div2 = div;
		}

	}

	jQuery('#kpRifOrdineCliente_row_'+iIndex).html(div2);
	jQuery('#kpRifOrdineCliente_row_'+iSwapIndex).html(div1);

}

/* kpro@tom101220181102 end */