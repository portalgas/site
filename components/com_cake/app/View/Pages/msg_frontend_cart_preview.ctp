<div style="float:left;padding: 10px;">
	<img alt="exclamation" src="<?php echo Configure::read('App.img.cake'); ;?>/msg_exclamation.png" style="float: none;" />
</div>	

	<h1>Errore nel passaggio dei parametri!</h1>
	Effettua la  
	<input type="submit" name="cartPreview" value="Login" class="btn btn-orange cartPreview" id="btn-account">
	per visualizzare i dati del tuo carrello e<br /><br />contatta l'amministratore del sistema all'indirizzo <a title="Scrivi a <?php echo Configure::read('SOC.mail-assistenza');?>" href="mailto:<?php echo Configure::read('SOC.mail-assistenza');?>"><?php echo Configure::read('SOC.mail-assistenza');?></a> per segnalare il problema, grazie. 
	
<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('.cartPreview').click(function () {

		jQuery('html, body').animate({scrollTop:0}, 'slow');		
		jQuery('#box-account-dashboard').show();
	});
	
});	
</script>	