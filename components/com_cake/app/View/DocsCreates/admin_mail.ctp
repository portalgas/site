<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List DocsCreate'), array('controller' => 'DocsCreates', 'action' => 'index'));
$this->Html->addCrumb(__('DocsCreatesMailPrepare'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';

echo $this->Form->create('DocsCreate');

echo $this->element('legendaDocsCreatesSendMail');
	
echo '<fieldset>';
echo '<legend>'.__('DocsCreatesMailPrepare').'</legend>';

		echo $this->Form->input('doc_id', ['type' => 'hidden', 'value' => $doc_id, 'id' => 'doc_id']);
		
		echo '<div class="input text">';
		$label = __('Users').'&nbsp;('.count($users).')';
		echo '<label for="MailUser">'.$label.'</label> ';
		echo $this->Form->select('master_user_id', $users, array('label' => $label, 'multiple' => true, 'size' => count($users), 'disabled' => 'disabled'));
		echo '</div>';
			
		echo '<div class="input text">';
		echo '<label for="OrderDeliveryId">'.__('Email').'</label> ';
		echo '<a href="'.$user->email.'" target="_blank">'.$user->email.'</a>';
		echo '</div>';
		
		echo $this->Form->input('subject');
		
		echo $this->Form->input('body_header',array('label' => 'Intestazione', 'value' => str_replace('<br />', '', $body_header), 'disabled' => 'true'));
		
		echo $this->Form->textarea('body_mail', array('value' => $body_mail, 'rows' => '15', 'cols' => '75'));
		
		echo '<div class="clearfix"></div>';
		echo '<div class="input text"><label>Piè di pagina</label> ';
		echo '<textarea cols="85%" rows="2" class="noeditor form-control" disabled="true" id="body_footer" style="display:inline;">'.str_replace('<br />', '', $body_footer).'</textarea>';
		
	echo '</fieldset>';

echo $this->Form->submit(__('Send'), array('div'=> 'submitMultiple'));

echo $this->Form->end();

echo '</div>';
?>
<script type="text/javascript">
$(document).ready(function() {
		
	$('#formGas').submit(function() {

		var subject = $('#DocsCreateSubject').val();
		if(subject=="") {
			alert("Devi indicare l'oggetto della mail");
			return false;
		}
		/*
		var intestazione = $('#DocsCreateIntestazione').val();
		if(intestazione=="") {
			alert("Devi indicare l'intestazione della mail");
			return false;
		}
		*/
		var body = $('#DocsCreateMailOpenTesto').val();
		if(body=="") {
			alert("Devi indicare il testo della mail");
			return false;
		}
	
		alert("Verrà inviata la mail, attendere che venga terminata l'esecuzione");
	
		$("input[type=submit]").attr('disabled', 'disabled');
		$("input[type=submit]").css('background-image', '-moz-linear-gradient(center top , #ccc, #dedede)');
		$("input[type=submit]").css('box-shadow', 'none');

		return true;
	});	
});
</script>