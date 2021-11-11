<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdDeliveries'),array('controller' => 'ProdDeliveries', 'action' => 'index'));
if(isset($prod_delivery_id) && !empty($prod_delivery_id))
	$this->Html->addCrumb(__('ProdDelivery home'),array('controller'=>'ProdDeliveries','action'=>'home', null, 'prod_delivery_id='.$prod_delivery_id));
$this->Html->addCrumb(__('Export Docs to delivery'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var prod_delivery_id = <?php echo $prod_delivery_id; // se arrivo da ProdDeliveries/admin_index.ctp e' valorizzato ?>;
var call_action = '<?php echo $this->action; // in base alla pagina chiamante, setto il MSG in admin_box_permission?>';

function choiceProdDeliveryPermission() {
	var div_contenitore = 'prod_delivery-permission';
	showHideBox(div_contenitore,call_child=true); 
	
	AjaxCallToDocOptions();  /* chiamata Ajax per optioni formato documento (csv, pdf) */
}
function choiceDocOptions() {
	var div_contenitore = 'doc-options';
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToDocPrint(); /* chiamata Ajax per tasto print */	
}	
function choiceDocPrint() {
	var div_contenitore = 'doc-print';
	
	showHideBox(div_contenitore,call_child=true);
	AjaxCallToDocPreview(); /* chiamata Ajax per anteprima documento */	
}



/*
 * chiamata Ajax il formato del doc (csv, pdf)
 */
function AjaxCallToDocOptions() {	
	var url = "/administrator/index.php?option=com_cake&controller=AjaxProdCodes&action=box_doc_options&format=notmpl";
	var idDivTarget = 'doc-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per tasto print
 */
function AjaxCallToDocPrint() {
	var doc_options = $("input[name='doc_options']:checked").val();
	
	var url = "/administrator/index.php?option=com_cake&controller=AjaxProdCodes&action=box_doc_print&doc_options="+doc_options+"&format=notmpl";
	var idDivTarget = 'doc-print';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per anteprima documento
 */
function AjaxCallToDocPreview() {
	var prod_delivery_id    = $('#prod_delivery_id').val(); 
	var doc_options = $("input[name='doc_options']:checked").val();
	
	if(prod_delivery_id=='' || doc_options=='') return;
	
	/*
	 * setting, uguale a 
	 *              Ajax::admin_view_tesoriere_export_docs.ctp 	 
	 *				AjaxGasCode::admin_box_doc_print_referente.ctp, 
	 *				Doc::admin_referent_docs_export.ctp, 
	 *				Doc::admin_cassiere_docs_export.ctp, 
	 *              Pages:admin_home.ctp
	 */
	var a = '';
	var b = '';
	var c = '';
	var d = '';
	var e = '';
	var f = '';
	if(doc_options=='to-users-all-modify') {
		if($("input[name='trasport1']").length > 0) 
			a = $("input[name='trasport1']:checked").val();
	}
	else	
	if(doc_options=='to-users') {
		a = $("input[name='user_phone1']:checked").val();
		b = $("input[name='user_email1']:checked").val();
		c = $("input[name='user_address1']:checked").val();
		d = $("input[name='totale_per_utente']:checked").val();
		if($("input[name='trasport2']").length > 0)
			e = $("input[name='trasport2']:checked").val();
		else
			e = 'N';
		f = $("input[name='user_avatar1']:checked").val();
	}
	else
	if(doc_options=='to-users-label') {
		a = $("input[name='user_phone']:checked").val();
		b = $("input[name='user_email']:checked").val();
		c = $("input[name='user_address']:checked").val();
		if($("input[name='trasport3']").length > 0)
			d = $("input[name='trasport3']:checked").val();
		else
			d = 'N';
		e = $("input[name='user_avatar2']:checked").val();
	}
	else
	if(doc_options=='to-articles') {
		if($("input[name='trasport4']").length > 0)
			a = $("input[name='trasport4']:checked").val();
		else
			a = 'N';		
	}
	else
	if(doc_options=='to-articles-details') {
		a = $("input[name='acquistato_il']:checked").val();
		if($("input[name='article_img']").length > 0)
			b = $("input[name='article_img']:checked").val();
		else
			b = 'N';		
		if($("input[name='trasport5']").length > 0)
			c = $("input[name='trasport5']:checked").val();
		else
			c = 'N';		
	}			
	
	var url = '/administrator/index.php?option=com_cake&controller=ProdExportDocs&action=exportToProduttore&prod_delivery_id='+prod_delivery_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&a='+a+'&b='+b+'&c='+c+'&d='+d+'&e='+e+'&f='+f+'&format=notmpl';
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);	
}
</script>
		
<h2 class="ico-export-docs">
	<?php echo __('Export Docs to delivery');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('ProdDelivery home'), array('controller' => 'ProdDeliveries', 'action' => 'home', null, 'prod_delivery_id='.$prod_delivery_id),array('class' => 'action actionWorkflow','title' => __('ProdDelivery home'))); ?></li>
		</ul>
	</div>
</h2>


<div class="docs">
<?php echo $this->Form->create();?>
	<fieldset>
	
	<?php 
	echo $this->element('boxProdDelivery',array('results' => $results));
	?>	
		
	<div id="doc-options" style="display:none;"></div>

	<div id="doc-print" style="display:none;"></div>

	<div id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>
	   	
<?php 
echo $this->element('menuProdDeliveryLaterale');
?>