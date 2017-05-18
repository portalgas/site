<?php
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
			
				if($user->organization['Organization']['type']=='GAS' && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
					echo '<div class="row">';
					echo '<div class="col-md-10">';
					echo $this->Form->input('category_article_id', array('label' => '&nbsp;', 'class' => 'form-control', 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false));
					echo '</div>';
					echo '<div class="col-md-2">';
					echo $this->Form->input('flag_presente_articlesorders',array('label' => __('FlagPresenteArticlesorders'), 'class' => 'form-control', 'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false)); 
					echo '</div>';
					echo '</div>';
				}	
				else {
					if($user->organization['Organization']['type']=='GAS' && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
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
					$options = array('label' => '&nbsp;', 'options' => $ACLsuppliersOrganization,
											'empty' => 'Filtra per produttore',
											'name'=>'FilterArticleSupplierId','default'=>$FilterArticleSupplierId,'escape' => false);
					if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += array('class'=> 'selectpicker', 'data-live-search' => true);
					echo $this->Form->input('supplier_organization_id',$options);					
				}
				else
					echo $this->Form->input('category_article_id', array('label' => '&nbsp;', 'class' => 'form-control', 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false));
				echo '</div>';
				
				echo '<div class="col-md-2">';
				echo $this->Form->input('um',array('label' => __('Um'), 'class' => 'form-control', 'options' => $um,'name'=>'FilterArticleUm','empty'=>'-----','default'=>$FilterArticleUm,'escape' => false)); 
				echo '</div>';			
				echo '<div class="col-md-2">';
				echo $this->Form->input('stato',array('label' => __('Stato'), 'class' => 'form-control', 'options' => $stato,'name'=>'FilterArticleStato','default'=>$FilterArticleStato,'escape' => false)); 
				echo '</div>';	
				echo '</div>';	
				
				echo '<div class="row">';
				echo '<div class="col-md-6">';				 
				echo $this->Ajax->autoComplete('FilterArticleName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextArticlesArticles_name&format=notmpl',
										array('label' => 'Nome', 'class' => 'form-control', 'name'=>'FilterArticleName','value'=>$FilterArticleName,'escape' => false));
				echo '</div>';
				echo '<div class="col-md-3">';	
				echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); 
				echo '</div>';	
				echo '<div class="col-md-3">';	
				echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
				echo '</div>';		
				echo '</div>';
				
		echo '</fieldset>';

	if(!empty($results)) { 

		echo $this->Form->create('Article',array('id' => 'formGas'));
		echo $this->Form->hidden('articles_in_articlesorders',array('id' =>'articles_in_articlesorders', 'value'=>''));

		echo '<table cellpadding="0" cellspacing="0">';	
		echo '<tr>';	
			echo '<th></th>';	
			echo '<th>'.__('N').'</th>';	
			if($user->organization['Organization']['type']=='GAS')
				echo '<th>'.$this->Paginator->sort('supplier_id').'</th>';			
			if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') 
				echo '<th>'.$this->Paginator->sort('Category').'</th>';
			if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
				echo '<th>'.$this->Paginator->sort('codice').'</th>';
			?>
			<th colspan="2"><?php echo $this->Paginator->sort('name','Nome prodotto');?></th>
			<th><?php echo $this->Paginator->sort('confezione');?></th>
			<th><?php echo $this->Paginator->sort('PrezzoUnita');?></th>
			<th><?php echo $this->Paginator->sort('Prezzo/UM');?></th>
			<th><?php echo $this->Paginator->sort('bio',__('Bio'));?></th>
			<th><?php echo __('Type');?></th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
			<th>Associabili<?php echo '<span style="float:right;">'.$this->App->drawTooltip('Articoli associabili ad un ordine', __('toolFlag_presente_articlesorders'),$type='HELP',$pos='LEFT').'</span>';?>
			<th style="width:15px"></th>
			</th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);

		echo '<tr class="view">';
		echo '<td><a action="article_carts-0_'.$result['Article']['organization_id'].'_'.$result['Article']['id'].'" class="actionTrView openTrView" href="#" title="'.__('Href_title_expand').'"></a></td>';
		echo '<td>'.$numRow.'</td>';
		
		if($user->organization['Organization']['type']=='GAS') {
			echo '<td>';
			echo $this->Html->link($result['SuppliersOrganization']['name'], array('controller' => 'articles', 'action' => 'context_articles_index',null,'FilterArticleSupplierId='.$result['SuppliersOrganization']['id']),array('title' => 'filtra per produttore'));
			echo '</td>';
		}
		if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') 
			echo '<td>'.$result['CategoriesArticle']['name'].'</td>';
		if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
			echo '<td>'.$result['Article']['codice'].'</td>';
		
		echo '<td>';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}		
		echo '</td>';
		
		echo '<td>'.$result['Article']['name'].'&nbsp;';
		echo $this->App->drawArticleNota($i, strip_tags($result['Article']['nota']));
		echo '</td>';
		?>
		<td><?php echo $this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']); ?></td>
		<td><?php echo $result['Article']['prezzo_e'];?></td>
		<td><?php echo $this->App->getArticlePrezzoUM($result['Article']['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']); ?></td>
		<?php 
		/*		 * qui calcolo runtime se e' bio, se no prendo il campo article.bio		*/		
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
		 */
		echo '<td class="stato_'.$this->App->traslateEnum($result['Article']['stato']).'" title="'.__('toolTipStato').'" ></td>';

		if($result['Article']['flag_presente_articlesorders']=='Y') 
			echo '<td class="orderStatoPROCESSED-POST-DELIVERY" title="'.__('si').'" >';		
		else
			echo '<td class="orderStatoCLOSE" title="'.__('no').'" >';		

		echo '<td title="Articolo non presente tra quelli da ordinare" ';
		if($result['Article']['flag_presente_articlesorders']=='N' || $result['Article']['stato']=='N') 
			echo '" style="background-color:red;text-align:center;vertical-align: middle;"';
		else
			echo 'style="text-align:center;vertical-align: middle;"';
		echo '>';
		echo '</td>';


		echo '<td class="actions-table-img-3">';
			
			/*
			 *  ad admin_edit passo i parametri della ricerca, ordinamento e paginazione
			 * 	cosi' quando ritorno ad admin_index mantengo i filtri
			 */
			 
			/*
			 * se Organization.id == Article.organization_id 
			 *		e' il proprietario degli articoli
			 * se NO, gli articoli sono di ProdGas
			 *
			 * se SuppliersOrganization.owner_articles == 'REFERENT'
			 *		e' il proprietario degli articoli
			 * se NO, gli articoli sono di ProdGasSupplier 
			 */			
			if($user->organization['Organization']['id']==$result['Article']['organization_id'] &&
			   $result['SuppliersOrganization']['owner_articles']=='REFERENT') {
				echo $this->Html->link(null, array('action' => 'context_articles_edit', $result['Article']['id'],  
														'sort:'.$sort,'direction:'.$direction,'page:'.$page)
														,array('class' => 'action actionEdit','title' => __('Edit'))); 
				echo $this->Html->link(null, array('action' => 'context_articles_copy', $result['Article']['id'],
														'sort:'.$sort,'direction:'.$direction,'page:'.$page)
														,array('class' => 'action actionCopy','title' => __('Copy')));
				echo $this->Html->link(null, array('action' => 'context_articles_delete', $result['Article']['id'],
														'sort:'.$sort,'direction:'.$direction,'page:'.$page)
														,array('class' => 'action actionDelete','title' => __('Delete'))); 
			}
			else {
				echo $this->Html->link(null, array('action' => 'context_articles_view', $result['Article']['id'],
														'article_organization_id' => $result['Article']['organization_id'],
														'sort:'.$sort,'direction:'.$direction,'page:'.$page)
														,array('class' => 'action actionView','title' => __('View'))); 			
			}
			?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-0_<?php echo $result['Article']['organization_id'];?>_<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-0_<?php echo $result['Article']['organization_id'];?>_<?php echo $result['Article']['id'];?>"></td> 
	</tr>
<?php 
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

		echo $this->element('legendaArticlesPresentiInArticlesOrder');
		
		echo $this->element('legendaArticleFlagPresenteArticlesorders');
		
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

	jQuery('#articles_in_articlesorders_all').click(function () {
		var checked = jQuery("input[name='articles_in_articlesorders_all']:checked").val();
		if(checked=='ALL')
			jQuery('input[name=articles_in_articlesorders]').prop('checked',true);
		else
			jQuery('input[name=articles_in_articlesorders]').prop('checked',false);
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