<?php
App::uses('AppController', 'Controller');

class TemplatesOrdersStatesOrdersActionsController extends AppController {

	public $components = array('Paginator');

	public $paginate = array(
			'limit' => 500,
			'order' => array('id' => 'asc')
	);
	
	public function admin_index() {
		
		$conditions = array();
		
		/*
		 * filtri
		 */
		$FilterTemplateId = null;
		$FilterGroupId = Configure::read('group_id_referent');
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'TemplateId')) {
			$FilterTemplateId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'TemplateId');
			$conditions+= array('TemplatesOrdersStatesOrdersAction.template_id' => $FilterTemplateId);
		}
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'GroupId')) {
			$FilterGroupId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'GroupId');
		}
		$conditions+= array('TemplatesOrdersStatesOrdersAction.group_id' => $FilterGroupId);
		
		$this->set('FilterTemplateId', $FilterTemplateId);
		$this->set('FilterGroupId', $FilterGroupId);
		
		$this->paginate += array('conditions' => $conditions);
		$this->Paginator->settings = $this->paginate;
		
		$this->TemplatesOrdersStatesOrdersAction->recursive = 0;
		$this->set('results', $this->Paginator->paginate());
		
		$this->set('templates', Configure::read('templates'));
		
		/*
		 * estraggo i gruppi associati al template
		 */
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;
		$groups = $TemplatesOrdersState->getListGroups();
	
		$this->set('groups', $groups);
		
		
	}
}