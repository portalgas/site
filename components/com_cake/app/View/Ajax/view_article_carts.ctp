<?php 
echo '<div class="related">';

/* * ctrl che lo Article.stato = Y, se no non posso avere acquisti*/
if($article_stato=='N') 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "L'articolo ha il campo Stato settato a <b>No</b> e non può essere acquistato"));
else {
	/*
	 * ctrl se c'e' almeno un acquisto
	 * */
	$numArticlesOrder=0;
	if(isset($results['Tab']))
	foreach($results['Tab'] as $numTabs => $tab)
		foreach($tab['Delivery'] as $numDelivery => $delivery) 
			if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) 
				foreach($delivery['Order'] as $numOrder => $order) 
					if(isset($order['ArticlesOrder'])) 
						foreach($order['ArticlesOrder'] as $articlesOrder) 
							$numArticlesOrder++;
	?>	
		<h3 class="title_details"><?php echo __('Related Article Carts');?></h3>
		
	<?php	
	if($numArticlesOrder>0) {	
	?>
		<div class="table-responsive"><table class="table table-hover">
			<tr>
				<th style="height:10px;width:30px;" rowspan="2"><?php echo __('N');?></th>
				<th style="height:10px;" rowspan="2"><?php echo __('User');?></th>
				<th style="height:10px;" rowspan="2"><?php echo __('Prezzo');?></th>
				<th style="text-align:center;width:50px;height:10px;border-bottom:none;border-left:1px solid #CCCCCC;"><?php echo __('qta');?></th>
				<th style="text-align:center;width:100px;height:10px;border-bottom:none;border-right:1px solid #CCCCCC;"><?php echo __('Importo');?></th>
				<th colspan="2" style="text-align:center;width:150px;height:10px;border-bottom:none;">Quantità e importi totali</th>
				<th style="height: 10px;" rowspan="2"><?php echo __('Importo');?></th>
				<th style="height: 10px;" rowspan="2"><?php echo __('stato');?></th>
				<th style="height: 10px;" rowspan="2"><?php echo __('Acquistato il'); ?></th>
			</tr>	
			<tr>
				<th style="text-align:center;height:10px;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;" colspan="2">dell'utente</th>
				<th style="text-align:center;height:10px;border-right:1px solid #CCCCCC;" colspan="2">modificati dal referente</th>
			</tr>
		<?php 
		foreach($results['Tab'] as $numTabs => $tab) {
		
			foreach($tab['Delivery'] as $numDelivery => $delivery) {
					
				if($delivery['totOrders']>0 && $delivery['totArticlesOrder']>0) {
	
					foreach($delivery['Order'] as $numOrder => $order) {
						
						$numArticlesOrder=0;
						if(isset($order['ArticlesOrder'])) {
							
							if($numArticlesOrder==0) {
								echo '<tr>';
								echo '<td colspan="10" class="trGroup">';
								if($delivery['sys']=='N')
									echo __('Delivery').' : '.$delivery['luogoData'];
								else
									echo __('Delivery').' : '.$delivery['luogo'];
								echo ' - ordine dal '.$this->App->formatDateCreatedModifier($order['Order']['data_inizio']).' al '.$this->App->formatDateCreatedModifier($order['Order']['data_fine']);
								echo '</td>';
								echo '</tr>';							
							}
										
							foreach($order['ArticlesOrder'] as $numArticlesOrder => $articlesOrder) {
	
								/*
								 * Cart.importo e' calcolato nel Model come prezzo * qta ma qui non e' sufficiente perche' devo considerare i qta_forzato o importo_forzato
								 */
								if($order['Cart'][$numArticlesOrder]['importo_forzato']>0)
									$importo = $order['Cart'][$numArticlesOrder]['importo_forzato'];
								else if($order['Cart'][$numArticlesOrder]['qta_forzato']>0) 
									$importo = number_format(($order['Cart'][$numArticlesOrder]['qta_forzato'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
								else
									$importo = number_format(($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
	
	
								echo "\r\n";
								echo '<tr>';
								echo '<td>'.($numArticlesOrder+1).'</td>';
								echo '<td>'.$order['User'][$numArticlesOrder]['name'].'</td>';
								echo '<td>'.$order['ArticlesOrder'][$numArticlesOrder]['prezzo_e'].'</td>';
								echo '<td style="text-align:center;">';
								if($order['Cart'][$numArticlesOrder]['qta']>0)
									echo $order['Cart'][$numArticlesOrder]['qta'];
								else
									echo '-';
								echo '</td>';
									
								echo "\r\n";
								echo '<td style="text-align:center;">';
								if($order['Cart'][$numArticlesOrder]['qta']>0)
									echo number_format(($order['Cart'][$numArticlesOrder]['qta'] * $order['ArticlesOrder'][$numArticlesOrder]['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
								else
									echo '-';
								echo '</td>';
								echo '<td style="text-align:center;">';
								if($order['Cart'][$numArticlesOrder]['qta_forzato']>0)
									echo $order['Cart'][$numArticlesOrder]['qta_forzato'];
								else
									echo '-';
								echo '</td>';
								echo '<td style="text-align:center;">';
								if($order['Cart'][$numArticlesOrder]['importo_forzato']>0)
									echo $order['Cart'][$numArticlesOrder]['importo_forzato'].'&nbsp;&euro;';
								else
									echo '-';
								echo '</td>';
								echo '<td>'.$importo.'&nbsp;&euro;</td>';
	
								echo "\r\n";
								echo '<td title="Stato dell\'articolo associato all\'ordine: '.$this->App->traslateEnum($order['ArticlesOrder'][$numArticlesOrder]['stato']).'" class="stato_'.strtolower($order['ArticlesOrder'][$numArticlesOrder]['stato']).'"></td>';
								echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($order['Cart'][$numArticlesOrder]['created']).'</td>';
									
								echo '</tr>';
							}
						}
					}
				}
			}
		}
		
		echo '</table></div>';
	
	}
	else // if($numArticlesOrder>0)
		echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'articolo non &egrave; stato ancora acquistato"));
		
	if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') {
	
		/*
		 *  dispensa 
		 */	
		if(empty($storeroomDeliveryResults) && empty($storeroomResults)) 
				echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'articolo non &egrave; in dispensa"));
		else {
			
			echo '<h3 class="title_details">';
			echo __('Storeroom');
			if(isset($storeroomResults[0]['Storeroom']['qta']) && !empty($storeroomResults[0]['Storeroom']['qta']))
				echo ', '.$storeroomResults[0]['Storeroom']['qta'].' confezioni in dispensa';
			echo '</h3>';
			
			if(!empty($storeroomDeliveryResults)) {
			?>
				<table cellpadding = "0" cellspacing = "0">
				<tr>
					<th><?php echo __('N');?></th>
					<th><?php echo __('User');?></th>
					<th><?php echo __('Delivery');?></th>
					<th><?php echo __('Conf');?></th>
					<th><?php echo __('PrezzoUnita');?></th>
					<th><?php echo __('Prezzo/UM');?></th>		
					<th><?php echo __('Acquistato'); ?></th>
					<th><?php echo __('Importo'); ?></th>
					<th><?php echo __('Acquistato il'); ?></th>
				</tr>
				<?php
					$i = 0;
					foreach ($storeroomDeliveryResults as $i => $result): ?>
					<tr>
						<td><?php echo ($i+1);?></td>
						<td><?php echo $result['User']['name'];?></td>
						<td><?php echo $result['Delivery']['luogo'];?> <?php echo $this->App->formatDateCreatedModifier($result['Delivery']['data']);?></td>
						<td><?php echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);?></td>
						<td><?php echo $result['Storeroom']['prezzo_e'];?></td>
						<td><?php echo $this->App->getArticlePrezzoUM($result['Storeroom']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);?></td>
						<td><?php echo $result['Storeroom']['qta'];?></td>
						<td><?php echo $this->App->getArticleImporto($result['Storeroom']['prezzo'], $result['Storeroom']['qta']);?></td>
						<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Storeroom']['created']); ?></td>
					</tr>
				<?php endforeach; 
				echo '</table>';
			} // end if(!empty($storeroomDeliveryResults))
		} // end if(empty($storeroomDeliveryResults) && empty($storeroomResults))
			
	} // end if($user->organization['Organization']['hasStoreroom']=='Y' && $user->organization['Organization']['hasStoreroomFrontEnd']=='Y') 		
}  // if($article_stato=='N') 

echo '</div>';
?>