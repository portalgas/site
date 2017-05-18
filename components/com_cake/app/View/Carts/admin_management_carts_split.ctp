<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
if(isset($order_id) && !empty($order_id))
	$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
$this->Html->addCrumb(__('Management Carts Split'));
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
	
	AjaxCallToCartsSplitsOptions(delivery_id, order_id); 	/* chiamata Ajax opzioni cart split */
}
function choiceCartsSplitsOptions() {

	var div_contenitore = 'cart-splits-options';
	
	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */
	var cartsSplitsOptions = jQuery("input[name='cart-splits-options']:checked").val(); 

	if(cartsSplitsOptions=='options-delete-yes') {
		if(!confirm("Sei sicuro di voler rigenerare i dati cancellando quelli sottostanti?")) {
			jQuery("#options-carts_splits-delete-no").prop('checked',true);
			return;
		}
	}
	
	if(debugLocal) alert("choiceCartsSplitsOptions - div_contenitore "+div_contenitore+", cartsSplitsOptions "+cartsSplitsOptions);
	if(delivery_id == '' || order_id=='' || cartsSplitsOptions=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCartsSplitsResult(delivery_id, order_id, cartsSplitsOptions); /* chiamata Ajax l'elenco degli cart splits */
}
function choiceCartsSplitsOptionsReadOnly() {

	var div_contenitore = 'cart-splits-options';
	
	var delivery_id = jQuery('#delivery_id').val();
	var order_id    = jQuery('#order_id').val(); /* estraggo info di delivery_id e supplier_id */

	if(debugLocal) alert("choiceCartsSplitsOptions - div_contenitore "+div_contenitore);
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCartsSplitsResultReadOnly(delivery_id, order_id); /* chiamata Ajax l'elenco degli cart splits solo in lettura */ 

}

/*
 *  chiamata Ajax per opzioni cart splits
 */
function AjaxCallToCartsSplitsOptions(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_carts_splits_options&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'cart-splits-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco CartsSplits
 */
function AjaxCallToCartsSplitsResult(delivery_id, order_id, cartsSplitsOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_carts_splits&delivery_id="+delivery_id+"&order_id="+order_id+"&cartsSplitsOptions="+cartsSplitsOptions+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco CartsSplits solo in lettura
 */
function AjaxCallToCartsSplitsResultReadOnly(delivery_id, order_id) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_carts_splits_read_only&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
</script>



<h2 class="ico-management-carts-split">
	<?php echo __('Management Carts Split');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create(array('id'=>'formGas'));
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>

	<?php 
	echo $this->element('boxOrder',array('results' => $results));
	?>	
	
	<div id="cart-splits-options" style="display:none;margin-top:5px;"></div>

	<div id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>

<?php 
$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);

/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>
<style type="text/css">
.cakeContainer label {
    width: 100px !important;
}
</style>