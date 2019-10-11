<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List ProdDeliveries'),array('controller' => 'ProdDeliveries', 'action' => 'index'));
if(isset($prod_delivery_id) && !empty($prod_delivery_id))
	$this->Html->addCrumb(__('ProdDelivery home'),array('controller'=>'ProdDeliveries','action'=>'home', null, 'prod_delivery_id='.$prod_delivery_id));
$this->Html->addCrumb(__('Management Carts Split'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<script type="text/javascript">
var debugLocal = false;
var prod_delivery_id = <?php echo $prod_delivery_id; // se arrivo da ProdDelivery/admin_index.ctp e' valorizzato ?>;

function choiceOrderPermission() {
	var div_contenitore = 'prod_delivery-permission';
	showHideBox(div_contenitore,call_child=true); 

	var prod_delivery_id    = $('#prod_delivery_id').val(); /* estraggo info di delivery_id e supplier_id */
	
	AjaxCallToCartsSplitsOptions(prod_delivery_id); 	/* chiamata Ajax opzioni cart split */
}
function choiceCartsSplitsOptions() {

	var div_contenitore = 'cart-splits-options';
	
	var prod_delivery_id    = $('#prod_delivery_id').val(); /* estraggo info di delivery_id e supplier_id */
	var cartsSplitsOptions = $("input[name='cart-splits-options']:checked").val(); 

	if(cartsSplitsOptions=='options-delete-yes') {
		if(!confirm("Sei sicuro di voler rigenerare i dati cancellando quelli sottostanti?")) {
			$("#options-carts_splits-delete-no").prop('checked',true);
			return;
		}
	}
	
	if(debugLocal) alert("choiceCartsSplitsOptions - div_contenitore "+div_contenitore+", cartsSplitsOptions "+cartsSplitsOptions);
	if(prod_delivery_id=='' || cartsSplitsOptions=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCartsSplitsResult(prod_delivery_id, cartsSplitsOptions); /* chiamata Ajax l'elenco degli cart splits */
}
function choiceCartsSplitsOptionsReadOnly() {

	var div_contenitore = 'cart-splits-options';
	
	var prod_delivery_id    = $('#prod_delivery_id').val(); /* estraggo info di delivery_id e supplier_id */

	if(debugLocal) alert("choiceCartsSplitsOptions - div_contenitore "+div_contenitore);
	if(prod_delivery_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToCartsSplitsResultReadOnly(prod_delivery_id); /* chiamata Ajax l'elenco degli cart splits solo in lettura */ 

}

/*
 *  chiamata Ajax per opzioni ProdCartsSplits
 */
function AjaxCallToCartsSplitsOptions(prod_delivery_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxProdCodes&action=box_carts_splits_options&prod_delivery_id="+prod_delivery_id+"&format=notmpl";
	var idDivTarget = 'cart-splits-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco ProdCartsSplits
 */
function AjaxCallToCartsSplitsResult(prod_delivery_id, cartsSplitsOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxProdCodes&action=box_carts_splits&prod_delivery_id="+prod_delivery_id+"&cartsSplitsOptions="+cartsSplitsOptions+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco ProdCartsSplits solo in lettura
 */
function AjaxCallToCartsSplitsResultReadOnly(prod_delivery_id) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxProdCodes&action=box_carts_splits_read_only&prod_delivery_id="+prod_delivery_id+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
</script>



<h2 class="ico-management-carts-split">
	<?php echo __('Management Carts Split');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('ProdDelivery home'), array('controller' => 'ProdDeliveries', 'action' => 'home', null, 'prod_delivery_id='.$prod_delivery_id),array('class' => 'action actionWorkflow','title' => __('ProdDelivery home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="carts">
<?php echo $this->Form->create(array('id'=>'formGas'));?>
	<fieldset>

		<?php 
		echo $this->element('boxProdDelivery',array('results' => $results));
		?>		
	
	<div id="cart-splits-options" style="display:none;margin-top:5px;"></div>

	<div id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>

<?php 
echo $this->element('menuProdDeliveryLaterale');

echo $this->element('legendaProdDeliveriesState',array('htmlLegenda' => $htmlLegenda));
?>