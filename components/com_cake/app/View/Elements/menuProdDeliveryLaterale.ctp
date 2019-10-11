<?php
if(isset($prod_delivery_id) && ($prod_delivery_id>0)) { 
?>
	<div class="menuLaterale">
		<a class="menuLateraleClose"></a>
		<div id="prod-delivery-sotto-menu-<?php echo $prod_delivery_id;?>" class="prod-delivery-sotto-menu-unique"></div>
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

	viewProdDeliverySottoMenu(<?php echo $prod_delivery_id;?>, "bgRight");
	</script>
<?php 
}
?>