<?php
if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $user->organization['Organization']['hasFieldArticleCodice']=='Y')
	$colspan = 10;
else
if($user->organization['Organization']['hasFieldArticleCategoryId']=='N' && $user->organization['Organization']['hasFieldArticleCodice']=='N')
	$colspan = 8;
else
	$colspan = 9;

if($user->organization['Organization']['type']=='PROD')
	$colspan--;
	
echo '<div class="articles">';
echo '<h2 class="ico-articles">';
echo __('Articles');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>';
echo $this->Html->link(__('List Articles'), array('action' => 'context_articles_index'),array('class' => 'action actionList','title' => __('List Articles')));
echo '</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';
	
		echo $this->Form->create('FilterArticle',array('id'=>'formGasFilter','type'=>'get'));
		echo '<fieldset class="filter">';
			echo '<legend>'.__('Filter Articles').'</legend>';
			echo '<table>';
			
				if(!empty($FilterArticleOrderId)) {
					echo '<tr>';
					echo '<td colspan="4">';
					echo $this->Form->input('order_id',array('label' => false, 'empty' => Configure::read('option.empty'), 'name'=>'FilterArticleOrderId' ,'default' => $FilterArticleOrderId));
					echo '</td>';
					echo '</tr>';
				}	
				echo '<tr>';
				echo '<td colspan="4">';
				echo $this->Form->drawFormCheckbox('Article', 'FilterArticleArticleTypeIds', array('options' => $ArticlesTypeResults, 'selected'=> $FilterArticleArticleIds, 'label'=>false, 'name'=>'FilterArticleArticleTypeIds'));
				echo '</td>';		
				echo '</tr>';	
				
				if($user->organization['Organization']['type']=='GAS' && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
					echo '<tr>';
					echo '<td colspan="3">';
					echo $this->Form->input('category_article_id', array('label' => false, 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false));
					echo '</td>';
					echo '<td>';
					echo $this->Form->input('flag_presente_articlesorders',array('label' => __('FlagPresenteArticlesorders'),'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false)); 
					echo '</td>';					
					echo '</tr>';
				}	
				else {
				if($user->organization['Organization']['type']=='GAS' && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
					echo '<tr>';
					echo '<td colspan="3">';
					echo '</td>';
					echo '<td>';
					echo $this->Form->input('flag_presente_articlesorders',array('label' => __('FlagPresenteArticlesorders'),'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false)); 
					echo '</td>';					
					echo '</tr>';
				}	
				
				}
								
				echo '<tr>';
				echo '<td colspan="2">';
				if($user->organization['Organization']['type']=='GAS') {
					$options = array('label' => false, 'options' => $ACLsuppliersOrganization,
											'empty' => 'Filtra per produttore',
											'name'=>'FilterArticleSupplierId','default'=>$FilterArticleSupplierId,'escape' => false);
					if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
						$options += array('class'=> 'selectpicker', 'data-live-search' => true);
					echo $this->Form->input('supplier_organization_id',$options);					
				}
				else
					echo $this->Form->input('category_article_id', array('label' => false, 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false));
				echo '</td>';
				
				echo '<td>';
				echo $this->Form->input('um',array('label' => __('Um'),'options' => $um,'name'=>'FilterArticleUm','empty'=>'-----','default'=>$FilterArticleUm,'escape' => false)); 
				echo '</td>';			
				echo '<td>';
				echo $this->Form->input('stato',array('label' => __('Stato'),'options' => $stato,'name'=>'FilterArticleStato','default'=>$FilterArticleStato,'escape' => false)); 
				echo '</td>';	
				echo '</tr>';	
				echo '</tr>';
				echo '<td colspan="2">';					 
				echo $this->Ajax->autoComplete('FilterArticleName', 
									   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextArticlesArticles_name&format=notmpl',
										array('label' => 'Nome','name'=>'FilterArticleName','value'=>$FilterArticleName,'size'=>'100','escape' => false));
				echo '</td>';
				echo '<td>';
				echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); 
				echo '</td>';	
				echo '<td>';
				echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); 
				echo '</td>';		
				echo '</tr>';
			echo '</table>';
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
			<th><?php echo __('Type');?></th>
			<th><?php echo $this->Paginator->sort('stato',__('Stato'));?></th>
			<th>		
				<input type="checkbox" id="articles_in_articlesorders_all" name="articles_in_articlesorders_all" value="ALL" />
					Presente nell'elenco tra gli articoli da associare ad un ordine
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
		
		echo '<td>';
		if(!empty($result['ArticlesType'])) 
			foreach($result['ArticlesType'] as $articleType)
				echo $articleType['label'].'<br />';
		echo '</td>';
		
		echo '<td title="'.__('toolTipStato').'" class="stato_'.$this->App->traslateEnum($result['Article']['stato']).'"></td>';

		/*
	     * ctrl se l'articolo e' presente tra gli articoli da ordinare
		 */
		
		echo '<td ';
		if($result['Article']['flag_presente_articlesorders']=='N' || $result['Article']['stato']=='N') 
			echo 'title="Articolo non presente tra quelli da ordinare" style="background-color:red;text-align:center;vertical-align: middle;"';
		else
			echo 'style="text-align:center;vertical-align: middle;"';
		echo '>';
		
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
			echo '<input type="checkbox" ';
			if($result['Article']['flag_presente_articlesorders']=='Y') 
				echo 'checked="checked" ';
			echo ' name="articles_in_articlesorders" value="'.$result['Article']['id'].'" />';
	   }
	   echo '</td>';


		echo '<td class="actions-table-img-3">';

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
		
		echo $this->Form->end(__('Submit'));

		echo $this->element('legendaArticlesPresentiInArticlesOrder');
		
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
	
	jQuery('#formGas').submit(function() {

		var tmp = '';
		jQuery("input[name='articles_in_articlesorders']").each(function( index ) {
		    var article_id = id = jQuery(this).val();
		    if(this.checked) 
		    	flag_presente_articlesorders = 'Y';
		    else 
		    	flag_presente_articlesorders = 'N';
		    	
		    tmp += article_id+'-'+flag_presente_articlesorders+',';
		});

        if(tmp!="")
        	tmp = tmp.substr(0, tmp.length-1);
        /* console.log(tmp); */
        	
		jQuery('#articles_in_articlesorders').val(tmp);
	
		return true;
	});
		
});
</script>