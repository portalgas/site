<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

class ProdGasArticlesController extends AppController {
			
	public $components = array('Documents');			
	public $helpers = array('Javascript', 'Tabs', 'Image');
	
	public function beforeFilter() {
		parent::beforeFilter();
		
		/* ctrl ACL */
		if(empty($this->user->supplier['Supplier'])) {
			$this->Session->setFlash(__('msg_not_organization_config'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}	
		/* ctrl ACL */	
	}
	
	public function admin_index() { 
		$conditions = $this->__admin_index_sql_conditions($this->user);
		
		$SqlLimit = 25;				
		/*
		 * parametri da passare eventualmente a admin_edit
		 */
		$sort = '';
		$direction = '';
		$page = 0;
		if (!empty($this->request->params['named']['sort'])) 
			$sort = $this->request->params['named']['sort'];
		else 
			unset($this->request->params['named']['sort']);
		
		if (!empty($this->request->params['named']['direction'])) 
			$direction = $this->request->params['named']['direction'];
		if (!empty($this->request->params['named']['page'])) 
			$page = $this->request->params['named']['page'];		$this->set('sort', $sort);		$this->set('direction', $direction);		$this->set('page', $page);
		$this->paginate = array('conditions' => $conditions,
								'order' => 'ProdGasArticle.name','recursive' => -1,'limit' => $SqlLimit);
	    $results = $this->paginate('ProdGasArticle');
	    /*
	    echo "<pre>";
	    print_r($results);
	    echo "</pre>";
	    */
	    $this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);
	}

	public function admin_index_quick() {
		
		$debug = false;
		
		$results = array();		$SqlLimit = 1000;		
		if ($this->request->is('post') || $this->request->is('put')) {				
			if(isset($this->request->data['ProdGasArticle']['article_id_selected']) && !empty($this->request->data['ProdGasArticle']['article_id_selected'])) {
				$array_article_id = explode(',',$this->request->data['ProdGasArticle']['article_id_selected']);
				$msg = '';
				foreach ($array_article_id as $id) {
					
					$options = array();
					$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->supplier['Supplier']['id'],
												   'ProdGasArticle.id' => $id);
					$options['recursive'] = -1;
					$articleResults = $this->ProdGasArticle->find('first', $options);	
					if (empty($articleResults)) 						$msg .= 'Errore articolo id '.$id.'<br />';
					else {
						
						$name = $articleResults['ProdGasArticle']['name'];
						
						if(!$this->ProdGasArticle->delete($id))
							$msg .= 'Errore cancellazione articolo "'.$name.'".<br />';
						else
							$msg .= 'Articolo "'.$name.'" cancellato definitivamente.<br />';
					}					
				}
				$this->Session->setFlash($msg);
			}	
		}  // end if ($this->request->is('post') || $this->request->is('put'))						
		$conditions = $this->__admin_index_sql_conditions($this->user);		
		$this->paginate = array('conditions' => $conditions,
								'order' => 'ProdGasArticle.name','recursive' => -1,'limit' => $SqlLimit);
	    $results = $this->paginate('ProdGasArticle');
	    /*
	    echo "<pre>";
	    print_r($results);
	    echo "</pre>";
	    */
	    $this->set('results', $results);
		$this->set('SqlLimit', $SqlLimit);	}
		
