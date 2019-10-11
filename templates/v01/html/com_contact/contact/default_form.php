<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');

/*
 * dalla home pg del GAS gli passo l'organization_id
 */
$debug = false; 
$contactOrganizationId  = 0;
$contactOrganizationId = JRequest::getVar('contactOrganizationId');
if($debug)
	echo '<h1>com_contact  '.$contactOrganizationId.'</h1>';
if(!empty($contactOrganizationId)) {
	
		$db = JFactory::getDbo();
		$rows = array();
		
		$sql = "SELECT 
					Organization.id, Organization.name, Organization.j_seo, 
					Organization.localita, Organization.provincia, Organization.mail    
				FROM
					k_organizations Organization
				WHERE
					Organization.stato = 'Y' and 
					Organization.id = ".$contactOrganizationId." 
				ORDER BY Organization.name";
		if($debug)
			echo '<br />'.$sql;
		$db->setQuery($sql);
		if ($db->query())
			$organization = $db->loadAssoc();
		if($debug) {
			echo "<pre>";
			print_r($organization);
			echo "</pre>";
		}
}


if (isset($this->error)) :
    ?>
    <div class="contact-error">
        <?php echo $this->error; ?>
    </div>
<?php endif; ?>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("input:radio[name=w]").change(function () {
            /*console.log(jQuery(this).attr('data-attr'));*/
            jQuery('.collapse').removeClass("in");
            jQuery('#' + jQuery(this).attr('data-attr')).addClass("in");
        });
    });

    function ctrl_form() {
		
		<?php
		if(empty($contactOrganizationId)) {
		?>
        if (jQuery("#jform_contact_organization_id").val() == '') {
            alert(jQuery("#jform_organization_id").attr("data-validation-required-message"));
            return false;
        }
		<?php
		}
		?>
        if (jQuery("#jform_contact_name").val() == '') {
            alert(jQuery("#jform_contact_name").attr("data-validation-required-message"));
            return false;
        }
        if (jQuery("#jform_contact_email").val() == '') {
            alert(jQuery("#jform_contact_email").attr("data-validation-required-message"));
            return false;
        }
        if (!validateEmail(jQuery("#jform_contact_email").val())) {
            alert("L'indirizzo email che hai inserito non è valido");
            return false;
        }
        if (jQuery("#jform_contact_emailmsg").val() == '') {
            alert(jQuery("#jform_contact_emailmsg").attr("data-validation-required-message"));
            return false;
        }
        if (jQuery("#jform_contact_message").val() == '') {
            alert(jQuery("#jform_contact_message").attr("data-validation-required-message"));
            return false;
        }
		
		var response = grecaptcha.getResponse();

		if(response.length == 0) {
            alert("Devi spuntate il Captcha!");
            return false;			
		}
		
        return true;
    }

    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }
</script>

