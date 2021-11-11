<?php
App::uses('AppController', 'Controller');

class TemplatesOrdersStatesOrdersActionsController extends AppController {

	public $components = array('Paginator');

	public $paginate = array(
			'limit' => 500,
			'order' => array('id' => 'asc')
	);
	
	public function admin_index() {
		
		$conditions = [];
		
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
		
		$this->paginate += ['conditions' => $conditions,
							'order' => ['TemplatesOrdersStatesOrdersAction.state_code', 'TemplatesOrdersStatesOrdersAction.sort']];
		$this->Paginator->settings = $this->paginate;
		
		$this->TemplatesOrdersStatesOrdersAction->recursive = 0;
		$this->set('results', $this->Paginator->paginate());
		
		/*
		 * estraggo i gruppi associati al template
		 */
		App::import('Model', 'TemplatesOrdersState');
		$TemplatesOrdersState = new TemplatesOrdersState;
		$groups = $TemplatesOrdersState->getListGroups();
	
		$this->set('groups', $groups);
		
		/*
		 * template
		 */ 
		 $options = [];
		 $options = ['order' => 'Template.name asc'];
		 $templates = $this->TemplatesOrdersStatesOrdersAction->Template->find('list', $options);		
         $this->set('templates', $templates);		
	}
}