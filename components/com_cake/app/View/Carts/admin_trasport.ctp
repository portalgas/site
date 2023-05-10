<?php
if(isset($order['Order']) && $order['Order']['order_type_id']==Configure::read('Order.type.gas_groups')) {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), Configure::read('Neo.portalgas.url').'admin/orders/index/'.$order['Order']['order_type_id']);
	$this->Html->addCrumb(__('Order home'), Configure::read('Neo.portalgas.url').'admin/orders/home/'.$order['Order']['order_type_id'].'/'.$order['Order']['id']);
	$this->Html->addCrumb(__('Management trasport'));
}
else {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id) && !empty($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
	$this->Html->addCrumb(__('Management trasport'));	
}
echo $this->Html->getCrumbList(['class'=>'crumbs']);
?>
<script type="text/javascript">
var debugLocal = false;
var delivery_id = <?php echo $delivery_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;
var order_id = <?php echo $order_id; // se arrivo da Orders/admin_index.ctp e' valorizzato ?>;

function choiceOrderPermission() {
	var div_contenitore = 'order-permission';
	showHideBox(div_contenitore,call_child=true); 

	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	
	AjaxCallToImporto(delivery_id, order_id); 	/* chiamata Ajax con il trasporto */
	
	$('.submit').css('display','none');
}
function choiceImporto() {
	var div_contenitore = 'summay-order-plus-importo';

	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var trasport = $('#trasport').val();

	if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
		alert("Devi indicare l'importo del trasporto");
		$("input[name='summay-order-plus-options']").prop('checked',false);
		return false;
	}
	
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToOptions(delivery_id, order_id); 	// chiamata Ajax opzioni summary orders
}
function choiceOptions() {

	var div_contenitore = 'summay-order-plus-options';
	
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var trasport = $('#trasport').val();
	var options = $("input[name='summay-order-plus-options']:checked").val(); 

	if(options=='' || options==undefined) return;
	
	if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
		alert("Devi indicare l'importo del trasporto");
		$("input[name='summay-order-plus-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceOptions - div_contenitore "+div_contenitore+", summay-order-plus-options "+options);
	if(delivery_id == '' || order_id=='' || options=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToResults(delivery_id, order_id, options); /* chiamata Ajax l'elenco degli SummaryOrders con il trasporto calcolato */ 
}

/*
 *  chiamata Ajax per importo del trasporto
 */
function AjaxCallToImporto(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_trasport_importo&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'summay-order-plus-importo';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per opzioni Trasport
 */
function AjaxCallToOptions(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_trasport_options&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'summay-order-plus-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryOrders e il trasporto calcolato
 */
function AjaxCallToResults(delivery_id, order_id, options) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_trasport&delivery_id="+delivery_id+"&order_id="+order_id+"&options="+options+"&format=notmpl";
	var idDivTarget = 'summay-order-plus-results';
	ajaxCallBox(url, idDivTarget);
	
	$('.submit').css('display','block');
}
</script>



<h2 class="ico-trasport">
	<?php echo __('Management trasport');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create('SummaryOrderTrasport',array('id' => 'formGas'));
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>


	<?php 
	echo $this->element('boxOrder',array('results' => $results));
	?>	
	
	<div id="summay-order-plus-importo" style="display:none;margin-top:5px;"></div>

	<div id="summay-order-plus-options" style="display:none;margin-top:5px;"></div>

	<div id="summay-order-plus-results" style="display:none;min-height:50px;"></div>
	
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
$(document).ready(function() {

	$('.submit').css('display','none');
	
	$('#sumbitElabora').click(function() {

		var delivery_id = $('#delivery_id').val();
		var order_id = $('#order_id').val();
		var trasport = $('#trasport').val();
		
		if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
			alert("Devi indicare l'importo del trasporto");
			$("input[name='summay-order-plus-options']").prop('checked',false);
			return false;
		}
		
		$('#actionSubmit').val('submitElabora');

		return true;

	});
})
</script>