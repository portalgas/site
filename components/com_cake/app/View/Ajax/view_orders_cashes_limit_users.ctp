<?php
$this->App->d($results);

echo '<div class="related">';
echo '<h3 class="title_details">';
echo __('Related Orders Cashs Users');
echo '</h3>';


if (!empty($results)): ?>
	<div class="table-responsive"><table class="table table-hover">
	<tr>
		<th><?php echo __('N');?></th>
		<th><?php echo __('Delivery')?></th>
		<th colspan="2"><?php echo __('Supplier')?></th>
		<th>
			<?php echo __('DataInizio');?><br />
			<?php echo __('DataFine');?>
		</th>
		<th><?php echo __('OpenClose');?></th>
		<th><?php echo __('StatoElaborazione'); ?></th>
		<?php 
		if($user->organization['Organization']['hasVisibility']=='Y')			echo '<th>'.__('isVisibleFrontEnd').'</th>';	
		?>
	</tr>	
	<?php
		$i = 0;
		foreach ($results as $i => $result):
		?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td><?php echo $this->Time->i18nFormat($result['Delivery']['data'],"%A %e %B %Y"); ?> <?php echo $result['Delivery']['luogo']; ?></td>
			<td>
			<?php 
			// dato non estratto 
			if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
					echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['Supplier']['name'].'" /> ';
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
			<td><?php echo __($result['Order']['state_code'].'-label'); ?>
				<?php echo $this->App->drawOrdersStateDiv($result['Order']);?>			
			</td>
			<?php if($user->organization['Organization']['hasVisibility']=='Y')
					echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($result['Order']['isVisibleFrontEnd']).'"></td>';
			?>
		</tr>
	<?php endforeach; ?>
	</table></div>
<?php else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "Non ci sono ordini associati"));
endif; ?>
</div>