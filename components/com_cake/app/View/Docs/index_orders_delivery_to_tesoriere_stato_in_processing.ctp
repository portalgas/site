<script type="text/javascript">
$(document).ready(function() {

	$('#formGas').submit(function() {
		var delivery_id = $('#delivery_id').val();
		var order_id_selected = '';
		for(i = 0; i < $("input[name='order_id_selected']:checked").length; i++) {
			order_id_selected += $("input[name='order_id_selected']:checked").eq(i).val()+',';
		}

		if(delivery_id=='') {
			alert("<?php echo __('jsAlertDeliveryRequired');?>");
			return false;
		}
		if(order_id_selected=='') {
			alert("<?php echo __('jsAlertOrderAtLeastToRunRequired');?>");
			return false;
		}	    
		order_id_selected = order_id_selected.substring(0,order_id_selected.length-1);
		
		var action = $('#formGas').attr('action');
		action = action.substring(0,action.indexOf('tesoriereStatoInProcessing')+new String('tesoriereStatoInProcessing').length);
		action += '&delivery_id='+delivery_id+'&order_id_selected='+order_id_selected;

		$('#formGas').attr('action',action);
		
		return true;
	});

});
</script>
<label for="order_id">Ordini</label>
<div style="margin-left: 210px;">
<?php if (!empty($orders)):?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th colspan="2"><?php echo __('N');?></th>
		<th><?php echo __('StatoElaborazione'); ?></th>
		<th><?php echo __('Supplier');?></th>
		<th>
			<?php echo __('DataInizio');?><br />
			<?php echo __('DataFine');?>
		</th>
		<th><?php echo __('OpenClose');?></th>
		<th><?php echo __('isVisibleFrontEnd'); ?></th>
		<th>Referenti</th>
		<th><?php echo __('Modified'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($orders as $i => $order):		
		?>
		<tr>
			<td><?php echo ($i+1);?></td>
			<td>
				<?php 
				if($order['state_code']=='WAIT-PROCESSED-TESORIERE')
					echo '<input type="checkbox" name="order_id_selected" value="'.$order['id'].'" />';
				?>
			</td>
			<td><?php 
			if($order['Order']['state_code'] == 'CREATE-INCOMPLETE' || $order['Order']['state_code'] == 'OPEN-NEXT' || $order['Order']['state_code'] == 'OPEN') 
				echo "In attesa che l'ordine termini";
			else {
			    echo __($order['Order']['state_code'].'-label');
				echo '&nbsp;';
				echo $this->App->drawOrdersStateDiv($order);
			}
				?>
			</td>
			<td><?php echo $order['SuppliersOrganization']['name']; ?></td>
			<td style="white-space:nowrap;">
				<?php echo $this->Time->i18nFormat($order['data_inizio'],"%A %e %B %Y"); ?><br />
				<?php echo $this->Time->i18nFormat($order['data_fine'],"%A %e %B %Y"); ?>
			</td>
			<td style="white-space:nowrap;">
				<?php 
					echo $this->App->utilsCommons->getOrderTime($order);
				?>
			</td>
			<td title="<?php echo __('toolTipsVisibleFrontEnd');?>" class="stato_<?php echo $this->App->traslateEnum($order['isVisibleFrontEnd']); ?>"></td>
			<td>
				<?php 
				echo $this->app->drawListSuppliersOrganizationsReferents($user, $order['SuppliersOrganizationsReferent']);
				?>
			</td>
			<td style="white-space: nowrap;"><?php echo $this->App->formatDateCreatedModifier($order['modified']); ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
	
<?php 
	echo $this->Form->end('Prendi in carico gli ordini');
else: 
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Non ci sono ordini associati"));
endif;?>
</div>