<section id="contact">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h2>Scrivici</h2>
                <hr class="star-primary">
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 col-lg-offset-2">
                <form id="contact-form" action="<?php echo JRoute::_('index.php'); ?>" method="post" onSubmit="return ctrl_form(this);" class="form-validate" novalidate>


					<?php
					if(empty($contactOrganizationId)) {
					?>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">

							<div class="radio">
								<label><input type="radio" name="w" data-attr="account" />Ho problemi a <b>registrarmi</b> PortAlGas</label>
							</div>
							<div class="radio">
								<label><input type="radio" name="w" data-attr="login" />Ho problemi ad <b>accedere</b> a PortAlGas</label>
							</div>
							<div class="radio">
								<label><input type="radio" name="w" data-attr="gas" />Cerco un <b>GAS</b> vicino a me</label>
							</div>
							<div class="radio">
								<label><input type="radio" name="w" data-attr="des" />Voglio creare un <b>D.E.S.</b></label>
							</div>
							<div class="radio">
								<label><input type="radio" name="w" data-attr="produttore" />Sono un <b>produttore</b>, vorrei farmi conoscere ai GAS che aderiscono a PortAlGas</label>
							</div>
							<div class="radio">
								<label><input type="radio" name="w" data-attr="altro" />Altro</label>
							</div>

							<div id="produttore" class="collapse well">
								Se desideri farti conoscere ai GAS che aderiscono a PortAlGas, consulta l'elenco dei GAS e contattali direttamente:<br />
								<a href="http://www.portalgas.it/gmaps-gas" title="elenco dei GAS che aderiscono a PortAlGas">vai all'elenco dei GAS</a>, buona ricerca.
							</div>
							<div id="account" class="collapse well">
								Se hai problemi a registrari a PortAlGas <a href="http://manuali.portalgas.it/problemi.php#problemi-registrazione-portalgas" title="Problemi registrazione PortAlGas">consulta il manuale</a>.
							</div>
							<div id="login" class="collapse well">
								Se hai problemi ad accere a PortAlGas o hai dimenticato le credenziali <a href="http://manuali.portalgas.it/problemi.php#problemi-ad-accedere-al-front-end" title="Problemi ad accedere al front-end">consulta il manuale</a>.
							</div>
							<div id="gas" class="collapse well">
								PortAlGas <b>non</b> &egrave; un GAS ma un gestionale software per i GAS.<br />
								<a href="http://www.portalgas.it/gmaps-gas" title="elenco dei GAS che aderiscono a PortAlGas">Qui trovi l'elenco dei GAS che aderiscono a PortAlGas</a>, buona ricerca.
							</div>
							
                        </div>
                    </div>
				<?php
				}
				?>


                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">

							<?php
							$continua = true;
							if(empty($contactOrganizationId)) {
							?>						
								<label>Faccio parte del G.A.S</label>
								<p>
									<select name="jform[contact_organization_id]" id="jform_contact_organization_id" class="selectpicker-disabled" data-validation-required-message="Indica a quale GAS appartieni">
										<option selected="selected" value="Nessun G.A.S.">Nessun G.A.S.</option>
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
											echo ' value="' . $item->id . '-' . $item->name . '">' . $item->name;

											echo ' - ' . $item->localita . ' (' . $item->provincia . ')';

											echo '</option>';
										}
										?>
									</select>
								</p>
							<?php
							}
							else {
								if(!empty($organization))
									$msg = 'Scrivi al GAS <b>'.$organization['name'].'</b> - '.$organization['localita'];
								else {
									$continua = false;
									$msg = "G.A.S. non trovato!";
								}
								echo '<div class="well">';
								echo $msg; 
								echo '</div>';
							}
							?>
							
                        </div>
                    </div>

					<?php
					if($continua) {
					?>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Come ti chiami</label>
                            <input type="text" class="form-control" placeholder="Come ti chiami" id="jform_contact_name" name="jform[contact_name]" required data-validation-required-message="Inserisci il tuo nominativo">
                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Il tuo indirizzo mail</label>
                            <input type="email" class="form-control" placeholder="Il tuo indirizzo mail" id="jform_contact_email" name="jform[contact_email]" required data-validation-required-message="Inserisci il tuo indirizzo mail">
                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Oggetto del messaggio</label>
                            <input type="text" class="form-control" placeholder="Oggetto del messaggio" id="jform_contact_emailmsg" name="jform[contact_subject]" required data-validation-required-message="Inserisci l'oggetto del messaggio">
                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label>Messaggio</label>
                            <textarea rows="10" class="form-control" placeholder="Messaggio" id="jform_contact_message" name="jform[contact_message]" required data-validation-required-message="Inserisci il tuo messaggio"></textarea>
                            <p class="help-block text-danger"></p>
                        </div>
                    </div>
                    <div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">
                            <label id="jform_contact_email_copy-lbl" class="hasTip" title="" for="jform_contact_email_copy"> Invia una copia alla tua email.</label>
                            <input id="jform_contact_email_copy" type="checkbox" value="1" name="jform[contact_email_copy]">
                        </div>
                    </div>	
					
					
					<div class="row control-group">
                        <div class="form-group col-xs-12 floating-label-form-group controls">                    
							<!-- https://www.google.com/recaptcha/admin -->
							<script src='https://www.google.com/recaptcha/api.js'></script>
							<div class="g-recaptcha" data-sitekey="6Lf1LSQUAAAAAI5ovUxolIdrzOitL8HzCHg4Y_3K"></div>
						</div>
                    </div>
                    
                                        
                    <br>
                    <div id="success"></div>
                    <div class="row">
                        <div class="form-group col-xs-12">
                            <button type="submit" class="btn btn-success btn-lg"><?php echo JText::_('COM_CONTACT_CONTACT_SEND'); ?></button>
                        </div>
                    </div>

					<?php
					if(!empty($contactOrganizationId)) 
						echo '<input type="hidden" id="jform_contact_contactOrganizationId" name="jform[contact_contactOrganizationId]" value="'.$contactOrganizationId.'" />';
					?>
                    <input type="hidden" name="option" value="com_contact" />
                    <input type="hidden" name="task" value="contact.submit" />
                    <input type="hidden" name="return" value="<?php echo $this->return_page; ?>" />
                    <input type="hidden" name="id" value="<?php echo $this->contact->slug; ?>" />
                    <?php echo JHtml::_('form.token'); 
					
					} // end if($continua) 
						?>						
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 text-center">

                <div class="contact-misc">
                    <?php echo $this->contact->misc; ?>
                </div>

            </div>
        </div>			
    </div>
