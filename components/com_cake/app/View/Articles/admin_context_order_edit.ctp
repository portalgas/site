<?php
echo $this->Html->script('moduleCtrlArticle-v02.min');

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('List Articles'),array('controller'=>'Articles','action'=>'context_order_index', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Edit Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

$qta = $this->Form->value('Article.qta');
$prezzo = $this->Form->value('Article.prezzo');

echo '<div class="contentMenuLaterale">';

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
			echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('Dati articolo').'</a></li>';
			echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Price').'</a></li>';
			echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Condizioni d\'acquisto').'</a></li>';
			echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Img').'</a></li>';			
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
						echo $this->Form->input('ingredienti',array('tabindex'=>($i+1), 'class' => 'noeditor', 'cols' => '75','required' => 'false'));
						
					echo $this->Form->input('nota',array('tabindex'=>($i+1), 'class' => 'noeditor','cols' => '75', 'required' => 'false'));
					
					echo $this->Form->drawFormCheckbox('Article', 'article_type_id', array('options' => $ArticlesTypeResults, 'selected'=> $this->request->data['ArticlesType'], 'label'=>__('Type'), 'tabindex'=>($i+1), 'required'=>'false'));
						
					/*
					 * stato
					 */
					$options = array('options' => $stato, 'value'=>$this->Form->value('Article.stato'), 'label'=>__('Stato'), 'tabindex'=>($i+1), 'required'=>'false');
					echo $this->App->drawFormRadio('Article','stato', $options);
					
					echo $this->element('legendaArticleEditStato', array('context' => 'order', 'results' => $this->request->data, 'resultsAssociateArticlesOrder' => $resultsAssociateArticlesOrder, 'isArticleInCart' => $isArticleInCart, 'isUserPermissionArticlesOrder' => $this->App->isUserPermissionArticlesOrder($user)));

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-1">';
			
							echo "\r\n";
							echo '<table>';
							echo '<tr>';
							echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">'.__('Package').'</th>';
							echo '<th rowspan="2">Prezzo</th>';
							echo '<th rowspan="2"><span style="float:left;">Prezzo/UM<br />(Unit&agrave; di misura di riferimento)</span>';
							echo '<span style="float:right;">'.$this->App->drawTooltip('Unit&agrave; di misura di riferimento',__('toolTipUmRiferimento'),$type='HELP',$pos='LEFT').'</span>';
							echo '</th>';
							echo '</tr>';

							echo '<tr>';
							echo '<th style="width:135px;">'.__('qta').'</th>';
							echo '<th style="width:135px;">Unit&agrave; di misura</th>';
							echo '<tr>';
								
							echo "\r\n";
							echo '<tr>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('qta',array('id' => 'qta', 'value' => $this->request->data['Article']['qta_'], 'type' => 'text','label' => false,'tabindex'=>($i+1),'class' => 'double', 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td>';
							echo $this->Form->input('um',array('id' => 'um', 'label' => false,'options' => $um,'tabindex'=>($i+1), 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td style="white-space: nowrap;">';
							echo $this->Form->input('prezzo',array('id' => 'prezzo', 'value' => $this->request->data['Article']['prezzo_'], 'type' => 'text','label' => false,'after' => '&nbsp;&euro;', 'tabindex'=>($i+1),'class' => 'double', 'style' => 'display:inline', 'required' => 'false'));
							echo '</td>';
							echo "\r\n";
							echo '<td class="prezzo_um_riferimento" style="padding-left:25px;">';
							echo '</td>';
							echo '</tr>';
							echo '</table>';
							echo "\r\n";

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-2">';
			
						echo $this->Form->input('pezzi_confezione',array('id' => 'pezzi_confezione', 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'), 'required' => 'false'));
						echo $this->Form->input('qta_minima',array('id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','tabindex'=>($i+1), 'required' => 'false'));
						echo $this->Form->input('qta_massima',array('id' => 'qta_massima', 'label' => __('qta_massima'), 'type' => 'text','size' => '2','tabindex'=>($i+1), 'required' => 'false'));
						echo $this->Form->input('qta_multipli',array('id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'), 'required' => 'false'));
						if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
							echo $this->Form->input('alert_to_qta',array('id' => 'alert_to_qta', 'type' => 'text','size' => '2','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO'), 'required' => 'false'));

						/*
						 * settaggi rispetto all'ordine
						*/
						echo $this->Form->input('qta_minima_order',array('id' => 'qta_minima_order', 'label' => __('qta_minima_order'), 'type' => 'text','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO'), 'required' => 'false'));
						echo $this->Form->input('qta_massima_order',array('id' => 'qta_massima_order', 'label' => __('qta_massima_order'), 'type' => 'text','tabindex'=>($i+1),'after'=>$this->App->drawTooltip(__('qta_massima_order'),__('toolTipQtaMaxOrder'),$type='INFO'), 'required' => 'false'));
			
			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-3">';
			
						if(isset($file1)) {
							echo '<div class="input">';
							echo '<img class="img-responsive-disabled" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$this->request->data['Article']['organization_id'].'/'.$file1->name.'" />';	
							echo '&nbsp;&nbsp;&nbsp;'.$this->App->formatBytes($file1->size());
							echo '</div>';	
							echo $this->Form->checkbox('file1_delete', array('label' => 'Cancella file', 'value' => 'Y'));
							echo $this->Form->label('Cancella file');					
						}
														
						echo $this->Form->input('Document.img1', array(
						    'between' => '<br />',
						    'type' => 'file',
							'accept' => '.jpg, .jpeg, .gif, .png',
						     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
						));
						
						echo $this->element('legendaArticleImg');
			echo '</div>';
			echo '</div>'; // tab-content
			echo '</div>';
			echo '</fieldset>';
			
			
if(!empty($sort)) echo $this->Form->hidden('sort', ['value' => $sort]);
if(!empty($direction)) echo $this->Form->hidden('direction', ['value' => $direction]);
if(!empty($page)) echo $this->Form->hidden('page', ['value' => $page]);
echo $this->Form->hidden('id', ['value' => $this->request->data['Article']['id']]);
echo $this->Form->hidden('article_organization_id', ['value' => $this->request->data['Article']['organization_id']]);

echo $this->Form->end(__('Submit'));
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>
<script type="text/javascript">
$(document).ready(function() {
	$('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	$('.double').focusout(function() {validateNumberField(this,'prezzo');});

	$('#qta').focusout(function() {setArticlePrezzoUmRiferimento('', $("input:radio[name='data[Article][um_riferimento]']").filter(':checked').val());});
	$('#um').change(function() {setArticlePrezzoUmRiferimento('', $("input:radio[name='data[Article][um_riferimento]']").filter(':checked').val());});
	$('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento('', $("input:radio[name='data[Article][um_riferimento]']").filter(':checked').val());});

	setArticlePrezzoUmRiferimento('<?php echo $this->Form->value('Article.um');?>', '<?php echo $this->Form->value('Article.um_riferimento');?>');

	$('#formGas').submit(function() {
		if(!moduleCtrlArticle()) return false;
		else
			return true;
	});	
});
</script>