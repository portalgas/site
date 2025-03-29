<style type="text/css">
.cakeContainer h3 {
    border-bottom: 1px solid #0a659e;
    font-weight: bold;
}		
</style>
<?php
echo $this->Html->script('organizations');

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Organizations'), array('controller' => 'Organizations', 'action' => 'index'));
$this->Html->addCrumb(__('Add Organization'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="organizations form">';
echo $this->Form->create('Organization');
echo $this->Form->input('type', ['type'=> 'hidden']);
echo '<fieldset>';
echo '<legend>'.__('Add Organization').'</legend>';
		
echo '<div class="tabs">';
echo '<ul class="nav nav-tabs">'; // nav-tabs nav-pills
echo '<li class="active"><a href="#tabs-0" data-toggle="tab">'.__('User profile').'</a></li>';
echo '<li><a href="#tabs-1" data-toggle="tab">'.__('Contatti').'</a></li>';
echo '<li><a href="#tabs-2" data-toggle="tab">'.__('Dati fatturazione').'</a></li>';
echo '<li><a href="#tabs-3" data-toggle="tab">'.__('Pay').'</a></li>';			
echo '<li><a href="#tabs-4" data-toggle="tab">'.__('Joomla').'</a></li>';			
echo '<li><a href="#tabs-5" data-toggle="tab">'.__('Configurazione').'</a></li>';			
echo '<li><a href="#tabs-6" data-toggle="tab">'.__('Configurazione Ruoli').'</a></li>';			
echo '<li><a href="#tabs-7" data-toggle="tab">'.__('Configurazione campi').'</a></li>';			
echo '</ul>';

echo '<div class="tab-content">';
echo '<div class="tab-pane fade active in" id="tabs-0">';

	echo $this->App->drawFormRadio('Organization', 'type', array('options' => $type, 'value' => 'GAS', 'label'=>__('Type'), 'required'=>'required'));
	echo $this->Form->input('template_id', array('label'=>__('Template'), 'required'=>'required'));
	echo $this->Form->input('name', array('label' => 'Name', 'required'=>'required'));
	echo $this->Form->input('descrizione',array('label' => __('Description')));
	echo $this->Form->input('indirizzo');
	echo $this->Form->input('localita');
	echo $this->Form->input('cap',array('style' => 'width:100px;'));
	echo $this->Form->input('provincia', array('style' => 'width:75px;'));
	echo $this->Form->input('lat');
	echo $this->Form->input('lng');
	echo '<p><a href="'.Configure::read('UrlApiGpsCoordinate').'" target="_blank">geocode</a></p>';

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-1">';
				 
	echo $this->Form->input('telefono');
	echo $this->Form->input('telefono2');
	echo $this->Form->input('mail', array('required'=>'required'));
	echo $this->Form->input('www', array('label' => 'http://xxx.portalgas.it', 'value' => 'http://xxx.portalgas.it', 'required'=>'required'));
	echo '<span class="label label-info" title="https non è gestisto dal certificato">https non è gestisto dal certificato</span>';
	echo $this->Form->input('www2', array('label' => 'Www'));
	echo $this->Form->input('sede_logistica_1');
	echo $this->Form->input('sede_logistica_2');
	echo $this->Form->input('sede_logistica_3');
	echo $this->Form->input('sede_logistica_4');

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-2">';

	echo $this->Form->input('cf');
	echo $this->Form->input('piva');
	echo $this->Form->input('banca');				
	echo $this->Form->input('banca_iban');

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-3">';

echo $this->Form->input('payContatto', array('id' => 'payContatto', 'label' => __('payContatto')));
echo $this->Form->input('payMail', array('id' => 'payMail', 'label' => __('payMail')));
echo $this->Form->input('payIntestatario', array('id' => 'payIntestatario', 'label' => __('payIntestatario')));
echo $this->Form->input('payIndirizzo', array('id' => 'payIndirizzo', 'label' => __('payIndirizzo')));
echo $this->Form->input('payCap', array('id' => 'payCap', 'label' => __('payCap')));
echo $this->Form->input('payCitta', array('id' => 'payCitta', 'label' => __('payCitta')));
echo $this->Form->input('payProv', array('id' => 'payProv', 'label' => __('payProv')));
echo $this->Form->input('payCf', array('id' => 'payCf', 'label' => __('payCf')));
echo $this->Form->input('payPiva', array('id' => 'payPiva', 'label' => __('payPiva')));
echo $this->Form->input('payType', array('id' => 'payType', 'label' => __('payType'), 'options' => ['RICEVUTA' => 'RICEVUTA', 'RITENUTA' => "RITENUTA D'ACCONTO"]));

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-4">';
	
	echo $this->Form->input('j_group_registred',array('label' => __('joomla_group_registred'), 'type' => 'text', 'required'=>'required', 'after'=>$this->App->drawTooltip(null,__('toolJoomlaGroupRegistred'),$type='HELP')));
	
	echo $this->Form->input('j_page_category_id',array('label' => __('joomla_page_category_id'), 'type' => 'text', 'required'=>'required', 'after'=>$this->App->drawTooltip(null,__('toolJoomlaCategory'),$type='HELP')));
		
	/*
	 * prefix joomla seo
	 */
	echo $this->Form->input('j_seo', ['label' => __('joomla_seo'),'value' => 'gas-','required' => 'required', 'after' => $this->App->drawTooltip(null,__('toolJoomlaSeo'),$type='HELP')]);	
	echo $this->element('legendaOrganizationjoomlaSeo');

	echo $this->Form->input('img1',array('label' => __('img1'),'required' => 'required'));
	echo '<p>id della page home</p>';
	echo '<p>'.Configure::read('App.img.upload.content').'/N.jpg</p>';
	echo '<p><img src="'.Configure::read('App.img.upload.content').'/0.jpg"></p>';

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-5">';
		
	echo '<div class="typeGAS">';
	
	echo '<h3>'.__('Articles').'</h3>';
	echo $this->App->drawFormRadio('Organization','hasBookmarsArticles',array('options' => $hasBookmarsArticles, 'value'=>'N', 'label'=>__('HasBookmarsArticles'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasArticlesOrder',array('options' => $hasArticlesOrder, 'value'=>'Y', 'label'=>__('HasArticlesOrder'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasValidate',array('options' => $hasValidate, 'value'=>'Y', 'label'=>__('HasValidate'), 'required'=>'required',
	      'after'=>$this->App->drawTooltip(null,"Se è abilitata la dispensa si potranno mettere gli articoli eccedenti in dispensa se no si dovranno sottrarre agli utenti",$type='WARNING')));


	echo '<h3>'.__('Orders').'</h3>';
	echo $this->App->drawFormRadio('Organization','hasVisibility',array('options' => $hasVisibility, 'value'=>'N', 'label'=>__('HasVisibility'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasTrasport',array('options' => $hasTrasport, 'value'=>'Y', 'label'=>__('HasTrasport'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasCostMore',array('options' => $hasCostMore, 'value'=>'Y', 'label'=>__('HasCostMore'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasCostLess',array('options' => $hasCostLess, 'value'=>'Y', 'label'=>__('HasCostLess'), 'required'=>'required'));


	echo '<h3>'.__('Storeroom').'</h3>';
	echo $this->App->drawFormRadio('Organization','hasStoreroom',array('options' => $hasStoreroom, 'value'=>'Y', 'label'=>__('HasStoreroom'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasStoreroomFrontEnd',array('options' => $hasStoreroomFrontEnd, 'value'=>'N', 'label'=>__('HasStoreroomFrontEnd'), 'required'=>'required',
		'after'=>$this->App->drawTooltip(null,__('toolTipGgArchiveStatics'),$type='HELP')));
	/*
	echo $this->App->drawFormRadio('Organization','payToDelivery',array('options' => $payToDelivery, 'value' => $this->request->data['Template']['payToDelivery'], 'label'=>__('PayToDelivery'), 'required'=>'required'));
	
	echo $this->App->drawFormRadio('Organization','orderLifeCycleEnd',array('options' => $orderLifeCycleEnd, 'value'=> $this->request->data['Organization']['orderLifeCycleEnd'], 'label'=>__('OrderLifeCycleEnd'), 'required'=>'required'));
	*/
	
	echo '<h3>'.__('OrganizationsCash').'</h3>';					
	echo $this->App->drawFormRadio('Organization','cashLimit',array('options' => $cashLimit, 'value'=> $this->request->data['Organization']['cashLimit'], 'label'=>__('CashLimit'), 'required'=>'required'));
	echo $this->Form->input('limitCashAfter', ['label' => __('LimitCashAfter'),'value' => $this->request->data['Organization']['limitCashAfter'],'required' => 'required', 'after' => $this->App->drawTooltip(null,__('tooLimitCashAfter'),$type='HELP')]);
	echo $this->App->drawFormRadio('Organization','hasCashFilterSupplier',array('options' => $hasCashFilterSupplier, 'value'=>'N', 'label'=>__('HasCashFilterSupplier'), 'required'=>'required'));


	echo '<h3>'.__('OrderLifeCycle').'</h3>';						
	echo $this->App->drawFormRadio('Organization','canOrdersClose', ['options' => $canOrdersClose, 'value'=> $this->request->data['Organization']['canOrdersClose'], 'label'=>__('CanOrdersClose'), 'required'=>'required']);
	echo $this->App->drawFormRadio('Organization','canOrdersDelete', ['options' => $canOrdersDelete, 'value'=> $this->request->data['Organization']['canOrdersDelete'], 'label'=>__('CanOrdersDelete'), 'required'=>'required']);
	echo $this->Form->input('ggArchiveStatics', ['value' => $this->request->data['Organization']['ggArchiveStatics'], 'required'=>'required', 'after' => $this->App->drawTooltip(null,__('tooltipGgArchiveStatics'), $type='INFO')]);


	echo '<h3>'.__('Users').'</h3>';
	echo $this->App->drawFormRadio('Organization','hasUsersRegistrationFE',array('options' => $hasUsersRegistrationFE, 'value'=>'N', 'label'=>__('HasUsersRegistrationFE'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasUserFlagPrivacy', ['options' => $hasUserFlagPrivacy, 'value'=>'N', 'label'=>__('HasUserFlagPrivacy'), 'required'=>'required',
		'after' => $this->App->drawTooltip(null,__('toolTipUserFlagPrivacy'), $type='HELP')]);
	echo $this->App->drawFormRadio('Organization','hasUserRegistrationExpire', ['options' => $hasUserRegistrationExpire, 'value'=>'N', 'label'=>__('HasUserRegistrationExpire'), 'required'=>'required',
		'after' => $this->App->drawTooltip(null,__('toolTipUserRegistrationExpire'), $type='HELP')]);					
	echo $this->Form->input('userRegistrationExpireDate', ['label' => __('UserRegistrationExpireDate')]);
	
	
	echo '<h3>D.E.S.</h3>';
	echo $this->App->drawFormRadio('Organization','hasDes',array('options' => $hasDes, 'value'=>'N', 'label'=>__('HasDes'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasDesReferentAllGas',array('options' => $hasDesReferentAllGas, 'value'=>'N', 'label'=>__('HasDesReferentAllGas'), 'required'=>'required'));			
	echo $this->App->drawFormRadio('Organization','hasDesUserManager',array('options' => $hasDesUserManager, 'value'=>'N', 'label'=>__('HasDesUserManager'), 'required'=>'required'));
	
	echo '<h3>Gruppi dale G.A.S.</h3>';
	echo $this->App->drawFormRadio('Organization','hasGasGroups',array('options' => $hasGasGroups, 'value' => 'N', 'label'=>__('HasGasGroups'), 'required'=>'required'));

	echo '<h3>GDXP</h3>';
	echo $this->App->drawFormRadio('Organization','hasArticlesGdxp',array('options' => $hasArticlesGdxp, 'value'=>'N', 'label'=>__('HasArticlesGdxp'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasOrdersGdxp',array('options' => $hasOrdersGdxp, 'value'=>'N', 'label'=>__('HasOrdersGdxp'), 'required'=>'required'));

	echo '<h3>Cms</h3>';
    echo $this->App->drawFormRadio('Organization','hasCms', ['options' => $hasCms, 'value'=> 'N', 'label'=>__('HasCms'), 'required'=>'required']);
    echo $this->App->drawFormRadio('Organization','hasDocuments', ['options' => $hasDocuments, 'value'=> 'N', 'label'=>__('HasDocuments'), 'required'=>'required']);


	echo '<h3>Organization</h3>';
	echo $this->App->drawFormRadio('Organization','stato',array('options' => $stato, 'value'=>'Y', 'label'=>__('Stato'), 'required'=>'required',
				'after'=>$this->App->drawTooltip(null,__('toolTipStato'),$type='HELP')));

	echo '</div>'; // end class=typeGAS

	echo '<div class="typePROD">';
	echo $this->Form->input('prodSupplierOrganizationId', [
				'after'=> $this->App->drawTooltip(null,__('toolTipProdSupplierOrganizationId'),$type='HELP')]);
	echo '</div>';				

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-6">';

	echo $this->element('boxOrganizationUserGroups', ['value' => $this->request->data]);

echo '</div>';
echo '<div class="tab-pane fade" id="tabs-7">';
			
	echo $this->App->drawFormRadio('Organization','hasFieldArticleCodice',array('options' => $hasFieldArticleCodice, 'value'=>'Y', 'label'=>__('HasFieldArticleCodice'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasFieldArticleIngredienti',array('options' => $hasFieldArticleIngredienti, 'value'=>'Y', 'label'=>__('HasFieldArticleIngredienti'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasFieldArticleAlertToQta',array('options' => $hasFieldArticleAlertToQta, 'value'=>'N', 'label'=>__('HasFieldArticleAlertToQta'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasFieldPaymentPos',array('options' => $hasFieldPaymentPos, 'value'=>'N', 'label'=>__('HasFieldPaymentPos'), 'required'=>'required'));
	echo $this->Form->input('paymentPos', ['after' => '&nbsp;&euro;']);
	echo $this->App->drawFormRadio('Organization','hasFieldArticleCategoryId',array('options' => $hasFieldArticleCategoryId, 'value'=>'Y', 'label'=>__('HasFieldArticleCategoryId'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasFieldSupplierCategoryId',array('options' => $hasFieldSupplierCategoryId, 'value'=>'Y', 'label'=>__('HasFieldSupplierCategoryId'), 'required'=>'required',
				'after'=>$this->App->drawTooltip(null,"Deve sempre essere abilitato, nel front-end c'è il menù con l'elenco delle categorie dei produttori",$type='WARNING')));
	echo $this->App->drawFormRadio('Organization','hasFieldFatturaRequired',array('options' => $hasFieldFatturaRequired, 'value'=>'N', 'label'=>__('HasFieldFatturaRequired'), 'required'=>'required'));
	echo $this->App->drawFormRadio('Organization','hasFieldCartNote', ['options' => $hasFieldCartNote, 'value'=>'N', 'label'=>__('HasFieldCartNote'), 'required'=>'required']);
	
echo '</div>';
echo '</div>'; // tab-content
echo '</div>';
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('List Organizations'), array('action' => 'index'),array('class'=>'action actionReload'));?></li>
	</ul>
</div>