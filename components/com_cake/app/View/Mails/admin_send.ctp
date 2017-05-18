<?php
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List Mails'),array('controller'=>'Mails','action'=>'index'));
$this->Html->addCrumb(__('Send Mail'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<div class="mails">
	<h2 class="ico-mails">
		<?php echo __('Send Mail');?>
	<div class="actions-img">
	<ul>
		<li><?php echo $this->Html->link(__('List Mails'), array('action' => 'index'),array('class' => 'action actionConfig','title' => __('List Mails'))); ?></li>
	</ul>
	</div>
	</h2>


<?php echo $this->Form->create('Mail',array('id'=>'formGas','enctype' => 'multipart/form-data'));?>

	<fieldset>
		<legend><?php echo __('Send Mail'); ?></legend>
	<?php
		$i=0;
		echo $this->Form->input('mittenti', array('options' => $mittenti, 'value' => Configure::read('Mail.no_reply_mail'), 'label'=>__('A chi rispondere'),'tabindex'=>($i+1)));

		echo $this->App->drawFormRadio('Mail','dest_options',array('options' => $dest_options, 'value'=>'USERS', 'name' => 'dest-options', 'label' => __('A chi inviarla'),'tabindex'=>($i+1)));
		
		echo $this->App->drawFormRadio('Mail','dest_options_qta',array('options' => $dest_options_qta, 'value'=>'ALL', 'name' => 'dest-options-qta', 'label' => __('A quanti'),'tabindex'=>($i+1)));
		
		/*
		 * produttori
		 */
		echo '<div id="suppliersorganization" style="display:none;">';
		$label = __('SuppliersOrganization').'&nbsp;('.count($ACLsuppliersOrganization).')';
		echo $this->Form->input('supplier_organization',array('label' => $label,'options' => $ACLsuppliersOrganization,'escape' => false,'multiple' => true));
		echo '</div>';
				
		/*
		 * utenti dell'ordine
		 */
		echo '<div id="users_cart" style="display:none;">';
		$label = "Utenti che hanno effettuato acquisti";
		echo $this->Form->input('orders',array('label' => $label,'options' => $orders, 'escape' => false));
		echo '</div>';
		
		/*
		 * gruppi
		 */
		echo '<div id="userGroups" style="display:none;">';
		$label = __('Groups').'&nbsp;('.count($userGroups).')';
		echo $this->Form->input('usergroups',array('label' => $label,'options' => $userGroups,'escape' => false,'multiple' => true));
		echo '</div>';
				
		/*
		 * utenti
		 */
		echo '<div id="users" style="display:none;">';
		$label = __('Users').'&nbsp;('.count($users).')';
		echo '<label for="MailUser">'.$label.'</label>';
		
		echo $this->Form->select('master_user_id', $users, array('label' => $label, 'multiple' => true, 'size' =>10));
		echo $this->Form->select('user_id', array(), array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));					
		echo $this->Form->hidden('user_ids',array('id' => 'user_ids','value' => ''));
		echo '</div>';
		
		/*
		 * referenti
		 */
		echo '<div id="referenti" style="display:none;">';
		$label = __('Referenti').'&nbsp;('.count($referenti).')';
		echo '<label for="MailUser">'.$label.'</label>';
		
		echo $this->Form->select('master_referente_id', $referenti, array('label' => $label, 'multiple' => true, 'size' =>10));
		echo $this->Form->select('referente_id', array(), array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));					
		echo $this->Form->hidden('referente_ids',array('id' => 'referente_ids','value' => ''));
		echo '</div>';
		
		echo $this->Form->input('subject');
		
		echo $this->Form->input('name',array('label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'true'));

		echo '<div class="input text"><label></label>';
		echo $body_header_mittente; 
		
		echo $this->Form->textarea('body', array('rows' => '15', 'cols' => '75'));
		
		echo '<div class="input text"><label>Piè di pagina</label>';
		
		echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer_no_reply" style="display:inline;">'.str_replace('<br />', '', $body_footer_no_reply).'</textarea>';
		echo '<textarea cols="85%" rows="4" class="noeditor" disabled="true" id="body_footer" style="display:none;">'.str_replace('<br />', '', $body_footer).'</textarea>';
		
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
	
	jQuery('#MailMasterUserId').click(function() {
		jQuery("#MailMasterUserId option:selected" ).each(function (){			
			jQuery('#MailUserId').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
	
	jQuery('#MailUserId').click(function() {
		jQuery("#MailUserId option:selected" ).each(function (){			
			jQuery('#MailMasterUserId').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
	
	jQuery('#MailMasterReferenteId').click(function() {
		jQuery("#MailMasterReferenteId option:selected" ).each(function (){			
			jQuery('#MailReferenteId').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
	});
	
	jQuery('#MailReferenteId').click(function() {
		jQuery("#MailReferenteId option:selected" ).each(function (){			
			jQuery('#MailMasterReferenteId').append(jQuery("<option></option>")
	         .attr("value",jQuery(this).val())
	         .text(jQuery(this).text()));
	         
	         jQuery(this).remove();
		});
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
			if(dest_options=='USERS') {
				var user_ids = '';
				jQuery("#MailUserId option" ).each(function (){	
					user_ids +=  jQuery(this).val()+',';
				});
				user_ids = user_ids.substring(0,user_ids.length-1);
				
				if(user_ids=='') {
					alert("Devi selezionare almeno un utente come destinatario");
					return false;
				}
				
				jQuery('#user_ids').val(user_ids);			
			}
			else 
			if(dest_options=='REFERENTI') {
				var referente_ids = '';
				jQuery("#MailReferenteId option" ).each(function (){	
					referente_ids +=  jQuery(this).val()+',';
				});
				referente_ids = referente_ids.substring(0,referente_ids.length-1);
				
				if(referente_ids=='') {
					alert("Devi selezionare almeno un referente come destinatario");
					return false;
				}
				
				jQuery('#referente_ids').val(referente_ids);	
			}
			else	
			if(dest_options=='SUPPLIERS') {
				destinatariScelti = jQuery("#MailSupplierOrganization").val();
	
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

	if(dest_options=='USERS_CART') {
		jQuery('#users_cart').css('display','block');
		jQuery('#users').css('display','none');
		jQuery('#userGroups').css('display','none');
		jQuery('#referenti').css('display','none');
		jQuery('#suppliersorganization').css('display','none');
		
		jQuery('#Maildest_options_qtaALL').prop("checked", true);
		jQuery('#Maildest_options_qtaSOME').attr('disabled',true);
	}
	else
	if(dest_options_qta=='ALL') {
		jQuery('#Maildest_options_qtaUSERS_CART').css('display','none');
	
		jQuery('#users_cart').css('display','none');
		jQuery('#users').css('display','none');
		jQuery('#userGroups').css('display','none');
		jQuery('#referenti').css('display','none');
		jQuery('#suppliersorganization').css('display','none');
	}	
	else {
		if(dest_options=='USERS') {			
			jQuery('#users_cart').css('display','none');
			jQuery('#users').css('display','block');
			jQuery('#userGroups').css('display','none');
			jQuery('#referenti').css('display','none');
			jQuery('#suppliersorganization').css('display','none');
			
			jQuery('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else
		if(dest_options=='USERGROUPS') {			
			jQuery('#users_cart').css('display','none');
			jQuery('#users').css('display','none');
			jQuery('#userGroups').css('display','block');
			jQuery('#referenti').css('display','none');
			jQuery('#suppliersorganization').css('display','none');
			
			jQuery('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else	
		if(dest_options=='REFERENTI') {
			jQuery('#users_cart').css('display','none');
			jQuery('#users').css('display','none');
			jQuery('#userGroups').css('display','none');
			jQuery('#referenti').css('display','block');
			jQuery('#suppliersorganization').css('display','none');
			
			jQuery('#Maildest_options_qtaSOME').attr('disabled',false);
		}
		else	
		if(dest_options=='SUPPLIERS') {
			jQuery('#users_cart').css('display','none');
			jQuery('#users').css('display','none');
			jQuery('#userGroups').css('display','none');
			jQuery('#referenti').css('display','none');
			jQuery('#suppliersorganization').css('display','block');
			
			jQuery('#Maildest_options_qtaSOME').attr('disabled',false);
		}
	}
	
}
</script>