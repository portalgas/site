<?php
App::uses('AppController', 'Controller');
App::import('Vendor', 'ImageTool');

/**
 * SuppliersOrganizationsJcontents Controller
 *
 * @property SuppliersOrganizationsJcontent $SuppliersOrganizationsJcontent
 * @property PaginatorComponent $Paginator
 */
class SuppliersOrganizationsJcontentsController extends AppController {

	 
	public function beforeFilter() {
		parent::beforeFilter();

	}

	public function admin_edit($supplier_organization_id=0) {
		
		$debug = false;

		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$msg = "";
		
		if ($this->request->is('post') || $this->request->is('put')) 
			$supplier_organization_id = $this->request->data['SuppliersOrganizationsJcontent']['supplier_organization_id'];
		
		$SuppliersOrganization->id = $supplier_organization_id;
		if (!$SuppliersOrganization->exists($SuppliersOrganization->id, $this->user->organization['Organization']['id'])) {
			$this->Session->setFlash(__('msg_error_params'));
			$this->myRedirect(Configure::read('routes_msg_exclamation'));
		}
			
			
		$this->set('supplier_organization_id', $supplier_organization_id);
			
		/*
		 * dati produttore
		*/
		$results = $this->_getDatiProduttore($this->user, $supplier_organization_id, $debug);
		$this->set('results', $results);
			
		
		if ($this->request->is('post') || $this->request->is('put')) {
			
			if($debug) {
				echo "Dati Request<pre>";
				print_r($this->request->data);
				echo "</pre>";
			}

			/*
			 *  gestione intro_text / full_text
			 *  text_intro_end indica dove si trova il punto nel testo per troncare intro_text
			 */
			$j_content_id = 0;
			$text_intro_end = $this->request->data['SuppliersOrganizationsJcontent']['text_intro_end'];
			$full_text = $this->request->data['SuppliersOrganizationsJcontent']['full_text'];
			if(!empty($full_text)) {
				if($text_intro_end==0) {
					$this->request->data['SuppliersOrganizationsJcontent']['intro_text'] = $full_text;
					$this->request->data['SuppliersOrganizationsJcontent']['full_text'] = '';
				}
				else {
					$this->request->data['SuppliersOrganizationsJcontent']['intro_text'] = substr($full_text, 0, ($text_intro_end+2));
					
					$full_text = substr($full_text, ($text_intro_end+2), strlen($full_text));
					$full_text .= "<p>{flike}</p>";
					
					$this->request->data['SuppliersOrganizationsJcontent']['full_text'] = $full_text;
					
					if($debug) {
						echo '<br />'.$this->request->data['SuppliersOrganizationsJcontent']['intro_text'];
						echo '<hr />';
						echo '<br />'.$this->request->data['SuppliersOrganizationsJcontent']['full_text'];
					}
				}	
				
				$j_content_id = $this->_gestJContent($this->request->data, $results, $debug);
			}
			
			if($j_content_id > 0) 
				$fileName = $j_content_id;
			else {
				// se non ho un articolo di joomla nome file tmp-supplier_id
				$fileName = Configure::read('App.prefix.upload.content').$results['Supplier']['id'];			
			}				
			
			$esito = $this->_gestImg($this->request->data, $fileName, $debug);
			if(!empty($esito['ERROR']))
				$msg .= $esito['ERROR'];
			$fileName = $esito['FILE-NAME'];
			
			/*
			 * aggiorno Supplier
			 */
			App::import('Model', 'Supplier');
			$Supplier = new Supplier;
			
			$data = [];
			if(!empty($this->request->data['Document']['img1']['name']))  
				$data['Supplier']['img1'] = $fileName;
			
			$data['Supplier']['id'] = $results['Supplier']['id'];
			$data['Supplier']['j_content_id'] = $j_content_id;
			if($debug) {
				echo "Dati Supplier<pre>";
				print_r($data);
				echo "</pre>";
			}
			$Supplier->save($data);
			
			
			/*
			 * creo occorrenza in SuppliersOrganizationsJcontent
			 */
			if(!empty($this->request->data['SuppliersOrganizationsJcontent']['intro_text'])) {
				$data = [];
				if(!empty($results['SuppliersOrganizationsJcontent']['id']))
					$data['SuppliersOrganizationsJcontent']['id'] = $results['SuppliersOrganizationsJcontent']['id'];
				
				$data['SuppliersOrganizationsJcontent']['organization_id'] = $this->user->organization['Organization']['id'];
				$data['SuppliersOrganizationsJcontent']['title'] = $results['Supplier']['name'];
				$data['SuppliersOrganizationsJcontent']['supplier_organization_id'] = $supplier_organization_id;
				$data['SuppliersOrganizationsJcontent']['intro_text'] = $this->request->data['SuppliersOrganizationsJcontent']['intro_text'];
				$data['SuppliersOrganizationsJcontent']['full_text'] = $this->request->data['SuppliersOrganizationsJcontent']['full_text'];
				if($debug) {
					echo "<pre>";
					print_r($data);
					echo "</pre>";
				}
				$this->SuppliersOrganizationsJcontent->save($data);
		
			
				if(empty($msg)) 
					$msg = __('The supplier organization jcontent has been saved');
				else 
					$msg = __('The supplier organization jcontent has been saved').'<br />Il file non è stato caricato, '.$msg;
			}
						
			if(!empty($msg))
				$this->Session->setFlash($msg);
			
			if($debug)
				echo "<br />msg ".$msg;
			
			if(!$debug) 
				$this->myRedirect(array('controller' => 'SuppliersOrganizations', 'action' => 'index', $supplier_organization_id));
			
		} // end if ($this->request->is('post') || $this->request->is('put')) 
	}

