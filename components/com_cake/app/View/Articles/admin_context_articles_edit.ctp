<?php
echo $this->Html->script('moduleCtrlArticle.min');

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Articles'), array('controller' => 'Articles', 'action' => 'context_articles_index'));
$this->Html->addCrumb(__('Edit Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

$qta = $this->Form->value('Article.qta');
$prezzo = $this->Form->value('Article.prezzo');

echo '<div class="articles form">';

echo $this->Form->create('Article',array('id'=>'formGas','enctype' => 'multipart/form-data'));

if(!empty($articlesOrdersResults))
	echo $this->Form->hidden('isAssociateArticlesOrders',array('value'=>true));
else
	echo $this->Form->hidden('isAssociateArticlesOrders',array('value'=>false));

if(!empty($articlesStoreroomsResults))
	echo $this->Form->hidden('isAssociateStorerooms',array('value'=>true));
else
	echo $this->Form->hidden('isAssociateStorerooms',array('value'=>false));
?>
	<fieldset>
		<legend><?php echo __('Edit Article'); ?></legend>

			<?php
			echo '<div class="tabs">';
			echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
			echo '<li><a href="#tabs-0" data-toggle="tab">'.__('Dati articolo').'</a></li>';
			echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Prezzo').'</a></li>';
			echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Condizioni d\'acquisto').'</a></li>';
			echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Immagine').'</a></li>';			
			echo '</ul>';

			echo '<div class="tab-content">';
			echo '<div class="tab-pane fade active in" id="tabs-0">';	
					$i=0; 
					echo $this->Form->input('id');
					
					if($user->organization['Organization']['type']=='GAS')
						echo $this->Form->input('supplier_organization_id',array('id' => 'supplier_organization_id','options' => $ACLsuppliersOrganization,'tabindex'=>($i+1), 'required' => 'false'));
					
					if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
						echo $this->Form->input('category_article_id', array('id' => 'category_article_id', 'options' => $categories, 'empty' => Configure::read('option.empty'),'tabindex'=>($i+1),'escape' => false));
					
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo $this->Form->input('codice',array('id' => 'codice','style' => 'width:75px;','tabindex'=>($i+1), 'required' => 'false'));
						
					echo $this->Form->input('name',array('id' => 'name','tabindex'=>($i+1), 'required' => 'false'));

					if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
						echo $this->Form->input('ingredienti',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));
						
					echo $this->Form->input('nota',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));

					echo $this->Form->drawFormCheckbox('Article', 'article_type_id', array('options' => $ArticlesTypeResults, 'selected'=> $this->request->data['ArticlesType'], 'label'=>__('Type'), 'tabindex'=>($i+1), 'required'=>'false'));						
					/*
					 * stato
					 */
					
					echo $this->element('legendaArticleEditStato', array('context' => 'article', 'results' => $this->request->data, 'resultsAssociateArticlesOrder' => $resultsAssociateArticlesOrder, 'isArticleInCart' => $isArticleInCart, 'isUserPermissionArticlesOrder' => $this->App->isUserPermissionArticlesOrder($user)));

					$options = array('options' => $stato, 'value'=>$this->Form->value('Article.stato'), 'label'=>__('Stato'), 'tabindex'=>($i+1), 'required'=>'false');
					echo $this->App->drawFormRadio('Article','stato', $options);

					$options = array('options' => $flag_presente_articlesorders, 'value'=>$this->Form->value('Article.flag_presente_articlesorders'), 'label'=>__('FlagPresenteArticlesorders'), 'tabindex'=>($i+1), 'required'=>'false');
					echo $this->App->drawFormRadio('Article','flag_presente_articlesorders', $options);
					
					
					echo '<div class="input text">';
					echo '<label for="codice">'.__('Created').'</label>';
					if($this->request->data['Article']['created'] != '0000-00-00 00:00:00')
						echo $this->Time->i18nFormat($this->request->data['Article']['created'],"%e %B %Y");
					echo '</div>';

					echo '<div class="input text">';
					echo '<label for="codice">'.__('Modified').'</label>';
					if($this->request->data['Article']['modified'] != '0000-00-00 00:00:00')
						echo $this->Time->i18nFormat($this->request->data['Article']['modified'],"%e %B %Y");
					echo '</div>';
			
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-2">';
			
							echo "\r\n";
							echo '<table>';
							echo '<tr>';
							echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">Confezione</th>';
							echo '<th rowspan="2">Prezzo</th>';
							echo '<th rowspan="2"><span style="float:left;">Prezzo/UM<br />(Unit&agrave; di misura di riferimento)</span>';
							echo '<span style="float:right;">'.$this->App->drawTooltip('Unit&agrave; di misura di riferimento',__('toolFlag_presente_articlesorders'), $type='HELP',$pos='LEFT').'</span>';
							echo '</th>';
							echo '</tr>';

							echo '<tr>';
							echo '<th style="width:135px;">Quantit&agrave;</th>';
							echo '<th style="width:135px;">Unit&agrave; di misura</th>';
							echo '<tr>';
								
							echo "\r\n";
							echo '<tr>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('qta',array('id' => 'qta', 'value' => $this->request->data['Article']['qta_'], 'type' => 'text','label' => false,'size' => '5','tabindex'=>($i+1),'class' => 'double', 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('um',array('id' => 'um', 'label' => false,'options' => $um,'tabindex'=>($i+1), 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('prezzo',array('id' => 'prezzo', 'value' => $this->request->data['Article']['prezzo_'], 'type' => 'text','label' => false,'after' => '&nbsp;&euro;','size' => '5','tabindex'=>($i+1),'class' => 'double', 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td class="prezzo_um_riferimento">';
							echo '</td>';
							echo '</tr>';
							echo '</table>';
							echo "\r\n";
							
							if($this->App->isUserPermissionArticlesOrder($user) && !empty($resultsAssociateArticlesOrder))  
								echo $this->element('legendaArticlesInArticlesOrder', array('results' => $resultsAssociateArticlesOrder));							
			
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-3">';
			
						echo $this->Form->input('qta_minima',array('id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','tabindex'=>($i+1),'class' => 'noWidth', 'required' => 'false'));
						echo $this->Form->input('qta_massima',array('id' => 'qta_massima', 'label' => __('qta_massima'), 'type' => 'text','size' => '2','tabindex'=>($i+1),'class' => 'noWidth', 'required' => 'false'));
						echo $this->Form->input('pezzi_confezione',array('id' => 'pezzi_confezione', 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
						echo $this->Form->input('qta_multipli',array('id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
						if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
							echo $this->Form->input('alert_to_qta',array('id' => 'alert_to_qta', 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
						
						if($this->App->isUserPermissionArticlesOrder($user) && !empty($resultsAssociateArticlesOrder))  							echo $this->element('legendaArticlesInArticlesOrder', array('results' => $resultsAssociateArticlesOrder));						

						/*
						 * settaggi rispetto all'ordine
						*/						
						echo $this->Form->input('qta_minima_order',array('id' => 'qta_minima_order', 'label' => __('qta_minima_order'), 'type' => 'text','size' => '5','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
						echo $this->Form->input('qta_massima_order',array('id' => 'qta_massima_order', 'label' => __('qta_massima_order'), 'type' => 'text','size' => '5','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_massima_order'),__('toolTipQtaMaxOrder'),$type='INFO'),'class' => 'noWidth', 'required' => 'false'));
			
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-4">';
			
						if(isset($file1)) {
							echo '<div class="input">';
							echo '<img src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$this->request->data['Article']['organization_id'].'/'.$file1->name.'" />';	
							echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
							echo '</div>';	
							echo $this->Form->checkbox('file1_delete', array('label' => 'Cancella file', 'value' => 'Y'));
							echo $this->Form->label('Cancella file');					
						}
														
						echo $this->Form->input('Document.img1', array(
						    'between' => '<br />',
						    'type' => 'file',
						     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
						));
						
						echo $this->element('legendaArticleImg');

					echo $this->element('legendaArticleImg');
					
			echo '</div>';
			echo '</div>'; // tab-content
			echo '</div>';
			echo '</fieldset>';
			
if(!empty($sort)) echo $this->Form->hidden('sort',array('value'=>$sort));
if(!empty($direction)) echo $this->Form->hidden('direction',array('value'=>$direction));
if(!empty($page)) echo $this->Form->hidden('page',array('value'=>$page));

echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'context_articles_index',null,
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class'=>'action actionReload'));?></li>
		<li><?php echo $this->Html->link(__('Delete'), array('action' => 'context_articles_delete', $this->Form->value('Article.id'),
																	'sort:'.$sort,'direction:'.$direction,'page:'.$page),
																	array('class' => 'action actionDelete','title' => __('Delete'))); ?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	jQuery('.double').focusout(function() {validateNumberField(this,'prezzo');});

	jQuery('#qta').focusout(function() {setArticlePrezzoUmRiferimento('', jQuery("input:radio[name='data[Article][um_riferimento]']").filter(':checked').val());});
	jQuery('#um').change(function() {setArticlePrezzoUmRiferimento('', jQuery("input:radio[name='data[Article][um_riferimento]']").filter(':checked').val());});
	jQuery('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento('', jQuery("input:radio[name='data[Article][um_riferimento]']").filter(':checked').val());});

	setArticlePrezzoUmRiferimento('<?php echo $this->Form->value('Article.um');?>', '<?php echo $this->Form->value('Article.um_riferimento');?>');

	jQuery('#formGas').submit(function() {
		if(!moduleCtrlArticle()) return false;
		else
			return true;
	});
});
</script>