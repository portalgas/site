<?php
App::uses('AppModel', 'Model');


class TemplatesOrdersState extends AppModel {

	/*
	 * estrae la lista dei gruppi associati ad un template
	 */
	public function getListGroups() {
		$sql = "SELECT
					UserGroup.id, UserGroup.title
				FROM
					".Configure::read('DB.prefix')."templates_orders_states AS TemplatesOrdersState, 
					".Configure::read('DB.portalPrefix')."usergroups AS UserGroup
				WHERE
					UserGroup.id = TemplatesOrdersState.group_id
				GROUP BY UserGroup.id, UserGroup.title
				ORDER BY UserGroup.title ";
		self::d($sql, false);
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}		
		
		$newResults = [];
		if(!empty($results))
			foreach ($results as $result) 
			$newResults[$result['UserGroup']['id']] = $result['UserGroup']['title'];
			
		return $newResults;
	}
	
	public $validate = array(
		'template_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'state_code' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'group_id' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
		'action_controller' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'action_action' => array(
			'notEmpty' => array(
				'rule' => ['notBlank']
			),
		),
		'sort' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
	);

	public $belongsTo = array(
		'UserGroup' => array(
			'className' => 'UserGroup',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Template' => array(
			'className' => 'Template',
			'foreignKey' => 'template_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)		
	);
}