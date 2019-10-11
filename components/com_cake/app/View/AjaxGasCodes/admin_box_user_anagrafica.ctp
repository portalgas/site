<?php
if(!empty($utente)) {
	
	echo '<div class="box-details">';
	echo '<table cellpadding = "0" cellspacing = "0">';
	echo '<tr>';
	echo '	<th></th>';
	echo '	<th>'.__('Email').'</th>';
	echo '  <th>'.__('Telephone').'</th>';
	echo '  <th>'.__('Address').'</th>';
	echo '</tr>';
	echo '<tr>';
	
	echo '<td>';
	echo $this->App->drawUserAvatar($user, $utente['User']['id'], $utente['User']);
	echo '</td>';
	
	echo '<td>';
	if(!empty($utente['User']['email']))
		echo '<a title="'.__('Email send').'" target="_blank" href="mailto:'.$utente['User']['email'].'">'.$utente['User']['email'].'</a>';
	echo '</td>';
	echo '<td>';
	if(!empty($utente['Profile']['phone'])) echo $utente['Profile']['phone'].'<br />';
	if(!empty($utente['Profile']['phone2'])) echo $utente['Profile']['phone2'];
	echo '</td>';
	echo '<td>';
	echo $utente['Profile']['address'];
	if(!empty($utente['Profile']['city'])) echo ' '.$utente['Profile']['city'];
	echo '</td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>';

}
?>
<script type="text/javascript">
$(document).ready(function() {
	<?php 
	if($call=='managementCartsOne')
		echo 'choiceUserAnagrafica();';
	else
	if($call=='cassiereDeliveryDocsExport')
		echo 'choiceUserAnagrafica();';    
	?>
	
});
</script>