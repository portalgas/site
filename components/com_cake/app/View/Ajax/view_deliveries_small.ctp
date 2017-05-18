<div class="related">

	<h3 class="title_details"><?php echo __('Related Orders to Delivery');?></h3>

<?php if (!empty($results)):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('N');?></th>
		<th colspan="2"><?php echo __('Supplier')?></th>
		<th><?php echo __('Data inizio');?></th>
		<th><?php echo __('Data fine');?></th>		
		<th><?php echo __('Aperto/Chiuso');?></th>
		<th><?php echo __('stato_elaborazione'); ?></th>
		<?php 
		if($user->organization['Organization']['hasVisibility']=='Y')
			echo '<th>'.__('isVisibleFrontEnd').'</th>';	
		?>
	</tr>	
	<?php
		$i = 0;
		foreach ($results as $i => $result):		
		?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td><?php if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
					echo ' <img width="50" class="userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
			?>
			</td>
			<td><?php echo $result['SuppliersOrganization']['name']; ?></td>
			
			<td style="white-space:nowrap;">
				<?php echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y"); ?>
			</td>
			<td style="white-space:nowrap;">
				<?php echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y"); ?>
			</td>
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
		</tr>
	<?php endforeach; ?>
	</table>
<?php else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono ordini associati"));
endif; ?>
</div>