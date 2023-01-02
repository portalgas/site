<?php
echo $this->Html->script('generic-v04.min');
	
if($user->organization['Organization']['type']=='GAS') {
	echo $this->Html->script('genericEcomm-v03.min');
	echo $this->Html->script('genericFrontEnd-v01.min');
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
var now = new Date();
var time = now.getTime();

$(function () {
	$('.double').focusout(function() {setNumberFormat(this);});  /* applicato a tutti i campi prezzo */
	
	$(function() {
		$(".blank").attr("target","_blank");
	});
	
	/* torna in alto */
	$("body").append("<div id=\"scroll_to_top\"><a href=\"#top\">Torna su</a></div>");
	$("#scroll_to_top a").css({	'display' : 'none', 'z-index' : '9', 'position' : 'fixed', 'top' : '80%', 'width' : '110px', 'margin-top' : '-30px', 'right' : '0', 'margin-left' : '-50px', 'height' : '20px', 'padding' : '3px 5px', 'font-size' : '14px', 'text-align' : 'center', 'padding' : '3px', 'color' : '#FFFFFF', 'background-color' : '#625043', '-moz-border-radius' : '5px', '-khtml-border-radius' : '5px', '-webkit-border-radius' : '5px', 'opacity' : '.8', 'text-decoration' : 'none'});
	$('#scroll_to_top a').click(function(){
		$('html, body').animate({scrollTop:0}, 'slow');
	});

	$('.actionTrConfig').css('display','inline-block');  /* rendo visibile il tasto espandi per i dettagli ajax */
	$('.actionTrConfig').each(function () {
		actionTrConfig(this);
	});

	var scroll_timer;
	var displayed = false;
	var top = $(document.body).children(0).position().top;
	$(window).scroll(function () {
		window.clearTimeout(scroll_timer);
		scroll_timer = window.setTimeout(function () {
			if($(window).scrollTop() <= top)
			{
				displayed = false;
				$('#scroll_to_top a').fadeOut(500);
			}
			else if(displayed == false)
			{
				displayed = true;
				$('#scroll_to_top a').stop(true, true).show().click(function () { $('#scroll_to_top a').fadeOut(500); });
			}
		}, 100);
	});
});		
//]]>
</script>
<?php 
echo '<div class="cakeContainer">';
		
echo '<div id="cart-short"></div>';
	
echo '<div id="content">';
		
if($this->Session->check('Message')) {
	$msg = $this->Session->flash();

	if(!empty($msg))  {
		echo '<div role="alert" class="alert alert-success">';
		echo '<a href="#" class="close" data-dismiss="alert">&times;</a>';
		echo $msg;
		echo '</div>';
	}
} 

echo $this->fetch('content'); 

echo '</div>';  // content

if(Configure::read('developer.mode')) {
echo '<div role="alert" class="alert alert-warning">';
echo '<a href="#" class="close" data-dismiss="alert">&times;</a>';
echo __('developer.mode');
echo '</div>';
} 
	
echo '</div>';  // cakeContainer

if(isset($hasUserRegistrationExpire) && $hasUserRegistrationExpire=='N') {
	echo $this->Html->script('jquery/jquery.cookie');
?>
<script type="text/javascript">

		var html =  '<div class="modal fade" id="modalWindow" role="dialog">';
		html += '<div class="modal-dialog">';
		html += '<div class="modal-content">';
		html += '<div class="modal-header">';
		html += '<button type="button" class="close" data-dismiss="modal">&times;</button>';
		html += '<h4 class="modal-title">Messaggio</h4>';
		html += '</div>';
		html += '<div class="modal-body">';
		html += '<p>';
		html += "<?php echo __('msg_fe_user_registration_expire_modal');?>";
		html += '</div>';
		html += '<div class="modal-footer">';
		html += '<button type="button" class="btn btn-warning" data-dismiss="modal"><?php echo __('Chiudi');?></button>'; 
		html += '</div>'; 
		html += '</div>'; 
		
		$(html).appendTo('body');
		$("#modalWindow").modal('show');
		
        $("#modalWindow").on("hide.bs.modal", function () {

            $.cookie("<?php echo Configure::read('Cookies.user.registration.expire');?>", "<?php echo $user->id;?>", { expires: <?php echo Configure::read('Cookies.expire');?>, path: '<?php echo Configure::read('Cookies.path');?>/' });

			$("#modalWindow").detach();
        });		
</script>
<?php
}