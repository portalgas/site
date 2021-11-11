<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class LogsController extends AppController {

	var $dir_path;
	
	public function beforeFilter() {
		parent::beforeFilter();

		if(!$this->isRoot()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}

		$this->dir_path = Configure::read('App.root').Configure::read('App.log');
		$this->dir_path_joomla = Configure::read('App.root').Configure::read('App.log_joomla');
	}

	public function admin_index() {

		/*
		 * logs di cake
		*/
		$dir = new Folder($this->dir_path);
		$files = $dir->find('.*\.log');
		
		$this->set('dir_path',$this->dir_path);
		$this->set('files',$files);
		
		/*
		 * logs di joomla (ex com_users.log)
		 */
		$dir_joomla = new Folder($this->dir_path_joomla);
		$files_joomla = $dir_joomla->find('.*\.log');
		
		$this->set('dir_path_joomla',$this->dir_path_joomla);
		$this->set('files_joomla',$files_joomla);
	}
	
	/*
	 * target: com_cake / joomla
	 */
	public function admin_read($fileLog, $target) {
		
		if($target=='com_cake')
			$dir = $this->dir_path;
		else
		if($target=='joomla')
			$dir = $this->dir_path_joomla;
		else
		if($target=='apache2')
			$dir = $this->dir_path_apache2;
		else 
		if($target=='php')
			$dir = $this->dir_path_php;
		else 
			$dir = "";
		
		$file = new File($dir. DS . $fileLog);
		if($file->exists()) {
			$contents = $file->read();
			$file->close(); // Be sure to close the file when you're done
		}
		else {
			$file = null;
			$contents = null;
		}
		
		$this->set('file',$file);
		$this->set('contents',$contents);	
	}

	public function admin_execute($metohd) {
		$utilsCrons = new UtilsCrons(new View(null));
		echo "<pre>";		$utilsCrons->$metohd($this->user->organization['Organization']['id'], true);		echo "</pre>";			}
}