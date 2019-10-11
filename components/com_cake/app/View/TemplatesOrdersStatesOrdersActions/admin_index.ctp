<div class="templatesOrdersStatesOrdersActions index">
	<h2>Il template N ha i seguenti Order.state_code associati al Gruppo X</h2>

	<?php 
	echo $this->Form->create('FilterTemplatesOrdersStatesOrdersAction',array('id'=>'formGasFilter','type'=>'get'));
	echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter Templates').'</legend>';
	echo '<table>';

	echo '<tr>';
	echo '<td>';
	echo $this->Form->input('template',array('label' => __('Template'), 'options' => $templates, 
																	    'name'=>'FilterTemplatesOrdersStatesOrdersActionTemplateId',
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

		/*
		 * sostituisco - tanto serve solo OrdersAction.id
		 */		
		$key_state_code = str_replace('-','', $result['TemplatesOrdersStatesOrdersAction']['state_code']);
		$key = $result['TemplatesOrdersStatesOrdersAction']['template_id'].'_'.$key_state_code.'_'.$result['OrdersAction']['id'].'_'.$result['UserGroup']['id'];
		
		if($templates_id_old != $result['TemplatesOrdersStatesOrdersAction']['template_id']) {
				
			if($templates_id_old>0) {
				echo '</tbody>';
				echo '</table><br />';
			}
			echo '<h2 class="ico-organizations">Template '.$result['Template']['id'].' '.$result['Template']['name'].'</h2>';
			?>
		
				<div class="table-responsive"><table class="table table-hover">
				<thead>
				<tr>
						<th></th>
						<th colspan="3"><?php echo $this->Paginator->sort('state_code'); ?></th>
						<th><?php echo $this->Paginator->sort('group_id'); ?></th>
						<th colspan="3">Azione</th>
						<th><?php echo $this->Paginator->sort('sort'); ?></th>
				</tr>
				</thead>
				<tbody>
			
					<?php 
		}
		
		if($user_group_id_old != $result['UserGroup']['id'])
			echo '<tr><td colspan="9"><h2 class="ico-users">Gruppo '.$result['UserGroup']['title'].' ('.$result['UserGroup']['id'].')</h2></td></tr>';		
		?>
	<tr class="view">
		<td><a action="orders_actions-<?php echo $key;?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo '<div class="action orderStato'.$result['TemplatesOrdersStatesOrdersAction']['state_code'].'" title="'.__($result['TemplatesOrdersStatesOrdersAction']['state_code'].'-intro').'"></div>'; ?></td>
		<td><?php echo h($result['TemplatesOrdersStatesOrdersAction']['state_code']); ?>&nbsp;</td>
		<td><?php echo __($result['TemplatesOrdersStatesOrdersAction']['state_code'].'-label'); ?>&nbsp;</td>
		<td><?php echo $result['UserGroup']['title']; ?></td>		
		<td><?php
			if(!empty($result['OrdersAction']['css_class']))
				echo '<div style="width:32px;height:32px" class="'.$result['OrdersAction']['css_class'].'"></div>'; 
			?>
		</td>		
		<td><?php echo __($result['OrdersAction']['label']);?> (<?php echo $result['OrdersAction']['id'];?>)</td>
		<td><?php echo $result['OrdersAction']['controller'].'::'.$result['OrdersAction']['action']; ?></td>		
		<td><?php echo h($result['TemplatesOrdersStatesOrdersAction']['sort']); ?>&nbsp;</td>
	</tr>
	
	<tr class="trView" id="trViewId-<?php echo $key;?>">
		<td></td>
		<td colspan="8" id="tdViewId-<?php echo $key;?>"></td>
	</tr>
<?php 
	$user_group_id_old=$result['UserGroup']['id'];
	$templates_id_old=$result['TemplatesOrdersStatesOrdersAction']['template_id'];
	
	endforeach; ?>
	</tbody>
	</table></div>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li class="statoCurrent">Join Az. ACL e Az. Templates/Gruppi</li>	
		<li><?php echo $this->Html->link(__('Azioni con ACL'), array('controller' => 'OrdersActions', 'action' => 'index'),array('class'=>'action actionList'));?></li>
		<li><?php echo $this->Html->link(__('Az. dei Templates/Gruppi'), array('controller' => 'TemplatesOrdersStates', 'action' => 'index'),array('class'=>'action actionList'));?></li>
	</ul>
</div>