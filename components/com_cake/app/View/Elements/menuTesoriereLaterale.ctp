<?php
if(!isset($delivery_id) || empty($delivery_id)) $delivery_id=0; 
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
		echo 'viewReferenteTesoriereSottoMenu('.$delivery_id.', "bgRight");';
	else
		echo 'viewTesoriereSottoMenu('.$delivery_id.', "bgRight");';
	?>
	
	</script>