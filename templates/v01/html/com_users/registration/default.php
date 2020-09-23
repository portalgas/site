<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

/*
 * override \components\com_users\models\forms\registration.xml
 */
//$this->form->reset( true ); // to reset the form xml loaded by the view
//$this->form->loadFile( dirname(__FILE__) . DS . "registration.xml"); // to load in our own version of login.xml
?>

<h2>Registrazione</h2>

<?php if ($this->params->get('show_page_heading')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
<?php endif; ?>


	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-horizontal form-validate" enctype="multipart/form-data" class="form form-inline" role="form">
		
	<div class="container">
		<div class="col-xs-12 col-sm-12 col-lg-8 col-md-8">
   
			
			<fieldset>
				<legend>Registrazione utente</legend>
				
						
		  
			 <div class="form-group">	
						   
				<select name="jform[organization_id]" id="jform_organization_id" aria-required="true" required="required" data-live-search="true" class="selectpicker-disabled form-control">
					<option selected="selected" value="" data-attr-active="">Faccio parte del G.A.S.</option>
					<?php
						$db = JFactory::getDbo();
						$rows = array();
						
						$sql = "SELECT 
									Organization.id, Organization.name, Organization.j_seo, Organization.mail,
									Organization.localita, Organization.provincia, Organization.paramsConfig   
								FROM
									k_organizations Organization
								WHERE
									Organization.stato = 'Y' and Organization.type = 'GAS' 
								ORDER BY Organization.name";
						//echo '<br />'.$sql;
						$db->setQuery($sql);
						if ($db->query())
							$rows = $db->loadObjectList();	

						foreach ($rows as $numResult => $item) {
						
							$paramsConfig = json_decode($item->paramsConfig, true);
							
							echo '<option data-attr-active="'.$paramsConfig['hasUsersRegistrationFE'].'" ';
							if($paramsConfig['hasUsersRegistrationFE']=='Y')
								echo ' value="'.$item->id.'"';
							else
								echo ' value="'.$item->id.'"'; // organization_id mi serve per inviare la mail
							echo '>'.$item->name;
							
							echo ' - '.$item->localita.' ('.$item->provincia.')';
							
							echo '</option>';
						}
					?>
				</select>
	  
		   
		 
				</div>
					
				<div id="form-msg" style="display:none;">	

					<div class="alert alert-info" role="alert"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<p>Per registrarsi al GAS scelto contattare il manger del GAS</p> 
						<p>
						<a id="link-mail" href="/contattaci?contactOrganizationId=" title="scrivi una mail al G.A.S.">contattalo scrivendo una mail</a>
					</div>
		
				</div>
					
				<div id="form-data" style="display:none;">	

					 <div class="form-group">	
						<!-- a title="" -->
						<label for="jform_spacer" class="col-xs-3-disabled"><span class="spacer"><span class="before"></span><span class="text"><label class="" id="jform_spacer-lbl"><strong class="red">*</strong> Campi richiesti</label></span><span class="after"></span></span>									</label></a>
						<div class="col-xs-9-disabled">
							&nbsp;					
						</div>
					</div>

					 <div class="form-group">	
						<label for="jform_name" class="col-xs-3-disabled">Nome:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9-disabled">
							<input type="text" size="30" class="required form-control" value="" id="jform_name" name="jform[name]" aria-required="true" required="required" placeholder="Nome">					
							<div class="alert alert-info">Sarà il nome che gli altri gasisti visualizzeranno</div>
						</div>
		 
					</div>
					
		        	<div class="jumbotron">
		                <h3>Dati del tuo account</h3>				
							 <div class="form-group">	
								<!-- a title="Inserisci il nome con il quale potrai autenticarti" -->
								<label for="jform_username" class="col-xs-3-disabled">Account:<span class="star">&nbsp;*</span></label>
								<div class="col-xs-9-disabled">
									<input type="text" size="30" class="validate-username required form-control" value="" id="jform_username" name="jform[username]" aria-required="true" required="required" placeholder="Account">
									<div class="alert alert-info">Inserisci il nome con il quale potrai autenticarti</div>						
								</div>
								<!-- /a -->
							</div>
										
							 <div class="form-group">	
								<!-- a title="Inserisci la password desiderata. Minimo 4 caratteri" -->
								<label for="jform_password1" class="col-xs-3-disabled">Password:<span class="star">&nbsp;*</span></label>
								<div class="col-xs-9-disabled">
									<input type="password" size="30" class="validate-password required form-control" autocomplete="off" value="" id="jform_password1" name="jform[password1]" aria-required="true" required="required" placeholder="Password">			
									<div class="alert alert-info">Inserisci la password desiderata. Minimo 4 caratteri</div>					
								</div>
								<!-- /a -->
							</div>
										
							 <div class="form-group">	
								<!-- a title="Conferma la tua password" -->
								<label for="jform_password2" class="col-xs-3-disabled">Conferma password:<span class="star">&nbsp;*</span></label>
								<div class="col-xs-9-disabled">
									<input type="password" size="30" class="validate-password required form-control" autocomplete="off" value="" id="jform_password2" name="jform[password2]" aria-required="true" required="required" placeholder="Conferma password">										
								</div>
								<!-- /a -->
							</div>
					 </div>
					
					 <div class="form-group">	
						<!-- a title="Inserisci il tuo indirizzo email: a questo indirizzo riceverai una mail per continuare la registrazione" -->
						<label for="jform_email1" class="col-xs-3-disabled">Indirizzo email:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9-disabled">
							<input type="email" size="40" value="" id="jform_email1" class="validate-email required form-control" name="jform[email1]" aria-required="true" required="required" placeholder="Indirizzo email">					
							<div class="alert alert-info">Inserisci il tuo indirizzo email: a questo indirizzo riceverai una mail per continuare la registrazione</div>						
						</div>
						<!-- /a -->
					</div>
							
					 <div class="form-group">	
						<!-- a title="Conferma il tuo indirizzo email" data-toggle="tooltip" data-placement="right" -->
						<label for="jform_email2" class="col-xs-3-disabled">Conferma indirizzo email:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9-disabled">
							<input type="email" size="40" value="" id="jform_email2" class="validate-email required form-control" name="jform[email2]" aria-required="true" required="required" placeholder="Conferma indirizzo email">					
						</div>
						<!-- /a -->
					</div>
																
					<legend>Profilo utente</legend>
					
					 <div class="form-group">	
						<!-- a title="" -->
						<label for="jform_profile_cf" class="col-xs-3-disabled">Codice fiscale:</label>
						<div class="col-xs-9-disabled">
							<input type="text" size="16" value="" id="jform_profile_cf" name="jform[profile][cf]" class="form-control" aria-invalid="false" placeholder="Codice fiscale">					
						</div>
						<!-- /a -->
					  </div>
										
					 <div class="form-group">	
						<!-- a title="" -->
						<label for="jform_profile_address" class="col-xs-3-disabled">Indirizzo:</label>
						<div class="col-xs-9-disabled">
							<input type="text" size="50" value="" id="jform_profile_address" name="jform[profile][address]" class="form-control" aria-invalid="false" placeholder="Indirizzo">					
						</div>
						<!-- /a -->
					</div>
								
					 <div class="form-group">	
						<!-- a title="Indica la tua Città di appartenenza." -->
						<label for="jform_profile_city" class="col-xs-3-disabled">Città:</label>
						
						<div class="col-xs-9-disabled">
							<input type="text" size="30" value="" id="jform_profile_city" name="jform[profile][city]" class="form-control" aria-invalid="false" placeholder="Città">					
						</div>
						<!-- /a -->
					</div>
									
					 <div class="form-group">	
						<!-- a title="" -->
						<label for="jform_profile_region" class="col-xs-3-disabled">Provincia:</label>
						
						<div class="col-xs-9-disabled">
							<input type="text" size="2" value="" id="jform_profile_region" name="jform[profile][region]" class="form-control" aria-invalid="false" placeholder="Provincia">					
						</div>
						<!-- /a -->
					</div>
													
					 <div class="form-group">	
						<!-- a title="" -->
						<label for="jform_profile_postal_code" class="col-xs-3-disabled">CAP:</label>
						
						<div class="col-xs-9-disabled">
							<input type="text" size="5" value="" id="jform_profile_postal_code" name="jform[profile][postal_code]" class="form-control" aria-invalid="false" placeholder="CAP">					
						</div>
						<!-- /a -->
					</div>
								
					 <div class="form-group">	
						<!-- a title="" -->
						<label for="jform_profile_email" class="col-xs-3-disabled">Altro indirizzo mail:</label>
						<div class="col-xs-9-disabled">
							<input type="text" size="30" class="invalid form-control" value="" id="jform_profile_email" name="jform[profile][email]" aria-invalid="true" placeholder="Altro indirizzo mail">					
						</div>
						<!-- /a -->
					</div>	
								
					 <div class="form-group">	
						<!-- a title="Non sarà visibile su PortAlGas ma solamente ai referenti che avranno necessità per gli ordini" -->
						<label for="jform_profile_phone" class="col-xs-3-disabled">Cellulare:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9-disabled">
							<input type="text" size="30" class="required invalid form-control" value="" id="jform_profile_phone" name="jform[profile][phone]" aria-required="true" required="required" aria-invalid="true" placeholder="Cellulare">
							<div class="alert alert-info">Non sarà visibile su PortAlGas ma solamente ai referenti che avranno necessità per gli ordini</div>						
						</div>
						<!-- /a -->
					</div>
				
					
					 <div class="form-group">	
						<!-- a title="Questa informazione potrebbe essere utile per effettuare i pagamenti" -->
						<label for="jform_profile_phone" class="col-xs-3-disabled">Satispay:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9-disabled">
							<label class="radio-inline"><input type="radio" name="satispay" class="required invalid" value="" id="jform_profile_satispay" name="jform[profile][satispay]" aria-required="true" required="required"  checked />No</label>
							<label class="radio-inline"><input type="radio" name="satispay" class="required invalid" value="" id="jform_profile_satispay" name="jform[profile][satispay]" aria-required="true" required="required" />Si</label>	
							<div class="alert alert-info">Questa informazione potrebbe essere utile per effettuare i pagamenti</div>		
						</div>
						<!-- /a -->
					</div>
				

						
					<div class="form-group">
						<div class="col-xs-offset-3 col-xs-9">
							<label class="checkbox-inline">
								<input type="checkbox" name="jform[profile][tos]" value="OK" id="jform[profile][tos]" class="" />  accetto i 
								<a title="Accettazione dei termini di utilizzo" rel="nofollow" data-toggle="modal" data-target="#myModal">termini di utilizzo e le condizioni</a>.
							</label>
						</div>
					</div>
					
					<input type="hidden" size="30" value="" id="jform_profile_country" name="jform[profile][country]" value="Italia" aria-invalid="false">					
		
					<!-- https://www.google.com/recaptcha/admin -->
					<script src='https://www.google.com/recaptcha/api.js'></script>
					<div class="g-recaptcha" data-sitekey="6LfGvQsUAAAAAJLXgcGb8MUueMTlXz6FtxkACxIx"></div>
					
				
				
				<br>
		        <div class="form-group">
		            <div class="col-xs-offset-3 col-xs-9">
		            	<input id="member-registration" class="btn btn-success" type="submit" value="<?php echo JText::_('JREGISTER');?>" />
						<?php echo JText::_('COM_USERS_OR');?>
						<a href="<?php echo JRoute::_('');?>" title="<?php echo JText::_('JCANCEL');?>"><?php echo JText::_('JCANCEL');?></a>
						<input type="hidden" name="option" value="com_users" />
						<input type="hidden" name="task" value="registration.register" />
						<?php echo JHtml::_('form.token');?>
		           </div>
		        </div>
				
		</div>

	</div> <!-- id="form-data" -->

	<div class="col-xs-4 hidden-sm hidden-xs col-lg-4 col-md-4">
		<img align="middle" class="img-responsive hidden-sm hidden-xs" alt="Immagine-contatto" src="/images/monitor2.png" />
	</div>	
	
		
	</fieldset>
		
</div> <!-- class="container" -->
		
</form>



<div id="myModal" class="modal fade">
 <div class="modal-dialog modal-lg">
  <div class="modal-content">
   <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title">&nbsp;</h4>
   </div>
   <div class="modal-body">
   </div>
   <div class="modal-footer">
    <button type="button" class="btn btn-success" data-dismiss="modal">Chiudi</button>
   </div> 
  </div>
 </div>
</div>

<style>
#member-registration .alert {
	margin-top: 10px;
    width: 75%;
    float: right;	
}
.form-horizontal .jumbotron .form-group {
	margin-right: -0px;
    margin-left: 0px;	
}
</style>
<script type="text/javascript">
$(document).ready(function() {

	$('#member-registration').submit(function() {

		var organization_id = $('#jform_organization_id').val();
		if(organization_id=="0" || organization_id=="") {
			alert("Devi indicare il G.A.S. di appartenenza");
			return false;
		}
		
		var accettaTermini = $("[name='jform[profile][tos]']:checked").val();
		if(accettaTermini==undefined || accettaTermini=="") {
			alert("Leggi i termini di utilizzo e accetta le condizioni");
			return false;
		}
		else
		if(accettaTermini=="0") {
			alert("Per poter proseguire con la registrazione devi accettare le condizioni dei termini di utilizzo");
			return false;
		}
		else
			return true;
	});

	$("select[name='jform[organization_id]']").change(function() {	
		
		var select = $("#jform_organization_id option:selected");
		var data_attr_active = $(select).attr('data-attr-active');
		
		/* console.log(data_attr_active); */

		if(data_attr_active=='') {
			$('#form-data').hide();
			$('#form-msg').hide();
		}
		else
		if(data_attr_active=='N') {
			$('#form-data').hide();
			$('#form-msg').show();
		}
		else 
		if(data_attr_active=='Y') {
			$('#form-data').show();
			$('#form-msg').hide();
		}
		else {
			$('#form-data').hide();
			$('#form-msg').hide();		
		}
	}); 

	$('#link-mail').click(function(event) {	
		/* event.preventDefault(); */
		var organization_id = $("#jform_organization_id option:selected").val();
		console.log(organization_id);
		if(organization_id>0) {
			 var url = $(this).attr('href');
			 /* console.log(url); */
			 url = url + organization_id;
			 $(this).attr('href', url);

			 return true;
		}	
		return false;
	});
	
	$('.selectpicker').selectpicker({
		style: 'btn-default'
	});
		
	$('#jform_profile_region').keyup(function(){
		this.value = this.value.toUpperCase();
	});

	/* $('a').tooltip(); */

	$('#myModal').on('show.bs.modal', function (e) {
		var url = "/component/content/article?layout=modal&id=2&tmpl=component";
		$(".modal-body").load(url).animate({ opacity: 1}, 750);
	})
});
</script>