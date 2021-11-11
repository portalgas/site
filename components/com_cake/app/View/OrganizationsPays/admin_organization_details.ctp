<?php
// debug($results);

if(!empty($results)) {
	echo '<div class="alert alert-info">';
	echo '<label>'.__('beneficiario_pay').'</label>'.$results['OrganizationsPay']['beneficiario_pay'].'<br />';
	echo '<label>'.__('type_pay').'</label>'.$results['OrganizationsPay']['type_pay'].'<br />';
	echo '<label>'.__('importo').'</label>'.$results['OrganizationsPay']['importo'].'<br />';
	echo'<label>'. __('import_additional_cost').'</label>'.$results['OrganizationsPay']['import_additional_cost'].'<br />';
	echo '</div>';	
}
else {
	echo $this->element('boxMsg', ['msg' => "Non sono ancora stati generati i dati di pagamento per il G.A.S. per l'anno ".date('Y'), 'class_msg' => 'danger']);
}
?>