</section>


<?php
/* 	
  <div class="contact-form">
  <form id="contact-form" action="<?php echo JRoute::_('index.php'); ?>" method="post" class="form-validate">
  <fieldset>
  <legend><?php echo JText::_('COM_CONTACT_FORM_LABEL'); ?></legend>
  <dl>
  <dt><?php echo $this->form->getLabel('contact_name'); ?></dt>
  <dd><?php echo $this->form->getInput('contact_name'); ?></dd>
  <dt><?php echo $this->form->getLabel('contact_email'); ?></dt>
  <dd><?php echo $this->form->getInput('contact_email'); ?></dd>
  <dt><?php echo $this->form->getLabel('contact_subject'); ?></dt>
  <dd><?php echo $this->form->getInput('contact_subject'); ?></dd>
  <dt><?php echo $this->form->getLabel('contact_message'); ?></dt>
  <dd><?php echo $this->form->getInput('contact_message'); ?></dd>
  <?php 	if ($this->params->get('show_email_copy')){ ?>
  <dt><?php echo $this->form->getLabel('contact_email_copy'); ?></dt>
  <dd><?php echo $this->form->getInput('contact_email_copy'); ?></dd>
  <?php 	} ?>
  <?php //Dynamically load any additional fields from plugins. ?>
  <?php foreach ($this->form->getFieldsets() as $fieldset): ?>
  <?php if ($fieldset->name != 'contact'):?>
  <?php $fields = $this->form->getFieldset($fieldset->name);?>
  <?php foreach($fields as $field): ?>
  <?php if ($field->hidden): ?>
  <?php echo $field->input;?>
  <?php else:?>
  <dt>
  <?php echo $field->label; ?>
  <?php if (!$field->required && $field->type != "Spacer"): ?>
  <span class="optional"><?php echo JText::_('COM_CONTACT_OPTIONAL');?></span>
  <?php endif; ?>
  </dt>
  <dd><?php echo $field->input;?></dd>
  <?php endif;?>
  <?php endforeach;?>
  <?php endif ?>
  <?php endforeach;?>
  <dt></dt>
  <dd class="content-btn">
  <button class="validate btn btn-primary" type="submit"><?php echo JText::_('COM_CONTACT_CONTACT_SEND'); ?></button>
  <input type="hidden" name="option" value="com_contact" />
  <input type="hidden" name="task" value="contact.submit" />
  <input type="hidden" name="return" value="<?php echo $this->return_page;?>" />
  <input type="hidden" name="id" value="<?php echo $this->contact->slug; ?>" />
  <?php echo JHtml::_( 'form.token' ); ?>
  </dd>
  </dl>
  </fieldset>
  </form>
  </div>
 */
?>