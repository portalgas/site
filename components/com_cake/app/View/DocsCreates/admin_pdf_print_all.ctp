<?php 
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('List DocsCreate'), array('controller' => 'DocsCreates', 'action' => 'index'));
$this->Html->addCrumb(__('PrintAllDocs'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));

echo '<div class="contentMenuLaterale">';
echo $this->Form->create('DocsCreate', array('id' => 'formGas', 'target' => ''));

echo '<fieldset>';
echo '<legend>'.__('PrintAllDocs').'</legend>';

echo $this->Form->input('doc_id', ['type' => 'hidden', 'value' => $doc_id, 'id' => 'doc_id']);

/*
 * utenti
 */
echo '<div id="users">';
$label = __('Users').'&nbsp;('.count($usersResults).')';
echo '<label for="MailUser">'.$label.'</label> ';
echo $this->Form->select('user_id', $usersResults, array('multiple' => true, 'size' => 10, 'style' => 'min-width:300px', 'id' => 'user_id'));					
echo $this->Form->hidden('user_ids',array('id' => 'user_ids','value' => ''));
echo '</div>';

echo $this->Form->input('name',array('label' => __('Name'), 'disabled' => 'disabled'));
echo $this->Form->value('DocsCreate.txt_testo');

echo '</fieldset>';

echo "&nbsp;";
echo $this->Form->Submit(__('PrintAllDocs'), ['class' => 'btn btn-primary']);

echo $this->Form->end();
echo '</div>';
?>	
<script type="text/javascript">
$(document).ready(function() {
	
	$('#formGas').submit(function() {

		if($("#user_id > option").lenght==0) {
			alert("Scegli gli utenti ai quali inviare il documento");
			return false;
		}
		
		$("#user_id > option").each(function() {
			/* console.log(this.text + ' ' + this.value); */ 
		
			var user_id = this.value;
			if(user_id!='') {
				var doc_id = $("#doc_id").val();
				
				var url = '<?php echo Configure::read('App.server');?>/administrator/index.php?option=com_cake&controller=DocsCreates&action=pdf_create&doc_id='+doc_id+'&user_id='+user_id+'&format=notmpl';
				/* console.log(url); */
				window.open(url,'win'+user_id,'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=300,height=200,directories=no,location=no'); 
			} 
		});	
	});	
	
});	
</script>