<?php
echo $this->Html->script('moduleCtrlArticle.min');

$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('List Articles'),array('controller'=>'Articles','action'=>'context_order_index', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Add Article'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create('Article',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>

	<fieldset>
		<legend><?php echo __('Add Article'); ?></legend>

         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati articolo'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Prezzo'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Condizioni d\'acquisto'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Immagine'); ?></span></a></li>
             </ul>

             <div id="tabs-0">
				<?php
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
					echo $this->Form->hidden('stato', array('value' => 'Y'));				?>
			</div>
            <div id="tabs-1">
					<?php
						echo "\r\n";
						echo '<table>';
						echo '<tr>';
						echo '<th colspan="2" style="text-align:center;border-bottom:medium none;">Confezione</th>';
						echo '<th rowspan="2">Prezzo</th>';
						echo '<th rowspan="2"><span style="float:left;">Prezzo/UM<br />(Unit&agrave; di misura di riferimento)</span>';
						echo '<span style="float:right;">'.$this->App->drawTooltip('Unit&agrave; di misura di riferimento',__('toolTipUmRiferimento'),$type='HELP',$pos='LEFT').'</span>';
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
						echo $this->Form->input('qta',array('id' => 'qta', 'type' => 'text', 'label' => false,'size' => '5','tabindex'=>($i+1),'class' => 'double', 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td>';
						echo $this->Form->input('um',array('id' => 'um', 'label' => false,'options' => $um,'tabindex'=>($i+1), 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td>';
						echo $this->Form->input('prezzo',array('id' => 'prezzo', 'type' => 'text','label' => false,'after' => '&nbsp;&euro;','size' => '5','tabindex'=>($i+1),'class' => 'double', 'required'=>'false'));
						echo '</td>';
						echo "\r\n";
						echo '<td class="prezzo_um_riferimento">';
						echo '</td>';
						echo '</tr>';
						echo '</table>';
						echo "\r\n";
						?>
				</div>
           		<div id="tabs-2">
					<?php 
						echo $this->Form->input('qta_minima',array('id' => 'qta_minima', 'label' => __('qta_minima'), 'type' => 'text','size' => '2','default' => '1','tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));
						echo $this->Form->input('qta_massima',array('id' => 'qta_massima', 'label' => __('qta_massima'), 'type' => 'text','size' => '2','default' => '0','tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));
						echo $this->Form->input('pezzi_confezione',array('id' => 'pezzi_confezione', 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('pezzi_confezione'),__('toolTipPezziConfezione'),$type='INFO'),'tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));
						echo $this->Form->input('qta_multipli',array('id' => 'qta_multipli', 'label' => __('qta_multipli'), 'type' => 'text','size' => '2','default' => '1','after'=>$this->App->drawTooltip(__('qta_multipli'),__('toolTipQtaMultipli'),$type='INFO'),'tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));
						if($user->organization['Organization']['hasFieldArticleAlertToQta']=='Y')
							echo $this->Form->input('alert_to_qta',array('id' => 'alert_to_qta', 'type' => 'text','size' => '2','default' => '0','after'=>$this->App->drawTooltip(__('alert_to_qta'),__('toolTipAlertToQta'),$type='INFO'),'tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));

						/*
						 * settaggi rispetto all'ordine
						*/
						echo $this->Form->input('qta_minima_order',array('id' => 'qta_minima_order', 'label' => __('qta_minima_order'), 'type' => 'text','size' => '5','default' => '0','after'=>$this->App->drawTooltip(__('qta_minima_order'),__('toolTipQtaMinOrder'),$type='INFO'),'tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));
						echo $this->Form->input('qta_massima_order',array('id' => 'qta_massima_order', 'label' => __('qta_massima_order'), 'type' => 'text','size' => '5','default' => '0','after'=>$this->App->drawTooltip(__('qta_massima_order'),__('toolTipQtaMaxOrder'),$type='INFO'),'tabindex'=>($i+1),'class' => 'noWidth', 'required'=>'false'));
					?>
				</div>
           		<div id="tabs-3">
					<?php 
					echo $this->Form->input('Document.img1', array(
					    'between' => '<br />',
					    'type' => 'file',
					     'label' => 'Carica una nuova immagine', 'tabindex'=>($i+1)
					));
						
					echo $this->element('legendaArticleImg');
					?>
				</div>
			</div>
	</fieldset>
<?php 
echo $this->Form->hidden('action_post',array('id' => 'action_post','value' => 'context_order_index'));

echo $this->Form->submit(__('Submit Post Index'),array('id' => 'action_post_index', 'div'=> 'submitMultiple'));
echo $this->Form->submit(__('Submit Post Add Article'),array('id' => 'action_post_add', 'div'=> 'submitMultiple','class' => 'buttonBlu'));

echo $this->Form->end();

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#qta').focusout(function() {validateNumberField(this,'quantita\'');});
	jQuery('.double').focusout(function() {validateNumberField(this,'prezzo');});

	jQuery('#qta').focusout(function() {setArticlePrezzoUmRiferimento();});
	jQuery('#um').change(function() {setArticlePrezzoUmRiferimento();});
	jQuery('#prezzo').focusout(function() {setArticlePrezzoUmRiferimento();});

	jQuery('#action_post_index').click(function() {	
		jQuery('#action_post').val('context_order_index');
	});
	jQuery('#action_post_add').click(function() {	
		jQuery('#action_post').val('context_order_add');
	});
	
	jQuery('#formGas').submit(function() {
		if(!moduleCtrlArticle()) return false;
		else
			return true;
	});

	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});	
});
</script>