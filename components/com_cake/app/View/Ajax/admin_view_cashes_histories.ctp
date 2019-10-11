<?php
if(!empty($results)) {
?>		
<div class="table-responsive"><table class="table table-hover">
<tr>
		<th><?php echo __('N');?></th>
		<th colspan="2"><?php echo __('CashSaldo');?></th>
		<th><?php echo __('CashOperazione');?></th>
		<th><?php echo __('nota');?></th>
		<th><?php echo __('Created');?></th>
</tr>
<?php
foreach ($results as $numResult => $result):
	 
		echo '<tr class="view">';
	
		echo '<td>'.($numResult+1).'</td>';
		
		echo '<td style="width:10px;background-color:';
		if($result['CashesHistory']['importo']=='0.00') echo '#fff';
		else
		if($result['CashesHistory']['importo']<0) echo 'red';
		else
		if($result['CashesHistory']['importo']>0) echo 'green';
		echo '"></td>';
		
		echo '<td>';
		echo $result['CashesHistory']['importo_e'];
		echo '</td>';

		echo '<td>';
		if($result['CashesHistory']['operazione']>0)
			echo '+';		
		echo $result['CashesHistory']['operazione_e'];
		echo '</td>';
		
		echo '<td>';
		echo $result['CashesHistory']['nota'];
		echo '</td>';			
		echo '<td style="white-space: nowrap;">';
		echo $this->Time->i18nFormat($result['CashesHistory']['modified'],"%A, %e %B %Y");
		echo '</td>';
		echo '</tr>';
	
	
		$tot_importo += $result['CashesHistory']['importo'];
endforeach; 

echo '</table></div>';
}
else 
	echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono voci in cassa precedenti"));		
?>