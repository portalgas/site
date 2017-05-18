<?php
$mail_order_close = 'N';
$mail_order_data_fine = 'N';

if(isset($results['MonitoringSuppliersOrganization']['mail_order_close'])) {
	$mail_order_close = $results['MonitoringSuppliersOrganization']['mail_order_close'];
}
if(isset($results['MonitoringSuppliersOrganization']['mail_order_data_fine'])) {
	$mail_order_data_fine = $results['MonitoringSuppliersOrganization']['mail_order_data_fine'];
}

/*
echo '<p>';
echo '<label for="mail_order_close" style="width:auto !important;">'.__('MonitoringMailOrderClose').'</label>&nbsp;&nbsp;';
echo '   <input type="radio" name="data[MonitoringSuppliersOrganization][mail_order_close]" id="mail_order_close_N" value="N" ';
if($mail_order_close=='N') echo 'checked';
echo '/> '.__('NO');
echo '   <input type="radio" name="data[MonitoringSuppliersOrganization][mail_order_close]" id="mail_order_close_Y" value="Y" ';
if($mail_order_close=='Y') echo 'checked';
echo '/> '.__('Y');
echo '</p>';
 */


echo '<p>';
echo '<label for="mail_order_close" style="width:auto !important;">'.__('MonitoringMailOrderDataFine').'</label>&nbsp;&nbsp;';
echo '   <input type="radio" name="data[MonitoringSuppliersOrganization][mail_order_data_fine]" id="mail_order_data_fine_N" value="N" ';
if($mail_order_data_fine=='N') echo 'checked';
echo '/> '.__('NO');
echo '   <input type="radio" name="data[MonitoringSuppliersOrganization][mail_order_data_fine]" id="mail_order_data_fine_Y" value="Y" ';
if($mail_order_data_fine=='Y') echo 'checked';
echo '/> '.__('Y');
echo '</p>';
?>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('.submit').css('display','block');
});
</script>