<?php
App::uses('AppModel', 'Model');


/* 
 * tabelle da importare (db.Sql297434_4)
 *    lacavagnetta_anagrafiche
 *    lacavagnetta_articoli
 *    lacavagnetta_referenti
 *    lacavagnetta_catmerceologica
 *    lacavagnetta_tipiarticoli
 *    
 * tabela da importare per allineamento password Sql297434_3.jos_user
 *
 * lacavagnetta_anagrafiche.tipocfa
 * lacavagnetta_tipoanagrafiche (codice, descrizione) VALUES
 * ('C', 'Clienti'),
 * ('F', 'Fornitori'),
 * ('U', 'Utenti'),     non utilizzati
 * ('V', 'Volontari');  non utilizzati
 *  
 */

class DatabaseDate extends AppModel {

	public $useTable = false;
	private $dataProfile = [];
	
	/* campo joomla.users.tmp_migration_codice contiente lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti */
	public function executeMigrationEg3Users($user,$group_id_root,$group_id_user,$parameters) {	
		echo '<h1>Eseguo executeMigrationEg3Users, organization '.$user->organization['Organization']['id'].'</h1>';
	
		$continue = false;
		$continue = $this->_ctrl_organization_j_group_registred($user);
		
		if($continue) {
			$sql = 'SELECT 
						email,password,
						descrizione,
						nome,cognome,
						indirizzo,localita,cap,provincia,
						email,telefono,telefono2,
						data_ins,codice
				   FROM lacavagnetta_anagrafiche
				   WHERE tipocfa = \'C\' and stato=\'1\'
			   ';
			echo 'Query '.$sql.'<br />';
			$results = $this->query($sql);
			if(!empty($results)) {
				$db = JFactory::getDbo();
				
				/*				 * ctrl se il campo tmp_migration_codice esiste				*/				$sql = "SHOW COLUMNS FROM ".Configure::read('DB.portalPrefix')."users LIKE 'tmp_migration_codice'";				$resultsCtrl = $this->query($sql);				if(empty($resultsCtrl)) {
					/* 
					 * creo campo tmp_migration_codice per registrare lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti
					 */
					$sql = "ALTER TABLE #__users ADD tmp_migration_codice VARCHAR( 100 ) DEFAULT NULL COMMENT 'field tmp to migration'";
					$db->setQuery($sql);
					if (!$db->query())  echo 'error ALTER TABLE: #__users ADD tmp_migration_codice <br />';
				}
								
				/*
				 * insert Joomla in 
				 * 					#__user_usergroup_map
				 * 					#__users
				 * 					#__user_profiles
				 */
				$userTable = JTable::getInstance('User', 'JTable', $config = []);
				$params = JComponentHelper::getParams('com_users');
	
				jimport('joomla.user.helper');
				
				foreach ($results as $numResult => $result) {
					$continue = false;
					$data = [];
					
					$userTable->set('id',0);
					
					$result = $result['lacavagnetta_anagrafiche'];
					
					echo '<br />'.($numResult+1).'  utente '.$result['cognome'].' '.$result['nome'].' '.$result['email'];
					
					/*
					 * field add custom
					 */
					$data['organization_id'] = $user->organization['Organization']['id'];
					
					$data['groups'] = array(Configure::read('group_id_user'));
					$data['name'] = $this->_setUserName($user, $result);
					$data['username'] = $result['email'];
					$data['email'] = $result['email'];
			
					// password  TODO
					$salt = JUserHelper::genRandomPassword(32);
					//$pswrd = $result['password'];
					$pswrd = $parameters['password_default'];
					$cryptpswrd = JUserHelper::getCryptedPassword($pswrd, $salt);
					$dbpassword = $cryptpswrd . ':' . $salt;
					$data['password'] = $dbpassword;
					
					$data['block'] = 0;
					$data['registerDate'] = $result['data_ins']; // date('Y-m-d H:i:s')
					
					$data['activation'] = null;
					$data['block'] = 0;
									
					// Inserting Data into Users Table
					if (!$userTable->bind($data)) {
						echo ' ERROR userTable->bind '.$data['name'].'<br />';
						$continue = false;
					}
					else 
						$continue = true;
					
					
					// Check the data.
					if ($continue) {
						if(!$userTable->check()) {
						//if(!$this->_check($db, $userTable, $user)) {
							echo ' <span style="color:yellow;background-color: #000000;">ALERT</span> userTable->check '.$data['name'].' (forse user gia\' esistente)';
							$continue = false;
						}
						else 
							$continue = true;
					}
					
					// Store the data.
					if ($continue) {
						if (!$userTable->store()) {
							echo ' <span style="color:red;">ERROR</span> userTable->store '.$data['name'];
							$continue = false;
						}
						else {
							echo ' - <span style="color:green;">INSERITO</span> in #__user_usergroup_map, #__users';
							$continue = true;
						}
					}

					if($continue) {
						$user_id = $userTable->get('id');
						echo '	- USERID '.$user_id;
			
						/*						 * aggiungo gruppo joomla __user_usergroup_map GasPages[nome organizazione]						*/
						$continue = $this->_user_set_j_group_registred($user, $user_id);					}
					
										
					if($continue) {								
						/*
						 * users.tmp_migration_codice = lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti
						 */
						$sql = "UPDATE #__users SET tmp_migration_codice ='".$result['codice']."' 
								WHERE 
									organization_id = ".(int)$user->organization['Organization']['id']." 
									and id =".(int)$user_id;
						$db->setQuery($sql);
						if (!$db->query())  echo 'error UPDATE users.codice<br />';
	
						/*						 * user_profiles						*/
						$this->_user_set_profile($user, $user_id, $result, $parameters);	
					} 
					
				} // end foreach
			} // end if(!empty($results)) 
		}	// end if $continue
	}

