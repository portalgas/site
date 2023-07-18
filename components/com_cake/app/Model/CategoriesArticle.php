<?php
App::uses('AppModel', 'Model');


class CategoriesArticle extends AppModel {

    public $name = 'CategoriesArticle';
    public $actsAs = ['Tree'];

	public $belongsTo = [
		'Parent' => [
			'className' => 'CategoriesArticle',
			'foreignKey' => 'parent_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		]
	];

	public $hasMany = [
		'ChildCategory' => [
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
		]
	];

	public function getIsSystemId($user, $organization_id) {

		$results = $this->getIsSystem($user, $organization_id);		
		if(empty($results)) {
			return 0;
		}
		return $results['CategoriesArticle']['id'];
	}

	public function getIsSystem($user, $organization_id) {

		$options = [];
		$options['conditions'] = ['organization_id' => $organization_id,
								  'is_system'=> true];
		$options['recursive'] = -1;
		$results = $this->find('first', $options);		
		if(empty($results)) {
			if($this->createIsSystem($user, $organization_id)) {
				$results = $this->find('first', $options);
			}
		}
		return $results;
	}

	public function createIsSystem($user, $organization_id) {

		$datas = [];
		$datas['CategoriesArticle']['organization_id'] = $organization_id;
		$datas['CategoriesArticle']['name'] = 'Generale';
		$datas['CategoriesArticle']['is_system'] = true;
		$datas['CategoriesArticle']['parent_id'] = null;
		$datas['CategoriesArticle']['lft'] = 1;
		$datas['CategoriesArticle']['rght'] = 2;
		$this->create();
		if (!$this->save($datas)) {
			return false;
		}

		return true;
	}
}