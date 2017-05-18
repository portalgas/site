<?php
echo '<div class="articles">';
echo '<h2 class="ico-articles">';
echo __('Articles');
echo '<div class="actions-img">';
echo '<ul>';

echo '<li>'.$this->Html->link(__('Articles Quick'), array('action' => 'index_quick'),array('class' => 'action actionQuick','title' => __('Articles Quick'))).'</li>';

echo '<li>';
echo $this->Html->link(__('New Article'), array('action' => 'add'),array('class' => 'action actionAdd','title' => __('New Article')));
echo '</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';
	
	if(!empty($results)) { 

		echo $this->Form->create('ProdGasArticle',array('id' => 'formGas'));

		echo '<table cellpadding="0" cellspacing="0">';	
		echo '<tr>';	
			echo '<th>'.__('N').'</th>';	
			echo '<th>'.$this->Paginator->sort('codice').'</th>';
			?>
			<th colspan="2"><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('confezione');?></th>
			<th><?php echo $this->Paginator->sort('PrezzoUnita');?></th>
			<th><?php echo $this->Paginator->sort('Prezzo/UM');?></th>
			<th><?php echo $this->Paginator->sort('bio',__('Bio'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);

		echo '<tr class="view">';
		echo '<td>'.$numRow.'</td>';
		
		echo '<td>'.$result['ProdGasArticle']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['ProdGasArticle']['name'].'&nbsp;';
		echo $this->App->drawArticleNota($i, strip_tags($result['ProdGasArticle']['nota']));
		echo '</td>';
		echo '<td>';
		echo $this->App->getArticleConf($result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um']); 
		echo '</td>';
		echo '<td>';
		echo $result['ProdGasArticle']['prezzo_e'];
		echo '</td>';
		echo '<td>';
		echo $this->App->getArticlePrezzoUM($result['ProdGasArticle']['prezzo'], $result['ProdGasArticle']['qta'], $result['ProdGasArticle']['um'], $result['ProdGasArticle']['um_riferimento']);
		echo '</td>';
		
		/*		 * qui calcolo runtime se e' bio, se no prendo il campo article.bio		*/		
		echo '<td>';
		if($result['ProdGasArticle']['bio']=='Y') 
			echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
		echo '</td>';
		
		echo '<td class="actions-table-img-3">';
			
			/*
			 *  ad admin_edit passo i parametri della ricerca, ordinamento e paginazione
			 * 	cosi' quando ritorno ad admin_index mantengo i filtri
			 */
			echo $this->Html->link(null, array('action' => 'edit', $result['ProdGasArticle']['id'],  
													'sort:'.$sort,'direction:'.$direction,'page:'.$page)
													,array('class' => 'action actionEdit','title' => __('Edit'))); 
			echo $this->Html->link(null, array('action' => 'copy', $result['ProdGasArticle']['id'],
													'sort:'.$sort,'direction:'.$direction,'page:'.$page)
													,array('class' => 'action actionCopy','title' => __('Copy')));
			echo $this->Html->link(null, array('action' => 'delete', $result['ProdGasArticle']['id'],
													'sort:'.$sort,'direction:'.$direction,'page:'.$page)
													,array('class' => 'action actionDelete','title' => __('Delete'))); 
		
		echo '</td>';
	echo '</tr>';

endforeach;
?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>

	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	
		echo '</div>';
		
		echo '</fieldset>';
		
		echo $this->Form->end();		
	}
	else {    
		if($iniCallPage)
			echo $this->element('boxMsg',array('class_msg' => 'success resultsNotFonud', 'msg' => __('msg_search_no_parameter')));
		else
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
	}
echo '</div>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {
		
	jQuery(".actionNotaDetail").each(function () {
		jQuery(this).click(function() {
			
			dataElement = jQuery(this).attr('id');
			dataElementArray = dataElement.split('-');
			var label = dataElementArray[0];
			var idElement = dataElementArray[1];
			
			jQuery('#articleNota-'+idElement).fadeIn();
			jQuery('#articleNotaContinue-'+idElement).hide();
			
		});
	});	
	
	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
	jQuery('.actionCopy').click(function() {

		if(!confirm("Sei sicuro di voler copiare l'articolo selezionato?")) {
			return false;
		}		
		return true;
	});		
});
</script>