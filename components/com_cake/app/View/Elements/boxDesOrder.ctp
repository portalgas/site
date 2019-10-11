<?php
/*
echo "<pre>";
print_r($summaryDesOrderResults);
echo "</pre>";
*/


if(!empty($desOrdersResults)) {
	echo '<div class="row">';
	echo '<div class="col-md-12">';	
	echo '<div class="legenda legenda-ico-info">';
	echo '<h2 class="ico-orders">D.E.S. ordine condiviso</h2>';
	
	if(!empty($desOrdersResults['DesOrder']['nota'])) {
		echo '<p style="padding-left: 45px;background-color:#fff;" ';
		echo 'class="nota_evidenza_'.strtolower($desOrdersResults['DesOrder']['nota_evidenza']).'"';
		echo '>';

		echo '<div style="overflow-y:auto;max-height:150px">';
		echo $desOrdersResults['DesOrder']['nota'];
		echo '</div>';

		echo '</p>';
	}
	
	echo '<div class="table-responsive"><table class="table">';
	echo '<tr>';
	echo '<th colspan="2">'.__('OwnOrganization').'</th>';
	echo '<th>'.__('DesDelivery').'</th>';
	echo '<th>'.__('DataFineMax').'</th>';
	echo '<th>'.__('Orders').'</th>';
	echo '<th>'.__('StatoElaborazione').'</th>';			
	echo '</tr>';
	
	echo '<tr class="view-2">';
		
	echo '<td>';
	echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$desOrdersResults['OwnOrganization']['img1'].'" alt="'.$desOrdersResults['OwnOrganization']['name'].'" />';
	echo '</td>';
	echo '<td>'.$desOrdersResults['OwnOrganization']['name'].'</td>';
	
	echo '<td>';
	echo $desOrdersResults['DesOrder']['luogo'];
	echo '</td>';	
	echo '<td>';
	echo $this->Time->i18nFormat($desOrdersResults['DesOrder']['data_fine_max'],"%A %e %B %Y");
	echo '</td>';
	
	echo '<td>';
	echo count($desOrdersResults['DesOrdersOrganizations']);
	echo '</td>';
	
	echo '<td>';
	echo $this->App->drawDesOrdersStateDiv($desOrdersResults);
	echo '&nbsp;';
	echo __($desOrdersResults['DesOrder']['state_code'].'-label');
	echo '</td>';
	
	echo '</tr>';
	
	/*
	 *  spese trasporto / sconto / spese aggiuntive
	 */
	if($desOrdersResults['DesOrder']['hasTrasport']=='Y' || $desOrdersResults['DesOrder']['hasCostMore']=='Y' || $desOrdersResults['DesOrder']['hasCostLess']=='Y') { 
		echo '<tr class="view-2">';			
		echo '<td></td>';
		echo '<td></td>';
		if($desOrdersResults['DesOrder']['hasTrasport']=='Y') 
			echo '<td><div class="action actionTrasport"></div> L\'ordine prevede le spese di trasporto</td>';
		else
			echo '<td></td>';
			
		if($desOrdersResults['DesOrder']['hasCostMore']=='Y') 
			echo '<td colspan="2"><div class="action actionCostMore"></div> L\'ordine prevede delle spese aggiuntive</td>';
		else
			echo '<td colspan="2"></td>';
		
		if($desOrdersResults['DesOrder']['hasCostLess']=='Y') 
			echo '<td><div class="action actionCostLess"></div> L\'ordine prevede uno sconto</td>';	
		else
			echo '<td></td>';
		echo '</tr>';
	}
	echo '</table></div>';
	 
	/*
	 * summaryDesOrder
	 */
	 if(!empty($summaryDesOrderResults)) {

		echo '<div class="table-responsive"><table class="table">';
		echo '<tr>';
		echo '<th style="width:20%">Importo dell\'ordine</th>';
		echo '<th style="width:20%">Importo dovuto al D.E.S.</th>';
		echo '<th colspan="2">'.__('Delta').'</th>';
		echo '<th style="width:55%">Nota del titolare</th>';
		echo '</tr>';
		
		foreach($summaryDesOrderResults as $summaryDesOrderResult) {
		 
		 	$differenza = ($summaryDesOrderResult['SummaryDesOrder']['importo_orig'] - $summaryDesOrderResult['SummaryDesOrder']['importo']);
		 	
		 	/*
		 	echo "<pre>";
		 	print_r($summaryDesOrderResult);
		 	echo "</pre>";
		 	*/
				 	
			echo '<tr class="view-2">';			
			echo '<td>'.$summaryDesOrderResult['SummaryDesOrder']['importo_orig_e'].'</td>';
			echo '<td>'.$summaryDesOrderResult['SummaryDesOrder']['importo_e'].'</td>';
			if($differenza!=0) {
				echo '<td style="width:10px;background-color:';
				if($differenza<0) echo "red;";
				else echo "green;";
				echo '"></td>';
				
				$differenza = (-1 * $differenza);
				$differenza = number_format($differenza, 2 , Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				echo '<td>'.$differenza.'&nbsp;&euro;</td>';
			}
			else {	
				echo '<td style="width:10px;background-color:green;"></td>';
				echo '<td>0&nbsp;&euro;</td>';
			}
			echo '<td>'.$summaryDesOrderResult['SummaryDesOrder']['nota'].'</td>';
			echo '</tr>';
		}
		echo '</table></div>';
	}
	
	/* 
	 *  dati da comunicare al produttore
	 */
	 $draw_header_table = false;
	 foreach($desOrdersResults['DesOrdersOrganizations'] as $desOrdersOrganization) {
	 	
	 	if(!empty($desOrdersOrganization['DesOrdersOrganization']['luogo'])) {
			 	
			 	if(!$draw_header_table) {

					echo '<h2 style="cursor:pointer;" class="ico-mails" id="dati_produttore_header">Dati da trasmettere al produttore</h2>';
					
					echo '<div id="dati_produttore" style="display:none;">';
					echo '<div class="table-responsive"><table class="table">';
					echo '<tr>';
					echo '<th colspan="2">'.__('G.A.S.').'</th>';
					echo '<th>'.__('DesDelivery').'</th>';
					echo '<th>'.__('Riferimenti').'</th>';
					echo '<th>'.__('Nota').'</th>';
					echo '</tr>';
			 	
			 		$draw_header_table = true;
			 		
			 	} // end (!$draw_header_table)
				
					 	
				echo '<tr class="view-2">';	
				echo '<td>';
				echo '<img width="50" src="'.Configure::read('App.web.img.upload.content').'/'.$desOrdersOrganization['Organization']['img1'].'" alt="'.$desOrdersOrganization['Organization']['name'].'" />';
				echo '</td>';						
				echo '<td>'.$desOrdersOrganization['Organization']['name'].'</td>';
				echo '<td>';
				echo $desOrdersOrganization['DesOrdersOrganization']['luogo'];
				if($desOrdersOrganization['DesOrdersOrganization']['data']!=Configure::read('DB.field.date.empty'))
					echo '<br />'.$this->Time->i18nFormat($desOrdersOrganization['DesOrdersOrganization']['data'],"%A, %e %B %Y");
				if($desOrdersOrganization['DesOrdersOrganization']['orario']!=Configure::read('DB.field.date.empty'))
					echo '<br />'.$this->App->formatOrario($desOrdersOrganization['DesOrdersOrganization']['orario']);
				echo '</td>';
					
				echo '<td>';
				if(!empty($desOrdersOrganization['DesOrdersOrganization']['contatto_nominativo']))
					echo '<br />'.$desOrdersOrganization['DesOrdersOrganization']['contatto_nominativo'];
				if(!empty($desOrdersOrganization['DesOrdersOrganization']['contatto_telefono']))
					echo '<br />'.$desOrdersOrganization['DesOrdersOrganization']['contatto_telefono'];
				if(!empty($desOrdersOrganization['DesOrdersOrganization']['contatto_mail']))
					echo '<br /><a href="mailto:'.$desOrdersOrganization['DesOrdersOrganization']['contatto_mail'].'">'.$desOrdersOrganization['DesOrdersOrganization']['contatto_mail'].'</a>';
				echo '</td>';									
				echo '<td>'.$desOrdersOrganization['DesOrdersOrganization']['nota'].'</td>';
				echo '</tr>';	 	
	 	} // if(!empty($desOrdersOrganization['DesOrdersOrganization']['luogo'])) 
	 }
	 
	 if($draw_header_table)
	 	echo '</table></div></div>';	

	echo '</div>';
	echo '</div>'; // col-md-12
	echo '</div>'; // row
}
?>
<script>
$(document).ready(function() {
	$('#dati_produttore_header').click(function() {
		$("#dati_produttore").toggle('slow');
	});
});
</script>