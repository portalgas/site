<?php 
$headers = array('csv' => array(
		'N' => 'N',		
		'suppliers' => __('Suppliers'),
		'frequenza' => __('Frequenza'),
		'code' => __('Code'),
		'name' => __('Name'),
		'mail' => __('Mail'),
		'telephone' => __('Telephone'),		'type' => __('Type'),
		)
); 

$data = [];foreach($results as $numResult => $result) {	$dataTmp = [];	$telephone = "";	if(!empty($result['Profile']['phone'])) $telephone .= $result['Profile']['phone'].' ';	if(!empty($result['Profile']['phone2'])) $telephone .= $result['Profile']['phone2'];	$dataTmp = array(			'N' => ($numResult+1),			'suppliers' => $result['SuppliersOrganization']['name'],
			'frequenza' => $result['SuppliersOrganization']['frequenza'],
			'code' => $result['Profile']['codice'],
			'name' => $result['User']['name'],			'mail' => $result['User']['email'],			'telephone' => $telephone,			'type' => $this->App->traslateEnum($result['SuppliersOrganizationsReferent']['type'])	);
		$data[]['csv'] = $dataTmp;}

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