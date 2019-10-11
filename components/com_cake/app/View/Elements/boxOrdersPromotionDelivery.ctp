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
			if($modalita=='EDIT' && $tot_delivery_old > 0) {
			?>	
			<tr>
				<td>
					<input type="radio" value="delivery_old" name="typeDelivery" />
				</td>
				<td id="delivery_old_content">
				</td>
			</tr>			
			<?php
			}
			?>			
		</table>
	
	</div>
</div>