	public function executeMigrationEg3UsersPwd($user, $group_id_root, $group_id_user) {		echo '<h1>Eseguo executeMigrationEg3UsersPwd, organization '.$user->organization['Organization']['id'].'</h1>';
		
		$sql = "SELECT
					Slave.name, Slave.password, User.* 
				FROM ".Configure::read('DB.tableJoomlaWithPassword')." Slave, ".Configure::read('DB.portalPrefix')."users User
				WHERE 
				    User.organization_id = ".(int)$user->organization['Organization']['id']." 
					and User.email = Slave.email
				ORDER BY User.id";
		$results = $this->query($sql);		foreach ($results as $numResult => $result) {
			echo '<h3>'.($numResult+1).') tratto l\'utente '.$result['User']['email'].'</h3>';
			
			$sql = "UPDATE  
						".Configure::read('DB.portalPrefix')."users
					SET password = '".$result['Slave']['password']."'
					WHERE 
						organization_id = ".(int)$user->organization['Organization']['id']."
						and id = ".$result['User']['id'];
			self::d($sql, false);		
			$resultsUpdate = $this->query($sql);
		}		}
	
	/* campo supplier.tmp_migration_codice contiente lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti */
	public function executeMigrationEg3Suppliers() {	
		echo '<h1>Eseguo executeMigrationEg3Suppliers</h1>';
		
		/*		 * ctrl se il campo tmp_migration_codice esiste		*/		$sql = "SHOW COLUMNS FROM ".Configure::read('DB.prefix')."suppliers LIKE 'tmp_migration_codice'";		$resultsCtrl = $this->query($sql);		if(empty($resultsCtrl)) {
			/* 
			 * creo campo tmp_migration_codice per registrare lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti
			 */
			$sql = "ALTER TABLE ".Configure::read('DB.prefix')."suppliers ADD tmp_migration_codice VARCHAR( 100 ) DEFAULT NULL COMMENT 'field tmp to migration'";
			$resultAlter = $this->query($sql);
		}
				
		$sql = "INSERT INTO ".Configure::read('DB.prefix')."suppliers 
					(name,nome,cognome,descrizione,indirizzo,localita,cap,provincia,telefono,telefono2,fax,mail,www,cf,piva,conto,stato,created,tmp_migration_codice) 
				  SELECT `descrizione`,`nome`,`cognome`,desc_agg,indirizzo,localita,cap,provincia,telefono,telefono2,fax,email,www,cf,piva,conto,'Y',data_ins, codice
				  FROM `lacavagnetta_anagrafiche` 
				  where tipocfa = 'F'
			   ";
		echo 'Query '.$sql.'<br />';
		$results = $this->query($sql);
		
		echo '<h2>Migrazione avvenuta con successo</h2>';
	}

