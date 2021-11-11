<?php
if(isset($order_id) && ($order_id>0)) {
?>
	<div class="menuLaterale hidden-xs">
		<a class="menuLateraleClose"></a>
		<div id="order-sotto-menu-<?php echo $order_id;?>" class="order-sotto-menu-unique"></div>
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

	viewOrderSottoMenu(<?php echo $order_id;?>, "bgRight");
	</script>
<?php 
}
?>