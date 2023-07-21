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

	/* 
	 * setto con la categoria di default (Generali) gli articoli che non hanno categoria
	 */
	public function setCategoryDefaultToArticles($user, $organization_id, $debug=false) {

		$category = $this->getIsSystem($user, $organization_id);

		/*
		 * estraggo articoli senza categoria impostata
		 */
		App::import('Model', 'Article');
		$Article = new Article;
				
		$update_fields = ['Article.category_article_id' => $category['CategoriesArticle']['id']];
		$where = ['Article.organization_id' => $organization_id,
				   'Article.category_article_id' => 0];
		$results = $Article->updateAll($update_fields, $where);
		if($debug) debug($update_fields);
		if($debug) debug($where);

		return $results;
	}
	
	/* 
	 * se $truncate=true cancella tutte le cateogirie dell'org che non sono is_system
	 * 	utile la prima volta per creare quella 'Generale' e cancella le vecchie categorie
	 * 
	 *  ora gestione con truncate in neo
	 */
	public function getIsSystem($user, $organization_id, $truncate=false, $debug=false) {
		
		$options = [];
		$options['conditions'] = ['organization_id' => $organization_id,
								  'is_system'=> true];
		$options['recursive'] = -1;
		$results = $this->find('first', $options);	
		if($debug) print_r($options['conditions']);	
		if($debug) print_r($results);
		if(empty($results)) {
		   
			if($truncate) {			
				$sql = "DELETE FROM " . Configure::read('DB.portalPrefix') . "categories_articles WHERE organization_id = " . $organization_id;
				$delete_results = $this->query($sql);				
				if($debug) echo('deleteAll '.$sql);
				// $this->deleteAll(['organization_id' => $organization_id], false);
			}

			if($this->createIsSystem($user, $organization_id)) {
				if($debug) echo('createIsSystem organization_id '.$organization_id);
				$results = $this->find('first', $options);
			}
	
			if($truncate) {
				if($debug) echo('setCategoryDefaultToArticles organization_id '.$organization_id);
				$this->setCategoryDefaultToArticles($user, $organization_id);
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