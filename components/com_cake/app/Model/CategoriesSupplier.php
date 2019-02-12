<?php
App::uses('AppModel', 'Model');


class CategoriesSupplier extends AppModel {

    public $name = 'CategoriesSupplier';
    public $actsAs = array('Tree');

	public $belongsTo = array(
		'Parent' => array(
			'className' => 'CategoriesSupplier',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasMany = array(
		'ChildCategory' => array(
			'className' => 'CategoriesSupplier',
			'foreignKey' => 'parent_id',
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
}