	public function admin_add() {
		
		$debug = false;
		$continue = true;
		
		if ($this->request->is('post') || $this->request->is('put')) {	

			$msg = "";	
			$this->request->data['ProdGasArticle']['supplier_id'] = $this->user->supplier['Supplier']['id'];
			
			/*
			 * il js setArticlePrezzoUmRiferimento crea ['Article']['um_riferimento']
			 */
			$this->request->data['ProdGasArticle']['um_riferimento'] = $this->request->data['Article']['um_riferimento'];
			
			/*
			 * richiamo la validazione 
			 */
			$this->ProdGasArticle->set($this->request->data);			if(!$this->ProdGasArticle->validates()) {
			
					$errors = $this->ProdGasArticle->validationErrors;
					$tmp = '';
					$flatErrors = Set::flatten($errors);
					if(count($errors) > 0) { 
						$tmp = '';
						foreach($flatErrors as $key => $value) 
							$tmp .= $value.' - ';
					}
					$msg .= "Articolo non inserito: dati non validi, $tmp<br />";
					$this->Session->setFlash($msg);
			}
			else {
				/*
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";	
				*/				
				$this->ProdGasArticle->create();
				if($this->ProdGasArticle->save($this->request->data)) {
				
					$id = $this->request->data['ProdGasArticle']['id'];
					$msg = __('The article has been saved');
						
					/*
					 * immagine
					 */
					if($continue) {
						$arr_extensions = Configure::read('App.web.img.upload.extension');
						$arr_contentTypes = Configure::read('ContentType.img');		 
						$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS;
						
						if(!empty($this->request->data['Document']['img1']['name'])){
							$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.article'), $debug);
							if(empty($esito['msg'])) {	
								$sql = "UPDATE
											".Configure::read('DB.prefix')."prod_gas_articles
										SET
											img1 = '".$esito['fileNewName']."'
										WHERE
											supplier_id = ".$this->user->supplier['Supplier']['id']."
											and id = ".$id;
								if($debug) echo "UPDATE IMG ".$sql;
								$uploadResults = $this->ProdGasPromotion->query($sql);						
							}
							else
								$msg = $esito['msg'];
								if($debug)
									echo "<br  />msg UPLOAD ".$msg;
						}				
					}
				
					$this->Session->setFlash($msg);
					
					$this->myRedirect(array('action' => $this->request->data['ProdGasArticle']['action_post'],'supplier_id' => $this->request->data['ProdGasArticle']['supplier_id'])); 
				} else {
					$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
				}
			}  // end if(!$this->ProdGasArticle->validates()) 
		} // end if ($this->request->is('post') || $this->request->is('put'))
				
		$um = ClassRegistry::init('ProdGasArticle')->enumOptions('um');
		$this->set(compact('um'));
		$stato = ClassRegistry::init('ProdGasArticle')->enumOptions('stato');
		$this->set(compact('stato'));
	}

	/*
	 * $sort					 passati dalla ricerca da admin_index  sort:value
	 * $direction				 passati dalla ricerca da admin_index  direction:asc
	 * $page					 passati dalla ricerca da admin_index  page:2
	 */
	public function admin_edit($id) {
		
		$debug = false;
		$continue = true;
		
		$options = array();
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->supplier['Supplier']['id'],
									   'ProdGasArticle.id' => $id);
		$options['recursive'] = -1;
		$results = $this->ProdGasArticle->find('first', $options);
		
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			$msg = "";
			
			$this->request->data['ProdGasArticle']['supplier_id'] = $this->user->supplier['Supplier']['id'];			
			
			/*
			 * il js setArticlePrezzoUmRiferimento crea ['Article']['um_riferimento']
			 */
			$this->request->data['ProdGasArticle']['um_riferimento'] = $this->request->data['Article']['um_riferimento'];
			
