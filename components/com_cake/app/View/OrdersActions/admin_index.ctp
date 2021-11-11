<div class="ordersActions index">
	<h2>Anagrafica di tutte le azioni possibili con i permessi per accedergli</h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th colspan="2">Azione</th>	
			<th><?php echo $this->Paginator->sort('flag_menu'); ?></th>
			<th><?php echo $this->Paginator->sort('permission'); ?></th>
			<th><?php echo $this->Paginator->sort('permission_or'); ?></th>
			<th><?php echo $this->Paginator->sort('label'); ?></th>
			<th><?php echo $this->Paginator->sort('img'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php 
	foreach ($results as $result):

		if(!empty($result['OrdersAction']['permission']))
			$permission = json_decode($result['OrdersAction']['permission'], true);
		else
			$permission = '';
		
		if(!empty($result['OrdersAction']['permission_or']))
			$permission_or = json_decode($result['OrdersAction']['permission_or'], true);
		else
			$permission_or = '';
	?>
	<tr>
		<td><?php echo h($result['OrdersAction']['id']); ?>&nbsp;</td>
		<td><?php
			if(!empty($result['OrdersAction']['css_class']))
				echo '<div style="width:32px;height:32px" class="'.$result['OrdersAction']['css_class'].'"></div>'; 
			?>
		</td>		
		<td><?php echo h($result['OrdersAction']['controller']); ?><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo h($result['OrdersAction']['action']); ?>&nbsp;</td>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['OrdersAction']['flag_menu']);?>"></td>
		<td><?php 
			if(!empty($permission)) {
				foreach ($permission as $key => $value)
					echo $key.':'.$value.'<br />';
			}
			?>
		</td>
		<td><?php 
			if(!empty($permission_or))  {
				foreach ($permission_or as $key => $value)
					echo $key.':'.$value.'<br />';
			}
			?>
		</td>
		<td><?php
			if(!empty($result['OrdersAction']['label']))
				echo __($result['OrdersAction']['label']); 
			?>
		</td>		
		<td><?php 
			if(!empty($result['OrdersAction']['img']))
				echo '<img width="100" src="'.Configure::read('App.img.cake').'/help-online/'.$result['OrdersAction']['img'].'" />';
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
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
		<li class="statoCurrent">Azioni con ACL</li>
		<li><?php echo $this->Html->link(__('Az. dei Templates/Gruppi'), array('controller' => 'TemplatesOrdersStates', 'action' => 'index'),array('class'=>'action actionList'));?></li>
		<li><?php echo $this->Html->link(__('Join Az. ACL e Az. Templates/Gruppi'), array('controller' => 'TemplatesOrdersStatesOrdersActions', 'action' => 'index'),array('class'=>'action actionList'));?></li>
	</ul>
</div>