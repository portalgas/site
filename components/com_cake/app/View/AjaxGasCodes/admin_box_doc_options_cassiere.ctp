<div class="left label" style="width:125px !important;">Opzioni documento</div>
<div class="left radio">
	<?php
	if($user_id=='ALL') {
	?>
		<p>
			<input type="radio" name="doc_options" id="to-delivery-cassiere-users-compact-all" value="to-delivery-cassiere-users-compact-all" />
				<label for="to-delivery-cassiere-users-compact-all"><?php echo __('to_delivery_cassiere_users_compact_all');?></label>
		</p>
		<p>
			<input type="radio" name="doc_options" id="to-delivery-cassiere-users-all" value="to-delivery-cassiere-users-all" />
				<label for="to-delivery-cassiere-users-all"><?php echo __('to_delivery_cassiere_users_all');?></label>
		</p>
		<p>
			<input type="radio" name="doc_options" id="to-delivery-cassiere-users-all-split" value="to-delivery-cassiere-users-all-split" />
				<label for="to-delivery-cassiere-users-all-split"><?php echo __('to_delivery_cassiere_users_all_split');?></label>
		</p>
		<p>
			<input type="radio" name="doc_options" id="to-lists-suppliers-cassiere" value="to-lists-suppliers-cassiere" />
				<label for="to-lists-suppliers-cassiere"><?php echo __('to_lists_suppliers_cassiere');?></label>
		</p>
		<p>
			<input type="radio" name="doc_options" id="to-lists-orders-cassiere" value="to-lists-orders-cassiere" />
				<label for="to-lists-orders-cassiere"><?php echo __('to_lists_orders_cassiere');?></label>
		</p>
		<p>
			<input type="radio" name="doc_options" id="to-list-users-delivery-cassiere" value="to-list-users-delivery-cassiere" />
				<label for="to-list-users-delivery-cassiere"><?php echo __('to_list_users_delivery_cassiere');?></label>
		</p>
	<?php
	}
	else {
	?>
		<p>
			<input type="radio" name="doc_options" id="to-delivery-cassiere-user-one" value="to-delivery-cassiere-user-one" checked="checked" />
				<label for="to-delivery-cassiere-user-one"><?php echo __('to_delivery_cassiere_user_one');?></label>
		</p>	
	<?php
	}
	?>
</div>

<style type="text/css">
.box-options {
	border:1px solid #DEDEDE;
	border-radius:8px;
	margin:10px;
	padding:8px; 
	display:none;
}
</style>
<div class="left setting" style="width:30%;">

</div>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='doc_options']").change(function() {	

		var doc_options = jQuery("input[name='doc_options']:checked").val();
			
		choiceDocOptions();
	});
	
	<?php
	/*
	 * c'e' un solo report
	 */
	if($user_id!='ALL') 
		echo 'choiceDocOptions();';
	?>	
});
</script>