{**************************************************************************************
/* kpro@bid19062018 */

/**
 * @author Bidese Jacopo
 * @copyright (c) 2018, Kpro Consulting Srl
 */
 ***************************************************************************************}

<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>
<td valign="top"></td>
<td class="showPanelBg" style="padding: 5px;" valign="top" width="100%">
<form action="index.php" method="post" id="form" onsubmit="VtigerJS_DialogBox.block();">
<input type='hidden' name='module' value='Users'>
<input type='hidden' name='action' value='DefModuleView'>
<input type='hidden' name='return_action' value='ListView'>
<input type='hidden' name='return_module' value='Users'>
<input type='hidden' name='parenttab' value='Settings'>

	<div align=center>
			{include file='SetMenu.tpl'}
			{include file='Buttons_List.tpl'}
				<!-- DISPLAY -->
				<table border=0 cellspacing=0 cellpadding=5 width=100% class="settingsSelUITopLine">
				<tr>
					<td width=50 rowspan=2 valign=top><img src="{'taxConfiguration.gif'|@vtiger_imageurl:$THEME}" alt="Dettagli Società Aggiuntivi" width="48" height="48" border=0 title="Dettagli Società Aggiuntivi"></td>
					<td class=heading2 valign=bottom><b>Impostazioni Spro > Dettagli Società Aggiuntivi</b></td>
				</tr>
				<tr>
					<td valign=top class="small">Permette di inserire dati aggiuntivi rispetto alla società</td>
				</tr>
				</table>
				<br>

				<table width=100%>

					<tr>

						<td>
							<strong>Dettagli Società Aggiuntivi</strong><br>
						</td>

						<td style="text-align: right;">
							<button id="bottone_modifica" type="button" class="crmbutton small edit" title="Modifica">Modifica</button>
							<button id="bottone_salva" type="button" class="crmbutton small save" title="Salva" style="margin-right:5px; display: none;">Salva</button>
							<button id="bottone_annulla" type="button" class="crmbutton small cancel" title="Annulla" style="display: none;">Annulla</button>
						</td>

					</tr>

				</table>

				<div id="pagina_principale">

					<table width=100% class='table table-striped' >
                
            			{$tabella_dettagli_societa}

					</table>

				</div>

				<!-- kpro@tom290120190943 -->

					<table width=100%>

						<tr>

							<td>
								<strong>Dettagli Banche Società</strong><br>
							</td>

							<td style="text-align: right;">
								<button id="bottone_modifica_banche" type="button" class="crmbutton small edit" title="Modifica">Modifica</button>
								<button id="bottone_salva_banche" type="button" class="crmbutton small save" title="Salva" style="margin-right:5px; display: none;">Salva</button>
								<button id="bottone_annulla_banche" type="button" class="crmbutton small cancel" title="Annulla" style="display: none;">Annulla</button>
							</td>

						</tr>

					</table>

					<div id="pagina_principale_banche">

						<table width=100% class='table table-striped' >
					
							{$tabella_banche_societa}

						</table>

					</div>

				<!-- kpro@tom290120190943 end -->

				</td>
				</tr>
				</table>
			</td>
			</tr>
			</table>
		</td>
	</tr>
	</table>
		
	</div>
</td>
<td valign="top"></td>
</tr>
</tbody>
</form>
</table>

{literal}

<script src="Smarty/templates/SproCore/Settings/KpCompanyInfo/js/general.js"></script>

{/literal}