<?php
/*
 * Controller/FullCalendarController.php
 * CakePHP Full Calendar Plugin
 *
 * Copyright (c) 2010 Silas Montgomery
 * http://silasmontgomery.com
 *
 * Licensed under MIT
 * http://www.opensource.org/licenses/mit-license.php
 */
 
class FullCalendarController extends AppController {

	public function beforeFilter() {
		parent::beforeFilter();
		
        /* ctrl ACL */
		if (!$this->isManager() && !$this->isManagerEvents()) {
			$this->Session->setFlash(__('msg_not_permission'));
			$this->myRedirect(Configure::read('routes_msg_stop'));
		}
        /* ctrl ACL */			
	}
	
	var $components = array('Session');
	var $helpers = array('Html', 'Form', 'Session', 'Js'=>array('Jquery'));

	var $name = 'FullCalendar';

	function admin_index() {
	}

}
?>
