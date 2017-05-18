<?php
if(isset($order_id) && ($order_id>0)) {
?>
	<div class="menuLaterale">
		<a class="menuLateraleClose"></a>
		<div id="order-sotto-menu-<?php echo $order_id;?>" class="order-sotto-menu-unique"></div>
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

	viewOrderSottoMenu(<?php echo $order_id;?>, "bgRight");
	</script>
<?php 
}
?>