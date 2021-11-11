<?php
echo $this->Html->css('popupSlider-min');
?>
<h3>Attenzione: se modifichi il dettaglio di un utente</h3>

<div class="sp-slideshow">

	<input id="button-1" type="radio" name="radio-set" class="sp-selector-1" checked="checked" />
	<label for="button-1" class="button-label-1"></label>
	
	<input id="button-2" type="radio" name="radio-set" class="sp-selector-2" />
	<label for="button-2" class="button-label-2"></label>
	
	<input id="button-3" type="radio" name="radio-set" class="sp-selector-3" />
	<label for="button-3" class="button-label-3"></label>
	
	<!-- input id="button-4" type="radio" name="radio-set" class="sp-selector-4" />
	<label for="button-4" class="button-label-4"></label -->
	
	
	<label for="button-1" class="sp-arrow sp-a1"></label>
	<label for="button-2" class="sp-arrow sp-a2"></label>
	<label for="button-3" class="sp-arrow sp-a3"></label>
	<!-- label for="button-4" class="sp-arrow sp-a4"></label -->
	
	<div class="sp-content">
		<div class="sp-parallax-bg"></div>
		<ul class="sp-slider clearfix">
			<li>


		<h2>Per quest'ordine hai attivato la gestione</h2>
		
		<table style="float:left;">
			<tr>
				<td style="width:32px;padding:0 15px;">
					<img src="/images/cake/apps/32x32/kexi.png" />
				</td>
				<td>degli acquisti <b>aggregati</b> per importo</td>
			</tr>
		</table>					

		<img style="width:425px;float:right;" class="imgPrintScreen" alt="tab della gestione dell'ordine" src="<?php echo Configure::read('App.img.cake'); ;?>/info_ordine_gestisci_acquisti_aggregati.jpg" />
		
	</li>
	
	<li>
		Se ora, con la gestione <img src="/images/cake/apps/32x32/kexi_one.png" /> degli acquisti dell'ordine nel <b>dettaglio</b> 
		<br />farai qualche modifica sul carrello di un gasista dovrai <b>ricalcolare</b> gli importi aggregati
			<br />
			<br />
			Per effettuare il ricalcolo, così da prendere le modifiche che ora effettuerai, 
			<div class="cakeContainer">		
				<ul class="menuLateraleItems">
					<li><span class="popupNum">1</span> vai su "Gestisci gli acquisti <b>aggregati</b> per importo"</li>
					<li><span class="popupNum">2</span> Scegli "Rigenero i dati sottostanti perché ho modificato gli acquisti degli utenti"</li>
				</ul>
			</div>
			<img style="float:right;" class="imgPrintScreen" alt="rigenera i dati" src="<?php echo Configure::read('App.img.cake'); ;?>/info_acquisti_aggregati_ricarica.jpg" />
			
	</li>
	<li>
			Il corretto flusso per gestire un ordine è il seguente			
			<br /><br />			
			<div class="cakeContainer">		
				<ul class="menuLateraleItems">
				<li><span class="popupNum">1</span> <span class="popupVoceMenu bgLeft actionEditDbOne"> Gestisci gli acquisti nel dettaglio</span></li>
				<li><span class="popupNum">2</span> <span class="popupVoceMenu bgLeft actionEditDbGroupByUsers">Gestisci gli acquisti aggregati per importo</span></li>
			<!--
				<li><span class="popupNum">3</span> <span class="popupVoceMenu bgLeft actionEditDbSplit">Gestisci gli acquisti dividendo le quantità</span></li>
				<li><span class="popupNum">2</span> 
					<ul style="margin: 0px;">
						<li><span class="popupVoceMenuSub bgLeft actionTrasport">Gestione del trasporto</span></li>
						<li><span class="popupVoceMenuSub bgLeft actionCostMore">Gestione del costo aggiuntivo</span></li>
						<li><span class="popupVoceMenuSub bgLeft actionCostLess">Gestione dello sconto</span></li>
					</ul>
				 </li>
			-->
				</ul>
			</div>		
		</li>
	</ul>
</div><!-- sp-content -->
</div><!-- sp-slideshow -->

<?php
if(!empty($order_id)) {
echo $this->element('boxConflictPopUp', array('order_id' => $order_id, 'cookie_name' => $cookie_name));
}
?>	