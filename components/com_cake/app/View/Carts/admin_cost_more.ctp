<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Management cost_more'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var order_id = <?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;

function choiceOrderPermission() {
	var div_contenitore = 'order-permission';
	showHideBox(div_contenitore,call_child=true); 

	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	
	AjaxCallToCostMoreImporto(delivery_id, order_id); 	/* chiamata Ajax con il cost_more */
	
	jQuery('.submit').css('display','none');
}
function choiceCostMoreImporto() {
	var div_contenitore = 'cost-more-importo';

	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var cost_more = jQuery('#cost_more').val();

	if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
		alert("Devi indicare l'importo del costo aggiuntivo");
		jQuery("input[name='cost-more-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceCostMoreImporto - div_contenitore "+div_contenitore+", cost_moreOptions "+cost_moreOptions);
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCostMoreOptions(delivery_id, order_id); 	// chiamata Ajax opzioni summary orders
}
function choiceCostMoreOptions() {

	var div_contenitore = 'cost-more-options';
	
	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var cost_more = jQuery('#cost_more').val();
	var cost_moreOptions = jQuery("input[name='cost-more-options']:checked").val(); 

	if(cost_moreOptions=='' || cost_moreOptions==undefined) return;
	
	if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
		alert("Devi indicare l'importo del costo aggiuntivo");
		jQuery("input[name='cost-more-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceCostMoreOptions - div_contenitore "+div_contenitore+", cost_moreOptions "+cost_moreOptions);
	if(delivery_id == '' || order_id=='' || cost_moreOptions=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCostMoreResult(delivery_id, order_id, cost_moreOptions); /* chiamata Ajax l'elenco degli SummaryOrders con il costo aggiuntivo calcolato */ 
}

/*
 *  chiamata Ajax per importo del costo aggiuntivo
 */
function AjaxCallToCostMoreImporto(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_cost_more_importo&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'cost-more-importo';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per opzioni CostMore
 */
function AjaxCallToCostMoreOptions(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_cost_more_options&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'cost-more-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryOrders e il costo aggiuntivo calcolato
 */
function AjaxCallToCostMoreResult(delivery_id, order_id, cost_moreOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_cost_more&delivery_id="+delivery_id+"&order_id="+order_id+"&cost_moreOptions="+cost_moreOptions+"&format=notmpl";
	var idDivTarget = 'cost-more-results';
	ajaxCallBox(url, idDivTarget);
	
	jQuery('.submit').css('display','block');
}
</script>



<h2 class="ico-cost-more">
	<?php echo __('Management cost_more');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create('SummaryOrderCostMore',array('id' => 'formGas'));
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>


	<?php 
	echo $this->element('boxOrder',array('results' => $results));
	?>	
	
	<div id="cost-more-importo" style="display:none;margin-top:5px;"></div>

	<div id="cost-more-options" style="display:none;margin-top:5px;"></div>

	<div id="cost-more-results" style="display:none;min-height:50px;"></div>
	
	<div class="submit" style="float:right;">
		<div class="submit"><input id="sumbitElabora" type="submit" value="<?php echo __('Submit');?>"></div>
	</div>
	
	</fieldset>
	<?php 
		/*
		 * gestisce i valori submitImportoInsert, submitImportoUpdate, submitImportoDelete, submitElabora
		 */
		echo $this->Form->hidden('actionSubmit', array('id' => 'actionSubmit', 'value' => ''));
		echo $this->Form->end();
	
	echo '</div>';
	
$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);	
?>
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.submit').css('display','none');
	
	jQuery('#sumbitElabora').click(function() {

		var delivery_id = jQuery('#delivery_id').val();
		var order_id = jQuery('#order_id').val();
		var cost_more = jQuery('#cost_more').val();
	
		if(cost_more=='' || cost_more==null || cost_more=='0,00' || cost_more=='0.00' || cost_more=='0') {
			alert("Devi indicare l'importo del costo aggiuntivo");
			jQuery("input[name='cost-more-options']").prop('checked',false);
			return false;
		}
		
		jQuery('#actionSubmit').val('submitElabora');

		return true;

	});
})
</script>
<style type="text/css">
.cakeContainer label {
    width: 100px !important;
}
</style>