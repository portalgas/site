<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Management cost_less'));
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
	
	AjaxCallToCostLessImporto(delivery_id, order_id); 	/* chiamata Ajax con il cost_less */
	
	jQuery('.submit').css('display','none');
}
function choiceCostLessImporto() {
	var div_contenitore = 'cost-less-importo';

	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var cost_less = jQuery('#cost_less').val();

	if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
		alert("Devi indicare l'importo dello sconto");
		jQuery("input[name='cost-less-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceCostLessImporto - div_contenitore "+div_contenitore+", cost_lessOptions "+cost_lessOptions);
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCostLessOptions(delivery_id, order_id); 	// chiamata Ajax opzioni summary orders
}
function choiceCostLessOptions() {

	var div_contenitore = 'cost-less-options';
	
	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var cost_less = jQuery('#cost_less').val();
	var cost_lessOptions = jQuery("input[name='cost-less-options']:checked").val(); 

	if(cost_lessOptions=='' || cost_lessOptions==undefined) return;
	
	if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
		alert("Devi indicare l'importo dello sconto");
		jQuery("input[name='cost-less-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceCostLessOptions - div_contenitore "+div_contenitore+", cost_lessOptions "+cost_lessOptions);
	if(delivery_id == '' || order_id=='' || cost_lessOptions=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCostLessResult(delivery_id, order_id, cost_lessOptions); /* chiamata Ajax l'elenco degli SummaryOrders con il sconto calcolato */ 
}

/*
 *  chiamata Ajax per importo dello sconto
 */
function AjaxCallToCostLessImporto(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_cost_less_importo&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'cost-less-importo';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per opzioni CostLess
 */
function AjaxCallToCostLessOptions(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_cost_less_options&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'cost-less-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryOrders e il sconto calcolato
 */
function AjaxCallToCostLessResult(delivery_id, order_id, cost_lessOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_cost_less&delivery_id="+delivery_id+"&order_id="+order_id+"&cost_lessOptions="+cost_lessOptions+"&format=notmpl";
	var idDivTarget = 'cost-less-results';
	ajaxCallBox(url, idDivTarget);
	
	jQuery('.submit').css('display','block');
}
</script>



<h2 class="ico-cost-less">
	<?php echo __('Management cost_less');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create('SummaryOrderCostLess',array('id' => 'formGas'));
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>


	<?php 
	echo $this->element('boxOrder',array('results' => $results));
	?>	
	
	<div id="cost-less-importo" style="display:none;margin-top:5px;"></div>

	<div id="cost-less-options" style="display:none;margin-top:5px;"></div>

	<div id="cost-less-results" style="display:none;min-height:50px;"></div>
	
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
		var cost_less = jQuery('#cost_less').val();
	
		if(cost_less=='' || cost_less==null || cost_less=='0,00' || cost_less=='0.00' || cost_less=='0') {
			alert("Devi indicare l'importo dello sconto");
			jQuery("input[name='cost-less-options']").prop('checked',false);
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