	private function _getDatiProduttore($user, $supplier_organization_id, $debug=false) {
		
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
		
		$options = [];
		$options['conditions'] = array('SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
									   'SuppliersOrganization.id' => $supplier_organization_id);
		$options['recursive'] = 0;
		
		$SuppliersOrganization->unbindModel(array('belongsTo' => array('Organization')));
		$results = $SuppliersOrganization->find('first', $options);

		
		$options = [];
		$options['conditions'] = array('SuppliersOrganizationsJcontent.organization_id' => $user->organization['Organization']['id'],
									   'SuppliersOrganizationsJcontent.supplier_organization_id' => $supplier_organization_id);
		$options['recursive'] = -1;	
		$resultsSuppliersOrganizationsJcontent = $this->SuppliersOrganizationsJcontent->find('first', $options);
		
		if(!empty($resultsSuppliersOrganizationsJcontent)) {
			$results['SuppliersOrganizationsJcontent'] = $resultsSuppliersOrganizationsJcontent['SuppliersOrganizationsJcontent'];
			$results['SuppliersOrganizationsJcontent']['text'] = $resultsSuppliersOrganizationsJcontent['SuppliersOrganizationsJcontent']['intro_text'] . $resultsSuppliersOrganizationsJcontent['SuppliersOrganizationsJcontent']['full_text'];			
		}
		else {
			$results['SuppliersOrganizationsJcontent']['id'] = 0;
			$results['SuppliersOrganizationsJcontent']['organization_id'] = '';
			$results['SuppliersOrganizationsJcontent']['supplier_organization_id'] = '';
			$results['SuppliersOrganizationsJcontent']['title'] = '';
			$results['SuppliersOrganizationsJcontent']['full_text'] = '';
			$results['SuppliersOrganizationsJcontent']['text'] = '';
		}

		self::d($results, $debug);
		
		return $results;
	}
	
