<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Management trasport'));
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
	
	AjaxCallToTrasportImporto(delivery_id, order_id); 	/* chiamata Ajax con il trasporto */
	
	jQuery('.submit').css('display','none');
}
function choiceTrasportImporto() {
	var div_contenitore = 'trasport-importo';

	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var trasport = jQuery('#trasport').val();

	if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
		alert("Devi indicare l'importo del trasporto");
		jQuery("input[name='trasport-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceTrasportImporto - div_contenitore "+div_contenitore+", trasportOptions "+trasportOptions);
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToTrasportOptions(delivery_id, order_id); 	// chiamata Ajax opzioni summary orders
}
function choiceTrasportOptions() {

	var div_contenitore = 'trasport-options';
	
	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var trasport = jQuery('#trasport').val();
	var trasportOptions = jQuery("input[name='trasport-options']:checked").val(); 

	if(trasportOptions=='' || trasportOptions==undefined) return;
	
	if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
		alert("Devi indicare l'importo del trasporto");
		jQuery("input[name='trasport-options']").prop('checked',false);
		return false;
	}
	
	if(debugLocal) alert("choiceTrasportOptions - div_contenitore "+div_contenitore+", trasportOptions "+trasportOptions);
	if(delivery_id == '' || order_id=='' || trasportOptions=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToTrasportResult(delivery_id, order_id, trasportOptions); /* chiamata Ajax l'elenco degli SummaryOrders con il trasporto calcolato */ 
}

/*
 *  chiamata Ajax per importo del trasporto
 */
function AjaxCallToTrasportImporto(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_trasport_importo&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'trasport-importo';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per opzioni Trasport
 */
function AjaxCallToTrasportOptions(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_trasport_options&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'trasport-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryOrders e il trasporto calcolato
 */
function AjaxCallToTrasportResult(delivery_id, order_id, trasportOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_trasport&delivery_id="+delivery_id+"&order_id="+order_id+"&trasportOptions="+trasportOptions+"&format=notmpl";
	var idDivTarget = 'trasport-results';
	ajaxCallBox(url, idDivTarget);
	
	jQuery('.submit').css('display','block');
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
	
	<div id="trasport-importo" style="display:none;margin-top:5px;"></div>

	<div id="trasport-options" style="display:none;margin-top:5px;"></div>

	<div id="trasport-results" style="display:none;min-height:50px;"></div>
	
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
		var trasport = jQuery('#trasport').val();
		
		if(trasport=='' || trasport==null || trasport=='0,00' || trasport=='0.00' || trasport=='0') {
			alert("Devi indicare l'importo del trasporto");
			jQuery("input[name='trasport-options']").prop('checked',false);
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