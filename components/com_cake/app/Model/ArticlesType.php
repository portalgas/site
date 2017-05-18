<?php
App::uses('AppModel', 'Model');
/**
 * ArticlesType Model
 *
 * @property Article $Article
 */
class ArticlesType extends AppModel {
	
	/*
	 * estraggo tutti gli articleType
	 */
	public function getArticlesTypes() {
		$results = array();
			$results = $this->find('all', array('order_by' => 'sort',
												'recursive' => -1));
		}
		return $results;
	}
	
	/*
	 * da 
	 * 		Array ([0] => Array([ArticlesType] => Array (
									                    [id] => 1
									                    [code] => BIO
									                    [label] => Biologico
									                    [descrizione] => Da agricoltura biologica
									                    [sort] => 1))
	   a  [1] => Biologico	                   
	 */
	public function prepareArray($results) {

		$tmp = array();
		foreach ($results as $result) {
			$tmp += array($result['ArticlesType']['id'] => $result['ArticlesType']['label']); 
		}
				
		return $tmp;
	}
		

	 * simile codice in AppHelper
	public function isArticlesTypeBio($results) {
	
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'code' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'label' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'sort' => array(
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
}