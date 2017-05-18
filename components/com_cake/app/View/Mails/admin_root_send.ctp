<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Mails'),array('controller'=>'Mails','action'=>'root_index'));
$this->Html->addCrumb(__('Send Mail'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="mails">
	<h2 class="ico-mails">
		<?php echo __('Send Mail');?>
	<div class="actions-img">
	<ul>
		<li><?php echo $this->Html->link(__('List Mails'), array('action' => 'root_index'),array('class' => 'action actionConfig','title' => __('List Mails'))); ?></li>
	</ul>
	</div>
	</h2>


<?php echo $this->Form->create('Mail',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>

	<fieldset>
		<legend><?php echo __('Send Mail'); ?></legend>
	<?php
		$i=0;
		echo $this->Form->input('mittenti', array('options' => $mittenti, 'value' => Configure::read('Mail.no_reply_mail'), 'label'=>__('A chi rispondere'),'tabindex'=>($i+1)));

		echo $this->App->drawFormRadio('Mail','dest_options',array('options' => $dest_options, 'value'=>'SUPPLIERS', 'name' => 'dest-options', 'label' => __('A chi inviarla'),'tabindex'=>($i+1)));
		
		echo $this->App->drawFormRadio('Mail','dest_options_qta',array('options' => $dest_options_qta, 'value'=>'ALL', 'name' => 'dest-options-qta', 'label' => __('A quanti'),'tabindex'=>($i+1)));

		/*
		 * organizations
		 */
		echo '<div id="organization" style="display:block;">';
		$label = __('Organization').'&nbsp;('.count($organizationResults).')';
		echo $this->Form->input('organizations',array('label' => $label,'options' => $organizationResults,'escape' => false,'multiple' => true));
		echo '</div>';
		
		/*
		 * produttori
		 */
		echo '<div id="suppliersorganization" style="display:block;">';
		$label = __('SuppliersOrganization').'&nbsp;('.count($ACLsuppliersOrganization).')';
		echo $this->Form->input('suppliers',array('label' => $label,'options' => $ACLsuppliersOrganization,'escape' => false,'multiple' => true));
		echo '</div>';
				
		echo $this->Form->input('subject');
		
		echo '<div class="input text"><label></label>';
		echo $body_header_mittente; 
		
		echo $this->Form->textarea('body', array('rows' => '15', 'cols' => '75'));
		
		echo '<div class="input text"><label>Piè di pagina</label>';
		
		echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		
		echo '</div>';		
		
		echo $this->Form->input('Document.img1', array(
													'label' => 'Allegato',
												    'between' => '<br />',
												    'type' => 'file'
												));	
		
		echo '</fieldset>';
		
		echo $this->Form->end(__('Send'));
		?>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {

	jQuery('#MailMittenti').change(function() {
		var mittenti = jQuery('#MailMittenti').val();	
		
		if(mittenti=='<?php echo Configure::read('Mail.no_reply_mail');?>') {
			jQuery('#body_footer_no_reply').show();
			jQuery('#body_footer').hide();
		}	
		else {
			jQuery('#body_footer_no_reply').hide();
			jQuery('#body_footer').show();
		}	
	});
	
	jQuery("input[name='data[Mail][dest_options]']").change(function() {
		choiceDestOptions();
	});

	jQuery("input[name='data[Mail][dest_options_qta]']").change(function() {
		choiceDestOptions();
	});
	
	choiceDestOptions();

	jQuery('#formGas').submit(function() {

		var dest_options_qta = jQuery("input[name='data[Mail][dest_options_qta]']:checked").val();
		if(dest_options_qta=='SOME') {
			var dest_options = jQuery("input[name='data[Mail][dest_options]']:checked").val();
			
			var destinatariScelti = null;
			if(dest_options=='SUPPLIERS') {
				destinatariScelti = jQuery("#MailSuppliers").val();
	
				if(destinatariScelti==null) {
					alert("Devi scegliere almeno un destinatario");
					return false;
				}			
			}
		}
		var subject = jQuery('#MailSubject').val();
		if(subject=="") {
			alert("Devi indicare il soggetto della mail");
			return false;
		}
	
		var body = jQuery('#MailBody').val();
		if(body=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
	
		alert("Verrà inviata la mail, attendere che venga terminata l'esecuzione");
	
		jQuery("input[type=submit]").attr('disabled', 'disabled');
		jQuery("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
		jQuery("input[type=submit]").css('box-shadow', 'none');

		return true;
	});	
});

function choiceDestOptions() {
	var dest_options = jQuery("input[name='data[Mail][dest_options]']:checked").val();
	var dest_options_qta = jQuery("input[name='data[Mail][dest_options_qta]']:checked").val();

	jQuery('#Maildest_options_qtaALL').attr('disabled',false);
	jQuery('#Maildest_options_qtaSOME').attr('disabled',false);

	if(dest_options_qta=='ALL') {

		jQuery('#organization').css('display','none');
		jQuery('#suppliersorganization').css('display','none');
	}	
	else {	
		if(dest_options=='ORGANIZATIONS') {
			jQuery('#organization').css('display','block');
			jQuery('#suppliersorganization').css('display','none');
			
			jQuery('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else	
		if(dest_options=='SUPPLIERS') {
			jQuery('#organization').css('display','none');
			jQuery('#suppliersorganization').css('display','block');
			
			jQuery('#Maildest_options_qtaSOME').attr('disabled',false);
		}
	}
}
</script>