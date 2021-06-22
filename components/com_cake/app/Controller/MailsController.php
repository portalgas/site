<?php
App::uses('AppController', 'Controller');
App::uses('CakeEmail', 'Network/Email');

class MailsController extends AppController {

	public $components = ['ActionsDesOrder'];

	public function beforeFilter() {
		parent::beforeFilter();
	}

	public function admin_index() {
		$conditions = ['Mail.organization_id' => $this->user->organization['Organization']['id']];
		if(!$this->isRoot() && !$this->isManager())
			$conditions += ['Mail.user_id' => $this->user->id];
		
		$SqlLimit = 100;
		$this->paginate = ['conditions' => [$conditions], 'order' => 'Mail.created desc, User.name', 'maxLimit' => $SqlLimit, 'limit' => $SqlLimit];
		$results = $this->paginate('Mail');
		$this->set(compact('results'));	
		
		$this->set('isRoot',$this->isRoot());
		$this->set('isManager',$this->isManager());
	}

	/*
	 * pass_org_id indica l'organization_id (in suppliersOrganization::add_index non e' quello dello user)
	 * pass_entity indica il Model per ricercare l'id
	 * 		da suppliersOrganization::add_index = suppliersOrganization, da li ricavo i referenti associati
	 */
	public function admin_popup_send() {

		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			App::import('Model', 'Mail');
			$Mail = new Mail;
			
			$Email = $Mail->getMailSystem($this->user);
		
			self::d($this->request->data,$debug);
		
			$pass_entity = $this->request->data['pass_entity'];
			$pass_org_id = $this->request->data['pass_org_id'];
			$pass_id = $this->request->data['pass_id'];
			
			$mittente = $this->user->get('email');
			$body_mail = $this->request->data['body_mail'];
			
			$msg_ok = '';
			$msg_no = '';
			$tot_ok=0;
			$tot_no=0;				
			switch ($pass_entity) {
				case "suppliersOrganization":
				
					$subject_mail = "Contatto per informazioni su un produttore di cui sei referente";

					App::import('Model', 'SuppliersOrganization');
					$SuppliersOrganization = new SuppliersOrganization;
					/*
					 * dati produttore
					 */
					$options = [];
					$options['conditions'] = ['SuppliersOrganization.organization_id' => $pass_org_id,
											   'SuppliersOrganization.id' => $pass_id];
					$options['fields'] = ['SuppliersOrganization.name'];
					$options['recursive'] = -1;
					$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);

					$body_mail = "E' stato richiesto un contatto per maggior informazioni circa il produttore ".$suppliersOrganizationResults['SuppliersOrganization']['name']." di cui sei referente.<br />Di seguito il testo della mail:<br />".$body_mail;
					
					/*
					 * ottengo referenti del produttore
					*/
					App::import('Model', 'SuppliersOrganizationsReferent');
					$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
						
					$conditions = ['User.block' => 0,
									'SuppliersOrganization.id' => $pass_id];
					/*
					 * non gli passo organization_id dell'utente ma dell'organization
					*/
					$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $pass_org_id]);
					$results = $SuppliersOrganizationsReferent->getReferentsCompact($tmp_user, $conditions);	
					
					self::d($results, $debug);
					
					if(empty($results)) {
						/*
						 * save Mail
						*/
						$data = [];
						$data['Mail']['organization_id'] = $this->user->organization['Organization']['id'];
						$data['Mail']['user_id'] = $this->user->id;
						$data['Mail']['mittente'] = $mittente;
						$data['Mail']['dest_options'] = 'REFERENTI';
						$data['Mail']['dest_options_qta'] = 'SOME';
						$data['Mail']['dest_ids'] = '';
						$data['Mail']['subject'] = $subject_mail;
						$data['Mail']['body'] = $body_mail;
						$data['Mail']['allegato'] = '';
							
						$this->Mail->create();
						
						self::d($data,$debug);
						
						$this->Mail->save($data);
					} // end if(empty($results)) 
								
					foreach ($results as $numResult  => $result) {
					
						$mail = $result['User']['email'];
						$name = $result['User']['name'];

						if($debug) {
							echo "<br />mail to ".$mail." - name ".$name;
						}
								
						$Email->replyTo(array($mittente => $mittente));
						$Email->subject($subject_mail);
					
						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
					
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
						else
							$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

						$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
						if(isset($mailResults['OK'])) {
							$tot_ok++;
							$msg_ok .= $mailResults['OK'].'<br />';							
						}
						else 
						if(isset($mailResults['KO'])) {
							$tot_no++;
							$msg_ok .= $mailResults['KO'].'<br />';	
						}
							
					} // end foreach ($results as $numResult  => $result) 			
				break;
				case "DeliveryNew":
					/*
					 * invio mail a Configure::read('group_id_manager_delivery')
					*/
					App::import('Model', 'User');
					$User = new User;
						
					$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_manager_delivery'));
					$results = $User->getUsers($this->user, $conditions);
					
					$subject_mail = "Richiesta di apertura di una nuova consegna"; 
					
					foreach ($results as $numResult  => $result) {
					
						$mail = $result['User']['email'];
						$name = $result['User']['name'];

						if($debug) {
							echo "<br />mail to ".$mail." - name ".$name;
						}
								
						$Email->replyTo(array($mittente => $mittente));
						
						$Email->subject($subject_mail);
					
						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
					
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
						else
							$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

						$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
						if(isset($mailResults['OK'])) {
							$tot_ok++;
							$msg_ok .= $mailResults['OK'].'<br />';							
						}
						else 
						if(isset($mailResults['KO'])) {
							$tot_no++;
							$msg_ok .= $mailResults['KO'].'<br />';	
						}						

					} // end foreach ($results as $numResult  => $result) 			
				break;
				case "SupplierChange":
					/*
					 * invio mail a Configure::read('group_id_root_supplier')
					*/
					App::import('Model', 'Supplier');
					$Supplier = new Supplier;
					/*
					 * dati produttore
					*/
					$options = [];
					$options['conditions'] = array('Supplier.id' => $pass_id);
					$options['recursive'] = -1;
					$supplierResults = $Supplier->find('first', $options);
							
					App::import('Model', 'User');
					$User = new User;
				
					$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_root_supplier'));
					$results = $User->getUsersNoOrganization($conditions);
						
					$subject_mail = "Richiesta di modifica dei dati del produttore ".$supplierResults['Supplier']['name'];
						
					$tmp = "Richiesta di modifica dei dati del produttore ".$supplierResults['Supplier']['name']." (".$supplierResults['Supplier']['id'].") <br />";
					$tmp .= "Segnalazione da parte dell'utente ".$this->user->name." - <a href=mailto:".$this->user->email.">".$this->user->email."</a> del G.A.S. ".$this->user->organization['Organization']['name']." (".$this->user->organization['Organization']['id'].")<br /><br />";
					$body_mail = $tmp.$body_mail;
					
					foreach ($results as $numResult  => $result) {
							
						$mail = $result['User']['email'];
						$name = $result['User']['name'];
				
						if($debug) {
							echo "<br />mail to ".$mail." - name ".$name;
						}
				
						$Email->replyTo(array($mittente => $mittente));
							
						$Email->subject($subject_mail);
						$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
			
						if(!empty($this->user->organization['Organization']['www']))
							$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
						else
							$Email->viewVars(['body_footer_simple' => sprintf(Configure::read('Mail.body_footer'))]);

						$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
						if(isset($mailResults['OK'])) {
							$tot_ok++;
							$msg_ok .= $mailResults['OK'].'<br />';							
						}
						else 
						if(isset($mailResults['KO'])) {
							$tot_no++;
							$msg_ok .= $mailResults['KO'].'<br />';	
						}						
						
					} // end foreach ($results as $numResult  => $result)
					break;				
			}
		}
		
		$this->layout = 'ajax';
		$this->render('/Layouts/ajax');
	}
	
	/*
	 * se id valorizzato arrivo da admin_index e voglio utilizzare subject / title di una mail precedente
	 */
	public function admin_send($mail_id=0) {
		
		$debug = false;
		
		/*
		 * ACL Organization / prod_gas_supplier
		 */
		if(!isset($this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}
		
		$body_header_mittente = '';	
		$body_header_mittente .= 'Il gasista '.$this->user->name.' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$this->user->email.'">'.$this->user->email.'</a> scrive:';
					
		if ($this->request->is('post') || $this->request->is('put')) {
			
			App::import('Model', 'Mail');
			$Mail = new Mail;
			
			$Email = $Mail->getMailSystem($this->user);
		
			self::d($this->request->data,$debug);
			
			App::import('Model', 'User');
			$User = new User;
			
			$ids = [];
			switch($this->request->data['Mail']['dest_options']) {
				case 'USERGROUPS';
					if($this->request->data['Mail']['dest_options_qta']=='ALL') {
						$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
						$destinatari = $User->getUsersList($this->user, $conditions);
					}
					else { 
						$usergroups = $this->request->data['Mail']['usergroups'];
						$usergroups_ids = "";
						foreach($usergroups as $usergroup) 
							$usergroups_ids .= $usergroup.',';
						if(!empty($usergroups_ids))
							$usergroups_ids = substr($usergroups_ids, 0, (strlen($usergroups_ids)-1));
							
						$conditions = ['UserGroupMap.group_id IN' => "(".$usergroups_ids.")"];
						$destinatari = $User->getUsersList($this->user, $conditions);
					}	

					foreach ($destinatari as $key => $value) 
						array_push($ids, $key);	
				break;
				case 'USERS';
					if($this->request->data['Mail']['dest_options_qta']=='ALL') {
						$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_user')];
						$destinatari = $User->getUsersList($this->user, $conditions);

						$ids = [];
						foreach ($destinatari as $key => $value) 
							array_push($ids, $key);						
					}
					else 
						$ids = explode(',', $this->request->data['Mail']['user_ids']);	
				break;
				case 'USERS_CART';
					/*
					 * utenti che hanno effettuato ordini ad una consegna
					 */				
					$order_id = $this->request->data['Mail']['orders'];
					$conditions = [];
					
					if($this->request->data['Mail']['article_order_key_selected_all']!='ALL') {
						$article_order_key_selecteds = $this->request->data['Mail']['article_order_key_selecteds'];
						$article_order_key_selecteds = explode('|', $article_order_key_selecteds);
						$article_ids = '';
						
						foreach($article_order_key_selecteds as $article_order_key_selected) {
							
							list($article_organization_id, $article_id) = explode('-', $article_order_key_selected);
							
							if(!empty($article_id))
								$article_ids .= $article_id.',';
						} 
						
						if(!empty($article_ids))
							$article_ids = substr($article_ids, 0, (strlen($article_ids)-1));
						$conditions += ['ArticlesOrder.article_ids' => $article_ids];
						
					} // end if($this->request->data['Mail']['article_order_key_selected_all']!='ALL')
					
					$conditions += ['ArticlesOrder.order_id' => $order_id];
					$destinatari  = $User->getUserWithCartByOrder($this->user ,$conditions);	

					foreach ($destinatari as $destinatario) 
						array_push($ids, $destinatario['User']['id']);	
					
				break;
				case 'REFERENTI';
					if($this->request->data['Mail']['dest_options_qta']=='ALL') {
						/*
						 * referenti
						*/
						$conditions = ['UserGroupMap.group_id IN' => '('.Configure::read('group_id_referent').')'];
						$destinatari = $User->getUsersList($this->user, $conditions);
						
						foreach ($destinatari as $key => $value) 
							array_push($ids, $key);						
					}
					else 
						$ids = explode(',', $this->request->data['Mail']['referente_ids']);

				break;
				case 'SUPPLIERS';
					if($this->request->data['Mail']['dest_options_qta']=='ALL') {

						if($this->isReferentGeneric()) {
							App::import('Model', 'SuppliersOrganization');
							$SuppliersOrganization = new SuppliersOrganization;
							
							$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));
							$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
							
							$options = [];
							if($this->isSuperReferente()) {
								$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										'SuppliersOrganization.stato' => 'Y',
										"(Supplier.mail is not null and Supplier.mail != '')",
										"(Supplier.stato = 'Y' OR Supplier.stato = 'T' OR Supplier.stato = 'PG')"];
							}
							else {
								$options['conditions'] = ['SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
										'SuppliersOrganization.stato' => 'Y',
										"(Supplier.mail is not null and Supplier.mail != '')",
										"(Supplier.stato = 'Y' OR Supplier.stato = 'T' OR Supplier.stato = 'PG')",
										'SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];
							}
							
							$options['fields'] = array('SuppliersOrganization.id');
							$options['recursive'] = 1;
							$options['order'] = array('SuppliersOrganization.id');
							$results = $SuppliersOrganization->find('all', $options);
							
							self::d($options, $debug);
							
							if(!empty($results))
								foreach($results as $result) 
									array_push($ids, $result['SuppliersOrganization']['id']);
									
						} // end if($this->isReferentGeneric()) 
					}
					else 
						$ids = $this->request->data['Mail']['supplier_organization'];				
				break;
				default:
					die("Mail.dest_options not valid!");
				break;
			}
			
			self::d($ids, $debug);
	
			if(!empty($ids)) {

				if($this->request->data['Mail']['dest_options']=='SUPPLIERS') {
	
					$sql = "SELECT
								Supplier.mail, SuppliersOrganization.name
				    		FROM
								".Configure::read('DB.prefix')."suppliers AS Supplier,
								".Configure::read('DB.prefix')."suppliers_organizations AS SuppliersOrganization
							WHERE
								SuppliersOrganization.organization_id = ".$this->user->organization['Organization']['id']."
								AND Supplier.id = SuppliersOrganization.supplier_id
								AND (Supplier.mail is not null and Supplier.mail != '') 
								AND Supplier.stato IN ('Y', 'T', 'PG') 
								AND SuppliersOrganization.id IN (".implode(',', $ids).")";
					self::d($sql, false);
					$results = $User->query($sql);
				}
				else {
					$results = $User->getUsersToMailByIds($this->user, $ids, false);
				}
				
				self::d($results, false);
			
				/*
				 * save Mail
				 */	
				$data = [];
				$data['Mail']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['Mail']['user_id'] = $this->user->id;
				$data['Mail']['mittente'] = $this->request->data['Mail']['mittenti'];
				$data['Mail']['dest_options'] = $this->request->data['Mail']['dest_options'];
				$data['Mail']['dest_options_qta'] = $this->request->data['Mail']['dest_options_qta'];
				$data['Mail']['dest_ids'] = '';
				$data['Mail']['subject'] = $this->request->data['Mail']['subject'];
				$data['Mail']['body'] = $this->request->data['Mail']['body'];
				$data['Mail']['allegato'] = $this->request->data['Document']['img1']['name'];

				$this->Mail->create();
				$this->Mail->save($data);
					
				/*
				 * mittenti
				*/
				if($this->request->data['Mail']['mittenti']==Configure::read('Mail.no_reply_mail'))
					$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
				else
					$Email->replyTo([$this->user->email => $this->user->email]);
	
				$subject_mail = $this->request->data['Mail']['subject'];
				$Email->subject($subject_mail);
				
				
				/*
				 * 	$img1 = array(
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
				$msgAttachment = "";
				if(!empty($this->request->data['Document']['img1']['name'])) {
				
					$uploadSuccess=false;
					
					$img1 = $this->request->data['Document']['img1'];
					
					if($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name']))	{
							
						$path_upload = Configure::read('App.root').Configure::read('App.img.upload.tmp').DS;
						$ext = strtolower(pathinfo($img1['name'],PATHINFO_EXTENSION));
						
						if(move_uploaded_file($img1['tmp_name'], $path_upload.$img1['name'])) {
							$Email->attachments($path_upload.$img1['name']);
							$uploadSuccess=true;
						}
						else
							$uploadSuccess=false;
					}
					else 
						$uploadSuccess=false;		

					if($uploadSuccess)
						$msgAttachment .= "<br />Caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
					else {
						$msgAttachment .= "<br />Non caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
						CakeLog::write("error", $img1['error'], array("mails"));
					}
				} // end if(!empty($this->request->data['Document']['img1']['name']))
								
				/*
				 * loop dei destinatari
				*/
				$msg_ok = '';
				$msg_no = '';
				$tot_ok=0;
				$tot_no=0;
				foreach($results as $result) {
					if(isset($result['User'])) {
						$mail = $result['User']['email'];
						$mail2 = $result['UserProfile']['email'];
						$name = $result['User']['name'];
					}
					else  {	
						$mail = $result['Supplier']['mail'];
						$mail2 = '';
						$name = $result['SuppliersOrganization']['name'];
					}

					$Email->viewVars(['body_header' => sprintf(Configure::read('Mail.body_header'), $name)]);
						
					if(!empty($this->user->organization['Organization']['www'])) {
						if($this->request->data['Mail']['mittenti']==Configure::read('Mail.no_reply_mail'))
							$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply'), $this->traslateWww($this->user->organization['Organization']['www']))));
						else
							$Email->viewVars(['body_footer' => sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www']))]);
					}
					else {
						if($this->request->data['Mail']['mittenti']==Configure::read('Mail.no_reply_mail'))
							$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply_simple'))));
						else
							$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_simple'))));
					}
													
					$body_mail = $body_header_mittente.'<br /><br />';
					$body_mail .= $this->request->data['Mail']['body'];

					$mailResults = $Mail->send($Email, [$mail2, $mail], $body_mail, $debug);
					if(isset($mailResults['OK'])) {
						$tot_ok++;
						$msg_ok .= $mailResults['OK'].'<br />';							
					}
					else 
					if(isset($mailResults['KO'])) {
						$tot_no++;
						$msg_ok .= $mailResults['KO'].'<br />';	
					}						
				} 			
				$msg_ok .= $msgAttachment;
			} // if(!empty($destinatari))
			else 
				$msg_no .= "Nessun destinatario selezionato!";
			
			/*
			 * messaggio
			 */
			if(!empty($msg_ok)) $msg_ok = 'La mail è stata inviata a<br />'.$msg_ok.'<br/>Totale: '.$tot_ok; 
			if(!empty($msg_no)) $msg_no = '<hr />La mail NON è stata inviata a<br />'.$msg_no.'<br/>Totale: '.$tot_no; 
			$msg = $msg_ok.$msg_no;	
			$this->Session->setFlash($msg);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		/*
		 * get elenco ordini filtrati
		*/
		$orders = [];
		if($this->isReferentGeneric()) {
			App::import('Model', 'Order');
			$Order = new Order;
			
			$options = [];
			$options['conditions'] = ['Order.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Delivery.organization_id' => (int)$this->user->organization['Organization']['id'],
									   'Order.state_code !='=> 'CREATE-INCOMPLETE',
									   'Order.isVisibleBackOffice'=> 'Y',
									   'Delivery.isVisibleBackOffice'=> 'Y',
									   'Delivery.stato_elaborazione'=> 'OPEN'];
			if(!$this->isSuperReferente())
				$options['conditions'] += ['Order.supplier_organization_id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')'];

			$options['order'] = ['Delivery.data ASC, Order.data_inizio ASC'];
			$options['recursive'] = 1;
			$results = $Order->find('all', $options);
			$orders = [];
			if(!empty($results))
			foreach ($results as $result) {
				if($result['Delivery']['sys']=='N')
					$label = $result['Delivery']['luogoData'];
				else 
					$label = $result['Delivery']['luogo'];
				
				if($result['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty'))
					$data_fine = $result['Order']['data_fine_validation_'];
				else 
					$data_fine = $result['Order']['data_fine_'];
				
				$orders[$result['Order']['id']] = $label.' '.$result['SuppliersOrganization']['name'].' - dal '.$result['Order']['data_inizio_'].' al '.$data_fine;
			}
			$this->set(compact('orders'));
		}
		
		/*
		 * destinatari
		 */
		$dest_options = [];
				 
		if($this->isManager())
			$dest_options += array('USERGROUPS' => 'Gruppi');
			
		$dest_options += array('USERS' => 'Utenti',
							  'REFERENTI' => 'Referenti');
		
		if($this->isReferentGeneric())
			$dest_options += array('SUPPLIERS' => 'Produttori');
			
		if(!empty($orders))	
			$dest_options += array('USERS_CART' => "Utenti dell'ordine");
		
		$dest_options_qta = array('ALL' => 'A tutti',
								  'SOME' => 'Ad alcuni');
		
		$this->set(compact('dest_options','dest_options_qta'));
		
		/*
		 * mittenti
		 */
		$mittenti = array($this->user->email => $this->user->email.' '.$this->user->name,
						  Configure::read('Mail.no_reply_mail') => Configure::read('SOC.name').' '.Configure::read('Mail.no_reply_mail'));
		$this->set(compact('mittenti'));
	
		App::import('Model', 'User');
		$User = new User;

		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
		$users = $User->getUsersList($this->user, $conditions, Configure::read('orderUser'), array('name', 'email'));
		$this->set('users',$users);

		/*
		 * gruppi
		 */
		$userGroups = []; 
		
		if ($this->user->organization['Organization']['hasDes'] == 'N') { 
			foreach ($this->userGroups as $group_id => $data) {
					if($data['type']=='DES')	
						unset($this->userGroups[$group_id]);
			}	
		}
		
		foreach ($this->userGroups as $group_id => $data) 
			$userGroups[$group_id] = $data['name'];

		unset($userGroups[Configure::read('group_id_storeroom')]);
		unset($userGroups[Configure::read('group_id_user')]);  // gia' non c'e'
		
		$this->set('userGroups',$userGroups);
		 
		/*
		 * referenti
		*/		
		$conditions = array('UserGroupMap.group_id IN' => '('.Configure::read('group_id_referent').')');
		$referenti = $User->getUsersList($this->user, $conditions);
		$this->set('referenti',$referenti);
		
		/*
		 * get elenco produttori filtrati
		*/
		if($this->isReferentGeneric()) {
			
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization', 'CategoriesSupplier')));
			$SuppliersOrganization->unbindModel(array('hasMany' => array('Article', 'Order', 'SuppliersOrganizationsReferent')));
			
			$options = [];
			if($this->isSuperReferente()) {
	
				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
											   'SuppliersOrganization.stato' => 'Y',
												'Supplier.mail != ' => '',
											   "(Supplier.stato = 'Y' OR Supplier.stato = 'T' OR Supplier.stato = 'PG')");

			}
			else {

				$options['conditions'] = array('SuppliersOrganization.organization_id' => $this->user->organization['Organization']['id'],
												'SuppliersOrganization.stato' => 'Y',
												'Supplier.mail != ' => '',
												0 => "(Supplier.stato = 'Y' OR Supplier.stato = 'T' OR Supplier.stato = 'PG')",
												1 => 'SuppliersOrganization.id IN ('.$this->user->get('ACLsuppliersIdsOrganization').')');
			}
			$options['recursive'] = 1;
			$options['order'] = array('SuppliersOrganization.name');

			$results = $SuppliersOrganization->find('list', $options);
			$this->set('ACLsuppliersOrganization',$results);
		}
				
		$this->set('body_header', sprintf(Configure::read('Mail.body_header'), 'Mario Rossi'));
		$this->set('body_header_mittente', $body_header_mittente);
		
		$this->set('body_footer', sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www'])));
		$this->set('body_footer_no_reply', sprintf(Configure::read('Mail.body_footer_no_reply'), $this->traslateWww($this->user->organization['Organization']['www'])));

		/*
		 * se id valorizzato arrivo da admin_index e voglio utilizzare subject / title di una mail precedente
		 */	
		 if(!empty($mail_id)) {
			$options = [];
			$options['conditions'] = array('Mail.organization_id' => $this->user->organization['Organization']['id'],
										   'Mail.id' => $mail_id);
			$options['fields'] = array('Mail.subject','Mail.body');
			$options['recursive'] = -1;
			$mailResults = $this->Mail->find('first', $options);
			
			$this->request->data['Mail']['subject'] = $mailResults['Mail']['subject'];
			$this->request->data['Mail']['body'] = $mailResults['Mail']['body'];
			
			$this->Session->setFlash("Copiato l'oggetto e il testo della mail selezionata");
		 }	
	}	
	
	public function admin_root_index() {
		
		if(!$this->isRoot()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$conditions = array('Mail.organization_id' => 0);
		$conditions += array('Mail.user_id' => $this->user->id);
		
		$this->paginate = array('conditions' => array($conditions), 'order' => 'Mail.created desc, User.name');
		$results = $this->paginate('Mail');
		$this->set(compact('results'));	
	}
	
	public function admin_root_send() {

		$debug = false;
		
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));		
		}
					
		$body_header_mittente = '';
					
		if ($this->request->is('post') || $this->request->is('put')) {
				
			self::d($this->request->data, $debug);
			
			App::import('Model', 'Organization');
			$Organization = new Organization;
			
			App::import('Model', 'Supplier');
			$Supplier = new Supplier;
				
			App::import('Model', 'Mail');
			$Mail = new Mail;
			
			$Email = $Mail->getMailSystem($this->user);
		
			self::d($this->request->data,$debug);
			
			switch($this->request->data['Mail']['dest_options']) {
				case 'SUPPLIERS':
							
					$sql = "SELECT
								Supplier.id, Supplier.mail, Supplier.name
							FROM
								".Configure::read('DB.prefix')."suppliers AS Supplier
							WHERE
								(Supplier.mail is not null and Supplier.mail != '') 
								AND (Supplier.stato = 'Y' OR Supplier.stato = 'T' OR Supplier.stato = 'PG') ";
				
					if($this->request->data['Mail']['dest_options_qta_supplier']=='SOME') {
						$destinatari = $this->request->data['Mail']['suppliers'];
						$ids = '';
						foreach($destinatari as $key => $value)
							$ids .= $value.',';
						$ids = substr($ids , 0, strlen($ids)-1);
									
						$sql .= " AND Supplier.id IN (".$ids.")";
					}
					self::d($sql, $debug);
					$results = $Supplier->query($sql);
				break;
				case 'GAS':
				case 'PRODGAS':

					$sql = "SELECT
								Organization.id, Organization.mail, Organization.name
							FROM
								".Configure::read('DB.prefix')."organizations AS Organization 
							WHERE
								Organization.type = '".$this->request->data['Mail']['dest_options']."'";
								
					if($this->request->data['Mail']['dest_options_qta_gas']=='SOME' || 
					   $this->request->data['Mail']['dest_options_qta_gas']=='SOME-MANAGER') {
						   
						if($this->request->data['Mail']['dest_options']=='GAS')
							$destinatari = $this->request->data['Mail']['gas'];
						else
							$destinatari = $this->request->data['Mail']['prodgas'];
						$ids = '';
						foreach($destinatari as $key => $value)
							$ids .= $value.',';
						$ids = substr($ids , 0, strlen($ids)-1);
									
						$sql .= " AND Organization.id IN (".$ids.")";
					}
					self::d($sql, $debug);
					$organizationResults = $Organization->query($sql);	

					/*
					 * estraggo gli users dell'organization 
					 */
					App::import('Model', 'User');
					
					$results = [];
					$i=0;
					foreach($organizationResults as $numResult => $organizationResult) {
						
						$User = new User;
						$conditions = [];
						if($this->request->data['Mail']['dest_options_qta_gas']=='SOME-MANAGER') 
							$conditions += ['UserGroup.group_id' => Configure::read('group_id_manager')];
						else 
							$conditions += ['UserGroup.group_id' => Configure::read('group_id_user')];
							
						$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organizationResult['Organization']['id']]);

						$usersResults = $User->getUsersComplete($tmp_user, $conditions);
								
						foreach($usersResults as $usersResult) {
							$results[$i]['User'] = $usersResult['User'];
							$i++;
						}
					}	
				break;
			}			

			/*
			 * save Mail
			 */	
			$data = [];
			$data['Mail']['organization_id'] = 0;
			$data['Mail']['user_id'] = $this->user->id;
			$data['Mail']['mittente'] = $this->request->data['Mail']['mittenti'];
			$data['Mail']['dest_options'] = $this->request->data['Mail']['dest_options'];
			if(!empty($this->request->data['Mail']['dest_options_qta_supplier']))
				$data['Mail']['dest_options_qta'] = $this->request->data['Mail']['dest_options_qta_supplier'];
			else 
				$data['Mail']['dest_options_qta'] = $this->request->data['Mail']['dest_options_qta_gas'];
			$data['Mail']['dest_ids'] = '';
			$data['Mail']['subject'] = $this->request->data['Mail']['subject'];
			$data['Mail']['body'] = $this->request->data['Mail']['body'];
			$data['Mail']['allegato'] = $this->request->data['Document']['img1']['name'];

			$this->Mail->create();
			$this->Mail->save($data);
				
			/*	
			 * mittenti
			*/
			if($this->request->data['Mail']['mittenti']==Configure::read('Mail.no_reply_mail'))
				$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
			else
				$Email->replyTo([$this->user->email => $this->user->email]);
	
			$subject_mail = $this->request->data['Mail']['subject'];
			$Email->subject($subject_mail);
			
			
			/*
			 * 	$img1 = array(
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
			$msgAttachment = "";
			if(!empty($this->request->data['Document']['img1']['name'])) {
			
				$uploadSuccess=false;
				
				$img1 = $this->request->data['Document']['img1'];
				
				if($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name']))	{
						
					$path_upload = Configure::read('App.root').Configure::read('App.img.upload.tmp').DS;
					$ext = strtolower(pathinfo($img1['name'],PATHINFO_EXTENSION));
					
					if(move_uploaded_file($img1['tmp_name'], $path_upload.$img1['name'])) {
						$Email->attachments($path_upload.$img1['name']);
						$uploadSuccess=true;
					}
					else
						$uploadSuccess=false;
				}
				else 
					$uploadSuccess=false;		

				if($uploadSuccess)
					$msgAttachment .= "<br />Caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
				else {
					$msgAttachment .= "<br />Non caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
					CakeLog::write("error", $img1['error'], array("mails"));
				}
			} // end if(!empty($this->request->data['Document']['img1']['name']))
							
			/*
			 * loop dei destinatari
			*/
			$msg_ok = '';
			$msg_no = '';
			$tot_ok=0;
			$tot_no=0;
			foreach($results as $result) {
				switch($this->request->data['Mail']['dest_options']) {
					case 'SUPPLIERS':
						$id = $result['Supplier']['id'];
						$mail = $result['Supplier']['mail'];
						$name = $result['Supplier']['name'];								
					break;
					case 'GAS':
					case 'PRODGAS':
						$id = $result['User']['id'];
						$mail = $result['User']['email'];
						$name = $result['User']['name'];					
					break;
				}				

				if($this->request->data['Mail']['mittenti']==Configure::read('Mail.no_reply_mail'))  
					$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_no_reply_simple'), $this->traslateWww(Configure::read('SOC.site')))));
				else
					$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_simple'), $this->traslateWww(Configure::read('SOC.site')))));
																			
				$body_mail = $body_header_mittente.'<br /><br />';
				$body_mail .= $this->request->data['Mail']['body'];
						
				$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
				if(isset($mailResults['OK'])) {
					$tot_ok++;
					$msg_ok .= $mailResults['OK'].'<br />';							
				}
				else 
				if(isset($mailResults['KO'])) {
					$tot_no++;
					$msg_ok .= $mailResults['KO'].'<br />';	
				}						

			}  // end loop			
			$msg_ok .= $msgAttachment;
		
			/*
			 * messaggio
			 */
			if(!empty($msg_ok)) $msg_ok = 'La mail è stata inviata a<br />'.$msg_ok.'<br/>Totale: '.$tot_ok; 
			if(!empty($msg_no)) $msg_no = '<hr />La mail NON è stata inviata a<br />'.$msg_no.'<br/>Totale: '.$tot_no; 
			$msg = $msg_ok.$msg_no;	
			$this->Session->setFlash($msg);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		
		$dest_options = ['SUPPLIERS' => 'Produttori', 
						 'GAS' => 'Organization - G.A.S.', 
						 'PRODGAS' => 'Organization - Produttori'];
					
		$dest_options_qta_supplier = ['ALL' => 'A tutti',
								  'SOME' => 'Ad alcuni'];
								  
					
		$dest_options_qta_gas = ['ALL' => 'A tutti',
								  'SOME' => 'Ad alcuni',
								  'SOME-MANAGER' => 'Solo ai manager'];
		
		$this->set(compact('dest_options','dest_options_qta_supplier','dest_options_qta_gas'));

		/*
		 *  organizations => users
		*/	
		App::import('Model', 'Organization');
		$Organization = new Organization;
		
		App::import('Model', 'User');
			
		$options = [];
		$options['recursive'] = -1;
        $options['conditions'] = ['Organization.type' => 'GAS'];
        $options['order'] = ['Organization.name'];
		$organizationResults = $Organization->find('all', $options);
		$newOrganizationResults = [];
		foreach($organizationResults as $organizationResult) {
			
			$User = new User;
			$options['conditions'] = ['User.organization_id' => $organizationResult['Organization']['id'], 'User.block' => 0];
			$tot_users = $User->find('count', $options);
			$tmp = '';
			if($organizationResult['Organization']['stato']=='N') 
				$tmp = ' NON ATTIVO';
			$newOrganizationResults[$organizationResult['Organization']['id']] = $organizationResult['Organization']['name'].' ('.$tot_users.')'.$tmp;			
		}
		$this->set('organizationResults', $newOrganizationResults);
			
		$options = [];
		$options['recursive'] = -1;
        $options['conditions'] = ['Organization.type' => 'PRODGAS'];
        $options['order'] = ['Organization.name'];
		$organizationProdGasResults = $Organization->find('all', $options);
		$newOrganizationProdGasResults = [];
		foreach($organizationProdGasResults as $organizationProdGasResult) {
			
			$User = new User;
			$options['conditions'] = ['User.organization_id' => $organizationProdGasResult['Organization']['id'], 'User.block' => 0];
			$tot_users = $User->find('count', $options);
			if($organizationProdGasResult['Organization']['stato']=='N') 
				$tmp = ' NON ATTIVO';			
			$newOrganizationProdGasResults[$organizationProdGasResult['Organization']['id']] = $organizationProdGasResult['Organization']['name'].' ('.$tot_users.')'.$tmp;			
		}
		$this->set('organizationProdGasResults', $newOrganizationProdGasResults);
		
		/*
		 * mittenti
		 */
		$mittenti = [$this->user->email => $this->user->email.' '.$this->user->name,
					  Configure::read('Mail.no_reply_mail') => Configure::read('SOC.name').' '.Configure::read('Mail.no_reply_mail')];
		$this->set(compact('mittenti'));

		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
	
		/*
		 *  produttori
		*/	
		$options = [];
		$options['conditions'] = ["(Supplier.mail is not null and Supplier.mail != '')",
					"(Supplier.stato = 'Y' OR Supplier.stato = 'T' OR Supplier.stato = 'PG')"];
			
		$options['fields'] = ['Supplier.id', 'Supplier.name'];
		$options['order'] = ['Supplier.name'];
		$options['recursive'] = -1;
		$results = $Supplier->find('list', $options);

		$this->set('ACLsuppliersOrganization',$results);
				
		$this->set('body_header_mittente', $body_header_mittente);

		$this->set('body_footer_no_reply', sprintf(Configure::read('Mail.body_footer_no_reply_simple'), $this->traslateWww(Configure::read('SOC.site'))));		
	}	
	
	public function admin_des_index() {
		
		if(!$this->isManagerDes() && !$this->isTitolareDesSupplier()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
				
		App::import('Model', 'DesSupplier');
		
		$conditions = ['Mail.organization_id' => $this->user->organization['Organization']['id'],
							'Mail.user_id' => $this->user->id,
							'Mail.dest_options' => 'DES'];
		
		$this->paginate = ['conditions' => [$conditions], 'order' => 'Mail.created desc, User.name'];
		$results = $this->paginate('Mail');
		foreach($results as $numResult => $result) {
			
			/*
			 * estraggo i PRODUTTORI
			 */
			$DesSupplier = new DesSupplier;
			$DesSupplier->unbindModel(array('belongsTo' => array('De', 'OwnOrganization')));
			
			$options = [];
			$options['conditions'] = array('DesSupplier.id' => $result['Mail']['dest_ids']);				
			$options['recursive'] = 0;
			$desSupplierResults = $DesSupplier->find('first', $options);			
			
			$results[$numResult]['DesSupplier'] = $desSupplierResults['DesSupplier'];
			$results[$numResult]['Supplier'] = $desSupplierResults['Supplier'];
		}
		$this->set(compact('results'));	
	}
	
	/*
	 * se arrivo dal menu gli passo $des_order_id
	 */	
	public function admin_des_send($des_order_id=0) {

   		// $debug = true;
		
		if(!$this->isManagerDes() && !$this->isTitolareDesSupplier()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}

		$roles = [Configure::read('group_id_manager_des'),
				  Configure::read('group_id_super_referent_des'),
				  Configure::read('group_id_referent_des'),
				  Configure::read('group_id_titolare_des_supplier'),
				  Configure::read('group_id_des_supplier_all_gas')
		];
			
		$body_header_mittente = '';	
		$body_header_mittente .= 'Il gasista '.$this->user->name.' <a title="'.__('Email send').'" target="_blank" href="mailto:'.$this->user->email.'">'.$this->user->email.'</a> scrive:';

		App::import('Model', 'DesOrganization');
		App::import('Model', 'DesSuppliersReferent');
		App::import('Model', 'User');
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			App::import('Model', 'Mail');
			$Mail = new Mail;
			
			$Email = $Mail->getMailSystem($this->user);
		
			self::d($this->request->data,$debug);
			
			$des_supplier_id = $this->request->data['DesSupplier']['id'];			
	
			/*
			 * i GAS associati
			 */
			$DesOrganization = new DesOrganization;
			$DesOrganization->unbindModel(array('belongsTo' => array('De')));
	
			$options = [];
			$options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id,
										   // escludo il proprio 'DesOrganization.organization_id != ' => $this->user->organization['Organization']['id']
										];
			$options['recursive'] = 0;
			$options['order_by'] = ['Organization.name'];
			$desOrganizationsResults = $DesOrganization->find('all', $options);	
				
	
			$results = [];
			/*
			 * per ogni GAS estraggo gli utenti
			 */			
			foreach($desOrganizationsResults as $numResult2 => $desOrganizationsResult) {
				$organization_id = $desOrganizationsResult['Organization']['id'];
				
				$DesSuppliersReferent = new DesSuppliersReferent;
				$results += $DesSuppliersReferent->getUsersRoles($this->user, $organization_id, $roles, $des_supplier_id);			
			}
		
			/*
			 * save Mail
			 */	
			$data = [];
			$data['Mail']['organization_id'] = $this->user->organization['Organization']['id'];
			$data['Mail']['user_id'] = $this->user->id;
			$data['Mail']['mittente'] = $this->user->email;
			$data['Mail']['dest_options'] = 'DES';
			$data['Mail']['dest_options_qta'] = 'ALL';
			$data['Mail']['dest_ids'] = $des_supplier_id;
			$data['Mail']['subject'] = $this->request->data['Mail']['subject'];
			$data['Mail']['body'] = $this->request->data['Mail']['body'];
			$data['Mail']['allegato'] = $this->request->data['Document']['img1']['name'];

			$this->Mail->create();
			$this->Mail->save($data);
				
			/*
			 * mittenti
			*/
			$Email->replyTo([$this->user->email => $this->user->email]);
			$subject_mail = $this->request->data['Mail']['subject'];
			$Email->subject($subject_mail);
			
			/*
			 * 	$img1 = array(
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
			$msgAttachment = "";
			if(!empty($this->request->data['Document']['img1']['name'])) {
			
				$uploadSuccess=false;
				
				$img1 = $this->request->data['Document']['img1'];
				
				if($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name']))	{
						
					$path_upload = Configure::read('App.root').Configure::read('App.img.upload.tmp').DS;
					$ext = strtolower(pathinfo($img1['name'],PATHINFO_EXTENSION));
					
					if(move_uploaded_file($img1['tmp_name'], $path_upload.$img1['name'])) {
						$Email->attachments($path_upload.$img1['name']);
						$uploadSuccess=true;
					}
					else
						$uploadSuccess=false;
				}
				else 
					$uploadSuccess=false;		

				if($uploadSuccess)
					$msgAttachment .= "<br />Caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
				else {
					$msgAttachment .= "<br />Non caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
					CakeLog::write("error", $img1['error'], array("mails"));
				}
			} // end if(!empty($this->request->data['Document']['img1']['name']))
							
			/*
			 * loop dei destinatari
			*/
			$msg_ok = '';
			$msg_no = '';
			$tot_ok=0;
			$tot_no=0;
			foreach($results as $result) {
			
				$id = $result['User']['id'];
				
				/*
				 * escludo lo user
				 */
				if($id!=$this->user->id) {
					$mail = $result['User']['email'];
					$name = $result['User']['name'];					
		
					$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_simple'), $this->traslateWww(Configure::read('SOC.site')))));
							
					$body_mail = $body_header_mittente.'<br /><br />';
					$body_mail .= $this->request->data['Mail']['body'];
						
					$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
					if(isset($mailResults['OK'])) {
						$tot_ok++;
						$msg_ok .= $mailResults['OK'].'<br />';							
					}
					else 
					if(isset($mailResults['KO'])) {
						$tot_no++;
						$msg_ok .= $mailResults['KO'].'<br />';	
					}						
				
				} // if($id!=$this->user->id) 
			}  // end loop			
			$msg_ok .= $msgAttachment;
		
			/*
			 * messaggio
			 */
			if(!empty($msg_ok)) $msg_ok = 'La mail è stata inviata a<br />'.$msg_ok.'<br/>Totale: '.$tot_ok; 
			if(!empty($msg_no)) $msg_no = '<hr />La mail NON è stata inviata a<br />'.$msg_no.'<br/>Totale: '.$tot_no; 
			$msg = $msg_ok.$msg_no;	
			$this->Session->setFlash($msg);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
	
		/*
		 * elenco di tutti i gruppi dell'organization userGroupsComponent
		*/
		$this->set('userGroups',$this->userGroups);
		
  		$this->set('isManagerDes', $this->isManagerDes());
   		$this->set('isTitolareDesSupplier', $this->isTitolareDesSupplier());

		App::import('Model', 'DesSupplier');
		$DesSupplier = new DesSupplier;
		$DesSupplier->unbindModel(array('belongsTo' => array('De', 'OwnOrganization')));
		$DesSupplier->unbindModel(array('hasMany' => array('DesOrder')));

		App::import('Model', 'Supplier');
		
		App::import('Model', 'Organization');

		
		/*
		 * se arrivo dal menu, $des_order_id valorizzato, recupero $des_supplier_id
		 * 	e visualizzo solo quel produttore
		 */
		$des_supplier_id = 0; 
		if(!empty($des_order_id)) {
			App::import('Model', 'DesOrder');
			$DesOrder = new DesOrder();
			
			$options = [];
			$options['conditions'] = ['DesOrder.des_id' => $this->user->des_id,
									  'DesOrder.id' => $des_order_id];
			$options['fields'] = ['DesOrder.des_supplier_id']; 
			$options['recursive'] = -1;
			$desOrderResults = $DesOrder->find('first', $options);
			$des_supplier_id = $desOrderResults['DesOrder']['des_supplier_id']; 
		
		}
			
		/*
		 * estraggo i PRODUTTORI
		 */
		$options = [];
		$options['recursive'] = -1;
		$options['conditions'] = ['DesSupplier.des_id' => $this->user->des_id];
		if(!empty($des_supplier_id)) 
			$options['conditions'] += ['DesSupplier.id' => $des_supplier_id];
		else
		if($this->isTitolareDesSupplier()) {
			$ACLsuppliersIdsDes = $this->user->get('ACLsuppliersIdsDes');
			if(empty($ACLsuppliersIdsDes)) 
				$options['conditions'] += ['DesSupplier.id IN (0)'];
			else
				$options['conditions'] += ['DesSupplier.id IN ('.$this->user->get('ACLsuppliersIdsDes').')'];
		}
		$options['recursive'] = 1;
		$results = $DesSupplier->find('all', $options);
	
		self::d($results, $debug);

		$this->set(compact('results'));	

		$this->set('body_header', sprintf(Configure::read('Mail.body_header'), 'Mario Rossi'));
		$this->set('body_header_mittente', $body_header_mittente);
		
		$this->set('body_footer', sprintf(Configure::read('Mail.body_footer'), $this->traslateWww($this->user->organization['Organization']['www'])));		
	}
	
	public function admin_des_send_details_users($des_supplier_id) {
		
		$debug= false;

		$roles = [Configure::read('group_id_manager_des'),
				  Configure::read('group_id_super_referent_des'),
				  Configure::read('group_id_referent_des'),
				  Configure::read('group_id_titolare_des_supplier'),
				  Configure::read('group_id_des_supplier_all_gas')
		];
		
		App::import('Model', 'DesSuppliersReferent');

		App::import('Model', 'DesOrganization');
		$DesOrganization = new DesOrganization;
	
		$DesOrganization->unbindModel(array('belongsTo' => array('De')));

		$options = [];
		$options['conditions'] = ['DesOrganization.des_id' => $this->user->des_id,
								  // escludo il proprio 'DesOrganization.organization_id != ' => $this->user->organization['Organization']['id']
								 ];
		$options['recursive'] = 0;
		$options['order_by'] = ['Organization.name'];
		$desOrganizationsResults = $DesOrganization->find('all', $options);	
		
		self::d([$options,$desOrganizationsResults],$debug);
		
		$results = [];
		/*
		 * per ogni GAS estraggo gli utenti
		 */			
		foreach($desOrganizationsResults as $numResult => $desOrganizationsResult) {
			$organization_id = $desOrganizationsResult['Organization']['id'];
			
			$DesSuppliersReferent = new DesSuppliersReferent;
			$usersResults = $DesSuppliersReferent->getUsersRoles($this->user, $organization_id, $roles, $des_supplier_id);			
						
			$results[$numResult]['Organization'] = $desOrganizationsResult['Organization'];
			$results[$numResult]['Organization']['Referenti'] = $usersResults;
		}
		
		self::d($results, $debug);
			
		$this->set(compact('results'));	
		
		$this->set('userGroups',$this->userGroups);
		
		$this->layout = 'ajax';
	}
	
	public function admin_root_delete($id = null) {
	
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		$this->Mail->id = $id;
		if (!$this->Mail->exists($this->Mail->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		if ($this->Mail->delete())
			$this->Session->setFlash(__('Delete Mail'));
		else
			$this->Session->setFlash(__('Mail was not deleted'));
		$this->myRedirect(array('action' => 'root_index'));
	}
		
	public function admin_prod_gas_supplier_delete($id = null) {
	
		/*
		 * ACL Organization / prod_gas_supplier
		 */		
		if(empty($this->user->organization['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$this->Mail->id = $id;
		if (!$this->Mail->exists($this->Mail->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		if ($this->Mail->delete())
			$this->Session->setFlash(__('Delete Mail'));
		else
			$this->Session->setFlash(__('Mail was not deleted'));
		$this->myRedirect(array('action' => 'prod_gas_supplier_index'));
	}
	
	public function admin_delete($id = null) {
	
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));			
		}
		
		$this->Mail->id = $id;
		if (!$this->Mail->exists($this->Mail->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
	
		if ($this->Mail->delete())
			$this->Session->setFlash(__('Delete Mail'));
		else
			$this->Session->setFlash(__('Mail was not deleted'));
		$this->myRedirect(['action' => 'index']);
	}
	
	/* 
	 * PRODUTTORE
	 */
	public function admin_prod_gas_supplier_index() {

		/*
		 * ACL Organization / prod_gas_supplier
		 */		
		if(empty($this->user->organization['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		
		$conditions = array('Mail.organization_id' => 0);
		$conditions += array('Mail.user_id' => $this->user->id);
		
		$this->paginate = array('conditions' => array($conditions), 'order' => 'Mail.created desc, User.name');
		$results = $this->paginate('Mail');
		$this->set(compact('results'));	
	}
	
	public function admin_prod_gas_supplier_send() {

		$debug = false;

		/*
		 * ACL Organization / prod_gas_supplier
		 */
		if(empty($this->user->organization['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
					
		$body_header_mittente = '';
			
		App::import('Model', 'User');
		$User = new User;

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;

		App::import('Model', 'SuppliersOrganizationsReferent');
		$SuppliersOrganizationsReferent = new SuppliersOrganizationsReferent;
		
		App::import('Model', 'Organization');
		$Organization = new Organization;

													
		if ($this->request->is('post') || $this->request->is('put')) {
			
			App::import('Model', 'Mail');
			$Mail = new Mail;
			
			$Email = $Mail->getMailSystem($this->user);
		
			self::d($this->request->data,$debug);
						
			/*
			 * MANAGER, RFERENTI
			 */
			$results = [];			 
			if(!empty($this->request->data['Mail']['dest_options_hidden'])) {
				
				$organizations = explode(',', $this->request->data['Mail']['organizations_hidden']);
				
				foreach($organizations as $organization_id) {

					$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);
					
					$dest_options = explode(',', $this->request->data['Mail']['dest_options_hidden']);
					
					foreach($dest_options as $dest_option) {
					
						switch($dest_option) {
							case Configure::read('group_id_manager'):
								/*
								 * estraggo i manager dell'organization 
								 */
								$conditions = ['UserGroupMap.group_id' => Configure::read('group_id_manager')];
								$usersResults = $User->getUsers($tmp_user, $conditions);
								
								self::d([$conditions,$usersResults],$debug);
															
								foreach($usersResults as $usersResult) {
									$results[$usersResult['User']['id']] = $usersResult;
								}								
							break;
							case Configure::read('group_id_referent'):
								/*
								 * dati produttore
								 */
								$options = [];
								$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization_id,
														  'SuppliersOrganization.supplier_id' => $this->user->organization['Supplier']['Supplier']['id']];
								$options['fields'] = ['SuppliersOrganization.id'];
								$options['recursive'] = -1;
								$suppliersOrganizationResults = $SuppliersOrganization->find('first', $options);

								/*
								 * ottengo referenti del produttore
								*/
								if(!empty($suppliersOrganizationResult)) {
									$conditions = ['User.block' => 0,
													'SuppliersOrganization.id' => $suppliersOrganizationResults['SuppliersOrganization']['id']];
									$suppliersOrganizationsReferentResults = $SuppliersOrganizationsReferent->getReferentsCompact($tmp_user, $conditions);	
									
									self::d([$conditions,$suppliersOrganizationsReferentResults],$debug);
																	
									foreach($suppliersOrganizationsReferentResults as $suppliersOrganizationsReferentResult) {
										$results[$suppliersOrganizationsReferentResult['User']['id']] = $suppliersOrganizationsReferentResult;
									}								
								}
							break;
						}
					}
					
				} // end foreach($this->request->data['Mail']['organizations'] as $organization_id)
				
				self::d($results, $debug);
			
				if(!empty($results)) {
					/*
					 * save Mail
					 */	
					$dest_ids = ''; 
					$organizations = explode(',', $this->request->data['Mail']['organizations_hidden']);
					foreach($organizations as $organization_id) {
						$options = [];
						$options['conditions'] = ['Organization.id' => $organization_id];
						$options['fields'] = array('Organization.name');
						$options['recursive'] = -1;
						$organizationResults = $Organization->find('first', $options);
						$dest_ids .= $organizationResults['Organization']['name'].' ';
					}	
					
					$dest_ids .= " - ";
					$dest_options = explode(',', $this->request->data['Mail']['dest_options_hidden']);
					foreach($dest_options as $dest_option) {
					
						switch($dest_option) {
							case Configure::read('group_id_manager'):
								$dest_ids .= "Manager ";
							break;
							case Configure::read('group_id_referent'):
								$dest_ids .= "Referenti ";
							break;
						}
					}
	
						 
					$data = [];
					$data['Mail']['organization_id'] = 0;
					$data['Mail']['user_id'] = $this->user->id;
					$data['Mail']['mittente'] = $this->request->data['Mail']['mittenti'];
					$data['Mail']['dest_options'] = 'ORGANIZATIONS';
					$data['Mail']['dest_options_qta'] = 'ALL';
					$data['Mail']['dest_ids'] = $dest_ids;
					$data['Mail']['subject'] = $this->request->data['Mail']['subject'];
					$data['Mail']['body'] = $this->request->data['Mail']['body'];
					$data['Mail']['allegato'] = $this->request->data['Document']['img1']['name'];
			
					self::d($data,$debug);
					
					$this->Mail->create();
					$this->Mail->save($data);
				} // end if(!empty($this->request->data['Mail']['dest_options_hidden'))) 
					
				/*
				 * mittenti
				*/
				if($this->request->data['Mail']['mittenti']==Configure::read('Mail.no_reply_mail'))
					$Email->replyTo(Configure::read('Mail.no_reply_mail'), Configure::read('Mail.no_reply_name'));
				else
					$Email->replyTo([$this->user->email => $this->user->email]);
		
				$subject_mail = $this->request->data['Mail']['subject'];
				$Email->subject($subject_mail);
				
			
				/*
				 * 	$img1 = array(
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
				$msgAttachment = "";
				if(!empty($this->request->data['Document']['img1']['name'])) {
				
					$uploadSuccess=false;
					
					$img1 = $this->request->data['Document']['img1'];
					
					if($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name']))	{
							
						$path_upload = Configure::read('App.root').Configure::read('App.img.upload.tmp').DS;
						$ext = strtolower(pathinfo($img1['name'],PATHINFO_EXTENSION));
						
						if(move_uploaded_file($img1['tmp_name'], $path_upload.$img1['name'])) {
							$Email->attachments($path_upload.$img1['name']);
							$uploadSuccess=true;
						}
						else
							$uploadSuccess=false;
					}
					else 
						$uploadSuccess=false;		

					if($uploadSuccess)
						$msgAttachment .= "<br />Caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
					else {
						$msgAttachment .= "<br />Non caricato l'allegato \"".$this->request->data['Document']['img1']['name']."\"";
						CakeLog::write("error", $img1['error'], array("mails"));
					}
				} // end if(!empty($this->request->data['Document']['img1']['name']))
							
				/*
				 * loop dei destinatari
				*/
				$msg_ok = '';
				$msg_no = '';
				$tot_ok=0;
				$tot_no=0;
				foreach($results as $result) {
					$id = $result['User']['id'];
					$mail = $result['User']['email'];
					$name = $result['User']['name'];					
		
					$Email->viewVars(array('body_footer' => sprintf(Configure::read('Mail.body_footer_simple'), $this->traslateWww(Configure::read('SOC.site')))));
												
					$body_mail = $body_header_mittente.'<br /><br />';
					$body_mail .= $this->request->data['Mail']['body'];
							
					$mailResults = $Mail->send($Email, $mail, $body_mail, $debug);
					if(isset($mailResults['OK'])) {
						$tot_ok++;
						$msg_ok .= $mailResults['OK'].'<br />';							
					}
					else 
					if(isset($mailResults['KO'])) {
						$tot_no++;
						$msg_ok .= $mailResults['KO'].'<br />';	
					}						
				}  // end loop			
				$msg_ok .= $msgAttachment;
			
				/*
				 * messaggio
				 */
				if(!empty($msg_ok)) $msg_ok = 'La mail è stata inviata a<br />'.$msg_ok.'<br/>Totale: '.$tot_ok; 
				if(!empty($msg_no)) $msg_no = '<hr />La mail NON è stata inviata a<br />'.$msg_no.'<br/>Totale: '.$tot_no; 
				$msg = $msg_ok.$msg_no;	
			}
			else {
				$msg = "Non ci sono destinatari ai quali inviare mail!"; 
			}
			
			$this->Session->setFlash($msg);
			
		} // end if ($this->request->is('post') || $this->request->is('put'))
					
		$dest_options = [Configure::read('group_id_manager') => 'Manager del GAS', 
						 Configure::read('group_id_referent') => 'Referenti'];
							
		$this->set(compact('dest_options'));

		/*
		 * GAS associati
		 */
		App::import('Model', 'ProdGasSuppliersImport');
		$ProdGasSuppliersImport = new ProdGasSuppliersImport;
		$filters['ownerArticles'] = 'SUPPLIER';
		$organizationResults = $ProdGasSuppliersImport->getProdGasSuppliers($this->user, $this->user->organization['Organization']['id'], 0, $filters, $debug);
		self::d($organizationResults['Supplier']['Organization']);
		
		$newOrganizationResults = [];
		foreach($organizationResults['Supplier']['Organization'] as $organizationResult) {
			
			$newOrganizationResults[$organizationResult['Organization']['id']] = $organizationResult['Organization']['name'];			
		}
		$this->set('organizationResults', $newOrganizationResults);
			
		/*
		 * mittenti
		 */
		$mittenti = [$this->user->email => $this->user->email.' '.$this->user->name];
		$this->set(compact('mittenti'));

		$this->set('body_header_mittente', $body_header_mittente);
	}

	/*
	 * articoli acquistati di un ordine
	 */
	public function admin_ajax_users_cart_articles_orders($order_id) {
		
		$debug = false;
				
		$results = $this->Mail->getArticlesByOrderId_ConAcquisti($this->user, $order_id, [], $debug); 
        self::d($results, $debug);

        $this->set(compact('results'));	
				
        $this->layout = 'ajax';
        $this->render('/Mails/ajax_users_cart_articles_orders');	
	}
}