<?php
App::uses('AppModel', 'Model');

class ProdGasArticle extends AppModel {

	public $name = 'ProdGasArticle';
	
	/*
	 * estrae i GAS che associato l'articolo ad un ordine
	 */
	public function getGasOrganizationInArticlesOrder($user, $article_organization_id, $article_id, $debug=false) {
        
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;
		$ArticlesOrder->bindModel(['belongsTo' => ['Organization' => ['className' => 'Organization', 'foreignKey' => 'organization_id']]]);
		$ArticlesOrder->unbindModel(['belongsTo' => ['Article', 'Cart']]);
					
        $options = [];
        $options['conditions'] = ['ArticlesOrder.article_organization_id' => $article_organization_id, 'ArticlesOrder.article_id' => $article_id];
        $options['order'] = ['Organization.name', 'Order.data_inizio'];
        $options['group'] = ['Order.id'];
        $options['recursive'] = 1;
        $articlesOrderResults = $ArticlesOrder->find('all', $options);
		
		if(!empty($articlesOrderResults))
		foreach($articlesOrderResults as $numResult => $articlesOrderResult) {
			
			/*
			 * Delivery
			 */
			$options = [];
			$options['conditions'] = ['Delivery.organization_id' => $articlesOrderResult['Order']['organization_id'], 'Delivery.id' => $articlesOrderResult['Order']['delivery_id']];
			$options['recursive'] = -1;
			$deliveryResults = $Delivery->find('first', $options);	
			$articlesOrderResults[$numResult]['Delivery'] = $deliveryResults['Delivery'];
		
		}
		self::d($articlesOrderResults, $debug);
		
		return $articlesOrderResults;
	}
	
	/*
	 * estrae i GAS che hanno acquistato un articolo
	 */
	public function getGasOrganizationInCart($user, $article_organization_id, $article_id, $debug=false) {
        
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;

        App::import('Model', 'Cart');
        $Cart = new Cart;
		$Cart->bindModel(['belongsTo' => ['Organization' => ['className' => 'Organization', 'foreignKey' => 'organization_id']]]);
		$Cart->unbindModel(['belongsTo' => ['Article', 'User']]);

        $options = [];
        $options['conditions'] = ['Cart.article_organization_id' => $article_organization_id, 'Cart.article_id' => $article_id];
        $options['group'] = ['Order.id'];
        $options['recursive'] = 1;
        $articlesOrderResults = $Cart->find('all', $options);
		self::d($articlesOrderResults, $debug);
				
		if(!empty($articlesOrderResults))
		foreach($articlesOrderResults as $numResult => $articlesOrderResult) {
			
			/*
			 * Delivery
			 */
			$options = [];
			$options['conditions'] = ['Delivery.organization_id' => $articlesOrderResult['Order']['organization_id'], 'Delivery.id' => $articlesOrderResult['Order']['delivery_id']];
			$options['recursive'] = -1;
			$deliveryResults = $Delivery->find('first', $options);	
			$articlesOrderResults[$numResult]['Delivery'] = $deliveryResults['Delivery'];
		
		}
		self::d($articlesOrderResults, $debug);
		
		return $articlesOrderResults;
	}
		
	/*
	 * come Article::isArticleInCart()
	 */
	public function isArticleInCart($user, $article_organization_id, $article_id, $debug=false) {
        
        $results = $this->getGasOrganizationInCart($user, $article_organization_id, $article_id, $debug);

		if(empty($results)) 
			return false;
		else 
			return true;		
	}
	
				
	
	/*
	 * estrae tutti gli articoli di un produttore
	 * se valorizzo prod_gas_promotion_id prendo solo quelli della promozione
	 */
	public function getArticles($user, $prod_gas_promotion_id=0, $debug=false) {
		
		$options = [];
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $user->organization['Supplier']['Supplier']['id']);
		$options['order'] = array('ProdGasArticle.name');
		$options['recursive'] = -1;
		$results = $this->find('all', $options);
		
