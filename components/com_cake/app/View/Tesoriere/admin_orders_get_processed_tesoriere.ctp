<?php
echo '<div class="old-menu">';

$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if(!isset($delivery_id)) $delivery_id = 0;
$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
$this->Html->addCrumb(__('OrdersProcessedTesoriere'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>

<script type="text/javascript">
$(document).ready(function() {

	$('.submit').css('display','none');
	
	var delivery_id = $('#delivery_id').val();
	if(delivery_id!="" && delivery_id!=undefined) caricaOrdini();
	
	$('#delivery_id').change(function() {
		caricaOrdini();
	});

	$('#sumbitElabora').click(function() {

		if(!ctrlCampi()) return false;

		var delivery_id = $('#delivery_id').val();
		var order_id_selected = '';
		for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);

		$('#doc-preview').css('display', 'block');
		$('#doc-preview').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');

		$.ajax({
			type: "get", 
			url: "/administrator/index.php?option=com_cake&controller=AjaxGasCodes&action=box_summary_orders&delivery_id="+delivery_id+"&order_id_selected="+order_id_selected+"&format=notmpl",
			data: "",  
			success: function(response) {
				$('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				$("#doc-preview").html(response);
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				$('#doc-preview').css('background', 'none repeat scroll 0 0 transparent');
				$('#doc-preview').html(textStatus);
			}
		});
		
		return false;
	});

	$('#sumbitToPROCESSED_REFERENTE_POST_DELIVERY').click(function() {

		if(!ctrlCampi()) return false;

		var order_id_selected = '';
		for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		$('#order_id_selected').val(order_id_selected);

		$('#action_submit').val('OrdersToPROCESSED_REFERENTE_POST_DELIVERY');

		return true;
	});		


	$('#sumbitToTO_REQUEST_PAYMENT').click(function() {

		if(!ctrlCampi()) return false;

		var order_id_selected = '';
		for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
		}
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		$('#order_id_selected').val(order_id_selected);
		
		$('#action_submit').val('OrdersToTO_REQUEST_PAYMENT');

		return true;
	});		  
});

function ctrlCampi() {
	var delivery_id = $('#delivery_id').val();
	var order_id_selected = '';
	for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
		order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
	}

	if(delivery_id=='') {
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		alert("<?php echo __('jsAlertDeliveryRequired');?>");
		return false;
	}
	if(order_id_selected=='') {
		alert("<?php echo __('jsAlertOrderAtLeastRequired');?>");
		return false;
	}
	return true;
}
function caricaOrdini() {
	var delivery_id = $('#delivery_id').val();

	if(delivery_id=="") {
		$('#orders-result').html('');
		$('#orders-result').css('display', 'none');
		$('#doc-preview').css('display', 'none');	
		return;
	}

	$('#orders-result').html('');
	$('#orders-result').css('background', 'url("<?php echo Configure::read('App.server').Configure::read('App.img.cake');?>/ajax-loader.gif") no-repeat scroll center 0 transparent');
	$('#orders-result').css('display', 'block');
	$('#doc-preview').css('display', 'none');
	$('#doc-preview').html('');	
			
	$.ajax({
		type: "get",
		url: "/administrator/index.php?option=com_cake&controller=Tesoriere&action=orders_index&delivery_id="+delivery_id+"&order_state_code_checked=<?php echo $order_state_code_checked;?>&format=notmpl",
		data: "", 
		success: function(response) {
			$('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#orders-result').html(response);
		},
		error:function (XMLHttpRequest, textStatus, errorThrown) {
			$('#orders-result').css('background', 'none repeat scroll 0 0 transparent');
			$('#orders-result').html(textStatus);
		}
	});
}
</script>

<h2 class="ico-config">
	<?php echo __('OrdersProcessedTesoriere');?>
</h2>


<?php 
echo $this->Form->create('Tesoriere',array('id' => 'formGas'));
echo '<fieldset>';
	
// $this->App->dd($deliveries);
$options = array('id'=>'delivery_id');
if(!empty($delivery_id) && $delivery_id>0)
	$options += array('default' => $delivery_id);
else
	$options += array('empty' => Configure::read('option.empty'));

echo $this->Form->input('delivery_id',$options);
?>	
	
	<div id="orders-result" style="display:none;min-height:50px;"></div>
	
	<div class="submit">
		<label for="order_id">Azioni</label>
		<div style="margin-left: 210px;">
	
			<div class="table-responsive"><table class="table ">
				<tr>
					
						<?php 
						/*
						 echo '<td>';
						 echo '<div class="submit"><input id="sumbitElabora" type="submit" value="Elabora gli ordini" /></div>';
						 echo '</td>';
						*/
						echo '<td>';
						echo '<div class="submit"><input id="sumbitToPROCESSED_REFERENTE_POST_DELIVERY" type="submit" class="buttonBlu" value="Porta gli ordini allo stato \'in carico al referente\'" /></div>';
						echo '</td>';
						echo '<td>';
						echo '<div class="submit"><input id="sumbitToTO_REQUEST_PAYMENT" type="submit" class="buttonBlu" value="Porta gli ordini allo stato \'Possibilità di richiederne il pagamento\'" /></div>';
						?>
					</td>
				</tr>
			</table></div>
		</div>
	</div>
	
	<?php	
		echo $this->Form->hidden('order_id_selected', array('id' => 'order_id_selected', 'value' => ''));
		echo $this->Form->hidden('action_submit', array('id' => 'action_submit', 'value' => ''));
		
		echo $this->Form->end();
	?>
	
	<div id="doc-preview" style="display:none;"></div>
	
	</fieldset>
</div>

<?php
/*
 * legenda profilata
*/
echo $this->App->drawLegenda($user, $orderStatesToLegenda);
?>