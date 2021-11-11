<table cellpadding="0" cellspacing="0" style="margin-bottom:0px !important;">
	<tr>
		<th><?php echo __('Articolo');?></th>
		<th><?php echo __('Conf');?></th>
		<th><?php echo __('PrezzoUnita');?></th>
		<th>Qtà</th>
		<th><?php echo __('Importo');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result): 
		if($i==0) $strClass = 'lastCart';
		else $strClass = '';

		echo '<tr>';
		echo '<td class="'.$strClass.'">'.substr($result['Article']['name'],0,25).'...</td>';
		
		echo '<td class="'.$strClass.'">'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		echo '<td class="'.$strClass.'">'.$result['ProdDeliveriesArticle']['prezzo_e'].'</td>';
		echo '<td class="'.$strClass.'">'.$result['ProdCart']['qta'].'</td>';
		echo '<td class="'.$strClass.'">'.$this->App->getArticleImporto($result['ProdDeliveriesArticle']['prezzo'], $result['ProdCart']['qta']).'</td>';
		echo '</tr>';
	endforeach;
	?>
</table>