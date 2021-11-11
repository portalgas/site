<?php
echo $this->Html->script('moduleCtrlArticle-v02.min');

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('List Articles'),array('controller'=>'Articles','action'=>'context_order_index', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Add Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo $this->Form->create('Article',array('id'=>'formGas','enctype' => 'multipart/form-data'));
?>
	<fieldset>
		<legend><?php echo __('Add Article'); ?></legend>

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
					if($user->organization['Organization']['type']=='GAS')
						echo $this->Form->input('supplier_organization_id', array('id' => 'supplier_organization_id', 'options' => $ACLsuppliersOrganization,'empty' => Configure::read('option.empty'), 'default'=> $supplier_organization_id, 'tabindex'=>($i+1),'escape' => false, 'required' => 'false'));
					
					if($user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
						echo $this->Form->input('category_article_id', array('id' => 'category_article_id', 'options' => $categories, 'empty' => Configure::read('option.empty'),'tabindex'=>($i+1),'escape' => false));
					
					if($user->organization['Organization']['hasFieldArticleCodice']=='Y')
						echo $this->Form->input('codice',array('style' => 'width:75px','tabindex'=>($i+1), 'required'=>'false'));
						
					echo $this->Form->input('name',array('id' => 'name','tabindex'=>($i+1),  'required' => 'false'));

					if($user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
						echo $this->Form->input('ingredienti',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));
						
					echo $this->Form->input('nota',array('tabindex'=>($i+1),  'class' => 'noeditor', 'cols' => '75', 'required' => 'false'));

					echo $this->Form->drawFormCheckbox('Article', 'article_type_id', array('options' => $ArticlesTypeResults, 'selected'=> '', 'label'=>__('Type'), 'tabindex'=>($i+1), 'required'=>'false'));						
					/*					 * stato, un articolo se associato ad un ordine puo' solo essere a Y					*/
					echo $this->Form->hidden('stato', array('value' => 'Y'));			
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
						echo $this->Form->input('qta',array('id' => 'qta', 'type' => 'text', 'label' => false,'tabindex'=>($i+1),'class' => 'double', 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td>';
						echo $this->Form->input('um',array('id' => 'um', 'label' => false,'options' => $um,'tabindex'=>($i+1), 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td style="white-space: nowrap;">';
						echo $this->Form->input('prezzo',array('id' => 'prezzo', 'type' => 'text','label' => false,'after' => '&nbsp;&euro;', 'tabindex'=>($i+1),'class' => 'double', 'style' => 'display:inline', 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td class="prezzo_um_riferimento" style="padding-left:25px;">';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						echo "\r\n";

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-2">';
			
						echo $this->Form->input('pezzi_confezione',array('id' => 'pezzi_confezione', 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));
						echo $this->Form->input('qta_minima',array('id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','default' => '1','tabindex'=>($i+1), 'required'=>'false'));
						echo $this->Form->input('qta_massima',array('id' => 'qta_massima', 'label' => __('qta_massima'), 'type' => 'text','size' => '2','default' => '0','tabindex'=>($i+1), 'required'=>'false'));
						echo $this->Form->input('qta_multipli',array('id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));
						if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
							echo $this->Form->input('alert_to_qta',array('id' => 'alert_to_qta', 'type' => 'text','size' => '2','default' => '0','after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));

						/*
						 * settaggi rispetto all'ordine
						*/
						echo $this->Form->input('qta_minima_order',array('id' => 'qta_minima_order', 'label' => __('qta_minima_order'), 'type' => 'text','default' => '0','after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));
						echo $this->Form->input('qta_massima_order',array('id' => 'qta_massima_order', 'label' => __('qta_massima_order'), 'type' => 'text','default' => '0','after'=>$this->App->drawTooltip(__('qta_massima_order'),__('toolTipQtaMaxOrder'),$type='INFO'),'tabindex'=>($i+1), 'required'=>'false'));

			echo '</div>';
			echo '<div class="tab-pane fade" id="tabs-3">';
			
					echo $this->Form->input('Document.img1', array(
					    'between' => '<br />',
					    'type' => 'file',
					     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
					));
						
					echo $this->element('legendaArticleImg');
			echo '</div>';
			echo '</div>'; // tab-content
			echo '</div>';
			echo '</fieldset>';
			
echo $this->Form->hidden('action_post',array('id' => 'action_post','value' => 'context_order_index'));

echo $this->Form->submit(__('Submit Post Index'),array('id' => 'action_post_index', 'div'=> 'submitMultiple'));
echo '&nbsp;';
echo $this->Form->submit(__('Submit Post Add Article'),array('id' => 'action_post_add', 'div'=> 'submitMultiple','class' => 'buttonBlu'));

echo $this->Form->end();

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	$('.double').focusout(function() {validateNumberField(this,'prezzo');});

	$('#qta').focusout(function() {setArticlePrezzoUmRiferimento();});
	$('#um').change(function() {setArticlePrezzoUmRiferimento();});
	$('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento();});

	$('#action_post_index').click(function() {	
		$('#action_post').val('context_order_index');
	});
	$('#action_post_add').click(function() {	
		$('#action_post').val('context_order_add');
	});
	
	$('#formGas').submit(function() {
		if(!moduleCtrlArticle()) return false;
		else
			return true;
	});	
});
</script>