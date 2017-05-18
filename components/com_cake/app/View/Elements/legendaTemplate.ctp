<div class="legenda">

<?php 
foreach ($templates as $template) 
	echo '<b>'.$template.'</b><br />';
?>
<table cellpadding="0" cellspacing="0" >
	<tr>
		<td colspan="2"><h3>Template 1</h3></td>
	</tr>	
	<tr>
		<td>
			<?php echo __('toolTipPayToDelivery');?>
		</td>
		<td>
			<?php echo '<div title="'.__('toolTipPayToDelivery').'">'.__('PayToDelivery').' POST</div>';?>
		</td>
	</tr>	
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferent');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferent').'" class="stato_si_int">'.__('Referente').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsSuperReferent');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsSuperReferent').'" class="stato_si_int">'.__('Super Referente').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsCassiere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsCassiere').'" class="stato_no_int">'.__('Cassiere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferentTesoriere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferentTesoriere').'" class="stato_no_int">'.__('Referente Tesoriere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsTesoriere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsTesoriere').'" class="stato_si_int">'.__('Tesoriere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsStoreroom');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsStoreroom').'" class="stato_no_int">'.__('Storeroom').'</div>';?>
		</td>
	</tr>
	

	<tr>
		<td colspan="2"><h3>Template 2 / Template 3</h3>
			allo stato <?php echo __('PROCESSED-BEFORE-DELIVERY-label');?> e <?php echo __('INCOMING_ORDER-label');?> il referente può modificare l'ordine<br />
			allo stato <?php echo __('PROCESSED-ON-DELIVERY-label');?> il referente non può più modificare l'ordine<br />
			dopo lo stato <?php echo __('PROCESSED-ON-DELIVERY-label');?> l'ordine passa a <?php echo __('CLOSE-label');?><br />
		</td>
	</tr>	
	<tr>
		<td>
			<?php echo __('toolTipPayToDelivery');?>
		</td>
		<td>
			<?php echo '<div title="'.__('toolTipPayToDelivery').'">'.__('PayToDelivery').' ON</div>';?>
		</td>
	</tr>	
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferent');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferent').'" class="stato_si_int">'.__('Referente').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsSuperReferent');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsSuperReferent').'" class="stato_si_int">'.__('Super Referente').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferentCassa');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferentCassa').'" class="stato_si_int">'.__('Referente Cassiere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsSuperReferentCassa');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsSuperReferentCassa').'" class="stato_si_int">'.__('Super Referente Cassiere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferentTesoriere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferentTesoriere').'" class="stato_no_int">'.__('Referente Tesoriere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsTesoriere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsTesoriere').'" class="stato_si_int">'.__('Tesoriere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsStoreroom');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsStoreroom').'" class="stato_no_int">'.__('Storeroom').'</div>';?>
		</td>
	</tr>	
	
	<tr>
		<td colspan="2"><h3>Template 4</h3>
			allo stato <?php echo __('PROCESSED-BEFORE-DELIVERY-label');?> e <?php echo __('INCOMING_ORDER-label');?> e <?php echo __('PROCESSED-ON-DELIVERY-label');?> il referente può modificare l'ordine<br />
			dopo lo stato <?php echo __('PROCESSED-ON-DELIVERY-label');?> l'ordine passa a <?php echo __('WAIT-PROCESSED-TESORIERE-label');?><br />		
		</td>
	</tr>	
	<tr>
		<td>
			<?php echo __('toolTipPayToDelivery');?>
		</td>
		<td>
			<?php echo '<div title="'.__('toolTipPayToDelivery').'">'.__('PayToDelivery').' ON-POST</div>';?>
		</td>
	</tr>	
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferent');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferent').'" class="stato_si_int">'.__('Referente').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsSuperReferent');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsSuperReferent').'" class="stato_si_int">'.__('Super Referente').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferentCassa');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferentCassa').'" class="stato_si_int">'.__('Referente Cassiere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsSuperReferentCassa');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsSuperReferentCassa').'" class="stato_si_int">'.__('Super Referente Cassiere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsReferentTesoriere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsReferentTesoriere').'" class="stato_no_int">'.__('Referente Tesoriere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsTesoriere');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsTesoriere').'" class="stato_si_int">'.__('Tesoriere').'</div>';?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo __('HasUserGroupsStoreroom');?>
		</td>
		<td>
			<?php echo '<div title="'.__('HasUserGroupsStoreroom').'" class="stato_no_int">'.__('Storeroom').'</div>';?>
		</td>
	</tr>		
</table>

</div>