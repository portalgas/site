<?php 
/*
 * Elenco utenti presenti ad una data consegna
*/
$headers = array('csv' => array(
		'N' => 'N',
		'code' => __('Code'),
		'name' => __('Name'),
		'mail' => __('Mail'),
		'telephone' => __('Telephone'),
		'address' => __('Address'),
		'qta_tot' => 'Qta totale',
		'suppliers_organizations_referent' => __('Suppliers Organizations Referents'),
		)
); 

$data = array();
foreach($results as $numResult => $result) {

	$dataTmp = array();	
	$telephone = "";
	if(!empty($result['Profile']['phone'])) $telephone .= $result['Profile']['phone'].' ';	if(!empty($result['Profile']['phone2'])) $telephone .= $result['Profile']['phone2'];	
	$address = "";	if(!empty($result['Profile']['address'])) $address = $result['Profile']['address'];
	
	$dataTmp = array(
			'N' => ($numResult+1),
			'code' => $result['Profile']['codice'],
			'name' => $result['User']['name'],
			'mail' => $result['User']['email'],
			'telephone' => $telephone,
			'address' => $address,
			'qta_tot' => $result['User']['cart_qta_tot']
			);
	
	if(isset($result['SuppliersOrganization'])) 		foreach($result['SuppliersOrganization'] as $numSuppliersOrganization => $suppliersOrganization) 			$dataTmp['suppliers_organizations_referent_'.$numSuppliersOrganization] = $suppliersOrganization['name'].' '.$result['SuppliersOrganizationsReferent'][$numSuppliersOrganization]['type'];	
	$data[]['csv'] = $dataTmp;
}

array_unshift($data,$headers);

foreach ($data as $row)
{
	foreach ($row['csv'] as &$value) {
		// Apply opening and closing text delimiters to every value
		$value = "\"".$value."\"";
	}
	// Echo all values in a row comma separated
	echo implode(",",$row['csv'])."\n";
}
?>