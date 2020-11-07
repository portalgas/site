<?php
App::uses('AppController', 'Controller');

class TemplatesOrdersStatesController extends AppController {

	public $components = array('Paginator');

	public $paginate = [
			'maxLimit' => 500, 'limit' => 500,
			'order' => ['id' => 'asc']
	];
	
	public function admin_index() {

		$conditions = [];
		
		/*
		 * filtri
		 */
		$FilterTemplateId = null;
		$FilterGroupId = Configure::read('group_id_referent');
		
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'TemplateId')) {
			$FilterTemplateId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'TemplateId');
			$conditions += array('TemplatesOrdersState.template_id' => $FilterTemplateId);
		}
		if($this->Session->check(Configure::read('Filter.prefix').$this->modelClass.'GroupId')) {
			$FilterGroupId = $this->Session->read(Configure::read('Filter.prefix').$this->modelClass.'GroupId');
		}
		$conditions+= array('TemplatesOrdersState.group_id' => $FilterGroupId); 
		
		$this->set('FilterTemplateId', $FilterTemplateId);
		$this->set('FilterGroupId', $FilterGroupId);
		
		$this->paginate += ['conditions' => $conditions,
							 'order' => 'TemplatesOrdersState.sort ASC'];
		$this->Paginator->settings = $this->paginate;
		
		$this->TemplatesOrdersState->recursive = 0;
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
		 $templates = $this->TemplatesOrdersState->Template->find('list', $options);		
         $this->set('templates', $templates);		
	}
}