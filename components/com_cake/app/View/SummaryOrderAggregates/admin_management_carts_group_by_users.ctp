<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Management Carts Group By Users'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var order_id = <?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;

function choiceOrderPermission() {
	var div_contenitore = 'order-permission';
	showHideBox(div_contenitore,call_child=true); 

	var order_id    = $('#order_id').val(); /* estraggo info supplier_id */
	
	AjaxCallToSummaryOrderAggregatesOptions(order_id); 	/* chiamata Ajax opzioni summary orders */
}
function choiceSummaryOrderAggregatesOptions() {

	var div_contenitore = 'summary-order-aggregates-options';
	
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */

	var summaryOrderAggregatesOptions = '';
	if($("input[name='summary-order-aggregates-options']").length>0)
		summaryOrderAggregatesOptions = $("input[name='summary-order-aggregates-options']:checked").val(); 

	if(summaryOrderAggregatesOptions=='options-delete-yes') {
		if(!confirm("Sei sicuro di voler rigenerare i dati cancellando quelli sottostanti?")) {
			$("#options-summary_order-aggregates-delete-no").prop('checked',true);
			return;
		}
	}
	
	if(debugLocal) alert("choiceSummaryOrderAggregatesOptions - div_contenitore "+div_contenitore+", summaryOrderAggregatesOptions "+summaryOrderAggregatesOptions);
	if(order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToSummaryOrderAggregatesResult(order_id, summaryOrderAggregatesOptions); /* chiamata Ajax l'elenco degli SummaryOrderAggregates */ 
}
function AjaxCallToSummaryOrderAggregatesOptions(order_id) {
	url = "/administrator/index.php?option=com_cake&controller=SummaryOrderAggregates&action=box_summary_order_aggregates_options&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'summary-order-aggregates-options';
	ajaxCallBox(url, idDivTarget);
}
function AjaxCallToSummaryOrderAggregatesResult(order_id, summaryOrderAggregatesOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=SummaryOrderAggregates&action=box_summary_order_aggregates&order_id_selected="+order_id+"&summaryOrderAggregatesOptions="+summaryOrderAggregatesOptions+"&format=notmpl";
	var idDivTarget = 'doc-results';
	ajaxCallBox(url, idDivTarget);
}
</script>

<?php
echo '<h2 class="ico-management-carts-group-by-users">';
echo __('Management Carts Group By Users');
echo '<div class="actions-img">';
echo '<ul>';
echo '<li>';
echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home')));
echo '</li>';
echo '</ul>';
echo '</div>';
echo '</h2>';

echo '<div class="contentMenuLaterale">';

echo $this->Form->create();
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));

echo '<fieldset>';

echo $this->element('boxOrder',array('results' => $results));

echo '<div id="summary-order-aggregates-options" style="display:none;margin-top:5px;clear: both;"></div>';

echo '<div id="doc-results" style="display:none;clear: both;"></div>';

echo '</fieldset>';
echo '</div>';

$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);

/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>
<script type="text/javascript">
$(document).ready(function() {
	
	<?php if(!empty($alertModuleConflicts)) {
		if(!$popUpDisabled)
			echo "apriPopUpBootstrap('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=".$alertModuleConflicts."&orderHasTrasport=".$orderHasTrasport."&orderHasCostMore=".$orderHasCostMore."&orderHasCostLess=".$orderHasCostLess."&format=notmpl', '')";
	}
	?>
});
</script>