<div class="input required">

	<label for=""><?php echo __('Delivery');?></label>
		
	<div style="width:75%;float: right;">
	 
		<table>
			<tr>
				<td>
					<input type="radio" 
					<?php
					if($modalita=='ADD')
						echo 'checked="checked"';
					else
					if($modalita=='EDIT' && $this->request->data['Delivery']['sys']=='N')
						echo 'checked="checked"';
					?> value="select" name="typeDelivery" />
				</td>
				<td id="select_content">
					<?php
					if($modalita=='ADD') {
						if(empty($deliveries))
							echo __('OrderNotFoundDeliveries');
						else
							echo $this->Form->input('delivery_id',array('label' => false, 'empty' => Configure::read('option.empty'), 'id' => 'delivery_id', 'default' => $delivery_id, 'required' => 'false'));
					} 
					else 
					if($modalita=='EDIT') {
						if(empty($deliveries))
							echo __('OrderNotFoundDeliveries');
						else
							echo $this->Form->input('delivery_id',array('label' => false, 'id' => 'delivery_id', 'default' => $delivery_id, 'required' => 'false'));
					}
					?>
				</td>
			</tr>
			<?php
			if($modalita=='ADD') {
			?>				
				<tr>
					<td>
						<input type="radio" value="mail" name="typeDelivery" />
					</td>
					<td id="mail_content">
						<div class="actions-img" style="float:left;">
						<?php 
						if($isManagerDelivery) {
							echo $this->Html->link(__('New Delivery'), array('controller' => 'Deliveries', 'action' => 'add'), array('id' => 'content_link', 'class' => 'action actionAdd','title' => __('New Delivery')));
							
							echo '<div id="content_link_only_text" title="'.__('New Delivery').'" class="action actionAdd">'.__('New Delivery').'</div>';							
						}
						else {
							echo $this->Html->link(__('Send mail to manager to delivery'), array(), array( 'class' => 'action actionEdit sendMail','title' => __('Send mail to manager to delivery'),
											'pass_org_id' => $result['SuppliersOrganization']['organization_id'],
											'pass_id' => 0,
											'pass_entity' => 'DeliveryNew',
											'id' => 'content_link'));
		
							echo '<div id="content_link_only_text" title="'.__('Send mail to manager to delivery').'" class="action actionEdit">'.__('Send mail to manager to delivery').'</div>';
						}
						?>
						</div>
					</td>
				</tr>
			<?php
			}
			?>			
			<tr>
				<td>
					<input type="radio" 
					<?php
					if($modalita=='EDIT' && $this->request->data['Delivery']['sys']=='Y')
						echo 'checked="checked"';
					?> 					
					value="to_defined" name="typeDelivery" />
				</td>
				<td id="to_defined_content">
					Data e luogo della consegna ancora da definire
				</td>
			</tr>
			<?php
			if($modalita=='EDIT' && $tot_delivery_old > 0) {
			?>	
			<tr>
				<td>
					<input type="radio" value="delivery_old" name="typeDelivery" />
				</td>
				<td id="delivery_old_content">
					<?php echo $this->Html->link(__('Associalo ad una consegna scaduta'), array('controller' => 'Orders', 'action' => 'edit_delivery_old', null, 'delivery_id='.$this->request->data['Order']['delivery_id'].'&order_id='.$this->request->data['Order']['id']),array('id' => 'action_delivery_old', 'title' => __('Associalo ad una consegna scaduta')));?>
					<span id="label_delivery_old"><?php echo __('Associalo ad una consegna scaduta');?></span>
				</td>
			</tr>			
			<?php
			}
			?>			
		</table>
	
	</div>
</div>

<script type="text/javascript">
function gestTypeDelivery(typeDelivery) {

	/*console.log("gestTypeDelivery "+typeDelivery);*/
	
	if(typeDelivery=='select') {
		$('#delivery_id').removeAttr('disabled');
		$('#select_content').css('opacity','1');
		$('#mail_content').css('opacity','0.3');
		$('#content_link').hide();
		$('#content_link_only_text').show();
		$('#to_defined_content').css('opacity','0.3');
		$('#delivery_old_content').css('opacity','0.3');
		$('#action_delivery_old').hide();
		$('#label_delivery_old').show();
		$('#delivery_old_content').css('background-color','#fff');
	}
	else
	if(typeDelivery=='mail') {
		$('#delivery_id').attr('disabled', 'disabled');
		$('#select_content').css('opacity','0.3');
		$('#mail_content').css('opacity','1');
		$('#content_link').show();
		$('#content_link_only_text').hide();
		$('#to_defined_content').css('opacity','0.3');
		$('#delivery_old_content').css('opacity','0.3');
		$('#action_delivery_old').hide();
		$('#label_delivery_old').show();
		$('#delivery_old_content').css('background-color','#fff');
	}
	else
	if(typeDelivery=='to_defined') {
		$('#delivery_id').attr('disabled', 'disabled');
		$('#select_content').css('opacity','0.3');
		$('#mail_content').css('opacity','0.3');
		$('#content_link').hide();
		$('#content_link_only_text').show();
		$('#to_defined_content').css('opacity','1');
		$('#delivery_old_content').css('opacity','0.3');
		$('#action_delivery_old').hide();
		$('#label_delivery_old').show();
		$('#delivery_old_content').css('background-color','#fff');
	}
	else
	if(typeDelivery=='delivery_old') {
		$('#delivery_id').attr('disabled', 'disabled');
		$('#select_content').css('opacity','0.3');
		$('#mail_content').css('opacity','0.3');
		$('#content_link').hide();
		$('#content_link_only_text').show();
		$('#to_defined_content').css('opacity','0.3');
		$('#delivery_old_content').css('opacity','1');
		$('#action_delivery_old').show();
		$('#label_delivery_old').hide();
		$('#delivery_old_content').css('background-color','#fff');
	}	
}
					
$(document).ready(function() { 
	$("input[name='typeDelivery']").change(function() {	
		var typeDelivery = $("input[name='typeDelivery']:checked").val();
		gestTypeDelivery(typeDelivery);
	});

	<?php
	if($modalita=='ADD') {
		echo "gestTypeDelivery('select');";						
	} 
	else 
	if($modalita=='EDIT') {
		if($this->request->data['Delivery']['sys']=='Y')
			echo "gestTypeDelivery('to_defined');";
		else 
			echo "gestTypeDelivery('select');";
	}
	?>
});
</script>