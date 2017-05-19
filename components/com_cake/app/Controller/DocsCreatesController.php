<?php
App::uses('AppController', 'Controller');

class DocsCreatesController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
		if(!$this->isManager()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */
	}

    public function admin_index() {
	    $this->paginate = array('conditions' => array('DocsCreate.organization_id' => $this->user->organization['Organization']['id']),
					    		'recursive' => 1,
								'limit' => 100,
								'order' => array('DocsCreate.created' => 'asc'));
	    $results = $this->paginate('DocsCreate');
		$this->set('results', $results);
	}
	
	public function admin_add() {
	
		$debug = false;
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$this->request->data['DocsCreate']['organization_id'] = $this->user->organization['Organization']['id'];
			
			$this->DocsCreate->create();
			if ($this->DocsCreate->save($this->request->data)) {
				
				$doc_id = $this->DocsCreate->getLastInsertId();
				
				/*
				 * inserisco gli Users associati al documento
				 */
				if(!empty($this->request->data['DocsCreate']['user_ids'])) {
					
					App::import('Model', 'DocsCreateUser ');
					$DocsCreateUser  = new DocsCreateUser ;
					
					$DocsCreateUser ->insert($this->user, $doc_id, $this->request->data['DocsCreate']['user_ids'], $debug);
				}  
		
				$this->Session->setFlash(__('The docsCreates has been saved', true));
				if(!$debug) $this->myRedirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The docsCreates could not be saved. Please, try again.', true));
			}
		} // end if ($this->request->is('post') || $this->request->is('put')) 
			
		
		App::import('Model', 'User');
		$User = new User;

		$conditions = array('UserGroupMap.group_id' => Configure::read('group_id_user'));
		$users = $User->getUsersList($this->user, $conditions, Configure::read('orderUser'), array('name', 'email'));
		$this->set('users',$users);
	}
	
	public function admin_invoice_create_form() {
	
		App::import('Model', 'Organization');
        $Organization = new Organization;
		
		$year = date('Y');
		
		$options = array();
		$options['order'] = array('Organization.name');
		$options['recursive'] = -1;
	
        $organizations = $Organization->find('all', $options);
		
		$organizationsNew = array();
        foreach($organizations as $organization) {
        	$organizationsNew[$organization['Organization']['id']] = $organization['Organization']['name'].' ('.$organization['Organization']['id'].')';
        }		
		/*
  		echo "<pre>";
		print_r($organizationsNew);
        echo "</pre>";
		*/
        $this->set('organizations', $organizationsNew);
		
		$type_pay = ClassRegistry::init('OrganizationsPay')->enumOptions('type_pay');
		
		$title_RICEVUTA = "Nota di pagamento";
		$title_RITENUTA = "RICEVUTA per PRESTAZIONE di LAVORO AUTONOMO OCCASIONALE";
		$this->request->data['OrganizationsPay']['intro'] = 
			"Il sottoscritto Francesco Actis Grosso nato a Torino (TO), il 02/11/1973 codice fiscale CTSFNC73S02L219I residente a Torino in via Sant'Anselmo, 28<br /><br /> ".
			"Il sottoscritto Marco Siviero nato a Torino (TO), il 14/06/1965 codice fiscale SVRMRC65H14L219S residente a Torino in via San Donato, 55b<br /><br /> ".
			"dichiara di ricevere il pagamento del compenso lordo di ..... &euro; relativo alla seguente prestazione: assistenza software fornita per la gestione del portale PG ".date('Y').", per un totale di giorni lavorativi inferiore a 30.<br /><br />";
			
		$text_RICEVUTA = "";
		$text_RITENUTA = 
		    "Tale importo ha natura di compenso per lavoro autonomo occasionale e deriva dal seguente conteggio:<br /><br />".
			str_repeat("&nbsp;", 67)."Compenso lordo".str_repeat("&nbsp;", 18)."Euro ....................<br />".
			str_repeat("&nbsp;", 67)."Ritenuta d’acconto 20%".str_repeat("&nbsp;", 6)."Euro ....................<br />".
			str_repeat("&nbsp;", 67)."Netto da pagare".str_repeat("&nbsp;", 19)."Euro ....................<br />";
		$this->request->data['OrganizationsPay']['nota'] = "La presente prestazione di lavoro autonomo occasionale è esclusa dal campo di applicazione IVA ai sensi degli art. 1 del D.P.R. 633/1972.<br /><br />Marca da bollo sull’originale € 2,00 se l’importo netto è superiore a Euro 77,47.";
		$this->request->data['OrganizationsPay']['nota2'] = "Dichiaro di aver percepito fino ad oggi meno di 5.000 &euro;";
		
	   $this->set(compact('type_pay', 'title_RICEVUTA', 'title_RITENUTA', 'text_RICEVUTA', 'text_RITENUTA'));
	}	
	
	public function admin_invoice_create_pdf() {

		if ($this->request->is('post') || $this->request->is('put')) {
			
			Configure::write('debug', 0);
			
			/*
			echo "<pre>";
			print_r($this->request->data);
			echo "<pre>";
			*/

			App::import('Model', 'Organization');
			$Organization = new Organization;
		
			$organization_id = $this->request->data['OrganizationsPay']['organization_id'];
			$title = $this->request->data['OrganizationsPay']['title'];
			$intro = $this->request->data['OrganizationsPay']['intro'];
			$text = $this->request->data['OrganizationsPay']['text'];
			$nota = $this->request->data['OrganizationsPay']['nota'];
			$nota2 = $this->request->data['OrganizationsPay']['nota2'];
			
			$this->set('title', $title);
			$this->set('intro', $intro);
			$this->set('text', $text);
			$this->set('nota', $nota);
			$this->set('nota2', $nota2);
			
			$options = array();
			$options['conditions'] = array('Organization.id' => $organization_id);
			$options['recursive'] = -1;
		
			$organizationResults = $Organization->find('first', $options);
			if(!empty($organizationResults['Organization']['paramsPay'])) {
	    		$paramsPay = json_decode($organizationResults['Organization']['paramsPay'], true);
	    		$organizationResults['Organization'] += $paramsPay;	  
		    }				
			$this->set('organizationResults', $organizationResults);
			/*
			echo "<pre>";
			print_r($organizationResults);
			echo "<pre>";
			*/
			$fileData['fileTitle'] = $organizationResults['Organization']['name'];
			$fileData['fileName'] = strtolower(str_replace(" ","_",$organizationResults['Organization']['id'].'_'.$organizationResults['Organization']['name']));
			$this->set('fileData', $fileData);
			
			/*
			 * se desidero il logo
			if(empty($this->user->organization)){
				// non ho selezionato alcun GAS
				$this->user->organization['Organization']['id']=1;
			}
			$this->set('organization', $this->user->organization);
			*/
			$this->layout = 'pdf';			
		}
	}	
}