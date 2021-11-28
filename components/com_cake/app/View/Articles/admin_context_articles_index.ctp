<?php
$this->App->d($results, false);

if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $user->organization['Organization']['hasFieldArticleCodice']=='Y')
	$colspan = 15;
else
if($user->organization['Organization']['hasFieldArticleCategoryId']=='N' && $user->organization['Organization']['hasFieldArticleCodice']=='N')
	$colspan = 13;
else
	$colspan = 14;

if($user->organization['Organization']['type']=='PROD')
	$colspan--;
	
echo '<div class="articles">';
echo '<h2 class="ico-articles">';
echo __('Articles');
echo '<div class="actions-img">';
echo '<ul>';

if($this->App->isUserPermissionArticlesOrder($user) &&  
   !empty($FilterArticleSupplierId) && count($results)>0) 
	echo '<li>'.$this->Html->link(__('Articles Quick'), array('action' => 'context_articles_index_quick',null,'FilterArticleSupplierId='.$FilterArticleSupplierId),array('class' => 'action actionQuick','title' => __('Articles Quick'))).'</li>';

echo '<li>';
echo $this->Html->link(__('New Article'), array('action' => 'context_articles_add'),array('class' => 'action actionAdd','title' => __('New Article')));
echo '</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';
	
		echo $this->Form->create('FilterArticle',array('id'=>'formGasFilter','type'=>'get'));
		echo '<fieldset class="filter">';
			echo '<legend>'.__('Filter Articles').'</legend>';
			
				if(!empty($FilterArticleOrderId)) {
					echo '<div class="row">';
					echo '<div class="col-md-12">';
					echo $this->Form->input('order_id',array('label' => '&nbsp;', 'class' => 'form-control', 'empty' => Configure::read('option.empty'), 'name'=>'FilterArticleOrderId' ,'default' => $FilterArticleOrderId));
					echo '</div>';
					echo '</div>';
				}	
				echo '<div class="row">';
				echo '<div class="col-md-12">';
				echo $this->Form->drawFormCheckbox('Article', 'FilterArticleArticleTypeIds', array('options' => $ArticlesTypeResults, 'class' => 'form-control', 'selected'=> $FilterArticleArticleIds, 'label'=> ' ', 'name'=>'FilterArticleArticleTypeIds'));
				echo '</div>';
				echo '</div>';
			
				if(($user->organization['Organization']['type']=='GAS' || $user->organization['Organization']['type']=='PRODGAS') && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
					echo '<div class="row">';
					echo '<div class="col-md-8">';
					echo $this->Form->input('category_article_id', array('label' => '&nbsp;', 'class' => 'form-control', 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false));
					echo '</div>';
					echo '<div class="col-md-4">';
					echo $this->Form->input('flag_presente_articlesorders',array('label' => __('FlagPresenteArticlesorders'), 'class' => 'form-control', 'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false)); 
					echo '</div>';
					echo '</div>';
				}	
				else {
					if(($user->organization['Organization']['type']=='GAS' || $user->organization['Organization']['type']=='PRODGAS') && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
						echo '<div class="row">';
						echo '<div class="col-md-2 col-md-offset-10">';
						echo $this->Form->input('flag_presente_articlesorders',array('label' => __('FlagPresenteArticlesorders'), 'class' => 'form-control', 'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false)); 
						echo '</div>';
						echo '</div>';
					}	
				}
								
				echo '<div class="row">';
				echo '<div class="col-md-8">';
				if($user->organization['Organization']['type']=='GAS') {
					$options = ['label' => '&nbsp;', 
								'options' => $ACLsuppliersOrganization,
								'name'=>'FilterArticleSupplierId', 'default' => $FilterArticleSupplierId, 'escape' => false];
					if(count($ACLsuppliersOrganization) > 1) 
						$options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];								
					if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += ['class'=> 'selectpicker', 'data-live-search' => true];
					echo $this->Form->input('supplier_organization_id',$options);					
				}
				else
					echo $this->Form->input('supplier_organization_id', ['label' => '&nbsp;', 'class' => 'form-control', 'options' => $ACLsuppliersOrganization, 'name'=>'FilterArticleSupplierId','default'=>$FilterArticleSupplierId,'escape' => false]);
				echo '</div>';
				
				echo '<div class="col-md-2">';
				echo $this->Form->input('um',array('label' => __('Um'), 'class' => 'form-control', 'options' => $um,'name'=>'FilterArticleUm','empty'=>'-----','default'=>$FilterArticleUm,'escape' => false)); 
				echo '</div>';			
				echo '<div class="col-md-2">';
				echo $this->Form->input('stato',array('label' => __('Stato'), 'class' => 'form-control', 'options' => $stato,'name'=>'FilterArticleStato','default'=>$FilterArticleStato,'escape' => false)); 
				echo '</div>';	
				echo '</div>';	
				
				echo '<div class="row">';
				echo '<div class="col-md-8">';				 
				echo $this->Ajax->autoComplete('FilterArticleName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextArticlesArticles_name&format=notmpl',
										array('label' => 'Nome', 'class' => 'form-control', 'name'=>'FilterArticleName','value'=>$FilterArticleName,'escape' => false));
				echo '</div>';
				echo '<div class="col-md-2">';	
				echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); 
				echo '</div>';	
				echo '<div class="col-md-2">';	
				echo $this->Form->end(['label' => __('Filter'), 'class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]); 
				echo '</div>';		
				echo '</div>';
				
		echo '</fieldset>';

	if(!empty($results)) { 

		if($isSupplierOrganizationDesTitolare)
			echo $this->element('boxArticleOwnOrganization',array('ownOrganizationResults' => $ownOrganizationResults));
		
		echo $this->Form->create('Article',array('id' => 'formGas'));
		echo $this->Form->hidden('articles_in_articlesorders',array('id' =>'articles_in_articlesorders', 'value'=>''));

		echo '<div class="table-responsive"><table class="table table-hover">';	
		echo '<tr>';	
		echo '<th></th>';	
		echo '<th>'.__('N').'</th>';	
		if($user->organization['Organization']['type']=='GAS')
			echo '<th>'.__('SuppliersOrganization').'</th>';			
		if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') 
			echo '<th>'.__('Category').'</th>';
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<th>'.__('Codice').'</th>';
		echo '<th colspan="2">'.__('Nome prodotto').'</th>';
		echo '<th>'.__('Package').'</th>';
		echo '<th>'.__('PrezzoUnita').'</th>';
		echo '<th>'.__('Prezzo/UM').'</th>';
		echo '<th>'.__('Bio').'</th>';
		echo '<th>'.__('Type').'</th>';
		echo '<th>'.__('Stato').'</th>';
		echo '<th>Associabili <span style="float:right;">'.$this->App->drawTooltip('Articoli associabili ad un ordine', __('toolFlag_presente_articlesorders'),$type='HELP',$pos='LEFT').'</span>';
		echo '<th style="width:15px"></th>';
		echo '<th class="actions">'.__('Actions').'</th>';
		echo '</tr>';

	foreach ($results as $numResults => $result) {
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $numResults+1);

		echo '<tr class="view">';
		echo '<td>';
		echo '<a name="anchor_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></a>';
		switch($user->organization['Organization']['type']) {
			case 'PROD':
			break;
			case 'PRODGAS':
				echo '<a action="prodgas_article_carts-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
			break;
			case 'GAS':
				echo '<a action="article_carts-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a>';
			break;
		}
		echo '</td>';
		echo '<td>'.$numRow.'</td>';
		
		if($user->organization['Organization']['type']=='GAS') {
			echo '<td>';
			echo $this->Html->link($result['SuppliersOrganization']['name'], ['controller' => 'articles', 'action' => 'context_articles_index',null,'FilterArticleSupplierId='.$result['SuppliersOrganization']['id']], ['title' => __('FilterToSuppliers')]);
			echo '</td>';
		}
		if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') 
			echo '<td>'.$result['CategoriesArticle']['name'].'</td>';
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td>'.$result['Article']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['Article']['name'].'&nbsp;';
		echo $this->App->drawArticleNota($i, strip_tags($result['Article']['nota']));
		echo '</td>';
		echo '<td>'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']).'</td>';
		echo '<td>'.$result['Article']['prezzo_e'].'</td>';
		echo '<td>'.$this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']).'</td>';
		 
		/*
		 * qui calcolo runtime se e' bio, se no prendo il campo article.bio
		*/		
		echo '<td>';
		if($this->App->isArticlesTypeBio($result['ArticlesType'])) 
			echo '<span class="bio" title="'.Configure::read('bio').'"></span>';
		echo '</td>';
		echo '<td>';
		if(!empty($result['ArticlesType'])) 
			foreach($result['ArticlesType'] as $articleType)
				echo $articleType['label'].'<br />';
		echo '</td>';
		
		/*
	     * ctrl se l'articolo e' presente tra gli articoli da ordinare
	     *
	     * disabilitata la funzione di abilita / disabilita da qui: si puo' fare da edit perche' ho i diversi controlli
	     * se e' a stato = N non e' + visibile a front-end ma anche stampe / rich pagamento etc 
		echo '<td style="cursor:pointer;" data-attr-id="'.$result['Article']['id'].'" data-attr-organization-id="'.$result['Article']['organization_id'].'" data-attr-field="stato" class="articleUpdate stato_'.$this->App->traslateEnum($result['Article']['stato']).'" title="'.__('toolTipStato').'" ></td>';
		 */
		echo '<td class="articleUpdate stato_'.$this->App->traslateEnum($result['Article']['stato']).'" title="'.__('toolTipStato').'" ></td>';

		echo '<td style="cursor:pointer;" data-attr-id="'.$result['Article']['id'].'" data-attr-organization-id="'.$result['Article']['organization_id'].'" data-attr-field="flag_presente_articlesorders" class="articleUpdate ';
		if($result['Article']['flag_presente_articlesorders']=='Y') 
			echo 'orderStatoPROCESSED-POST-DELIVERY" title="'.__('si').'" >';		
		else
			echo 'orderStatoCLOSE" title="'.__('no').'" >';		

		echo '<td id="'.$result['Article']['organization_id'].'-'.$result['Article']['id'].'" title="Articolo non presente tra quelli da ordinare" style="text-align:center;vertical-align: middle;';
		if($result['Article']['flag_presente_articlesorders']=='N' || $result['Article']['stato']=='N') 
			echo 'background-color:red;"';
		else
			echo 'background-color:white;"';
		echo '>';
		echo '</td>';
		

		/*
		 * TODO 
		echo '<td>';
		$modal_url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Menus&action=article&article_organization_id='.$result['Article']['organization_id'].'&id='.$result['Article']['id'].'&format=notmpl';
		$modal_size = 'md'; 
		$modal_header = __('Article');
		echo '<button type="button" class="btn btn-primary btn-menu" data-attr-url="'.$modal_url.'" data-attr-size="'.$modal_size.'" data-attr-header="'.$modal_header.'" ><i class="fa fa-2x fa-navicon"></i></button>';
		echo '</td>';
		*/
		
		echo '<td class="actions-table-img-3">';
			
			/*
			 *  ad admin_edit passo i parametri della ricerca, ordinamento e paginazione
			 * 	cosi' quando ritorno ad admin_index mantengo i filtri
			 */
			if($result['Article']['owner']) {
				echo $this->Html->link(null, ['action' => 'context_articles_edit', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id'],  
														'sort:'.$sort,'direction:'.$direction,'page:'.$page]
														,['class' => 'action actionEdit','title' => __('Edit')]); 
				echo $this->Html->link(null, ['action' => 'context_articles_copy', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id'],
														'sort:'.$sort,'direction:'.$direction,'page:'.$page]
														,['class' => 'action actionCopy','title' => __('Copy')]);
				echo $this->Html->link(null, ['action' => 'context_articles_delete', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id'],
														'sort:'.$sort,'direction:'.$direction,'page:'.$page]
														,['class' => 'action actionDelete','title' => __('Delete')]); 
			}
			else {
				echo $this->Html->link(null, ['action' => 'context_articles_view', $result['Article']['id'], 'article_organization_id' => $result['Article']['organization_id'],
														'sort:'.$sort,'direction:'.$direction,'page:'.$page]
														,['class' => 'action actionView','title' => __('View')]); 
				
				// owner echo $result['SuppliersOrganization']['name'];
			}
		echo '</td>';
		echo '</tr>';
		echo '<tr class="trView" id="trViewId-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'">';
		echo '<td colspan="2"></td>';
		echo '<td colspan="'.$colspan.'" id="tdViewId-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'"></td> ';
		echo '</tr>'; 
	}

	echo '</table></div>';
	echo '<p>';
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	echo '</p>';

	echo '<div class="paging">';

		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	
		echo '</div>';
		
		echo '</fieldset>';
		
		echo $this->Form->end();

		echo $this->element('legendaArticlesPresentiInArticlesOrder');
		
		echo $this->element('legendaArticleFlagPresenteArticlesorders');
		
	}
	else {    
		if($iniCallPage)
			echo $this->element('boxMsg',array('class_msg' => 'success resultsNotFound', 'msg' => __('msg_search_no_parameter')));
		else
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => __('msg_search_not_result')));
	}
echo '</div>';
?>

<script type="text/javascript">
$(document).ready(function() {
		
	$(".actionNotaDetail").each(function () {
		$(this).click(function() {
			
			dataElement = $(this).attr('id');
			dataElementArray = dataElement.split('-');
			var label = dataElementArray[0];
			var idElement = dataElementArray[1];
			
			$('#articleNota-'+idElement).fadeIn();
			$('#articleNotaContinue-'+idElement).hide();
			
		});
	});	

	$('#articles_in_articlesorders_all').click(function () {
		var checked = $("input[name='articles_in_articlesorders_all']:checked").val();
		if(checked=='ALL')
			$('input[name=articles_in_articlesorders]').prop('checked',true);
		else
			$('input[name=articles_in_articlesorders]').prop('checked',false);
	});
	
	$('.actionCopy').click(function() {
		if(!confirm("Sei sicuro di voler copiare l'articolo selezionato?")) {
			return false;
		}		
		return true;
	});
	
	$('.articleUpdate').click(function() {

		var field = $(this).data('attr-field');
		var article_id = $(this).data('attr-id');
		var article_organization_id = $(this).data('attr-organization-id');

		switch(field) {
		  case 'stato':
				if($(this).hasClass('stato_no')) {
					$(this).removeClass('stato_no');
					$(this).addClass('stato_si');
				}
				else {
					$(this).removeClass('stato_si');
					$(this).addClass('stato_no');
				}
		  break;
		  case 'flag_presente_articlesorders':
				if($(this).hasClass('orderStatoPROCESSED-POST-DELIVERY')) {
					$(this).removeClass('orderStatoPROCESSED-POST-DELIVERY');
					$(this).addClass('orderStatoCLOSE');
					$(this).attr('title', 'No');
					$('#'+article_organization_id+'-'+article_id).css('background-color', 'red');
				}
				else {
					$(this).removeClass('orderStatoCLOSE');
					$(this).addClass('orderStatoPROCESSED-POST-DELIVERY');
					$(this).attr('title', 'Si');
					$('#'+article_organization_id+'-'+article_id).css('background-color', 'white');
				}
			break;
		}  
		
		var url = "/administrator/index.php?option=com_cake&controller=Articles&action=inverseValue&article_organization_id="+article_organization_id+"&article_id="+article_id+"&field="+field+"&format=notmpl";
		/* console.log(url); */
		
		$.ajax({
			type: "GET",
			url: url,
			success: function(response){
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$(this).html("Error!");
			}
		});
		return false;		
	});
	
});
</script>