	public function executeMigrationEg3SuppliersOrganizations($user) {	
		echo '<h1>Eseguo executeMigrationEg3SuppliersOrganizations</h1>';
		$sql = "select * from ".Configure::read('DB.prefix')."suppliers as Supplier";
		echo '<h2>Eseguo '.$sql.'</h2>';
		$results = $this->query($sql);
		if(!empty($results)) {
			foreach ($results as $numResult => $result) {
				echo ($numResult+1).') supplier: '.$result['Supplier']['name'].'<br/>';
				$sql = "INSERT INTO ".Configure::read('DB.prefix')."suppliers_organizations 
						(organization_id,supplier_id,name,category_supplier_id,frequenza,stato)
						values (".$user->organization['Organization']['id'].",".$result['Supplier']['id'].",
								'".addslashes($result['Supplier']['name'])."',".$result['Supplier']['category_supplier_id'].",'','Y')";
				echo 'Query '.$sql.'<br />';
				$executeInsert = $this->query($sql);					
			}
		}
	}

	/*
	 * key k_suppliers_organizations_referents: organization_id, supplier_organization_id, user_id
	 */
	public function executeMigrationEg3SuppliersOrganizationsReferents($user) {	
		echo '<h1>Eseguo executeMigrationEg3SuppliersOrganizationsReferents</h1>';
		$sql = 'select * from lacavagnetta_referenti';
		echo '<h2>Eseguo '.$sql.'</h2>';
		$results = $this->query($sql);	
		if(!empty($results)) {
			foreach ($results as $numResult => $result) {
				echo '<br />'.($numResult+1).') user: '.$result['lacavagnetta_referenti']['codanag'].' - supplier: '.$result['lacavagnetta_referenti']['codfornitore'];

				/* get joomla users */
				$db = JFactory::getDbo();
				$sql = 'SELECT u.* FROM '.Configure::read('DB.portalPrefix').'users u WHERE u.organization_id = '.$user->organization['Organization']['id'].' AND u.tmp_migration_codice = \''.$result['lacavagnetta_referenti']['codanag'].'\'';
				self::d($sql, false);
				$db->setQuery($sql);
				
				$resultId = $db->loadAssocList();
				if(!empty($resultId)) {
					$user_id = $resultId[0]['id'];
					echo '<br/>&nbsp;&nbsp;&nbsp;get #__users '.$result['lacavagnetta_referenti']['codanag'].' Eseguo '.$sql.' : user_id <b>'.$user_id.'</b>';

					/* getSuppliersOrganizations */
					$sql = "select SuppliersOrganization.id 
							from 
								".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization, 
								".Configure::read('DB.prefix')."suppliers Supplier 
							where SuppliersOrganization.supplier_id = Supplier.id 
							and Supplier.tmp_migration_codice = '".$result['lacavagnetta_referenti']['codfornitore']."'";
					$resultId = $this->query($sql);
					if(isset($resultId[0]['SuppliersOrganization']['id'])) {
						$supplier_organization_id = $resultId[0]['SuppliersOrganization']['id'];
						echo '<br />&nbsp;&nbsp;&nbsp;get SuppliersOrganizations '.$result['lacavagnetta_referenti']['codfornitore'].' Eseguo '.$sql.' : supplier_organization_id <b>'.$supplier_organization_id.'</b><br/>';

						$sql = "INSERT INTO ".Configure::read('DB.prefix')."suppliers_organizations_referents 
									(organization_id,supplier_organization_id,user_id,type) 
								  values
									(".$user->organization['Organization']['id'].",$supplier_organization_id,$user_id,'REFERENTE')
								";
						echo '<br />&nbsp;&nbsp;&nbsp;<span style="color:green;"><b>INSERT</b></span> '.$sql;
						$results = $this->query($sql);
						
						/*
						 * aggiungo gruppo joomla gasReferenti se non ci appartiene gia'
						 */ 
						App::import('Model', 'User');
						$User = new User;

						$User->joomlaBatchUser(Configure::read('group_id_referent'), $user_id, 'add');						
					}
					else
						echo '<br />&nbsp;&nbsp;&nbsp;getSuppliersOrganizations '.$result['lacavagnetta_referenti']['codfornitore'].' Eseguo '.$sql.' :<span style="color:red;">NOT FOUND!</span>';

				}
				else 
					echo '<br />&nbsp;&nbsp;&nbsp;getSuppliersOrganizations '.$result['lacavagnetta_referenti']['codanag'].' Eseguo '.$sql.' :<span style="color:red;">NOT FOUND!</span>';
			}
		}

	}

