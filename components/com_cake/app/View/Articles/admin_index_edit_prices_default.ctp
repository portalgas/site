<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Edit Articles Prices'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="articles">
	<h2 class="ico-articles">
		<?php echo __('Edit Articles Prices');?>
		<div class="actions-img">
			<ul>
				<li><?php echo $this->Html->link(__('Edit Articles Prices Rate'), ['action' => 'index_edit_prices_percentuale'], ['class' => 'action actionPrice','title' => __('Edit Articles Prices Rate')]); ?></li>
			</ul>
		</div>			
	</h2>

	<?php 
	echo $this->Form->create('FilterArticle', ['id'=>'formGasFilter', 'type'=>'get']);
	echo '<fieldset class="filter">';
	echo '<legend>'.__('Filter Articles').'</legend>';
	echo '<table>';
		echo '<tr>';
		echo '<td>';
		$options = ['label' => '&nbsp;',
				 'name'=>'FilterArticleSupplierId',								 
				 'options' => $ACLsuppliersOrganization, 
				 'default' => $FilterArticleSupplierId,
				 'required' => 'false',
				 'escape' => false];
		if(count($ACLsuppliersOrganization) > 1) 
			$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];
		if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
			$options += ['class'=> 'selectpicker', 'data-live-search' => true]; 				
		echo $this->Form->input('supplier_organization_id',$options); 
		
		echo '</td>';
		
		echo '<td>';
		if(count($ACLsuppliersOrganization) > 1)  
			echo $this->Form->reset('Reset', ['value' => 'Reimposta','class' => 'reset']);
		echo '</td>';
		echo '<td>';
		echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]);
		echo '</td>';

		echo '</tr>';	
	echo '</table>';
	echo '</fieldset>';
		

	if(count($results)>0) {
		echo $this->Form->create('Article', ['id' => 'formGas']);
		
		echo '<div class="table-responsive"><table class="table table-hover">';
		echo '<tr>';
		echo '<th></th>';
		echo '<th>'.__('N').'</th>';
		echo '<th>'.$this->Paginator->sort('supplier_id').'</th>';
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<th>'.$this->Paginator->sort('codice').'</th>';
		echo '<th colspan="2">'.$this->Paginator->sort('name','Nome prodotto').'</th>';
		echo '<th>'.$this->Paginator->sort('Prezzo/UM').'</th>';
		echo '<th>'.$this->Paginator->sort('Prezzo').'</th>';
		echo '<th>Prezzo nuovo</th>';
		echo '<th>'.$this->Paginator->sort('stato',__('Stato')).'</th>';
		echo '</tr>';

		foreach ($results as $numResult => $result) {

			echo '<tr class="view">';
			echo '<td><a action="articles-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
			echo '<td>'.((int)$numResult+1).'</td>';
			echo '<td>'.$result['SuppliersOrganization']['name'].'</td>';
			
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<td>'.$result['Article']['codice'].'</td>';
			
			echo '<td>';
			if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
			}		
			echo '</td>';		
			echo '<td>'.$result['Article']['name'].'</td>';
			echo '<td>'.$this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
			echo '<td>'.$result['Article']['prezzo_e'].'</td>';
			echo '<td style="white-space: nowrap;">';
				
            if($result['Article']['owner']) {
                   echo '<input class="double" type="hidden" name="data[Article][prezzo_old]['.$result['Article']['id'].']" value="'.$result['Article']['prezzo_'].'" />';
                   echo '<input class="double form-control" type="text" tabindex="'.($i+1).'" name="data[Article][prezzo]['.$result['Article']['id'].']" value="'.$result['Article']['prezzo_'].'" style="display:inline" />&nbsp;&euro;';
               }
             else {
                 echo '<span class="label label-info">'.__('OwnerArticle').' '.__('ArticlesOwnerSUPPLIER').'</span>';
                 // echo $result['Article']['prezzo_e'];
             }
			echo '</td>';
			echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['Article']['stato']).'"></td>';
			echo '</tr>';
			echo '<tr class="trView" id="trViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
			echo '<td colspan="2"></td>';
			echo '<td colspan="';
			echo ($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? '8' :'7';
			echo '" id="tdViewId-'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td>';
			echo '</tr>	';

		}
		
		echo '</table></div>';
		
		echo $this->Form->hidden('FilterArticleSupplierId', ['id' => 'FilterArticleSupplierId','value' => $FilterArticleSupplierId]);
		echo $this->Form->hidden('updateArticlesOrder', ['id' => 'update_articles_order','value' => 'N']);
	
		echo $this->Form->submit("Aggiorna i prezzi degli articoli", ['id' => 'updateArticlesOrder_N', 'div'=> 'submitMultiple']);
		
		if($this->App->isUserPermissionArticlesOrder($user)) 
			echo $this->Form->submit("Aggiorna i prezzi degli articoli e anche di quelli associati ad ordini", ['id' => 'updateArticlesOrder_Y', 'div'=> 'submitMultiple', 'class' => 'buttonBlu']);
		
		echo $this->Form->end();

		}
    else {
        echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result'))); 	
	}
echo '</div>';	
?>
<script type="text/javascript">
$(document).ready(function() {
	
	$('.double').focusout(function() {validateNumberField(this,'importo');});
	
	$('.double').focusout(function() {
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


	$('#updateArticlesOrder_N').click(function() {	
		$('#update_articles_order').val('N');
		return true;
	});
	$('#updateArticlesOrder_Y').click(function() {
		if(!confirm("Sei sicuro di voler modificare anche il prezzo degli articoli associati agli ordini?"))
			return false;

		$('#update_articles_order').val('Y');
		return true;
	});

	<?php
	if(!empty($FilterArticleSupplierId) && empty($results)) {
	?>
	$('#formGasFilter').submit();
	<?php
	}
	?>	
});		
</script> 