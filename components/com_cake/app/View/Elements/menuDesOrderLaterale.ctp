<?php
if(isset($des_order_id) && ($des_order_id>0)) { 
?>
	<div class="menuLaterale">
		<a class="menuLateraleClose"></a>
		<div id="des-order-sotto-menu-<?php echo $des_order_id;?>" class="des-order-sotto-menu-unique"></div>
	</div>
	<script type="text/javascript">
	var menuDesOrderLateraleOpen = false;
	jQuery(document).ready(function() {
		jQuery('.menuLaterale').mouseenter(function() {
			if(menuDesOrderLateraleOpen) return;

			jQuery('.menuLaterale').animate({left: '+=270'});
			jQuery('.menuLateraleClose').css('display','block');
			menuDesOrderLateraleOpen = true;
		});	

		jQuery('.menuLateraleClose').click(function() {
			jQuery('.menuLateraleClose').css('display','none');
			jQuery('.menuLaterale').animate({left: '-=270'}, 'fast');
		
			menuDesOrderLateraleOpen = false;
		});
		
		jQuery('.menuLaterale-disalbled').mouseleave(function() {
			if(!menuDesOrderLateraleOpen) return;
			
			jQuery('.menuLaterale').animate({left: '-=270'}, 'fast');
			menuDesOrderLateraleOpen = false;
		});	
	});

	viewDesOrderSottoMenu(<?php echo $des_order_id;?>, "bgRight");
	</script>
<?php 
}
?>