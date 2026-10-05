<?php 
$this->PhpExcel->createWorksheet();
$this->PhpExcel->setDefaultFont('Calibri', 12);

$this->App->d($results);

$table = [
	['label' => __('N'), 'width' => 'auto'],
	['label' => __('Code'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('Username'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('Name'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('RegisterDate'), 'filter' => true],
	['label' => __('LastvisitDate'), 'filter' => true],
	['label' => __('Ultimo acquisto'), 'filter' => true],	
	['label' => __('User Block'), 'filter' => true],
	['label' => __('User Can Login'), 'filter' => true],			
	['label' => __('Cf'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('Mail'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('Telephone'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('Address'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('City'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('CAP'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('Provincia'), 'width' => 'auto', 'wrap' => true, 'filter' => true],
	['label' => __('dataRichEnter'), 'filter' => true],
	['label' => __('dataEnter'), 'filter' => true],
	['label' => __('numDeliberaEnter'), 'filter' => true],
	['label' => __('dataDeliberaEnter'), 'filter' => true],
	['label' => __('dataRichExit'), 'filter' => true],
	['label' => __('motivoRichExit'), 'filter' => true],
	['label' => __('dataExit'), 'filter' => true],
	['label' => __('numDeliberaExit'), 'filter' => true],
	['label' => __('dataDeliberaExit'), 'filter' => true],
	['label' => __('dataRestituzCassa'), 'filter' => true],
	['label' => __('notaRestituzCassa'), 'filter' => true],
	['label' => __('Nota'), 'filter' => false],
	['label' => __('Role'), 'width' => 'auto', 'filter' => true],
	['label' => __('Suppliers Organizations Referents'), 'width' => 100, 'filter' => false]
];

// define table cells
if($isRoot) 
	$table += [['label' => __('Groups'), 'width' => 'auto', 'filter' => true]];

// heading
$this->PhpExcel->addTableHeader($table, ['name' => 'Cambria', 'bold' => true]);

foreach($results as $numResult => $result) {

	$data = [];
	
	if(!isset($result['Profile']['hasUserFlagPrivacy']))
		$result['Profile']['hasUserFlagPrivacy'] = __('NO');
		
	if(!isset($result['Profile']['hasUserRegistrationExpire']))
		$result['Profile']['hasUserRegistrationExpire'] = __('NO');
		
	if ($result['User']['block'] == 1)
		$block = __('Y');
	else
		$block = __('NO');	

	/*
	 * can_login per verificare gli utenti attivi, per i pagamenti annuali
	 */
	if ($result['User']['can_login'] == 0)
		$can_login = __('Y');
	else
		$can_login = __('NO');			
			
	if(!empty($result['User']['lastvisitDate']) && $result['User']['lastvisitDate']!=Configure::read('DB.field.datetime.empty')) 
		$lastvisitDate = $this->Time->i18nFormat($result['User']['lastvisitDate'],"%e %b %Y");
	else 
		$lastvisitDate = "";

	if(!empty($result['Cart']['date']) && $result['Cart']['date']!=Configure::read('DB.field.datetime.empty')) 
		$lastCartDate = $this->Time->i18nFormat($result['Cart']['date'],"%e %b %Y");
	else 
		$lastCartDate = "";
		
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
        
	$datas = [];
	$datas = [
			((int)$numResult+1),
			$result['Profile']['codice'],
			$result['User']['username'],
			$result['User']['name'],
			$this->Time->i18nFormat($result['User']['registerDate'],"%e %b %Y"),
			$lastvisitDate.' '.$result['User']['lastvisitDate'],
			$lastCartDate,
			$block,
			$can_login,			
			$result['Profile']['cf'],
			$result['User']['email'],
			$telephone,
			$address,
			$city,
			$postal_code,
			$region,
	];

	/*
	 * data Entrata/Uscita
	 */
	$datas[] = $result['Profile']['dataRichEnter'];
	$datas[] = $result['Profile']['dataEnter'];
	$datas[] = $result['Profile']['numDeliberaEnter'];
	$datas[] = $result['Profile']['dataDeliberaEnter'];
	$datas[] = $result['Profile']['dataRichExit'];
	$datas[] = $result['Profile']['motivoRichExit'];
	$datas[] = $result['Profile']['dataExit'];
	$datas[] = $result['Profile']['numDeliberaExit'];
	$datas[] = $result['Profile']['dataDeliberaExit'];
	$datas[] = $result['Profile']['dataRestituzCassa'];
	$datas[] = $result['Profile']['notaRestituzCassa'];
	$datas[] = $result['Profile']['nota'];
	
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
		$datas[] = $groupsTmp;
	}
	else 
		$datas[] = '';
			
	if(isset($result['SuppliersOrganization'])) {
		foreach($result['SuppliersOrganization'] as $numSuppliersOrganization => $suppliersOrganization)
			$datas[] = $suppliersOrganization['name']; /* .' '.$result['SuppliersOrganizationsReferent'][$numSuppliersOrganization]['type']; */
	}
	else
		$datas[] = '';
	
	if($isRoot) {
		$groupsTmp = "";
		if(isset($result['UserGroup'])) 
			foreach($result['UserGroup'] as $numUserGroup => $userGroup) 
				$groupsTmp .= $userGroup['title'].' - ';
		
		if(!empty($groupsTmp)) $groupsTmp = substr($groupsTmp, 0, (strlen($groupsTmp)-3));
		$datas[] = $groupsTmp;
	}	

	$this->PhpExcel->addTableRow($datas);
}

$this->PhpExcel->addTableFooter();
$this->PhpExcel->output($fileData['fileName'].'.xlsx');
?>