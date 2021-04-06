<?php
App::uses('AppController', 'Controller');

class OrganizationsPaysController extends AppController {
	
	public function beforeFilter() {
		parent::beforeFilter();

		/* ctrl ACL */
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
		/* ctrl ACL */
	}

	public function admin_index() {
	
		$year = date('Y');
	
		App::import('Model', 'Organization');
        $Organization = new Organization;
		
		$options = [];
        $options['conditions'] = ['Organization.type' => 'GAS', 'Organization.stato' => 'Y'];
        $options['order'] = ['Organization.name'];		
		$options['recursive'] = -1;
	
        $results = $Organization->find('all', $options);
		
		/*
		 *  prima riga ha i calcoli futuri
		 */ 
		 $resultsNew = [];
		 foreach($results as $numResult => $result) {
			$organization_id = $result['Organization']['id'];			
			
			$tot_users = $this->OrganizationsPay->totUsers($organization_id);
			if($organization_id==37)
				$tot_users = 24;
			
			/*
			 * tolgo info@nomegas.portalgas.it
			 * eventuale dispensa@nomegas.portalgas.it
			 */
			$paramsConfig = json_decode($result['Organization']['paramsConfig'], true); 
			if($paramsConfig['hasStoreroom']=='Y') 
				$users_default = 2;
			else
				$users_default = 1;
			$tot_users = ($tot_users - $users_default);
			
			$tot_orders = $this->OrganizationsPay->totOrders($organization_id, $year);
			
			$tot_suppliers_organizations = $this->OrganizationsPay->totSuppliersOrganizations($organization_id);
			
			$tot_articles = $this->OrganizationsPay->totArticlesOrganizations($organization_id);
			 
			$resultsNew[$numResult] = $result;
			$resultsNew[$numResult]['OrganizationsPay']['id'] = 0;
			$resultsNew[$numResult]['OrganizationsPay']['year'] = $year;
			$resultsNew[$numResult]['OrganizationsPay']['tot_users'] = $tot_users;
			$resultsNew[$numResult]['OrganizationsPay']['users_default'] = $users_default;
			$resultsNew[$numResult]['OrganizationsPay']['tot_orders'] = $tot_orders;
			$resultsNew[$numResult]['OrganizationsPay']['tot_suppliers_organizations'] = $tot_suppliers_organizations;
			$resultsNew[$numResult]['OrganizationsPay']['tot_articles'] = $tot_articles;
			$resultsNew[$numResult]['OrganizationsPay']['importo'] = 0;
			
			if(!empty($result['Organization']['paramsPay'])) {
					$paramsPay = json_decode($result['Organization']['paramsPay'], true);
					$resultsNew[$numResult]['Organization'] += $paramsPay;	  
			}
				
			$importoResults = $this->OrganizationsPay->getImporto($organization_id, $year, $tot_users);
			$resultsNew[$numResult]['OrganizationsPay']['importo'] = $importoResults['importo'];
			$resultsNew[$numResult]['OrganizationsPay']['importo_e'] = $importoResults['importo_e'];
			$resultsNew[$numResult]['OrganizationsPay']['importo_nota'] = $importoResults['importo_nota'];			
		 }

		 /*
		  * righe successive con i pagamenti effettuati
		  */
		 $results = []; 
				
		$options = [];
		$options['order'] = ['OrganizationsPay.year','Organization.name'];
		$options['recursive'] = 1;
		 
		$results = $this->OrganizationsPay->find('all', $options);
		foreach($results as $result) {
			$numResult++;

			$resultsNew[$numResult] = $result;
			
			if(!empty($result['Organization']['paramsPay'])) {
	    		$paramsPay = json_decode($result['Organization']['paramsPay'], true);
	    		$resultsNew[$numResult]['Organization'] += $paramsPay;	  
		    }	

			$importoResults = $this->OrganizationsPay->getImporto($result['Organization']['id'], $result['OrganizationsPay']['year'], $result['OrganizationsPay']['tot_users']);
			$resultsNew[$numResult]['OrganizationsPay']['importo'] = $importoResults['importo'];
			$resultsNew[$numResult]['OrganizationsPay']['importo_e'] = $importoResults['importo_e'];
			$resultsNew[$numResult]['OrganizationsPay']['importo_nota'] = $importoResults['importo_nota'];				
		}
		
		self::d($resultsNew, false);
		
        $this->set('results', $resultsNew);
	}
	
	public function admin_invoice_create_form() {
	
		App::import('Model', 'Organization');
        $Organization = new Organization;
		
		$year = date('Y');
		
		$options = [];
        $options['conditions'] = ['Organization.type' => 'GAS', 'Organization.stato' => 'Y'];
        $options['order'] = ['Organization.name'];
		$options['recursive'] = -1;
	
        $organizations = $Organization->find('all', $options);
		
		$organizationsNew = [];
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
			"Il sottoscritto Marco Siviero nato a Torino (TO), il 14/06/1965 codice fiscale SVRMRC65H14L219S residente a Torino in via Angelo Sismonda 10/4<br /><br /> ".
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
			
			$options = [];
			$options['conditions'] = ['Organization.id' => $organization_id];
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
			/*
			 * prendo solo l'id perche' lo gestisco con i msgText 
			$fileData['fileName'] = strtolower(str_replace(" ","_",$organizationResults['Organization']['id'].'_'.$organizationResults['Organization']['name']));
			*/
			$fileData['fileName'] = $organizationResults['Organization']['id'];
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