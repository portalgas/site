<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Export Docs to order'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var order_id = <?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var call_action = '<?php echo $this->action; // in base alla pagina chiamante, setto il MSG in admin_box_permission?>';

function choiceOrderPermission() {
	var div_contenitore = 'order-permission';
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
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_doc_options_referente&format=notmpl";
	var idDivTarget = 'doc-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per tasto print
 */
function AjaxCallToDocPrint() {
	var doc_options = $("input[name='doc_options']:checked").val();
	
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_doc_print_referente&doc_options="+doc_options+"&format=notmpl";
	var idDivTarget = 'doc-print';
	ajaxCallBox(url, idDivTarget);
}
/*
 * chiamata Ajax per anteprima documento
 */
function AjaxCallToDocPreview() {
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); 
	var doc_options = $("input[name='doc_options']:checked").val();

	if(delivery_id =='' || order_id=='' || doc_options=='') return;
	
	var parametersFilter = setExportDocsParameters(doc_options);	

	if(doc_options=='to-articles-weight')
		var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToArticlesWeight&delivery_id='+delivery_id+'&order_id='+order_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&format=notmpl';
	else		
		var url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action=exportToReferent&delivery_id='+delivery_id+'&order_id='+order_id+'&doc_options='+doc_options+'&doc_formato=PREVIEW&'+parametersFilter+'&format=notmpl';
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);	
}
</script>
		
<h2 class="ico-export-docs">
	<?php echo __('Export Docs to order');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<?php 
echo '<div class="contentMenuLaterale">';
echo $this->Form->create();
echo '<fieldset>';
	
echo $this->element('boxOrder',array('results' => $results));
	
echo '<div class="clearfix"></div>';
echo '<div id="doc-options" style="display:none;"></div>';

echo '<div class="clearfix"></div>';
echo '<div id="doc-print" style="display:none;"></div>';

echo '<div class="clearfix"></div>';
echo '<div id="doc-preview" style="display:none;"></div>';
	
echo '</fieldset>';
echo '</div>';
	   	
$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);
?>