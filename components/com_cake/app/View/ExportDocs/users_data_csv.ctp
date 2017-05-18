<?php 
if($isRoot) 
	$headers = array('csv' => array(
			'N' => 'N',		
			'code' => __('Code'),
			'code' => __('Username'),
			'name' => __('Name'),
			'mail' => __('Mail'),
			'telephone' => __('Telephone'),
			'address' => __('Address'),
			'city' => __('City'),
			'cap' => __('CAP'),
			'provincia' => __('Provincia'),
			'DataRichEnter' => __('dataRichEnter'),
			'DataEnter' => __('dataEnter'),
			'numDeliberaEnter' => __('numDeliberaEnter'),
			'DataRichExit' => __('dataRichExit'),
			'DataExit' => __('dataExit'),
			'numDeliberaExit' => __('numDeliberaExit'),
			'dataRestituzCassa' => __('dataRestituzCassa'), 
			'groups' => __('Groups'),
			'role' => __('Role'),
			'suppliers_organizations_referent' => __('Suppliers Organizations Referents'),
			)
	); 
else
	$headers = array('csv' => array(
			'N' => 'N',
			'code' => __('Code'),
			'name' => __('username'),
			'name' => __('Name'),
			'mail' => __('Mail'),
			'telephone' => __('Telephone'),
			'address' => __('Address'),
			'city' => __('City'),
			'cap' => __('CAP'),
			'provincia' => __('Provincia'),
			'DataRichEnter' => __('DataRichEnter'),
			'DataEnter' => __('DataEnter'),
			'numDeliberaEnter' => __('numDeliberaEnter'),
			'DataRichExit' => __('DataRichExit'),
			'DataExit' => __('DataExit'),
			'numDeliberaExit' => __('numDeliberaExit'),
			'dataRestituzCassa' => __('dataRestituzCassa'), 
			'groups' => __('Groups'),
			'role' => __('Role'),
			'suppliers_organizations_referent' => __('Suppliers Organizations Referents'),
		)
	);

$data = array();
foreach($results as $numResult => $result) {

	$dataTmp = array();

	$telephone = "";
	if(!empty($result['Profile']['phone'])) $telephone .= $result['Profile']['phone'].' ';
	if(!empty($result['Profile']['phone2'])) $telephone .= $result['Profile']['phone2'];

	$address = "";
	if(!empty($result['Profile']['address'])) $address = $result['Profile']['address'];
        
	$city = "";
	if(!empty($result['Profile']['city'])) $city = $result['Profile']['city'];
        
	$region = ""; // provincia
	if(!empty($result['Profile']['region'])) $region = $result['Profile']['region'];
        
	$postal_code = "";  // cap
	if(!empty($result['Profile']['postal_code'])) $postal_code = $result['Profile']['postal_code'];
        
	$dataTmp = array(
			'N' => ($numResult+1),
			'code' => $result['Profile']['codice'],
			'username' => $result['User']['username'],
			'name' => $result['User']['name'],
			'mail' => $result['User']['email'],
			'telephone' => $telephone,
			'address' => $address,
			'city' => $city,
			'postal_code' => $postal_code,
			'region' => $region
	);
	
	/*
	 * data Entrata/Uscita
	 */
	$dataTmp['dataRichEnter'] = $result['Profile']['dataRichEnter'];
	$dataTmp['dataEnter'] = $result['Profile']['dataEnter'];
	$dataTmp['numDeliberaEnter'] = $result['Profile']['numDeliberaEnter'];
	$dataTmp['dataRichExit'] = $result['Profile']['dataRichExit'];
	$dataTmp['dataExit'] = $result['Profile']['dataExit'];
	$dataTmp['numDeliberaExit'] = $result['Profile']['numDeliberaExit'];
	$dataTmp['dataRestituzCassa'] = $result['Profile']['dataRestituzCassa'];
	
	if($isRoot) {
		$groupsTmp = "";
		if(isset($result['UserGroup'])) 
			foreach($result['UserGroup'] as $numUserGroup => $userGroup) 
				$groupsTmp .= $userGroup['title'].' - ';
		
		if(!empty($groupsTmp)) $groupsTmp = substr($groupsTmp, 0, (strlen($groupsTmp)-3));
		$dataTmp['group'] = $groupsTmp; 
	}
		
	if(isset($result['UserGroup'])) {
		$groupsTmp = "";
		foreach($result['UserGroup'] as $userGroup) {
			if($userGroup['id']==Configure::read('group_id_manager'))
				$groupsTmp .= __("UserGroupsManager").' - ';
			if($userGroup['id']==Configure::read('group_id_manager_delivery'))
				$groupsTmp .= __("UserGroupsManagerDelivery").' - ';
			if($userGroup['id']==Configure::read('group_id_cassiere'))
				$groupsTmp .= __("UserGroupsCassiere").' - ';
			if($userGroup['id']==Configure::read('group_id_tesoriere'))
				$groupsTmp .= __("UserGroupsTesoriere").' - ';
			if($userGroup['id']==Configure::read('group_id_super_referent'))
				$groupsTmp .= __("UserGroupsSuperReferent").' - ';
			if($userGroup['id']==Configure::read('group_id_generic'))
				$groupsTmp .= __("UserGroupsGeneric").' - ';						
		}
		if(!empty($groupsTmp)) $groupsTmp = substr($groupsTmp, 0, (strlen($groupsTmp)-3));
		$dataTmp['role'] = $groupsTmp;
	}
	else 
		$dataTmp['role'] = '';
	
	if(isset($result['SuppliersOrganization'])) {
		foreach($result['SuppliersOrganization'] as $numSuppliersOrganization => $suppliersOrganization)
			$dataTmp['suppliers_organizations_referent_'.$numSuppliersOrganization] = $suppliersOrganization['name']; /* .' '.$result['SuppliersOrganizationsReferent'][$numSuppliersOrganization]['type']; */
	}
	else
		$dataTmp['suppliers_organizations_referent'] = '';
	
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