			/*			 * richiamo la validazione			*/			$this->ProdGasArticle->set($this->request->data);			if(!$this->ProdGasArticle->validates()) {
			
					$errors = $this->ProdGasArticle->validationErrors;
					$tmp = '';
					$flatErrors = Set::flatten($errors);
					if(count($errors) > 0) { 
						$tmp = '';
						foreach($flatErrors as $key => $value) 
							$tmp .= $value.' - ';
					}
					$msg .= "Articolo non inserito: dati non validi, $tmp<br />";
					$this->Session->setFlash($msg);											}			else {
				/*
				echo "<pre>";
				print_r($this->request->data);
				echo "</pre>";	
				*/
				$this->ProdGasArticle->create();								if($this->ProdGasArticle->save($this->request->data)) {
				
					$msg = __('The article has been saved');
				
					/*
					 * IMG1 delete
					 */
					if($this->request->data['ProdGasArticle']['file1_delete'] == 'Y') {
						$esito_delete = $this->__delete_img($id, $results['ProdGasArticle']['img1'], false);
						if($esito_delete)
							$msg .= "<br />e l'immagine cancellata";
						else
							$msg .= '<br />'.$esito_delete;						
					}
					
						
					/*
					 * immagine
					 */
					if($continue) {
						$arr_extensions = Configure::read('App.web.img.upload.extension');
						$arr_contentTypes = Configure::read('ContentType.img');		 
						$path_upload = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS;
						
						if(!empty($this->request->data['Document']['img1']['name'])){
							$esito = $this->Documents->genericUpload($this->user, $this->request->data['Document']['img1'], $path_upload, 'UPLOAD', $id, $arr_extensions, $arr_contentTypes, Configure::read('App.web.img.upload.width.article'), $debug);
							if(empty($esito['msg'])) {	
								$sql = "UPDATE
											".Configure::read('DB.prefix')."prod_gas_articles
										SET
											img1 = '".$esito['fileNewName']."'
										WHERE
											supplier_id = ".$this->user->supplier['Supplier']['id']."
											and id = ".$id;
								if($debug) echo "UPDATE IMG ".$sql;
								$uploadResults = $this->ProdGasArticle->query($sql);			
							}
							else
								$msg = $esito['msg'];
								if($debug)
									echo "<br  />msg UPLOAD ".$msg;
						}				
					}
			
										
					$this->Session->setFlash($msg);
						
					$filterParams = '';					$filterParams .= '&sort:'.$this->request->data['ProdGasArticle']['sort'];
					$filterParams .= '&direction:'.$this->request->data['ProdGasArticle']['direction'];
					$filterParams .= '&page:'.$this->request->data['ProdGasArticle']['page'];
					$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasArticles&action=index'.$filterParams);  
				} else {
					$this->Session->setFlash(__('The article could not be saved. Please, try again.'));
				}
			} // end if(!$this->ProdGasArticle->validates())	
		} // end if ($this->request->is('post') || $this->request->is('put'))
		
		$this->request->data=$results;
	
		if(!empty($results['ProdGasArticle']['img1']) && 
		   file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS.$results['ProdGasArticle']['img1'])) {
			
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS.$results['ProdGasArticle']['img1']);
			$this->set('file1', $file1);
		}
				
		$um = ClassRegistry::init('ProdGasArticle')->enumOptions('um');
		$this->set(compact('um'));
		
		$stato = ClassRegistry::init('ProdGasArticle')->enumOptions('stato');
		$this->set(compact('stato'));	
				
		
		/*
		 * parametri di ricerca da ripassare a admin_index
		 */ 
		$sort = '';		$direction = '';		$page = 0;		if (!empty($this->request->params['named']['sort']))			$sort = $this->request->params['named']['sort'];		if (!empty($this->request->params['named']['direction']))			$direction = $this->request->params['named']['direction'];		if (!empty($this->request->params['named']['page']))			$page = $this->request->params['named']['page'];		$this->set('sort', $sort);		$this->set('direction', $direction);		$this->set('page', $page);
	}
		
	public function admin_copy($id=0) {
	
		$debug = false;
		$url = "";
		
		$options = array();
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->supplier['Supplier']['id'],
									  'ProdGasArticle.id' => $id);
		$options['recursive'] = -1;
		$results = $this->ProdGasArticle->find('first', $options);			
		if (empty($results)) {
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
		if (!empty($this->request->params['named']['direction']))
			$direction = $this->request->params['named']['direction'];
		if (!empty($this->request->params['named']['page']))
			$page = $this->request->params['named']['page'];
		$this->set('sort', $sort);
		$this->set('direction', $direction);
		$this->set('page', $page);
		
		/*
		 * tolgo ProdGasArticle.id per fare l'insert
		 */
		unset($results['ProdGasArticle']['id']);

		if($debug) {
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}
	
		$this->ProdGasArticle->create();
		if ($this->ProdGasArticle->save($results['ProdGasArticle'], array('validate' => false))) {
			$id = $this->ProdGasArticle->getLastInsertId();
			
			$this->Session->setFlash(__('The article has been copied'));

			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasArticles&action=edit&id='.$id;			
		}
		else {
			$this->Session->setFlash(__('The article could not be copied. Please, try again.'));

			$url = Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasArticles&action=index';
				
		}

		if($debug) {
			echo '<br />url '.$url;
			exit;
		}
		$this->myRedirect($url);
	}
	
	public function admin_delete($id) {		
		$options = array();
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $this->user->supplier['Supplier']['id'],
									  'ProdGasArticle.id' => $id);
		$options['recursive'] = -1;
		$results = $this->ProdGasArticle->find('first', $options);			
		if (empty($results)) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			$filterParams = '';
			$filterParams .= '&sort:'.$this->request->data['ProdGasArticle']['sort'];
			$filterParams .= '&direction:'.$this->request->data['ProdGasArticle']['direction'];
			$filterParams .= '&page:'.$this->request->data['ProdGasArticle']['page'];
						
			if ($this->ProdGasArticle->delete($id)) {
				
				$msg = __('Delete Article');
				
				/*
				 * IMG1 delete
				*/
				if(!empty($results['ProdGasArticle']['img1'])) {
					$esito_delete = $this->__delete_img($id, $results['ProdGasArticle']['img1'], false);
					if($esito_delete)
						$msg .= "<br />e l'immagine cancellata";
					else
						$msg .= '<br />'.$esito_delete;
				}

				$this->Session->setFlash($msg);
			}	
			else
				$this->Session->setFlash(__('Article was not deleted'));
				
			$this->myRedirect(Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=ProdGasArticles&action=index'.$filterParams);					}
				
		/*
		 * img1 
		 */
		if(!empty($results['ProdGasArticle']['img1']) &&
		file_exists(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS.$results['ProdGasArticle']['img1'])) {
				
			$file1 = new File(Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS.$results['ProdGasArticle']['img1']);
			$this->set('file1', $file1);
		}

		/*		 * parametri di ricerca da ripassare a admin_index		*/		$sort = '';		$direction = '';		$page = 0;		if (!empty($this->request->params['named']['sort']))			$sort = $this->request->params['named']['sort'];		if (!empty($this->request->params['named']['direction']))			$direction = $this->request->params['named']['direction'];		if (!empty($this->request->params['named']['page']))			$page = $this->request->params['named']['page'];		$this->set('sort', $sort);		$this->set('direction', $direction);		$this->set('page', $page);


		$this->request->data = $results;

		/*
		 * promozioni associate all'articolo
		 */
		$promotionsResults = array();
		$this->set('promotionsResults', $promotionsResults);		
	}
	
	/*	 * crea sql per l'elenco articoli	*/	private function __admin_index_sql_conditions($user) {					$conditions = array();			/*
		 * conditions obbligatorie
		*/		$conditions[] = array('ProdGasArticle.supplier_id' => $user->supplier['Supplier']['id']);		
		/*		 * ctrl se non e' ancora stata effettuata una ricerca		* */		if(empty($conditions))			$this->set('iniCallPage', true);		else			$this->set('iniCallPage', false);			/*		echo "<pre>";		print_r($conditions);		echo "</pre>";		*/
			return $conditions;	}	
	private function __delete_img($prod_gas_article_id, $img1, $debug=false) {

		$img_path = Configure::read('App.root').Configure::read('App.img.upload.prod_gas_article').DS.$this->user->supplier['Supplier']['id'].DS;
		
		if($debug) {
			echo "<br >img1 $img1";
			echo "<br >img_path $img_path";
		}
		
		$esito = true;
		
		$file1 = new File($img_path.$img1, false, 0777);
		if($debug) {
			echo "<pre>";
			print_r($file1);
			echo "</pre>";
		}
		
		if(!$file1->delete()) 
			$esito = "<br />File $img1 non cancellato";
		
		
		/*
		 * update database
		*/
		$sql = "UPDATE
					".Configure::read('DB.prefix')."prod_gas_articles
				SET img1 = ''
				WHERE
					supplier_id = ".$this->user->supplier['Supplier']['id']."
					AND id = ".$prod_gas_article_id;
		if($debug) echo "<br >sql $sql";
		try {
			$this->ProdGasArticle->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		
		if($debug) exit;
		
		return $esito;
	}
}