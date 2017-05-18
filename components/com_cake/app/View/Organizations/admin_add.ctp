<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Organizations'), array('controller' => 'Organizations', 'action' => 'index'));
$this->Html->addCrumb(__('Add Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="organizations form">
<?php echo $this->Form->create('Organization');?>
	<fieldset>
		<legend><?php echo __('Add Organization'); ?></legend>
		
         <div class="tabs">
             <ul>
                 <li><a href="#tabs-0"><span><?php echo __('Dati anagrafici'); ?></span></a></li>
                 <li><a href="#tabs-1"><span><?php echo __('Contatti'); ?></span></a></li>
                 <li><a href="#tabs-2"><span><?php echo __('Dati fatturazione'); ?></span></a></li>
                 <li><a href="#tabs-3"><span><?php echo __('Pay'); ?></span></a></li>
                 <li><a href="#tabs-4"><span><?php echo __('Joomla'); ?></span></a></li>
				 <li><a href="#tabs-5"><span><?php echo __('Configurazione'); ?></span></a></li>
                 <li><a href="#tabs-6"><span><?php echo __('Configurazione Ruoli'); ?></span></a></li>
                 <li><a href="#tabs-7"><span><?php echo __('Configurazione campi'); ?></span></a></li>
             </ul>
             <div id="tabs-0">		
				<?php
					echo $this->App->drawFormRadio('Organization', 'type', array('options' => $type, 'value' => 'GAS', 'label'=>__('Type'), 'required'=>'required'));
					echo $this->Form->input('template_id', array('type' => 'text', 'style' => 'width:50px;',  'label'=>__('Template'), 'required'=>'required'));
					echo $this->Form->input('name', array('label' => 'Name', 'required'=>'required'));
					echo $this->Form->input('descrizione',array('label' => __('Description')));
					echo $this->Form->input('indirizzo');
					echo $this->Form->input('localita');
					echo $this->Form->input('cap',array('style' => 'width:75px;'));
					echo $this->Form->input('provincia', array('style' => 'width:50px;'));
					echo $this->Form->input('lat');
					echo $this->Form->input('lng');
				?><p><a href="http://maps.google.com/maps/api/geocode/json?sensor=false&address=" target="_blank">geocode</a></p>
			</div>
			<div id="tabs-1">
				<?php				 
					echo $this->Form->input('telefono');
					echo $this->Form->input('telefono2');
					echo $this->Form->input('mail', array('required'=>'required'));
					echo $this->Form->input('www', array('label' => 'http://xxx.portalgas.it', 'required'=>'required'));
					echo $this->Form->input('www2', array('label' => 'Www'));
					echo $this->Form->input('sede_logistica_1');
					echo $this->Form->input('sede_logistica_2');
					echo $this->Form->input('sede_logistica_3');
					echo $this->Form->input('sede_logistica_4');
				?>
			</div>
			<div id="tabs-2">
				<?php
					echo $this->Form->input('cf');
					echo $this->Form->input('piva');
					echo $this->Form->input('banca');				
					echo $this->Form->input('banca_iban');
				?>
			</div>
			<div id="tabs-3">
				<?php
					echo $this->Form->input('payMail');
					echo $this->Form->input('payContatto');
					echo $this->Form->input('payIntestatario');
					echo $this->Form->input('payIndirizzo');
					echo $this->Form->input('payCap');
					echo $this->Form->input('payCitta');
					echo $this->Form->input('payProv');
					echo $this->Form->input('payCf');
					echo $this->Form->input('payPiva');
				?>		
			</div>
			<div id="tabs-4">
				<?php
					echo $this->Form->input('j_group_registred',array('label' => __('joomla_group_registred'), 'type' => 'text', 'size' => '5', 'class' => 'noWidth', 'required'=>'required', 'after'=>$this->App->drawTooltip(null,__('toolJoomlaGroupRegistred'),$type='HELP')));
					echo $this->Form->input('j_seo',array('label' => __('joomla_seo'),'value' => 'gas-','required' => 'required', 'after' => $this->App->drawTooltip(null,__('toolJoomlaSeo'),$type='HELP')));
				?>
			</div>				
			<div id="tabs-5">
				<?php		
					echo '<div class="typeGAS">';
					
					echo $this->App->drawFormRadio('Organization','hasBookmarsArticles',array('options' => $hasBookmarsArticles, 'value'=>'N', 'label'=>__('HasBookmarsArticles'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasArticlesOrder',array('options' => $hasArticlesOrder, 'value'=>'Y', 'label'=>__('HasArticlesOrder'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasVisibility',array('options' => $hasVisibility, 'value'=>'N', 'label'=>__('HasVisibility'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasTrasport',array('options' => $hasTrasport, 'value'=>'Y', 'label'=>__('HasTrasport'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasCostMore',array('options' => $hasCostMore, 'value'=>'Y', 'label'=>__('HasCostMore'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasCostLess',array('options' => $hasCostLess, 'value'=>'Y', 'label'=>__('HasCostLess'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasValidate',array('options' => $hasValidate, 'value'=>'Y', 'label'=>__('HasValidate'), 'required'=>'required',
																				'after'=>$this->App->drawTooltip(null,"Se è abilitata la dispensa si potranno mettere gli articoli eccedenti in dispensa se no si dovranno sottrarre agli utenti",$type='WARNING')));
					echo $this->App->drawFormRadio('Organization','hasStoreroom',array('options' => $hasStoreroom, 'value'=>'Y', 'label'=>__('HasStoreroom'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasStoreroomFrontEnd',array('options' => $hasStoreroomFrontEnd, 'value'=>'N', 'label'=>__('HasStoreroomFrontEnd'), 'required'=>'required',
																				'after'=>$this->App->drawTooltip(null,__('toolTipHasStoreroomFrontEnd'),$type='HELP')));
					echo $this->App->drawFormRadio('Organization','payToDelivery',array('options' => $payToDelivery, 'value' => $this->request->data['Organization']['payToDelivery'], 'label'=>__('payToDelivery'), 'required'=>'required'));
					
					echo '<h3>D.E.S.</h3>';
					echo $this->App->drawFormRadio('Organization','hasDes',array('options' => $hasDes, 'value'=>'N', 'label'=>__('HasDes'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasDesReferentAllGas',array('options' => $hasDesReferentAllGas, 'value'=>'N', 'label'=>__('HasDesReferentAllGas'), 'required'=>'required'));
					
					echo $this->App->drawFormRadio('Organization','stato',array('options' => $stato, 'value'=>'Y', 'label'=>__('Stato'), 'required'=>'required',
																				'after'=>$this->App->drawTooltip(null,__('toolTipStato'),$type='HELP')));
				
					echo '</div>';
					
					echo '<div class="typePROD">';
					echo $this->Form->input('prodSupplierOrganizationId', array('style' => 'width:50px;', 
																				'after'=>$this->App->drawTooltip(null,__('toolTipProdSupplierOrganizationId'),$type='HELP')));
					echo '</div>';				
				?>
			</div>
			<div id="tabs-6">									
				<?php 
					echo $this->element('boxOrganizationUserGroups', array('value' => $this->request->data));
				?>
			</div>			
			<div id="tabs-7">
				<?php			
					echo $this->App->drawFormRadio('Organization','hasFieldArticleCodice',array('options' => $hasFieldArticleCodice, 'value'=>'Y', 'label'=>__('HasFieldArticleCodice'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasFieldArticleIngredienti',array('options' => $hasFieldArticleIngredienti, 'value'=>'Y', 'label'=>__('HasFieldArticleIngredienti'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasFieldArticleAlertToQta',array('options' => $hasFieldArticleAlertToQta, 'value'=>'N', 'label'=>__('HasFieldArticleAlertToQta'), 'required'=>'required'));
                    echo $this->App->drawFormRadio('Organization','hasFieldPaymentPos',array('options' => $hasFieldPaymentPos, 'value'=>'N', 'label'=>__('HasFieldPaymentPos'), 'required'=>'required'));
                    echo $this->Form->input('paymentPos', array('size' => 10, 'class' => 'noWidth', 'after' => '&euro;'));
					echo $this->App->drawFormRadio('Organization','hasFieldArticleCategoryId',array('options' => $hasFieldArticleCategoryId, 'value'=>'Y', 'label'=>__('HasFieldArticleCategoryId'), 'required'=>'required'));
					echo $this->App->drawFormRadio('Organization','hasFieldSupplierCategoryId',array('options' => $hasFieldSupplierCategoryId, 'value'=>'Y', 'label'=>__('HasFieldSupplierCategoryId'), 'required'=>'required',
								'after'=>$this->App->drawTooltip(null,"Deve sempre essere abilitato, nel front-end c'è il menù con l'elenco delle categorie dei produttori",$type='WARNING')));
					echo $this->App->drawFormRadio('Organization','hasFieldFatturaRequired',array('options' => $hasFieldFatturaRequired, 'value'=>'N', 'label'=>__('HasFieldFatturaRequired'), 'required'=>'required'));					
				?>
			</div>
		</div>
	</fieldset>
<?php 
echo $this->Form->end(__('Submit'));

echo $this->element('legendaTemplate');
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Organizations'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(function() {
		jQuery( ".tabs" ).tabs({
			event: "click"
		});
	});

	choiceType();
	choicePayToDelivery();
	
	jQuery("input[name='data[Organization][type]']").change(function() {	
		choiceType();
		choicePayToDelivery();
	});	

	jQuery("input[name='data[Organization][payToDelivery]']").change(function() {	
		choicePayToDelivery();
	});
});

function choiceType() {	
	var type = jQuery("input[name='data[Organization][type]']:checked").val();

	if(type=='GAS') {
		jQuery('.typeGAS').show();
		jQuery('.typePROD').hide();
		
		jQuery('#tr_group_id_manager_delivery').show();
		jQuery('#tr_group_id_referent').show();
		jQuery('#tr_group_id_super_referent').show();
		jQuery('#tr_group_id_cassiere').show();
		jQuery('#tr_group_id_referent_tesoriere').show();
		jQuery('#tr_group_id_tesoriere').show();
		jQuery('#tr_group_id_storeroom').show();
	}
	else		
	if(type=='PROD') {
		jQuery('.typeGAS').hide();
		jQuery('.typePROD').show();
		
		jQuery('#tr_group_id_manager_delivery').hide();
		jQuery('#tr_group_id_referent').hide();
		jQuery('#tr_group_id_super_referent').hide();
		jQuery('#tr_group_id_cassiere').hide();
		jQuery('#tr_group_id_referent_tesoriere').hide();
		jQuery('#tr_group_id_tesoriere').show();
		jQuery('#tr_group_id_storeroom').hide();
	}
}
function choicePayToDelivery() {	
	var payToDelivery = jQuery("input[name='data[Organization][payToDelivery]']:checked").val();

	if(payToDelivery=='BEFORE') {
		alert("<?php echo Configure::read('sys_function_not_implement');?>");
	}
	else		
	if(payToDelivery=='ON') {
		/*
		 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
		 * 		gestisce anche il pagamento del suo produttore
		 */
		jQuery('#tr_group_id_referent_tesoriere').hide();
	}
	else		
	if(payToDelivery=='POST') {
		jQuery('#tr_group_id_referent_tesoriere').show();
		jQuery('#tr_group_id_cassiere').show();
	}
	else		
	if(payToDelivery=='ON-POST') {
		jQuery('#tr_group_id_referent_tesoriere').show();
		jQuery('#tr_group_id_cassiere').show();
	}	
}	
</script>