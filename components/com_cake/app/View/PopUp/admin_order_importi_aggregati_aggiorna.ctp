<h3>Aggiorna gli importi aggregati</h3>
			
		<div class="sp-slideshow">
		
			<input id="button-1" type="radio" name="radio-set" class="sp-selector-1" checked="checked" />
			<label for="button-1" class="button-label-1"></label>
			
			<input id="button-2" type="radio" name="radio-set" class="sp-selector-2" />
			<label for="button-2" class="button-label-2"></label>
			
			<label for="button-1" class="sp-arrow sp-a1"></label>
			<label for="button-2" class="sp-arrow sp-a2"></label>
			
			<div class="sp-content">
				<div class="sp-parallax-bg"></div>
				<ul class="sp-slider clearfix">
					<li>
		
		
				<h2>Per quest'ordine hai attivato la gestione</h2>
				
				<table style="float:left;">
					<?php
					if($results['Order']['typeGest']=='AGGREGATE') {
					?>							
					<tr>
						<td style="width:32px;padding:0 15px;">
							<img src="/images/cake/apps/32x32/kexi.png" />
						</td>
						<td>degli acquisti dati <b>aggregati</b> per importo</td>
					</tr>
					<?php 
					}
					if($results['Order']['hasTrasport']=='Y') {
					?>
						<tr>		
							<td style="width:32px;padding:0 15px;">
								<img src="/images/cake/apps/32x32/ark2.png" />
							</td>
							<td>del <b>trasporto</b></td>
						</tr>
					<?php 
					}
					if($results['Order']['hasCostMore']=='Y') {
					?>
						<tr>		
							<td style="width:32px;padding:0 15px;">
								<img src="/images/cake/apps/32x32/kwallet2.png" />
							</td>
							<td>del <b>costo aggiuntivo</b></td>
						</tr>
					<?php 
					}
					if($results['Order']['hasCostLess']=='Y') {
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

				<img style="width:400px;float:right;" class="imgPrintScreen" alt="tab della gestione dell'ordine" src="<?php echo Configure::read('App.img.cake'); ;?>/info_ordine_trasporto_cost.jpg" />
				
			</li>
			
			<li>
				Se tra i dati sottostante trovi qualche incongruenza, potresti aver modificato qualche dato dopo che sono stati aggregati gli importi per utenti, rieffettua il calcolo dei dati aggregati per <b>importo</b>, 
					<br />
					<br />
					Per effettuare il ricalcolo, così da ricalcolare i dati aggregati, 
					<div class="cakeContainer">		
						<ul class="menuLateraleItems">
							<li><span class="popupNum">1</span> <b>cancella</b> 
								<?php 
								if($results['Order']['typeGest']=='AGGREGATE') 
									echo " i dati aggregati, ";
								if($results['Order']['hasTrasport']=='Y') 
									echo " l'importo del trasporto, ";
								if($results['Order']['hasCostMore']=='Y') 
									echo " l'importo del costo aggiuntivo, ";
								if($results['Order']['hasCostLess']=='Y') 
									echo " l'importo dello sconto";
								?>
							</li>
							<li><span class="popupNum">2</span> inseriscilo nuovamente</li>
						</ul>
					</div>
					<img style="float:right;" class="imgPrintScreen" alt="cancella trasporto" src="<?php echo Configure::read('App.img.cake'); ;?>/info_ordine_trasporto_cancella.jpg" />
					
			</li>

		</ul>
	</div><!-- sp-content -->
	
</div><!-- sp-slideshow -->			

<?php 
echo $this->Html->script('jquery/jquery.cookie');

echo $this->Form->create('PopUp',array('id'=>'formGas'));
echo $this->Form->end();
?>