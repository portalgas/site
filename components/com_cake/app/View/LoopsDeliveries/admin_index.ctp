<div class="loops">
	<h2 class="ico-loops">
		<?php echo __('Loops Deliveries');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New LoopsDelivery'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New LoopsDelivery'))); ?></li>
			</ul>
		</div>
	</h2>
	
	<?php
	if(!empty($results)) {
	?>	
	<div class="table-responsive"><table class="table table-hover table-striped">	
	<thead>
	<tr>
			<th>N</th>
			<th>Il giorno</th>
			<th>Sarà creata la consegna di</th>
			<th>Con i valori</th>
			<th></th>
			<th colspan="2">Ricorrenza</th>
			<th><?php echo __('Created'); ?></th>
			<th><?php echo __('Delivery'); ?> creata</th>
			<th>Utente</th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($results as $numResult => $result) {
		
		echo '<tr class="view">';
		echo '<td><?php echo ((int)$numResult+1);?></td>';
		echo '<td style="white-space:nowrap;">';
		echo $this->Time->i18nFormat($result['LoopsDelivery']['data_master_reale'],"%A %e %B %Y"); 
		echo '</td>';
		echo '<td style="white-space:nowrap;">';
		echo $this->Time->i18nFormat($result['LoopsDelivery']['data_copy_reale'],"%A %e %B %Y"); 
		echo '</td>';
		echo '<td>';
		echo 'Luogo '.$result['LoopsDelivery']['luogo'].'<br />';
		echo 'Orario, dalle ore '.$this->App->formatOrario($result['LoopsDelivery']['orario_da']).'&nbsp;alle&nbsp;'.$this->App->formatOrario($result['LoopsDelivery']['orario_a']).'<br />'; 
		if(!empty($result['LoopsDelivery']['nota']))  echo '<div class="small">'.$result['LoopsDelivery']['nota'].'</div>';
		echo '</td>';
		echo '<td class="nota_evidenza_'.strtolower($result['LoopsDelivery']['nota_evidenza']).'">&nbsp;</td>';
		echo '<td>';
		if($result['LoopsDelivery']['type']=='WEEK') echo "Settimanale";
		else
		if($result['LoopsDelivery']['type']=='MONTH') echo "Mensile";
		echo '</td>';			
		echo '<td>';
		if($result['LoopsDelivery']['type']=='WEEK') {
			if($result['LoopsDelivery']['week_every_week']==1)
				echo ' ogni settimana';
			else
				echo ' ogni '.$result['LoopsDelivery']['week_every_week'].' settimane';
		}
		else
		if($result['LoopsDelivery']['type']=='MONTH') {					
			if($result['LoopsDelivery']['type_month']=='MONTH1') {
				echo ' il '.$result['LoopsDelivery']['month1_day'].' di';
				if($result['LoopsDelivery']['month1_every_month']==1)
					echo ' ogni mese';
				else	
					echo ' '.$result['LoopsDelivery']['month1_every_month'].' mese';
			}
			else
			if($result['LoopsDelivery']['type_month']=='MONTH2') {
				echo $this->App->traslateEnum($result['LoopsDelivery']['month2_every_type']).' '.$this->App->traslateEnum($result['LoopsDelivery']['month2_day_week']);
				if($result['LoopsDelivery']['month2_every_month']==1)
					echo ' ogni mese';
				else
					echo ' ogni '.$result['LoopsDelivery']['month2_every_month'].' mesi';
			}
		} // end if($result['LoopsDelivery']['type']=='MONTH')
	
	 if($result['LoopsDelivery']['data_master']!=$result['LoopsDelivery']['data_master_reale'])
			echo '<div class="small">La data reale per calcolare la consegna ricorsiva è '.$this->Time->i18nFormat($result['LoopsDelivery']['data_master'],"%A %e %B %Y").'</div>'; 
	 
		echo '</td>';
		echo '<td style="white-space: nowrap;">'.$this->App->formatDateCreatedModifier($result['LoopsDelivery']['created']).'</td>';
		echo '<td>';
		if(!empty($result['Delivery']))
			echo $this->Html->link($result['Delivery']['luogoData'], ['controller' => 'deliveries', 'action' => 'edit', null, 'delivery_id='.$result['Delivery']['id']]);
		echo '</td>';
		echo '<td>'.$result['User']['name'].'</td>';
		if($isRoot) {
			echo '<td class="actions-table-img-3">';
			echo $this->Html->link(null, array('action' => 'testing', $result['LoopsDelivery']['id']),array('class' => 'action actionConfig','title' => __('Testing')));
		}
		else
			echo '<td class="actions-table-img">'; 
		echo $this->Html->link(null, array('action' => 'edit', $result['LoopsDelivery']['id']),array('class' => 'action actionEdit','title' => __('Edit'))); 
		echo $this->Html->link(null, array('action' => 'delete', $result['LoopsDelivery']['id']),array('class' => 'action actionDelete','title' => __('Delete'))); 
		echo '</td>';
		echo '</tr>';
	}
		echo '</tbody>';
		echo '</table></div>';
	} 
	else  
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora consegne ricorsive"));
	
echo '</div>';
?>