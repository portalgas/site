<?php
if(!isset($delivery_id)) $delivery_id=0; 

if(isset($requestPaymentResults['RequestPayment']['id'])) {
?>
	<div class="menuLaterale">
		<a class="menuLateraleClose"></a>
		<div id="order-sotto-menu-<?php echo $delivery_id;?>" class="order-sotto-menu-unique"></div>
	</div>
	<script type="text/javascript">
	var menuOrderLateraleOpen = false;
	jQuery(document).ready(function() {
		jQuery('.menuLaterale').mouseenter(function() {
			if(menuOrderLateraleOpen) return;

			jQuery('.menuLaterale').animate({left: '+=270'});
			jQuery('.menuLateraleClose').css('display','block');
			menuOrderLateraleOpen = true;
		});	

		jQuery('.menuLateraleClose').click(function() {
			jQuery('.menuLateraleClose').css('display','none');
			jQuery('.menuLaterale').animate({left: '-=270'}, 'fast');
		
			menuOrderLateraleOpen = false;
		});
		
		jQuery('.menuLaterale-disalbled').mouseleave(function() {
			if(!menuOrderLateraleOpen) return;
			
			jQuery('.menuLaterale').animate({left: '-=270'}, 'fast');
			menuOrderLateraleOpen = false;
		});	
	});

	<?php
	if($isReferenteTesoriere) 
		echo 'viewReferenteTesoriereRequestPaymentSottoMenu('.$delivery_id.', '.$requestPaymentResults['RequestPayment']['id'].', "bgRight");';
	else
		echo 'viewTesoriereRequestPaymentSottoMenu('.$delivery_id.', '.$requestPaymentResults['RequestPayment']['id'].', "bgRight");';
	?>
	
	</script>
<?php
}
?>