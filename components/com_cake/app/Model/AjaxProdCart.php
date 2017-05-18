<?php
App::uses('AppModel', 'Model');
App::import('Model', 'ProdCart');

/*
 * non c'e' distinzione tra backoffice e front-end perche' non sono gestiti i campi qta_forzato e importo_forzato
 */
class AjaxProdCart extends AppModel {

	public $useTable = 'prod_carts';

	private $returnJS;  		  // contiente il javascript di risposta
	private $qta;                 // e' globale perche' se supero la qta_massima_order la devo ricalcolare ($results['ProdDeliveriesArticle']['qta_massima_order'] - $qta_cart_calcolata);
	private $qta_cart_calcolata;  // ricalcolo da database il totale degli acquisti sull'articolo
	private $log;
	private $ProdCart;

	/* 
	 * action  INSERT o UPDATE/DELETE
	 * qta se 0 faccio DELETE di Cart
 	 */
	public function managementCart($user, $prod_delivery_id=0, $article_organization_id=0, $article_id=0, $user_id=0, $qta=0) {

		$this->ProdCart = new ProdCart;		
		$esito = true;
		$msg = '';
		$this->log = "";
		$this->returnJS = "";
		
		$this->log .= "\r\n------------------------------------------------------------------------------";
		$this->log .= "\r\n organization_id ".$user->organization['Organization']['id'];
		$this->log .= "\r\n ProdDeliveriesArticle.prod_delivery_id $prod_delivery_id";
		$this->log .= "\r\n ProdDeliveriesArticle.article_organization_id $article_organization_id";
		$this->log .= "\r\n ProdDeliveriesArticle.article_id $article_id";
		$this->log .= "\r\n user_id $user_id";
		$this->log .= "\r\n qta $qta";

		$this->qta = $qta; // e' globale perche' se supero la qta_massima_order la devo ricalcolare ($results['ProdDeliveriesArticle']['qta_massima_order'] - $qta_cart_calcolata);
		
		if(!$this->ProdCart->exists($user->organization['Organization']['id'], $prod_delivery_id, $article_organization_id, $article_id, $user_id))			$action = 'INSERT';		else			$action = 'UPDATE-DELETE';
		$this->log .= "\r\n action $action";

		if($action=='INSERT')
			$results = $this->__getProdDeliveriesArticle($user, $prod_delivery_id, $article_organization_id, $article_id);
		else
			$results = $this->__getProdCartProdDeliveriesArticle($user, $prod_delivery_id, $article_organization_id, $article_id, $user_id);

		$this->qta_cart_calcolata = $this->__getQtaCartRuntime($user, $results['ProdDeliveriesArticle']['prod_delivery_id'], $results['ProdDeliveriesArticle']['article_organization_id'], $results['ProdDeliveriesArticle']['article_id']);
		$this->qta_prima_modifica = $this->__getQtaPrimaModifica($user,$results);
		
		$esito = $this->__ctrlValidita($user, $results, $action);
		
		if($esito) {
			if($action == 'INSERT') 
				$esito = $this->__insertProdCart($user, $user_id, $results);  // I N S E R T
			else {
				if($this->qta==0)   //  D E L E T E
					$esito = $this->__deleteProdCart($user, $results);
				else //  U P D A T E 
					$esito = $this->__updateProdCart($user, $results);
			}
		}
		
		if (!$esito && empty($this->returnJS)) $this->returnJS = 'managementCart(\'%s\',\'NO\', null);';
		
		$this->log .= "\r\n msg $msg";
		$this->log .= print_r($this->returnJS, true);
		
		if(Configure::read('developer.mode')) CakeLog::write('debug', $this->log, array('ecomm'));

		return $this->returnJS;
	}
	
