<div class="related">

	<h3 class="title_details"><?php echo __('Related Suppliers Organizations Referents');?></h3>

<?php if (!empty($results)):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('N');?></th>
		<th><?php echo __('Supplier'); ?></th>
		<th><?php echo __('Description');?></th>
		<th>Frequenza</th>
		<th>Qualifica</th>
	</tr>
	<?php
		foreach ($results as $i => $result): ?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td><?php echo $result['SuppliersOrganization']['name'];?></td>
			<td><?php echo $result['Supplier']['descrizione'];?></td>
			<td><?php echo $result['SuppliersOrganization']['frequenza'];?></td>
			<td><?php echo $this->App->traslateEnum($result['SuppliersOrganizationsReferent']['type']);?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php else: 
	echo $this->element('boxMsg',array('class_msg' => 'notice', 'msg' => "L'utente non &egrave; referente"));
endif; ?>
</div>