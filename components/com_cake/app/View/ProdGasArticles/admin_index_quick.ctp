<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Articles quick');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'index'),array('class' => 'action actionList','title' => __('List Articles'))); ?></li>
		</ul>
	</div>
	</h2>					

	<?php
	if(count($results)>0) { 
	
	echo $this->Form->create('ProdGasArticle',array('id' => 'formGas'));
	?>
	<fieldset>
		
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th colspan="2">
				<input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" />
				<img alt="Seleziona gli articoli da cancellare" src="<?php echo Configure::read('App.img.cake');?>/actions/24x24/button_cancel.png" />
			</th>			
			<th></th>
			<?php
			echo '<th>'.$this->Paginator->sort('codice').'</th>';
			?>
			<th></th>
			<th><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('confezione');?></th>
			<th><?php echo $this->Paginator->sort('Prezzo');?></th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i=-1;
	foreach ($results as $numRow => $result):
	?>
	<tr class="view">
		<td><?php echo ($numRow+1);?></td>		
		<td><?php echo '<input type="checkbox" id="'.$result['ProdGasArticle']['id'].'[article_id_selected]" name="article_id_selected" value="'.$result['ProdGasArticle']['id'].'" />';?></td>		
		<td>
			<img alt="" src="<?php echo Configure::read('App.img.cake');?>/blank32x32.png" id="submitEcomm-<?php echo $result['ProdGasArticle']['id'];?>" class="buttonCarrello submitEcomm" />
			<div id="msgEcomm-<?php echo $result['ProdGasArticle']['id'];?>" class="msgEcomm"></div>
		</td>
		<?php
		echo '<td>'.$this->Form->input('codice',array('id' => $result['ProdGasArticle']['id'].'-codice-prod_gas_articles','class' => 'activeUpdate','value' => $result['ProdGasArticle']['codice'],'label' => false, 'size' => '10','tabindex'=>($i+1))).'</td>';
		echo '<td>';
		if(!empty($result['ProdGasArticle']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$result['ProdGasArticle']['supplier_id'].DS.$result['ProdGasArticle']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.prod_gas_article').'/'.$result['ProdGasArticle']['supplier_id'].'/'.$result['ProdGasArticle']['img1'].'" />';
		}		
		echo '</td>';
		?>		
		<td><?php echo $this->Form->input('name',array('id' => $result['ProdGasArticle']['id'].'-name-prod_gas_articles','class' => 'activeUpdate','value' => $result['ProdGasArticle']['name'],'label' => false, 'size' => '50', 'style' => 'width: auto;', 'tabindex'=>($i+1))); ?></td>
		<td><?php echo $this->Form->input('qta',array('id' => $result['ProdGasArticle']['id'].'-qta-prod_gas_articles','class' => 'activeUpdate qta','value' => $result['ProdGasArticle']['qta_'],'type' => 'text','label' => false, 'size' => '2','after' => '&nbsp;'.$this->App->traslateEnum($result['ProdGasArticle']['um']), 'tabindex'=>($i+1))); ?></td>
		<td><?php echo $this->Form->input('prezzo',array('id' => $result['ProdGasArticle']['id'].'-prezzo-prod_gas_articles','class' => 'importoSubmit activeUpdate double','value' => $result['ProdGasArticle']['prezzo_'],'type' => 'text','label' => false, 'after' => '&nbsp;&euro;','size' => '10','tabindex'=>($i+1))); ?></td>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['ProdGasArticle']['stato']); ?>"></td>
		<?php 
		echo '<td class="actions-table-img">';
			echo $this->Html->link(null, array('action' => 'edit', $result['ProdGasArticle']['id']),array('class' => 'action actionEdit','title' => __('Edit')));
			echo $this->Html->link(null, array('action' => 'delete', $result['ProdGasArticle']['id']),array('class' => 'action actionDelete','title' => __('Delete')));
		echo '</td>';
	echo '</tr>';
endforeach;
	
	echo '</table>';
	echo '</fieldset>';
	
		echo $this->Form->hidden('article_id_selected',array('id' => 'article_id_selected', 'value' => ''));
		echo $this->Form->end("Cancella gli articoli selezionati");	
	}
	else {  // if(count($results)>0)
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
	}	
	?>	
	
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.qta').focusout(function() {validateNumberField(this,'quantita\'');});
	jQuery('.double').focusout(function() {validateNumberField(this,'prezzo');});

	jQuery(".activeUpdate").each(function () {
		jQuery(this).change(function() {
			/* get id da id="id-field-table"  */
			var idRow = jQuery(this).attr('id');
			numRow = idRow.substring(0,idRow.indexOf('-'));
			
			var value =  jQuery(this).val();
			
			var url = '';
			url = "/administrator/index.php?option=com_cake&controller=Ajax&action=updateGeneric&idRow="+idRow+"&format=notmpl";

			jQuery.ajax({
				type: "POST",
				url: url,
				data: "value="+value,
				success: function(response){
					 jQuery('#msgEcomm-'+numRow).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					 jQuery('#msgEcomm-'+numRow).html(textStatus);
					 jQuery('#submitEcomm-'+numRow).attr('src',app_img+'/blank32x32.png');
				}
			});
			return false;			
		});
	});
	
	jQuery('#article_id_selected_all').click(function () {
		var checked = jQuery("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=article_id_selected]').prop('checked',true);
		else
			jQuery('input[name=article_id_selected]').prop('checked',false);
	});

	jQuery('.importoSubmit').change(function() {

		var prezzo = jQuery(this).val();

		if(prezzo=='' || prezzo==undefined) {
			alert("Devi indicare il prezzo da associare all'articolo");
			jQuery(this).val("0,00");
			jQuery(this).focus();
			return false;
		}	
		
		if(prezzo=='0,00') {
			alert("Il prezzo dev'essere indicato con un valore maggior di 0");
			jQuery(this).focus();
			return false;
		}
	});

	jQuery('#formGas').submit(function() {

		var article_id_selected = '';
		for(i = 0; i < jQuery("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += jQuery("input[name='article_id_selected']:checked").eq(i).val()+',';
		}
		if(article_id_selected=='') {
			alert("Seleziona gli articoli che desideri cancellare definitivamente");
			return false;
		}	    
	    
	    if(!confirm("Sei sicuro di volere eliminare definitivamente gli articoli selezionati?")) return false;
	    
		article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
		jQuery('#article_id_selected').val(article_id_selected);
		
		return true;
	});

	<?php 
	/*
	 * devo ripulire il campo hidden che inizia per page perche' dopo la prima pagina sbaglia la ricerca con filtri
	 */
	?>
	jQuery('.filter').click(function() {
		jQuery("input[name^='page']").val('');
	});
	
});		
</script>