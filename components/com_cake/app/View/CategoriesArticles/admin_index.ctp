<div class="categories">
	<h2 class="ico-categories">
		<?php echo __('Categories Articles');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('New Category Article'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Category Article'))); ?></li>
			</ul>
		</div>
	</h2>

	<?php
	if(!empty($results)) {
	?>	
		<table cellpadding="0" cellspacing="0">
		<tr>
				<th><?php echo __('Name');?></th>
				<th>Articoli associati</th>
				<th class="actions"><?php echo __('Actions');?></th>
		</tr>
		<?php
		$totArticleAssociati = 0;
		foreach ($results as $key => $value):
			$totArticleAssociati += $resultsTotArticle[$key]['totArticle'];
			?>
			<tr>
				<td><?php echo $value; ?></td>
				<td>
					<?php echo $resultsTotArticle[$key]['totArticle']; ?>
				</td>				
				<td class="actions-table-img">
					<?php echo $this->Html->link(null, array('action' => 'edit', $key),array('class' => 'action actionEdit','title' => __('Edit'))); ?>
					<?php echo $this->Html->link(null, array('action' => 'delete', $key),array('class' => 'action actionDelete','title' => __('Delete'))); ?>
				</td>
			</tr>
		<?php endforeach; 

			echo '<tr>';
			echo '<td style="text-align:right;">Totale articoli associati</td>';
			echo '<td>'.$totArticleAssociati.' su ('.$totArticles.')</td>';
			echo '<td></td>';
			echo '</tr>';		
		echo '</table>';
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora categorie per gli articoli registrate"));
		
echo '</div>';
?>