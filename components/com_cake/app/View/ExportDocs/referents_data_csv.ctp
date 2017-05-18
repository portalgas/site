<?php 
$headers = array('csv' => array(
		'N' => 'N',		
		'suppliers' => __('Suppliers'),
		'frequenza' => __('Frequenza'),
		'code' => __('Code'),
		'name' => __('Name'),
		'mail' => __('Mail'),
		'telephone' => __('Telephone'),
		)
); 

$data = array();
			'frequenza' => $result['SuppliersOrganization']['frequenza'],
			'code' => $result['Profile']['codice'],
			'name' => $result['User']['name'],
	

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