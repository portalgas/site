<?php
class CsvImportsController extends AppController {

	private $array_um = [];
	private $array_y_n = ['Y','N'];
	
	private $esito_value = '';
	private $esito_row = true;
	
	public function beforeFilter() {
		parent::beforeFilter();
	
		$this->array_um = ClassRegistry::init('Article')->enumOptions('um');
		$this->set('array_um', $this->array_um);
		$this->set('array_y_n', $this->array_y_n);	}

	public function admin_users() {
		
		if(!$this->isManager()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		if(isset($this->request->data['CsvImport']['deliminatore']))
			$deliminatore = $this->request->data['CsvImport']['deliminatore'];
		else
			$deliminatore = Configure::read('CsvImportDelimiterDefault');
		
		if(isset($this->request->data['CsvImport']['password_default']))
			$password_default = $this->request->data['CsvImport']['password_default'];
		else
			$password_default = '';
		
		$this->set(compact('deliminatore', 'password_default'));
		
		$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, 'COMPLETE');
		$this->set(compact('struttura_file'));
	}
	
	public function admin_articles() {

		if(!$this->isReferentGeneric()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
		/*
		 * get elenco categorie articoli
		*/
		if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
			App::import('Model', 'Article');
			$Article = new Article;
			
			$conditions = ['organization_id' => $this->user->organization['Organization']['id']];
			$categories = $Article->CategoriesArticle->generateTreeList($conditions, null, null, '&nbsp;&nbsp;&nbsp;');
			$this->set(compact('categories'));
		}

		/*
		 * campi filtro
		 */
		if(isset($this->request->data['CsvImport']['supplier_organization_id']))
			$supplier_organization_id = $this->request->data['CsvImport']['supplier_organization_id'];
		else 
			$supplier_organization_id = null;
		
		if(isset($this->request->data['CsvImport']['category_article_id']))
			$category_article_id = $this->request->data['CsvImport']['category_article_id'];
		else
			$category_article_id = null;
		
		if(isset($this->request->data['CsvImport']['deliminatore']))
			$deliminatore = $this->request->data['CsvImport']['deliminatore'];
		else
			$deliminatore = Configure::read('CsvImportDelimiterDefault');
		
		$versions = ['COMPLETE' => 'Completa', 'SIMPLE' => 'Semplificata'];
		$version = 'SIMPLE';
		$this->set(compact('versions', 'version'));
		
		$this->set(compact('supplier_organization_id', 'category_article_id', 'deliminatore'));
		
		$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, 'COMPLETE');
		$this->set(compact('struttura_file'));		
	}
	
	public function admin_articles_prepare() {
	
		$debug = false;
		$msg = "";
		$results = [];
		
		if ($this->request->is('post') || $this->request->is('put')) {

			self::d($this->request->data, $debug);
		
			/*
			 * campi filtro
			*/
			$supplier_organization_id = $this->request->data['CsvImport']['supplier_organization_id'];
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y') 
				$category_article_id = $this->request->data['CsvImport']['category_article_id'];
			else 
				$category_article_id = 0;
			$deliminatore = $this->request->data['CsvImport']['deliminatore'];
			$file1 = $this->request->data['Document']['file1'];
			$version = $this->request->data['CsvImport']['version'];
			
			$this->set(compact('supplier_organization_id', 'category_article_id', 'deliminatore', 'version'));
	
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
				if(empty($supplier_organization_id) || empty($category_article_id) || empty($deliminatore) || $file1['size']==0) {
					$this->Session->setFlash(__('msg_error_params'));
					$this->myRedirect(Configure::read('routes_msg_exclamation'));
				}
			}
			else {
				if(empty($supplier_organization_id) || empty($deliminatore) || $file1['size']==0) {
					$this->Session->setFlash(__('msg_error_params'));
					$this->myRedirect(Configure::read('routes_msg_exclamation'));
				}
			}
	
			$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, $version);
			$this->set(compact('struttura_file'));
		
			$result = $this->_readFileSend($file1, $deliminatore, $version, false, $supplier_organization_id, $debug);
			$esito = $result['esito'];
			$results = $result['results'];

			if($esito!==true) {
				$this->Session->setFlash($esito);
				$this->myRedirect(array('action' => 'articles'));
			}
				
		} // if ($this->request->is('post') || $this->request->is('put'))
		else {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));			
		}	

		$this->set('totRows',$totRows);
		if($totRows>Configure::read('CsvImportRowsMaxArticles')) 
			$this->set('results',[]);
		else
			$this->set('results',$results);
	}	
	
	public function admin_users_prepare() {
	
		$debug = false;
		$msg = "";
		$results = [];
	
		if ($this->request->is('post') || $this->request->is('put')) {
				
			self::d($this->request->data, $debug);
				
			/*
			 * campi filtro
			*/
			$deliminatore = $this->request->data['CsvImport']['deliminatore'];
			$password_default = $this->request->data['CsvImport']['password_default'];	
			$version = 'COMPLETE';
			
			$this->set(compact('deliminatore', 'password_default', 'version'));
						
			$file1 = $this->request->data['Document']['file1'];

			if(empty($password_default) || empty($deliminatore) || $file1['size']==0) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			
			$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, $version);
			$this->set(compact('struttura_file'));
					
			$result = $this->_readFileSend($file1, $deliminatore, 'COMPLETE', false, 0, $debug);
			$esito = $result['esito'];
			$results = $result['results'];
			
			if($esito!==true) {
				$this->Session->setFlash($esito);
				$this->myRedirect(array('action' => 'users'));
			}
	
		} // if ($this->request->is('post') || $this->request->is('put'))
		else {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		$this->set('totRows',$totRows);
		if($totRows>Configure::read('CsvImportRowsMaxUsers'))
			$this->set('results',[]);
		else
			$this->set('results',$results);
	}
	
	public function admin_articles_insert() {

		$debug = false;
		$msg = "";
	
		if ($this->request->is('post') || $this->request->is('put')) {

			self::d($this->request->data, $debug);
			
			/*
			 * campi filtro
			*/
			$supplier_organization_id = $this->request->data['CsvImport']['supplier_organization_id'];
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
				$category_article_id = $this->request->data['CsvImport']['category_article_id'];
			else
				$category_article_id = 0;
			
			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y') {
				if(empty($supplier_organization_id) || empty($category_article_id)) {
					$this->Session->setFlash(__('msg_error_params'));
					$this->myRedirect(Configure::read('routes_msg_exclamation'));
				}
			}
			else {
				if(empty($supplier_organization_id)) {
					$this->Session->setFlash(__('msg_error_params'));
					$this->myRedirect(Configure::read('routes_msg_exclamation'));
				}
			}
			/*
			 * ctrl che sia referente del produttore
			 */
			App::import('Model', 'SuppliersOrganizationsReferent');
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
			if(!$SuppliersOrganizationsReferent->aclReferenteSupplierOrganization($this->user, $supplier_organization_id)) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));				
			}
			
			unset($this->request->data['CsvImport']['supplier_organization_id']);
			unset($this->request->data['CsvImport']['category_article_id']);
			unset($this->request->data['CsvImport']['deliminatore']);
			unset($this->request->data['CsvImport']['version']);
			
			App::import('Model', 'Article');
			
			foreach ($this->request->data['CsvImport'] as $result) {
				
				$rows = [];
				
				/*
				 * escludo i campi hidden
				*/
				if(isset($result['name'])) {
				
					$Article = new Article;
					
					$rows['Article']['organization_id'] = $this->user->organization['Organization']['id'];
					$rows['Article']['id'] = $Article->getMaxIdOrganizationId($this->user->organization['Organization']['id']);
					$rows['Article']['supplier_organization_id'] = $supplier_organization_id;
					$rows['Article']['category_article_id'] = $category_article_id;
		
					$rows['Article']['name'] = $result['name'];
					if($this->user->organization['Organization']['hasFieldArticleCodice']=='Y')
						$rows['Article']['codice'] = $result['codice'];
					$rows['Article']['nota'] = $result['nota'];
					if($this->user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
						$rows['Article']['ingredienti'] = $result['ingredienti'];
					$rows['Article']['prezzo'] = $result['prezzo'];
					$rows['Article']['qta'] = $result['qta'];
					$rows['Article']['um'] = $result['um'];
					$rows['Article']['um_riferimento'] = $result['um_riferimento'];
					/*
					 * campi non presenti nella version SIMPLE
					 */
					if(!isset($result['pezzi_confezione']))
						$rows['Article']['pezzi_confezione'] = 1;
					else 
						$rows['Article']['pezzi_confezione'] = $result['pezzi_confezione'];
					
					if(!isset($result['qta_minima']))
						$rows['Article']['qta_minima'] = 1;
					else 					
						$rows['Article']['qta_minima'] = $result['qta_minima'];
					
					if(!isset($result['qta_massima']))
						$rows['Article']['qta_massima'] = 0;
					else 				
						$rows['Article']['qta_massima'] = $result['qta_massima'];
					
					if(!isset($result['qta_minima_order']))
						$rows['Article']['qta_minima_order'] = 0;
					else 				
						$rows['Article']['qta_minima_order'] = $result['qta_minima_order'];
					
					if(!isset($result['qta_massima_order']))
						$rows['Article']['qta_massima_order'] = 0;
					else 				
						$rows['Article']['qta_massima_order'] = $result['qta_massima_order'];
					
					if(!isset($result['qta_multipli']))
						$rows['Article']['qta_multipli'] = 1;
					else 				
						$rows['Article']['qta_multipli'] = $result['qta_multipli'];
					
					if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N') 
						$rows['Article']['alert_to_qta'] = 0;
					
					if(!isset($result['bio']))
						$rows['Article']['bio'] = 'N';
					else
						$rows['Article']['bio'] = $result['bio'];
					$rows['Article']['stato'] = 'Y';
			
					self::d($rows, $debug);			
					
					/*
					 * richiamo la validazione
					*/
					$Article->set($rows);
					if(!$Article->validates()) {
						$errors = $Article->validationErrors;
						$tmp = '';
						$flatErrors = Set::flatten($errors);
						if(count($errors) > 0) { 
							$tmp = '';
							foreach($flatErrors as $key => $value) 
								$tmp .= $value.' - ';
						}
						$msg .= $rows['Article']['name']." non inserito: dati non validi, $tmp<br />";
					}
					else {
						$Article->create();
						if($Article->save($rows)) {
							$article_id = $rows['Article']['id'];
							
							if($debug) $msg .= $rows['Article']['name']." inserito con ID $article_id<br />";
							
							if($rows['Article']['bio']=='Y') {
			
								/*
								 * k_articles_articles_types
								*/
								$sql = "INSERT INTO ".Configure::read('DB.prefix')."articles_articles_types
											(organization_id,article_id,article_type_id)
											VALUES (".$this->user->organization['Organization']['id'].",".$article_id.",1)";  // BIO
								self::d($sql, $debug);
								$executeInsert = $Article->query($sql);	
							} // end if($rows['Article']['bio']=='Y') 		 			
						} 
						else {
							$msg .= $rows['Article']['name']." non inserito<br />";
						} // end if(!$Article->validates())
					}
					
				} // if(isset($result['name'])) 	
			} // foreach ($this->request->data['CsvImport'] as $csvImport)

			if($debug) echo $msg;
			if($debug) exit;
			
			if(empty($msg)) {
				$msg = __('Csv Import Articles has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_articles_index&FilterArticleSupplierId='.$supplier_organization_id.'&FilterArticleCategoryArticleId='.$category_article_id.'&FilterArticleStato=Y';
			}
			else 
				$url = ['action' => 'articles'];

			self::d($msg, $debug);
			self::d($url, $debug);
			
			$this->Session->setFlash($msg);
			$this->myRedirect($url);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
	}	
	
	/*
	 * insert Joomla in
	* 					#__user_usergroup_map
	* 					#__users
	* 					#__user_profiles
	*/
	public function admin_users_insert() {
	
		$debug = false;
	
		if ($this->request->is('post') || $this->request->is('put')) {
	
			self::d($this->request->data, $debug);
			
			if(empty($this->request->data['CsvImport']['password_default'])) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			
			/*
			 * parameters
			 */
			$parameters = [];
			$parameters += array('password_default' => $this->request->data['CsvImport']['password_default']);
			if($this->user->organization['Organization']['hasArticlesOrder']=='Y')
				$parameters += array('hasArticlesOrder' => 'Y');
			else
				$parameters += array('hasArticlesOrder' => 'N');
			
			$userTable = JTable::getInstance('User', 'JTable', $config = []);
			$params = JComponentHelper::getParams('com_users');
				
			jimport('joomla.user.helper');
			
			foreach ($this->request->data['CsvImport'] as $numResult => $result) {
			
				/*
				 * escludo i campi hidden
				 */
				if(isset($result['name'])) {
					$continue = false;
					$data = [];
					
					if($debug) echo '<br />'.($numResult+1).'  utente '.$result['name'].' '.$result['username'].' '.$result['email'];
					
					$userTable->set('id',0);
					
					/*
					 * field add custom
					*/
					$data['organization_id'] = $this->user->organization['Organization']['id'];
						
					$data['groups'] = array(Configure::read('group_id_user'));
					$data['name'] = $result['name'];
					$data['username'] = $result['username'];
					$data['email'] = $result['email'];
					
					
					// password  TODO
					$salt = JUserHelper::genRandomPassword(32);
					//$pswrd = $result['password'];
					$pswrd = $parameters['password_default'];
					$cryptpswrd = JUserHelper::getCryptedPassword($pswrd, $salt);
					$dbpassword = $cryptpswrd . ':' . $salt;
					$data['password'] = $dbpassword;
						
					$data['block'] = 0;
					$data['registerDate'] = date('Y-m-d H:i:s');
						
					$data['activation'] = null;
					$data['block'] = 0;
					
					// Inserting Data into Users Table
					if (!$userTable->bind($data)) {
						if($debug) echo ' ERROR userTable->bind '.$data['name'].'<br />';
						$continue = false;
					}
					else 
						$continue = true;
					
					// Check the data.
					if ($continue) {
						if(!$userTable->check()) {
							//if(!$this->_check($db, $userTable, $user)) {
							if($debug) echo '<span style="color:yellow;background-color: #000000;">ALERT</span> userTable->check '.$data['name'].' (forse user gia\' esistente)';
							$continue = false;
						}
						else
							$continue = true;
					}
						
					// Store the data.
					if ($continue) {
						if (!$userTable->store()) {
							if($debug) echo '<span style="color:red;">ERROR</span> userTable->store '.$data['name'];
							$continue = false;
						}
						else {
							if($debug) echo ' - <span style="color:green;">INSERITO</span> in #__user_usergroup_map, #__users';
							$continue = true;
						}
					}
					
					if($continue) {
						$user_id = $userTable->get('id');
						if($debug) echo '	- USERID '.$user_id;
					
						/*
						 * aggiungo gruppo joomla __user_usergroup_map GasPages[nome organizazione]
						*/
						$continue = $this->_user_set_j_group_registred($user, $user_id, $debug);
					}
					
					/*
					 * user_profiles
					*/
					if($continue) 
						$this->_user_set_profile($user, $user_id, $result, $parameters, $debug);
					
				} // if(isset($result['name'])) 
			} // end foreach ($this->request->data['CsvImport'] as $result) 
				
			if($debug) exit;
				
			$this->Session->setFlash(__('Csv Import Users has been saved'));
			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Users&action=index';
			$this->myRedirect($url);
							
		} // end if ($this->request->is('post') || $this->request->is('put'))
	}	

	/*
	 * codice uguale in DatabaseDate::executeMigrationEg3Users()
	*/
	private function _user_set_j_group_registred($user, $user_id, $debug=false) {
	
		$result = false;
	
		App::import('Model', 'User');
		$User = new User;
			
		$sql = "SELECT 
					".Configure::read('DB.portalPrefix')."group_registred
				FROM 
					".Configure::read('DB.prefix')."organizations Organization
				WHERE 
					id = ".(int)$this->user->organization['Organization']['id'];
		self::d($sql, false);
		$results = $User->query($sql);
		if(!empty($results) && !empty($results[0]['Organization']['j_group_registred'])) {
			$User->joomlaBatchUser($results[0]['Organization']['j_group_registred'], $user_id, 'add');
			if($debug) echo ' - <span style="color:green;">INSERITO</span> in #__user_usergroup_map al gruppo GasPages[nome organizzazione]';
			$result = true;
		}
		else {
			if($debug) echo ' - <span style="color:red;">NON inserito</span> in #__user_usergroup_map al gruppo GasPages[nome organizzazione]: campo Organizations.j_group_registred non valorizzato';
			$result = false;
		}
	
		return $result;
	}
	
	/*
	 * codice uguale in DatabaseDate::executeMigrationEg3Users()
	*
	* Gestisci gli articoli associati all'ordine hasArticlesOrder: Y/N
	* Codice preso da Eg3.descrizione (054 Rossi Mario)
	* Dati anagrafici
	*/
	private function _user_set_profile($user, $user_id, $result, $parameters, $debug=false) {
		
		$db = JFactory::getDbo();
	
		$data = [];
		$data['id'] = $user_id;
		$data['profile']['address'] = $this->_pulisciDaValueNull($result['address']);
		$data['profile']['city'] =$this->_pulisciDaValueNull($result['city']);
		$data['profile']['postal_code'] = $this->_pulisciDaValueNull($result['postal_code']);
		$data['profile']['region'] = $this->_pulisciDaValueNull($result['region']);
		if(!isset($result['country']) || empty($result['country']))
			$this->dataProfile['country'] = 'Italia';
		else		
		$data['profile']['country'] = $this->_pulisciDaValueNull($result['country']);
		$data['profile']['phone'] = $this->_pulisciDaValueNull($result['phone']);
		$data['profile']['phone2'] = $this->_pulisciDaValueNull($result['phone2']);
		$data['profile']['codice'] = $this->_pulisciDaValueNull($result['codice']);
			
		/*
		 * Gestisci gli articoli associati all'ordine hasArticlesOrder: Y/N
		* */
		$data['profile']['hasArticlesOrder'] = $parameters['hasArticlesOrder'];
			
		$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$data['id'] .
				" AND profile_key LIKE 'profile.%'");
		if (!$db->query())  echo 'error DELETE __user_profiles<br />';
			
		$tuples = [];
		$order	= 1;
		
		foreach ($data['profile'] as $k => $v) 	{
			$tuples[] = '('.$data['id'].', '.$db->quote('profile.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
		}
			
		$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));
		$result = $db->query();
		
		if($debug) {
			if(!$result)
				echo ' - __user_profiles <span style="color:red;">NO INSERT</span>';
			else
				echo ' - __user_profiles <span style="color:green;">OK insert</span>';			
		}
	}
	
	/*
	 * ctrl l'estensione del file CSV uplodato
	 */
	private function _ctrl_file_exstension($file1, $debug=false) {
	
		$esito = true;
	
		$path_upload = Configure::read('App.root').Configure::read('App.img.upload.tmp').DS;
	
		/*
		 * ctrl exstension / content type
		*/
		$ext = strtolower(pathinfo($file1['name'], PATHINFO_EXTENSION));
	
		$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
		$type = finfo_file ($finfo, $file1['tmp_name']);
		finfo_close($finfo);
	
		self::d(["ext ".$ext, Configure::read('App.web.csv.upload.extension')], $debug);
		self::d(["type ".$type, Configure::read('ContentType.csv')], $debug);

		if(!in_array($ext, Configure::read('App.web.csv.upload.extension')) || !in_array($type, Configure::read('ContentType.csv'))) {
			$esito = "Estensione .$ext non valida: si possono caricare file con l'estensione: ";
			foreach ( Configure::read('App.web.csv.upload.extension') as $estensione)
				$esito .= '.'.$estensione.'&nbsp;';
		}
		
		return $esito;
	}
	

	private function _pulisciDaValueNull($value) {
		
		if(!isset($value) || $value==null) $value = "";
		
		return $value;
	}
	
	/*
	 * preso da libraries/joomla/database/table/user.php
	*/
	private function _check($db, $userTable, $user)
	{
		// Validate user information
		if (trim($userTable->name) == '')
		{
			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME'));
			return false;
		}
	
		if (trim($userTable->username) == '')
		{
			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME'));
			return false;
		}
	
		if (preg_match("#[<>\"'%;()&]#i", $userTable->username) || strlen(utf8_decode($userTable->username)) < 2)
		{
			$userTable->setError(JText::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2));
			return false;
		}
	
		if ((trim($userTable->email) == "") || !JMailHelper::isEmailAddress($userTable->email))
		{
			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_VALID_MAIL'));
			return false;
		}
	
		// Set the registration timestamp
		if ($userTable->registerDate == null || $userTable->registerDate == $db->getNullDate())
		{
			$userTable->registerDate = JFactory::getDate()->toSql();
		}
	
		// check for existing username
		$query = $db->getQuery(true);
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('username') . ' = ' . $db->quote($userTable->username));
		$query->where($db->quoteName('id') . ' != ' . (int) $userTable->id);
	
		// fractis
		$query->where($db->quoteName('organization_id') . ' = ' . $user->organization['Organization']['id']);
	
		$db->setQuery($query);
	
		$xid = intval($db->loadResult());
		if ($xid && $xid != intval($userTable->id))
		{
			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'));
			return false;
		}
	
		// check for existing email
		$query->clear();
		$query->select($db->quoteName('id'));
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('email') . ' = ' . $db->quote($userTable->email));
		$query->where($db->quoteName('id') . ' != ' . (int) $userTable->id);
	
		// fractis
		$query->where($db->quoteName('organization_id') . ' = ' . $user->organization['Organization']['id']);
	
		$db->setQuery($query);
		$xid = intval($db->loadResult());
		if ($xid && $xid != intval($userTable->id))
		{
			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));
			return false;
		}
	
		// check for root_user != username
		$config = JFactory::getConfig();
		$rootUser = $config->get('root_user');
		if (!is_numeric($rootUser))
		{
			$query->clear();
			$query->select($db->quoteName('id'));
			$query->from($db->quoteName('#__users'));
			$query->where($db->quoteName('username') . ' = ' . $db->quote($rootUser));
				
			// fractis
			$query->where($db->quoteName('organization_id') . ' = ' . $user->organization['Organization']['id']);
				
			$db->setQuery($query);
			$xid = intval($db->loadResult());
			if ($rootUser == $userTable->username && (!$xid || $xid && $xid != intval($userTable->id))
			|| $xid && $xid == intval($userTable->id) && $rootUser != $userTable->username)
			{
				$userTable->setError(JText::_('JLIB_DATABASE_ERROR_USERNAME_CANNOT_CHANGE'));
				return false;
			}
		}
	
		return true;
	}
	
	public function admin_articles_form_export() {

		if(!$this->isReferentGeneric()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = ['SuppliersOrganization.name'];
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());
		
		$typeDocOptions = ['CSV' => 'Csv'];
		$this->set('typeDocOptions', $typeDocOptions);	

		$version = 'COMPLETE';
		$this->set(compact('version'));
		
		$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, $version);
		$this->set(compact('struttura_file'));		
	}
	
	public function admin_articles_export($supplier_organization_id, $doc_options='export_file_csv', $doc_formato = 'CSV') {


        if ($supplier_organization_id == null || $doc_formato == null) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * dati anagrafici articoli 
         * 	   Article, SupplierOrganization, CategoriesArticle, ArticlesType
         */
        App::import('Model', 'Article');
        $Article = new Article;

        $options = [];
        $options['conditions'] = ['Article.organization_id' => $this->user->organization['Organization']['id'], 'Article.supplier_organization_id' => $supplier_organization_id];

        /*
         * se lo user e' referente del produttore ho anche gli articoli a stato N
         */
        $isReferenteSupplierOrganization = false;
        if ($this->isReferentGeneric()) {
            App::import('Model', 'Order');
            $Order = new Order;

            if (!$this->isSuperReferente() && !$Order->aclReferenteSupplierOrganization($this->user, $supplier_organization_id)) {
                $options['conditions'] += ['Article.stato' => 'Y'];
                $isReferenteSupplierOrganization = false;
            } else
                $isReferenteSupplierOrganization = true;
        }

        $this->set('isReferenteSupplierOrganization', $isReferenteSupplierOrganization);

        $results = $Article->getArticlesDataAnagr($this->user, $options);
		
		self::d([$options, $results], false);
		
        $this->set('results', $results);

        $params = ['supplier_organization_id' => $supplier_organization_id];
        $this->set('fileData', $this->utilsCommons->getFileData($this->user, $doc_options = 'articles_supplier_organization', $params, null));
        $this->set('organization', $this->user->organization);

		$version = 'COMPLETE';
		$this->set(compact('version'));
		
		$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, $version);
		$this->set(compact('struttura_file'));	
		
        switch ($doc_formato) {
            case 'CSV':
                $this->layout = 'csv';
                $this->render('admin_articles_export_csv');
                break;
        }		
	}	
	
	public function admin_articles_form_import() {

		if(!$this->isReferentGeneric()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isSuperReferente()) {
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$options = [];
			$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										   'SuppliersOrganization.stato' => 'Y'];
			$options['recursive'] = -1;
			$options['order'] = array('SuppliersOrganization.name');
			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
		else 
			$this->set('ACLsuppliersOrganization',$this->getACLsuppliersOrganization());

		$version = 'COMPLETE';
		$this->set(compact('version'));
			
		$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, $version);
		$this->set(compact('struttura_file'));		
	}	
	
	public function admin_articles_prepare_import() {

		$debug = false;
	
		if(!$this->isReferentGeneric()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}

		$supplier_organization_id = $this->request->data['CsvImport']['supplier_organization_id'];
		$file1 = $this->request->data['Document']['file1'];
		$deliminatore = ',';
		$version = $this->request->data['CsvImport']['version'];
		
		if(empty($supplier_organization_id) || $file1['size']==0) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {	
		
			$result = $this->_readFileSend($file1, $deliminatore, $version, true, $supplier_organization_id, $debug);
			$esito = $result['esito'];
			$results = $result['results'];
					
			if($esito!==true) {
				$this->Session->setFlash($esito);
				$this->myRedirect(array('action' => 'articles_form_import'));
			}
				
		} // if ($this->request->is('post') || $this->request->is('put'))
		else {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}

		$this->set('totRows',$totRows);
		if($totRows>Configure::read('CsvImportRowsMaxArticles'))
			$this->set('results',[]);
		else
			$this->set('results',$results);
	}

	public function admin_articles_import() {

		$debug = false;
		$msg = "";
	
		if ($this->request->is('post') || $this->request->is('put')) {

			self::d($this->request->data, $debug);

			/*
			 * campi filtro
			*/
			$supplier_organization_id = $this->request->data['CsvImport']['supplier_organization_id'];
			if(empty($supplier_organization_id)) {
				$this->Session->setFlash(__('msg_error_params'));
				$this->myRedirect(Configure::read('routes_msg_exclamation'));
			}
			/*
			 * ctrl che sia referente del produttore
			 */
			App::import('Model', 'SuppliersOrganizationsReferent');
			$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
			if(!$SuppliersOrganizationsReferent->aclReferenteSupplierOrganization($this->user, $supplier_organization_id)) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));				
			}
			unset($this->request->data['CsvImport']['supplier_organization_id']);
			
			App::import('Model', 'Article');
			
			foreach ($this->request->data['CsvImport'] as $result) {
				
				$rows = [];
				
				/*
				 * escludo i campi hidden
				*/
				if(isset($result['name'])) {
				
					$Article = new Article;
					
					/*
					 * ricerco per evitare che modifiche article_id diversi
					 */	
					$options = []; 
					$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
												   'Article.id' => $result['id'],
												   'Article.supplier_organization_id' => $supplier_organization_id);
					$options['recursive'] = -1;
					$articleResults = $Article->find('first', $options);

					if(empty($articleResults)) {
						$msg .= "Articolo con id ".$result['id']." non trovato!<br />";
					}
					else {
						$rows['Article'] = $articleResults['Article'];
						
						$rows['Article']['organization_id'] = $this->user->organization['Organization']['id'];
						$rows['Article']['id'] = $result['id'];
						$rows['Article']['supplier_organization_id'] = $supplier_organization_id;
			
						$rows['Article']['name'] = $result['name'];
						if($this->user->organization['Organization']['hasFieldArticleCodice']=='Y')
							$rows['Article']['codice'] = $result['codice'];
						$rows['Article']['nota'] = $result['nota'];
						if($this->user->organization['Organization']['hasFieldArticleIngredienti']=='Y')
							$rows['Article']['ingredienti'] = $result['ingredienti'];
						$rows['Article']['prezzo'] = $result['prezzo'];
						$rows['Article']['qta'] = $result['qta'];
						$rows['Article']['um'] = $result['um'];
						$rows['Article']['um_riferimento'] = $result['um_riferimento'];
						$rows['Article']['pezzi_confezione'] = $result['pezzi_confezione'];
						$rows['Article']['qta_minima'] = $result['qta_minima'];
						$rows['Article']['qta_massima'] = $result['qta_massima'];
						$rows['Article']['qta_minima_order'] = $result['qta_minima_order'];
						$rows['Article']['qta_massima_order'] = $result['qta_massima_order'];
						$rows['Article']['qta_multipli'] = $result['qta_multipli'];
						if($this->user->organization['Organization']['hasFieldArticleAlertToQta']=='N') 
							$rows['Article']['alert_to_qta'] = 0;

						self::d($rows, $debug);
						
						/*
						 * richiamo la validazione
						*/
						$Article->set($rows);
						if(!$Article->validates()) {
							$errors = $Article->validationErrors;
							$tmp = '';
							$flatErrors = Set::flatten($errors);
							if(count($errors) > 0) { 
								$tmp = '';
								foreach($flatErrors as $key => $value) 
									$tmp .= $value.' - ';
							}
							$msg .= $rows['Article']['name']." non aggiornato: dati non validi, $tmp<br />";
						}
						else {
							$Article->create();
							if($Article->save($rows)) {
								
								if($debug) $msg .= $rows['Article']['name']." aggiornato<br />";
							} 
							else {
								$msg .= $rows['Article']['name']." non aggiornato<br />";
							} // end if(!$Article->validates())
						}
						
					}
					
				} // if(isset($result['name'])) 	
			} // foreach ($this->request->data['CsvImport'] as $csvImport)

			if($debug) echo $msg;
			if($debug) exit;
			
			if(empty($msg)) {
				$msg = __('Csv Import Articles has been saved');
				$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Articles&action=context_articles_index&FilterArticleSupplierId='.$supplier_organization_id;
			}
			else 
				$url = ['action' => 'articles_import'];

			self::d($msg, $debug);
			self::d($url, $debug);			
			
			$this->Session->setFlash($msg);
			$this->myRedirect($url);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
	}	

	/*
	 * 	$file = array(
	* 		'name' => 'immagine.jpg',
	* 		'type' => 'image/jpeg',
	* 		'tmp_name' => /tmp/phpsNYCIB',
	* 		'error' => 0,
	*		'size' => 41737,
	* 	);
	*
	* UPLOAD_ERR_OK (0): Non vi sono errori, l’upload e' stato eseguito con successo;
	* UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
	* UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
	* UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
	* UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
	* UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
	*/	
	private function _readFileSend($file, $deliminatore, $version='COMPLETE', $first_row_header=false, $supplier_organization_id=0, $debug=false) {

		setlocale(LC_ALL, 'it_IT.utf8');

		$struttura_file = $this->CsvImport->getStrutturaFile($this->user, $this->action, $version, $debug);
		self::d($struttura_file);
		
		$results = [];
		if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {

			$esito = $this->_ctrl_file_exstension($file, $debug);
				
			if($esito===true) {
				/*
				 * leggo il file uplodato e creo obj
				*/
				$totRows = 0;	
				if (($handle = fopen($file['tmp_name'], "r")) !== false) {
						
					$i=0;
					while (($data = fgetcsv($handle, 1000, $deliminatore)) !== false) {
						
						/*
						 * ultima riga vuota
						 */
						if(empty($data) || empty($data[0]) || $data[0]==' ') {
							break;
						}
						
						/*
					     * prima riga puo' essere l'intestazione
						 */
						if($first_row_header && $i==0) {
						}
						else {							
							$num = count($data); // totale colonne del file csv
						
							if($num > (count($struttura_file)+1)){
								$esito = "Il file csv contiene troppo colonne alla riga ".($i+1).": sono ".$num." e devono essere ".count($struttura_file);
								break;
							}
							
							/*
							 * tutta la riga e' nella prima colonna => separatore errato
							*/	
							if($num==1) {
								$esito = "Il Deliminatore indicato (".$deliminatore.") non è corretto";
								break;
							}

							$results[$totRows]['ESITO'] = 'OK';
							for ($c=0; $c < $num; $c++) {	
								
								$value = $this->_ctrl_data_validation($c, $struttura_file, $data[$c], $version, $supplier_organization_id, $debug);
																
								$results[$totRows]['Row'][$c]['LABEL'] = $struttura_file[$c]['LABEL'];
								$results[$totRows]['Row'][$c]['INPUT_NAME'] = $struttura_file[$c]['INPUT_NAME'];
								$results[$totRows]['Row'][$c]['INPUT_TYPE'] = $struttura_file[$c]['INPUT_TYPE'];
								$results[$totRows]['Row'][$c]['REQUEST'] = $struttura_file[$c]['REQUEST'];
								$results[$totRows]['Row'][$c]['VALUE'] = $value;
								$results[$totRows]['Row'][$c]['ESITO'] = $this->esito_value;
								
								/*
								 * basta un solo campo della riga per non essere valido
								 */
								if($this->esito_value!='OK') 
									$results[$totRows]['ESITO'] = 'KO';

							}	// for ($c=0; $c < $num; $c++) 							
							$totRows++;
						}

						$i++;							
					} // loop file			
					fclose($handle);
				} // end if (($handle = fopen($file['tmp_name'], "r")) !== FALSE)
				else 
					$esito = "errore nell'apertura del file ".$file['tmp_name'];
			} // end if($esito)
		} // end if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) 
		else
			$esito = $file['error'];
		
		self::d($results, $debug);
		
		$result['esito'] = $esito;
		$result['results'] = $results;
		
		return $result;
	}
	
	/*
	 * verifica consisteneza dei dati
	*/
	private function _ctrl_data_validation($c, $struttura_file, $value, $version, $supplier_organization_id, $debug=false) {
		
		self::d('CsvImportController::_ctrl_data_validation() '.$c.' - '.$struttura_file[$c]['INPUT_NAME'].': '.$value.' '.$struttura_file[$c]['INPUT_TYPE'], $debug);
		
		$value = trim($value);
		
		if($struttura_file[$c]['UPPERCASE']=='Y') $value = strtoupper($value);

		if($struttura_file[$c]['UCWORDS']=='Y') $value = UCWORDS(strtolower($value));
		
		if($struttura_file[$c]['REQUEST']=='Y' && ($value=='' || !isset($value))) {
			$this->esito_value = 'ERROR_EMPTY';
			$this->esito_row = false;
			
			return $value;
		}
		
		switch($struttura_file[$c]['INPUT_TYPE']) {
			case "double":
				/*
				 * calcoli per verificare se e' float/double 1.00
				 */
				$pos = strpos($value, ',');
				if ($pos !== false)
					$value = str_replace(',','.',$value);
				else
					$value = $value.'.00';
				
				$value = floatval($value);  // se string diventa 0
				if($value==0) {
					$this->esito_value = 'ERROR_FORMAT';
					$this->esito_row = false;
				}
				else
					$this->esito_row = true;
					

				/*
				 * floatval() toglie i decimali = a 0, li ricreo (1.00)
				 */
				$pos = strpos($value, ',');
				if ($pos !== false)
					$value = str_replace(',','.',$value);
				else
					$value = $value.'.00';
				
				$pos = strpos($value, '.');
				if ($pos !== false) {
					$ctrl = substr($value, $pos+1, strlen($value));
					if(strlen($ctrl)==1)
						$value = $value.'0';
				}
				else
					$value = $value.'.00';				
			break;
			case "array_um":
				$continue=false;
				foreach ($this->array_um as $um) {
					if(strtoupper($um)==$value) {
						$continue=true;
						break;
					}
				}
				if(!$continue) {
					$this->esito_value = 'ERROR_FORMAT_ARRAY';
					$this->esito_row = false;
				}	
				else
					$this->esito_row = true;
			break;
			case "array_y_n":
				if(empty($value)) $value = 'N';
				$continue=false;
				foreach ($this->array_y_n as $y_n) {
					if(strtoupper($y_n)==$value) {
						$continue=true;
						break;
					}
				}
				if(!$continue) {
					$this->esito_value = 'ERROR_FORMAT_ARRAY';
					$this->esito_row = false;
				}
				else
					$this->esito_row = true;	
			break;
			case "email":
				if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
					$this->esito_value = 'ERROR_FORMAT_EMAIL';
					$this->esito_row = false;
				}
				else
					$this->esito_row = true;
			break;
			case "int":
			case "int_max_zero":
				$value = intval($value);
				$this->esito_row = true;
				
				if($struttura_file[$c]['INPUT_TYPE']=='int_max_zero') {
					if($value<=0) {
						$this->esito_value = 'ERROR_NUM_MAX_ZERO';
						$this->esito_row = false;					
					}
				}

				if($this->esito_row==true) {
	 				if($struttura_file[$c]['INPUT_NAME']=='id') {			
						/*
						 * ctrl che l'articolo esiste per quel GAS e produttore
						 * ricerco per evitare che modifiche article_id diversi
						 */
											
						App::import('Model', 'Article');
						$Article = new Article;
						
						$options = []; 
						$options['conditions'] = array('Article.organization_id' => $this->user->organization['Organization']['id'],
													   'Article.id' => $value,
													   'Article.supplier_organization_id' => $supplier_organization_id);
						$options['recursive'] = -1;
						$articleResults = $Article->find('first', $options);
						
						if(empty($articleResults)) {
							$this->esito_value = "ID dell'Articolo inesistente";
							$this->esito_row = false;
						}
						else
							$this->esito_row = true;
					}
				}				
			break;
			case "text":
				$this->esito_value = 'OK';
				$this->esito_row = true;
			break;
			default:
				$this->esito_value = 'ERROR_FORMAT';
				$this->esito_row = false;				
			break;
		}
		
		if($this->esito_row)
			$this->esito_value = 'OK';
		
		self::d(' => '.$value.' ESITO '.$this->esito_value, $debug);
		
		return $value;
	}	
}