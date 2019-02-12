<?php
App::uses('AppModel', 'Model');


class CategoriesArticle extends AppModel {

    public $name = 'CategoriesArticle';
    public $actsAs = array('Tree');

	public $belongsTo = array(
		'Parent' => array(
			'className' => 'CategoriesArticle',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

	public $hasMany = array(
		'ChildCategory' => array(
			'className' => 'CategoriesArticle',
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