<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Articles Gest Categories');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'context_articles_index'),array('class' => 'action actionList','title' => __('List Articles'))); ?></li>
		</ul>
	</div>
	</h2>
	
	
	<?php echo $this->Form->create('FilterArticle',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Articles'); ?></legend>
			<table>
				<tr>
					<?php 
					if($user->organization['Organization']['type']=='GAS') {
						echo '<td>';
						echo $this->Form->input('supplier_organization_id',array('label' => false,'options' => $ACLsuppliersOrganization,'empty' => 'Filtra per produttore','name'=>'FilterArticleSupplierId','default' => $FilterArticleSupplierId,'escape' => false));
						echo '</td>';
					}
					echo '<td>';
					echo $this->Ajax->autoComplete('FilterArticleName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteArticles_name&format=notmpl',
										array('label' => 'Nome','name'=>'FilterArticleName','value'=>$FilterArticleName,'size'=>'75','escape' => false));
					echo '</td>';
					?>
					<td>
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>
				</tr>	
			</table>
		</fieldset>	
					

	<?php
	if(count($results)>0) { 
	
	echo $this->Form->create('Article',array('id' => 'formGas'));
	?>
	<fieldset>
		
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" /></th>
			<th><?php echo $this->Paginator->sort('category');?></th>			
			<th></th>
			<?php
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<th>'.$this->Paginator->sort('codice').'</th>';
			?>
			<th><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
	</tr>
	<?php
	foreach ($results as $numRow => $result):
	?>
	<tr class="view">
		<td><a action="articles-<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo '<input type="checkbox" id="'.$result['Article']['id'].'[article_id_selected]" name="article_id_selected" value="'.$result['Article']['id'].'" />';?></td>		
		<td><?php echo $result['CategoriesArticle']['name']; ?></td>
		<?php
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td>'.$result['Article']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['Article']['name'].'</td>';
		?>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '7' :'6';?>" id="tdViewId-<?php echo $result['Article']['id'];?>"></td>
	</tr>
<?php endforeach;
	
	echo '</table>';
	echo '</fieldset>';
	
		echo $this->Form->hidden('FilterArticleSupplierId',array('id' => 'FilterArticleSupplierId', 'value' => $FilterArticleSupplierId));
		echo $this->Form->hidden('FilterArticleName',array('id' => 'FilterArticleName', 'value' => $FilterArticleName));
		echo $this->Form->hidden('article_id_selected',array('id' => 'article_id_selected', 'value' => ''));
		
		echo '< <div class="box-message"><div id="flashMessage" class="notice" style="text-align:right;">';
		echo $this->Form->input('category_article_id', array('label' => false, 'id' => 'category_article_id', 'options' => $categories, 'empty' => 'Scegli la categoria', 'escape' => false));
		echo '</div></div>';
		
		echo $this->Form->end("Aggiorna la categoria degli articoli selezionati");	
	}
	else {  // if(count($results)>0)
		if(empty($FilterArticleSupplierId))  
			echo $this->element('boxMsg',array('class_msg' => 'success resultsNotFonud', 'msg' => __('msg_search_no_parameter')));
		else
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
	}	
	?>	
	
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	
	jQuery('#article_id_selected_all').click(function () {
		var checked = jQuery("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=article_id_selected]').prop('checked',true);
		else
			jQuery('input[name=article_id_selected]').prop('checked',false);
	});

	jQuery('#formGas').submit(function() {

		var article_id_selected = '';
		for(i = 0; i < jQuery("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += jQuery("input[name='article_id_selected']:checked").eq(i).val()+',';
		}
		if(article_id_selected=='') {
			alert("Seleziona gli articoli che desideri aggiornare");
			return false;
		}	    
	    
		article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
		jQuery('#article_id_selected').val(article_id_selected);

		var category_article_id = jQuery('#category_article_id').val();
		if(category_article_id=='' || category_article_id==undefined) {
			alert("Devi scegliere la categoria da associare agli articoli");
			jQuery('#category_article_id').focus();
			return false;
		}
		
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