<div class="modal fade" id="dialog-send_mail" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?php echo __('Send Mail');?></h4>
			</div>
			<div class="modal-body">
			
			<?php 
			echo $this->Form->create('Mail', array('id'=>'formGasMail'));
			echo '<fieldset>';
			echo '<legend style="display:none;">'.__('Send Mail').'</legend>';
		
			echo '<div class="form-group">';
			echo '<label for="email">Mittente</label>';
			echo '<input type="email" class="form-control" id="email" value="'.$user->get('email').'" disabled/>';
			echo '</div>';
            
			echo '<div class="form-group">';
			echo $this->Form->textarea('body', array('rows' => '10', 'cols' => '100%', 'id' => 'body_mail'));
			echo '</div>';

			echo $this->Form->hidden('pass_org_id', array('id' => 'pass_org_id'));
			
			echo $this->Form->hidden('pass_id', array('id' => 'pass_id'));
			
			echo $this->Form->hidden('pass_entity', array('id' => 'pass_entity'));
			
			echo '<div class="clearfix"></div>';
			echo '<div class="form-group">';
			echo $this->Form->end(__('Send'));
			echo '</div>';
			
			echo '</fieldset>';
			?>			
			</div>

		</div>
	</div>		
</div>	
<script type="text/javascript"> 
jQuery(document).ready(function() {
	jQuery('.sendMail').click(function() {
		
		var pass_org_id = jQuery(this).attr('pass_org_id');
		var pass_id = jQuery(this).attr('pass_id');
		var pass_entity = jQuery(this).attr('pass_entity');

		/*
		console.log("pass_org_id "+pass_org_id);
		console.log("pass_id "+pass_id);
		console.log("pass_entity "+pass_entity);
		*/
		jQuery('#pass_org_id').val(pass_org_id);
		jQuery('#pass_id').val(pass_id);
		jQuery('#pass_entity').val(pass_entity);
		
		jQuery("#dialog-send_mail").dialog("open");

		return false;	
	});

	jQuery('#formGasMail').submit(function() {

		var body_mail = jQuery('#body_mail').val();
		if(body_mail=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
		body_mail = encodeURIComponent(body_mail);
		
		var pass_org_id = jQuery('#pass_org_id').val();
		var pass_id = jQuery('#pass_id').val();
		var pass_entity = jQuery('#pass_entity').val();

		var data = 'pass_org_id='+pass_org_id+'&pass_id='+pass_id+'&pass_entity='+pass_entity+'&pass_entity='+pass_entity+'&body_mail='+body_mail;
		/* console.log("data "+data); */ 
		
		var url = '';
		url = '/administrator/index.php?option=com_cake&controller=Mails&action=popup_send&format=notmpl';

		jQuery.ajax({
			type: "POST",
			url: url,
			data: data,
			success: function(response){
				alert("Mail inviata");
			},
			error:function (XMLHttpRequest, textStatus, errorThrown) {
				alert("Mail non inviata");
			}
		});

		jQuery("#dialog-send_mail").dialog("close");
		
		return false;
	
	});	
});
</script>