		if(!empty($results)) {
			
			App::import('Model', 'ProdGasArticlesPromotion');
			
			foreach($results as $numResult => $result) {
				
				$prodGasArticlesPromotionResults = [];
				
				if($prod_gas_promotion_id!=0) {
					$prod_gas_article_id = $result['ProdGasArticle']['id'];
					
					$options = [];
					$options['conditions'] = array('ProdGasArticlesPromotion.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
												   'ProdGasArticlesPromotion.prod_gas_promotion_id' => $prod_gas_promotion_id,
												   'ProdGasArticlesPromotion.prod_gas_article_id' => $prod_gas_article_id);
					$options['recursive'] = -1;
					$ProdGasArticlesPromotion = new ProdGasArticlesPromotion;
					$prodGasArticlesPromotionResults = $ProdGasArticlesPromotion->find('first', $options);
					if(!empty($prodGasArticlesPromotionResults)) {
						$results[$numResult]['ProdGasArticlesPromotion'] = $prodGasArticlesPromotionResults['ProdGasArticlesPromotion'];
					}
				}
				
				if($prod_gas_promotion_id==0 || empty($prodGasArticlesPromotionResults)) {
					$results[$numResult]['ProdGasArticlesPromotion']['qta'] = '0';
					$results[$numResult]['ProdGasArticlesPromotion']['prezzo_unita'] = '0.00';
					$results[$numResult]['ProdGasArticlesPromotion']['prezzo_unita_'] = '0,00';
					$results[$numResult]['ProdGasArticlesPromotion']['prezzo_unita_e'] = '0,00 €';
					$results[$numResult]['ProdGasArticlesPromotion']['importo'] = '0.00';
					$results[$numResult]['ProdGasArticlesPromotion']['importo_'] = '0,00';
					$results[$numResult]['ProdGasArticlesPromotion']['importo_e'] = '0,00 €';
				} 				
			}
		}

		if($debug) {
			echo "<pre>";
			print_r($options);
			print_r($results);
			echo "</pre>";	
		}							
		
		return $results;
	}
	  
   public $validate = array(
		'supplier_id' => array(
			'rule' => array('naturalNumber', false),
			'message' => "Scegli il produttore da associare all'ordine",
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => "Indica il nome dell'articolo",
			),
		),
		'qta' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => "Indica la quantità dell'articolo",
			),
		),
		'prezzo' => array(
			'rule' => array('decimal', 2),
			'message' => "Indica il prezzo dell'articolo con un valore numerico con 2 decimali (1,00)",
		),		
		'pezzi_confezione' => array(
			'notempty' => array(					'rule' => array('notempty', false),					'message' => 'Indica il numero di pezzi che può contenere una confezione',			),			'numeric' => array(					'rule' => array('naturalNumber', false),					'message' => "Il numero di pezzi che può contenere una confezione dev'essere indicato con un valore numerico maggiore di zero",					'allowEmpty' => false,			),		),
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
		'qta_multipli' => array(
			'notempty' => array(
					'rule' => array('notempty', false),
					'message' => 'Indica la quantità minima che si può acquistabile',
			),		
			'numeric' => array(
					'rule' => array('naturalNumber', false),
					'message' => "La quantità multipla dev'essere indicata con un valore numerico maggiore di zero",
					'allowEmpty' => true,
			),
		),
	);

	public $hasMany = array(
		'ProdGasArticlesPromotion' => array(
				'className' => 'ProdGasArticlesPromotion',
				'foreignKey' => 'prod_gas_article_id',
				'dependent' => false,
				'conditions' =>  '',
				'fields' => '',
				'order' => '',
				'limit' => '',
				'offset' => '',
				'exclusive' => '',
				'finderQuery' => '',
				'counterQuery' => ''
		)
	);

	public function beforeSave($options = []) {
	
		if (!empty($this->data['ProdGasArticle']['qta']))
    	    $this->data['ProdGasArticle']['prezzo'] = $this->importoToDatabase($this->data['ProdGasArticle']['prezzo']);
        if (!empty($this->data['ProdGasArticle']['qta']))
        	$this->data['ProdGasArticle']['qta'] = $this->importoToDatabase($this->data['ProdGasArticle']['qta']);
	    
	    return true;
	}

	public function afterFind($results, $primary = false) {

		foreach ($results as $key => $val) {
			if(!empty($val)) {

				if(isset($val['ProdGasArticle']['prezzo'])) {
					$results[$key]['ProdGasArticle']['prezzo_'] = number_format($val['ProdGasArticle']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasArticle']['prezzo_e'] = $results[$key]['ProdGasArticle']['prezzo_'].' &euro;';
				}
				else
					/*					 * se il find() arriva da $hasAndBelongsToMany					*/
				 if(isset($val['prezzo'])) {					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';				}				
				/*
				 * qta, da 1.00 a 1
				 * 		da 0.75 a 0,75  
				 * */
				if(isset($val['ProdGasArticle']['qta'])) {
					$qta = str_replace(".", ",", $val['ProdGasArticle']['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['ProdGasArticle']['qta_'] = $qta;
				}
				else
				/*				 * se il find() arriva da $hasAndBelongsToMany				*/	
				if(isset($val['qta'])) {					$qta = str_replace(".", ",", $val['qta']);					$arrCtrlTwoZero = explode(",",$qta);					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];					$results[$key]['qta_'] = $qta;				}
			}
		}
		
		return $results;
	}	
}