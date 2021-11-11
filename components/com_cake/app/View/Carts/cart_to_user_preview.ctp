<div class="table">
<table class="table table-hover">
	<tr>
		<th><?php echo __('Articolo');?></th>
		<th><?php echo __('Conf');?></th>
		<th><?php echo __('PrezzoUnita');?></th>
		<th>Qtà</th>
		<th><?php echo __('Importo');?></th>
	</tr>
	<?php
	foreach ($results as $numResult => $result): 
		if($numResult==0) $strClass = 'lastCart';
		else $strClass = '';

		echo '<tr>';
		echo '<td class="'.$strClass.'">'.substr($result['ArticlesOrder']['name'],0,25).'...</td>';
		
		echo '<td class="'.$strClass.'">'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		echo '<td class="'.$strClass.'">'.$result['ArticlesOrder']['prezzo_e'].'</td>';
		echo '<td class="'.$strClass.'">'.$result['Cart']['qta'].'</td>';
		echo '<td class="'.$strClass.'">'.$this->App->getArticleImporto($result['ArticlesOrder']['prezzo'], $result['Cart']['qta']).'</td>';
		echo '</tr>';
	endforeach;
	?>
</table>
</div>