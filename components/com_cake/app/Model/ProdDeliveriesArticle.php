<?php
App::uses('AppModel', 'Model');
App::import('Model', 'ProdDeliveriesArticleMultiKey');

class ProdDeliveriesArticle extends ProdDeliveriesArticleMultiKey {

	/*
	 * estraggo tutti gli articoli di una consegna ed EVENTUALI acquisti (ProdCart) di un utente
	* fitrando per ArticleType, Article.name, Article.category_id
	*
	* 	ProdDeliveries::ecomm() in front-end per l'ecommerce
	*/
	public function getArticoliEventualiAcquistiInConsegna($user, $options) {
	
		$results = array();
	
		try {
			if(!isset($options['order'])) $options['order'] = 'Article.name ASC';
				
			$sql = "SELECT
						ProdDeliveriesArticle.*,Article.*";
			if(isset($options['conditions']['ProdCart.user_id']))
				$sql .= ",ProdCart.* ";
			if(isset($options['conditions']['ArticleArticleTypeId.article_type_id'])) $sql .= ",ArticlesArticlesType.article_type_id ";
			$sql .= "FROM ".
					Configure::read('DB.prefix')."articles AS Article, ";
			if(isset($options['conditions']['ArticleArticleTypeId.article_type_id'])) $sql .= Configure::read('DB.prefix')."articles_articles_types ArticlesArticlesType, ";
			$sql .= Configure::read('DB.prefix')."prod_deliveries_articles AS ProdDeliveriesArticle ";
			if(isset($options['conditions']['ProdCart.user_id'])) {
				$sql .= " LEFT JOIN ".Configure::read('DB.prefix')."prod_carts AS ProdCart ON ".
						"(ProdCart.organization_id = ProdDeliveriesArticle.organization_id AND ProdCart.prod_delivery_id = ProdDeliveriesArticle.prod_delivery_id AND ProdCart.article_organization_id = ProdDeliveriesArticle.article_organization_id AND ProdCart.article_id = ProdDeliveriesArticle.article_id ".
						"AND ProdCart.user_id = ".$options['conditions']['ProdCart.user_id']."
						 AND ProdCart.deleteToReferent = 'N')";
			}
			$sql .= "WHERE
					ProdDeliveriesArticle.organization_id = ".$user->organization['Organization']['id']."
					AND Article.organization_id = ProdDeliveriesArticle.article_organization_id
					AND ProdDeliveriesArticle.article_id = Article.id
					AND ProdDeliveriesArticle.stato != 'N'
					AND Article.stato = 'Y'
					AND ProdDeliveriesArticle.prod_delivery_id = ".$options['conditions']['ProdDeliveriesArticle.prod_delivery_id'];
				
			if(isset($options['conditions']['ArticleArticleTypeId.article_type_id']))
				$sql .= " AND ArticlesArticlesType.organization_id = ".$user->organization['Organization']['id']."
						  AND ArticlesArticlesType.article_type_id IN (".$options['conditions']['ArticleArticleTypeId.article_type_id'].")
						  AND Article.id = ArticlesArticlesType.article_id ";
				
			if(isset($options['conditions']['Article.name']))
				$sql .= " AND lower(Article.name) LIKE '%".strtolower(addslashes($options['conditions']['Article.name']))."%'";
				
			/*
			 * filtro un solo ordine AjaxProdCartComtroller::__managementCart()
			*/
			if(isset($options['conditions']['Article.organization_id']))
				$sql .= " AND Article.organization_id = ".$options['conditions']['Article.organization_id'];
			if(isset($options['conditions']['Article.id']))
				$sql .= " AND Article.id = ".$options['conditions']['Article.id'];
	
			// Organization.hasFieldArticleCategoryId
			if(isset($options['conditions']['Article.category_id']))
				$sql .= " AND Article.category_id = ".$options['conditions']['Article.category_id'];
				
			$sql .= " ORDER BY ".$options['order'];
			// echo '<br />'.$sql;
			$results = $this->query($sql);
				
			/*
			 * applico metodi afterFind()
			*/
			foreach ($results as $numResult => $result) {
	
				/*
				 * Article
				*/
				$results[$numResult]['Article']['prezzo_'] = number_format($result['Article']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results[$numResult]['Article']['prezzo_e'] = $results[$numResult]['Article']['prezzo_'].' &euro;';
	
				$qta = str_replace(".", ",", $result['Article']['qta']);
				$arrCtrlTwoZero = explode(",",$qta);
				if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
				$results[$numResult]['Article']['qta_'] = $qta;
	
				/*
				 * ProdDeliveriesArticle
				*/
				$results[$numResult]['ProdDeliveriesArticle']['prezzo_'] = number_format($result['ProdDeliveriesArticle']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
				$results[$numResult]['ProdDeliveriesArticle']['prezzo_e'] = $results[$numResult]['ProdDeliveriesArticle']['prezzo_'].' &euro;';
	
				/*
				 * ProdCart
				*/
	
			} // foreach ($results as $numResult => $result)
				
			/*
				echo "<pre>";
			print_r($results);
			echo "</pre>";
			*/
	
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}
	
		return $results;
	}
	
	/*
	 * estraggo SOLO gli articoli acquistati da un utente in base alla prodDelivery
	*      $conditions['ProdCart.user_id'] e $conditions['User.id'] necessario!
	*
	*  ProdDeliveries::tabsUserCart()   carrello
	*/
	public function getArticoliDellUtenteInConsegna($user, $conditions, $orderBy=null, $limit=null) {
	
		if((!isset($conditions['ProdCart.user_id']) || empty($conditions['ProdCart.user_id'])) &&
		(!isset($conditions['User.id']) || empty($conditions['User.id'])))
			die("Errore getProdDeliveriesArticleInProdDeliveryAndProdCartsByUserId conditions['ProdCart.user_id'] o conditions['User.id'] obbligatori");
	
		if(isset($orderBy['ProdDeliveriesArticle'])) $order = $orderBy['ProdDeliveriesArticle'];
		else
		if(isset($orderBy['ProdCartPreview'])) $order = $orderBy['ProdCartPreview'];
		else
		if(isset($orderBy['Article'])) $order = $orderBy['Article'];
		else $order = 'Article.name ASC';
	
		App::import('Model', 'ProdCart');
		$ProdCart = new ProdCart();
	
		$options['conditions'] = array('ProdCart.organization_id' => $user->organization['Organization']['id'],
										'ProdDeliveriesArticle.stato != ' => 'N',
										'Article.stato' => 'Y'
										);

		/*
		 * solo per il ProdCartPreview (box in front-end che compare dopo un acquisto)
		* 		filtro per lo stato dell'ordine
		* per il Carrello mi filtra il Tab della Consegna
		*/
		if(isset($orderBy['ProdCartPreview'])) {
			$options['conditions'] += array('(ProdDelivery.prod_delivery_state_id = '.Configure::read('OPEN').' OR ProdDelivery.prod_delivery_state_id = '.Configure::read('PROCESSED-BEFORE-DELIVERY').')');
		}
		
		
		if(isset($conditions['ProdDelivery.id']))               $options['conditions'] += array('ProdCart.prod_delivery_id' => $conditions['ProdDelivery.id']);
		if(isset($conditions['ProdCart.prod_delivery_id']))     $options['conditions'] += array('ProdCart.prod_delivery_id' => $conditions['ProdCart.prod_delivery_id']);
		if(isset($conditions['ProdCart.article_id']))           $options['conditions'] += array('ProdCart.article_id' => $conditions['ProdCart.article_id']);
		if(isset($conditions['ProdCart.user_id']))              $options['conditions'] += array('ProdCart.user_id' => $conditions['ProdCart.user_id']);
		$options['recursive'] = 0;
		$options['order'] = $order;
		if(!empty($limit)) $options['limit'] = $limit;
			
		$results = $ProdCart->find('all', $options);
			
		return $results;
	}
	
	/*
	 * estraggo SOLO gli articoli acquistati da TUTTI gli utente in base alla consegna
	*
	*  Ajax::admin_view_articles() quando e chi ha acquistato un articolo
	*  ExportDocs::admin_exportToReferent() tutti gli articoli di un ordine aggregati per produttore
	*  										tutti gli articoli di un ordine aggregati per utenti
	*/

	public function getArticoliAcquistatiDaUtenteInConsegna($user, $conditions, $orderBy=null) {
			
		if(isset($orderBy['ProdDeliveriesArticle'])) $order = $orderBy['ProdDeliveriesArticle'];
		else
		if(isset($orderBy['ProdCartPreview'])) $order = $orderBy['ProdCartPreview'];
		else
		if(isset($orderBy['Article'])) $order = $orderBy['Article'];
		else
		if(isset($orderBy['User'])) $order = $orderBy['User'];
		else $order = 'Article.name ASC, User.name ';
	
		App::import('Model', 'ProdCart');
		$ProdCart = new ProdCart();
	
		$ProdCart->unbindModel(array('belongsTo' => array('ProdDelivery')));
		$options['conditions'] = array('ProdCart.organization_id' => $user->organization['Organization']['id'],
										'ProdDeliveriesArticle.stato != ' => 'N',
										'Article.stato' => 'Y'
		);
		if(isset($conditions['ProdDeliveriesArticle.order_id']))     $options['conditions'] += array('ProdCart.order_id' => $conditions['ProdDeliveriesArticle.order_id']);
		if(isset($conditions['ProdDelivery.id']))     				 $options['conditions'] += array('ProdCart.prod_delivery_id' => $conditions['ProdCart.prod_delivery_id']);
		if(isset($conditions['ProdDeliveriesArticle.article_id']))   $options['conditions'] += array('ProdCart.article_id' => $conditions['ProdDeliveriesArticle.article_id']);
		if(isset($conditions['Article.id']))                 $options['conditions'] += array('ProdCart.article_id' => $conditions['Article.id']);
		if(isset($conditions['ProdCart.user_id']))               $options['conditions'] += array('ProdCart.user_id' => $conditions['ProdCart.user_id']);
		if(isset($conditions['ProdCart.deleteToReferent']))      $options['conditions'] += array('ProdCart.deleteToReferent' => $conditions['ProdCart.deleteToReferent']);
		if(isset($conditions['User.id']))                    $options['conditions'] += array('ProdCart.user_id' => $conditions['User.id']);
	
		$options['recursive'] = 0;
		$options['order'] = $order;
	
		$results = $ProdCart->find('all', $options);
		return $results;
	}
	
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
		'prod_delivery_id' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'article_organization_id' => array(
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
		'qta_cart' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'pezzi_confezione' => array(
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
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
			'numeric' => array(
				'rule' => array('numeric'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'alert_to_qta' => array(
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
			'Article' => array(
					'className' => 'Article',
					'foreignKey' => 'article_id',
					'conditions' => 'Article.organization_id = ProdDeliveriesArticle.article_organization_id',
					'fields' => '',
					'order' => ''
			),
			'ProdDelivery' => array(
					'className' => 'ProdDelivery',
					'foreignKey' => 'prod_delivery_id',
					'conditions' => 'ProdDelivery.organization_id = ProdDeliveriesArticle.organization_id',
					'fields' => '',
					'order' => ''
			),
			'ProdCart' => array(
					'className' => 'ProdCart',
					'foreignKey' => '',
					'conditions' => 'ProdCart.organization_id = ProdDeliveriesArticle.organization_id AND ProdCart.prod_delivery_id = ProdDeliveriesArticle.prod_delivery_id AND ProdCart.article_organization_id = ProdDeliveriesArticle.article_organization_id AND ProdCart.article_id = ProdDeliveriesArticle.article_id',
					'fields' => '',
					'order' => '',
			),
	);
	
	public function afterFind($results, $primary = true) {
	
		foreach ($results as $key => $val) {
			if(!empty($val)) {
				if (isset($val['ProdDeliveriesArticle']['prezzo'])) {
					$results[$key]['ProdDeliveriesArticle']['prezzo_'] = number_format($val['ProdDeliveriesArticle']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdDeliveriesArticle']['prezzo_e'] = $results[$key]['ProdDeliveriesArticle']['prezzo_'].' &euro;';
				}
				else
					/*
					 * se il find() arriva da $hasAndBelongsToMany
				*/
					if(isset($val['prezzo'])) {
					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';
				}
			}
		}
		return $results;
	}	
}