	/* campo category.description contiente lacavagnetta_tipiarticoli.codice e lacavagnetta_catmerceologica.codice */
	public function executeMigrationEg3Articles($user) {	
		
		App::import('Model', 'Article');
		$Article = new Article();
		
		echo '<h1>Eseguo executeMigrationEg3Articles</h1>';
		$sql = 'select * from lacavagnetta_articoli order by centrale, descrizione';
		echo '<h2>Eseguo '.$sql.'</h2>';
		$results = $this->query($sql);
		if(!empty($results)) {
			foreach ($results as $numResult => $result) {
				echo '<h3>'.($numResult+1).') Articolo: '.$result['lacavagnetta_articoli']['descrizione'].'</h3>';

				/* getSuppliersOrganizations */
				$sql = "SELECT SuppliersOrganization.id 
						FROM 
							".Configure::read('DB.prefix')."suppliers_organizations as SuppliersOrganization, 
							".Configure::read('DB.prefix')."suppliers as Supplier 
						WHERE
							 SuppliersOrganization.organization_id = ".(int)$user->organization['Organization']['id']."
							 and SuppliersOrganization.supplier_id = Supplier.id	 
						  	 and Supplier.tmp_migration_codice = '".$result['lacavagnetta_articoli']['centrale']."'";
				$resultId = $this->query($sql);
				if(!empty($resultId)) {
						$supplier_organization_id = $resultId[0]['SuppliersOrganization']['id'];
						echo '&nbsp;&nbsp;&nbsp;&nbsp;getSuppliersOrganizations '.$result['lacavagnetta_articoli']['centrale'].' Eseguo '.$sql.' : supplier_organization_id <b>'.$supplier_organization_id.'</b><br/>';

						/* getCategory */						$sql = "SELECT 
									id 
								FROM ".Configure::read('DB.prefix')."categories_articles CategoriesArticle
								WHERE 
									organization_id = ".(int)$user->organization['Organization']['id']."
									and tmp_migration_codice = '".$result['lacavagnetta_articoli']['catmerce']."'";
						$resultId = $this->query($sql);						if(!isset($resultId[0])) {							echo '<h3 style="color:red;">Attenzione: l\'articolo ha un codice categoria (catmerce = '.$result['lacavagnetta_articoli']['catmerce'].') che non estiste in '.Configure::read('DB.prefix').'categories_articles: aggiornare le tabelle lacavagnetta_catmerceologica e lacavagnetta_tipiarticoli</h3>';						}						else {						
							$category_article_id = $resultId[0]['CategoriesArticle']['id'];							echo '&nbsp;&nbsp;&nbsp;&nbsp;getCategoryArticle '.$result['lacavagnetta_articoli']['catmerce'].' Eseguo '.$sql.' : id <b>'.$category_article_id.'</b><br/>';								
							/* setStato */
							if($result['lacavagnetta_articoli']['stato']==1) $stato = 'Y';
							else	$stato = 'N';
	
							/* setPezziConfezione, sono invertiti in Eg3 qtaminordine e pzperconf */
							if(empty($result['lacavagnetta_articoli']['qtaminordinepzperconf']))
								$pezzi_confezione = 1;
							else 
								$pezzi_confezione = $result['lacavagnetta_articoli']['qtaminordine'];
							
							/* setQtaMinima, sono invertiti in Eg3 qtaminordine e pzperconf */
							if(empty($result['lacavagnetta_articoli']['pzperconf']))								$qta_minima = 1;							else								$qta_minima = $result['lacavagnetta_articoli']['pzperconf'];
							
							/* setQtaMultipli */
							if(empty($result['lacavagnetta_articoli']['qtaminperfamiglia']))
								$qta_multipli = 1;
							else
								$qta_multipli = $result['lacavagnetta_articoli']['qtaminperfamiglia'];
								
							/* setBio */							if(empty($result['lacavagnetta_articoli']['bio']) || $result['lacavagnetta_articoli']['bio']==0)								$bio = 'N';							else								$bio = 'Y';														$article_id = $Article->getMaxIdOrganizationId($user->organization['Organization']['id']);
							$sql = "INSERT INTO ".Configure::read('DB.prefix')."articles 
									(id,organization_id,
									supplier_organization_id,category_article_id,
									name,nota,ingredienti,
									prezzo,qta,
									um,um_riferimento,
									pezzi_confezione,
									qta_minima,qta_multipli,bio,stato,created)
									VALUES (";
							$sql .=		$article_id.','.
										$user->organization['Organization']['id'].','.
										$supplier_organization_id.','.$category_article_id.',		
										\''.addslashes($result['lacavagnetta_articoli']['descrizione']).'\',
										\''.addslashes($result['lacavagnetta_articoli']['desc_agg']).'\',
										\''.addslashes($result['lacavagnetta_articoli']['ingredienti']).'\',
										\''.$result['lacavagnetta_articoli']['prezzoven'].'\',
										\''.$result['lacavagnetta_articoli']['um_qta'].'\',
										\''.$result['lacavagnetta_articoli']['um'].'\',
										\''.$result['lacavagnetta_articoli']['um'].'\',
										\''.$pezzi_confezione.'\',
										\''.$qta_minima.'\',
										\''.$qta_multipli.'\',
										\''.$bio.'\',
										\''.$stato.'\',
										\''.$result['lacavagnetta_articoli']['data_ins'].'\'							
									)';
							echo $sql.'<br />';
							$executeInsert = $this->query($sql);
							
							/*
							 * k_articles_articles_types
							 */
							if($bio=='Y') {
								$sql = "INSERT INTO ".Configure::read('DB.prefix')."articles_articles_types
									(organization_id,article_id,article_type_id)
									VALUES (";
								$sql .=		
										$user->organization['Organization']['id'].','.
										$article_id.',
										1)';  // BIO
								echo $sql.'<br />';
								$executeInsert = $this->query($sql);
							}
						}
				}
				else 
					echo '&nbsp;&nbsp;&nbsp;&nbsp;getSuppliersOrganizations '.$result['lacavagnetta_articoli']['centrale'].' Eseguo '.$sql.' :<b style="color:red;">non trovato!</b><br/>';
			}
		}

		$sql = "update ".Configure::read('DB.prefix')."articles set um='GR', um_riferimento='GR' where um = 'G' and organization_id = ".$user->organization['Organization']['id'];
		$executeUpdate = $this->query($sql);
	}

