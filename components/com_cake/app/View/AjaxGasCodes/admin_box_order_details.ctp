<?php 
echo '<div class="box-details">';
if (!empty($results)) {

	echo '<table cellpadding = "0" cellspacing = "0">';
	echo '<tr>';
	if($user->organization['Organization']['hasDes']=='Y' && !empty($desOrdersOrganizationResults)) {
		echo '<th></th>';
	}	
	echo '	<th>'.__('StateOrder').'</th>';
	echo '  <th>'.__('stato_elaborazione').'</th>';
	if($user->organization['Organization']['hasVisibility']=='Y')
		echo '<th>'.__('isVisibleFrontEnd').'</th>';
	echo '</tr>';
	echo '<tr>';
	if($user->organization['Organization']['hasDes']=='Y' && !empty($desOrdersOrganizationResults)) {
		echo '<td>';
		if($desOrdersOrganizationResults['DesOrdersOrganization']['des_id']==$user->des_id) {
			echo '<a title="" href="/administrator/index.php?option=com_cake&amp;controller=DesOrdersOrganizations&amp;action=index&amp;des_order_id='.$desOrdersOrganizationResults['DesOrdersOrganization']['des_order_id'].'">';
			echo '<span class="ico-order-is-des" title="'.__('OrderIsDes').'"></span></a>';	
		}
		else
			echo '<span class="ico-order-is-des" title="'.__('OrderIsDes').'"></span>';
		echo '</td>';
	}	
	echo '<td style="white-space:nowrap;">';
	echo $this->App->utilsCommons->getOrderTime($results['Order']);
	echo '</td>';
	echo '<td>'.__($results['Order']['state_code'].'-label');
	echo $this->App->drawOrdersStateDiv($results);
	echo '</td>';
	if($user->organization['Organization']['hasVisibility']=='Y')
		echo '<td title="'.__('toolTipsVisibleFrontEnd').'" class="stato_'.$this->App->traslateEnum($results['Order']['isVisibleFrontEnd']).'"></td>';
	echo '</tr>';
	echo '</table>';
	?>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		var order_id = jQuery("#order_id").val();
		if(order_id>0)	choiceOrderDetails(); 
	});
	</script>		
<?php 
}
else  
	echo $this->element('boxMsg',array('class_msg' => 'message', 'msg' => "Dettaglio ordine non trovato!"));
echo '</div>';
?>