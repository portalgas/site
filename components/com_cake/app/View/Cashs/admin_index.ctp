<div class="cashs">
	<h2 class="ico-cashs">
		<?php echo __('Cash');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Add Cash'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('Add Cash'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	if(!empty($results)) {
	?>		
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th colspan="2"><?php echo __('N');?></th>
			<th colspan="2"><?php echo $this->Paginator->sort('name');?></th>
			<th><?php echo $this->Paginator->sort('email');?></th>
			<th colspan="2"><?php echo $this->Paginator->sort('CashSaldo');?></th>
			<th><?php echo $this->Paginator->sort('nota');?></th>
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i=0;
	$tot_importo=0;
	foreach ($results as $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
		 
		echo '<tr class="view">';
		echo '	<td><a action="cashes_histories-'.$result['Cash']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
	
		echo '<td>'.$numRow.'</td>';
		
		if(isset($result['User']['id'])) {
			echo '<td>'.$this->App->drawUserAvatar($user, $result['User']['id'], $result['User']).'</td>';
			echo '<td>'.$result['User']['name'].'</td>';
			echo '<td>';
			if(!empty($result['User']['email']))	
				echo ' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$result['User']['email'].'">'.$result['User']['email'].'</a>';
			echo '</td>';
		}
		else {
			/*
			 * voce di cassa non associata all'utente
			 */
			echo '<td colspan="3">'.__('CashVoceGeneric').'</td>';
		}

		echo '<td style="width:10px;background-color:';
		if($result['Cash']['importo']=='0.00') echo '#fff';
		else
		if($result['Cash']['importo']<0) echo 'red';
		else
		if($result['Cash']['importo']>0) echo 'green';
		echo '"></td>';
		
		echo '<td>';
		echo $result['Cash']['importo_e'];
		echo '</td>';
		
		echo '<td>';
		echo $result['Cash']['nota'];
		echo '</td>';			
		echo '<td style="white-space: nowrap;">';
		echo $this->App->formatDateCreatedModifier($result['Cash']['modified']);
		echo '</td>';
		echo '<td class="actions-table-img">';
		echo $this->Html->link(null, array('action' => 'edit', $result['Cash']['id']),array('class' => 'action actionEdit','title' => __('Edit')));
		echo $this->Html->link(null, array('action' => 'delete', $result['Cash']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
		echo '</td>';
	echo '</tr>';

	echo '<tr class="trView" id="trViewId-'.$result['Cash']['id'].'">';
	echo '	<td colspan="2"></td>'; 
	echo '	<td colspan="8" id="tdViewId-'.$result['Cash']['id'].'"></td>';
	echo '</tr>';
	
	$tot_importo += $result['Cash']['importo'];
	$i++;
	endforeach; 
	
	/*
	 * totale cassa
	 */
	echo '</tr>';
	echo '<td></td>';	
	echo '<td></td>';	
	echo '<td></td>';
	echo '<td></td>';
	echo '<td style="text-align:right;font-weight: bold;">Totale</td>';
	echo '<td style="width:10px;background-color:';
	if($tot_importo=='0.00' || $tot_importo=='0') echo '#fff';
	else
	if($tot_importo<0) echo 'red';
	else
	if($tot_importo>0) echo 'green';
	echo '"></td>';
	echo '<td>';
	echo number_format($tot_importo,2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia')).'&nbsp;&euro;';
	echo '</td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '</tr>';	
	
	echo '</table></div>';
	
	echo $this->element('legendaCash');
	
	echo '<p>';
	
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));

	 	echo '</div>';
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono voci in cassa"));
		
echo '</div>';
?>