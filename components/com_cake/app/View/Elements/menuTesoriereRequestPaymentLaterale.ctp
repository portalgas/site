<?php
/*
 * non + utilizzato, sostituito da MenuRequestPaymentHelper
 */
if(!isset($delivery_id) || empty($delivery_id)) $delivery_id=0; 

if(isset($requestPaymentResults['RequestPayment']['id'])) {
?>
	<div class="menuLaterale">
		<a class="menuLateraleClose"></a>
		<div id="order-sotto-menu-<?php echo $delivery_id;?>" class="order-sotto-menu-unique"></div>
	</div>
	<script type="text/javascript">
	var menuOrderLateraleOpen = false;
	$(document).ready(function() {
		$('.menuLaterale').mouseenter(function() {
			if(menuOrderLateraleOpen) return;

			$('.menuLaterale').animate({left: '+=230'});
			$('.menuLateraleClose').css('display','block');
			menuOrderLateraleOpen = true;
		});	

		$('.menuLateraleClose').click(function() {
			$('.menuLateraleClose').css('display','none');
			$('.menuLaterale').animate({left: '-=230'}, 'fast');
		
			menuOrderLateraleOpen = false;
		});
		
		$('.menuLaterale-disalbled').mouseleave(function() {
			if(!menuOrderLateraleOpen) return;
			
			$('.menuLaterale').animate({left: '-=230'}, 'fast');
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