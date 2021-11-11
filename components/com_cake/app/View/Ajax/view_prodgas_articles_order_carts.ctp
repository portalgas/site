<?php 
echo '<div class="related">';
echo '<h3 class="title_details">'.__('Related Article Carts').'</h3>';
	
if(!empty($results)) {	
?>
	<div class="table-responsive"><table class="table table-hover">
		<tr>
			<th><?php echo __('N');?></th>
			<th><?php echo __('User');?></th>
			<th><?php echo __('qta');?></th>
			<th><?php echo __('Prezzo');?></th>
			<th><?php echo __('Importo');?></th>
			<th><?php echo __('Stato');?></th>
			<th><?php echo __('Acquistato il'); ?></th>
		</tr>	
	<?php 
		$tot_qta = 0;
		$tot_importo = 0;
		$tot_qta_modify = 0;
		$tot_importo_modify = 0;
		$tot_importo_new = 0;        
		foreach($results as $numArticlesOrder => $result) {
			
			$this->App->d($result, $debug);

			if($result['Cart']['qta']) {
				$tot_qta += $result['Cart']['qta'];
				$tot_importo += ($result['ArticlesOrder']['prezzo']* $result['Cart']['qta']);
			}
			if($result['Cart']['qta_forzato']>0)
				$tot_qta_modify += $result['Cart']['qta_forzato'];
			if($result['Cart']['importo_forzato']>0)
				$tot_importo_modify += $result['Cart']['importo_forzato'];
                       
			echo "\r\n";
			echo '<tr>';
			echo '<td>'.($numArticlesOrder+1).'</td>';
			echo '<td>'.$result['User']['name'].'</td>';
			echo '<td style="text-align:center;">';
			if($result['Cart']['qta']>0)
				echo $result['Cart']['qta'];
			else
				echo '-';
			echo '</td>';
			
			echo "\r\n";
			echo '<td style="text-align:center;">';
			if($result['Cart']['qta']>0)
				echo $this->App->getArticleImporto($result['ArticlesOrder']['prezzo'], $result['Cart']['qta']);
			else
				echo '-';
			echo '</td>';			
			if($result['Cart']['importo_forzato']==0) {
				if($result['Cart']['qta_forzato']>0)
                    $importo = ($result['Cart']['qta_forzato'] * $result['ArticlesOrder']['prezzo']);
				else 
                    $importo = ($result['Cart']['qta'] * $result['ArticlesOrder']['prezzo']);
			}
			else 
                $importo = $result['Cart']['importo_forzato'];
			
            $tot_importo_new += $importo; 
                         
			echo '<td>'.number_format($importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</td>';
			
			echo "\r\n";
			echo '<td ';
			echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';			
			echo ' class="stato_'.strtolower($result['ArticlesOrder']['stato']).'">';
			echo '</td>';
				
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['Cart']['date']).'</td>';
						
			echo '</tr>';
		}
                
		/*
		 * totali
		 */
		echo '<tr>';
		echo '<th></th>';
		echo '<th></th>';
		echo '<th style="text-align:center;">'.$tot_qta.'</th>';
		echo '<th style="text-align:center;">'.number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
		echo '<th>'.number_format($tot_importo_new,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;</th>';
		echo '<th></th>';
		echo '<th></th>';
		echo '</tr>';                
	echo '</table></div>';
}
else {
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'articolo non &egrave; stato ancora acquistato"));
}

echo '</div>';
?>