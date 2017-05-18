<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('List Articles'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y' && $user->organization['Organization']['hasFieldArticleCodice']=='Y')
	$colspan = '14';
else
if($user->organization['Organization']['hasFieldArticleCategoryId']=='N' && $user->organization['Organization']['hasFieldArticleCodice']=='N')
	$colspan = '12';
else
	$colspan = '13';

?>

<div class="contentMenuLaterale">
	<h2 class="ico-articles">
		Articoli associati all'ordine
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('New Article'), array('action' => 'context_order_add', null, 'FilterArticleSupplierId='.$FilterArticleSupplierId),array('class' => 'action actionAdd','title' => __('New Article'))); ?></li>
		</ul>
	</div>
	</h2>
	
	<?php echo $this->Form->create('FilterArticle',array('id'=>'formGasFilter','type'=>'get'));?>
		<fieldset class="filter">
			<legend><?php echo __('Filter Articles'); ?></legend>	
			<table>
				<tr>
					<td>
						<?php echo $this->Ajax->autoComplete('FilterArticleName', 
										   Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextOrderArticles_name&supplier_organization_id='.$FilterArticleSupplierId.'&format=notmpl',
											array('label' => 'Nome','name'=>'FilterArticleName','value'=>$FilterArticleName,'size'=>'100','escape' => false));
						?>
					</td>			
					<?php 
						echo '<td>';
						if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y') 
							echo $this->Form->input('category_article_id', array('label' => false, 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false));
						echo '</td>';
					?>					
					<td>
						<?php echo $this->Form->reset('Reset', array('value' => 'Reimposta','class' => 'reset')); ?>
					</td>					
				</tr>
				<tr>
					<td>
						<?php echo $this->Form->drawFormCheckbox('Article', 'FilterArticleArticleTypeIds', array('options' => $ArticlesTypeResults, 'selected'=> $FilterArticleArticleIds, 'label'=>false, 'name'=>'FilterArticleArticleTypeIds'));?>
					</td>
					<td>
						<?php echo $this->Form->input('um',array('label' => __('Um'),'options' => $um,'name'=>'FilterArticleUm','empty'=>'-----','default'=>$FilterArticleUm,'escape' => false)); ?>
					</td>							
					<td>
						<?php echo $this->Form->end(array('label' => __('Filter'), 'class' => 'filter', 'div' => array('class' => 'submit filter', 'style' => 'display:none'))); ?>
					</td>		
				</tr>
			</table>
		</fieldset>


	<?php
	if(!empty($results)) { 
	?>	
	
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<?php
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
			<th><?php echo $this->Paginator->sort('Created');?></th>
			<th><?php echo $this->Paginator->sort('Modified');?></th>
			<th class="actions"><?php echo __('Actions');?></th>
	</tr>
	<?php
	foreach ($results as $i => $result):
		$numRow = ((($this->Paginator->counter(array('format'=>'{:page}'))-1) * $SqlLimit) + $i+1);
	?>
	<tr class="view">
		<td><a action="article_carts-<?php echo $order_id;?>_<?php echo $result['Article']['organization_id'];?>_<?php echo $result['Article']['id'];?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<td><?php echo $numRow;?></td>
		<?php
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
		<td><?php echo $result['ArticlesOrder'][0]['prezzo_e'];?></td>
		<td><?php echo $this->App->getArticlePrezzoUM($result['ArticlesOrder'][0]['prezzo'], $result['Article']['qta'], $result['Article']['um'], $result['Article']['um_riferimento']);?></td>
		<?php 
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
		?>
		<td title="<?php echo __('toolTipStato');?>" class="stato_<?php echo $this->App->traslateEnum($result['Article']['stato']); ?>"></td>
		<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Article']['created']); ?></td>
		<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($result['Article']['modified']); ?></td>
		<td class="actions-table-img-3">
			<?php 
			/*
			 *  ad admin_edit passo i parametri della ricerca, ordinamento e paginazione
			 * 	cosi' quando ritorno ad admin_index mantengo i filtri
			 */		
			echo $this->Html->link(null, array('action' => 'context_order_edit', $result['Article']['id'],  
													'sort:'.$sort,'direction:'.$direction,'page:'.$page)
													,array('class' => 'action actionEdit','title' => __('Edit'))); 
			echo $this->Html->link(null, array('action' => 'context_order_copy', $result['Article']['id'],
													'sort:'.$sort,'direction:'.$direction,'page:'.$page)
													,array('class' => 'action actionCopy','title' => __('Copy')));
			echo $this->Html->link(null, array('action' => 'context_order_delete', $result['Article']['id'],
													'sort:'.$sort,'direction:'.$direction,'page:'.$page)
													,array('class' => 'action actionDelete','title' => __('Delete'))); ?>
		</td>
	</tr>
	<tr class="trView" id="trViewId-<?php echo $order_id;?>_<?php echo $result['Article']['organization_id'];?>_<?php echo $result['Article']['id'];?>">
		<td colspan="2"></td>
		<td colspan="<?php echo $colspan;?>" id="tdViewId-<?php echo $order_id;?>_<?php echo $result['Article']['organization_id'];?>_<?php echo $result['Article']['id'];?>"></td>
	</tr>
<?php 
endforeach;
echo '</table>';

	}
	else {    
		if($iniCallPage)
			echo $this->element('boxMsg',array('class_msg' => 'success resultsNotFonud', 'msg' => __('msg_search_no_parameter')));
		else
			echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFonud'));
	}
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
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