	/*
	 * $action = INSERT
	 * $action = UPDATE-DELETE
	 */
	private function __ctrlValidita($user, $results, $action) {
		
		$esito=true;
		
		if(empty($results)) {
			$this->log .= "\r\n recordset vuoto!!";
			$esito = false;
		}
		else
		/*
		 * S T A T I     ProdDeliveriesArticle (N)   Cart (N, CLOSE)
		 * */
		if($results['ProdDeliveriesArticle']['stato']=='N') {
			$msg = sprintf(Configure::read('cart_msg_stato_N'), $results['Article']['name']);
			$this->returnJS = 'managementCart(\'%s\',\'ERRORE-STATO-N\',"'.$msg.'");';
			$this->log .= "\r\n __ctrlValidita ProdDeliveriesArticle.stato = N";
			$esito = false;
		}
		else
		if($results['ProdDeliveriesArticle']['stato']=='N' || $results['ProdCart']['stato']=='N') {
			$msg = sprintf(Configure::read('cart_msg_stato_N'), $results['Article']['name']);
			$this->returnJS = 'managementCart(\'%s\',\'ERRORE-STATO-N\',"'.$msg.'");';
			$this->log .= "\r\n __ctrlValidita ProdDeliveriesArticle.stato = N || ProdCart.stato = N";
			$esito = false;
		}
		else
		/*
		 * S T A T I     ProdDeliveriesArticle.stato QTAMAXORDER e ProdCart.stato LOCK posso solo diminuire la qta
		 * */			
		if($action!='INSERT') { // in insert empty
			
			if($results['ProdDeliveriesArticle']['stato']=='QTAMAXORDER' && ($this->qta > $this->qta_prima_modifica)) {
				$msg = sprintf(Configure::read('cart_msg_qtamax_order_stop'), $results['Article']['name'], $results['ProdDeliveriesArticle']['qta_massima_order']);
				$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMAXORDER-STOP\',"'.$msg.'");';
				$this->log .= "\r\n __ctrlValidita ProdDeliveriesArticle.stato = QTAMAXORDER e qta aumentata";
				$esito = false;
			}
			if($results['ProdDeliveriesArticle']['stato']=='LOCK' && ($this->qta > $this->qta_prima_modifica)) {
				$msg = sprintf(Configure::read('cart_msg_block_stop'), $results['Article']['name']);
				$this->returnJS = 'managementCart(\'%s\',\'ERRORE-LOCK-STOP\',"'.$msg.'");';
				$this->log .= "\r\n __ctrlValidita ProdCart.stato = LOCK e qta aumentata";
				$esito = false;
			}					
		}	
		/*
		 * D A T I 
		* */
		if($this->qta>0 && ($this->qta < $results['ProdDeliveriesArticle']['qta_minima'])) {  // ctrl qta minima riferita all'acquisto del singolo gasista
			$msg = sprintf(Configure::read('cart_msg_qtamin'), $results['Article']['name'],$results['ProdDeliveriesArticle']['qta_minima'], $this->qta);
			$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMIN\',"'.$msg.'");';
			$this->log .= "\r\n __ctrlValidita ProdDeliveriesArticle.qta_minima inferiore ($this->qta) ";
			$esito = false;
		}			
		else
		/*
		 * Q T A - M A X
		* */
		if($results['ArticlesOrder']['qta_massima'] > 0) {
			if($this->qta>0 && ($this->qta > $results['ProdDeliveriesArticle']['qta_massima'])) {  // ctrl qta massima riferita all'acquisto del singolo gasista
				$msg = sprintf(Configure::read('cart_msg_qtamax'), $results['Article']['name'],$results['ProdDeliveriesArticle']['qta_massima'], $this->qta);
				$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMAX\',"'.$msg.'");';
				$this->log .= "\r\n __ctrlValidita ProdDeliveriesArticle.qta_massima superiore ($this->qta) ";
				$esito = false;
			}
		}
		else				
		/*
		 * Q T A - M A X   ricalcolo da database il totale degli acquisti sull'articolo  
		 * */
		if($results['ProdDeliveriesArticle']['qta_massima_order'] > 0) {
			
			if($this->qta > $this->qta_prima_modifica) { // ctrl che l'utente non abbia diminuito la qta
				
				// qta_massima_order superata: ricalcolo la qta e ProdDeliveriesArticle.stato = QTAMAXORDER				if(($this->qta_cart_calcolata - $this->qta_prima_modifica + $this->qta) > $results['ProdDeliveriesArticle']['qta_massima_order']) {									$this->qta = ($results['ProdDeliveriesArticle']['qta_massima_order'] - $this->qta_cart_calcolata); // la ricalcolo									$msg = sprintf(Configure::read('cart_msg_qtamax_order'),$results['Article']['name'], $results['ProdDeliveriesArticle']['qta_massima_order'], $this->qta);					$this->log .= "\r\n".$msg;					$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMAXORDER\',"'.$msg.'");';					$this->log .= "\r\n Qta massima superata: qta nuova in $this->qta, ProdDeliveriesArticle.stato = QTAMAXORDER";				}				else  // qta massima raggiunta ProdDeliveriesArticle.stato = QTAMAXORDER				if(($this->qta_cart_calcolata - $this->qta_prima_modifica + $this->qta) == $results['ProdDeliveriesArticle']['qta_massima_order']) {					$this->log .= "\r\nQta massima raggiunta: ProdDeliveriesArticle.stato = QTAMAXORDER";				}				
			}
			
			$esito=true;
		}

		return $esito;
	}

