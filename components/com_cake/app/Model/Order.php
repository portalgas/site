<?php
App::uses('AppModel', 'Model');

class Order extends AppModel {

	public $virtualFields = ['name' => "CONCAT_WS(' - ',DATE_FORMAT(Order.data_inizio, '%W, %e %M %Y'),DATE_FORMAT(Order.data_fine, '%W, %e %M %Y'))"]; 		
		
	/*
	 * ctrl se l'utente e' referente dell'ordine
	 */
	public function aclReferenteSupplierOrganization($user, $order_id) {

		$options = [];
		$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['recursive'] = -1;
		$options['fields'] = ['Order.supplier_organization_id'];
		
		$results = [];
		$results = $this->find('first', $options);
		$supplier_organization_id = $results['Order']['supplier_organization_id'];
		
		if(!in_array($supplier_organization_id,explode(",",$user->get('ACLsuppliersIdsOrganization'))))
			return false;
		else
			return true;
	}
	
	public function getOrderPermissionToEditUtente($order, $desOrderStateCode) {
		if($order['state_code']=='OPEN' || $order['state_code']=='RI-OPEN-VALIDATE')
			return true; 
		else
			return false;
	}

	public function getOrderPermissionToEditReferente($order, $desOrderStateCode) {

		$permission_oks = ['PROCESSED-BEFORE-DELIVERY', 'PROCESSED-POST-DELIVERY', 'INCOMING-ORDER'];
		$permission_des_oks = ['BEFORE-TRASMISSION', 'REFERENT-WORKING'];

		if (in_array($order['state_code'], $permission_oks)) {
			if(empty($desOrderStateCode))
				return true;
			else {
				if (in_array($desOrderStateCode, $permission_des_oks)) 
					return true;
				else
					return false;
			}
		}
		else
			return false;
	}
	
	public function getOrderPermissionToEditCassiere($order, $desOrderStateCode) {
		
		if($order['state_code']=='PROCESSED-ON-DELIVERY')
			return true; 
		else
			return false;
	}
	
	public function getOrderPermissionToEditTesoriere($order, $desOrderStateCode) {
		if($order['state_code']=='PROCESSED-TESORIERE')
			return true; 
		else
			return false;
	}
		
	/*
	 * ctrl se la validazione del carrello e' abilitata (ArticlesOrder.pezzi_confezione > 1) per la gestione dei colli
	*/
	public function isOrderToValidate($user, $order_id) {
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
			
		$conditions = ['Order.id' => (int)$order_id,
					   'ArticlesOrder.pezzi_confezione' => '1'];
	
		$results = [];
		try {
			$results = $ArticlesOrder->getArticlesOrdersInOrder($user ,$conditions);
			if(empty($results))
				$isToValidate = false;
			else
				$isToValidate = true;
			
			return $isToValidate;
				
		}
		catch (Exception $e) {
			CakeLog::write('error',$e);
		}
	}
	
	/*
	 * ctrl se l'ordine e' da monitorare (ArticlesOrder.qta_massima_order > 0) per la gestione dei colli
	*/
	public function isOrderToQtaMassima($user, $order_id) {
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
			
		$conditions = ['Order.id' => (int)$order_id,
						'ArticlesOrder.qta_massima_order' => '0'];
	
		$results = [];
		try {
			$results = $ArticlesOrder->getArticlesOrdersInOrder($user ,$conditions);
			if(empty($results))
				$isToValidate = false;
			else
				$isToValidate = true;
			
			return $isToValidate;
				
		}
		catch (Exception $e) {
			CakeLog::write('error',$e);
		}
	}	
	

	/*
	 * ctrl se l'ordine e' da monitorare (ArticlesOrder.qta_minima_order > 0) 
	*/
	public function isOrderToQtaMinimaOrder($user, $order_id) {
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
			
		$conditions =['Order.id' => (int)$order_id,
							'ArticlesOrder.qta_minima_order' => '0'];
	
		$results = [];
		try {
			$results = $ArticlesOrder->getArticlesOrdersInOrder($user ,$conditions);
			if(empty($results))
				$isToValidate = false;
			else
				$isToValidate = true;
				
			return $isToValidate;
	
		}
		catch (Exception $e) {
			CakeLog::write('error',$e);
		}
	}

	/*
	 * ctrl data_inizio con data_oggi
	 * 
	 * 		se data_inizio < data_oggi NON invio mail
	 * 		se data_inizio = data_oggi mail_open_send = Y, Cron::mailUsersOrdersOpen domani invio mail
	 * 		se data_inizio > data_oggi mail_open_send = N, Cron::mailUsersOrdersOpen invio mail
	 */		
	public function setOrderMailOpenSend($user, $request, $debug=false) {
		$mail_open_send = 'Y';
		
		/*
		 * ctrl che il produttore scelto abbia SuppliersOrganization.mail_order_open = Y
		 */
        App::import('Model', 'SuppliersOrganization');
        $SuppliersOrganization = new SuppliersOrganization;

        $option = [];
        $option['conditions'] = ['SuppliersOrganization.organization_id' => $user->organization['Organization']['id'],
        						 'SuppliersOrganization.id' => $request['Order']['supplier_organization_id']];
        $option['fields'] = ['SuppliersOrganization.mail_order_open'];
        $option['recursive'] = -1;
        $results = $SuppliersOrganization->find('first', $option);
		if($debug) {
			echo "<pre>Order::__setOrderMailOpenSend() \n ";
			print_r($option);
			print_r($results);
			echo "</pre>";			
		}
		if($results['SuppliersOrganization']['mail_order_open']=='N')
			$mail_open_send = 'N';	
		 else {
			$data_inizio_db = $request['Order']['data_inizio_db'];	
			$data_oggi = date("Y-m-d");
			if ($data_inizio_db == $data_oggi)
				$mail_open_send = 'Y';
			else 
				$mail_open_send = 'N';	
		}
				
		if($debug) {
			echo "<br />Order::setOrderMailOpenSend() \n ";
			echo "mail_open_send ".$mail_open_send;			
		}
	
		return $mail_open_send;
	}   	
	
