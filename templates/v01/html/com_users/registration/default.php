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


	<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=registration.register'); ?>" method="post" class="form-horizontal form-validate" enctype="multipart/form-data">
		
	<div class="container">
		<div class="col-xs-8">
			

			<fieldset>
					<legend>Registrazione utente</legend>
					
					
       			
			 <div class="form-group">	
				<a title="">
				<label for="jform_spacer" class="col-xs-3"><span class="spacer"><span class="before"></span><span class="text"><label class="" id="jform_spacer-lbl"><strong class="red">*</strong> Campi richiesti</label></span><span class="after"></span></span>									</label></a>
				<div class="col-xs-9">
					&nbsp;					
				</div>
			</div>
						
			 <div class="form-group">	
				
				<a title="">
				<label for="jform_organization_id" class="col-xs-3">Faccio parte del G.A.S<span class="star">&nbsp;*</span></label>
				<div class="col-xs-9">
					<select name="jform[organization_id]" id="jform_organization_id" aria-required="true" required="required" data-live-search="true" class="selectpicker required">
						<option selected="selected" value="0">Faccio parte del G.A.S.</option>
						<?php
							$db = JFactory::getDbo();
							$rows = array();
							
							$sql = "SELECT 
										Organization.id, Organization.name, Organization.j_seo, 
										Organization.localita, Organization.provincia   
									FROM
										k_organizations Organization
									WHERE
										Organization.stato = 'Y'
									ORDER BY Organization.name";
							//echo '<br />'.$sql;
							$db->setQuery($sql);
							if ($db->query())
								$rows = $db->loadObjectList();	

							foreach ($rows as $item) {
								echo '<option ';
								echo ' value="'.$item->id.'">'.$item->name;
								
								echo ' - '.$item->localita.' ('.$item->provincia.')';
								
								echo '</option>';
							}
						?>
					</select>
					
				</div>
				</a>
			</div>
						
			 <div class="form-group">	
				<a title="Sarà il nome che gli altri gasisti visualizzeranno">
				<label for="jform_name" class="col-xs-3">Nome:<span class="star">&nbsp;*</span></label>
				<div class="col-xs-9">
					<input type="text" size="30" class="required" value="" id="jform_name" name="jform[name]" aria-required="true" required="required">					
				</div>
				</a>
			</div>
				
        	<div class="jumbotron">
                <h3>Dati del tuo account</h3>				
					 <div class="form-group">	
						<a title="Inserisci il nome con il quale potrai autenticarti">
						<label for="jform_username" class="col-xs-3">Account:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9">
							<input type="text" size="30" class="validate-username required" value="" id="jform_username" name="jform[username]" aria-required="true" required="required">					
						</div>
						</a>
					</div>
								
					 <div class="form-group">	
						<a title="Inserisci la password desiderata. Minimo 4 caratteri">
						<label for="jform_password1" class="col-xs-3">Password:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9">
							<input type="password" size="30" class="validate-password required" autocomplete="off" value="" id="jform_password1" name="jform[password1]" aria-required="true" required="required">					
						</div>
						</a>
					</div>
								
					 <div class="form-group">	
						<a title="Conferma la tua password">
						<label for="jform_password2" class="col-xs-3">Conferma password:<span class="star">&nbsp;*</span></label>
						<div class="col-xs-9">
							<input type="password" size="30" class="validate-password required" autocomplete="off" value="" id="jform_password2" name="jform[password2]" aria-required="true" required="required">					
						</div>
						</a>
					</div>
			 </div>
			
			 <div class="form-group">	
				<a title="Inserisci il tuo indirizzo email: a questo indirizzo riceverai una mail per continuare la registrazione">
				<label for="jform_email1" class="col-xs-3">Indirizzo email:<span class="star">&nbsp;*</span></label>
				<div class="col-xs-9">
					<input type="email" size="40" value="" id="jform_email1" class="validate-email required" name="jform[email1]" aria-required="true" required="required">					
				</div>
				</a>
			</div>
						
			 <div class="form-group">	
				<a title="Conferma il tuo indirizzo email" data-toggle="tooltip" data-placement="right">
				<label for="jform_email2" class="col-xs-3">Conferma indirizzo email:<span class="star">&nbsp;*</span></label>
				<div class="col-xs-9">
					<input type="email" size="40" value="" id="jform_email2" class="validate-email required" name="jform[email2]" aria-required="true" required="required">					
				</div>
				</a>
			</div>
															
		</fieldset>
		
			
		<fieldset class="" aria-invalid="false">
			<legend>Profilo utente</legend>
					
			 <div class="form-group">	
				<a title="">
				<label for="jform_profile_address" class="col-xs-3">Indirizzo:</label>
				</a><div class="col-xs-9">
					<input type="text" size="50" value="" id="jform_profile_address" name="jform[profile][address]" class="" aria-invalid="false">					
				</div>
				</a>
			</div>
						
			 <div class="form-group">	
				<a title="Indica la tua Città di appartenenza.">
				<label for="jform_profile_city" class="col-xs-3">Città:</label>
				
				<div class="col-xs-9">
					<input type="text" size="30" value="" id="jform_profile_city" name="jform[profile][city]" class="" aria-invalid="false">					
				</div>
				</a>
			</div>
						
			 <div class="form-group">	
				<a title="">
				<label for="jform_profile_region" class="col-xs-3">Provincia:</label>
				
				<div class="col-xs-9">
					<input type="text" size="2" value="" id="jform_profile_region" name="jform[profile][region]" class="" aria-invalid="false">					
				</div>
				</a>
			</div>
												
			 <div class="form-group">	
				<a title="">
				<label for="jform_profile_postal_code" class="col-xs-3">CAP:</label>
				
				<div class="col-xs-9">
					<input type="text" size="5" value="" id="jform_profile_postal_code" name="jform[profile][postal_code]" class="" aria-invalid="false">					
				</div>
				</a>
			</div>
						
			 <div class="form-group">	
				<a title="Non sarà visibile su PortAlGas ma solamente ai referenti che avranno necessità per gli ordini">
				<label for="jform_profile_phone" class="col-xs-3">Cellulare:<span class="star">&nbsp;*</span></label>
				<div class="col-xs-9">
					<input type="text" size="30" class="required invalid" value="" id="jform_profile_phone" name="jform[profile][phone]" aria-required="true" required="required" aria-invalid="true">					
				</div>
				</a>
			</div>
						
			<div class="form-group">
				<div class="col-xs-offset-3 col-xs-9">
					<label class="checkbox-inline">
						<input type="checkbox" name="jform[profile][tos]" value="OK" id="jform[profile][tos]" />  accetto i 
						<a title="Accettazione dei termini di utilizzo" rel="nofollow" data-toggle="modal" data-target="#myModal">termini di utilizzo e le condizioni</a>.
					</label>
				</div>
			</div>
			
			<input type="hidden" size="30" value="" id="jform_profile_country" name="jform[profile][country]" value="Italia" aria-invalid="false">					

			<!-- https://www.google.com/recaptcha/admin -->
			<script src='https://www.google.com/recaptcha/api.js'></script>
			<div class="g-recaptcha" data-sitekey="6LfGvQsUAAAAAJLXgcGb8MUueMTlXz6FtxkACxIx"></div>
			
		</fieldset>
		
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
	<div class="col-xs-4 hidden-sm hidden-xs">
		<img align="middle" class="img-responsive hidden-sm hidden-xs" alt="Immagine-contatto" src="/images/monitor2.png" />
	</div>		
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


<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#member-registration').submit(function() {

		var organization_id = jQuery('#jform_organization_id').val();
		if(organization_id=="0") {
			alert("Devi indicare il G.A.S. di appartenenza");
			return false;
		}
		
		var accettaTermini = jQuery("[name='jform[profile][tos]']:checked").val();
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

	jQuery('.selectpicker').selectpicker({
			style: 'btn-default'
	});
		
	jQuery('#jform_profile_region').keyup(function(){
		this.value = this.value.toUpperCase();
	});

	jQuery('a').tooltip();
	
	jQuery('#myModal').on('show.bs.modal', function (e) {
		var url = "/component/content/article?layout=modal&id=2&tmpl=component";
		jQuery(".modal-body").load(url).animate({ opacity: 1}, 750);
	})
});
</script>