	/* campo category.description contiente lacavagnetta_tipiarticoli.codice e lacavagnetta_catmerceologica.codice */
	public function executeMigrationEg3CategoriesArticles($user) {	
		echo '<h1>Eseguo executeMigrationEg3CategoriesArticles</h1>';

		/*
		 * estraggo la categoria PADRE 
		 */
		$sql = "SELECT 
					codice, descrizione 
				FROM lacavagnetta_tipiarticoli 
				ORDER BY descrizione";
		echo '<h3>Estraggo le <span style="color:red;">categorie padre</span> '.$sql.'</h3>';
		$results = $this->query($sql);
		if(!empty($results)) {
			
			/*
			 * ctrl se il campo tmp_migration_codice esiste
			 */
			$sql = "SHOW COLUMNS FROM ".Configure::read('DB.prefix')."categories_articles LIKE 'tmp_migration_codice'";			$resultsCtrl = $this->query($sql);
			if(empty($resultsCtrl)) {
				/*
				 * creo campo tmp_migration_codice per registrare lacavagnetta_catmerceologica.codice o lacavagnetta_catmerceologica.codice
				 * 		servira' per la migrazione degli articoli
				 */
				$sql = "ALTER TABLE ".Configure::read('DB.prefix')."categories_articles ADD tmp_migration_codice VARCHAR( 100 ) DEFAULT NULL COMMENT 'field tmp to migration'";
				$resultAlter = $this->query($sql);
			}
			
			foreach ($results as $numResult => $result) {
					$data = [];

					echo '  '.($numResult+1).') codice '.$result['lacavagnetta_tipiarticoli']['codice'].' - descrizione '.$result['lacavagnetta_tipiarticoli']['descrizione'].'<br/>';

					$data['CategoriesArticle']['organization_id'] = $user->organization['Organization']['id'];
					$data['CategoriesArticle']['name'] = $result['lacavagnetta_tipiarticoli']['descrizione'];
					$data['CategoriesArticle']['tmp_migration_codice'] = $result['lacavagnetta_tipiarticoli']['codice'];
					$parent_id = $this->_categoryArticlesAdd($data);

					/*					 * estraggo le categorie FIGLI					*/
					$sql = "SELECT 
								codice, descrizione, tipo 
							FROM 
								lacavagnetta_catmerceologica 
							WHERE 
								tipo = '".$result['lacavagnetta_tipiarticoli']['codice']."' 
							ORDER BY descrizione";
					echo '<h3>Estraggo le <span style="color:red;">categorie figlie</span> query '.$sql.'</h3>';
					echo '<h4>   associo le categorie con la categoria <i>'.$data['CategoriesArticle']['name'].'</i> (parent_id '.$parent_id.')</h4>';
					$subResults = $this->query($sql);
					if(!empty($subResults)) {
						foreach ($subResults as $numResult2 => $subResult) {
								echo '   '. ($numResult2+1).') codice '.$subResult['lacavagnetta_catmerceologica']['codice'].' - descrizione '.$subResult['lacavagnetta_catmerceologica']['descrizione'].'<br/>';
								$data['CategoriesArticle']['organization_id'] = $user->organization['Organization']['id'];
								$data['CategoriesArticle']['parent_id'] = $parent_id;
								$data['CategoriesArticle']['name'] = $subResult['lacavagnetta_catmerceologica']['descrizione'];
								$data['CategoriesArticle']['tmp_migration_codice'] = $subResult['lacavagnetta_catmerceologica']['codice'];
								$this->_categoryArticlesAdd($data);
						}
					}
			}
		}
	}