	/*
	 * se riapro ordine cancello le modifiche del referente
	 * 		da Order::edit 
	 * 		da Order::edit_validation_cart (per gestione colli)
	 * 		ribalto qta_forzato in qta
	 * 		cancello importo_forzato
	 * 		cancello summary_order
	 * 		cancello summary_order_trasport
	 * 		cancello Order.trasport
	 * 		ricalcolo le ArticlesOrder.qta_cart e ArticlesOrder.stato (QTAMAXORDER)
	 * 
	 *      si puo' fare solo Order.state_code = PROCESSED-BEFORE-DELIVERY => tutti i dati vuoti!
	 *      in PROCESSED-POST-DELIVERY se setto Order.data_fine > Delivery.data non mi viene permesso
	 */
	public function riapriOrdine($user, $order_id, $debug) {

		/*
		 * ribalto qta_forzato in qta dove il referente ha fatto modifiche
		*/
		$sql = "UPDATE 
					".Configure::read('DB.prefix')."carts
				SET
					qta = qta_forzato
				WHERE
				    organization_id = ".(int)$user->organization['Organization']['id']."
					and qta_forzato > 0
				    and order_id = ".(int)$order_id;
		self::d($sql, $debug);
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
		
		/*
		 * cancello qta_forzato, importo_forzato
		 */
		$sql = "UPDATE 
					".Configure::read('DB.prefix')."carts
				SET
					importo_forzato = '0.00',
					qta_forzato = 0  
				WHERE
				    organization_id = ".(int)$user->organization['Organization']['id']."
				    and order_id = ".(int)$order_id;
		self::d($sql, $debug);
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}
				
		App::import('Model', 'SummaryOrder');
		$SummaryOrder = new SummaryOrder;
		
		$SummaryOrder->delete_to_order($user, $order_id, $debug);
		
		App::import('Model', 'SummaryOrderTrasport');
		$SummaryOrderTrasport = new SummaryOrderTrasport;
		
		$SummaryOrderTrasport->delete_importo_to_order($user, $order_id, $debug);
		
