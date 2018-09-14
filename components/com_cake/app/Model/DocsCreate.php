<?php
App::uses('AppModel', 'Model');


class DocsCreate extends AppModel {

	public $useTable = 'docs';
	
	/**
	 * hasMany associations
	 *
	 * @var array
	 */
	public $hasMany = array(
			'DocsCreateUser' => array(
					'className' => 'DocsCreateUser',
					'foreignKey' => 'doc_id',
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
		'Organization' => array(
			'className' => 'Organization',
			'foreignKey' => 'organization_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)	
	);
}