	public function executeMigrationEg3DropField() {
		
		/*		 * ctrl se il campo tmp_migration_codice esiste		*/		$sql = "SHOW COLUMNS FROM ".Configure::read('DB.portalPrefix')."users LIKE 'tmp_migration_codice'";		$resultsCtrl = $this->query($sql);		if(!empty($resultsCtrl)) {
			$sql = "ALTER TABLE ".Configure::read('DB.portalPrefix')."users DROP tmp_migration_codice";
			echo '<h3>'.$sql.'</h3>';
			$resultAlter = $this->query($sql);
		}

		/*		 * ctrl se il campo tmp_migration_codice esiste		*/		$sql = "SHOW COLUMNS FROM ".Configure::read('DB.prefix')."suppliers LIKE 'tmp_migration_codice'";		$resultsCtrl = $this->query($sql);		if(!empty($resultsCtrl)) {
			$sql = "ALTER TABLE ".Configure::read('DB.prefix')."suppliers DROP tmp_migration_codice";
			echo '<h3>'.$sql.'</h3>';
			$resultAlter = $this->query($sql);
		}

		/*		 * ctrl se il campo tmp_migration_codice esiste		*/		$sql = "SHOW COLUMNS FROM ".Configure::read('DB.prefix')."categories_articles LIKE 'tmp_migration_codice'";		$resultsCtrl = $this->query($sql);		if(!empty($resultsCtrl)) {
			$sql = "ALTER TABLE ".Configure::read('DB.prefix')."categories_articles DROP tmp_migration_codice";
			echo '<h3>'.$sql.'</h3>';
			$resultAlter = $this->query($sql);
		}
	}
	
	private function getUserProfileValue($resultsProfile,$keyToFound) {
		foreach($resultsProfile as $key => $value) {
			if($value['profile_key']==$keyToFound) {
				if($value['profile_value']==null || $value['profile_value']=='null')  $result = '';
				else
					$result = str_replace('"', "",$value['profile_value']);
				return $result;
			}
		} 
	}
	
	private function _setUserName($user,$result) {
		$name = "";
				switch ($user->organization['Organization']['id']) {
			case 1:
				$name = $result['descrizione']; //  057 Actis Grosso Francesco
				break;
			case 2:
				$name = $name = $result['cognome'].' '.$result['nome'];
				break;
			default:
				$name = $result['email'];
		}

		return $name;
	}
	
