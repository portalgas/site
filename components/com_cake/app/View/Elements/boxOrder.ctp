<?php 
// debug($results['Order']);

if($results['Delivery']['sys']=='N')
	$deliveryLabel = $results['Delivery']['luogoData'];
else 
	$deliveryLabel = $results['Delivery']['luogo'];

$orderDataInizio = $this->Time->i18nFormat($results['Order']['data_inizio'],"%A, %e %B %Y");
$orderDataFine = $this->Time->i18nFormat($results['Order']['data_fine'],"%A, %e %B %Y");

echo '<div class="row">';
echo '<div class="col-md-6">';	
echo '<select name="delivery_id" id="delivery_id" class="form-control">';
echo '<option value="'.$results['Delivery']['id'].'">'.$deliveryLabel.'</option>';
echo '</select>';
echo '<br />';
echo '<select name="order_id" id="order_id" class="form-control">';
echo '<option value="'.$results['Order']['id'].'">'.$results['SuppliersOrganization']['name'].' - dal '.$orderDataInizio.' al '.$orderDataFine;
echo '</option>';
echo '</select>';
echo '</div>';
echo '<div class="col-md-6">';
?>	
		<div class="table-responsive"><table class="table">
			<tr>
				<th><?php echo __('StateOrder');?></th>
				<th><?php echo __('StatoElaborazione');?></th>
				<?php 
				if($user->organization['Organization']['hasVisibility']=='Y')
					echo '<th>'.__('isVisibleFrontEnd').'</th>';
				?>
			</tr>
			<tr>
				<td style="white-space:nowrap;">
					<?php echo $this->App->utilsCommons->getOrderTime($results['Order']);?>
				</td>
				<td><?php 
						echo __($results['Order']['state_code'].'-label');
						echo $this->App->drawOrdersStateDiv($results);
					?>
				</td>
				<?php 
				if($user->organization['Organization']['hasVisibility']=='Y')
					echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($results['Order']['isVisibleFrontEnd']).'"></td>';
				?>
			</tr>
			<tr>
				<td colspan="2">
					<div id="suppliers_organization_details"></div>
				</td>
				<?php 
				if($user->organization['Organization']['hasVisibility']=='Y')
					echo '<td></td>';
				?>				
			</tr>			
		</table>
		</div>

	</div>
</div> <!-- row -->

<div class="row">
	<div id="order-permission">

	<?php 
	if(isset($results['msgExportDocs']) && !empty($results['msgExportDocs']))
		echo $this->element('boxMsg',array('class_msg' => 'message nomargin','msg' => $results['msgExportDocs']));
	?>
	<script type="text/javascript">            
	function suppliersOrganizationOrderDetails(order_id) {
		if(order_id!=undefined && order_id!=0 && order_id!='') {
			var url = "/administrator/index.php?option=com_cake&controller=Ajax&action=suppliersOrganizationOrderDetails&order_id="+order_id+"&des_order_id=<?php echo $results['SuppliersOrganization']['des_order_id'];?>&format=notmpl";
			var idDivTarget = 'suppliers_organization_details';
			ajaxCallBox(url, idDivTarget);		
		}
	}	

	$(document).ready(function() {
		var order_id = $("#order_id").val();
		if(order_id>0) {
			if (typeof choiceOrderPermission !== 'undefined' && typeof choiceOrderPermission === 'function')
				choiceOrderPermission();
		}
		
		suppliersOrganizationOrderDetails(<?php echo $results['Order']['id'];?>);
	});
	</script>

	</div>
</div> <!-- row -->	