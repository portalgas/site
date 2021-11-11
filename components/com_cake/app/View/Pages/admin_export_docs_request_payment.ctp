<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
if($isReferenteTesoriere)  {
	$this->Html->addCrumb(__('List Orders'), array('controller' => 'Orders', 'action' => 'index'));
	if(isset($order_id))
		$this->Html->addCrumb(__('Order home'),array('controller'=>'Orders','action'=>'home', null, 'order_id='.$order_id));
}
else {
	if(!isset($delivery_id)) $delivery_id = 0;
		$this->Html->addCrumb(__('Tesoriere'),array('controller' => 'Tesoriere', 'action' => 'home', $delivery_id));
}
$this->Html->addCrumb(__('List Request Payments'), array('controller' => 'RequestPayments', 'action' => 'index'));
$this->Html->addCrumb(__('Request Payments Printer'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale form">';

	echo '<h2 class="ico-pay">';
	echo __('request_payment_num').' '.$requestPaymentResults['RequestPayment']['num'].' di '.$tot_importo.' &euro; ('.$this->Time->i18nFormat($requestPaymentResults['RequestPayment']['created'],"%A %e %B %Y").')';
	echo '<span style="float:right;">';
	echo $this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).' <span style="padding-left: 20px;" title="'.$this->App->traslateEnum('REQUEST_PAYMENT_STATO_ELABORAZIONE_'.$requestPaymentResults['RequestPayment']['stato_elaborazione']).'" class="stato_'.strtolower($requestPaymentResults['RequestPayment']['stato_elaborazione']).'"></span>';
	echo '</span>';
	echo '</h2>';
    ?>
    
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th>Tipologia di documento</th>
		<th></th>
		<th>Formato pdf</th>
		<th>Formato excel</th>
	</tr>
	<tr>
		<td>Esportazione <b>completa</b></td>
		<td></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="tesoriere_request_payment" id="tesoriere_request_payment-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<tr>
		<td>Stato <b>pagamenti</b></td>
		<td></td>
		<td></td>
		<td>
			<?php
			if(Configure::read('developer.mode'))
				echo 'No in developer mode';
			else
				echo '<a class="tesoriere_request_payment_pagamenti" id="tesoriere_request_payment_pagamenti-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa stato pagamentila richiesta di pagamento '.__('formatFileExcel').'"><img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png"></a>';
			?>
		</td>		
	</tr>
	<?php 
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		if($isRoot || $isManager || $isTesoriereGeneric) {
	?>
		<tr>
			<td>La <b>richiesta di pagamento</b> dell'utente</td>
			<td style="vertical-align: middle;">
				<?php
					echo $this->Form->input('user_id',array('label' => false, 'id' => 'request_payment_user_id',
																'class'=> 'selectpicker', 'data-live-search' => true,
																'empty' => 'Scegli l\'utente','escape' => false));
	
					echo '<br />';
				?>
			<td><a class="exportRequestPayment" id="userOtherRequestPayment-PDF" style="cursor:pointer;" rel="nofollow" title="stampa la richiesta di pagamento dell'utente scelto <?php echo __('formatFilePdf');?>"><img alt="PDF" src="<?php echo Configure::read('App.img.cake');?>/minetypes/32x32/pdf.png"></a></td>
			<td></td>
		</tr>
	<?php 
		} 
	}
		
	echo '</table>';
	
	echo '<div class="clearfix" id="doc-preview" style="display:none;"></div>';
	
echo '</div>'; // end contentMenuLaterale

$options = [];
echo $this->MenuRequestPayment->drawWrapper($requestPaymentResults['RequestPayment']['id'], $options);
?>


<script type="text/javascript">
var idDivTarget = 'doc-preview';
var url = "";

$(document).ready(function() {

	$('.tesoriere_request_payment').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];
		
		
		url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&request_payment_id=<?php echo $requestPaymentResults['RequestPayment']['id'];?>&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
		window.open(url);
	});
	
	$('.tesoriere_request_payment_pagamenti').click(function() {	
		var id =  $(this).attr('id');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];
		
		
		url = '/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&request_payment_id=<?php echo $requestPaymentResults['RequestPayment']['id'];?>&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
		window.open(url);
	});	
	
	<?php
	if($user->organization['Template']['payToDelivery']=='POST' || $user->organization['Template']['payToDelivery']=='ON-POST') {
		if($isRoot || $isManager|| $isTesoriereGeneric) {
	?>
		$('.exportRequestPayment').click(function() {
			var user_id = $('#request_payment_user_id').val();
			if(user_id=="") {
				alert("<?php echo __('jsAlertUserRequired');?>");
				return false;
			}
			
			var request_payment_id = <?php echo $requestPaymentResults['RequestPayment']['id'];?>;
			
			var id =  $(this).attr('id');
			idArray = id.split('-');
			var action          = idArray[0];
			var doc_formato = idArray[1];
	
			window.open('/administrator/index.php?option=com_cake&controller=ExportDocs&action='+action+'&request_payment_id='+request_payment_id+'&user_id='+user_id+'&doc_formato='+doc_formato+'&format=notmpl','win2','status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
		});
	<?php
		}
	}
	?>		

});
</script>