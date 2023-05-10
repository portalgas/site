<?php
if(isset($order['Order']) && $order['Order']['order_type_id']==Configure::read('Order.type.gas_groups')) {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'), Configure::read('Neo.portalgas.url').'admin/orders/index/'.$order['Order']['order_type_id']);
	$this->Html->addCrumb(__('Order home'), Configure::read('Neo.portalgas.url').'admin/orders/home/'.$order['Order']['order_type_id'].'/'.$order['Order']['id']);
	$this->Html->addCrumb(__('Management Carts Group By Users'));
}
else {
	$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
	$this->Html->addCrumb(__('List Orders'),array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id) && !empty($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
	$this->Html->addCrumb(__('Management Carts Group By Users'));	
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
	
	AjaxCallToSummaryOrdersOptions(delivery_id, order_id); 	/* chiamata Ajax opzioni summary orders */
}
function choiceSummaryOrdersOptions() {

	var div_contenitore = 'summary-orders-options';
	
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */

	var summaryOrdersOptions = '';
	if($("input[name='summary_orders-options']").length>0)
		summaryOrdersOptions = $("input[name='summary_orders-options']:checked").val(); 

	if(summaryOrdersOptions=='options-delete-yes') {
		if(!confirm("Sei sicuro di voler rigenerare i dati cancellando quelli sottostanti?")) {
			$("#options-summary_orders-delete-no").prop('checked',true);
			return;
		}
	}
	
	if(debugLocal) alert("choiceSummaryOrdersOptions - div_contenitore "+div_contenitore+", summaryOrdersOptions "+summaryOrdersOptions);
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToSummaryOrdersResult(delivery_id, order_id, summaryOrdersOptions); /* chiamata Ajax l'elenco degli Summary Orders */ 
}
function choiceSummaryOrdersOptionsReadOnly() {

	var div_contenitore = 'summary-orders-options';
	
	var delivery_id = $('#delivery_id').val();
	var order_id    = $('#order_id').val(); /* estraggo info di delivery_id e supplier_id */

	if(debugLocal) alert("choiceSummaryOrdersOptions - div_contenitore "+div_contenitore);
	if(delivery_id == '' || order_id=='') {
		showHideBox(div_contenitore,call_child=false);
		return;
	}
	showHideBox(div_contenitore,call_child=true);
	
	AjaxCallToSummaryOrdersResultReadOnly(delivery_id, order_id); /* chiamata Ajax l'elenco degli Summary Orders solo in lettura */ 

}

/*
 *  chiamata Ajax per opzioni summary orders
 */
function AjaxCallToSummaryOrdersOptions(delivery_id, order_id) {
	url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_orders_options&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'summary-orders-options';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryOrders
 */
function AjaxCallToSummaryOrdersResult(delivery_id, order_id, summaryOrdersOptions) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_orders&delivery_id="+delivery_id+"&order_id_selected="+order_id+"&summaryOrdersOptions="+summaryOrdersOptions+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
/*
 *  chiamata Ajax per elenco SummaryOrders solo in lettura
 */
function AjaxCallToSummaryOrdersResultReadOnly(delivery_id, order_id) {
	var url = "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_orders_read_only&delivery_id="+delivery_id+"&order_id="+order_id+"&format=notmpl";
	var idDivTarget = 'doc-preview';
	ajaxCallBox(url, idDivTarget);
}
</script>



<h2 class="ico-management-carts-group-by-users">
	<?php echo __('Management Carts Group By Users');?>
	<div class="actions-img">
		<ul>
			<li><?php echo $this->Html->link(__('Order home'), array('controller' => 'Orders', 'action' => 'home', null, 'order_id='.$order_id),array('class' => 'action actionWorkflow','title' => __('Order home'))); ?></li>
		</ul>
	</div>
</h2>

<div class="contentMenuLaterale">
<?php 
echo $this->Form->create();
echo $this->element('boxDesOrder', array('results' => $desOrdersResults, 'summaryDesOrderResults' => $summaryDesOrderResults));
?>
	<fieldset>

	<?php 
	echo $this->element('boxOrder',array('results' => $results));
	?>	

	<div id="summary-orders-options" style="display:none;margin-top:5px;clear: both;"></div>

	<div id="doc-preview" style="display:none;clear: both;"></div>
	
	</fieldset>
</div>



<div id="dialogmodal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nota da associare all'articolo acquistato</h4>
      </div>
      <div class="modal-body">
        <p><textarea class="noeditor" id="notaTextEcomm" name="nota" style="width: 100%;" rows="10"></textarea>
		<div class="clearfix"></div></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Close');?></button>
        <button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo __('Submit');?></button>
      </div>
    </div>

  </div>
</div>


<?php 
$options = [];
echo $this->MenuOrders->drawWrapper($order_id, $options);

/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>
<script type="text/javascript">
$(document).ready(function() {
	
    $('#dialogmodal').on('shown.bs.modal', function() {
		$('#notaTextEcomm').val("");

		var order_id = $('#order_id-'+numRow).val();
		var article_organization_id = $('#article_organization_id-'+numRow).val();
		var article_id = $('#article_id-'+numRow).val();
		var user_id = $('#user_id').val();
		var key = order_id+"_"+article_organization_id+"_"+article_id+"_"+user_id;
				
		$.ajax({
			type: "GET",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=getNotaForzato&key="+key+"&format=notmpl",
			data: "",
			success: function(response){
				$('#notaTextEcomm').val(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
		return false;
    })
	.on('hidden.bs.modal', function() {
		var notaTextEcomm = encodeURIComponent($('#notaTextEcomm').val());

		var order_id = $('#order_id-'+numRow).val();
		var article_organization_id = $('#article_organization_id-'+numRow).val();
		var article_id = $('#article_id-'+numRow).val();
		var user_id = $('#user_id').val();
		var key = order_id+"_"+article_organization_id+"_"+article_id+"_"+user_id;

		$.ajax({
			type: "POST",
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=setNotaForzato&key="+key+"&format=notmpl",
			data: "notaTextEcomm="+notaTextEcomm,
			success: function(response){
				if(notaTextEcomm=="")
					$('#notaEcomm-'+numRow).attr('src','<?php echo Configure::read('App.img.cake');?>/actions/32x32/filenew.png');					
				else	
					$('#notaEcomm-'+numRow).attr('src','<?php echo Configure::read('App.img.cake');?>/actions/32x32/playlist.png');
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
			}
		});
		return false;
    });

	
	<?php if(!empty($alertModuleConflicts)) {
		if(!$popUpDisabled)
			echo "apriPopUpBootstrap('".Configure::read('App.server')."/administrator/index.php?option=com_cake&controller=PopUp&action=".$alertModuleConflicts."&orderHasTrasport=".$orderHasTrasport."&orderHasCostMore=".$orderHasCostMore."&orderHasCostLess=".$orderHasCostLess."&format=notmpl', '')";
	}
	?>
});
</script>