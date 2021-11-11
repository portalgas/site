<a id="intro" name="name"></a>
<?php
/*
PROCESSED-BEFORE-DELIVERY
PROCESSED-POST-DELIVERY
INCOMING-ORDER

PROCESSED-ON-DELIVERY 
PROCESSED-TESORIERE
WAIT-PROCESSED-TESORIERE
*/

if($tot_importi_aggregati_diversi==0) {
	echo '<div class="legenda legenda-ico-info" style="float:none;">';
	echo 'Non sono presenti dati aggregati differenti alla somma degli importi dei singoli utenti';
	echo '</div>';
}
else {
	$hasTrasport = $results['Order']['hasTrasport']; /* trasporto */
	$hasCostMore = $results['Order']['hasCostMore']; /* spesa aggiuntiva */
	$hasCostLess = $results['Order']['hasCostLess'];  /* sconto */
	$typeGest =    $results['Order']['typeGest'];   /* AGGREGATE / SPLIT */
	$state_code = $results['Order']['state_code'];

	
	if($hasTrasport=='N' && $hasCostMore=='N' && $hasCostLess=='N') {
		echo '<div class="legenda legenda-ico-info" style="float:none;">';
		echo "Ricalcola importo aggregato";
		echo '</div>';
	}
	else { 
		/*
		 * ordine in mano al referente con Trasporto, etc etc, => gestione tipica dal modulo di gestione trasporto
		 */
		if($state_code=='PROCESSED-BEFORE-DELIVERY' || 
		   $state_code=='PROCESSED-POST-DELIVERY' || 
		   $state_code=='INCOMING-ORDER') {
			?>		
	<?php
		}
		else
		/*
		 * ordine in mano al tesoriere con Trasporto, etc etc, => gestione tipica => riportare al referente
		 */
		if($state_code=='WAIT-PROCESSED-TESORIERE') {
		?>
		<?php
		}
		else 
		if($state_code=='PROCESSED-TESORIERE') {
			echo '<div class="legenda legenda-ico-info" style="float:none;">';
			echo "<h2>Richiedi al tesoriere di riporta l'ordine al referente</h2>";
			echo '</div>';
		}
		else
		/*
		 * ordine in mano al cassiere con Trasporto, etc etc, => gestione tipica => riportare al referente
		 */
		if($state_code=='PROCESSED-ON-DELIVERY') {
		?>
		<?php
		}
		
	} // end if($hasTrasport=='N' && $hasCostMore=='N' && $hasCostLess=='N')
	
} // end if($tot_importi_aggregati_diversi==0)
