{* crmv@42024 *}

<table width="100%" border="0" cellpadding="5" cellspacing="0" class="small" id="tax_table{$row_no}">
	<tr>
		<td id="tax_div_title{$row_no}" class="level3Bg" colspan="3" align="left" nowrap><b>{$APP.LABEL_SET_TAX_FOR}: {$data.$totalAfterDiscount|formatUserNumber}</b></td>
	</tr>

{foreach key=tax_row_no item=tax_data from=$data.taxes}

	{assign var="taxname" value=$tax_data.taxname|cat:"_percentage"|cat:$row_no}
	{assign var="tax_id_name" value="hidden_tax"|cat:$tax_row_no+1|cat:"_percentage"|cat:$row_no}
	{assign var="taxlabel" value=$tax_data.taxlabel|cat:"_percentage"|cat:$row_no}
	{assign var="popup_tax_rowname" value="popup_tax_row"|cat:$row_no|cat:"_"|cat:$tax_row_no}

	<tr>
		<!-- kpro@tom101220181102 --> 
		<td align="left" class="lineOnTop">
			{if $tax_data.kpTassaSelezionata eq '1' }
				<script type="text/javascript">
					kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');
				</script>
				<input type="radio" checked id="{$taxname}_check"  class="{$row_no}_check" name="{$row_no}_check" value="attivo" onChange="kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');calcTotal();">	
			{elseif count($data.taxes) eq 1 }
				<script type="text/javascript">
					kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');
				</script>
				<input type="radio" checked id="{$taxname}_check" class="{$row_no}_check" name="{$row_no}_check" value="attivo" onChange="kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');calcTotal();">	
			{elseif $tax_data.kpTassaSelezionata neq '0' && $tax_data.kpTassaSelezionata neq '1' && $taxname eq 'tax1' }
				<script type="text/javascript">
					kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');
				</script>
				<input type="radio" checked id="{$taxname}_check" class="{$row_no}_check" name="{$row_no}_check" value="attivo" onChange="kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');calcTotal();">	
			{else}
				<input type="radio" id="{$taxname}_check" class="{$row_no}_check" name="{$row_no}_check" value="non_attivo" onChange="kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');calcTotal();">	
			{/if}

			<script type="text/javascript">
				kpCalcDefaultTax({$row_no});
			</script>

		</td>

		<td align="center" class="lineOnTop" id="{$taxname}_nomeTassa" >{$tax_data.taxlabel}</td>

		<td align="left" class="lineOnTop">

			<input type="text" readonly class="small tax_row_{$row_no}" size="5" name="{$taxname}" id="{$taxname}" value="{$tax_data.percentage|formatUserNumber}" onChange="kpCalcCurrentTax('{$taxname}','kprow_{$row_no}',{$tax_row_no},'{$tax_data.taxlabel}');calcTotal();">&nbsp;% 	

			{if $tax_data.percentuale_default && $tax_data.percentuale_default neq '0' && $tax_data.percentuale_default neq '' }
				<input type="hidden" id="{$taxname}_percentuale" value="{$tax_data.percentuale_default|formatUserNumber}">
			{else}
				<input type="hidden" id="{$taxname}_percentuale" value="{$tax_data.percentage|formatUserNumber}">
			{/if}

			<input type="hidden" id="{$tax_id_name}" value="{$taxname}">
		</td>
		
		<td align="right" class="lineOnTop">
			<input type="text" class="small" size="6" name="{$popup_tax_rowname}" id="{$popup_tax_rowname}" value="{if $tax_data.taxtotal neq ''}{$tax_data.taxtotal|formatUserNumber}{else}{0.0|formatUserNumber}{/if}" readonly>
		</td>
		<!-- kpro@tom101220181102 end -->

	</tr>
{/foreach}

</table>

{if count($data.taxes) eq 0}
	<div align="left" class="lineOnTop" width="100%">{$MOD.LBL_NO_TAXES_ASSOCIATED}</div>
{/if}

<input type="hidden" id="hdnTaxTotal{$row_no}" name="hdnTaxTotal{$row_no}" value="{if $data.$taxTotal neq ''}{$data.$taxTotal}{else}0{/if}">

<div class="closebutton" onClick="fnHidePopDiv('tax_div{$row_no}')"></div>