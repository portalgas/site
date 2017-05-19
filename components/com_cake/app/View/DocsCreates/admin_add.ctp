<?php 
$this->Html->addCrumb(__('Home'),array('controller' => 'Pages', 'action' => 'home'));
$this->Html->addCrumb(__('List DocsCreate'), array('controller' => 'DocsCreates', 'action' => 'index'));
$this->Html->addCrumb(__('Add DocsCreat'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('DocsCreate', array('id' => 'formGas'));

echo '<fieldset>';
echo '<legend>'.__('DocsCreate').'</legend>';

/*
 * utenti
 */
echo '<div id="users">';
$label = __('Users').'&nbsp;('.count($users).')';
echo '<label for="MailUser">'.$label.'</label>';
echo $this->Form->select('master_user_id', $users, array('label' => $label, 'multiple' => true, 'size' =>10));
echo $this->Form->select('user_id', array(), array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px'));					
echo $this->Form->hidden('user_ids',array('id' => 'user_ids','value' => ''));
echo '</div>';

echo $this->Form->input('txt_name',array('label' => __('Name'), 'required'=>'true'));
echo $this->Form->input('txt_testo',array('label' => __('Text'), 'required'=>'true'));
echo '<div class="clearfix"></div>';
echo $this->Form->input('txt_data',array('label' => __('Date'), 'required'=>'false',));

echo '</fieldset>';
echo $this->Form->end(__('Submit', true));
echo '</div>';
?>	

<script type="text/javascript">
$(document).ready(function() {
	$('#DocsCreateMasterUserId').click(function() {
		$("#DocsCreateMasterUserId option:selected" ).each(function (){			
			$('#DocsCreateUserId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});
	
	$('#DocsCreateUserId').click(function() {
		$("#DocsCreateUserId option:selected" ).each(function (){			
			$('#DocsCreateMasterUserId').append($("<option></option>")
	         .attr("value",$(this).val())
	         .text($(this).text()));
	         
	         $(this).remove();
		});
	});	
	
	$('#formGas').submit(function() {
		var user_ids = '';
		$("#DocsCreateUserId option" ).each(function (){	
			user_ids +=  $(this).val()+',';
		});
		user_ids = user_ids.substring(0,user_ids.length-1);
		
		if(user_ids=='') {
			alert("Devi selezionare almeno un utente come destinatario");
			return false;
		}
		
		$('#user_ids').val(user_ids);
		
		return true;
	});	
	
});
</script>