<h3>Riporta l'ordine al referente</h3>
			
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
	
			<h2>L'ordine &egrave; in carico al <b>cassiere</b> per effettuare i pagamenti</h2>
			<p>Se desideri effettuare modifiche, vai sulla HOME dell'ordine e clicca su </p>

			<table style="float:left;">												
				<tbody><tr>
					<td style="width:32px;padding:0 15px;">
						<img src="/images/cake/actions/32x32/reload.png">
					</td>
					<td><?php echo __('OrdersReturnProcessedReferentePostDelivery');?></td>
				</tr>
			</tbody></table>
			
		</li>
		
		<li>
			Dopo che l'ordine è tornato al <b>referente</b> rieffettua il calcolo dei dati aggregati per <b>importo</b>, 
				<br />
				<br />
				Per effettuare il ricalcolo, così da ricalcolare i dati aggregati, 
				<div class="cakeContainer">		
					<ul class="menuLateraleItems">
						<li><span class="popupNum">1</span> <b>cancella</b> 
							<?php 
							if($typeGest=='AGGREGATE') 
								echo " i dati aggregati, ";
							if($hasTrasport=='Y') 
								echo " l'importo del trasporto, ";
							if($hasCostMore=='Y') 
								echo " l'importo del costo aggiuntivo, ";
							if($hasCostLess=='Y') 
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