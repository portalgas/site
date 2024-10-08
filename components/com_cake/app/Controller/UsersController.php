<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

class UsersController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function admin_index() {

		$debug = false;
		
        $this->set('isManager', $this->isManager());

        $FilterUserUserGroups = null;
        $FilterUserUsername = null;
        $FilterUserName = '';
        $FilterUserBlock = 'ALL';
        $FilterUserCanLogin = 'ALL';
        $FilterUserSort = Configure::read('orderUser');	

        /*
         * filtri per gruppo
         */
		switch($this->user->organization['Organization']['type']) {
			case 'GAS':
				$userGroups = [Configure::read('group_id_user') => __("UserGroupsUser"),
								Configure::read('group_id_manager') => __("UserGroupsManager"),
								Configure::read('group_id_manager_delivery') => __("UserGroupsManagerDelivery"),
								Configure::read('group_id_referent') => __("UserGroupsReferent"),
								Configure::read('group_id_super_referent') => __("UserGroupsSuperReferent"),
								Configure::read('group_id_tesoriere') => __("UserGroupsTesoriere"),
								Configure::read('group_id_generic') => __("UserGroupsGeneric")];
			break;
			case 'PRODGAS':
				$userGroups = [Configure::read('prod_gas_supplier_manager') => __("HasUserGroupsRootSupplier"),
								Configure::read('group_id_manager') => __("UserGroupsManager"),
								Configure::read('group_id_super_referent') => __("UserGroupsSuperReferent")];
			break;
			case 'PROD':
			
			break;
		} 


        /*
         * referente cassa (pagamento degli utenti alla consegna)
         */
        if ($this->user->organization['Template']['payToDelivery'] == 'ON' || $this->user->organization['Template']['payToDelivery'] == 'ON-POST')
            $userGroups += [Configure::read('group_id_cassiere') => __("UserGroupsCassiere")];

        $this->set(compact('userGroups'));

