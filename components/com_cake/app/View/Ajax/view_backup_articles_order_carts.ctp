<?php 
echo '<div class="related">';
echo '<h3 class="title_details">'.__('Related Article Carts').'</h3>';
	
if(!empty($results)) {	
?>
	<table cellpadding = "0" cellspacing = "0">
		<tr>
			<th style="height:10px;width:30px;" rowspan="2"><?php echo __('N');?></th>
			<th style="height:10px;" rowspan="2"><?php echo __('User');?></th>
			<th style="text-align:center;width:50px;height:10px;border-bottom:none;border-left:1px solid #CCCCCC;"><?php echo __('qta');?></th>
			<th style="text-align:center;width:100px;height:10px;border-bottom:none;border-right:1px solid #CCCCCC;"><?php echo __('Importo');?></th>
			<th colspan="2" style="text-align:center;width:150px;height:10px;border-bottom:none;">Quantità e importi totali</th>
			<th style="height: 10px;" rowspan="2"><?php echo __('Importo');?></th>
			<th style="height: 10px;" rowspan="2"><?php echo __('Stato');?></th>
			<th style="height: 10px;" rowspan="2"><?php echo __('Acquistato il'); ?></th>
		</tr>	
		<tr>
			<th style="text-align:center;height:10px;border-left:1px solid #CCCCCC;border-right:1px solid #CCCCCC;" colspan="2">dell'utente</th>
			<th style="text-align:center;height:10px;border-right:1px solid #CCCCCC;" colspan="2">modificati dal referente</th>
		</tr>

	<?php 
		foreach($results as $numArticlesOrder => $result) {

			echo "\r\n";
			echo '<tr>';
			echo '<td>'.($numArticlesOrder+1).'</td>';
			echo '<td>'.$result['User']['name'].'</td>';
			echo '<td style="text-align:center;">';
			if($result['BackupCart']['qta']>0)
				echo $result['BackupCart']['qta'];
			else
				echo '-';
			echo '</td>';
			
			echo "\r\n";
			echo '<td style="text-align:center;">';
			if($result['BackupCart']['qta']>0)
				echo $this->App->getArticleImporto($result['BackupArticlesOrder']['prezzo'], $result['BackupCart']['qta']);
			else
				echo '-';
			echo '</td>';
			echo '<td style="text-align:center;">';
			if($result['BackupCart']['qta_forzato']>0)
				echo $result['BackupCart']['qta_forzato'];
			else
				echo '-';
			echo '</td>';
			echo '<td style="text-align:center;">';
			if($result['BackupCart']['importo_forzato']>0)
				echo $result['BackupCart']['importo_forzato'].'&nbsp;&euro;';
			else 
				echo '-';
			echo '</td>';
			
			if($result['BackupCart']['importo_forzato']==0) {				if($result['BackupCart']['qta_forzato']>0)					$importo = number_format(($result['BackupCart']['qta_forzato'] * $result['BackupArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));				else 					$importo = number_format(($result['BackupCart']['qta'] * $result['BackupArticlesOrder']['prezzo']),2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));			}			else 				$importo = number_format($result['BackupCart']['importo_forzato'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));			
			echo '<td>'.$importo.'&nbsp;&euro;</td>';
			
			echo "\r\n";
			echo '<td ';
			echo 'title="'.$this->App->traslateArticlesOrderStato($result).'" ';			
			echo ' class="stato_'.strtolower($result['BackupArticlesOrder']['stato']).'">';
			echo '</td>';
				
			echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['BackupCart']['date']).'</td>';
						
			echo '</tr>';
		}
	?>
	</table>

<?php 
}
else 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'articolo non &egrave; stato ancora acquistato"));
?>
</div>