	private function _gestImg($data, $fileName, $debug=false) {

		$msg = [];
		
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
		if(!empty($data['Document']['img1']['name'])) {

			$file1 = $data['Document']['img1'];
			if($file1['error'] == UPLOAD_ERR_OK && is_uploaded_file($file1['tmp_name'])) {
				
				$path_upload = Configure::read('App.root').Configure::read('App.img.upload.content').DS;

				/*
				 * ctrl exstension / content type
				*/
				$ext = strtolower(pathinfo($file1['name'], PATHINFO_EXTENSION));
				
				$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
				$type = finfo_file ($finfo, $file1['tmp_name']);
				finfo_close($finfo);
				
				if(!in_array($ext, Configure::read('App.web.img.upload.extension')) || !in_array($type, Configure::read('ContentType.img'))) {
					$msg['ERROR'] = "Estensione .$ext non valida: si possono caricare file con la seguente estensione ";
					foreach ( Configure::read('App.web.img.upload.extension') as $estensione)
						$msg['ERROR'] .= '.'.$estensione.'&nbsp;';
				
					if($debug) {
						echo "<br />ext ".$ext;
						echo "<br />type ".$type;
						echo "<br />msg ".$msg['ERROR'];
						//exit;
					}
				}
			}
			else
				$msg['ERROR'] = $file1['error'];
	
			if(empty($msg['ERROR'])) {
				
				$fileName = $fileName.'.'.$ext;
				$msg['FILE-NAME'] = $fileName; 
				
				if(move_uploaded_file($file1['tmp_name'], $path_upload.$fileName)) {
					
					$info = getimagesize($path_upload.$fileName);
					$width = $info[0];
					$height = $info[1];
					if($debug) {
						echo "<pre>";
						print_r($info);
						echo "</pre>";
					}
			
					/*
					* ridimensiona img
					*/
					if($width > Configure::read('App.web.img.upload.width.supplier')) {
						$status = ImageTool::resize(array(
								'input' => $path_upload.$fileName,
								'output' => $path_upload.$fileName,
								'width' => Configure::read('App.web.img.upload.width.supplier'),
								'height' => ''
						));
					
						if($debug) echo "<br />ridimensiono ".$status;
					}
				}
				else
					$msg['ERROR'] .= $file1['error'];
			}
									
		} // end if(!empty($data['Document']['img1']['name'])) 	
		
		return $msg;
	}
	
	/*
	 * insert/update articolo in joomla
	 */			
	private function _gestJContent($data, $results, $debug=false) {

		$table = JTable::getInstance('Content', 'JTable', []);
		
		$data = array(
				'catid' => $results['CategoriesSupplier']['j_category_id'],
				'title' => $results['Supplier']['name'],
				'intro_text' => $data['SuppliersOrganizationsJcontent']['intro_text'],
				'full_text' => $data['SuppliersOrganizationsJcontent']['full_text'],
				'state' => 1,
		);
		
		if(!empty($results['Supplier']['j_content_id'])) // update
			$id = $results['Supplier']['j_content_id'];
		$data += array('id' => $id);

		if($debug) {
			echo "Dati x articolo Joomla<pre>";
			print_r($data);
			echo "</pre>";
		}
		
		// Bind data
		if (!$table->bind($data))
		{
			$this->Session->setFlash($table->getError());
			if($debug) echo '<h2>'.$table->getError().'</h2>';
		}
		
		// Check the data.
		if (!$table->check())
		{
			$this->Session->setFlash($table->getError());
			if($debug) echo '<h2>'.$table->getError().'</h2>';
		}
		
		// Store the data.
		if (!$table->store())
		{
			$this->Session->setFlash($table->getError());
			if($debug) echo '<h2>'.$table->getError().'</h2>';
		}

		if(!empty($results['Supplier']['j_content_id'])) // update
			$id = $results['Supplier']['j_content_id'];
		else
			$id = $table->get('id');
			
		if($debug) 
			echo '<br />Id Table Joomla '.$id;
		
		return $id;
	}		
}