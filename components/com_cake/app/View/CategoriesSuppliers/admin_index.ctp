<div class="categories">
	<h2 class="ico-categories">
		<?php echo __('Categories Suppliers');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Category Supplier'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Category Supplier'))); ?></li>
			</ul>
		</div>
	</h2>

	<?php
	if(!empty($results)) {
	?>	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Name');?></th>
			<th>Corrispettivo su Joomla</th>
			<th>Produttori associati</th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$totSupplierAssociati = 0;
	foreach ($results as $key => $value):
		$totSupplierAssociati += $resultsTotSupplier[$key]['totSupplier'];
	?>
	<tr>
		<td><?php echo $value; ?></td>
		<td>
			<?php echo $resultsAdd[$key]['j_title']; 
				if(!empty($resultsAdd[$key]['j_id'])) echo '&nbsp;('.$resultsAdd[$key]['j_id'].')';
			?>
		</td>
		<td>
			<?php echo $resultsTotSupplier[$key]['totSupplier']; ?>
		</td>
		<td class="actions-table-img">
			<?php echo $this->Html->link(null, array('action' => 'edit', $key),array('class' => 'action actionEdit','title' => __('Edit'))); ?>
			<?php echo $this->Html->link(null, array('action' => 'delete', $key),array('class' => 'action actionDelete','title' => __('Delete'))); ?>
		</td>
	</tr>
		<?php endforeach; 
		
		echo '<tr>';
		echo '<td></td>';
		echo '<td style="text-align:right;">Totale produttori associati</td>';
		echo '<td>'.$totSupplierAssociati.' su ('.$totSuppliers.')</td>';
		echo '<td></td>';
		echo '</tr>';		
		echo '</table>';
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud', 'msg' => "Non ci sono ancora categorie per i produttori registrate"));
		
echo '</div>';
?>