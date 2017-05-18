<?php
echo $this->Html->script('generic.min');
	
if($user->organization['Organization']['type']=='GAS') {
	echo $this->Html->script('genericEcomm.min', array('date' => '2014nov'));
	echo $this->Html->script('genericFrontEnd.min', array('date' => '2014nov'));
}
else 
if($user->organization['Organization']['type']=='PROD') {
	echo $this->Html->script('genericEcommProd.min', array('date' => '2014nov'));
	echo $this->Html->script('genericFrontEndProd.min', array('date' => '2014nov'));
}	
?>
<script type="text/javascript">
//<![CDATA[
var app_img = "<?php echo Configure::read('App.img.cake');?>";

	jQuery(document).ready(function() {
			jQuery('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
			
			jQuery(function() {
				jQuery(".blank").attr("target","_blank");
			});
			
	        /* torna in alto */
    		jQuery("body").append("<div id=\"scroll_to_top\"><a href=\"#top\">Torna su</a></div>");
    		jQuery("#scroll_to_top a").css({	'display' : 'none', 'z-index' : '9', 'position' : 'fixed', 'top' : '80%', 'width' : '110px', 'margin-top' : '-30px', 'right' : '0', 'margin-left' : '-50px', 'height' : '20px', 'padding' : '3px 5px', 'font-size' : '14px', 'text-align' : 'center', 'padding' : '3px', 'color' : '#FFFFFF', 'background-color' : '#625043', '-moz-border-radius' : '5px', '-khtml-border-radius' : '5px', '-webkit-border-radius' : '5px', 'opacity' : '.8', 'text-decoration' : 'none'});
    		jQuery('#scroll_to_top a').click(function(){
				jQuery('html, body').animate({scrollTop:0}, 'slow');
			});

	        jQuery('.actionTrConfig').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	        jQuery('.actionTrConfig').each(function () {
	        	actionTrConfig(this);
			});
	});
		
    jQuery(function () {
		var scroll_timer;
		var displayed = false;
		var top = jQuery(document.body).children(0).position().top;
		jQuery(window).scroll(function () {
			window.clearTimeout(scroll_timer);
			scroll_timer = window.setTimeout(function () {
				if(jQuery(window).scrollTop() <= top)
				{
					displayed = false;
					jQuery('#scroll_to_top a').fadeOut(500);
				}
				else if(displayed == false)
				{
					displayed = true;
					jQuery('#scroll_to_top a').stop(true, true).show().click(function () { jQuery('#scroll_to_top a').fadeOut(500); });
				}
			}, 100);
		});
	});		
	//]]>
</script>

	<div class="cakeContainer">
		
		<div id="cart-short"></div>
	
		<div id="content">
		
			<?php if($this->Session->check('Message')) {
				$msg = $this->Session->flash();

				if(!empty($msg))  {
					echo '<div role="alert" class="alert alert-success">';
					echo '<a href="#" class="close" data-dismiss="alert">&times;</a>';
					echo $msg;
					echo '</div>';
				}
			} 

			echo $this->fetch('content'); ?>
		
		</div>
		
		<?php
		if(Configure::read('developer.mode')) {
			echo '<div role="alert" class="alert alert-warning">';
			echo '<a href="#" class="close" data-dismiss="alert">&times;</a>';
			echo __('developer.mode');
			echo '</div>';
		} 
		?>	
	</div>