<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class ProdGasSuppliersImportsController extends AppController {
		
	private $str_log = '';
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		if (!in_array($this->action, array('admin_organizations_articles', 'admin_updateField'))) {				
			if(!$this->isRoot()) {
				$this->Session->setFlash(__('msg_not_permission'));
				$this->myRedirect(Configure::read('routes_msg_stop'));
			}
		}
	}

	public function admin_index() { 

		$debug = false;
		
		App::import('Model', 'Organization');
		$Organization = new Organization;		
	    $results = $Organization->find('all', ['fields' => ['MAX(Organization.id) AS max_id']]);
	
	    $max_id = $results[0][0]['max_id'];
	    $max_id++;
	    $this->set('max_id', $max_id);
	   
		/*
		 * sql per settare la gestione del listino aricoli al produttore
		 */
		$sql_select = "SELECT * from ".Configure::read('DB.prefix')."suppliers_organizations WHERE organization_id = %s and id = %s;";
		$sql_update = "UPDATE ".Configure::read('DB.prefix')."suppliers_organizations SET owner_articles = 'SUPPLIER', owner_organization_id = %s, owner_supplier_organization_id = %s WHERE organization_id = %s and id = %s;";
		$sql_update_organization = "UPDATE ".Configure::read('DB.prefix')."organizations SET img1 = '%s' WHERE id = %s;";
		$sql_update_supplier = "UPDATE ".Configure::read('DB.prefix')."suppliers SET owner_organization_id = %s WHERE id = %s;";
		$sql_update_user = "UPDATE ".Configure::read('DB.portalPrefix')."users SET organization_id = %s WHERE supplier_id = %s;";
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$organization_id=0;
		if(isset($this->request->pass['organization_id']))
			$organization_id = $this->request->pass['organization_id'];
		
		$results = $this->ProdGasSuppliersImport->getProdGasSuppliers($this->user, $organization_id, 0, [], $debug);		

		/*
		 * per ogni il produttore, ctrl cho ogni suo GAS con suppliers_organizations.owner_articles = 'SUPPLIER' abbia owner_organization_id e owner_supplier_organization_id del produttore
		 */
		foreach($results as $numResult => $result) {
		
			/* 
			 * ctrl dati Organization
			 */
			if(empty($result['Organization']['img1'])) {
				$img1 = 'prodgas-'.$result['Organization']['id'].'.jpg';
				$results[$numResult]['Organization']['sql_update_organization'] = sprintf($sql_update_organization, $img1, $result['Organization']['id']);
			}
			else {
				$results[$numResult]['Organization']['sql_update_organization'] = 'OK';
				// echo '<br />'.sprintf($sql_update_organization, $result['Organization']['img1'], $result['Organization']['id']);
			}
			if(isset($result['Supplier']['Organization'])) {
				foreach($result['Supplier']['Organization'] as $numResult2 => $organization) {
					
					/* 
					 * ctrl directory img
					 */
					 $dir_images_path = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Organization']['id'];
					 if(is_dir($dir_images_path))
					 	$dir_images = true;
					 else {
					 	$dir_images = false;
					 }	
					 $results[$numResult]['Dir']['articles_path'] = $dir_images_path;
					 $results[$numResult]['Dir']['articles'] = $dir_images;

					 $dir_images_path = Configure::read('App.root').Configure::read('App.img.loghi').DS.$result['Organization']['id'];
					 if(is_dir($dir_images_path))
					 	$dir_images = true;
					 else {
					 	$dir_images = false;
					 }	
					 $results[$numResult]['Dir']['loghi_path'] = $dir_images_path;
					 $results[$numResult]['Dir']['loghi'] = $dir_images;
					 
					 $logo = Configure::read('App.root').Configure::read('App.img.loghi').DS.$result['Organization']['id'].DS.Configure::read('doc_export_logo'); // 150h50.png
					 if(file_exists($logo))
					 	$logo_images = true;
					 else {
					 	$logo_images = false;
					 }	
					 $results[$numResult]['Dir']['logo_images_path'] = $logo;
					 $results[$numResult]['Dir']['logo_images'] = $logo_images;
						 
					 
					 
					/* 
					 * ctrl dati Supplier.owner_organization_id
					 */
					if($result['Supplier']['Supplier']['owner_organization_id']==0) {
						$results[$numResult]['Supplier']['Supplier']['sql_update_supplier'] = sprintf($sql_update_supplier, $result['Organization']['id'], $result['Supplier']['Supplier']['id']);
					}
					else {
						// echo '<br />'.sprintf($sql_update_supplier, $result['Organization']['id'], $result['Supplier']['Supplier']['id']);;
					}	
					/*
					 * ctrl dati Users
					 */			
					if(!isset($result['User']) || empty($result['User'])) {
						$results[$numResult]['Users']['sql_update_user'] = sprintf($sql_update_user, $result['Organization']['id'], $result['Supplier']['Supplier']['id']);
					}
					else {
						// echo '<br />'.sprintf($sql_update_user, $result['Organization']['id'], $result['Supplier']['Supplier']['id']);
					}
					
					/*
					 * ctrl dati SuppliersOrganization
					 */ 
					$options = [];
					$options['conditions'] = ['SuppliersOrganization.organization_id' => $organization['SuppliersOrganization']['organization_id'],
										      'SuppliersOrganization.id' => $organization['SuppliersOrganization']['id'],
											  'SuppliersOrganization.owner_articles' => 'SUPPLIER', 
											  'SuppliersOrganization.owner_organization_id' => $result['Organization']['id'],
											  'SuppliersOrganization.owner_supplier_organization_id' => $result['Supplier']['SuppliersOrganization']['id']];
					$suppliersOrganizationCount = $SuppliersOrganization->find('count', $options); 
					if($suppliersOrganizationCount==1) {
						$results[$numResult]['Supplier']['Organization'][$numResult2]['code'] = "Y";	
						$results[$numResult]['Supplier']['Organization'][$numResult2]['msg'] = "Produttore allineato";						
						$results[$numResult]['Supplier']['Organization'][$numResult2]['sql'] = sprintf($sql_select, $organization['SuppliersOrganization']['organization_id'], $organization['SuppliersOrganization']['id']);
					} 
					else {
						if($organization['SuppliersOrganization']['owner_articles']=='SUPPLIER') {
							$results[$numResult]['Supplier']['Organization'][$numResult2]['code'] = "N";						
							$results[$numResult]['Supplier']['Organization'][$numResult2]['msg'] = "Produttore non allineato";
						}
						else {
							$results[$numResult]['Supplier']['Organization'][$numResult2]['code'] = "T";					
							$results[$numResult]['Supplier']['Organization'][$numResult2]['msg'] = "Produttore NON gestito dal produttore";
						}
						$results[$numResult]['Supplier']['Organization'][$numResult2]['sql'] = sprintf($sql_update, $result['Organization']['id'], $result['Supplier']['SuppliersOrganization']['id'], $organization['SuppliersOrganization']['organization_id'], $organization['SuppliersOrganization']['id']);
					}
						
				} // foreach($result['Supplier']['Organization'] as $numResult => $organization)
			} // end if(isset($result['Supplier']['Organization']))
		}
		
		$this->set(compact('results'));		
	}
	
	/*
	 * per ogni produttore paragono i listini di ogni GAS
	 * supplier_id = 0 se root
	 */
	public function admin_organizations_articles($supplier_id=0) { 

		if(empty($supplier_id)) {
			if(isset($this->user->supplier['Supplier'])) 
				$supplier_id = $this->user->supplier['Supplier'];
		}
		
		if(empty($supplier_id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
		$debug = false;
	
		
		$results = $this->ProdGasSuppliersImport->getOrganizationsArticles($this->user, $supplier_id, $debug);
						
		$this->set(compact('results'));	

		$this->set('isRoot', $this->isRoot());		
	}
		
	/*
	 * aggiorno il campo passato da admin_organizations_articles()
	 * supplier_id != 0 modifico articolo del produttore
	 * organization_id != 0 modifico articolo del gas
	 * id = nome campo - id campo
	 */
	public function admin_updateField($supplier_id, $organization_id, $id) {
		
		$esito = 'NO';
		
		$value = $this->request->data['value'];

		if(empty($id) || (empty($supplier_id) && empty($organization_id))) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
		
		list($name_field, $id) = explode("-", $id);	

		if (!empty($value)) {
			switch ($name_field) {
				case "prezzo":
				case "qta":
				case "importo":
					$value = $this->importoToDatabase($value);
				break;
				default:
					$value = addslashes($value);
				break;
			}
		} 
			
		if(!empty($organization_id)) {
			$sql = "UPDATE ".Configure::read('DB.prefix')."articles SET 
					$name_field = '".$value."', modified = '" . date('Y-m-d H:i:s') . "'
				WHERE id = ".(int) $id." AND organization_id = ".$organization_id;
		}
		else 
		if(!empty($supplier_id)) {
			$sql = "UPDATE ".Configure::read('DB.prefix')."prod_gas_articles SET 
					$name_field = '".$value."', modified = '" . date('Y-m-d H:i:s') . "'
				WHERE id = ".(int) $id." AND supplier_id = ".$supplier_id;			
		}
		self::d($sql, false);
		try {
			$results = $this->ProdGasSuppliersImport->query($sql);
			$esito = 'OK';
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
		} 
		
		$numRow = $supplier_id.'-'.$organization_id.'-'.$id; 
	
        if ($esito == 'NOCHANGE')
            $content_for_layout = '';
        else
            $content_for_layout = "<script type=\"text/javascript\">managementCart('" . $numRow . "','" . $esito . "',0,null);</script>";

        $this->set('content_for_layout', $content_for_layout);

        $this->layout = 'ajax';
        $this->render('/Layouts/ajax');	
	}
			
	public function admin_import() { 
	
		$debug = true;
		$debug_insert = false; // se false insert in Article e copy Article.img1

		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		if($debug) {
			echo "<h1>Modalita DEBUG: scrivo log</h1>";
			/*
			echo "<pre>";
			print_r($this->request->params['pass']);
			echo "</pre>";
			*/
		}
		if($debug_insert) 
			echo "<h1>Modalita DEBUG_INSERT: non scrivo sul database</h1>";
		else
			echo "<h1>Modalita DEBUG_INSERT: SCRIVO sul database</h1>";
		
		$prod_gas_supplier_id = $this->request->params['pass']['prod_gas_supplier_id'];
		$elabora = $this->request->params['pass']['elabora'];
		$this->set(compact('prod_gas_supplier_id', 'elabora'));
		
		if(!empty($prod_gas_supplier_id)) {
			
			App::import('Model', 'Organization');
			
			App::import('Model', 'User');
			$User = new User;
			
			App::import('Model', 'Article');
			$Article = new Article;

			App::import('Model', 'ProdGasArticle');

			/*
			 * dati produttore
			 */
			$options = array();
			$options['conditions'] = array('Supplier.id' =>  $prod_gas_supplier_id);
			$options['recursive'] = 1;
			$supplierResults = $Supplier->find('first', $options);		
			if(empty($supplierResults)) 
				self::x("Produttore ($prod_gas_supplier_id) non trovato!");
			
			/*
			 * ctrl Directory ProdGasArticle.img
			 */
			$path = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$prod_gas_supplier_id;
			$this->set(compact('path'));
				

			$folder_prod_gas_article = new Folder($path);
			if (is_null($folder_prod_gas_article->path)) {
				echo ("<h1 style=color:red;>Directory ProdGasArticle.img $path inesistente => la creo</h1>");
				$folder_prod_gas_article = new Folder($path, true, 0775);
				if (!$folder_prod_gas_article->path) { 
					echo ("<h1 style=color:red;>Error nella creazion della directory ProdGasArticle.img $path</h1>");
				}				
			}
			
			/*
			 * ctrl utenze
			 */
			$options = array();
			$options['conditions'] = array('User.organization_id' => 0, 
										   'User.supplier_id' => $prod_gas_supplier_id);
			$options['recursive'] =- 1;
			$userResults = $User->find('all', $options);	
			$this->set(compact('userResults'));
			
			/*
			 * GAS associati al produttore
			 */
			$supplier_organization_id = '';
			foreach($supplierResults['SuppliersOrganization'] as $numResult => $suppliersOrganizationResult) {
			
				$Organization = new Organization;
				
				$options = array();
				$options['conditions'] = array('Organization.id' =>  $suppliersOrganizationResult['organization_id']);
				$options['recursive'] = -1;
				$organizationResults = $Organization->find('first', $options);			
				$supplierResults['SuppliersOrganization'][$numResult]['Organization'] = $organizationResults['Organization'];
				
				if($suppliersOrganizationResult['organization_id']==$this->user->organization['Organization']['id']) 
					$supplier_organization_id = $suppliersOrganizationResult['id'];
			}
			$this->set(compact('supplierResults', 'supplier_organization_id'));
			
			if(empty($supplier_organization_id))
				self::x("Non trovato un produttore associato al G.A.S. ".$this->user->organization['Organization']['id']);
			
			/*
			$Article->unbindModel(['belongsTo' => ['SuppliersOrganization', 'CategoriesArticle']]);
			$Article->unbindModel(['hasOne' => ['ArticlesOrder']]);
			$Article->unbindModel(['hasMany' => ['ArticlesOrder']]);
			$Article->unbindModel(['hasAndBelongsToMany' => ['Order']]);
			$Article->unbindModel(['hasOne' => ['ArticlesArticlesType']]);
			$Article->unbindModel(['hasMany' => ['ArticlesArticlesType']]);
			$Article->unbindModel(['hasAndBelongsToMany' => ['ArticlesType']]);		
			*/
			
			$options = array();
			$options['conditions'] = ['Article.organization_id' =>  $this->user->organization['Organization']['id'],
										   'Article.supplier_organization_id' => $supplier_organization_id,
										   // escludo quelli gia' associati al prodGasSupplier
										   'Article.supplier_id' => 0,
										   'Article.prod_gas_article_id' => 0];
			$options['order'] = array('Article.id asc');
			$options['recursive'] = -1;
			$results = $Article->find('all', $options);	
			$this->set(compact('results'));
			/*
			if($debug) {
				echo "<pre>Elenco articoli \n";
				print_r($options);
				print_r($results);
				echo "</pre>";
			}
			*/

		} // end if(!empty($prod_gas_supplier_id))

		$this->str_log = "";
		if(!empty($elabora)) {
		
			foreach($results as $result) {
				
				$this->str_log .= "<hr /><h2>Tratto articolo ".$result['Article']['name']." (".$result['Article']['id'].")</h2>";
				
				$ProdGasArticle = new ProdGasArticle;

				/*
				 * creo $data[ProdGasArticle]
				 */
				$data = array(); 
				$data['ProdGasArticle'] = $result['Article'];
				$data['ProdGasArticle']['supplier_id'] = $prod_gas_supplier_id;
				unset($data['ProdGasArticle']['id']);  // se no prende quello dell'articolo!!
				unset($data['ProdGasArticle']['organization_id']);
				unset($data['ProdGasArticle']['supplier_organization_id']);
				unset($data['ProdGasArticle']['prod_gas_article_id']);
				unset($data['ProdGasArticle']['img1']);
				unset($data['ProdGasArticle']['created']);
				unset($data['ProdGasArticle']['modified']);
				
				if($debug) {
					/*
					echo "<pre>Tratto articolo \n";
					print_r($result);
					echo "</pre>";
					
					echo "<pre>SAVE Article per il produttore \n";
					print_r($data);
					echo "</pre>";
					*/
				}	

			
				/*
				 * richiamo la validazione 
				 * non funge!!!!!
				 */
				$ProdGasArticle->set($data);	
				if(!$ProdGasArticle->validates()) {
				
						$errors = $ProdGasArticle->validationErrors;
						$tmp = '';
						$flatErrors = Set::flatten($errors);
						if(count($errors) > 0) { 
							$tmp = '';
							foreach($flatErrors as $key => $value) 
								$tmp .= $value.' - ';
						}
						
						if($debug) 
							$this->str_log .= '<h1 style=color:red;>'.$tmp.'</h1>';
				}
				else {
					/*
					if($debug) 
						echo '<br />Validazione OK ';
					*/
					
					if(!$debug_insert) {
						$ProdGasArticle->create();
						if($ProdGasArticle->save($data)) {
							
							$prod_gas_article_id = $ProdGasArticle->getLastInsertId();
							
							if(empty($prod_gas_article_id))
								self::x("getLastInsertId() non ha restituito null!!");
								
							/*
							 * aggiorno articolo
							 */
							$sql = "UPDATE ".Configure::read('DB.prefix')."articles SET
										prod_gas_article_id = ".$prod_gas_article_id.", supplier_id = ".$prod_gas_supplier_id."  
									WHERE
										organization_id = ".$this->user->organization['Organization']['id']."
										AND id = ".$result['Article']['id'];
							$this->str_log .= '<br /><b>Articolo diventa del produttore</b> '.$sql;
							if(!$debug_insert) 
								$results = $ProdGasArticle->query($sql);

							/*
							 * gestione ProdGasArticles.img1
							 */
							if(!empty($result['Article']['img1'])) {
								$img1 = $this->__copy_img_prod_gas_supplier($prod_gas_supplier_id, $prod_gas_article_id, $result['Article']['organization_id'], $result['Article']['id'], $result['Article']['img1'], $debug, $debug_insert);
								if(!empty($img1)) {
									$sql = "UPDATE ".Configure::read('DB.prefix')."prod_gas_articles SET img1 = '".$img1."' WHERE id = ".$prod_gas_article_id." AND supplier_id = ".$prod_gas_supplier_id;
									$this->str_log .= '<br /><b>Aggiorno IMG per l\'articolo del produttore</b> '.$sql;
									if(!$debug_insert) 
										$results = $ProdGasArticle->query($sql);
								}
								else {
									if($debug)  
										$this->str_log .= '<h1 style=color:red;>ProdGasArticles.img1 NON aggiornato</h1>';								
								}
							}
							else {
								$this->str_log .= '<br />Articolo senza IMG ';
							}						
						}	
							
					} // end if(!$debug_insert)
					else {
						$this->str_log .= '<br />DEBUG Aggiorno Articolo del GAS '.$this->user->organization['Organization']['id'].' con article_id '.$result['Article']['id'];
						if(!empty($result['Article']['img1'])) 
							$this->str_log .= '<br />DEBUG copio img1 '.$result['Article']['img1'].' dell articolo nella directory del produttore';
						else
							$this->str_log .= '<br />DEBUG articolo senza img1';
					}
				}  // end if(!$ProdGasArticle->validates()) 			
			}
			$this->set(compact('results'));

		} // if(!empty($elabora)) 
        $this->set('str_log', $this->str_log);

		/*
		 * lista produttori
		 */
		$options = array();
		$options['recursive'] = -1;
		$options['order'] = array('Supplier.name' => 'asc');
		$suppliers = $Supplier->find('list', $options);	
		$this->set(compact('suppliers'));
	}
	
   /*
    * gestione immagine (inversa Article.copy_img_prod_gas_supplier)
    * Article.img a da ProdGasArticle.img
	* 
	* return $results['Article']['img1'] con il nuovo valore (Article.id + extension ProdGasArticle.img)
    */
   private function __copy_img_prod_gas_supplier($prod_gas_supplier_id, $prod_gas_article_id, $article_organization_id, $article_id, $img1, $debug, $debug_insert) {

	    $newFile = '';
	   
	    if(empty($prod_gas_supplier_id)) {
		    if($debug) $this->str_log .= '<br />Nessuna copia del file, prod_gas_supplier_id empty';
			return false;
	    }
	   		
	    if(empty($img1)) {
		    if($debug) $this->str_log .= '<br />Nessuna copia del file, Article.img1 empty';
			return false;
	    }
	   	
		try {
			$pathDa = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$article_organization_id;
			$pathA = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$prod_gas_supplier_id;
				
			$file = new File($pathDa . DS . $img1);
			if ($file->exists()) {
				$ext = strtolower(pathinfo($img1, PATHINFO_EXTENSION));
				$newFile = $prod_gas_article_id.'.'.$ext;
				// if($debug) echo '<br />newFile '.$newFile.' ext '.$ext;
				if(!$debug_insert)
					$file->copy($pathA . DS . $newFile);
				
				if($debug) $this->str_log .= '<br /><pre class="shell" rel="">cp '.$pathDa. DS .$img1.' '.$pathA. DS .$newFile.'</pre>';
			}
			else {
				if($debug) $this->str_log .= '<h1 style=color:red;>Nessuna copia del file '.$pathDa.DS.$img1.' non esiste</h1>';
			}			
		}		
		catch(Exception $e) {
		    if($debug) echo $e;
			return false;
	   	}

	    return $newFile;
	}	
}