	/*
	 * se INSERT
	 */
	private function __getProdDeliveriesArticle($user, $prod_delivery_id, $article_organization_id, $article_id) {			$this->log .= "\r\n __getProdDeliveriesArticle";	
		App::import('Model', 'ProdDeliveriesArticle');		$ProdDeliveriesArticle = new ProdDeliveriesArticle();
				$options['conditions'] = array('ProdDeliveriesArticle.organization_id' => $user->organization['Organization']['id'],										'ProdDeliveriesArticle.prod_delivery_id' => $prod_delivery_id,
										'ProdDeliveriesArticle.article_organization_id' => $article_organization_id,
										'ProdDeliveriesArticle.article_id' => $article_id									);		$options['recursive'] = 0;
		$ProdDeliveriesArticle->unbindModel(array('belongsTo' => array('ProdDelivery')));		$results = $ProdDeliveriesArticle->find('first', $options);		$this->log .= "\r\n Result dei dati ".print_r($results, true);		return $results;	}
	
	/*
	 * se UPDATE-DELETE
	 */
	private function __getProdCartProdDeliveriesArticle($user, $prod_delivery_id, $article_organization_id, $article_id, $user_id) {

		$this->log .= "\r\n __getProdCartProdDeliveriesArticle";
		
		$options['conditions'] = array('ProdCart.organization_id' => $user->organization['Organization']['id'],										'ProdCart.prod_delivery_id' => $prod_delivery_id,
										'ProdCart.article_organization_id' => $article_organization_id,
										'ProdCart.article_id' => $article_id,
										'ProdCart.user_id' => $user_id,											);				$options['recursive'] = 0;		$results = $this->ProdCart->find('first', $options);
				$this->log .= "\r\n Result dei dati ".print_r($results, true);
		return $results;
	}
	
	private function __insertProdCart($user, $user_id, $results) {
	
		$this->log .= "\r\n __insertProdCart";
		$esito = true;

		if($this->qta==0) {
			$this->log .= "\r\n in iserimento && qta = 0 impossibile!!";
			$esito = false;
		}
		else {			
			$cart['ProdCart']['organization_id'] = $user->organization['Organization']['id'];
			$cart['ProdCart']['user_id'] = $user_id;
			$cart['ProdCart']['prod_delivery_id'] = $results['ProdDeliveriesArticle']['prod_delivery_id'];
			$cart['ProdCart']['article_organization_id'] = $results['ProdDeliveriesArticle']['article_organization_id'];
			$cart['ProdCart']['article_id'] = $results['ProdDeliveriesArticle']['article_id'];
			$cart['ProdCart']['qta'] = $this->qta;
			$this->log .= "\r\n Result x INSERT ".print_r($cart, true);
			
			if ($this->ProdCart->save($cart)) {
				
				if(empty($this->returnJS))
					$this->returnJS = 'managementCart(\'%s\',\'OK\',null);';
				
				$this->__prodDeliveriesArticleUpdate($user, $results, 0);
				
				$esito = true;
			}
			else
				$esito = false;
		} // qta>0
	
		return $esito;
	}
	
	private function __updateProdCart($user, $results) {
		$esito = true;
		
		$this->log .= "\r\n__updateProdCart";
		
		$cart['ProdCart']['organization_id'] = $user->organization['Organization']['id'];
		$cart['ProdCart']['user_id']    = $results['ProdCart']['user_id'];
		$cart['ProdCart']['prod_delivery_id']   = $results['ProdDeliveriesArticle']['prod_delivery_id'];
		$cart['ProdCart']['article_organization_id'] = $results['ProdDeliveriesArticle']['article_organization_id'];
		$cart['ProdCart']['article_id'] = $results['ProdDeliveriesArticle']['article_id'];
		$cart['ProdCart']['name']       = $results['Article']['name'];
		$cart['ProdCart']['qta'] = $this->qta;
			
		$this->log .= "\r\n Result x UPDATE ".print_r($cart, true);

		if ($this->ProdCart->save($cart)) {
			if(empty($this->returnJS))
				$this->returnJS = 'managementCart(\'%s\',\'OK\',null);';
			
			$this->__prodDeliveriesArticleUpdate($user, $results, $results['ProdCart']['qta']);
			
			$esito = true;
		}
		else
			$esito = false;

		return $esito;
	}
	