		/*
		 * ricalcolo le ArticlesOrder.qta_cart e ArticlesOrder.stato (QTAMAXORDER)
		 */
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		 
		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
								  'ArticlesOrder.order_id' => $order_id];
		$options['recursive'] = -1;
		$results = $ArticlesOrder->find('all', $options);	 
		foreach ($results as $result) {
			 
			$organization_id = $result['ArticlesOrder']['organization_id'];
			$order_id = $result['ArticlesOrder']['order_id'];
			$article_organization_id = $result['ArticlesOrder']['article_organization_id'];
			$article_id = $result['ArticlesOrder']['article_id'];
		
			if($debug) echo '<pre>';
			$ArticlesOrder->aggiornaQtaCart_StatoQtaMax($organization_id, $order_id, $article_organization_id, $article_id, $debug);
			if($debug) echo '</pre>';
		}
	}
	
	
	/*
	 * restituisco il type_draw di un ordine: SIMPLE / COMPLETE
	 * 
	 * se ho ArticlesOrder <
	 * se ho tutte le img
	*/
	public function getTypeDraw($user, $order_id, $debug=false) {
	
		$type_draw = 'SIMPLE';
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
			
		$ArticlesOrder->unbindModel(['belongsTo' => ['Cart', 'Order']]);
		
		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
									'ArticlesOrder.order_id' => $order_id,
									'ArticlesOrder.stato != ' => 'N',
									'Article.stato' => 'Y'];
		$options['recursive'] = 1;
		$totArticlesOrders = $ArticlesOrder->find('count', $options);
		self::d("totArticlesOrders (totale articoli associati all'ordine) ".$totArticlesOrders, $debug);
		
		$ArticlesOrder->unbindModel(['belongsTo' => ['Cart', 'Order']]);
		
		$options['conditions'] += ['not' => ['Article.img1' => null]];
		$options['recursive'] = 1;
		$totArticlesImg = $ArticlesOrder->find('count', $options);
		self::d("totArticlesImg (totale articoli con IMG associati all'ordine) ".$totArticlesImg, $debug);

		/*
		 * % di articoli con IMG in un ordine per la modalita' COMPLETE: se - del 80% non ha img e' SIMPLE 
		 */
		if($totArticlesOrders > 0) {
			$perc_article_con_img_tollerata = round(($totArticlesOrders * Configure::read('ArticlesOrderWithImgToTypeDrawComplete') / 100));

			/*
			 * se l'ordine ha pochi (Configure::read('ArticlesOrderToTypeDrawComplete')) articoli e
			* 		tutti con le immagini
			*/
			if( ($totArticlesImg >= $perc_article_con_img_tollerata) &&
			     $totArticlesOrders <= Configure::read('ArticlesOrderToTypeDrawComplete'))
				$type_draw = 'COMPLETE';
			else
				$type_draw = 'SIMPLE';
		}
		else
			$type_draw = 'SIMPLE';
		
		self::d("perc_article_con_img_tollerata: se ci sono almeno ".$perc_article_con_img_tollerata." articoli con img => COMPLETE", $debug);
		self::d("Configure::read(ArticlesOrderWithImgToTypeDrawComplete) ".Configure::read('ArticlesOrderWithImgToTypeDrawComplete'), $debug);
		self::d("Configure::read(ArticlesOrderToTypeDrawComplete) ".Configure::read('ArticlesOrderToTypeDrawComplete'), $debug);
		self::d("type_draw $type_draw", $debug);
		
		if($debug)  exit;
		
		return $type_draw;
	}
	
	/*
	 * in base a cosa restituisce getTypeDraw() (COMPLETE o SIMPLE) aggiorno l'ordine
	 */
	public function updateTypeDraw($user, $order_id, $debug=false) {
		
		$type_draw = $this->getTypeDraw($user, $order_id, $debug);
		
		$sql = "UPDATE
					".Configure::read('DB.prefix')."orders
				SET
					type_draw = '".$type_draw."'
				WHERE
					organization_id = ".(int)$user->organization['Organization']['id']."
				    and id = ".(int)$order_id;
		self::d($sql, $debug);		
		try {
			$results = $this->query($sql);
				
		}
		catch (Exception $e) {
			CakeLog::write('error',$e);
			return false;
		}
		
		return true;
	}
	
	/*
	 * se c'e' stato un cambiamento ($results['Order']['typeGest']!=$results['Order']['OldResults'])
	 * typeGest = 'AGGREGATE', 'SPLIT'
	 */
	public function gestTypeGest($user, $results, $OldResults, $debug=false) {
	
		$aggregate=false;
		$split=false;
			
					
		/*
		 * se c'e' stato un cambiamento 
		 *    cancello il precedente
		 */
		if($results['Order']['typeGest']!=$OldResults['Order']['typeGest']) {
		
			switch ($results['Order']['typeGest']) {
				case "AGGREGATE":
					$split=true;
				break;
				case "SPLIT":
					$aggregate=true;
				break;
				default:
					$aggregate=true;
					$split=true;				
				break;
			}
		}
		
		/*
		 * delete summary_orders
		 */
		if($aggregate) {
			App::import('Model', 'SummaryOrder');
			$SummaryOrder = new SummaryOrder;
			
			$SummaryOrder->delete_to_order($user, $results['Order']['id'], $debug);
		}
				
		/*
		 * delete carts_splits e ArticlesOrder.importo_forzato
		 */
		if($split) {
			App::import('Model', 'CartsSplit');
			$CartsSplit = new CartsSplit;
				
			$CartsSplit->delete_to_order($user, $results['Order']['id'], $debug);		
		} 
	}

    /*
     * estrae l'importo totale degli acquisti di un ordine e lo salvo sull'ordine
     * per evitare discordanze (ex rich di pagamento con ordini con totImporti diversi a SummaryOrderS)
    */
    public function setTotImporto($user, $order_id, $debug=false) {

        $tot_importo = $this->getTotImporto($user, $order_id, $debug);
        if($tot_importo>0) {

            $options = [];
            $options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'], 'Order.id' => $order_id];
            $options['recursive'] = -1;

            $results = [];
            $results = $this->find('first', $options);
            $results['Order']['nota'] = 'test';
            $results['Order']['tot_importo'] = $tot_importo;

            if(!$this->save($results)) {
                CakeLog::write('error','Order::setTotImporto()');
                CakeLog::write('error',$this->validationErrors);
            }
        }
    }

	/*
	 * estrae l'importo totale degli acquisti di un ordine
	 * ctrl eventuali (come ExporDoc:getCartCompile() )
	 * 		- totali impostati dal referente (SummaryOrder) in Carts::managementCartsGroupByUsers
	 * 		- spese di trasporto  (SummaryOrderTrasport)
	 *
	 *  return $importo_totale gia' formattato 1.000,00
	 */
	public function getTotImporto($user, $order_id, $debug=false) {
		
		if(empty($user))
			self::x("Order::getTotImporto() user empty!");
			
		$importo_totale = 0;
		
		/*
		 * dati dell'ordine
		 */
		App::import('Model', 'Order');
		$Order = new Order;
		
		$options = [];
		$options['conditions'] = ['Order.organization_id' => $user->organization['Organization']['id'], 'Order.id' => $order_id];
		$options['recursive'] = -1;
		$order = $Order->find('first', $options);
	
		/*
		 * SummaryOrderAggregate: estraggo eventuali dati aggregati 
		 */
		App::import('Model', 'SummaryOrderAggregate');
		$SummaryOrderAggregate = new SummaryOrderAggregate;
		
		$summaryOrderAggregateResults = $SummaryOrderAggregate->select_to_order($user, $order_id); // se l'ordine e' ancora aperto e' vuoto
		
		if(!empty($summaryOrderAggregateResults)) {
			foreach ($summaryOrderAggregateResults as  $summaryOrderAggregateResult) 
				$importo_totale += $summaryOrderAggregateResult['SummaryOrderAggregate']['importo'];
				
			self::l("SummaryOrderAggregate->importo_totale ".$importo_totale, $debug);		
		}
		else {
			/*
			 * estrae l'importo totale degli acquisti (qta e qta_forzato, importo_forzato) di un ordine
			*/
			App::import('Model', 'Cart');
			$Cart = new Cart;
			
			$conditions['Order.id'] = $order_id;
			$importo_totale = $Cart->getTotImporto($user, $conditions, $debug);
			
			self::l("Cart->getTotImporto ".$importo_totale, $debug);	
		}
			
		/*
		 * trasporto
		*/
		if($order['Order']['hasTrasport']=='Y') 
			$importo_totale += $order['Order']['trasport'];
			
		if($order['Order']['hasCostMore']=='Y') 
			$importo_totale += $order['Order']['cost_more'];
			
		if($order['Order']['hasCostLess']=='Y') 
			$importo_totale -= $order['Order']['cost_less'];
		
		/* 
		 *  bugs float: i float li converte gia' con la virgola!  li riporto flaot
		 */
		if(strpos($importo_totale,',')!==false)  $importo_totale = str_replace(',','.',$importo_totale);
		
		self::l("Order->getTotImporto ".$importo_totale, $debug);	
		
		return $importo_totale;
	}
	
	/*
	 * estrae gli ordini con la consegna ancora da definire (Delivery.sys = Y)
	*/
	public function getOrdersDeliverySys($user) {
		$options = [];
		$options['conditions'] =  ['Order.organization_id' => $user->organization['Organization']['id'],
								   'Delivery.organization_id'=>$user->organization['Organization']['id'],
								   'Delivery.sys'=> 'Y'];
		$options['order'] = ['Order.data_inizio'];
		$options['recursive'] = 0;
		
		$results = $this->find('all', $options);
		
		return $results;
	}

	/*
	 *  calcola il totale quantita di un ordine 
	 *  	per il confronto con Order.quantita_massima
	 */
	 function getTotQuantitaArticlesOrder($user, $orderResult, $debug=false) {
	
		$sqlUmRange = '';
		if($orderResult['Order']['qta_massima_um']=='KG')
			$sqlUmRange = " AND (Article.um = 'KG' OR Article.um = 'HG' OR Article.um = 'GR') "; 
		else
		if($orderResult['Order']['qta_massima_um']=='LT')
			$sqlUmRange = " AND (Article.um = 'LT' OR Article.um = 'DL' OR Article.um = 'ML') "; 
		else 
		if($orderResult['Order']['qta_massima_um']=='PZ')
			$sqlUmRange = " AND Article.um = 'PZ' "; 
			
		$sql = "SELECT Article.name, Article.qta, Article.um, ArticlesOrder.qta_cart  
				FROM ".Configure::read('DB.prefix')."articles_orders as ArticlesOrder, 
					 ".Configure::read('DB.prefix')."articles as Article 
				WHERE ArticlesOrder.article_id = Article.id  
					  AND ArticlesOrder.article_organization_id = Article.organization_id 
					  AND ArticlesOrder.stato != 'N'
					  AND Article.stato = 'Y' 
					  AND ArticlesOrder.qta_cart > 0 
					  AND ArticlesOrder.order_id = ".$orderResult['Order']['id']." 
					  AND ArticlesOrder.organization_id = ".$user->organization['Organization']['id'];
		$sql .= $sqlUmRange;
		self::d($sql, $debug);
		$articlesResults = $this->query($sql);
		
		/*
		echo "<pre> ";
		print_r($articlesResults);
		echo "</pre>";
		*/
		
		/*
		 *  loop per calcolare la quantita massima (in GR o ML o PZ)
		 */
		$totQuantita = 0;
		foreach ($articlesResults as $numArticlesResults => $articlesResult) {
			
			if($debug) {
				echo "<br />".$articlesResult['Article']['name']." da ".$articlesResult['Article']['qta']." ".$articlesResult['Article']['um']." - qta_cart ".$articlesResult['ArticlesOrder']['qta_cart']." : ".$totQuantita;
			}
				
			if($articlesResult['Article']['um']=='KG' || $articlesResult['Article']['um']=='LT')
				$articlesResult['ArticlesOrder']['qta_cart'] = ($articlesResult['ArticlesOrder']['qta_cart'] * 1000);
			else
			if($articlesResult['Article']['um']=='HG' || $articlesResult['Article']['um']=='DL')
				$articlesResult['ArticlesOrder']['qta_cart'] = ($articlesResult['ArticlesOrder']['qta_cart'] * 100);
				
			$totQuantita += ($articlesResult['Article']['qta'] * $articlesResult['ArticlesOrder']['qta_cart']); 
		}
		
		if($debug) 
			echo '<br /> TOT QUANTITA '.$totQuantita;

		return $totQuantita;
	}	

			
	/*
	 *  calcola il totale importo di un ordine 
	 *  	per il confronto con Order.importo_massimo
	 */
	public function getTotImportoArticlesOrder($user, $order_id, $debug = false) {
			
		$sql = "SELECT sum(Article.prezzo * ArticlesOrder.qta_cart) as totImporto 
				FROM ".Configure::read('DB.prefix')."articles_orders as ArticlesOrder, 
					 ".Configure::read('DB.prefix')."articles as Article 
				WHERE ArticlesOrder.article_id = Article.id  
					  AND ArticlesOrder.article_organization_id = Article.organization_id 
					  AND ArticlesOrder.stato != 'N'
					  AND Article.stato = 'Y' 
					  AND ArticlesOrder.order_id = ".$order_id." 
					  AND ArticlesOrder.organization_id = ".$user->organization['Organization']['id'];
		self::d($sql, $debug);
		$totImporto = current($this->query($sql));
		
		return $totImporto[0]['totImporto'];
	}
	
	public $validate = array(
		'supplier_organization_id' => array(
			'empty' => array(
					'rule' => array('naturalNumber', false),
					'message' => "Scegli il produttore da associare all'ordine",
					),
			'totArticles' => array(
					'rule'       =>  array('tot_articles'),
					'message'    => 'Il produttore scelto non ha articoli che si possono associare ad un ordine',
					),
			'OrderDuplicate' => array(
					'rule'       =>  array('order_duplicate'),
					'message'    => 'Esiste già un ordine del produttore sulla consegna scelta',
					)
		),
		'owner_articles' => [
			'notempty' => [
				'rule' => ['notBlank'],
				'message' => "Indica il gestore del listino articoli",
			],
		],
		'owner_organization_id' => [
			'notempty' => [
				'rule' => ['notBlank'],
				'message' => "Indica il gestore del listino articoli",
			],
		],
		'owner_supplier_organization_id' => [
			'notempty' => [
				'rule' => ['notBlank'],
				'message' => "Indica il gestore del listino articoli",
			],
		],		
		'delivery_id' => array(
			'rule' => array('naturalNumber', false),
			'message' => "Scegli la consegna da associare all'ordine",
		),
		'data_inizio' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
			'dateMinore' => array(
				'rule'       =>  array('date_comparison', '<=', 'data_fine'),
				'message'    => 'La data di apertura non può essere posteriore della data di chiusura',
			),
			'dateToDelivery' => array(
				'rule'       =>  array('date_comparison_to_delivery', '>'),
				'message'    => 'La data di apertura non può essere posteriore della data della consegna',
			),
		),
		'data_fine' => array(
			'date' => array(
				'rule'       => 'date',
				'message'    => 'Inserisci una data valida',
				'allowEmpty' => false
			),
			'dateMaggiore' => array(
				'rule'       =>  array('date_comparison', '>=', 'data_inizio'),
				'message'    => 'La data di chiusura non può essere antecedente della data di apertura',
			),
			'dateToDelivery' => array(
				'rule'       =>  array('date_comparison_to_delivery','>'),
				'message'    => 'La data di chiusura non può essere posteriore o uguale della data della consegna',
			),
			'dateToDesDataFineMax' => array(
				'rule'       =>  array('date_comparison','<=', 'des_data_fine_max'),
				'message'    => 'La data di chiusura non può essere posteriore alla data di chiusura dell\'ordine condisivo',
			),
		),
	);

	function date_comparison($field=[], $operator, $field2) {
		foreach( $field as $key => $value1 ){
			$value2 = $this->data[$this->alias][$field2];
			
			if(empty($value2))
				return true;
			
			if (!Validation::comparison($value1, $operator, $value2))
				return false;
		}
		return true;
	}

	function date_comparison_to_delivery($field=[], $operator) {
		foreach( $field as $key => $value ){
			if(isset($this->data[$this->alias]['delivery_id'])) { // capita se l'elenco delle consegne è vuoto
				$delivery_id = $this->data[$this->alias]['delivery_id'];
				$organization_id = $this->data[$this->alias]['organization_id'];
				 
				$this->Delivery->unbindModel(array('hasMany' => array('Order','Cart')));
				$delivery = $this->Delivery->read($delivery_id, $organization_id, 'data');
				$delivery_data = $delivery['Delivery']['data'];
			
				if (!Validation::comparison($delivery_data, $operator, $value))
					return false;
			}
			else
				return false;
		}
		return true;		
	}
	
	function tot_articles($field=[]) {
		
		/*
		 * se e' DES posso anche non avere articoli associati
		if(!empty($this->data[$this->alias]['des_order_id'])) 
			return true;
		 */
		 
		App::import('Model', 'SuppliersOrganization');
		$SuppliersOrganization = new SuppliersOrganization;
				
		foreach( $field as $key => $value) {
			$supplier_organization_id = $value;

			$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $this->data[$this->alias]['organization_id']]);
	
			App::import('Model', 'SuppliersOrganization');
			$SuppliersOrganization = new SuppliersOrganization;
			$articleCount = $SuppliersOrganization->getTotArticlesPresentiInArticlesOrder($tmp_user, $supplier_organization_id);			
		}
		
		if($articleCount==0)
			return false;
		else
			return true;		
	}
	
	function order_duplicate($field=[]) {
		foreach( $field as $key => $value) {

			$supplier_organization_id = $value;
			$tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $this->data[$this->alias]['organization_id']]);
			$delivery_id = $this->data[$this->alias]['delivery_id'];

			/*
			 * type_draw SIMPLE / COMPLETE / PROMOTION 
			 */
			 if(isset($this->data[$this->alias]['prod_gas_promotion_id']) && !empty($this->data[$this->alias]['prod_gas_promotion_id']))
			 	$type_draws = ['PROMOTION'];
			 else
			 	$type_draws = ['SIMPLE', 'COMPLETE'];
			 				
			App::import('Model', 'Order');
			$Order = new Order;
			
			$options = [];
			$options['conditions'] = ['Order.organization_id' => $tmp_user->organization['Organization']['id'],
									'Order.delivery_id' => $delivery_id,
									'Order.supplier_organization_id' => $supplier_organization_id,
									'Order.isVisibleBackOffice' => 'Y',
									'Order.type_draw' => $type_draws]; 
			/*
			 * per edit
			 */
			if(isset($this->data[$this->alias]['id']))
				$options['conditions'] += ['Order.id !=' => $this->data[$this->alias]['id']];
				
			$options['fields'] = ['Order.id'];
			$options['recursive'] = -1;
			// self::d($options);
			$results = $Order->find('first', $options);
		}
			
		if(!empty($results))
			return false;
		else
			return true;		
	}	
				
	public $belongsTo = array(
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = Order.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Delivery' => array(
			'className' => 'Delivery',
			'foreignKey' => 'delivery_id',
			'conditions' => 'Delivery.organization_id = Order.organization_id',
			'fields' => '',
			'order' => ''
		)						
	);

	public function afterFind($results, $primary = true) {
		
		App::import('Model', 'DesOrder');

		foreach ($results as $key => $val) {

			if(!empty($val)) {
				if (isset($val['Order']['data_inizio'])) {
					$results[$key]['Order']['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['Order']['data_inizio']);
					if(!empty($val['Order']['data_fine_validation']) && $val['Order']['data_fine_validation']!=Configure::read('DB.field.date.empty')) 
						$results[$key]['Order']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['Order']['data_fine_validation']);
					else
						$results[$key]['Order']['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['Order']['data_fine']);

					/* 
					 * estraggo stato dell'ordine DES
					 * */
					$desOrderStateCode = '';
					if(!empty($results[$key]['Order']['des_order_id'])) {
						$DesOrder = new DesOrder;
						$options = [];
						$options['fields'] = ['state_code'];
						$options['conditions'] = ['id' => $results[$key]['Order']['des_order_id']];
						$options['recursive'] = -1;
						$desOrderStateCode = $DesOrder->find('first', $options);
						if(!empty($desOrderStateCode))
							$desOrderStateCode = $desOrderStateCode['DesOrder']['state_code'];
					}
											
					$results[$key]['Order']['permissionToEditUtente']    = $this->getOrderPermissionToEditUtente($val['Order'], $desOrderStateCode);
					$results[$key]['Order']['permissionToEditReferente'] = $this->getOrderPermissionToEditReferente($val['Order'], $desOrderStateCode);
					$results[$key]['Order']['permissionToEditCassiere'] = $this->getOrderPermissionToEditCassiere($val['Order'], $desOrderStateCode);
					$results[$key]['Order']['permissionToEditTesoriere'] = $this->getOrderPermissionToEditTesoriere($val['Order'], $desOrderStateCode);

					$results[$key]['Order']['data_inizio_'] = date('d',strtotime($val['Order']['data_inizio'])).'/'.date('n',strtotime($val['Order']['data_inizio'])).'/'.date('Y',strtotime($val['Order']['data_inizio']));
					$results[$key]['Order']['data_fine_'] = date('d',strtotime($val['Order']['data_fine'])).'/'.date('n',strtotime($val['Order']['data_fine'])).'/'.date('Y',strtotime($val['Order']['data_fine']));
					$results[$key]['Order']['data_fine_validation_'] = date('d',strtotime($val['Order']['data_fine_validation'])).'/'.date('n',strtotime($val['Order']['data_fine_validation'])).'/'.date('Y',strtotime($val['Order']['data_fine_validation']));
					$results[$key]['Order']['tesoriere_data_pay_'] = date('d',strtotime($val['Order']['tesoriere_data_pay'])).'/'.date('n',strtotime($val['Order']['tesoriere_data_pay'])).'/'.date('Y',strtotime($val['Order']['tesoriere_data_pay']));

					$results[$key]['Order']['trasport_'] = number_format($val['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['trasport_e'] = $results[$key]['Order']['trasport_'].' &euro;';				

					$results[$key]['Order']['cost_more_'] = number_format($val['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['cost_more_e'] = $results[$key]['Order']['cost_more_'].' &euro;';

					$results[$key]['Order']['cost_less_'] = number_format($val['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['cost_less_e'] = $results[$key]['Order']['cost_less_'].' &euro;';

					$results[$key]['Order']['tesoriere_importo_pay_'] = number_format($val['Order']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['tesoriere_importo_pay_e'] = $results[$key]['Order']['tesoriere_importo_pay_'].' &euro;';

					$results[$key]['Order']['tesoriere_fattura_importo_'] = number_format($val['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['tesoriere_fattura_importo_e'] = $results[$key]['Order']['tesoriere_fattura_importo_'].' &euro;';

					$results[$key]['Order']['importo_massimo_'] = number_format($val['Order']['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['importo_massimo_e'] = $results[$key]['Order']['importo_massimo_'].' &euro;';

					$results[$key]['Order']['tot_importo_'] = number_format($val['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['tot_importo_e'] = $results[$key]['Order']['tot_importo_'].' &euro;';					
				}
				else 
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				 */
				if (isset($val['data_inizio'])) {
					$results[$key]['dayDiffToDateInizio'] = $this->utilsCommons->dayDiffToDate($val['data_inizio']);
					if(!empty($val['data_fine_validation']) && $val['data_fine_validation']!=Configure::read('DB.field.date.empty'))
						$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);
					else
						$results[$key]['dayDiffToDateFine']   = $this->utilsCommons->dayDiffToDate($val['data_fine']);
						
					/* 
					 * estraggo stato dell'ordine DES
					 * */
					$desOrderStateCode = '';
					if(!empty($val['des_order_id'])) {
						$DesOrder = new DesOrder;
						$options = [];
						$options['fields'] = ['state_code'];
						$options['conditions'] = ['id' => $val['des_order_id']];
						$options['recursive'] = -1;
						$desOrderStateCode = $DesOrder->find('first', $options);
						if(!empty($desOrderStateCode))
							$desOrderStateCode = $desOrderStateCode['DesOrder']['state_code'];
					}

					$results[$key]['permissionToEditUtente']    = $this->getOrderPermissionToEditUtente($val, $desOrderStateCode);
					$results[$key]['permissionToEditReferente'] = $this->getOrderPermissionToEditReferente($val, $desOrderStateCode);
					$results[$key]['permissionToEditCassiere'] = $this->getOrderPermissionToEditCassiere($val, $desOrderStateCode);
					$results[$key]['permissionToEditTesoriere'] = $this->getOrderPermissionToEditTesoriere($val, $desOrderStateCode);
						
					$results[$key]['data_inizio_'] = date('d',strtotime($val['data_inizio'])).'/'.date('n',strtotime($val['data_inizio'])).'/'.date('Y',strtotime($val['data_inizio']));
					$results[$key]['data_fine_'] = date('d',strtotime($val['data_fine'])).'/'.date('n',strtotime($val['data_fine'])).'/'.date('Y',strtotime($val['data_fine']));
					$results[$key]['data_fine_validation_'] = date('d',strtotime($val['data_fine_validation'])).'/'.date('n',strtotime($val['data_fine_validation'])).'/'.date('Y',strtotime($val['data_fine_validation']));
					$results[$key]['tesoriere_data_pay_'] = date('d',strtotime($val['tesoriere_data_pay'])).'/'.date('n',strtotime($val['tesoriere_data_pay'])).'/'.date('Y',strtotime($val['tesoriere_data_pay']));
				}	
				
				if(isset($val['Order']['trasport'])) {
					$results[$key]['Order']['trasport_'] = number_format($val['Order']['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['trasport_e'] = $results[$key]['Order']['trasport_'].' &euro;';
				}
				else 
				if(isset($val['trasport'])) {
					$results[$key]['trasport_'] = number_format($val['trasport'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['trasport_e'] = $results['Order']['trasport_'].' &euro;';
				}		

				if(isset($val['Order']['cost_more'])) {
					$results[$key]['Order']['cost_more_'] = number_format($val['Order']['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['cost_more_e'] = $results[$key]['Order']['cost_more_'].' &euro;';
				}
				else
				if(isset($val['cost_more'])) {
					$results[$key]['cost_more_'] = number_format($val['cost_more'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_more_e'] = $results['Order']['cost_more_'].' &euro;';
				}

				if(isset($val['Order']['cost_less'])) {
					$results[$key]['Order']['cost_less_'] = number_format($val['Order']['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['cost_less_e'] = $results[$key]['Order']['cost_less_'].' &euro;';
				}
				else
				if(isset($val['cost_less'])) {
					$results[$key]['cost_less_'] = number_format($val['cost_less'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['cost_less_e'] = $results['Order']['cost_less_'].' &euro;';
				}
				
				if(isset($val['Order']['tesoriere_importo_pay'])) {
					$results[$key]['Order']['tesoriere_importo_pay_'] = number_format($val['Order']['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['tesoriere_importo_pay_e'] = $results[$key]['Order']['tesoriere_importo_pay_'].' &euro;';
				}
				else
				if(isset($val['tesoriere_importo_pay'])) {
					$results[$key]['tesoriere_importo_pay_'] = number_format($val['tesoriere_importo_pay'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tesoriere_importo_pay_e'] = $results['Order']['tesoriere_importo_pay_'].' &euro;';
				}
				
				if(isset($val['Order']['tesoriere_fattura_importo'])) {
					$results[$key]['Order']['tesoriere_fattura_importo_'] = number_format($val['Order']['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['tesoriere_fattura_importo_e'] = $results[$key]['Order']['tesoriere_fattura_importo_'].' &euro;';
				}
				else
				if(isset($val['tesoriere_fattura_importo'])) {
					$results[$key]['tesoriere_fattura_importo_'] = number_format($val['tesoriere_fattura_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tesoriere_fattura_importo_e'] = $results['Order']['tesoriere_fattura_importo_'].' &euro;';
				}
	
				if(isset($val['Order']['importo_massimo'])) {
					$results[$key]['Order']['importo_massimo_'] = number_format($val['Order']['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['importo_massimo_e'] = $results[$key]['Order']['importo_massimo_'].' &euro;';
				}
				else
				if(isset($val['importo_massimo'])) {
					$results[$key]['importo_massimo_'] = number_format($val['importo_massimo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['importo_massimo_e'] = $results['Order']['importo_massimo_'].' &euro;';
				}

				if(isset($val['Order']['tot_importo'])) {
					$results[$key]['Order']['tot_importo_'] = number_format($val['Order']['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['Order']['tot_importo_e'] = $results[$key]['Order']['tot_importo_'].' &euro;';
				}
				else
				if(isset($val['tot_importo'])) {
					$results[$key]['tot_importo_'] = number_format($val['tot_importo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['tot_importo_e'] = $results['Order']['tot_importo_'].' &euro;';
				}
			}				
		}
	
		return $results;
	}
	
	public function beforeValidate($options = []) {

		if (!empty($this->data['Order']['data_inizio']) && isset($this->data['Order']['data_inizio_db']))
			$this->data['Order']['data_inizio'] = $this->data['Order']['data_inizio_db'];

		if (!empty($this->data['Order']['data_fine']) && isset($this->data['Order']['data_fine_db']))
			$this->data['Order']['data_fine'] = $this->data['Order']['data_fine_db'];

		if (isset($this->data['Order']['data_fine_validation_db']) && !empty($this->data['Order']['data_fine_validation_db']))
			$this->data['Order']['data_fine_validation'] = $this->data['Order']['data_fine_validation_db'];
		
		if (isset($this->data['Order']['tesoriere_data_pay_db']) && !empty($this->data['Order']['tesoriere_data_pay_db']))
			$this->data['Order']['tesoriere_data_pay'] = $this->data['Order']['tesoriere_data_pay_db'];
			
		return true;
	}
		
	public function beforeSave($options = []) {
		if (!empty($this->data['Order']['data_inizio_db'])) 
	    	$this->data['Order']['data_inizio'] = $this->data['Order']['data_inizio_db'];

		if (!empty($this->data['Order']['data_fine_db']))
			$this->data['Order']['data_fine'] = $this->data['Order']['data_fine_db'];
				
	    if (!empty($this->data['Order']['data_fine_validation_db']))
	    	$this->data['Order']['data_fine_validation'] = $this->data['Order']['data_fine_validation_db'];
			
	    if (!empty($this->data['Order']['tesoriere_data_pay_db']))
	    	$this->data['Order']['tesoriere_data_pay'] = $this->data['Order']['tesoriere_data_pay_db'];

		if (empty($this->data['Order']['data_fine_validation']) || $this->data['Order']['data_fine_validation']==Configure::read('DB.field.date.error'))
			$this->data['Order']['data_fine_validation'] = Configure::read('DB.field.date.empty');
		
		if (empty($this->data['Order']['tesoriere_data_pay']) || $this->data['Order']['tesoriere_data_pay']==Configure::read('DB.field.date.error'))
			$this->data['Order']['tesoriere_data_pay'] = Configure::read('DB.field.date.empty');
		
		if (empty($this->data['Order']['data_incoming_order']) || $this->data['Order']['data_incoming_order']==Configure::read('DB.field.date.error'))
			$this->data['Order']['data_incoming_order'] = Configure::read('DB.field.date.empty');
				
		if (empty($this->data['Order']['data_state_code_close']) || $this->data['Order']['data_state_code_close']==Configure::read('DB.field.date.error'))
			$this->data['Order']['data_state_code_close'] = Configure::read('DB.field.date.empty');
		
		if (empty($this->data['Order']['trasport']))
			$this->data['Order']['trasport'] = Configure::read('DB.field.double.empty');
		
		if (empty($this->data['Order']['cost_more']))
			$this->data['Order']['cost_more'] = Configure::read('DB.field.double.empty');
		
		if (empty($this->data['Order']['cost_less']))
			$this->data['Order']['cost_less'] = Configure::read('DB.field.double.empty');
	
		if (empty($this->data['Order']['tot_importo']))
			$this->data['Order']['tot_importo'] = Configure::read('DB.field.double.empty');

		if (empty($this->data['Order']['importo_massimo']))
			$this->data['Order']['importo_massimo'] = Configure::read('DB.field.double.empty');
		else
			$this->data['Order']['importo_massimo'] = $this->importoToDatabase($this->data['Order']['importo_massimo']);

		if (empty($this->data['Order']['tesoriere_fattura_importo']))
			$this->data['Order']['tesoriere_fattura_importo'] = Configure::read('DB.field.double.empty');
	
		if (empty($this->data['Order']['mail_open_data']))
			$this->data['Order']['mail_open_data'] = Configure::read('DB.field.datetime.empty');
		
		if (empty($this->data['Order']['mail_close_data']))
			$this->data['Order']['mail_close_data'] = Configure::read('DB.field.datetime.empty');
				
	    return true;
	}
}