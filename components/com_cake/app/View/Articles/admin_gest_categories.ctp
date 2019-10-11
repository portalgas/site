<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Articles Gest Categories');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'context_articles_index'),array('class' => 'action actionList','title' => __('List Articles'))); ?></li>
		</ul>
	</div>
	</h2>
	

<?php 
echo $this->Form->create('FilterArticle', ['id'=>'formGasFilter','type'=>'get']);
echo '<fieldset class="filter">';
echo '	<legend>'.__('Filter Articles').'</legend>';
echo '<div class="row">';
if($user->organization['Organization']['type']=='GAS') {
	$options = ['label' => '&nbsp;', 
				'options' => $ACLsuppliersOrganization,	
				'empty' => __('FilterToSuppliers'),
				'name'=>'FilterArticleSupplierId',
				'default' => $FilterArticleSupplierId,
				'escape' => false];
	if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
		$options += ['class'=> 'selectpicker', 'data-live-search' => true];					

	echo '<div class="col-md-5">';
	echo $this->Form->input('supplier_organization_id', $options);
	echo '</div>';
}
echo '<div class="col-md-5">';
echo $this->Ajax->autoComplete('FilterArticleName', 
				   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteArticles_name&format=notmpl',
					['label' => 'Nome','name'=>'FilterArticleName','value'=>$FilterArticleName,'size'=>'75','escape' => false]);
echo '</div>';
echo '<div class="col-md-1">';
echo $this->Form->reset('Reset', ['value' => 'Reimposta','class' => 'reset']);
echo '</div>';
echo '<div class="col-md-1">';
echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]); 
echo '</div>';
echo '</div>';
echo '</fieldset>';
					
	if(count($results)>0) { 
	
	echo $this->Form->create('Article',array('id' => 'formGas'));
	?>
	<fieldset>
		
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th></th>
			<th><input type="checkbox" class="form-control" id="article_id_selected_all" name="article_id_selected_all" value="ALL" /></th>
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
	foreach ($results as $numRow => $result) {
	?>
	<tr class="view">
		<td><a action="articles-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo '<input type="checkbox" class="form-control" id="'.$result['Article']['id'].'[article_id_selected]" name="article_id_selected" value="'.$result['Article']['id'].'" />';?></td>		
		<td><?php echo $result['CategoriesArticle']['name']; ?></td>
		<?php
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td>'.$result['Article']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['Article']['name'].'</td>';
		?>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '7' :'6';?>" id="tdViewId-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id'];?>"></td>
	</tr>
<?php } // end loops
	
	echo '</table></div>';
	echo '</fieldset>';
	
		echo $this->Form->hidden('FilterArticleSupplierId',array('id' => 'FilterArticleSupplierId', 'value' => $FilterArticleSupplierId));
		echo $this->Form->hidden('FilterArticleName',array('id' => 'FilterArticleName', 'value' => $FilterArticleName));
		echo $this->Form->hidden('article_id_selected',array('id' => 'article_id_selected', 'value' => ''));
		
		echo '<div class="box-message"><div id="flashMessage" class="notice" style="text-align:right;">';
		echo $this->Form->input('category_article_id', array('label' => false, 'id' => 'category_article_id', 'options' => $categories, 'empty' => 'Scegli la categoria', 'escape' => false));
		echo '</div></div>';
		
		echo $this->Form->end("Aggiorna la categoria degli articoli selezionati");	
	}
	else {  // if(count($results)>0)
		if(empty($FilterArticleSupplierId))  
			echo $this->element('boxMsg',array('class_msg' => 'success resultsNotFound', 'msg' => __('msg_search_no_parameter')));
		else
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));
	}	
	?>	
	
</div>

<script type="text/javascript">
$(document).ready(function() {
	
	$('#article_id_selected_all').click(function () {
		var checked = $("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_id_selected]').prop('checked',true);
		else
			$('input[name=article_id_selected]').prop('checked',false);
	});

	$('#formGas').submit(function() {

		var article_id_selected = '';
		for(i = 0; i < $("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += $("input[name='article_id_selected']:checked").eq(i).val()+',';
		}
		if(article_id_selected=='') {
			alert("Seleziona gli articoli che desideri aggiornare");
			return false;
		}	    
	    
		article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
		$('#article_id_selected').val(article_id_selected);

		var category_article_id = $('#category_article_id').val();
		if(category_article_id=='' || category_article_id==undefined) {
			alert("Devi scegliere la categoria da associare agli articoli");
			$('#category_article_id').focus();
			return false;
		}
		
		return true;
	});
});		
</script>