	private function __deleteProdCart($user, $results) {
		$esito = true;
		
		$this->log .= "\r\nProdCart da DELETE";
		if ($this->ProdCart->delete($user->organization['Organization']['id'], $results['ProdCart']['prod_delivery_id'], $results['ProdCart']['article_organization_id'], $results['ProdCart']['article_id'], $results['ProdCart']['user_id'])) {
			$this->returnJS = 'managementCart(\'%s\',\'DELETE\',0,null);';
			
			$this->__prodDeliveriesArticleUpdate($user, $results, $results['ProdCart']['qta']);
			
			$esito=true;
		}
		else
			$esito=false;
		
		return $esito;
	}

	private function __prodDeliveriesArticleUpdate($user, $results) {
		
		$this->log .= "\r\n __prodDeliveriesArticleUpdate";
		$this->log .= "\r\n ProdCart.qta_prima_modifica ".$this->qta_prima_modifica;
		$this->log .= "\r\n ProdCart.qta ".$this->qta;
		$this->log .= "\r\n qta_cart_calcolata ".$this->qta_cart_calcolata;

		// qta_massima_order superata: ricalcolo la qta e ProdDeliveriesArticle.stato = QTAMAXORDER
		$qta_cart = ($this->qta_cart_calcolata - $this->qta_prima_modifica + $this->qta);
		$this->log .= "\r\n nuova qta_cart (qta_cart_calcolata - qta_prima_modifica + qta) ".$qta_cart;
				
		// TODOSQL
		$sql = "UPDATE 
					".Configure::read('DB.prefix')."prod_deliveries_articles
				SET 
					qta_cart = $qta_cart ";
		if($results['ProdDeliveriesArticle']['qta_massima_order'] > 0) {
			
			if($this->qta > $this->qta_prima_modifica) { // ctrl che l'utente non abbia diminuito la qta
				// qta_massima_order superata: ricalcolo la qta e ProdDeliveriesArticle.stato = QTAMAXORDER
				if(($this->qta_cart_calcolata - $this->qta_prima_modifica + $this->qta) >= $results['ProdDeliveriesArticle']['qta_massima_order']) 					$sql .= ",stato  = 'QTAMAXORDER' ";
				else
					$sql .= ",stato  = 'Y' ";
			}
			else 
				$sql .= ",stato  = 'Y' ";
			
		}
			
		$sql .= " WHERE
					organization_id = ".(int)$user->organization['Organization']['id']." 
				    and prod_delivery_id = ".(int)$results['ProdDeliveriesArticle']['prod_delivery_id']." 
				    and article_organization_id = ".(int)$results['ProdDeliveriesArticle']['article_organization_id']." 
				    and article_id = ".(int)$results['ProdDeliveriesArticle']['article_id'];
		$this->log .= "\r\n".$sql;
		
		$results = $this->query($sql);
	}
	
	/*
	 * calcolo il totale solo da sum(ProdCart.qta)
	 */
	public function __getQtaCartRuntime($user, $prod_delivery_id, $article_organization_id, $article_id) {
		
		$this->log .= "\r\n__getQtaCartRuntime";
		
		$sql = "SELECT sum(ProdCart.qta) as qta_cart_ricalcolata 
				FROM
					".Configure::read('DB.prefix')."prod_deliveries_articles ProdDeliveriesArticle,
					".Configure::read('DB.prefix')."prod_carts ProdCart
				WHERE
					ProdDeliveriesArticle.organization_id = ".(int)$user->organization['Organization']['id']."
					and ProdCart.organization_id = ".(int)$user->organization['Organization']['id']."
					and ProdDeliveriesArticle.prod_delivery_id = ProdCart.prod_delivery_id
					and ProdDeliveriesArticle.article_id = ProdCart.article_id
					and ProdDeliveriesArticle.article_organization_id = ProdCart.article_organization_id
					and ProdCart.stato != 'CLOSE' 
					and ProdDeliveriesArticle.prod_delivery_id = ".(int)$prod_delivery_id."
					and ProdDeliveriesArticle.article_organization_id = ".(int)$article_organization_id."
					and ProdDeliveriesArticle.article_id = ".(int)$article_id;					
		// echo $sql;
		$this->log .= "\r\n".$sql;
		$results = current(current($this->query($sql)));

		$this->log .= print_r($results, true);	
		
		if($results['qta_cart_ricalcolata']==0) $results['qta_cart_ricalcolata']=0;
		
		return $results['qta_cart_ricalcolata'];	
	}	
	
	private function __getQtaPrimaModifica($user,$results) {
		$qta_prima_modifica = 0;

		$qta_prima_modifica = $results['ProdCart']['qta'];		
		$this->log .= "\r\n qta_prima_modifica $qta_prima_modifica";
		return $qta_prima_modifica;
	}
}