        /*
         * conditions
         */
        $conditions = [];
        $conditions = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
						'User.block' => "User.block in (0,1)"];  // 0 attivo - li prendo tutti perche' ora li posso disabilitare

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Username')) {
            $FilterUserUsername = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Username');
            $conditions['User.username'] = "User.username LIKE '%" . $FilterUserUsername . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterUserName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            $conditions['User.name'] = "User.name LIKE '%" . $FilterUserName . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'UserGroups')) {
            $FilterUserUserGroups = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'UserGroups');
            
			self::d($FilterUserUserGroups, $debug);
            $conditions['UserGroup.group_id'] = $FilterUserUserGroups;
        }
		
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
            $FilterUserBlock = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Block');
            if ($FilterUserBlock != 'ALL')
                $conditions['User.block'] = "User.block = $FilterUserBlock";  // 0 attivi / 1 disattivati
            else
                $conditions['User.block'] = "User.block IN ('0','1')";
        }
        else {
            $FilterUserBlock = 'ALL';
            $conditions['User.block'] = "User.block IN ('0','1')"; // di default li prende tutti
        }
        
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
            $FilterUserCanLogin = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'CanLogin');
            if ($FilterUserCanLogin != 'ALL')
                $conditions['User.can_login'] = "User.can_login = $FilterUserCanLogin";  // 0 no login / 1 si login
            else
                $conditions['User.can_login'] = "User.can_login IN ('0','1')";
        }
        else {
            $FilterUserCanLogin = 'ALL';
            $conditions['User.can_login'] = "User.can_login IN ('0','1')"; // di default li prende tutti
        }
        
        

        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Sort')) 
            $FilterUserSort = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Sort');
        else 
            $FilterUserSort = Configure::read('orderUser');

        if (empty($FilterUserUserGroups))
            $FilterUserUserGroups = Configure::read('prod_gas_supplier_manager') . ',' . 
					Configure::read('group_id_user') . ',' .
                    Configure::read('group_id_manager') . ',' .
                    Configure::read('group_id_manager_delivery') . ',' .
                    Configure::read('group_id_referent') . ',' .
                    Configure::read('group_id_super_referent') . ',' .
                    Configure::read('group_id_tesoriere') . ',' .
                    Configure::read('group_id_generic') . ',' .
                    Configure::read('group_id_cassiere');

        /* filtro */
        $this->set('FilterUserUsername', $FilterUserUsername);
        $this->set('FilterUserName', $FilterUserName);
        $this->set('FilterUserUserGroups', $FilterUserUserGroups);
        $this->set('FilterUserBlock', $FilterUserBlock);
        $this->set('FilterUserCanLogin', $FilterUserCanLogin);
        $this->set('FilterUserSort', $FilterUserSort);
		
        $block = ['ALL' => 'Tutti', '0' => 'Attivi', '1' => 'Disattivi'];
        $can_logins = ['ALL' => 'Tutti', '0' => 'Possono loggarsi', '1' => 'Non possono loggarsi'];	
		$sorts = [Configure::read('orderUser') => __('Name'), 
				'User.registerDate' => __('registerDate'), 
			   /* 'Profile.dataRichEnter' => __('dataRichEnter'), 
				'Profile.dataEnter' => __('dataEnter'), 
				'Profile.dataRichExit' => __('dataRichExit'), 
				'Profile.dataExit' => __('dataExit')*/
			];
        $this->set(compact('sorts', 'block', 'can_logins'));
		
		App::import('Model', 'Cart');
		$Cart = new Cart;
		
        // debug($conditions);
        $userResults = $this->User->getUsersComplete($this->user, $conditions, Configure::read('orderUser'), false);
		if(!empty($userResults)) {
			foreach($userResults as $numResult => $userResult) {
				
                $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $userResult['User']['organization_id']]);

				$cartResults = $Cart->getLastCartDateByUser($tmp_user, $userResult['User']['id'], $debug);
				$userResults[$numResult] += $cartResults; 
			}
		}
		self::d($userResults, $debug);		
        $this->set('results', $userResults);
    }

    public function admin_index_block() {

        $this->set('isManager', $this->isManager());

        $SqlLimit = 50;

        $conditions = [];
        $conditions[] = ['User.organization_id' => (int) $this->user->organization['Organization']['id'],
						'User.block' => 1];

        $this->User->recursive = 0;
        $this->paginate = ['conditions' => $conditions, 'order' => Configure::read('orderUser'), 'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
        $results = $this->paginate('User');

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        foreach ($results as $numResult => $result) {

            $userTmp = JFactory::getUser($result['User']['id']);
            $userProfile = JUserHelper::getProfile($userTmp->id);

            $results[$numResult]['Profile'] = $userProfile->profile;
        }
        $this->set('results', $results);
        $this->set('SqlLimit', $SqlLimit);
    }

    public function admin_index_flag_privacy() {
		
        if((empty($this->user->organization['Organization']['hasUserFlagPrivacy']) || $this->user->organization['Organization']['hasUserFlagPrivacy'] == 'N') && 
		   (empty($this->user->organization['Organization']['hasUserRegistrationExpire']) || $this->user->organization['Organization']['hasUserRegistrationExpire'] == 'N')) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

		$isUserFlagPrivay = $this->isUserFlagPrivay();
		$this->set(compact('isUserFlagPrivay'));
		
        $ctrlUserFlagPrivacys = [];
        if(isset($this->user->organization['Organization']['hasUserFlagPrivacy']) && $this->user->organization['Organization']['hasUserFlagPrivacy'] == 'Y') {
        	
			App::import('Model', 'UserGroupMap');
		  	$UserGroupMap = new UserGroupMap();
		  	
		  	$ctrlUserFlagPrivacys = $UserGroupMap->getUserFlagPrivacys($this->user);
        } 
        $this->set(compact('ctrlUserFlagPrivacys'));

        $this->set('isManager', $this->isManager());

        $FilterUserUsername = '';
        $FilterUserName = '';
        $FilterUserProfileCF = '';
        $FilterUserBlock = 'ALL';
        $FilterUserCanLogin = 'ALL';
        $FilterUserSort = Configure::read('orderUser');		
        $FilterUserHasUserFlagPrivacy = 'ALL';
        $FilterUserHasUserRegistrationExpire = 'ALL';

        /*
         * conditions
         */
        $conditions = [];

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Username')) {
            $FilterUserUsername = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Username');
            $conditions['User.username'] = "User.username LIKE '%" . $FilterUserUsername . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterUserName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            $conditions['User.name'] = "User.name LIKE '%" . $FilterUserName . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'ProfileCF')) {
            $FilterUserProfileCF = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'ProfileCF');
            $conditions['UserProfile.CF'] = $FilterUserProfileCF;
        }        
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
            $FilterUserBlock = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Block');
            if ($FilterUserBlock != 'ALL')
                $conditions['User.block'] = "User.block = $FilterUserBlock";  // 0 attivi / 1 disattivati
            else
                $conditions['User.block'] = "User.block IN ('0','1')";
        }
        else {
            $FilterUserBlock = 'ALL';
            $conditions['User.block'] = "User.block IN ('0','1')"; // di default li prende tutti
        }
        
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
            $FilterUserCanLogin = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'CanLogin');
            if ($FilterUserCanLogin != 'ALL')
                $conditions['User.can_login'] = "User.can_login = $FilterUserCanLogin";  // 0 no login / 1 si login
            else
                $conditions['User.can_login'] = "User.can_login IN ('0','1')";
        }
        else {
            $FilterUserCanLogin = 'ALL';
            $conditions['User.can_login'] = "User.can_login IN ('0','1')"; // di default li prende tutti
        }

        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserFlagPrivacy')) {
            $FilterUserHasUserFlagPrivacy = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserFlagPrivacy');
            $conditions['UserProfile.UserFlagPrivacy'] = $FilterUserHasUserFlagPrivacy;
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserRegistrationExpire')) {
            $FilterUserHasUserRegistrationExpire = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'HasUserRegistrationExpire');
            $conditions['UserProfile.UserRegistrationExpire'] = $FilterUserHasUserRegistrationExpire;
        }
		
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Sort')) 
            $FilterUserSort = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Sort');
        else 
            $FilterUserSort = Configure::read('orderUser');
        
        /* filtro */
        $this->set('FilterUserUsername', $FilterUserUsername);
        $this->set('FilterUserName', $FilterUserName);
        $this->set('FilterUserProfileCF', $FilterUserProfileCF);
        $this->set('FilterUserBlock', $FilterUserBlock);
        $this->set('FilterUserCanLogin', $FilterUserCanLogin);
        $this->set('FilterUserHasUserFlagPrivacy', $FilterUserHasUserFlagPrivacy);
        $this->set('FilterUserHasUserRegistrationExpire', $FilterUserHasUserRegistrationExpire);
        $this->set('FilterUserSort', $FilterUserSort);

        $block = ['ALL' => 'Tutti', '0' => 'Attivi', '1' => 'Disattivi'];
        $can_logins = ['ALL' => 'Tutti', '0' => 'Possono loggarsi', '1' => 'Non possono loggarsi']; 
        $hasUserFlagPrivacys = ['ALL' => 'Tutti', 'Y' => __('Y'), 'N' => __('No')];
        $hasUserRegistrationExpires = ['ALL' => 'Tutti', 'Y' => __('Y'), 'N' => __('No')];
        $this->set(compact('block', 'hasUserFlagPrivacys', 'hasUserRegistrationExpires', 'can_logins'));

        $sorts = [Configure::read('orderUser') => __('Name'), 
                        'User.registerDate' => __('registerDate'), 
                       /* 'Profile.dataRichEnter' => __('dataRichEnter'), 
                        'Profile.dataEnter' => __('dataEnter'), 
                        'Profile.dataRichExit' => __('dataRichExit'), 
                        'Profile.dataExit' => __('dataExit')*/
                    ];
        $this->set('sorts', $sorts);
                
        self::d($conditions, $debug);

		$results = [];
		
		App::import('Model', 'Cart');
		$Cart = new Cart;
		
        App::import('Model', 'Organization');
        $Organization = new Organization;
		
        $options = [];
        $options['conditions'] = ['Organization.id' => $this->user->organization['Organization']['id']];
        $options['recursive'] = -1;
        $results = $Organization->find('first', $options);
		
        $userResults = $this->User->getUsersComplete($this->user, $conditions, $FilterUserSort, false);
		if(!empty($userResults)) {
			$results['User'] = $userResults;
			
			foreach($results['User'] as $numResult2 => $result) {
				
                $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $result['User']['organization_id']]);

				$cartResults = $Cart->getLastCartDateByUser($tmp_user, $result['User']['id'], $debug);    
				$results['User'][$numResult2] += $cartResults; 
			}
		}
		self::d($results, $debug);
		
        $this->set(compact('results'));
    }
	
    public function admin_index_date() {

        $this->set('isManager', $this->isManager());

        $FilterUserUsername = null;
        $FilterUserName = '';
        $FilterUserBlock = 'ALL';
        $FilterUserSort = Configure::read('orderUser');
        
        /*
         * conditions
         */
        $conditions = [];

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Username')) {
            $FilterUserUsername = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Username');
            $conditions['User.username'] = "User.username LIKE '%" . $FilterUserUsername . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterUserName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            $conditions['User.name'] = "User.name LIKE '%" . $FilterUserName . "%'";
        }
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Block')) {
            $FilterUserBlock = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Block');
            if ($FilterUserBlock != 'ALL')
                $conditions['User.block'] = "User.block = $FilterUserBlock";  // 0 attivi / 1 disattivati
            else
                $conditions['User.block'] = "User.block IN ('0','1')";
        }
        else {
            $FilterUserBlock = 'ALL';
            $conditions['User.block'] = "User.block IN ('0','1')"; // di default li prende tutti
        }
   
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Sort')) 
            $FilterUserSort = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Sort');
        else 
            $FilterUserSort = Configure::read('orderUser');
        
        /* filtro */
        $this->set('FilterUserUsername', $FilterUserUsername);
        $this->set('FilterUserName', $FilterUserName);
        $this->set('FilterUserBlock', $FilterUserBlock);
        $this->set('FilterUserSort', $FilterUserSort);

        $block = ['ALL' => 'Tutti', '0' => 'Attivi', '1' => 'Disattivi'];
        $this->set('block', $block);

        $sorts = [Configure::read('orderUser') => __('Name'), 
                        'User.registerDate' => __('registerDate'), 
                       /* 'Profile.dataRichEnter' => __('dataRichEnter'), 
                        'Profile.dataEnter' => __('dataEnter'), 
                        'Profile.dataRichExit' => __('dataRichExit'), 
                        'Profile.dataExit' => __('dataExit')*/
                    ];
        $this->set('sorts', $sorts);
                
        self::d($conditions, $debug);

        $results = $this->User->getUsersComplete($this->user, $conditions, $FilterUserSort, false);
        $this->set('results', $results);
    }

    public function admin_index_date_update() {

        $debug = false;

        $organization_id = $this->request->data['organization_id'];
		/*
		 * ACL
		*/ 		
		if($organization_id!=$this->user->organization['Organization']['id']) {
			
			$continua = true;
			
			if(!$this->isManagerUserDes())
				$continua = false;
			
			if($continua) {
				$continua = $this->aclOrganizationIdinUso($organization_id);
			}

			if(!$continua) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));				
			}			
		}
		/*
		 * ACL
		*/ 
		
        $user_id = $this->request->data['user_id'];
        $field_db = 'profile.' . $this->request->data['field_db'];
        $data_db = $this->request->data['data_db'];

        $sql = 'SELECT * from ' . Configure::read('DB.portalPrefix') . 'user_profiles WHERE user_id = ' . $user_id . ' and profile_key = "' . $field_db . '"';
        self::d($sql, $debug);
        $executeSql = $this->User->query($sql);

        if (empty($executeSql))
            $sql = 'INSERT INTO ' . Configure::read('DB.portalPrefix') . 'user_profiles (user_id,profile_key,profile_value) VALUES (' . $user_id . ', "' . $field_db . '", "\"' . $data_db . '\"")';
        else
            $sql = 'UPDATE ' . Configure::read('DB.portalPrefix') . 'user_profiles SET profile_value = "\"' . $data_db . '\"" WHERE user_id = ' . $user_id . ' AND profile_key = "' . $field_db . '"';
        self::d($sql, $debug);
        $executeSql = $this->User->query($sql);


        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }

    public function profile() {

        $debug = false;

        $user_id = $this->user->get('id');
        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_not_permission_guest'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }

        /*
         * upload img user
         */
        if ($this->request->is('post') || $this->request->is('put')) {

            $msg = '';

            self::d($this->request->data, $debug);

            /*
             * 	$file1 = [
             * 		'name' => 'immagine.jpg',
             * 		'type' => 'image/jpeg',
             * 		'tmp_name' => /tmp/phpsNYCIB',
             * 		'error' => 0,
             * 		'size' => 41737,
             * 	];
             *
             * UPLOAD_ERR_OK (0): Non vi sono errori, l’upload e' stato eseguito con successo;
             * UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
             * UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
             * UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
             * UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
             * UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
             */
            if (!empty($this->request->data['Document']['file1']['name'])) {

                $file1 = $this->request->data['Document']['file1'];
                if ($file1['error'] == UPLOAD_ERR_OK && is_uploaded_file($file1['tmp_name'])) {

                    $path_upload = Configure::read('App.root') . Configure::read('App.img.upload.user') . DS . $this->user->organization['Organization']['id'] . DS;

                    /*
                     * ctrl exstension / content type
                     */
                    $ext = strtolower(pathinfo($file1['name'], PATHINFO_EXTENSION));

                    $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
                    $type = finfo_file($finfo, $file1['tmp_name']);
                    finfo_close($finfo);

                    $arr_extension = array_merge(Configure::read('App.web.pdf.upload.extension'), Configure::read('App.web.img.upload.extension'));
                    $arr_contentType = array_merge(Configure::read('ContentType.pdf'), Configure::read('ContentType.img'));
                    if (!in_array($ext, $arr_extension) || !in_array($type, $arr_contentType)) {
                        $msg = "Estensione .$ext non valida: si possono caricare file con la seguente estensione ";
                        foreach ($arr_extension as $estensione)
                            $msg .= '.' . $estensione . '&nbsp;';

                        self::d([$ext, $type, $msg], $debug);
                    }

                    if (empty($msg)) {

                        $fileNewName = $user_id . '.' . $ext;
                        self::d(['path_upload '.$path_upload, 'ext '.$ext, 'fileNewName '.$fileNewName], $debug);
                           
                        if (!move_uploaded_file($file1['tmp_name'], $path_upload . $fileNewName))
                            $msg = $file1['error'];
                        else {

                            $info = getimagesize($path_upload . $fileNewName);
                            $width = $info[0];
                            $height = $info[1];
                            self::d($info, $debug);

                            /*
                             * ridimensiona img
                             */
                            if ($width > Configure::read('App.web.img.upload.width.user')) {
                                $status = ImageTool::resize([
                                            'input' => $path_upload . $fileNewName,
                                            'output' => $path_upload . $fileNewName,
                                            'width' => Configure::read('App.web.img.upload.width.user'),
                                            'height' => '']);

                                self::d("ridimensiono " . $status, $debug);
                            }

                            $msg = "Immagine caricata correttamente";
                        }
                    }  // end if(empty($msg))
                }
                else {
                    $msg = $file1['error'];
                }

                if ($msg == UPLOAD_ERR_OK)
                    $msg = "Immagine caricata correttamente";

                self::d("msg " . $msg, $debug);

                $this->Session->setFlash($msg);
            } // end if(!empty($this->request->data['Document']['file1']['name']))
        } // end if ($this->request->is('post') || $this->request->is('put'))

        $options = [];
        $options['conditions'] = ['User.id' => $user_id,
								  'User.organization_id' => (int) $this->user->organization['Organization']['id']];
        $options['recursive'] = -1;

        $this->User->unbindModel(['hasMany' => ['Cart']]);
        $results = $this->User->find('first', $options);

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        $userProfile = JUserHelper::getProfile($user_id);

        $results['Profile'] = $userProfile->profile;


        /*
         * G R O U P
         */
        $sql = "SELECT `Group`.title FROM
                        " . Configure::read('DB.portalPrefix') . "users User,
                        " . Configure::read('DB.portalPrefix') . "user_usergroup_map UserGroup,
                        " . Configure::read('DB.portalPrefix') . "usergroups AS `Group`
                WHERE
                        User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND UserGroup.user_id = User.id
                        AND UserGroup.group_id = `Group`.id
                        AND User.id = $user_id 
                        ORDER BY `Group`.title ";
        self::d($sql, false);
        try {
            $groupResults = $this->User->query($sql);
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        foreach ($groupResults as $numGroupResult => $groupResult)
            $results['UserGroup'][$numGroupResult]['UserGroup'] = $groupResult['Group'];

        /*
         * R E F E R E N T I
         */
        $sql = "SELECT
                        SuppliersOrganizationsReferent.type,
                        SuppliersOrganization.*, 
                        Supplier.* 
                FROM
                        " . Configure::read('DB.portalPrefix') . "users User,
                        " . Configure::read('DB.prefix') . "suppliers_organizations_referents SuppliersOrganizationsReferent,
                        " . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
                        " . Configure::read('DB.prefix') . "suppliers Supplier  
                WHERE
                        User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                        AND SuppliersOrganizationsReferent.organization_id =  " . (int) $this->user->organization['Organization']['id'] . "
                        AND SuppliersOrganizationsReferent.user_id = User.id
                        AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id
                        AND Supplier.id = SuppliersOrganization.supplier_id
                        AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG') 
                        AND SuppliersOrganization.stato = 'Y' 
                        AND User.id = $user_id 
                ORDER BY SuppliersOrganization.name ";
        self::d($sql, false);
        try {
            $supplierResults = $this->User->query($sql);
        } catch (Exception $e) {
            CakeLog::write('error', $sql);
            CakeLog::write('error', $e);
        }

        foreach ($supplierResults as $numSupplierResult => $supplierResult) {
            $results['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganization'] = $supplierResult['SuppliersOrganization'];
            $results['SuppliersOrganization'][$numSupplierResult]['Supplier'] = $supplierResult['Supplier'];
            $results['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganizationsReferent'] = $supplierResult['SuppliersOrganizationsReferent'];
        }

        $this->set('results', $results);

        $this->layout = 'default_front_end';
    }

    public function gmaps() {
        $options = [];
        $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
								  'User.block' => 0];
        $options['order'] = Configure::read('orderUser');
        $options['recursive'] = -1;
        $results = $this->User->find('all', $options);

        /*
         * userprofile
         */
        jimport('joomla.user.helper');
        $i = 0;
        $newResults = [];
        foreach ($results as $numResult => $result) {

            $userTmp = JFactory::getUser($result['User']['id']);
            $userProfile = JUserHelper::getProfile($userTmp->id);

            /*
             *  se il Cron non trova lat/lng perche' i dati non sono corretti, imposto a 0.0 se no non esegue i successivi
             */
            if (!empty($userProfile->profile['lat']) && $userProfile->profile['lat'] != Configure::read('LatLngNotFound') && !empty($userProfile->profile['lng']) && $userProfile->profile['lng'] != Configure::read('LatLngNotFound')) {

                $newResults[$i] = $result;
                $newResults[$i]['Profile'] = $userProfile->profile;



                /*
                 * R E F E R E N T I
                 */
                $sql = "SELECT
                                SuppliersOrganizationsReferent.type,
                                SuppliersOrganization.name, 
                                Supplier.img1,Supplier.descrizione,Supplier.localita,Supplier.provincia   
                        FROM
                                " . Configure::read('DB.portalPrefix') . "users User,
                                " . Configure::read('DB.prefix') . "suppliers_organizations_referents SuppliersOrganizationsReferent,
                                " . Configure::read('DB.prefix') . "suppliers_organizations SuppliersOrganization,
                                " . Configure::read('DB.prefix') . "suppliers Supplier  
                        WHERE
                                User.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                                AND SuppliersOrganization.organization_id = " . (int) $this->user->organization['Organization']['id'] . "
                                AND SuppliersOrganizationsReferent.organization_id =  " . (int) $this->user->organization['Organization']['id'] . "
                                AND SuppliersOrganizationsReferent.user_id = User.id
                                AND SuppliersOrganizationsReferent.supplier_organization_id = SuppliersOrganization.id
                                AND Supplier.id = SuppliersOrganization.supplier_id
                                AND (Supplier.stato = 'Y' or Supplier.stato = 'T' or Supplier.stato = 'PG')  
                                AND SuppliersOrganization.stato = 'Y' 
                                AND User.id = " . $result['User']['id'] . " 
                        ORDER BY SuppliersOrganization.name ";
                self::d($sql, false);
                try {
                    $supplierResults = $this->User->query($sql);
                } catch (Exception $e) {
                    CakeLog::write('error', $sql);
                    CakeLog::write('error', $e);
                }

                foreach ($supplierResults as $numSupplierResult => $supplierResult) {
                    $newResults[$i]['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganization'] = $supplierResult['SuppliersOrganization'];
                    $newResults[$i]['SuppliersOrganization'][$numSupplierResult]['Supplier'] = $supplierResult['Supplier'];
                    $newResults[$i]['SuppliersOrganization'][$numSupplierResult]['SuppliersOrganizationsReferent'] = $supplierResult['SuppliersOrganizationsReferent'];
                }

                $i++;
            }
        } // foreach ($results as $numResult => $result)

        /* 	
          echo "<pre>";
          print_r($newResults);
          echo "</pre>";
         */
        $this->set('results', $newResults);

        $this->layout = 'default_front_end';
    }

    public function bookmarks_mails() {

        App::import('Model', 'BookmarksMail');
        $BookmarksMail = new BookmarksMail;

        $options = [];
        $options['conditions'] = ['BookmarksMail.organization_id' => (int) $this->user->organization['Organization']['id'],
								  'BookmarksMail.user_id' => $this->user->id];

        $options['order'] = ['BookmarksMail.supplier_organization_id'];
        $options['fields'] = ['BookmarksMail.supplier_organization_id', 'BookmarksMail.order_open', 'BookmarksMail.order_close'];
        $options['recursive'] = -1;
        $bookmarksMailResults = $BookmarksMail->find('all', $options);

        // debug($bookmarksMailResults);

        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;
        $SuppliersOrganization->unbindModel(['belongsTo' => ['Organization']]);

        $options = [];
        $options['conditions'] = ['SuppliersOrganization.organization_id' => (int) $this->user->organization['Organization']['id'],
								'SuppliersOrganization.stato' => 'Y',
								'Supplier.stato IN ' => ['Y', 'PG']]; // escludo i Temporanei perche' possono essere produttori di appoggio utilizzati dal gas
        $options['order'] = ['SuppliersOrganization.name'];
        $options['recursive'] = 0;
        $suppliersOrganizationResults = $SuppliersOrganization->find('all', $options);

        $results = [];
        foreach ($suppliersOrganizationResults as $suppliersOrganizationResult) {
            $results[$suppliersOrganizationResult['SuppliersOrganization']['id']] = $suppliersOrganizationResult;

            /*
             * di default sono a Y
             */
            $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_open'] = 'Y';
            $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_close'] = 'Y';

            foreach ($bookmarksMailResults as $numResult => $bookmarksMailResult) {
                if ($suppliersOrganizationResult['SuppliersOrganization']['id'] == $bookmarksMailResult['BookmarksMail']['supplier_organization_id']) {
                    $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_open'] = $bookmarksMailResult['BookmarksMail']['order_open'];
                    $results[$suppliersOrganizationResult['SuppliersOrganization']['id']]['BookmarksMail']['order_close'] = $bookmarksMailResult['BookmarksMail']['order_close'];

                    unset($bookmarksMailResults[$numResult]);
                }
            }
        }
       
		self::d($results, false);
	   
        $this->set(compact('results'));

        $this->layout = 'default_front_end';
    }

    /*
     * $field order_open / order_close
     * $value Y / N
     */
    public function bookmarks_mails_update($supplier_organization_id, $field, $value) {

        App::import('Model', 'BookmarksMail');
        $BookmarksMail = new BookmarksMail;

        $BookmarksMail->update($this->user, $supplier_organization_id, $field, $value);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
    }
    
	/* 
	 * passato un campo (User.block) inverte il valore Y => N
	 */
    public function admin_inverseValue($organization_id, $user_id, $field, $format='notmpl') {

		$debug = false;
		
        if (empty($organization_id) && empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /* ctrl ACL */
		if ($this->user->organization['Organization']['hasDes'] == 'N' || $this->user->organization['Organization']['hasDesUserManager'] != 'Y' || empty($this->user->des_id)) {
            $this->Session->setFlash(__('msg_not_organization_config'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
		
        if (!$this->isManagerUserDes()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */
		
        App::import('Model', 'DesOrganization');
        $DesOrganization = new DesOrganization;
				
        /*
         * tutti i GAS del DES
         */
        $options = [];
        $options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id,
								  'DesOrganization.organization_id' => $organization_id];
        $options['recursive'] = 1;
        $desOrganizationsResults = $DesOrganization->find('first', $options);
		if(empty($desOrganizationsResults)) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));			
		}

        $options = [];
        $options['conditions'] = ['User.organization_id' => $organization_id,
								  'User.id' => $user_id];
        $options['recursive'] = -1;
        $userResults = $this->User->find('first', $options);
		if(empty($userResults)) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		self::d($field);
		self::d($userResults);

		if(isset($userResults['User'][$field])) {

			self::d($userResults['User'][$field]);

			switch ($userResults['User'][$field]) {
				case '0':
					$userResults['User'][$field] = '1';
				break;
				case '1':
					$userResults['User'][$field] = '0';
				break;
				default:
					$userResults['User'][$field] = '0';
				break;
			}

			self::d($userResults['User'][$field]);
			self::d($userResults);
			
			$this->User->create();
			if (!$this->User->save($userResults)) {
			}
		
		}

        $this->set('content_for_layout', '');

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
   }    
   
	/* 
	 * passato un campo (User.block) inverte il valore Y => N
	 */
    public function admin_inverseValueNoDES($user_id, $field, $format='notmpl') {

		$debug = false;
		
        if (empty($user_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
        if (!$this->isManager()) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));
        }
        /* ctrl ACL */
		
        $options = [];
        $options['conditions'] = ['User.organization_id' => $this->user->organization['Organization']['id'],
								  'User.id' => $user_id];
        $options['recursive'] = -1;
        $userResults = $this->User->find('first', $options);
		if(empty($userResults)) {
            $this->Session->setFlash(__('msg_not_permission'));
            $this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		self::d($field, $debug);
		self::d($userResults, $debug);

		if(isset($userResults['User'][$field])) {

			self::d($userResults['User'][$field], $debug);

			switch ($userResults['User'][$field]) {
				case '0':
					$userResults['User'][$field] = '1';
				break;
				case '1':
					$userResults['User'][$field] = '0';
				break;
				default:
					$userResults['User'][$field] = '0';
				break;
			}

			self::d($userResults['User'][$field], $debug);
			self::d($userResults, $debug);
			
			$this->User->create();
			if (!$this->User->save($userResults)) {
			}
		
		}

        $this->set('content_for_layout', '');

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');
   }       
}