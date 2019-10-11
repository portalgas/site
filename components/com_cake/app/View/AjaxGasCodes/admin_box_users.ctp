<?php
if(empty($users))
	echo $this->element('boxMsg', array('class_msg' => 'message', 'msg' => "Nessun utente ha effettuato acquisti"));
else {
	if(isset($user_id) && $user_id!=0)
		$defaultUserID = $user_id;
	else 
		$defaultUserID = 'ALL';
	
	$options = array('empty' => Configure::read('option.empty'),
					 'default' => $defaultUserID,
					 'onChange' => 'javascript:choiceUser(this);'
					);
	
	if(count($users) > Configure::read('HtmlSelectWithSearchNum'))
		$options += array('class'=> 'selectpicker', 'data-live-search' => true);
	
	echo $this->Form->input('user_id',$options);
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		javascript:choiceUser(this);
				
		$("#user_id").selectpicker({}).selectpicker("render");		
	});
	</script>
<?php
}
?>