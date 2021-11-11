<div class="orders_actions">
	<h3 class="title_details"><?php echo __('Related Orders Actions');?>
	</h3>

	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
			<th>Azione</th>			
			<th><?php echo __('Flag_menu'); ?></th>
			<th><?php echo __('Permission'); ?></th>
			<th><?php echo __('Permission_or'); ?></th>
			<th><?php echo __('Label'); ?></th>
			<th></th>
			<th><?php echo __('img'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php 
		if(!empty($results['OrdersAction']['permission']))
			$permission = json_decode($results['OrdersAction']['permission'], true);
		else
			$permission = '';
		
		if(!empty($results['OrdersAction']['permission_or']))
			$permission_or = json_decode($results['OrdersAction']['permission_or'], true);
		else
			$permission_or = '';
	?>
	<tr>
		<td><?php echo h($results['OrdersAction']['controller']); ?><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo h($results['OrdersAction']['action']); ?>&nbsp;</td>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($results['OrdersAction']['flag_menu']);?>"></td>
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
			if(!empty($results['OrdersAction']['label']))
				echo __($results['OrdersAction']['label']); 
			?>
		</td>
		<td><?php
			if(!empty($results['OrdersAction']['css_class']))
				echo '<div style="width:32px;height:32px" class="'.$results['OrdersAction']['css_class'].'"></div>'; 
			?>
		</td>		
		<td><?php 
			if(!empty($results['OrdersAction']['img']))
				echo '<img width="100" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.img.cake').'/help-online/'.$results['OrdersAction']['img'].'" />';
			?>
		</td>
	</tr>
	</tbody>
	</table>
	
</div>	