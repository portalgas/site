<?php 
echo '<label for="order_id">Ordini</label> ';
echo '<div>';

if(!empty($results)) {
?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th></th>
			<th><?php echo __('N');?></th>
			<th colspan="2">
				<input type="checkbox" id="order_ids_selected_all" name="order_ids_selected_all" value="ALL" />
			</th>
			<th colspan="2"><?php echo __('Supplier');?></th>
			<th><?php echo __('DataInizio');?></th>
			<th><?php echo __('DataFine');?></th>
			<th><?php echo __('StatoElaborazione'); ?></th>
	</tr>
	<?php
	foreach ($results as $numResult => $result):
	?>
	<tr class="view">
		<td><a action="orders-<?php echo $result['Order']['id']; ?>" class="actionTrView openTrView" href="#" title="<?php echo __('Href_title_expand');?>"></a></td>
		<?php 
		echo '<td>';
		echo ((int)$numResult+1);
		echo '</td>';
		
		echo '<td>';
		if($result['Order']['state_code']!='CLOSE') {
			echo '<input type="checkbox" ';
			echo 'class="order_ids_selected" id="'.$result['Order']['id'].'" name="data[MonitoringOrder]['.$result['Order']['id'].']" value="'.$result['Order']['id'].'" ';
			if(isset($result['MonitoringOrder']['id'])) echo 'checked';
			echo '/>';
			
			/*
			 * differenza tra old e new, se differente c'e' stato un cambiamento e aggiorno db
			 */
			echo '<input type="hidden" ';
			echo ' class="order_old_ids" name="data[MonitoringOrder]['.$result['Order']['id'].'][old]" ';
			if(isset($result['MonitoringOrder']['id'])) echo 'value="true"';
			else echo 'value="false"';
			echo '/>';
			
			echo '<input type="hidden" ';
			echo ' class="order_new_ids" id="'.$result['Order']['id'].'-new" name="data[MonitoringOrder]['.$result['Order']['id'].'][new]" ';
			if(isset($result['MonitoringOrder']['id'])) echo 'value="true"';
			else echo 'value="false"';
			echo '/>';
			
			/*
			 * monitoringOrder.id che mi serve per eventuale delete 
			 */
			if(isset($result['MonitoringOrder']['id']))
				$monitoring_order_id = $result['MonitoringOrder']['id']; 
			else
				$monitoring_order_id = 0;
			
			echo '<input type="hidden" ';
			echo ' name="data[MonitoringOrder]['.$result['Order']['id'].'][monitoring_order_id]" ';
			echo ' value="'.$monitoring_order_id.'" ';
			echo '/>';
		}
		echo '</td>';
		echo '<td width="10" ';
		if(isset($result['MonitoringOrder']['id'])) echo 'style="background-color:green;" title="Ordine monitorato"';
		echo '>';
		echo '</td>';
		
		echo '<td>';
		if(!empty($result['Supplier']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$result['Supplier']['img1']))
			echo ' <img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.web.img.upload.content').'/'.$result['Supplier']['img1'].'" alt="'.$result['SupplierOrganization']['name'].'" /> ';
		echo '</td>';
		?>
		<td>
			<?php echo $result['SuppliersOrganization']['name']; ?>
		</td>
		<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Order']['data_inizio'],"%A %e %B %Y"); ?></td>
		<td style="white-space:nowrap;"><?php echo $this->Time->i18nFormat($result['Order']['data_fine'],"%A %e %B %Y"); ?></td>
		<?php
		echo '<td>';
		echo $this->App->drawOrdersStateDiv($result);
		echo '&nbsp;';
		echo __($result['Order']['state_code'].'-label');
		echo '</td>';
		?>			
	</tr>
	<tr class="trView" id="trViewId-<?php echo $result['Order']['id'];?>">
		<td colspan="2"></td>
		<td colspan="7" id="tdViewId-<?php echo $result['Order']['id'];?>"></td>
	</tr>
<?php 
	endforeach; 
	
	echo '</table>';
	} 
	else 
		echo $this->element('boxMsg',array('class_msg' => 'message resultsNotFound', 'msg' => "Non ci sono ancora ordini associati"));
	
echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {

	<?php if (!empty($results)) { ?>
		$('.submit').css('display','block');
	<?php
	} else { ?>
		$('.submit').css('display','none');
	<?php
	}
	?>	

	$('.actionTrView').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	
	$('.actionTrView').each(function () {
		actionTrView(this);
	});
	
	$('#order_ids_selected_all').click(function () {
		var checked = $("input[name='order_ids_selected_all']:checked").val();
		if(checked=='ALL') 
			$('.order_ids_selected').prop('checked',true);
		else
			$('.order_ids_selected').prop('checked',false);
			
        $('.order_ids_selected').each(function () {
			var checked = $(this).prop('checked');
			var id = $(this).attr('id');
			$('#'+id+'-new').val(checked);
		});			
	});

	$('.order_ids_selected').change(function () {
		var checked = $(this).prop('checked');
		var id = $(this).attr('id');
		$('#'+id+'-new').val(checked);
	});

});
</script>