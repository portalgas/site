<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class DatabaseDateController extends AppController {

	public $organization_id = 0; 
	public $group_id_root = 0;
	public $group_id_user = 0;
	
	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */

		$this->group_id_root = Configure::read('group_id_root');
		$this->group_id_user = Configure::read('group_id_user');
		
		$this->set('organization',$this->user->organization);
	}

	public function display() {}
	public function admin_migration_eg3_index() {		$this->render('admin_migration_eg3_intro');	}
	
	public function admin_migration_eg3_categories_articles() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->DatabaseDate->executeMigrationEg3CategoriesArticles($this->user);
			$this->render('admin_migration_eg3');
		}
		else {
			/*
			 * ctrl se la tabella e' gia' popolata 
			 */
			$sql = "SELECT count(id) as totCtrl					FROM ".Configure::read('DB.prefix')."categories_articles 					WHERE						organization_id = ".(int)$this->user->organization['Organization']['id'];			$tot = current(current($this->DatabaseDate->query($sql)));			
			/*
			 * ctrl se organization hasFieldArticleCategoryId
			 */
			$introNote = "
					<table cellpadding='0' cellspacing='0'>
						<tr>
							<th style='width:50%;'>Da</th>
							<th>a</th>
					    </tr>
						<tr>
							<td>table.lacavagnetta_tipiarticoli (categorie padre)</td>
							<td rowspan='2'>table.categories_articles</td>
						</tr>
						<tr>
						<td>lacavagnetta_catmerceologica (categorie figlie)</td>
						</tr>
					</table>					
					<h3>Nota</h3>
					<ul>
						<li>
							creo campo <b>tmp_migration_codice</b> per registrare lacavagnetta_tipiarticoli.codice o lacavagnetta_catmerceologica.codice, servira' per la migrazione degli articoli
						</li>
						<li>
							se in table.categories_articles la categoria produttore esiste già la <span style='color:red'>duplica</span>!
						</li>
					</ul>
					";

			if($this->user->organization['Organization']['hasFieldArticleCategoryId']=='Y')
				$introNote .= "<h3>Organization permissions</h3>Modulo hasFieldArticleCategoryId abilitato.";
			else
				$introNote .= "<h2>Organization permissions: Modulo hasFieldArticleCategoryId NON abilitato.</h2>";
				
			
			if($tot['totCtrl']==0)
				$introNote .= "<h3>Test di controllo</h3>Tabella ".Configure::read('DB.prefix')."categories_articles non ancora popolata.";
			else 
				$introNote .= "<h2>Test di controllo: tabella ".Configure::read('DB.prefix')."categories_articles già popolata: trovati ".$tot['totCtrl']." records!</h2>";
			
			$this->set('introNote',$introNote);
			$this->set('introTitle',"categorie associate agli articoli associati all'organizzazione");
			
			$this->render('admin_migration_eg3_intro');
		}
	}

	public function admin_migration_eg3_suppliers() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->DatabaseDate->executeMigrationEg3Suppliers();
			$this->render('admin_migration_eg3');
		}
		else {
			/*			 * ctrl se la tabella e' gia' popolata			*/			$sql = "SELECT count(id) as totCtrl					FROM ".Configure::read('DB.prefix')."suppliers";			$tot = current(current($this->DatabaseDate->query($sql)));			
			$introNote = "
				<table cellpadding='0' cellspacing='0'>					<tr>						<th style='width:50%;'>Da</th>						<th>a</th>					</tr>					<tr>						<td>table.lacavagnetta_anagrafiche (con tipocfa = 'F')</td>						<td>table.suppliers</td>					</tr>				</table>				<h3>Nota</h3>				<ul>					<li>						creo campo <b>tmp_migration_codice</b> per registrare lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti					</li>
					<li>
						Campo category_supplier_id = 0 <span style='color:red'>vuoto</span>!, poi copiato in suppliers_organizations
					</li>
					<li>
						se in table.supplies il produttore esiste già lo <span style='color:red'>duplica</span>!
					</li>				</ul>
				<h3>Memo localhost</h3>
				<ul>
					<li style='list-style: square outside none;'>UPDATE ".Configure::read('DB.prefix')."suppliers SET mail = 'fractis@libero.it'</li>
				</ul>";
									
			if($tot['totCtrl']==0)				$introNote .= "<h3>Test di controllo</h3>Tabella ".Configure::read('DB.prefix')."suppliers non ancora popolata.";			else				$introNote .= "<h2>Test di controllo: tabella ".Configure::read('DB.prefix')."suppliers già popolata: trovati ".$tot['totCtrl']." records!</h2>";						
			$this->set('introNote',$introNote);
			$this->set('introTitle',"produttori generici non associati all'organizzazione");
			
			$this->render('admin_migration_eg3_intro');
		}
	}

	public function admin_migration_eg3_users() {		if ($this->request->is('post') || $this->request->is('put')) {
			
			$parameters = [];
			if(isset($this->request->data['hasArticlesOrder']) && !empty($this->request->data['hasArticlesOrder']))
				$parameters += array('hasArticlesOrder' => $this->request->data['hasArticlesOrder']);
				
			if(isset($this->request->data['password_default']) && !empty($this->request->data['password_default']))				$parameters += array('password_default' => $this->request->data['password_default']);

			if(isset($parameters['password_default'])) { 				$this->DatabaseDate->executeMigrationEg3Users($this->user,$this->group_id_root,$this->group_id_user, $parameters);
				$this->render('admin_migration_eg3');
			}						else 
				$this->Session->setFlash("Indica la password di default da impostare per tutti gli utenti migrati");
			
			
		}
				
		$introNote = "					<table cellpadding='0' cellspacing='0'>						<tr>							<th style='width:50%;'>Da</th>							<th>a</th>					    </tr>						<tr>							<td rowspan='3'>table.lacavagnetta_anagrafiche (con tipocfa = 'C' and stato='1')</td>							<td>Joomla.#__users</td>						</tr>						<tr>							<td>Joomla.#__user_usergroup_map (lo getisce joomla)</td>						</tr>						<tr>
							<td>Joomla.#__user_profiles
								<ul>
									<li>Codice preso da Eg3.descrizione (054 Rossi Mario)</li>
									<li>Dati anagrafici</li>
								</ul>
							</td>
						</tr>
					</table>					<h3>Nota</h3>					<ul>						<li>							con userTable->check <b>controllo</b> se user su joomla esiste già <b>non</b> filtrando per organization_id: campi controllati sono username e mail						</li>
						<li>
							se si vuole filtrare è già implementato __check(organization)
						</li>
						<li>
							creo campo <b>tmp_migration_codice</b> per registrare lacavagnetta_anagrafiche.codice, servira' per la migrazione dei referenti
						</li>					</ul>					<h3>Gruppi</h3>					in joomla <b>#__user_usergroup_map</b> associa l'utente al gruppo
					<ul>
						<li style='list-style: square outside none;'>Registred di default</li>
						<li style='list-style: square outside none;'>GasPages[nome organizazione]</li>
					</ul>
					<h3>Campi così mappati</h3>
					<ul>
						<li>Joomla.users.name = table.lacavagnetta_anagrafiche.cognome table.lacavagnetta_anagrafiche.nome<br /> (utilizzato per l'ordinamento per Configure::read('orderUser'))</li>
						<li>Joomla.users.username = table.lacavagnetta_anagrafiche.email</li>
						<li>Joomla.users.email = table.lacavagnetta_anagrafiche.email</li>
						<li>Joomla.user_profiles.codice = table.lacavagnetta_anagrafiche.descrizione</li>
					</ul>
					<h3>Memo</h3>
					creare gli utenti
					<ul>
						<li style='list-style: square outside none;'>Dispensa con gruppo gasDispensa</li>
						<li style='list-style: square outside none;'>Tesoriere con gruppo gasTesoriere</li>
					</ul>
					<h3>Memo localhost</h3>
					<ul>
						<li style='list-style: square outside none;'>UPDATE ".Configure::read('DB.portalPrefix')."users SET email = 'fractis@libero.it' where organization_id = ".$this->user->organization['Organization']['id']."</li>
						<li style='list-style: square outside none;'>Abilitare utente 'root' a ricevere le mail ".Configure::read('DB.portalPrefix')."users.sendMail = 1 di sistema (ex alla registrazione di un utente)</li>
						<li style='list-style: square outside none;'>Abilitare a 'SuperUser' (id gruppo  ".Configure::read('group_id_user').") gli utenti ROOT</li>
						<li style='list-style: square outside none;'>Abilitare a 'GasManager' (id gruppo  ".Configure::read('group_id_manager').") gli utente che amministrano un Gas (consegne, referenti)</li>
						<li style='list-style: square outside none;'>Abilitare a 'Tesoriere' (id gruppo  ".Configure::read('group_id_tesoriere').") almeno un utente per Gas</li>
					</ul>
				 
					<h3>Parametri da settare</h3>
					<label for='pwd'>Gestisci gli articoli associati all'ordine</label> 
						        <span style='color:red;'>No</span> <input type='radio' name='hasArticlesOrder' value='N' />
        						<span style='color:green;'>Si</span> <input type='radio' name='hasArticlesOrder' value='Y' checked='checked'  />
					<label for='pwd'>Password di default</label> <input id='password_default' name='password_default' type='text' value='' size='50' class='noWidth' />";
		$this->set('introNote',$introNote);
		$this->set('introTitle',"utenti");
					$this->render('admin_migration_eg3_intro');	}

	public function admin_migration_eg3_users_pwd() {		if ($this->request->is('post') || $this->request->is('put')) {			$this->DatabaseDate->executeMigrationEg3UsersPwd($this->user,$this->group_id_root,$this->group_id_user);			$this->render('admin_migration_eg3');		}		else {
			/*
			 * totale users migrati da table.lacavagnetta_anagrafiche (tipocfa = 'C' and stato='1')
			 * */
			$sql = "SELECT count(email) as totJoomla
					FROM ".Configure::read('DB.portalPrefix')."users 
					WHERE 
						organization_id = ".(int)$this->user->organization['Organization']['id']."
						AND block = 0";
			self::d($sql, false);
			$totJoomla = current($this->DatabaseDate->query($sql));
			
			/*
			 * Totale utenti nella tabella TMP di Joomla con le password
			 * */
			$sql = "SELECT count(email) as totJoomlaPwd
					FROM ".Configure::read('DB.tableJoomlaWithPassword')." 
					WHERE block = 0
					";
			$totJoomlaPwd = current($this->DatabaseDate->query($sql));
			
			/*			 * totale users Match tra 
			 * 	- la tabella tmp di Joomla con le password
			 *  - la tabella Joomla.users			* */			$sql = "SELECT count(Master.email) as totMatch					FROM ".Configure::read('DB.tableJoomlaWithPassword')." Slave, ".Configure::read('DB.portalPrefix')."users Master 					WHERE 
						organization_id = ".(int)$this->user->organization['Organization']['id']."
						AND Master.email = Slave.email";			$totMatch = current($this->DatabaseDate->query($sql));			
			$users_pwd_default = ($totJoomla[0]['totJoomla'] - $totMatch[0]['totMatch']);
			/*
			 * estraggo l'elenco degli utenti con la password di default
			 */ 
			if($users_pwd_default > 0) {
				$sql = "SELECT User.*						FROM ".Configure::read('DB.portalPrefix')."users User LEFT JOIN ".Configure::read('DB.tableJoomlaWithPassword')." Slave ON								(User.email = Slave.email)						WHERE							User.organization_id = ".(int)$this->user->organization['Organization']['id']."							and Slave.email is null
						ORDER BY ".Configure::read('orderUser');
				$results = $this->DatabaseDate->query($sql);
			}
						$introNote = "
					<h3>Match</h3>
					<table cellpadding='0' cellspacing='0'>
						<tr>
							<th style='width:33%;'></th>
							<th style='width:33%;'>Master</th>
							<th>Slave</th>
						</tr>
						<tr>
							<th>Tabelle</th>
							<td>table.".Configure::read('DB.portalPrefix')."users</td>
							<td>table.".Configure::read('DB.tableJoomlaWithPassword')."</td>
						</tr>
						<tr>
							<th>Comparazione</th>
							<td>Master.email</td>
							<td>Slave.email</td>
						</tr>
						<tr>
							<th>Settare</th>
							<td>Master.password</td>
							<td>Slave.password</td>
						</tr>
					</table>										<h3>Verifica totale utenti</h3>
					<table>
						<tr>
							<th>Totale utenti in ".Configure::read('DB.portalPrefix')."_users<br />migrati da Eg3.lacavagnetta_anagrafiche</th>
							<th>Totale utenti nella tabella TMP di Joomla con le password</th>
							<th>Totale utenti che avranno la password da TMP Joomla<br />match tra ".Configure::read('DB.portalPrefix')."_users e ".Configure::read('DB.tableJoomlaWithPassword')."</th>
							<th>Totale utenti che avranno la password di default<br />impostata da migration_eg3_users()</th>
						</tr>						<tr>
							<td>".$totJoomla[0]['totJoomla']."</td>
							<td>".$totJoomlaPwd[0]['totJoomlaPwd']."</td>
							<td>".$totMatch[0]['totMatch']."</td>
							<td>".$users_pwd_default."</td>
						</tr>
					</table>
					";
			
			if($users_pwd_default > 0) {
				$introNote .= "<div class='users'><h2 class='ico-users'>Utenti che rimarranno con la passwrod di default *</h2>
								<table cellpadding='0' cellspacing='0'>
									<tr>
										<th>".__('N.')."</th>
										<th>Nominativo</th>
										<th>Username</th>
										<th>Mail</th>
										<th>registerDate</th>
									</tr>";
				foreach ($results as $i => $result) {
				
					$registerDate = date('d',strtotime($result['User']['registerDate'])).'/'.date('n',strtotime($result['User']['registerDate'])).'/'.date('Y',strtotime($result['User']['registerDate']));
					$introNote .= "<tr>
										<td>".($i+1)."</td>
										<td>".$result['User']['name']."</td>
										<td>".$result['User']['username']."</td>
										<td>".$result['User']['email']."</td>
										<td>".$registerDate."</td>
									</tr>";
				}
				$introNote .= "</table>
						* <span style='font-size:12px;'>perchè esistono in Eg3.lacavagnetta_anagrafiche e qunidi ora anche su ".Configure::read('DB.portalPrefix')."_users, ma non sulla tabella TMP di Joomla con le password</span>
							</div>";
			} // end if($users_pwd_default > 0) 	
								$this->set('introNote',$introNote);			$this->set('introTitle',"allineamento password di tutti gli utenti con la password di Joomla");							$this->render('admin_migration_eg3_intro');		}	}	
	public function admin_migration_eg3_suppliers_organizations() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->DatabaseDate->executeMigrationEg3SuppliersOrganizations($this->user);
			$this->render('admin_migration_eg3');
		}
		else {
			/*			 * ctrl se la tabella e' gia' popolata			*/			$sql = "SELECT count(id) as totCtrl					FROM ".Configure::read('DB.prefix')."suppliers_organizations
					WHERE
						organization_id = ".(int)$this->user->organization['Organization']['id'];								$tot = current(current($this->DatabaseDate->query($sql)));								$introNote = "
				<table cellpadding='0' cellspacing='0'>					<tr>						<th style='width:50%;'>Da</th>						<th>a</th>					</tr>					<tr>						<td>table.suppliers</td>						<td>table.suppliers_organizations</td>					</tr>				</table>				<h3>Nota</h3>				<ul>					<li>						in table.suppliers_organizations ho i campi
						<ul>							<li>name: evito di fare un join con table.suppliers</li>							<li>frequenza: ogni organization potrà modificare il valore</li>						</ul>						</li>					<li>						se in table.suppliers_organizations il produttore esiste già lo <span style='color:red'>duplica</span>!					</li>				</ul>";
											
			if($tot['totCtrl']==0)				$introNote .= "<h3>Test di controllo</h3>Tabella ".Configure::read('DB.prefix')."suppliers_organizations non ancora popolata.";			else				$introNote .= "<h2>Test di controllo: tabella ".Configure::read('DB.prefix')."suppliers_organizations già popolata: trovati ".$tot['totCtrl']." records!</h2>";				
			$this->set('introNote',$introNote);
			$this->set('introTitle',"produttori associati all'organizzazione");
			
			$this->render('admin_migration_eg3_intro');
		}
	}

	public function admin_migration_eg3_suppliers_organizations_referents() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->DatabaseDate->executeMigrationEg3SuppliersOrganizationsReferents($this->user);
			$this->render('admin_migration_eg3');
		}
		else {
			/*			 * ctrl se la tabella e' gia' popolata			*/			$sql = "SELECT count(*) as totCtrl					FROM ".Configure::read('DB.prefix')."suppliers_organizations_referents					WHERE						organization_id = ".(int)$this->user->organization['Organization']['id'];			$tot = current(current($this->DatabaseDate->query($sql)));
			$introNote = "				<table cellpadding='0' cellspacing='0'>					<tr>						<th style='width:50%;'>Da</th>						<th>a</th>					</tr>					<tr>						<td rowspan='3'>table.lacavagnetta_referenti</td>						<td>table.suppliers_organizations_referents</td>					</tr>
					<tr>
						<td>table.#__users.tmp_migration_codice = table.lacavagnetta_referenti.codanag</td>
					</tr>
					<tr>
						<td>table.suppliers_organizations dove suppliers.tmp_migration_codice lacavagnetta_referenti.codfornitore</td>
					</tr>				</table>				<h3>Nota</h3>				<ul>					<li>						in joomla <b>#__user_usergroup_map</b> associa il referente al gruppo referenti (".Configure::read('group_id_referent').")					</li>					<li>
						se in table.suppliers_organizations_referents l'associazione produttore/utente esiste già la <span style='color:red'>duplica</span>!
					</li>
				</ul>";
						
			if($tot['totCtrl']==0)				$introNote .= "<h3>Test di controllo</h3>Tabella ".Configure::read('DB.prefix')."suppliers_organizations_referents non ancora popolata.";			else				$introNote .= "<h2>Test di controllo: tabella ".Configure::read('DB.prefix')."suppliers_organizations_referents già popolata: trovati ".$tot['totCtrl']." records!</h2>";							
			$this->set('introNote',$introNote);
			$this->set('introTitle',"tutti i referenti dei produttori associati all'organizzazione");
			
			$this->render('admin_migration_eg3_intro');
		}
	}
	
	public function admin_migration_eg3_articles() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->DatabaseDate->executeMigrationEg3Articles($this->user);
			$this->render('admin_migration_eg3');
		}
		else {
			/*			 * ctrl se la tabella e' gia' popolata			*/			$sql = "SELECT count(id) as totCtrl					FROM ".Configure::read('DB.prefix')."articles 					WHERE						organization_id = ".(int)$this->user->organization['Organization']['id'];			$tot = current(current($this->DatabaseDate->query($sql)));			
			$introNote = "				<table cellpadding='0' cellspacing='0'>					<tr>						<th style='width:50%;'>Da</th>						<th>a</th>					</tr>					<tr>						<td>table.lacavagnetta_articoli</td>						<td rowspan='3'>table.articles
							<ul> 
								<li>con i dati dell'articolo,</li>
								<li>il produttore</li>
								<li>la categoria</li>
							</ul>
						</td>					</tr>					<tr>						<td>table.suppliers_organizations dove suppliers.tmp_migration_codice = lacavagnetta_articoli.centrale</td>					</tr>					<tr>						<td>table.categories_articles dove categories_articles.tmp_migration_codice = lacavagnetta_articoli.catmerce</td>					</tr>				</table>				<h3>Nota</h3>				<ul>					<li>						se in table.articles l'articolo esiste già la <span style='color:red'>duplica</span>!					</li>				</ul>";
								if($tot['totCtrl']==0)				$introNote .= "<h3>Test di controllo</h3>Tabella ".Configure::read('DB.prefix')."articles non ancora popolata.";			else				$introNote .= "<h2>Test di controllo: tabella ".Configure::read('DB.prefix')."articles già popolata: trovati ".$tot['totCtrl']." records!</h2>";						
			$this->set('introNote',$introNote);
			$this->set('introTitle',"articoli");
			
			$this->render('admin_migration_eg3_intro');
		}
	}
	
	public function admin_migration_eg3_drop_field() {
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->DatabaseDate->executeMigrationEg3DropField();
			$this->render('admin_migration_eg3');
		}
		else {
			$introNote = "Elimino i campi tmp_migration_codice<br /><br />
					Da 
					<ul>
					<li>1) categories_articles, serviva per la migrazione degli articoli</li>
					<li>2) #__users, serviva per la migrazione dei referenti</li>
					<li>3) suppliers, serviva per la migrazione dei referenti</li>
					</ul>
					<br />
					cancellare il <b>MODEL</b> in ".Configure::read('App.component.base')."/tmp/cache/models/ delle tabelle 
					<ul>
					<li>- categories_articles</li>
					<li>- suppliers</li>
					</ul>							
					<p>					
					";
			$this->set('introNote',$introNote);
			$this->set('introTitle',"drop campi database");
			
			$this->render('admin_migration_eg3_intro');
		}
	}
}