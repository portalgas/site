<div class="templatesOrdersStates index">
	<h2>Possibili azioni per ogni Gruppo</h2>

	<?php 
	echo $this->Form->create('FilterTemplatesOrdersStates',array('id'=>'formGasFilter','type'=>'get'));
	echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter Templates').'</legend>';
	echo '<table>';
	
	echo '<tr>';
	echo '<td>';
	echo $this->Form->input('template',array('label' => __('Template'), 'options' => $templates,
																		'name'=>'FilterTemplatesOrdersStateTemplateId',
																		'default' => $FilterTemplateId,
																		'empty' => Configure::read('option.empty'),'escape' => false));
	echo '</td>';
	echo '<td>';
	echo $this->Form->input('group',array('label' => __('Template'), 'options' => $groups, 
																	    'name'=>'FilterTemplatesOrdersStatesOrdersActionGroupId',
																		'default' => $FilterGroupId,
																		'empty' => Configure::read('option.empty'),'escape' => false));
	echo '</td>';
	echo '<td>';
	echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
	echo '</td>';	
	echo '</tr>';
	
	echo '</table>';
	echo '</fieldset>';
	
	$templates_id_old = 0;
	$user_group_id_old = 0;
	foreach ($results as $result): 
		if($templates_id_old != $result['TemplatesOrdersState']['template_id']) {
			
			if($templates_id_old>0) {
				echo '</tbody>';
				echo '</table><br />';
			}
			echo '<h2 class="ico-organizations">Template '.$result['Template']['id'].' '.$result['Template']['name'].'</h2>';
			?>

				<div class="table-responsive"><table class="table table-hover">
				<thead>
				<tr>			
						<th colspan="3"><?php echo $this->Paginator->sort('state_code'); ?></th>
						<th><?php echo $this->Paginator->sort('group_id'); ?></th>
						<th>Possibile azione</th>
						<th><?php echo __('Flag_menu'); ?></th>
						<th><?php echo $this->Paginator->sort('sort'); ?></th>
				</tr>
				</thead>
				<tbody>
	
			<?php 
		}
		if($user_group_id_old != $result['UserGroup']['id'])
			echo '<tr><td colspan="8"><h2 class="ico-users">Gruppo '.$result['UserGroup']['title'].' ('.$result['UserGroup']['id'].')</h2></td></tr>';		
	?>
	<tr>
		<td><?php echo '<div class="action orderStato'.$result['TemplatesOrdersState']['state_code'].'" title="'.__($result['TemplatesOrdersState']['state_code'].'-intro').'"></div>'; ?></td>
		<td><?php echo h($result['TemplatesOrdersState']['state_code']); ?>&nbsp;</td>
		<td><?php echo __($result['TemplatesOrdersState']['state_code'].'-label'); ?>&nbsp;</td>
		<td><?php echo $result['UserGroup']['title']; ?></td>
		<td><?php 
			if(!empty($result['TemplatesOrdersState']['action_controller']) && !empty($result['TemplatesOrdersState']['action_action'])) {
				// echo h($result['TemplatesOrdersState']['action_controller']).'&nbsp;'.h($result['TemplatesOrdersState']['action_action']); 
				echo __($result['TemplatesOrdersState']['state_code'].'-action');
			}
			?>
		</td>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['TemplatesOrdersState']['flag_menu']);?>"></td>
		<td><?php echo h($result['TemplatesOrdersState']['sort']); ?>&nbsp;</td>
	</tr>
<?php 
	$user_group_id_old=$result['UserGroup']['id'];
	$templates_id_old=$result['TemplatesOrdersState']['template_id'];
	
	endforeach; 
	
	echo '</tbody>';
	echo '</table></div>';
	
	echo '<p>';
	
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	echo '</p>';
	echo '<div class="paging">';
	
	echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
	echo $this->Paginator->numbers(array('separator' => ''));
	echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li class="statoCurrent">Az. dei Templates/Gruppi</li>	
		<li><?php echo $this->Html->link(__('Azioni con ACL'), array('controller' => 'OrdersActions', 'action' => 'index'),array('class'=>'action actionList'));?></li>
		<li><?php echo $this->Html->link(__('Join Az. ACL e Az. Templates/Gruppi'), array('controller' => 'TemplatesOrdersStatesOrdersActions', 'action' => 'index'),array('class'=>'action actionList'));?></li>
	</ul>
</div>