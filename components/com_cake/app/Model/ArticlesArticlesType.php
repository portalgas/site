<?php
App::uses('AppModel', 'Model');


class ArticlesArticlesType extends AppModel {

	/*
	 * estraggo gli ArticlesType di un aticolo
	*/
	public function getArticlesArticlesTypes($user, $organization_id, $article_id, $debug=false) {
		$results = [];
  			  
		$this->unbindModel(['belongsTo' => ['Article']]);
		
		$options = []; 
		$options['conditions'] = ['ArticlesArticlesType.organization_id' => $organization_id,
					  			  'ArticlesArticlesType.article_id' => $article_id];
		$options['order'] = ['ArticlesType.sort'];
		$options['recursive'] = 0; 
		$results = $this->find('all', $options);
		self::d($options, $debug);
		self::d($results, $debug);	

		return $results;
	}
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_type_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_id' => array(
			'numeric' => array(
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	public $belongsTo = array(
		'ArticlesType' => array(
			'className' => 'ArticlesType',
			'foreignKey' => 'article_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'article_id',
			'conditions' => 'Article.organization_id = ArticlesArticlesType.organization_id',
			'fields' => '',
			'order' => ''
		)
	);
}