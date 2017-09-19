<?php
/*
echo "<pre>";
print_r($results);
echo "</pre>";
*/
echo '<div class="related table">';

if(!empty($results)) {
	
	echo '<div class="table-responsive"><table class="table table-hover">';
	echo '<thead>';
	echo '<tr>';
	echo '<th>'.__('N').'</th>';
	echo '<th>'.__('Name').'</th>';
	echo '<th style="text-align:center">'.__('StoreroomArticleQtaJustBooked').'</th>';
	echo '<th>'.__('StoreroomArticleDeliveryaJustBooked').'</th>';
	echo '</tr>';
	echo '</thead>';
	echo '<tbody>';

	$tot_qta = 0;
	foreach($results as $numResult => $result) {
	
		echo '<tr>';
		echo '<td>'._($numResult+1).'</td>';
		echo '<td>'.$result['User']['name'].'</td>';
		echo '<td style="text-align:center">'.$result['Storeroom']['qta'].'</td>';
		echo '<td>';
		if(!empty($result['Delivery']['luogo']))
			echo $result['Delivery']['luogo'].' del '.$this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y").' '.$result['Delivery']['data'];
		echo '</td>';
		echo '</tr>';
		
		$tot_qta = ($tot_qta + intval($result['Storeroom']['qta']));
	}
	echo '</tbody>';
	echo '<tfooter>';
	echo '<tr>';
	echo '<td></td>';
	echo '<td>Totale</td>';
	echo '<td style="text-align:center">'.$tot_qta.'</td>';
	echo '<td></td>';
	echo '</tr>';
	echo '</tfooter>';	
	echo '</table></div>';
}
else
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Nessun gasista ha prenotato l'articolo"));
		
echo '</div>';
?>