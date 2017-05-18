<?php
if(isset($prod_delivery_id) && ($prod_delivery_id>0)) { 
?>
	<div class="menuLaterale">
		<a class="menuLateraleClose"></a>
		<div id="prod-delivery-sotto-menu-<?php echo $prod_delivery_id;?>" class="prod-delivery-sotto-menu-unique"></div>
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

	viewProdDeliverySottoMenu(<?php echo $prod_delivery_id;?>, "bgRight");
	</script>
<?php 
}
?>