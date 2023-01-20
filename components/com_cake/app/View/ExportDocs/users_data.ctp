<?php 
/*
 * Elenco utenti
*/


if($this->layout=='pdf') {
	App::import('Vendor','xtcpdf');
	
	$output = new XTCPDF($organization, PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$output->headerText = $fileData['fileTitle'];
			
	// add a page
	$output->AddPage();
	$css = $output->getCss();
}
else 
if($this->layout=='ajax') {
	App::import('Vendor','xtcpreview');
	$output = new XTCPREVIEW();
	$css = $output->getCss();
}
	
$html = '';
$html .= '	<table class="table table-hover" cellpadding="0" cellspacing="0">';
$html .= '	<thead>'; // con questo TAG mi ripete l'intestazione della tabella
$html .= '		<tr>';
$html .= '			<th width="'.$output->getCELLWIDTH20().'">'.__('N').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH30().'">'.__('Cod.').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH90().'">'.__('Username').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Name').'</th>';
$html .= '			<th width="'.($output->getCELLWIDTH90()+$output->getCELLWIDTH40()).'">'.__('Contatti').'</th>';
$html .= '			<th width="'.($output->getCELLWIDTH90()+$output->getCELLWIDTH40()).'">'.__('Address').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Suppliers Organizations Referents').'</th>';
$html .= '			<th width="'.$output->getCELLWIDTH80().'">'.__('Role').'</th>';
$html .= '	</tr>';
$html .= '	</thead><tbody>';

foreach($results as $numUser => $result) {

	$html .= '<tr>';
	$html .= '	<td width="'.$output->getCELLWIDTH20().'">'.($numUser+1).'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH30().'">'.$result['Profile']['codice'].'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH90().'">'.$result['User']['username'].'</td>';
	$html .= '<td width="'.$output->getCELLWIDTH80().'">'.$result['User']['name'].'</td>';
	$html .= '<td width="'.($output->getCELLWIDTH90()+$output->getCELLWIDTH40()).'">';
        $html .= $result['User']['email'].'<br />';
	if(!empty($result['Profile']['phone'])) $html .= $result['Profile']['phone'].'<br />';
	if(!empty($result['Profile']['phone2'])) $html .= $result['Profile']['phone2'];
	$html .= '</td>';
	
	$html .= '<td width="'.($output->getCELLWIDTH80()+$output->getCELLWIDTH40()).'">';
	if(!empty($result['Profile']['address'])) $html .= $result['Profile']['address'].'<br />';
        if(!empty($result['Profile']['city'])) $html .= $result['Profile']['city'].'<br />';
        if(!empty($result['Profile']['region'])) $html .= $result['Profile']['region'].'<br />';
        if(!empty($result['Profile']['postal_code'])) $html .= $result['Profile']['postal_code'];
	$html .= '</td>';

	if(isset($result['SuppliersOrganization'])) {
		$html .= '<td width="'.$output->getCELLWIDTH80().'">';
		foreach($result['SuppliersOrganization'] as $numSuppliersOrganization => $suppliersOrganization) {
			$html .= $suppliersOrganization['name'].' ';
			/* $html .= $result['SuppliersOrganizationsReferent'][$numSuppliersOrganization]['type']; */
			if($numSuppliersOrganization < (count($result['SuppliersOrganization'])-1)) $html .= '<br />';
		}
		$html .= '</td>';
	}
	else
		$html .= '<td width="'.$output->getCELLWIDTH80().'"></td>';
	
	/*
	 * ruoli
	 */
	$html .= '<td width="'.$output->getCELLWIDTH80().'">';
	
	if(isset($result['UserGroup'])) {
		foreach($result['UserGroup'] as $userGroup) {
			if($userGroup['id']==Configure::read('group_id_manager')) 
				$html .= __("UserGroupsManager").'<br />';
			if($userGroup['id']==Configure::read('group_id_manager_delivery'))
				$html .= __("UserGroupsManagerDelivery").'<br />';
			if($userGroup['id']==Configure::read('group_id_cassiere'))
				$html .=__("UserGroupsCassiere").'<br />';
			if($userGroup['id']==Configure::read('group_id_tesoriere'))
				$html .= __("UserGroupsTesoriere").'<br />';
			if($userGroup['id']==Configure::read('group_id_super_referent'))
				$html .= __("UserGroupsSuperReferent").'<br />';
			if($userGroup['id']==Configure::read('group_id_generic'))
				$html .= __("UserGroupsGeneric").'<br />';
		}
	}
	
	$html .= '</td>';
	$html .= '</tr>';	
}
$html .= '</tbody></table>';

$output->writeHTML($css.$html , $ln=true, $fill=false, $reseth=true, $cell=true, $align='');

// reset pointer to the last page
$output->lastPage();

if($this->layout=='pdf') 
	ob_end_clean();
echo $output->Output($fileData['fileName'].'.pdf', 'D');
exit;
?>