	private function _categoryArticlesAdd($data) {
		App::import('Model', 'CategoriesArticle');
		$CategoriesArticle = new CategoriesArticle;
		$result = $CategoriesArticle->save($data);

  	   $insertId =	$result['CategoriesArticle']['id'];
		return $insertId;
	}

	private function _pulisciDaValueNull($value) {
		if($value==null) $value = "";
		return $value;
	}
	
	/*
	 * preso da libraries/joomla/database/table/user.php
	 */
	private function _check($db, $userTable, $user)	{		// Validate user information		if (trim($userTable->name) == '')		{			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_YOUR_NAME'));			return false;		}			if (trim($userTable->username) == '')		{			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_PLEASE_ENTER_A_USER_NAME'));			return false;		}			if (preg_match("#[<>\"'%;()&]#i", $userTable->username) || strlen(utf8_decode($userTable->username)) < 2)		{			$userTable->setError(JText::sprintf('JLIB_DATABASE_ERROR_VALID_AZ09', 2));			return false;		}			if ((trim($userTable->email) == "") || !JMailHelper::isEmailAddress($userTable->email))		{			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_VALID_MAIL'));			return false;		}			// Set the registration timestamp		if ($userTable->registerDate == null || $userTable->registerDate == $db->getNullDate())		{			$userTable->registerDate = JFactory::getDate()->toSql();		}			// check for existing username		$query = $db->getQuery(true);		$query->select($db->quoteName('id'));		$query->from($db->quoteName('#__users'));		$query->where($db->quoteName('username') . ' = ' . $db->quote($userTable->username));		$query->where($db->quoteName('id') . ' != ' . (int) $userTable->id);
		
		// fractis
		$query->where($db->quoteName('organization_id') . ' = ' . $user->organization['Organization']['id']);
				$db->setQuery($query);			$xid = intval($db->loadResult());		if ($xid && $xid != intval($userTable->id))		{			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_USERNAME_INUSE'));			return false;		}			// check for existing email		$query->clear();		$query->select($db->quoteName('id'));		$query->from($db->quoteName('#__users'));		$query->where($db->quoteName('email') . ' = ' . $db->quote($userTable->email));		$query->where($db->quoteName('id') . ' != ' . (int) $userTable->id);
		
		// fractis		$query->where($db->quoteName('organization_id') . ' = ' . $user->organization['Organization']['id']);				$db->setQuery($query);		$xid = intval($db->loadResult());		if ($xid && $xid != intval($userTable->id))		{			$userTable->setError(JText::_('JLIB_DATABASE_ERROR_EMAIL_INUSE'));			return false;		}			// check for root_user != username		$config = JFactory::getConfig();		$rootUser = $config->get('root_user');		if (!is_numeric($rootUser))		{			$query->clear();			$query->select($db->quoteName('id'));			$query->from($db->quoteName('#__users'));			$query->where($db->quoteName('username') . ' = ' . $db->quote($rootUser));
			
			// fractis			$query->where($db->quoteName('organization_id') . ' = ' . $user->organization['Organization']['id']);						$db->setQuery($query);			$xid = intval($db->loadResult());			if ($rootUser == $userTable->username && (!$xid || $xid && $xid != intval($userTable->id))					|| $xid && $xid == intval($userTable->id) && $rootUser != $userTable->username)			{				$userTable->setError(JText::_('JLIB_DATABASE_ERROR_USERNAME_CANNOT_CHANGE'));				return false;			}		}			return true;	}	
	
	/*
	 * aggiungo l'utente nel gruppo Registration->GasPage.. per il front-end (profilazione menu, ex "acquista", "stampe")
	 */
	private function _ctrl_organization_j_group_registred($user) {
		
		$result = false;
		
		$sql = "SELECT 
					".Configure::read('DB.portalPrefix')."group_registred  
				FROM 
					".Configure::read('DB.prefix')."organizations Organization 
				WHERE 
					id = ".(int)$user->organization['Organization']['id'];
		self::d($sql, false);
		$results = $this->query($sql);
		if(empty($results) || empty($results[0]['Organization']['j_group_registred'])) {
			$result = false;
			echo ' - <span style="color:red;">STOP</span> campo Organizations.j_group_registred non valorizzato';
		}	
		else 
			$result = true;
	
		return $result;
	}	
	
	/*
	 * codice uguale in CsvImport::admin_users_insert()
	 */
	private function _user_set_j_group_registred($user, $user_id, $debug=false) {
		
		$result = false;
		
		App::import('Model', 'User');
		$User = new User;
			
		$sql = "SELECT ".Configure::read('DB.portalPrefix')."group_registred  
				FROM ".Configure::read('DB.prefix')."organizations Organization 
				WHERE id = ".(int)$user->organization['Organization']['id'];
		self::d($sql, false);
		$results = $this->query($sql);
		if(!empty($results) && !empty($results[0]['Organization']['j_group_registred'])) {
			$User->joomlaBatchUser($results[0]['Organization']['j_group_registred'], $user_id, 'add');
			echo ' - <span style="color:green;">INSERITO</span> in #__user_usergroup_map al gruppo GasPages[nome organizzazione]';
			$result = true;
		}
		else {
			echo ' - <span style="color:red;">NON inserito</span> in #__user_usergroup_map al gruppo GasPages[nome organizzazione]: campo Organizations.j_group_registred non valorizzato';
			$result = false;
		}
		
		return $result;
	}

	/*
	 * codice uguale in CsvImport::::admin_users_insert()
	 * Codice preso da Eg3.descrizione (054 Rossi Mario)
	 * Dati anagrafici
	 */
	private function _user_set_profile($user, $user_id, $result, $parameters, $debug=false) {

			$db = JFactory::getDbo();
		
			if(empty($result['indirizzo']))
				$this->dataProfile['address'] = '';
			else
				$this->dataProfile['address'] = $this->_pulisciDaValueNull($result['indirizzo']);
			$this->dataProfile['city'] = $this->_pulisciDaValueNull($result['localita']);
			$this->dataProfile['postal_code'] = $this->_pulisciDaValueNull($result['cap']);
			$this->dataProfile['region'] = $this->_pulisciDaValueNull($result['provincia']);
			$this->dataProfile['country'] = 'Italia';
			$this->dataProfile['phone'] = $this->_pulisciDaValueNull($result['telefono']);
			if(empty($result['telefono']))
				$this->dataProfile['phone'] = '';
			else
				$this->dataProfile['phone'] = $this->_pulisciDaValueNull($result['telefono']);
			if(empty($result['telefono2']))
				$this->dataProfile['phone2'] = '';
			else
				$this->dataProfile['phone2'] = $this->_pulisciDaValueNull($result['telefono2']);
			$this->dataProfile['aboutme'] = '';
			
			$data = [];
			$data['id'] = $user_id;
			$data['profile']['address'] = $this->dataProfile['address'];
			$data['profile']['city'] = $this->dataProfile['city'];
			$data['profile']['postal_code'] = $this->dataProfile['postal_code'];
			$data['profile']['region'] = $this->dataProfile['region'];
			$data['profile']['country'] = $this->dataProfile['country'];
			$data['profile']['phone'] = $this->dataProfile['phone'];
			$data['profile']['phone2'] = $this->dataProfile['phone2'];
			$data['profile']['aboutme'] = $this->dataProfile['aboutme'];
			
			/*
			 * livello di complessita' dell'applicativo, di default SIMPLE
			 * */ 
			$data['profile']['hasArticlesOrder'] = $parameters['hasArticlesOrder'];
			
			/*			 * codice, 054 Rossi Mario			* */
			$codice = $result['descrizione'];
			if(!empty($codice))				$codice = substr($codice, 0, strpos($codice, " "));							$data['profile']['codice'] = $codice;				
			$db->setQuery('DELETE FROM #__user_profiles WHERE user_id = '.$data['id'] .
						  " AND profile_key LIKE 'profile.%'");
			if (!$db->query())  echo 'error DELETE __user_profiles<br />';
			
			$tuples = [];
			$order	= 1;
			
			foreach ($data['profile'] as $k => $v) 	{
				$tuples[] = '('.$data['id'].', '.$db->quote('profile.'.$k).', '.$db->quote(json_encode($v)).', '.$order++.')';
			}
			
			$db->setQuery('INSERT INTO #__user_profiles VALUES '.implode(', ', $tuples));
			if (!$db->query()) 	
				echo ' - __user_profiles <span style="color:red;">NO INSERT</span>';
			else 
				echo ' - __user_profiles <span style="color:green;">OK insert</span>';
	} 
}