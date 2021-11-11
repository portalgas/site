<?php
echo $this->Html->css('popupSlider-min');
?>
<h3>Attenzione: se modifichi un acquisto di un gasista</h3>

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
					<img src="/images/cake/apps/32x32/kexi_one.png" />
				</td>
				<td>degli acquisti dell'ordine nel <b>dettaglio</b></td>
			</tr>
			<?php 
			if($orderHasTrasport=='Y') {
			?>
				<tr>		
					<td style="width:32px;padding:0 15px;">
						<img src="/images/cake/apps/32x32/ark2.png" />
					</td>
					<td>del <b>trasporto</b></td>
				</tr>
			<?php 
			}
			if($orderHasCostMore=='Y') {
			?>
				<tr>		
					<td style="width:32px;padding:0 15px;">
						<img src="/images/cake/apps/32x32/kwallet2.png" />
					</td>
					<td>del <b>costo aggiuntivo</b></td>
				</tr>
			<?php 
			}
			if($orderHasCostLess=='Y') {
			?>
				<tr>		
					<td style="width:32px;padding:0 15px;">
						<img src="/images/cake/apps/32x32/kwallet.png" />
					</td>
					<td>dello <b>sconto</b></td>
				</tr>
			<?php 
			}
			?>														
		</table>					

		<img style="width:425px;float:right;" class="imgPrintScreen" alt="tab della gestione dell'ordine" src="<?php echo Configure::read('App.img.cake'); ;?>/info_ordine_trasporto_cost.jpg" />
		
	</li>
	
	<li>
		Se ora, con la gestione <img src="/images/cake/apps/32x32/kexi_one.png" /> degli acquisti dell'ordine nel <b>dettaglio</b> 
		<br />farai qualche modifica sul carrello di un gasista dovrai <b>ricalcolare</b>
			<?php 
			if($orderHasTrasport=='Y') 
				echo " il trasporto<br />";
			if($orderHasCostMore=='Y') 
				echo " il costo aggiuntivo<br />";
			if($orderHasCostLess=='Y') 
				echo " lo sconto<br />";
			?>
			<br />
			<br />
			Per effettuare il ricalcolo, così da prendere le modifiche che ora effettuerai, 
			<div class="cakeContainer">		
				<ul class="menuLateraleItems">
					<li><span class="popupNum">1</span> <b>cancella</b> 
						<?php 
						if($orderHasTrasport=='Y') 
							echo " l'importo del trasporto<br />";
						if($orderHasCostMore=='Y') 
							echo " l'importo del costo aggiuntivo<br />";
						if($orderHasCostLess=='Y') 
							echo " l'importo dello sconto<br />";
						?>
					</li>
					<li><span class="popupNum">2</span> inseriscilo nuovamente</li>
				</ul>
			</div>
			<img style="float:right;" class="imgPrintScreen" alt="cancella trasporto" src="<?php echo Configure::read('App.img.cake'); ;?>/info_ordine_trasporto_cancella.jpg" />
			
	</li>
	<li>
			Il corretto flusso per gestire un ordine è il seguente			
			<br /><br />			
			<div class="cakeContainer">		
				<ul class="menuLateraleItems">
				<li><span class="popupNum">1</span> <span class="popupVoceMenu bgLeft actionEditDbOne"> Gestisci gli acquisti nel dettaglio</span></li>
			<!--
				<li><span class="popupNum">2</span> <span class="popupVoceMenu bgLeft actionEditDbGroupByUsers">Gestisci gli acquisti aggregati per importo</span></li>
				<li><span class="popupNum">3</span> <span class="popupVoceMenu bgLeft actionEditDbSplit">Gestisci gli acquisti dividendo le quantità</span></li>
			-->
				<li><span class="popupNum">2</span> 
					<ul style="margin: 0px;">
						<li><span class="popupVoceMenuSub bgLeft actionTrasport">Gestione del trasporto</span></li>
						<li><span class="popupVoceMenuSub bgLeft actionCostMore">Gestione del costo aggiuntivo</span></li>
						<li><span class="popupVoceMenuSub bgLeft actionCostLess">Gestione dello sconto</span></li>
					</ul>
				 </li>
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