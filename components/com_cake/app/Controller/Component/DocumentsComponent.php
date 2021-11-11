<?php 
App::uses('Component', 'Controller');
App::uses('UtilsCommons', 'Lib');
App::import('Vendor', 'ImageTool', ['file' => 'ImageTool.php']);

/*
 * 	$file = [
 * 		'name' => 'immagine.jpg',
 * 		'type' => 'image/jpeg',
 * 		'tmp_name' => /tmp/phpsNYCIB',
 * 		'error' => 0,
 *		'size' => 41737,
 * 	];
 *
 * UPLOAD_ERR_OK (0): Non vi sono errori, l'upload e' stato eseguito con successo;
 * UPLOAD_ERR_INI_SIZE (1): Il file inviato eccede le dimensioni specificate nel parametro upload_max_filesize di php.ini;
 * UPLOAD_ERR_FORM_SIZE (2): Il file inviato eccede le dimensioni specificate nel parametro MAX_FILE_SIZE del form;
 * UPLOAD_ERR_PARTIAL (3): Upload eseguito parzialmente;
 * UPLOAD_ERR_NO_FILE (4): Nessun file e' stato inviato;
 * UPLOAD_ERR_NO_TMP_DIR (6): Mancanza della cartella temporanea;
*/	
class DocumentsComponent extends Component {
	
    private $Controller = null;
	public $utilsCommons;

    public function initialize(Controller $controller) 
    {
		$this->Controller = $controller;
		$this->utilsCommons = new UtilsCommons($this->Time);
		
    }
	
	/*
	 * $action= UPLOAD / DELETE
	 *
	 * return 
	 *		$esito['fileNewName']
	 *      $esito['msg']
	 *
	 * chmod 755 directory
	 * chmod 644 files
	*/	 
	public function genericUpload($user, $file, $path, $action='UPLOAD', $new_name='', $arr_extensions=[], $arr_contentTypes=[], $resizeWidth="", $debug = false) {
	
		$controllerLog = $this->Controller;
	
		$esito = [];  // fileNewName, msg
		
		$controllerLog::d("DocumentsComponent::genericUpload() - action ".$action,$debug);
		
		switch ($action) {
			case "DELETE":
				$file_to_delete = $path.$file;
				$file1 = new File($file_to_delete, false, 0777);
				
				$controllerLog::d("File da cancellare $file_to_delete",$debug);
					
				if(!$file1->delete())
					$esito['msg'] = "<br />File $file non eliminato";
				else
					$esito['msg'] = "<br />File $file eliminato definitivamente";			
			break;
			case "UPLOAD":
				if($file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name'])) {

					$esito['msg'] = $this->_ctrl_size($file, $debug);
						
					if(empty($esito['msg'])) {
						$esito['msg'] = $this->_ctrl_exstension($file, $arr_extensions, $arr_contentTypes, $debug);
					}
					
					if(empty($esito['msg'])) {
					
						if(!empty($new_name)) {
							/*
							 * se nel nome nuovo non c'e' l'estensione 
							 * la concateno
							 */
							if(strpos($new_name, ".")===false) {
								$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
								$esito['fileNewName'] = $new_name.".".$ext;
							}
							else {
								$esito['fileNewName'] = $new_name;
							}
						}
						else
							$esito['fileNewName'] = $file['name'];
						
						$controllerLog::d("file temporaneo ".$file['tmp_name']." => move to ".$path.$esito['fileNewName'],$debug);
					
						if(!move_uploaded_file($file['tmp_name'], $path.$esito['fileNewName'])) 
							$esito['msg'] = $file1['error'];
						else {
							/*
							 * resizeWidth
							 */
							if(!empty($resizeWidth)) {
								$this->_resize($path.$esito['fileNewName'], $resizeWidth, $debug);
							}							
						}
					}
				}
				else 
					$esito['msg'] = $file['error'];			
			break;
			default:
				$esito['msg'] = "<br />Action ($action) non valida!";
			break;
		}

		$controllerLog::d($esito,$debug);
			
		return $esito;
	}
	
	/*
	 * ctrl size
	*/	
	private function _ctrl_size($file, $debug=false) {
		
		$controllerLog = $this->Controller;
		
		$esito = '';

		if($file['size'] > Configure::read('App.web.upload.max.size')) {
			
			$fileSizeLabel = $this->utilsCommons->formatSizeUnits($file['size']);
			$uploadMaxSizeLabel = $this->utilsCommons->formatSizeUnits(Configure::read('App.web.upload.max.size'));
			
			$esito .= "File di dimensioni troppo grandi (".$fileSizeLabel.")!\nPuoi uploadare file al massimo di ".$uploadMaxSizeLabel;
		}
					
		return $esito;
	}
	
	/*
	 * ctrl exstension / content type
	*/	
	private function _ctrl_exstension($file, $arr_extensions=[], $arr_contentTypes=[], $debug=false) {
		
		$controllerLog = $this->Controller;
	
		$esito = '';

		if(!empty($arr_extensions) || !empty($arr_contentTypes)) {
			$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$type = finfo_file ($finfo, $file['tmp_name']);
			finfo_close($finfo);

			$controllerLog::d("DocumentsComponent::_ctrl_exstension ext ".$ext,$debug);
			$controllerLog::d("DocumentsComponent::_ctrl_exstension type ".$type,$debug);
				
			if(!in_array($ext, $arr_extensions) || !in_array($type, $arr_contentTypes)) {
				$esito = "Estensione .$ext non valida: si possono caricare file con la seguente estensione ";
				foreach($arr_extensions as $extension)
					$esito .= '.'.$extension.'&nbsp;';
			}
		}
		
		return $esito;
	}

	private function _resize($img, $resizeWidth, $debug) {
		
		$controllerLog = $this->Controller;
		
		$info = getimagesize($img);
		$width = $info[0];
		$height = $info[1];
		
		$controllerLog::d([$resizeWidth, $img, $info],$debug);
						
		if($width > $resizeWidth) {
			$imageTool = new ImageTool(); 
			$status = $imageTool::resize(['input' => $img,
										'output' => $img,
										'width' => $resizeWidth,
										'height' => '']);
	
			$controllerLog::d("Image resize ".$status,$debug);
		}	

		return $status;
	}			
}
?>