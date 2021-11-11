<div class="related">

	<h3 class="title_details"><?php echo __('Related Orders to Delivery');?>
		<div class="actions-img">
			<ul>
				<li><?php 
				if(!empty($deliveryResults) && $deliveryResults['daysToEndConsegna'] > 0) // la consegna non e' chiusa
					echo $this->Html->link(__('Add Order'), array('controller' => 'Orders','action' => 'add', null, 'delivery_id='.$deliveryResults['id']), array('class' => 'action actionAdd','title' => __('Add Order'))); ?></li>
			</ul>
		</div>	
	</h3>

<?php if (!empty($results)): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('N');?></th>
		<th colspan="2"><?php echo __('Supplier')?></th>
		<th>
			<?php echo __('DataInizio');?><br />
			<?php echo __('DataFine');?>
		</th>
		<th><?php echo __('OpenClose');?></th>
		<th><?php echo __('Nota'); ?></th>
		<th><?php echo __('StatoElaborazione'); ?></th>
		<?php 
		if($user->organization['Organization']['hasVisibility']=='Y')			echo '<th>'.__('isVisibleFrontEnd').'</th>';	
		?>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>	
	<?php
		$i = 0;
		foreach ($results as $i => $result):		
		?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td>
			<?php if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
			?>
			</td>
			<td><?php echo $result['SuppliersOrganization']['name']; ?></td>
			
			<td style="white-space:nowrap;">
				<?php echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y"); ?><br />
				<?php echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y"); ?>
			</td>
			<td style="white-space:nowrap;">
				<?php 
					echo $this->App->utilsCommons->getOrderTime($result['Order']);
				?>
			</td>
			<td><?php echo $result['Order']['nota']; ?></td>
			<td><?php echo __($result['Order']['state_code'].'-label'); ?>
				<?php echo $this->App->drawOrdersStateDiv($result);?>			
			</td>
			<?php if($user->organization['Organization']['hasVisibility']=='Y')
					echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
			?>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Order']['created']); ?></td>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Order']['modified']); ?></td>
			<td><?php 
			if($result['Order']['acl']) {  // ctrl se sono referente del produttore
				if($deliveryResults['isVisibleBackOffice']=='Y') {
					if(	$result['Order']['isVisibleBackOffice']=='Y')
						echo $this->Html->link(null, array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionWorkflow','title' => __('Order home')));
					else
						echo $this->Html->link(null, array('controller' => 'Orders', 'action' => 'edit', null, 'order_id='.$result['Order']['id']), array('class' => 'action actionEdit','title' => __('Edit Order')));
				}
			}
			?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono ordini associati"));
endif; ?>
</div>