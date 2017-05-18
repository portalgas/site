<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class ProdGasSuppliersImportsController extends AppController {
														
	public function beforeFilter() {
		parent::beforeFilter();
		
		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
	}
	
	public function admin_index() { 
	
		$debug = true;
		$debug_insert = true; // se false insert in Article e copy Article.img1

		App::import('Model', 'Supplier');
		$Supplier = new Supplier;
		
		if($debug) {
			echo "<h1>Modalita DEBUG</h1>";
			/*
			echo "<pre>";
			print_r($this->request->params['pass']);
			echo "</pre>";
			*/
		}
		if($debug_insert) {
			echo "<h1>Modalita DEBUG_INSERT: non scrivo sul database</h1>";
		}
		
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
				die("Produttore $prod_gas_supplier_id  non trovato!");
			
			
			/*
			 * ctrl Directory ProdGasArticle.img
			 */
			$path = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$prod_gas_supplier_id;
			$this->set(compact('path'));
				

			$folder_prod_gas_article = new Folder($path);
			if (is_null($folder_prod_gas_article->path)) 
				die("<h1 style=color:red;>Directory ProdGasArticle.img $path inesistente</h1>");
			
			
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
				die("<h1 style=color:red;>Non trovato un produttore associato al G.A.S. ".$this->user->organization['Organization']['id'].'</h1>');
			
			/*
			$Article->unbindModel(array('belongsTo' => array('SuppliersOrganization', 'CategoriesArticle')));
			$Article->unbindModel(array('hasOne' => array('ArticlesOrder')));
			$Article->unbindModel(array('hasMany' => array('ArticlesOrder')));
			$Article->unbindModel(array('hasAndBelongsToMany' => array('Order')));
			$Article->unbindModel(array('hasOne' => array('ArticlesArticlesType')));
			$Article->unbindModel(array('hasMany' => array('ArticlesArticlesType')));
			$Article->unbindModel(array('hasAndBelongsToMany' => array('ArticlesType')));		
			*/
			
			$options = array();
			$options['conditions'] = array('Article.organization_id' =>  $this->user->organization['Organization']['id'],
										   'Article.supplier_organization_id' => $supplier_organization_id);
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

		$str_log = "";
		if(!empty($elabora)) {	
			foreach($results as $result) {
				
				$str_log .= "<h2>Tratto articolo ".$result['Article']['name']." (".$result['Article']['id'].")</h2>";
				
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
							$str_log .= '<h1 style=color:red;>'.$tmp.'</h1>';
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
								die("<h1 style=color:red;>getLastInsertId() non ha restituito null!!</h1>");
							
							/*
							 * aggiorno articolo
							 */
							$sql = "UPDATE ".Configure::read('DB.prefix')."articles SET
										prod_gas_article_id = ".$prod_gas_article_id.", supplier_id = ".$prod_gas_supplier_id."  
									WHERE
										organization_id = ".$this->user->organization['Organization']['id']."
										AND id = ".$result['Article']['id'];
							$str_log .= '<br /><b>Articolo diventa del produttore</b> '.$sql;
							if(!$debug_insert) 
								$results = $ProdGasArticle->query($sql);

							/*
							 * gestione ProdGasArticles.img1
							 */
							if(!empty($result['Article']['img1'])) {
								$img1 = $this->__copy_img_prod_gas_supplier($prod_gas_supplier_id, $prod_gas_article_id, $result['Article']['organization_id'], $result['Article']['id'], $result['Article']['img1'], $debug, $debug_insert);
								if(!empty($img1)) {
									$sql = "UPDATE ".Configure::read('DB.prefix')."prod_gas_articles SET img1 = '".$img1."' WHERE id = ".$prod_gas_article_id." AND supplier_id = ".$prod_gas_supplier_id;
									$str_log .= '<br /><b>Aggiorno IMG per l\'articolo del produttore</b> '.$sql;
									if(!$debug_insert) 
										$results = $ProdGasArticle->query($sql);
								}
								else {
									if($debug)  
										$str_log .= '<h1 style=color:red;>ProdGasArticles.img1 NON aggiornato</h1>';								
								}
							}
							else {
								$str_log .= '<br />Articolo senza IMG ';
							}						
						}	
							
					} // end if(!$debug_insert)
					else {
						$str_log .= '<br />DEBUG Aggiorno Articolo del GAS '.$this->user->organization['Organization']['id'].' con article_id '.$result['Article']['id'];
						if(!empty($result['Article']['img1'])) 
							$str_log .= '<br />DEBUG copio img1 '.$result['Article']['img1'].' dell articolo nella directory del produttore';
						else
							$str_log .= '<br />DEBUG articolo senza img1';
					}
				}  // end if(!$ProdGasArticle->validates()) 			
			}
			$this->set(compact('results'));

		} // if(!empty($elabora)) 
        $this->set(compact('str_log'));

		/*
		 * lista produttori
		 */
		$options = array();
		$options['recursive'] = -1;
		$options['order'] = array('Supplier.name' => 'asc');
		$suppliers = $Supplier->find('list', $options);	
		$this->set(compact('suppliers'));	}
	
   /*
    * gestione immagine (inversa Article.copy_img_prod_gas_supplier)
    * Article.img a da ProdGasArticle.img
	* 
	* return $results['Article']['img1'] con il nuovo valore (Article.id + extension ProdGasArticle.img)
    */
   private function __copy_img_prod_gas_supplier($prod_gas_supplier_id, $prod_gas_article_id, $article_organization_id, $article_id, $img1, $debug, $debug_insert) {

	    $newFile = '';
	   
	    if(empty($prod_gas_supplier_id)) {
		    if($debug) echo '<br />Nessuna copia del file, prod_gas_supplier_id empty';
			return false;
	    }
	   		
	    if(empty($img1)) {
		    if($debug) echo '<br />Nessuna copia del file, Article.img1 empty';
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
				
				if($debug) echo '<br />Copy File DA '.$pathDa.'/'.$img1.' A '.$pathA.'/'.$newFile;
			}
			else {
				if($debug) echo '<h1 style=color:red;>Nessuna copia del file '.$pathDa.DS.$img1.' non esiste</h1>';
			}			
		}		
		catch(Exception $e) {
		    if($debug) echo $e;
			return false;
	   	}

	    return $newFile;
	}	
}