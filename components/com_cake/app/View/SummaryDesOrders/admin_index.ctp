<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List DesOrders'),array('controller' => 'DesOrders', 'action' => 'index'));
if(isset($des_order_id) && !empty($des_order_id))
	$this->Html->addCrumb(__('Order home DES'),array('controller'=>'DesOrdersOrganizations','action'=>'index', null, 'des_order_id='.$des_order_id));
$this->Html->addCrumb(__('Management Des Order Group By Gas'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var des_order_id = <?php echo $des_order_id; // se arrivo da DesOrders/admin_index.ctp e' valorizzato ?>;

function choiceSummaryDesOrdersOptions() {

	var div_contenitore = 'summary-des-orders-options';
	
	var des_order_id    = jQuery('#des_order_id').val();

	var summaryDesOrdersOptions = '';
	if(jQuery("input[name='summary_des_orders-options']").length>0)
		summaryDesOrdersOptions = jQuery("input[name='summary_des_orders-options']:checked").val(); 

	if(summaryDesOrdersOptions=='options-delete-yes') {
		if(!confirm("Sei sicuro di voler rigenerare i dati cancellando quelli sottostanti?")) {
			jQuery("#options-summary_des_orders-delete-no").prop('checked',true);
			return;
		}
	}
	
	if(debugLocal) alert("choiceSummaryDesOrdersOptions - div_contenitore "+div_contenitore+", summaryDesOrdersOptions "+summaryDesOrdersOptions);
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToSummaryDesOrdersResult(des_order_id, summaryDesOrdersOptions); /* chiamata Ajax l'elenco degli Summary Orders */ 
}
function choiceSummaryDesOrdersOptionsReadOnly() {

	var div_contenitore = 'summary-des-orders-options';
	
	var des_order_id    = jQuery('#des_order_id').val(); 

	if(debugLocal) alert("choiceSummaryDesOrdersOptionsReadOnly - div_contenitore "+div_contenitore);
	if(des_order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToSummaryDesOrdersResultReadOnly(des_order_id); /* chiamata Ajax l'elenco degli SummaryDesOrders solo in lettura */ 

}

/*
 *  chiamata Ajax per opzioni summary orders
 */
function AjaxCallToSummaryDesOrdersOptions(des_order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_des_orders_options&des_order_id="+des_order_id+"&format=notmpl";
	var idDivTarget = 'summary-des-orders-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryDesOrders
 */
function AjaxCallToSummaryDesOrdersResult(des_order_id, summaryDesOrdersOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_des_orders&des_order_id_selected="+des_order_id+"&summaryDesOrdersOptions="+summaryDesOrdersOptions+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryDesOrders solo in lettura
 */
function AjaxCallToSummaryDesOrdersResultReadOnly(des_order_id) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_des_orders_read_only&des_order_id="+des_order_id+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
</script>



<h2 class="ico-management-carts-group-by-users">
	<?php echo __('Management Des Order Group By Gas');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home DES'), array('controller' => 'DesOrdersOrganizations', 'action' => 'index', null, 'des_order_id='.$des_order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="carts">
<?php echo $this->Form->create();?>
	<fieldset>

	<?php 
	echo $this->element('boxDesOrder',array('results' => $results));
	?>	
	
	<div id="summary-des-orders-options" style="display:block;margin-top:5px;"></div>

	<div id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>

<?php 
echo $this->Form->hidden('des_order_id',array('id' => 'des_order_id','value' => $des_order_id));
echo $this->Form->end();

echo $this->element('menuDesOrderLaterale');
?>
<script type="text/javascript">
jQuery(document).ready(function() {
	<?php if(!empty($alertModuleConflicts)) {
		if(!$popUpDisabled)
			echo "apriPopUp('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=".$alertModuleConflicts."&orderHasTrasport=".$orderHasTrasport."&orderHasCostMore=".$orderHasCostMore."&orderHasCostLess=".$orderHasCostLess."&format=notmpl')";
	}
	?>
	
	var des_order_id = jQuery("#des_order_id").val();
	if(des_order_id>0)	AjaxCallToSummaryDesOrdersOptions(des_order_id);
});
</script>
<style type="text/css">
.cakeContainer label {
    width: 100px !important;
}
</style>