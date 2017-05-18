<?php
App::uses('AppModel', 'Model');

class DesUserGroupMap extends AppModel {

	public $actsAs = array('Containable');
    public $virtualFields = array('id' => 'user_id');
    
	public $useTable = 'user_usergroup_map'; 
	public $tablePrefix = 'j_';
	
	public $hasMany = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);
	
 	public $belongsTo = array(
		'UserGroup' => array(
			'className' => 'UserGroup',
			'foreignKey' => 'group_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);	
}