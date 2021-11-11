<?php
App::uses('AppModel', 'Model');


class ArticlesType extends AppModel {
	
	/*
	 * estraggo tutti gli articleType
	 */
	public function getArticlesTypes() {
		$results = [];
		try {
			$results = $this->find('all', array('order_by' => 'sort',
												'recursive' => -1));
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
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

		$tmp = [];
		foreach ($results as $result) {
			$tmp += array($result['ArticlesType']['id'] => $result['ArticlesType']['label']); 
		}
				
		return $tmp;
	}
		
	/*
	 * simile codice in AppHelper
	 * $results = $result['ArticlesType']
	*/	
	public function isArticlesTypeBio($results) {
		
		$count = 0;
		$isArticlesTypeBio = false;
	
		if(!empty($results)) {
			foreach($results['ArticlesType'] as $articleType) {				
				if($articleType['code']=='BIO' || $articleType['code']=='BIODINAMICO')
					$count++;
			}
		}
		 
		if($count>0) $isArticlesTypeBio = true;
		 
		return $isArticlesTypeBio;
	}
	
/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'code' => array(
			'notempty' => array(
				'rule' => ['notBlank']
			),
		),
		'label' => array(
			'notempty' => array(
				'rule' => ['notBlank']
			),
		),
		'sort' => array(
			'numeric' => array(
				'rule' => ['numeric']
			),
		),
	);
}