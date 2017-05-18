<?php
App::uses('AppModel', 'Model');

class ProdGasArticlesOrder extends Model {	
	
	public $validate = array(
		'supplier_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'article_organization_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'article_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
		'prezzo' => array(
			'rule' => array('decimal', 2),
			'message' => "Indica il prezzo dell'articolo con un valore numerico con 2 decimali (1,00)",
		),		
		'pezzi_confezione' => array(
			'notempty' => array(
				'rule' => array('naturalNumber', false),
			),
		),
		'qta_minima' => array(
			'notempty' => array(
				'rule' => array('notempty', false),
				'message' => 'Indica la quantità minima che un gasista può acquistare',
			),
			'numeric' => array(
				'rule' => array('naturalNumber', false),
				'message' => "La quantità minima che un gasista può acquistare dev'essere indicata con un valore numerico maggiore di zero",
				'allowEmpty' => false,
			),
		),
		'qta_massima' => array(
			'notempty' => array(
				'rule' => array('notempty', false),
				'message' => 'Indica la quantità massima che un gasista può acquistare',
			),
			'numeric' => array(
					'rule' => array('numeric', false),
					'message' => "La quantità massima che un gasista può acquistare dev'essere indicata con un valore numerico",
					'allowEmpty' => true,
			),				
		),
		'qta_minima_order' => array(
			'notempty' => array(
					'rule' => array('notempty', false),
					'message' => "Indica la quantità minima rispetto a tutti gli acquisti dell'ordine",
			),		
			'numeric' => array(
				'rule' => array('numeric', false),
				'message' => "La quantità minima rispetto a tutti gli acquisti dell'ordine dev'essere indicata con un valore numerico",
				'allowEmpty' => true,
			),
		),
		'qta_massima_order' => array(
			'notempty' => array(
					'rule' => array('notempty', false),
					'message' => "Indica la quantità massima rispetto a tutti gli acquisti dell'ordine",
			),		
			'numeric' => array(
				'rule' => array('numeric', false),
				'message' => "La quantità massima rispetto a tutti gli acquisti dell'ordine dev'essere indicata con un valore numerico",
				'allowEmpty' => true,
			),
		),
		'qta_multipli' => array(
			'notempty' => array(
				'rule' => array('naturalNumber', false),
			),
		),
	);
	
	public $belongsTo = array(
		'Article' => array(
			'className' => 'Article',
			'foreignKey' => 'article_id',
			'conditions' => '', // 'Article.organization_id = ProdGasArticlesOrder.article_organization_id',
			'fields' => '',
			'order' => ''
		),
		'Supplier' => array(
			'className' => 'Supplier',
			'foreignKey' => 'supplier_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Organization' => array(			'className' => 'Organization',			'foreignKey' => '',			'conditions' => 'Organization.id = ProdGasArticlesOrder.article_organization_id',			'fields' => '',			'order' => '',		),			
	);

	public function afterFind($results, $primary = true) {		
		foreach ($results as $key => $val) {
			if(!empty($val)) {				
				if (isset($val['ProdGasArticlesOrder']['prezzo'])) {
					$results[$key]['ProdGasArticlesOrder']['prezzo_'] = number_format($val['ProdGasArticlesOrder']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasArticlesOrder']['prezzo_e'] = $results[$key]['ProdGasArticlesOrder']['prezzo_'].' &euro;';
				}
				else
				/*				 * se il find() arriva da $hasAndBelongsToMany				*/				if(isset($val['prezzo'])) {					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';				}					
			}
		}
		return $results;
	}	
}