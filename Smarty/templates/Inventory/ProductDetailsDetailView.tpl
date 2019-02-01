{* crmv@42024 *}
<div id="block_{$PRODBLOCKINFO.blockid}" class="detailBlock" style="{if $PANELID != $PRODBLOCKINFO.panelid}display:none{/if}"> {* crmv@104568 *}
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" class="crmTable" id="proTab">
	<tr>
		<td colspan="{$COLSPAN}" class="dvInnerHeader"><b>{$APP.LBL_ITEM_DETAILS}</b></td>
		<td class="dvInnerHeader" align="center" colspan="2"><b>{$APP.LBL_CURRENCY}:</b> {$CURRENCY_NAME} ({$CURRENCY_SYMBOL})</td>
		<td class="dvInnerHeader" align="center" colspan="2"><b>{$APP.LBL_TAX_MODE}:</b> {$APP.$TAXTYPE}</td>
	</tr>
	<tr>
		<td width=40% class="lvtCol"><font color="red">*</font> <b>{$APP.LBL_ITEM_NAME}</b></td>
		{if $MODULE neq 'PurchaseOrder'}

			<!-- kpro@tom150620181140 -->
			<td width=10% class="lvtCol" style="display: none;"><b>{$APP.LBL_QTY_IN_STOCK}</b></td>
			<!--<td width=10% class="lvtCol"><b>{$APP.LBL_QTY_IN_STOCK}</b></td>-->
			<!-- kpro@tom150620181140 end -->

		{/if}
		<td width=10% class="lvtCol"><b>{$APP.LBL_QTY}</b></td>
		<td width=10% class="lvtCol" align="right"><b>{$APP.LBL_LIST_PRICE}</b></td>
		<td width=12% nowrap class="lvtCol" align="right"><b>{$APP.LBL_TOTAL}</b></td>
		<td width=13% valign="top" class="lvtCol" align="right"><b>{$APP.LBL_NET_PRICE}</b></td>
	</tr>

	{foreach key=i item=PROD from=$PRODUCT_DETAILS}
	{assign var=PRODUCTID value="hdnProductId$i"}
	{assign var=PRODUCTID value=$PROD.$PRODUCTID}
	{assign var=entityType value="entityType$i"}
	{assign var=entityType value=$PROD.$entityType}

	{assign var=netPrice value="netPrice$i"}
	{assign var=hdnProductcode value="hdnProductcode$i"}
	{assign var=productName value="productName$i"}
	{assign var=subprod_names value="subprod_names$i"}
	{assign var=productDescription value="productDescription$i"}
	{assign var=comment value="comment$i"}
	{assign var=qtyInStock value="qtyInStock$i"}
	{assign var=qty value="qty$i"}
	{assign var=listPrice value="listPrice$i"}
	{assign var=productTotal value="productTotal$i"}
	{assign var=discountTotal value="discountTotal$i"}
	{assign var=totalAfterDiscount value="totalAfterDiscount$i"}
	{assign var=taxTotal value="taxTotal$i"}
	{assign var=discountInfoMessage value="discountInfoMessage$i"}
	{assign var=taxesInfoMessage value="taxesInfoMessage$i"}
	{assign var=margin value="margin$i"} {* crmv@44323 *}

	{assign var=kpLabelTassaRiga value="kpLabelTassaRiga$i"} {* kpro@tom101220181102 *}
	{assign var=kpRifOrdineCliente value="kpRifOrdineCliente$i"} {* kpro@tom101220181102 *}
	{assign var=kpDataOrdineCliente value="kpDataOrdineCliente$i"} {* kpro@tom101220181102 *}
	{assign var=kpCodiceCup value="kpCodiceCup$i"} {* kpro@tom101220181102 *}
	{assign var=kpCodiceCig value="kpCodiceCig$i"} {* kpro@tom101220181102 *}

	<tr valign="top">
		<td class="crmTableRow small lineOnTop">
			<font color="gray">{$PROD.$hdnProductcode}</font>
			<br><font color="black"><a href="index.php?module={$entityType}&action=DetailView&record={$PRODUCTID}">{$PROD.$productName}</a>
			{if $PROD.$subprod_names neq ''}
			<br/><span style="color:#C0C0C0;font-style:italic;">{$PROD.$subprod_names}</span>
			{/if}
			</font>&nbsp;&nbsp;
			{if $entityType eq 'Services' && $MODULE eq 'SalesOrder'}
				{assign var=modstr value='SINGLE_ServiceContracts'|getTranslatedString:'ServiceContracts'}
				<a href="index.php?module=ServiceContracts&action=EditView&service_id={$PRODUCTID}&return_module={$MODULE}&return_id={$ID}&sorder_id={$ID}&sc_related_to={$ACCOUNTID}">	{* crmv@55225 *}
					<img border="0" src="{'handshake.gif'|resourcever}" title="{$APP.LBL_ADD_ITEM} {$modstr}" style="cursor: pointer;" align="absmiddle" />&nbsp; {$modstr[0]}
				</a>
			{/if}
			{if $entityType eq 'Products' && $MODULE eq 'SalesOrder'}
				{assign var=modstr value='SINGLE_Assets'|getTranslatedString:'Assets'}
				<a href="index.php?module=Assets&action=EditView&product={$PRODUCTID}&return_module={$MODULE}&return_id={$ID}&sorderid={$ID}&account={$ACCOUNTID}">
					<img border="0" src="{'handshake.gif'|resourcever}" title="{$APP.LBL_ADD_ITEM} {$modstr}" style="cursor: pointer;" align="absmiddle" />&nbsp; {$modstr[0]}
				</a>
			{/if}

			<br><font color="gray">{$PROD.$productDescription}</font>
			<br><font color="gray">{$PROD.$comment}</font>

			{* kpro@tom150620181140 *}
			{if $MODULE eq 'Invoice'}
				<table style="width: 100%;">
					<tr>
						<td style="width: 50%;">
							<span style="color: gray; font-style:italic">Rif. Ordine Cliente: <b>{$PROD.$kpRifOrdineCliente}</b></span>
						<td>
						<td>
							<span style="color: gray; font-style:italic">Data Ordine Cliente: <b>{$PROD.$kpDataOrdineCliente}</b></span>
						<td>
					</tr>
					<tr>
						<td>
							<span style="color: gray; font-style:italic">Codice CUP: <b>{$PROD.$kpCodiceCup}</b></span>
						<td>
						<td>
							<span style="color: gray; font-style:italic">Codice CIG: <b>{$PROD.$kpCodiceCig}</b></span>
						<td>
					</tr>
				</table>
			{/if}
			{* kpro@tom150620181140 end *}

		</td>

		{if $MODULE neq 'PurchaseOrder'}

			<!-- kpro@tom150620181140 -->
			<td class="crmTableRow small lineOnTop" style="display: none;" >{$PROD.$qtyInStock|formatUserNumber}</td>
			<!--<td class="crmTableRow small lineOnTop">{$PROD.$qtyInStock|formatUserNumber}</td>-->
			<!-- kpro@tom150620181140 end -->
	
		{/if}

		<td class="crmTableRow small lineOnTop">{$PROD.$qty|formatUserNumber}</td>

		<td class="crmTableRow small lineOnTop" align="right">
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
				<tr>
					<td align="right">{$PROD.$listPrice|formatUserNumber}</td>
				</tr>
				<tr>
					<td align="right">(-)&nbsp;<b><a href="javascript:void(0);" onclick="alert('{$PROD.$discountInfoMessage}');">{$APP.LBL_DISCOUNT} : </a></b></td>
				</tr>
				<tr>
					<td align="right" nowrap>{$APP.LBL_TOTAL_AFTER_DISCOUNT} : </td>
				</tr>
				{if $TAXTYPE eq 'individual'}
				<tr>
					<td align="right" nowrap>(+)&nbsp;<b><a href="javascript:void(0);" onclick="alert('{$PROD.$taxesInfoMessage}');">{$APP.LBL_TAX} : </a></b></td>
				</tr>
				{/if}
				{if $entityType eq 'Products'}
				<tr>
					<td align="right" nowrap>{$APP.LBL_MARGIN} :</td> {* crmv@44323 *}
				</tr>
				{/if}
			</table>
		</td>

		<td class="crmTableRow small lineOnTop" align="right">
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
				{* kpro@tom101220181102 *}
				<tr><td colspan='2' align="right">{$PROD.$productTotal|formatUserNumber}</td></tr>
				<tr><td colspan='2' align="right">{$PROD.$discountTotal|formatUserNumber}</td></tr>
				<tr><td colspan='2' align="right" nowrap>{$PROD.$totalAfterDiscount|formatUserNumber}</td></tr>
				{if $TAXTYPE eq 'individual'}
				<tr>
					<td align="left" nowrap>{$PROD.$kpLabelTassaRiga}</td>
					<td align="right" nowrap>{$PROD.$taxTotal|formatUserNumber}</td>
				</tr>
				{/if}
				{if $entityType eq 'Products'}
				<tr><td colspan='2' align="right" nowrap>{if $PROD.$margin neq ''}{$PROD.$margin*100|round}%{else}0%{/if}</td></tr> {* crmv@44323 *}
				{/if}
				{* kpro@tom101220181102 end *}
			</table>
		</td>

		<td class="crmTableRow small lineOnTop" valign="bottom" align="right">{$PROD.$netPrice|formatUserNumber}</td>
	</tr>
	{/foreach}

