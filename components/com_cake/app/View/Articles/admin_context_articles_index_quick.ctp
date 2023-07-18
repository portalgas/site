<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Articles quick');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('List Articles'), ['controller' => 'Connects','action' => 'index', 'c_to' => 'admin/articles', 'a_to' => 'index-quick'], ['class' => 'action actionList','title' => __('List Articles')]);?></li>
		</ul>
	</div>
	</h2>
	
	
<?php 
echo $this->Form->create('FilterArticle', ['id'=>'formGasFilter', 'type'=>'get']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Articles').'</legend>';
echo '<div class="row">';
					
if($user->organization['Organization']['type']=='GAS' || $user->organization['Organization']['type']=='PRODGAS') {

	$options = ['label' => '&nbsp;', 'options' => $ACLsuppliersOrganization, 'name' => 'FilterArticleSupplierId', 'default' => $FilterArticleSupplierId,'escape' => false];
	if(count($ACLsuppliersOrganization) > 1)
		$options += ['data-placeholder' => __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')]; 
	if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum'))
		$options += array('class'=> 'selectpicker', 'data-live-search' => true);
	
	echo '<div class="col-md-4">';
	echo $this->Form->input('supplier_organization_id', $options);
	echo '</div>';
}
echo '<div class="col-md-4">';
echo $this->Ajax->autoComplete('FilterArticleName', 
				   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextArticlesArticles_name&format=notmpl',
					array('label' => 'Nome', 'class' => 'form-control', 'name'=>'FilterArticleName','value'=>$FilterArticleName ,'escape' => false));
				echo '</div>';

				echo '<div class="col-md-2">';
				echo $this->Form->input('stato',array('label' => __('Stato'), 'class' => 'form-control', 'options' => $stato,'name'=>'FilterArticleStato','default'=>$FilterArticleStato,'escape' => false)); 
				echo '</div>';	

				echo '<div class="col-md-1">';
				echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); 
				echo '</div>';
				echo '<div class="col-md-1">';
				echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
				echo '</div>';
				
				echo '</div>';

echo '</fieldset>';
				
if(count($results)>0) { 
	
echo '<div class="alert alert-danger alert-dismissable">'.__('msg_article_edit_not_propagate_article_order').'</div>';

echo $this->Form->create('ArticlesOrder', ['id' => 'formGas']);
?>
<fieldset>
		
	<div class="table-responsive"><table class="table table-hover">
	<tr>
			<th></th>
			<th colspan="2">
				<input type="checkbox" id="article_id_selected_all" name="article_id_selected_all" value="ALL" />
				<img alt="Seleziona gli articoli da cancellare" src="<?php echo Configure::read('App.img.cake');?>/actions/24x24/button_cancel.png" />
			</th>			
			<th></th>
			<?php
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<th style="width:5%;">'.$this->Paginator->sort('codice').'</th>';
			?>
			<th></th>
			<th style="width:60%;"><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('Package');?></th>
			<th style="width:10%;padding-left:25px;"><?php echo $this->Paginator->sort('Prezzo');?></th>
			<th style="text-align:center;"><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	$i=-1;
	foreach ($results as $numResult => $result) {
	
			if($result['Article']['owner']) {		   	
				?>
				<tr class="view">
					<td><a action="articles-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
					<td><?php echo ((int)$numResult+1);?></td>		
					<td><?php echo '<input type="checkbox" id="'.$result['Article']['id'].'[article_id_selected]" name="article_id_selected" value="'.$result['Article']['id'].'" />';?></td>		
					<td>
						<img alt="" src="<?php echo Configure::read('App.img.cake');?>/blank32x32.png" id="submitEcomm-<?php echo $result['Article']['id'];?>" class="buttonCarrello submitEcomm" />
						<div id="msgEcomm-<?php echo $result['Article']['id'];?>" class="msgEcomm"></div>
					</td>
					<?php
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td>'.$this->Form->input('codice',array('id' => $result['Article']['id'].'-codice-articles','class' => 'activeUpdate','value' => $result['Article']['codice'],'label' => false, 'tabindex'=>($i+1))).'</td>';
					echo '<td>';
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
					}		
					echo '</td>';
					?>		
					<td><?php echo $this->Form->input('name',array('id' => $result['Article']['id'].'-name-articles','class' => 'activeUpdate','value' => $result['Article']['name'],'label' => false, 'tabindex'=>($i+1))); ?></td>
					<td style="white-space: nowrap;"><?php echo $this->Form->input('qta',array('id' => $result['Article']['id'].'-qta-articles','class' => 'activeUpdate qta','value' => $result['Article']['qta_'],'type' => 'text','label' => false, 'size' => '2','after' => '&nbsp;'.$this->App->traslateEnum($result['Article']['um']), 'style' => 'display:inline', 'tabindex'=>($i+1))); ?></td>
					<td style="white-space: nowrap; padding-left:25px;"><?php echo $this->Form->input('prezzo',array('id' => $result['Article']['id'].'-prezzo-articles','class' => 'importoSubmit activeUpdate double','value' => $result['Article']['prezzo_'],'type' => 'text','label' => false, 'after' => '&nbsp;&euro;','style' => 'display:inline','tabindex'=>($i+1))); ?></td>
					<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
					<td class="actions-table-img">
						<?php 
						echo $this->Html->link(null, ['action' => 'context_articles_edit', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id']], ['class' => 'action actionEdit','title' => __('Edit')]);
						echo $this->Html->link(null, ['action' => 'context_articles_delete', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id']], ['class' => 'action actionDelete','title' => __('Delete')]);
						?>
					</td>
				</tr>
			<?php
			}
			else {
			?>
				<tr class="view">
					<td><a action="articles-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
					<td><?php echo ((int)$numResult+1);?></td>		
					<td></td>		
					<td></td>
					<?php
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo '<td>'.$result['Article']['codice'].'</td>';
					echo '<td>';
					if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
						echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
					}		
					echo '</td>';
					?>		
					<td><?php echo $result['Article']['name']; ?></td>
					<td><?php echo $result['Article']['qta_'].'&nbsp;'.$this->App->traslateEnum($result['Article']['um']); ?></td>
					<td><?php echo $result['Article']['prezzo_e']; ?></td>
					<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
					<td class="actions-table-img">
						<?php 
						echo $this->Html->link(null, ['action' => 'context_articles_view', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id']], ['class' => 'action actionView','title' => __('View')]);
						?>
					</td>
				</tr>			
			<?php
			}
			?>			
	<tr class="trView" id="trViewId-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '9' :'8';?>" id="tdViewId-<?php echo $result['Article']['organization_id']; ?>_<?php echo $result['Article']['id'];?>"></td>
	</tr>
<?php }
	
	echo '</table></div>';
	echo '</fieldset>';
	
		echo $this->Form->hidden('FilterArticleSupplierId',array('id' => 'FilterArticleSupplierId', 'value '=> $FilterArticleSupplierId));
		echo $this->Form->hidden('FilterArticleName',array('id' => 'FilterArticleName', 'value' => $FilterArticleName));
		echo $this->Form->hidden('article_id_selected',array('id' => 'article_id_selected', 'value' => ''));
		echo $this->Form->end("Cancella gli articoli selezionati");	
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

	$('.qta').focusout(function() {validateNumberField(this,'quantita\'');});
	$('.double').focusout(function() {validateNumberField(this,'prezzo');});

	$(".activeUpdate").each(function () {
		$(this).change(function() {
			/* get id da id="id-field-table"  */
			var idRow = $(this).attr('id');
			numResult = idRow.substring(0,idRow.indexOf('-'));
			
			var value =  $(this).val();
			
			var url = '';
			url = "/administrator/index.php?option=com_cake&controller=Ajax&action=updateGeneric&idRow="+idRow+"&format=notmpl";

			$.ajax({
				type: "POST",
				url: url,
				data: "value="+value,
				success: function(response){
					 $('#msgEcomm-'+numResult).html(response);
				},
				error:function (XMLHttpRequest, textStatus, errorThrown) {
					 $('#msgEcomm-'+numResult).html(textStatus);
					 $('#submitEcomm-'+numResult).attr('src',app_img+'/blank32x32.png');
				}
			});
			return false;			
		});
	});
	
	$('#article_id_selected_all').click(function () {
		var checked = $("input[name='article_id_selected_all']:checked").val();
		if(checked=='ALL')
			$('input[name=article_id_selected]').prop('checked',true);
		else
			$('input[name=article_id_selected]').prop('checked',false);
	});

	$('.importoSubmit').change(function() {

		var prezzo = $(this).val();

		if(prezzo=='' || prezzo==undefined) {
			alert("Devi indicare il prezzo da associare all'articolo");
			$(this).val("0,00");
			$(this).focus();
			return false;
		}	
		
		if(prezzo=='0,00') {
			alert("Il prezzo dev'essere indicato con un valore maggior di 0");
			$(this).focus();
			return false;
		}
	});

	$('#formGas').submit(function() {

		var article_id_selected = '';
		for(i = 0; i < $("input[name='article_id_selected']:checked").length; i++) {
			article_id_selected += $("input[name='article_id_selected']:checked").eq(i).val()+',';
		}
		if(article_id_selected=='') {
			alert("Seleziona gli articoli che desideri cancellare definitivamente");
			return false;
		}	    
	    
	    if(!confirm("Sei sicuro di volere eliminare definitivamente gli articoli associati\ne le eventuali associazioni con gli ordini?")) return false;
	    
		article_id_selected = article_id_selected.substring(0,article_id_selected.length-1);
		$('#article_id_selected').val(article_id_selected);
		
		return true;
	});
});		
</script>