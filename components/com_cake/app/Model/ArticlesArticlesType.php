<?php
App::uses('AppModel', 'Model');

class ArticlesArticlesType extends AppModel {

	/*	 * estraggo gli ArticlesType di un aticolo	*/	public function getArticlesArticlesTypes($user, $article_id) {	
		$results = array();
		try {			$conditions = array('ArticlesArticlesType.organization_id' => $user->organization['Organization']['id'],								'ArticlesArticlesType.article_id' => $article_id);
			$this->unbindModel(array('belongsTo' => array('Article')));
			$results = $this->find('all', array('conditions' => $conditions,															'order_by' => 'ArticlesType.sort',															'recursive' => 0));
		}		catch (Exception $e) {			CakeLog::write('error',$sql);			CakeLog::write('error',$e);  		}						
		return $results;	}
	
	public $validate = array(
		'organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_type_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
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