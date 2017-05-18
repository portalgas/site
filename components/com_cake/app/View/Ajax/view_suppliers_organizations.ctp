<div class="related">

	<h3 class="title_details"><?php echo __('Related Orders to Supplier');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Add Order'), array('controller' => 'Orders','action' => 'add', null, 'delivery_id=0', 'order_id=0', 'supplier_organization_id='.$supplier_organization_id), array('class' => 'action actionAdd','title' => __('Add Order'))); ?></li>
			</ul>
		</div>	
	</h3>

<?php if (!empty($results)):?>	
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('N');?></th>
		<th><?php echo __('Delivery'); ?></th>
		<th><?php echo __('Data Inizio'); ?></th>
		<th><?php echo __('Data Fine'); ?></th>
		<th><?php echo __('Aperto/Chiuso');?></th>
		<th><?php echo __('stato_elaborazione'); ?></th>	
		<?php 
		if($user->organization['Organization']['hasVisibility']=='Y')
			echo '<th>'.__('isVisibleFrontEnd').'</th>';	
		?>
		<th><?php echo __('Created'); ?></th>
	</tr>
	<?php
		foreach ($results as $i => $result):
		
			if($result['Delivery']['sys']=='N')
				$label = $result['Delivery']['luogoData'];
			else
				$label = $result['Delivery']['luogo'];
		?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td><?php echo $label;?></td>
			<td><?php echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y"); ?></td>
			<td><?php echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y"); ?></td>
			<td style="white-space:nowrap;">
				<?php 
					echo $this->App->utilsCommons->getOrderTime($result['Order']);
				?>
			</td>
			<td><?php echo __($result['Order']['state_code'].'-label'); ?>
				<?php echo $this->App->drawOrdersStateDiv($result);?>			
			</td>
			<?php if($user->organization['Organization']['hasVisibility']=='Y')
					echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
			?>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Order']['created']); ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono ordini associati"));
endif; ?>
</div>