</table>

{* ----- TOTALS ----- *}

<table width="100%" border="0" cellspacing="0" cellpadding="5" class="crmTable" id="finalProTab">
	<tr>
		<td width="88%" class="crmTableRow small" align="right"><b>{$APP.LBL_NET_TOTAL}</td>
		<td width="12%" class="crmTableRow small" align="right"><b>{$FINAL_DETAILS.hdnSubTotal|formatUserNumber}</b></td>
	</tr>
	<tr style="display: none;"> <!-- kpro@tom01022019 -->
		<td align="right" class="crmTableRow small lineOnTop">(-)&nbsp;<b><a href="javascript:void(0);" onclick="alert('{$FINAL_DETAILS.discountInfoMessage}')" >{$APP.LBL_DISCOUNT}</a></b></td>
		<td align="right" class="crmTableRow small lineOnTop">{$FINAL_DETAILS.discountTotal_final|formatUserNumber}</td>
	</tr>
	{if $TAXTYPE eq 'group'}
	<tr>
		<td align="right" class="crmTableRow small">(+)&nbsp;<b><a href="javascript:;" onclick="alert('{$FINAL_DETAILS.taxesInfoMessage}');">{$APP.LBL_TAX}</a></b></td>
		<td align="right" class="crmTableRow small">{$FINAL_DETAILS.tax_totalamount|formatUserNumber}</td>
	</tr>
	{/if}
	<tr style="display: none;"> <!-- kpro@tom01022019 -->
		<td align="right" class="crmTableRow small">(+)&nbsp;<b>{$APP.LBL_SHIPPING_AND_HANDLING_CHARGES}</b></td>
		<td align="right" class="crmTableRow small">{$FINAL_DETAILS.shipping_handling_charge|formatUserNumber}</td>
	</tr>
	<tr style="display: none;"> <!-- kpro@tom01022019 -->
		<td align="right" class="crmTableRow small">(+)&nbsp;<b><a href="javascript:;" onclick="alert('{$FINAL_DETAILS.shtaxesInfoMessage}')">{$APP.LBL_TAX_FOR_SHIPPING_AND_HANDLING}</a></b></td>
		<td align="right" class="crmTableRow small">{$FINAL_DETAILS.shtax_totalamount|formatUserNumber}</td>
	</tr>
	<tr>
		<td align="right" class="crmTableRow small">&nbsp;<b>{$APP.LBL_ADJUSTMENT}</b></td>
		<td align="right" class="crmTableRow small">{$FINAL_DETAILS.adjustment|formatUserNumber}</td>
	</tr>
	<tr>
		<td align="right" class="crmTableRow small lineOnTop"><b>{$APP.LBL_GRAND_TOTAL}</b></td>
		<td align="right" class="crmTableRow small lineOnTop">{$FINAL_DETAILS.grandTotal|formatUserNumber}</td>
	</tr>
</table>

</div> {* crmv@104568 *}
