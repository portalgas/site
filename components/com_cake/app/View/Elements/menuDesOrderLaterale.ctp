<?php
if(isset($des_order_id) && ($des_order_id>0)) { 
?>
	<div class="menuLaterale hidden-xs">
		<a class="menuLateraleClose"></a>
		<div id="des-order-sotto-menu-<?php echo $des_order_id;?>" class="des-order-sotto-menu-unique"></div>
	</div>
	<script type="text/javascript">
	var menuDesOrderLateraleOpen = false;
	$(document).ready(function() {
		$('.menuLaterale').mouseenter(function() {
			if(menuDesOrderLateraleOpen) return;

			$('.menuLaterale').animate({left: '+=230'});
			$('.menuLateraleClose').css('display','block');
			menuDesOrderLateraleOpen = true;
		});	

		$('.menuLateraleClose').click(function() {
			$('.menuLateraleClose').css('display','none');
			$('.menuLaterale').animate({left: '-=230'}, 'fast');
		
			menuDesOrderLateraleOpen = false;
		});
		
		$('.menuLaterale-disalbled').mouseleave(function() {
			if(!menuDesOrderLateraleOpen) return;
			
			$('.menuLaterale').animate({left: '-=230'}, 'fast');
			menuDesOrderLateraleOpen = false;
		});	
	});

	viewDesOrderSottoMenu(<?php echo $des_order_id;?>, "bgRight");
	</script>
<?php 
}
?>