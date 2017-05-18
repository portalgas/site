<?php

App::uses('AppController', 'Controller');
jimport('joomla.application.categories');

class SuppliersController extends AppController {

    private $jCategories = Array();

    public function beforeFilter() {
        parent::beforeFilter();

        /* ctrl ACL */
        if (in_array($this->action, array('admin_index', 'admin_add', 'admin_delete'))) {
            if (!$this->isRoot() && !$this->isRootSupplier()) {
                $this->Session->setFlash(__('msg_not_permission'));
                $this->myRedirect(Configure::read('routes_msg_stop'));
            }
        }
        /* ctrl ACL */
    }

    /*
     * mostra tutti i produttori a quali gas e' associato
     */

    public function admin_index_relations() {
        $FilterSupplierOrganizationId = null;
        $FilterSupplierName = null;
        $FilterSupplierCategoryId = null;
        $conditions = array();
        $SqlLimit = 20;

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'OrganizationId')) {
            $FilterSupplierOrganizationId = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'OrganizationId');
            if (!empty($FilterSupplierOrganizationId))
                $conditions[] = array('SuppliersOrganization.organization_id' => $FilterSupplierOrganizationId);
        }

        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterSupplierName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            if (!empty($FilterSupplierName))
                $conditions[] = array('Supplier.name LIKE ' => '%' . $FilterSupplierName . '%');
        }

        $conditions[] = array('Supplier.stato' => 'Y');

        if ($this->user->organization['Organization']['hasFieldSupplierCategoryId'] == 'Y') {
            if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'CategoryId')) {
                $FilterSupplierCategoryId = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'CategoryId');
                $conditions[] = array('Supplier.category_supplier_id' => $FilterSupplierCategoryId);
            }
        }

        /* filtro */
        $this->set('FilterSupplierOrganizationId', $FilterSupplierOrganizationId);
        $this->set('FilterSupplierName', $FilterSupplierName);
        $this->set('FilterSupplierCategoryId', $FilterSupplierCategoryId);

		App::import('Model', 'SuppliersVote');
							
        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = array();
        $options['conditions'] = array('Organization.stato' => 'Y');
        $options['order'] = array('Organization.name');
        $organizations = $Organization->find('list', $options);
        $this->set(compact('organizations'));

        App::import('Model', 'CategoriesSupplier');
        $CategoriesSupplier = new CategoriesSupplier;

        $options = array();
        $options['order'] = array('CategoriesSupplier.name');
        $categories = $CategoriesSupplier->find('list', $options);
        $this->set(compact('categories'));

        /*
         * se non filtro per organizzazione 
         */
        if (empty($FilterSupplierOrganizationId)) {
            $this->Supplier->recursive = 1;
            $this->paginate = array('conditions' => array($conditions), 'order' => 'Supplier.name', 'limit' => $SqlLimit);
            $results = $this->paginate('Supplier');


            /*
             * estraggo il nome dell'organizzazione per ogni fornitore
             * solo se non ho filtrato per organization
             */
            foreach ($results as $i => $result) {
                foreach ($result['SuppliersOrganization'] as $ii => $suppliersOrganization) {

                    $Organization = new Organization;

                    $options = array();
                    $options['conditions'] = array('Organization.id' => $suppliersOrganization['organization_id']);
                    $options['recursive'] = -1;
                    $options['fields'] = array('name', 'img1');
                    $organizationResults = $Organization->find('first', $options);
                    $results[$i]['SuppliersOrganization'][$ii]['Organization'] = $organizationResults['Organization'];	

					/*
					 * per ogni produttore faccio la media dei voti
					 */	
                    $options = array();
                    $options['conditions'] = array('SuppliersVote.organization_id' => $suppliersOrganization['organization_id'],
												   'SuppliersVote.supplier_id' => $result['Supplier']['id']);
                    $options['recursive'] = -1;
					$SuppliersVote = new SuppliersVote;
					
					$suppliersVoteResults = $SuppliersVote->find('first', $options);
					$results[$i]['SuppliersOrganization'][$ii]['Organization']['SuppliersVote'] = $suppliersVoteResults['SuppliersVote'];										
                }
            } // foreach ($results as $i  => $result)					
        } else {
            $this->Supplier->SuppliersOrganization->recursive = 0;
            $this->paginate = array('conditions' => array($conditions), 'order' => 'Supplier.name', 'limit' => $SqlLimit);

            $results = $this->paginate('SuppliersOrganization');
			

			/*
			 * per ogni produttore faccio la media dei voti
			 */
			foreach ($results as $numResult => $result) {			 
				$options = array();
				$options['conditions'] = array('SuppliersVote.organization_id' => $result['SuppliersOrganization']['organization_id'],
											   'SuppliersVote.supplier_id' => $result['SuppliersOrganization']['supplier_id']);
				$options['recursive'] = -1;
				$SuppliersVote = new SuppliersVote;
			
				$suppliersVoteResults = $SuppliersVote->find('first', $options);
				$results[$numResult]['SuppliersVote'] = $suppliersVoteResults['SuppliersVote'];
			}
        }
		/*
		echo "<pre>";
		print_r($results);
		echo "</pre>";
		*/
        $this->set('results', $results);
        $this->set('SqlLimit', $SqlLimit);
    }

    public function admin_index() {
        $FilterSupplierOrganizationId = null;
        $FilterSupplierStato = null;
        $FilterSupplierName = null;
        $FilterSupplierCategoryId = null;
        $conditions = array();
        $SqlLimit = 20;

        /* recupero dati dalla Session gestita in appController::beforeFilter */
        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'OrganizationId')) {
            $FilterSupplierOrganizationId = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'OrganizationId');
            if (!empty($FilterSupplierOrganizationId))
                $conditions[] = array('SuppliersOrganization.organization_id' => $FilterSupplierOrganizationId);
        }

        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Name')) {
            $FilterSupplierName = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Name');
            if (!empty($FilterSupplierName))
                $conditions[] = array('Supplier.name LIKE ' => '%' . $FilterSupplierName . '%');
        }

        if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'Stato')) {
            $FilterSupplierStato = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'Stato');
            if ($FilterSupplierStato != 'ALL')
                $conditions[] = array('Supplier.stato' => $FilterSupplierStato);
        }

        /*
         * di default prendo quelli Temporanei
         */
        if (empty($FilterSupplierStato)) {
            $FilterSupplierStato = 'T';
            $conditions[] = array('Supplier.stato' => 'T');
        }

        if ($this->user->organization['Organization']['hasFieldSupplierCategoryId'] == 'Y') {
            if ($this->Session->check(Configure::read('Filter.prefix') . $this->modelClass . 'CategoryId')) {
                $FilterSupplierCategoryId = $this->Session->read(Configure::read('Filter.prefix') . $this->modelClass . 'CategoryId');
                $conditions[] = array('Supplier.category_supplier_id' => $FilterSupplierCategoryId);
            }
        }

        /* filtro */
        $this->set('FilterSupplierOrganizationId', $FilterSupplierOrganizationId);
        $this->set('FilterSupplierStato', $FilterSupplierStato);
        $this->set('FilterSupplierName', $FilterSupplierName);
        $this->set('FilterSupplierCategoryId', $FilterSupplierCategoryId);

		App::import('Model', 'SuppliersVote');
	
        App::import('Model', 'Organization');
        $Organization = new Organization;

        $options = array();
        $options['conditions'] = array('Organization.stato' => 'Y');
        $options['order'] = array('Organization.name');
        $organizations = $Organization->find('list', $options);
        $this->set(compact('organizations'));

        App::import('Model', 'CategoriesSupplier');
        $CategoriesSupplier = new CategoriesSupplier;

        $options = array();
        $options['order'] = array('CategoriesSupplier.name');
        $categories = $CategoriesSupplier->find('list', $options);
        $this->set(compact('categories'));

        /*
         * se non filtro per organizzazione 
         */
        if (empty($FilterSupplierOrganizationId)) {
            $this->Supplier->recursive = 1;
            $this->paginate = array('conditions' => array($conditions), 'order' => 'Supplier.name', 'limit' => $SqlLimit);
            $results = $this->paginate('Supplier');


            /*
             * estraggo il nome dell'organizzazione per ogni fornitore
             * solo se non ho filtrato per organization
             */
            foreach ($results as $i => $result) {
                foreach ($result['SuppliersOrganization'] as $ii => $suppliersOrganization) {

                    $Organization = new Organization;

                    $options = array();
                    $options['conditions'] = array('Organization.id' => $suppliersOrganization['organization_id']);
                    $options['recursive'] = -1;
                    $options['fields'] = array('name', 'img1');
                    $organizationResults = $Organization->find('first', $options);
                    $results[$i]['SuppliersOrganization'][$ii]['Organization'] = $organizationResults['Organization'];
                    
					/*
					 * per ogni produttore faccio la media dei voti
					 */	
                    $options = array();
                    $options['conditions'] = array('SuppliersVote.organization_id' => $suppliersOrganization['organization_id'],
												   'SuppliersVote.supplier_id' => $result['Supplier']['id']);
                    $options['recursive'] = -1;
					$SuppliersVote = new SuppliersVote;
					
					$suppliersVoteResults = $SuppliersVote->find('first', $options);
					$results[$i]['SuppliersOrganization'][$ii]['Organization']['SuppliersVote'] = $suppliersVoteResults['SuppliersVote'];	
                    
                }
            } // foreach ($results as $i  => $result)					
        } else {
            $this->Supplier->SuppliersOrganization->recursive = 0;
            $this->paginate = array('conditions' => array($conditions), 'order' => 'Supplier.name', 'limit' => $SqlLimit);

            $results = $this->paginate('SuppliersOrganization');
			
			/*
			 * per ogni produttore faccio la media dei voti
			 */
			foreach ($results as $numResult => $result) {			 
				$options = array();
				$options['conditions'] = array('SuppliersVote.organization_id' => $result['SuppliersOrganization']['organization_id'],
											   'SuppliersVote.supplier_id' => $result['SuppliersOrganization']['supplier_id']);
				$options['recursive'] = -1;
				$SuppliersVote = new SuppliersVote;
			
				$suppliersVoteResults = $SuppliersVote->find('first', $options);
				$results[$numResult]['SuppliersVote'] = $suppliersVoteResults['SuppliersVote'];
			}			
        }

        $this->set('results', $results);
        $this->set('SqlLimit', $SqlLimit);
        $this->set('isRoot', $this->isRoot()); // per accedere alla modifica dell'articolo

        /*
         * parametri da passare eventualmente a admin_edit
         */
        $sort = '';
        $direction = '';
        $page = 0;
        if (!empty($this->request->params['named']['sort']))
            $sort = $this->request->params['named']['sort'];
        if (!empty($this->request->params['named']['direction']))
            $direction = $this->request->params['named']['direction'];
        if (!empty($this->request->params['named']['named']))
            $page = $this->request->params['named']['named'];
        $this->set('sort', $sort);
        $this->set('direction', $direction);
        $this->set('page', $page);
        $stato = array('ALL' => 'Tutti', 'Y' => __('StatoY'), 'N' => __('StatoN'), 'T' => __('Temporary'), 'PG' => __('Pagina'));
        $this->set(compact('stato'));
    }

    public function admin_add() {
        if ($this->request->is('post') || $this->request->is('put')) {

            $this->Supplier->create();
            if ($this->Supplier->save($this->request->data)) {

                $id = $this->Supplier->getLastInsertId();
                $this->request->data['Supplier']['id'] = $id;

                /*
                 * 	$img1 = array(
                 * 		'name' => 'immagine.jpg',
                 * 		'type' => 'image/jpeg',
                 * 		'tmp_name' => /tmp/phpsNYCIB',
                 * 		'error' => 0,
                 * 		'size' => 41737,
                 * 	);
                 *
                 * UPLOAD_ERR_OK (0): Non vi sono errori, l’upload e' stato eseguito con successo;
                 * UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
                 * UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
                 * UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
                 * UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
                 * UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
                 */
                if (!empty($this->request->data['Document']['img1']['name'])) {

                    $img1 = $this->request->data['Document']['img1'];
                    if ($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name'])) {

                        $path_upload = Configure::read('App.root') . Configure::read('App.img.upload.content') . DS;
                        $ext = strtolower(pathinfo($img1['name'], PATHINFO_EXTENSION));

                        /*
                         * solo se ho scelto l'articolo di joomla da associare l'img e' rinominata con j_content_id.ext
                         * se no diventa Configure::read('App.prefix.upload.content')...
                         */
                        if (!empty($this->request->data['Supplier']['j_content_id']))
                            $imgNewName = $this->request->data['Supplier']['j_content_id'] . '.' . $ext;
                        else
                            $imgNewName = Configure::read('App.prefix.upload.content') . $id . '.' . $ext;

                        if (move_uploaded_file($img1['tmp_name'], $path_upload . $imgNewName)) {
                            $this->request->data['Supplier']['img1'] = $imgNewName;
                            $this->Supplier->save($this->request->data);
                        } else
                            $this->Session->setFlash(__('Error upload move_uploaded_file ') . $img1['error']);
                    } else
                        $this->Session->setFlash(__('Error upload is_uploaded_file ') . $img1['error']);
                } // end if(!empty($this->request->data['Document']['img1']['name']))

                $this->Session->setFlash(__('The supplier has been saved'));
                $this->myRedirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
            }
        }

        App::import('Model', 'CategoriesSupplier');
        $CategoriesSupplier = new CategoriesSupplier;

        $options = array();
        $options['order'] = array('CategoriesSupplier.name');
        $categories = $CategoriesSupplier->find('list', $options);
        $this->set(compact('categories'));

        $stato = ClassRegistry::init('Supplier')->enumOptions('stato');
        $this->set(compact('stato'));
        $this->set('modalArticle', $this->__drawJModalArticle(0));
    }

    public function admin_edit($id = null) {

        $this->Supplier->id = $id;
        if (!$this->Supplier->exists()) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        /*
         * parametri di ricerca da ripassare a admin_index
         */
        $sort = '';
        $direction = '';
        $page = 0;
        if (!empty($this->request->params['named']['sort']))
            $sort = $this->request->params['named']['sort'];
        else
        if (!empty($this->request->data['Supplier']['sort']))
            $sort = $this->request->data['Supplier']['sort'];

        if (!empty($this->request->params['named']['direction']))
            $direction = $this->request->params['named']['direction'];
        else
        if (!empty($this->request->data['Supplier']['direction']))
            $direction = $this->request->data['Supplier']['direction'];

        if (!empty($this->request->params['named']['page']))
            $page = $this->request->params['named']['page'];
        else
        if (!empty($this->request->data['Supplier']['page']))
            $page = $this->request->data['Supplier']['page'];
        $this->set('sort', $sort);
        $this->set('direction', $direction);
        $this->set('page', $page);

        if ($this->request->is('post') || $this->request->is('put')) {

            $this->Supplier->create();


            /*
             * 	$img1 = array(
             * 		'name' => 'immagine.jpg',
             * 		'type' => 'image/jpeg',
             * 		'tmp_name' => /tmp/phpsNYCIB',
             * 		'error' => 0,
             * 		'size' => 41737,
             * 	);
             *
             * UPLOAD_ERR_OK (0): Non vi sono errori, l’upload e' stato eseguito con successo;
             * UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
             * UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
             * UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
             * UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
             * UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
             */
            if (!empty($this->request->data['Document']['img1']['name'])) {

                $img1 = $this->request->data['Document']['img1'];
                if ($img1['error'] == UPLOAD_ERR_OK && is_uploaded_file($img1['tmp_name'])) {

                    $path_upload = Configure::read('App.root') . Configure::read('App.img.upload.content') . DS;
                    $ext = pathinfo($img1['name'], PATHINFO_EXTENSION);

                    /*
                     * solo se ho scelto l'articolo di joomla da associare l'img e' rinominata con j_content_id.ext
                     * se no diventa Configure::read('App.prefix.upload.content')...
                     */
                    if (!empty($this->request->data['Supplier']['j_content_id']))
                        $imgNewName = $this->request->data['Supplier']['j_content_id'] . '.' . $ext;
                    else
                        $imgNewName = Configure::read('App.prefix.upload.content') . $id . '.' . $ext;

                    if (move_uploaded_file($img1['tmp_name'], $path_upload . $imgNewName)) {
                        $this->request->data['Supplier']['img1'] = $imgNewName;
                    } else
                        $this->Session->setFlash(__('Error upload move_uploaded_file ') . $img1['error']);
                } else
                    $this->Session->setFlash(__('Error upload is_uploaded_file ') . $img1['error']);
            } // end if(!empty($this->request->data['Document']['img1']['name'])) 

            if (!empty($this->request->data['Supplier']['www']))
                $this->request->data['Supplier']['www'] = $this->traslateWww($this->request->data['Supplier']['www']);

            if ($this->Supplier->save($this->request->data)) {

                $this->request->data = $this->Supplier->read(0, null, $id);

                /*
                 * Aggiorno (name, category_supplier_id) di eventuali SuppliersOrganization
                 * */
                $sql = "select id
						from " . Configure::read('DB.prefix') . "suppliers_organizations as SuppliersOrganization  
						where supplier_id = " . (int) $id;
                //echo '<br />'.$sql;
                $SuppliersOrganizations = $this->Supplier->query($sql);
                if (!empty($SuppliersOrganizations)) {
                    foreach ($SuppliersOrganizations as $suppliersOrganization) {
                        $sql = "UPDATE
								 	" . Configure::read('DB.prefix') . "suppliers_organizations 
								SET  
									name = '" . addslashes($this->request->data['Supplier']['name']) . "',
									category_supplier_id = " . $this->request->data['Supplier']['category_supplier_id'] . "
								WHERE id = " . (int) $suppliersOrganization['SuppliersOrganization']['id'];
                        //echo '<br />'.$sql;
                        $result = $this->Supplier->query($sql);
                    }
                }

                $this->Session->setFlash(__('The supplier has been saved and supplierOrganization'));
                $this->myRedirect(Configure::read('App.server') . '/administrator/index.php?option=com_cake&controller=Suppliers&action=index&sort:' . $sort . '&direction:' . $direction . '&page:' . $page);
            } else {
                $this->Session->setFlash(__('The supplier could not be saved. Please, try again.'));
            }
        } else {
            $options = array();
            $options['conditions'] = array('Supplier.id' => $id);
            $options['recursive'] = 1;
            $results = $this->Supplier->find('first', $options);

            /*
             * estraggo il nome dell'organizzazione per ogni fornitore
             * solo se non ho filtrato per organization
             */
            foreach ($results['SuppliersOrganization'] as $ii => $suppliersOrganization) {

                App::import('Model', 'Organization');
                $Organization = new Organization;

                $options = array();
                $options['conditions'] = array('Organization.id' => $suppliersOrganization['organization_id']);
                $options['recursive'] = -1;
                $options['fields'] = array('name', 'descrizione', 'mail', 'www', 'www2');
                $organizationResults = $Organization->find('first', $options);
                $results['SuppliersOrganization'][$ii]['Organization'] = $organizationResults['Organization'];
            }
            $this->request->data = $results;

            App::import('Model', 'CategoriesSupplier');
            $CategoriesSupplier = new CategoriesSupplier;

            $options = array();
            $options['order'] = array('CategoriesSupplier.name');
            $categories = $CategoriesSupplier->find('list', $options);
            $this->set(compact('categories'));

            $stato = ClassRegistry::init('Supplier')->enumOptions('stato');
            $this->set(compact('stato'));

            /* parametri per joomla */
            $this->set('modalArticle', $this->__drawJModalArticle($this->request->data['Supplier']['j_content_id']));
        }
    }

    /*
     * suppliers_Trigger
     * 		suppliers_organizations
     */

    public function admin_delete($id = null) {

        $this->Supplier->id = $id;
        if (!$this->Supplier->exists()) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->Supplier->delete())
                $this->Session->setFlash(__('Delete Supplier'));
            else
                $this->Session->setFlash(__('Supplier was not deleted'));
            $this->myRedirect(array('action' => 'index'));
        }

        $options = array();
        $options['conditions'] = array('Supplier.id' => $id);
        $options['recursive'] = 1;
        $results = $this->Supplier->find('first', $options);
        $this->set(compact('results'));

		/*
		 * ctrl articoli inseriti x ogni GAS
		 */
		$totArticlesResults = array();	
		if(isset($results['SuppliersOrganization'])) {
			
			App::import('Model', 'Organization');
			$Organization = new Organization;
			
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			
			foreach($results['SuppliersOrganization'] as $numResult => $suppliersOrganization) {
				$tmp_user->organization['Organization']['id'] = $suppliersOrganization['organization_id'];
				$totArticlesResults[$numResult]['Articles'] = $SuppliersOrganization->getTotArticlesAttivi($tmp_user, $suppliersOrganization['id']);
				
				$options = array();
				$options['conditions'] = array('Organization.id' => $suppliersOrganization['organization_id']);
				$organizations = $Organization->find('first', $options);
		
				$totArticlesResults[$numResult]['Organization'] = $organizations['Organization'];
			}
			
		}
		/*
		echo "<pre>";
		print_r($totArticlesResults);
		echo "</pre>";
		*/
        $this->set(compact('totArticlesResults'));
		
    }

    // /var/www/portalgas/administrator/components/com_contact/models/fields/modal/article.php getInput();
    private function __drawJModalArticle($j_content_id) {
        $id = JSession::getFormToken();
        $COM_CONTENT_CHANGE_ARTICLE = "Seleziona o cambia articolo";
        $COM_CONTENT_CHANGE_ARTICLE_BUTTON = "Seleziona / Cambia";
        $COM_CONTENT_SELECT_AN_ARTICLE = "Seleziona un articolo";

        // Load the modal behavior script.
        JHtml::_('behavior.modal', 'a.modal');

        // Build the script.
        $script = array();
        $script[] = '	function jSelectArticle_' . $id . '(id, title, catid, object) {';
        $script[] = '		document.id("' . $id . '_id").value = id;';
        $script[] = '		document.id("' . $id . '_name").value = title;';
        $script[] = '		SqueezeBox.close();';
        $script[] = '	}';

        // Add the script to the document head.
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));


        // Setup variables for display.
        $html = array();
        $link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;function=jSelectArticle_' . $id;

        $db = JFactory::getDBO();
        $db->setQuery(
                'SELECT title' .
                ' FROM #__content' .
                ' WHERE id = ' . (int) $j_content_id
        );
        $title = $db->loadResult();

        if ($error = $db->getErrorMsg()) {
            JError::raiseWarning(500, $error);
        }

        if (empty($title)) {
            $title = $COM_CONTENT_SELECT_AN_ARTICLE;
        }
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        // The current user display field.
        $html[] = '  <input type="text" id="' . $id . '_name" value="' . $title . '" disabled="disabled" size="35" />';

        // The user select button.
        $html[] = '<div style="float: right;">';
        $html[] = '  <div class="blank">';
        $html[] = '	<a class="modal" title="' . $COM_CONTENT_CHANGE_ARTICLE . '"  href="' . $link . '&amp;' . JSession::getFormToken() . '=1" rel="{handler: \'iframe\', size: {x: 800, y: 450}}">' . $COM_CONTENT_CHANGE_ARTICLE_BUTTON . '</a>';
        $html[] = '  </div>';
        $html[] = '</div>';

        // The active article id field.
        if (0 == (int) $j_content_id) {
            $value = '';
        } else {
            $value = (int) $j_content_id;
        }

        // class='required' for client side validation
        $class = '';
        //if ($this->required) {
        //	$class = ' class="required modal-value"';
        //}

        $html[] = '<input type="hidden" id="' . $id . '_id"' . $class . ' name="data[Supplier][j_content_id]" value="' . $j_content_id . '" />';

        return implode("\n", $html);
    }

    /*
     * tab front-end produttori di un GAS
     */
    public function gmaps() {

        /*
         * setto organization_id preso dal template
         */        
        $tmp->user->organization['Organization']['id'] = $this->user->get('org_id');
        
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));

        $options = array();
        $options['conditions'] = array('SuppliersOrganization.organization_id' => $tmp->user->organization['Organization']['id'],
                                    'SuppliersOrganization.stato' => 'Y',
                                    'Supplier.stato' => 'Y');
        $options['order'] = array('Supplier.name');
        $options['recursive'] = 0;
        $results = $SuppliersOrganization->find('all', $options);

        $i = 0;
        $newResults = array();
        foreach ($results as $numResult => $result) {

            /*
             *  se il Cron non trova lat/lng perche' i dati non sono corretti, imposto a 0.0 se no non esegue i successivi
             */
            if (!empty($result['Supplier']['lat']) && $result['Supplier']['lat'] != Configure::read('LatLngNotFound') && !empty($result['Supplier']['lng']) && $result['Supplier']['lng'] != Configure::read('LatLngNotFound')) {
                $newResults[$i] = $result;

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

}
