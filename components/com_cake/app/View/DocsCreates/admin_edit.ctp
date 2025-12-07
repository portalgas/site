<?php 
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List DocsCreate'), array('controller' => 'DocsCreates', 'action' => 'index'));
$this->Html->addCrumb(__('Edit DocsCreate'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('DocsCreate', array('id' => 'formGas', 'target' => ''));

echo '<fieldset>';
echo '<legend>'.__('Edit DocsCreate').'</legend>';

echo $this->Form->input('doc_id', ['type' => 'hidden', 'value' => $doc_id, 'id' => 'doc_id']);

/*
 * utenti
 */
echo '<div id="users">';
$label = __('Users').'&nbsp;('.count($users).')';
echo '<label for="MailUser">'.$label.'</label> ';
echo $this->Form->select('master_user_id', $users, ['label' => $label, 'multiple' => true, 'size' => 10, 'id' => 'master_user_id']);
echo $this->Form->select('user_id', $usersResults, ['multiple' => true, 'size' => 10, 'style' => 'min-width:300px', 'id' => 'user_id']);					
echo $this->Form->hidden('user_ids', ['id' => 'user_ids','value' => '']);
echo '</div>';

echo $this->Form->input('name', ['label' => __('Name'), 'required'=>'true', 'id' => 'name']);
echo $this->Form->input('txt_testo', ['label' => __('Text'), 'required'=>'true']);
echo '<div class="clearfix"></div>';
if($this->Form->value('DocsCreate.txt_data')!=Configure::read('DB.field.date.empty'))
	$txt_data = $this->Form->value('DocsCreate.txt_data');
else 
	$txt_data = '';
echo $this->App->drawDate('DocsCreate', 'txt_data', __('Date'), $txt_data);

/*
 * stato
*/
$options = array('options' => $stato, 'value' => $this->Form->value('DocsCreate.stato'), 'label'=>__('Stato'), 'required'=>'false');
echo $this->App->drawFormRadio('DocsCreate','stato', $options);

echo '</fieldset>';

echo $this->Form->button(__('Preview'), ['id' => 'btn-preview', 'class' => 'btn btn-primary', 'type' => 'button']);
echo "&nbsp;";
echo $this->Form->button(__('Submit'), ['id' => 'btn-submit', 'class' => 'btn btn-success', 'type' => 'submit']);

echo $this->Form->end();
echo '</div>';
?>	
<script type="text/javascript">
$(document).ready(function() {

	$('#master_user_id').click(function() {
		$("#master_user_id option:selected" ).each(function (){			
			$('#user_id').append($("<option></option>")
			 .attr("value",$(this).val())
			 .text($(this).text()));
			 
			 $(this).remove();
		});
	});
	
	$('#user_id').click(function() {
		$("#user_id option:selected" ).each(function (){			
			$('#master_user_id').append($("<option></option>")
			 .attr("value",$(this).val())
			 .text($(this).text()));
			 
			 $(this).remove();
		});
	});
	
	$('#btn-preview').click(function () {
		
		$("#formGas").attr({'action': '<?php echo Configure::read('App.server');?>/administrator/index.php?option=com_cake&controller=DocsCreates&action=pdf_preview&format=notmpl'});
		$("#formGas").attr({'target': '_blank'});
		$("#formGas").submit();
					
		return false;
	});
	
	$('#btn-submit').click(function () {
		
		$("#formGas").attr({'action': '<?php echo Configure::read('App.server');?>/administrator/index.php?option=com_cake&controller=DocsCreates&action=edit'});
		$("#formGas").attr({'target': ''});
		$("#formGas").submit();
					
		return false;
	});
	
	$('#formGas').submit(function() {

		var user_ids = '';
		$("#user_id option").each(function (){	
			user_ids +=  $(this).val()+',';
		});
		user_ids = user_ids.substring(0,user_ids.length-1);

		if(user_ids=='') {
			alert("Devi selezionare almeno un utente al quale inviare il documento");
			return false;
		}

		$("#user_ids" ).val(user_ids);
		
		var name = $("#name").val();
		if(name=='') {
			alert("Indica il nome del documento");
			return false;			
		}
		
		return true;
	});	
	
});	
</script>