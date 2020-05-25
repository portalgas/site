<?php
App::uses('AppModel', 'Model');

App::import('Model', 'Cart');

class AjaxGasCart extends AppModel {

	private $debug = true;     // per debug locale 
	
	public $useTable = 'carts';

	private $returnJS;  		  // contiente il javascript di risposta
	private $qta;                 // e' globale perche' se supero la qta_massima_order la devo ricalcolare ($results['ArticlesOrder']['qta_massima_order'] - $results['ArticlesOrder']['qta_cart']);
	private $qta_prima_modifica;   // se frontEnd Cart.qta, se backOffice la prima volta Cart.qta e dopo Cart.qta_forzato
	private $log;
	private $backOffice;
	private $Cart;

	/* 
	 * action  INSERT o UPDATE/DELETE
	 * qta se 0 faccio DELETE di Cart
	 * backOffice  se true cambio qta_forzato e non qta
 	 * forzare_validazione  se true non esegue la validazione (qta_minima, qta_massima_order) Cart::admin_validation_carts_edit();
 	 */
	public function managementCart($user, $order_id=0, $article_organization_id=0, $article_id=0, $user_id=0, $qta=0, $backOffice=false, $forzare_validazione=false) {

		if($this->debug) echo "<pre>";
		
		$this->Cart = new Cart;
		
		$esito = true;
		$msg = '';
		$this->log = "";
		$this->returnJS = "";
		$this->backOffice = $backOffice;
				
		$this->log .= "\r\n------------------------------------------------------------------------------";
		$this->log .= "\r\n organization_id ".$user->organization['Organization']['id'];
		$this->log .= "\r\n ArticlesOrder.order_id $order_id";
		$this->log .= "\r\n ArticlesOrder.article_organization_id $article_organization_id";
		$this->log .= "\r\n ArticlesOrder.article_id $article_id";
		$this->log .= "\r\n user_id $user_id";
		$this->log .= "\r\n qta $qta";
		$this->log .= "\r\n backOffice $this->backOffice";

		$this->qta = $qta; // e' globale perche' se supero la qta_massima_order la devo ricalcolare ($results['ArticlesOrder']['qta_massima_order'] - $results['ArticlesOrder']['qta_cart']);
		
		if(!$this->Cart->exists($user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $user_id))
			$action = 'INSERT';
		else
			$action = 'UPDATE-DELETE';
		$this->log .= "\r\n action $action";
		
		if($action=='INSERT')
			$results = $this->_getArticlesOrder($user, $order_id, $article_organization_id, $article_id);
		else
			$results = $this->_getCartArticlesOrder($user, $order_id, $article_organization_id, $article_id, $user_id);
		
		$this->qta_prima_modifica = $this->getQtaPrimaModifica($user,$results);
		
		if($forzare_validazione)
			$esito = true;
		else
			$esito = $this->_ctrlValidita($user, $results, $action);
		
		if($esito) {
			if($action == 'INSERT') 
				$esito = $this->_insertCart($user, $user_id, $results);  // I N S E R T
			else {
				if($this->qta==0)   //  D E L E T E
					$esito = $this->_deleteCart($user, $results);
				else //  U P D A T E 
					$esito = $this->_updateCart($user, $results);
			}
			
			App::import('Model', 'ArticlesOrder');
			$ArticlesOrder = new ArticlesOrder;
			
			$ArticlesOrder->aggiornaQtaCart_StatoQtaMax($user->organization['Organization']['id'], $results['ArticlesOrder']['order_id'], $results['ArticlesOrder']['article_organization_id'], $results['ArticlesOrder']['article_id'], $this->debug);
		}
		
		if (!$esito && empty($this->returnJS)) $this->returnJS = 'managementCart(\'%s\',\'NO\', null);';
		
		$this->log .= "\r\n msg $msg";
		$this->log .= print_r($this->returnJS, true);
		
		if($this->debug) print_r($this->log);
		if($this->debug) echo "</pre>";
		
		if(Configure::read('developer.mode')) CakeLog::write('debug', $this->log, array('ecomm'));
		
		return $this->returnJS;
	}
	
	/*
	 * $action = INSERT
	 * $action = UPDATE-DELETE
	 */
	private function _ctrlValidita($user, $results, $action) {
		
		$esito=true;
		
		if(empty($results)) {
			$this->log .= "\r\n recordset vuoto!!";
			$esito = false;
		}
		else
		/*
		 * S T A T I     ArticlesOrder (N)   Cart (N, CLOSE)
		 * */
		if($results['ArticlesOrder']['stato']=='N') {
			$msg = sprintf(Configure::read('cart_msg_stato_N'), $results['ArticlesOrder']['name']);
			$this->returnJS = 'managementCart(\'%s\',\'ERRORE-STATO-N\',"'.$msg.'");';
			$this->log .= "\r\n _ctrlValidita ArticlesOrder.stato = N";
			$esito = false;
		}
		else
		if($results['ArticlesOrder']['stato']=='N' || $results['Cart']['stato']=='N') {
			$msg = sprintf(Configure::read('cart_msg_stato_N'), $results['ArticlesOrder']['name']);
			$this->returnJS = 'managementCart(\'%s\',\'ERRORE-STATO-N\',"'.$msg.'");';
			$this->log .= "\r\n _ctrlValidita ArticlesOrder.stato = N || Cart.stato = N";
			$esito = false;
		}
		else
		/*
		 * S T A T I     ArticlesOrder.stato QTAMAXORDER e Cart.stato LOCK posso solo diminuire la qta
		 * */			
		if($action!='INSERT') { // in insert empty
			
			if($results['ArticlesOrder']['stato']=='QTAMAXORDER' && ($this->qta > $this->qta_prima_modifica)) {
				$msg = sprintf(Configure::read('cart_msg_qtamax_order_stop'), $results['ArticlesOrder']['name'], $results['ArticlesOrder']['qta_massima_order']);
				$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMAXORDER-STOP\',"'.$msg.'");';
				$this->log .= "\r\n _ctrlValidita ArticlesOrder.stato = QTAMAXORDER e qta aumentata";
				$esito = false;
			}
			if($results['ArticlesOrder']['stato']=='LOCK' && ($this->qta > $this->qta_prima_modifica)) {
				$msg = sprintf(Configure::read('cart_msg_block_stop'), $results['ArticlesOrder']['name']);
				$this->returnJS = 'managementCart(\'%s\',\'ERRORE-LOCK-STOP\',"'.$msg.'");';
				$this->log .= "\r\n _ctrlValidita Cart.stato = LOCK e qta aumentata";
				$esito = false;
			}					
		}	
		/*
		 * D A T I 
		* */
		if($this->qta>0 && ($this->qta < $results['ArticlesOrder']['qta_minima'])) {  // ctrl qta minima riferita all'acquisto del singolo gasista
			$msg = sprintf(Configure::read('cart_msg_qtamin'), $results['ArticlesOrder']['name'],$results['ArticlesOrder']['qta_minima'], $this->qta);
			$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMIN\',"'.$msg.'");';
			$this->log .= "\r\n _ctrlValidita ArticlesOrder.qta_minima inferiore ($this->qta) ";
			$esito = false;
		}	
		else
		/*
		 * Q T A - M A X
		* */
		if($results['ArticlesOrder']['qta_massima'] > 0) {
			if($this->qta>0 && ($this->qta > $results['ArticlesOrder']['qta_massima'])) {  // ctrl qta massima riferita all'acquisto del singolo gasista
				$msg = sprintf(Configure::read('cart_msg_qtamax'), $results['ArticlesOrder']['name'],$results['ArticlesOrder']['qta_massima'], $this->qta);
				$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMAX\',"'.$msg.'");';
				$this->log .= "\r\n _ctrlValidita ArticlesOrder.qta_massima superiore ($this->qta) ";
				$esito = false;
			}		
		}
		else	
		/*
		 * Q T A - M A X 
		 * */
		if($results['ArticlesOrder']['qta_massima_order'] > 0) {
			
			if($this->qta > $this->qta_prima_modifica) { // ctrl che l'utente non abbia diminuito la qta
		
				// qta_massima_order superata: ricalcolo la qta e articlesOrder.stato = QTAMAXORDER
				if(($results['ArticlesOrder']['qta_cart'] - $this->qta_prima_modifica + $this->qta) > $results['ArticlesOrder']['qta_massima_order']) {
				
					$this->qta = ($results['ArticlesOrder']['qta_massima_order'] - $results['ArticlesOrder']['qta_cart'] + $this->qta_prima_modifica); // la ricalcolo
				
					$msg = sprintf(Configure::read('cart_msg_qtamax_order'),$results['ArticlesOrder']['name'], $results['ArticlesOrder']['qta_massima_order'], $this->qta);
					$this->log .= "\r\n".$msg;
					$this->returnJS = 'managementCart(\'%s\',\'ERRORE-QTAMAXORDER\',"'.$msg.'");';
					$this->log .= "\r\n Qta massima superata: qta nuova in $this->qta, articlesOrder.stato = QTAMAXORDER";
				}
				else  // qta massima raggiunta articlesOrder.stato = QTAMAXORDER
				if(($results['ArticlesOrder']['qta_cart'] - $this->qta_prima_modifica + $this->qta) == $results['ArticlesOrder']['qta_massima_order']) {
					$this->log .= "\r\nQta massima raggiunta: articlesOrder.stato = QTAMAXORDER";
				}
				
			}
			
			$esito=true;
		}

		return $esito;
	}

	/*
	 * se INSERT
	 */
	private function _getArticlesOrder($user, $order_id, $article_organization_id, $article_id) {
	
		$this->log .= "\r\n _getArticlesOrder";
	
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder();
		
		$options['conditions'] = array('ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
										'ArticlesOrder.order_id' => $order_id,
										'ArticlesOrder.article_organization_id' => $article_organization_id,
										'ArticlesOrder.article_id' => $article_id
									);
		$options['recursive'] = 0;
		$ArticlesOrder->unbindModel(['belongsTo' => ['Order']]);
		$results = $ArticlesOrder->find('first', $options);
		
		$this->log .= "\r\n Result dei dati ".print_r($results, true);
		return $results;
	}
	
	/*
	 * se UPDATE-DELETE
	 */
	private function _getCartArticlesOrder($user, $order_id, $article_organization_id, $article_id, $user_id) {

		$this->log .= "\r\n _getCartArticlesOrder";
		
		$options['conditions'] = array('Cart.organization_id' => $user->organization['Organization']['id'],
										'Cart.order_id' => $order_id,
										'Cart.article_organization_id' => $article_organization_id,
										'Cart.article_id' => $article_id,
										'Cart.user_id' => $user_id,
										);
		$options['recursive'] = 0;
		$this->Cart->unbindModel(array('belongsTo' => array('Order','User')));
		$results = $this->Cart->find('first', $options);
		
		$this->log .= "\r\n Result dei dati ".print_r($results, true);
		return $results;
	}
	
	private function _insertCart($user, $user_id, $results) {
	
		$this->log .= "\r\n _insertCart";
		$esito = true;

		if($this->qta==0) {
			$this->log .= "\r\n in inserimento && qta = 0 impossibile!!";
			$esito = false;
		}
		else {			
			$cart['Cart']['organization_id'] = $user->organization['Organization']['id'];
			$cart['Cart']['user_id'] = $user_id;
			$cart['Cart']['order_id'] = $results['ArticlesOrder']['order_id'];
			$cart['Cart']['article_organization_id'] = $results['ArticlesOrder']['article_organization_id'];
			$cart['Cart']['article_id'] = $results['ArticlesOrder']['article_id'];
			$cart['Cart']['deleteToReferent'] = 'N';
			if($this->backOffice)
				$cart['Cart']['qta_forzato'] = $this->qta;
			else
				$cart['Cart']['qta'] = $this->qta;
			$this->log .= "\r\n Result x INSERT ".print_r($cart, true);
			
			/* 
			 * ctrl Cassa 
			 */
			App::import('Model', 'CashesUser');
			$CashesUser = new CashesUser;

			if($CashesUser->ctrlLimitCart($user, $results['Article']['supplier_organization_id'], $this->qta_prima_modifica, $this->qta, $results['ArticlesOrder']['prezzo'])) {
				if ($this->Cart->save($cart)) {
					
					if(empty($this->returnJS))
						$this->returnJS = 'managementCart(\'%s\',\'OK\',null);';
					
					$esito = true;
				}
				else
					$esito = false;
			}
			else {
				$msg = Configure::read('cart_msg_limit_cash');
				$this->returnJS = 'managementCart(\'%s\',\'LIMIT-CASH\',"'.$msg.'");';
				$esito = false;
			}
		} // qta>0
	
		return $esito;
	}
	
	private function _updateCart($user, $results) {
		$esito = true;
		
		$this->log .= "\r\n_updateCart";
		
		$cart['Cart']['organization_id'] = $user->organization['Organization']['id'];
		$cart['Cart']['user_id']    = $results['Cart']['user_id'];
		$cart['Cart']['order_id']   = $results['ArticlesOrder']['order_id'];
		$cart['Cart']['article_organization_id'] = $results['ArticlesOrder']['article_organization_id'];
		$cart['Cart']['article_id'] = $results['ArticlesOrder']['article_id'];
		$cart['Cart']['deleteToReferent'] = 'N';
		if($this->backOffice)
			$cart['Cart']['qta_forzato'] = $this->qta; // ($results['Cart']['qta_forzato'] + $this->qta);
		else
			$cart['Cart']['qta'] = $this->qta;
			
		$this->log .= "\r\n Result x UPDATE ".print_r($cart, true);

		/* 
		 * ctrl Cassa 
		 * solo se aumento la qta
		 */
		$esito_ctrl_limit_cart = true; 
		if($this->qta > $this->qta_prima_modifica) {
			App::import('Model', 'CashesUser');
			$CashesUser = new CashesUser;
				
			$esito_ctrl_limit_cart = $CashesUser->ctrlLimitCart($user, $results['Article']['supplier_organization_id'], $this->qta_prima_modifica, $this->qta, $results['ArticlesOrder']['prezzo']);		
		} 

		if($esito_ctrl_limit_cart) {
			if ($this->Cart->save($cart)) {
				if(empty($this->returnJS))
					$this->returnJS = 'managementCart(\'%s\',\'OK\',null);';
				
				$esito = true;
			}
			else
				$esito = false;
		}
		else {
			$msg = Configure::read('cart_msg_limit_cash');
			$this->returnJS = 'managementCart(\'%s\',\'LIMIT-CASH\',"'.$msg.'");';
			$esito = false;
		}
		
		return $esito;
	}
	
	private function _deleteCart($user, $results) {
		$esito = true;
	
		if($this->backOffice) {
			$this->log .= "\r\nCART to DELETE Back-office: setto deleteToReferent = Y";

			$cart['Cart']['organization_id'] = $user->organization['Organization']['id'];
			$cart['Cart']['user_id']    = $results['Cart']['user_id'];
			$cart['Cart']['order_id']   = $results['ArticlesOrder']['order_id'];
			$cart['Cart']['article_organization_id'] = $results['ArticlesOrder']['article_organization_id'];
			$cart['Cart']['article_id'] = $results['ArticlesOrder']['article_id'];
			$cart['Cart']['deleteToReferent'] = 'Y';
			$cart['Cart']['qta_forzato'] = 0;
			$cart['Cart']['importo_forzato'] = 0;
			$this->log .= "\r\n Result x UPDATE ".print_r($cart, true);
			if ($this->Cart->save($cart)) {
				$this->returnJS = 'managementCart(\'%s\',\'DELETE\',0,null);';
			
				$esito = true;
			}
			else
				$esito = false;
		}
		else {
			$this->log .= "\r\nCART to DELETE Front-end: cancello acquisto";
			if ($this->Cart->delete($user->organization['Organization']['id'], $results['Cart']['order_id'], $results['Cart']['article_organization_id'], $results['Cart']['article_id'], $results['Cart']['user_id'])) {
				$this->returnJS = 'managementCart(\'%s\',\'DELETE\',0,null);';
				
				$esito=true;
			}
			else
				$esito=false;
		}
		
		return $esito;
	}

	public function getQtaPrimaModifica($user, $results) {
		$qta_prima_modifica = 0;

		if($this->backOffice) {
			if($results['Cart']['qta_forzato']==0)  // e' la prima volta che da backOffice faccio una modifica
				$qta_prima_modifica = $results['Cart']['qta'];
			else 
				$qta_prima_modifica = $results['Cart']['qta_forzato'];
		}
		else
			$qta_prima_modifica = $results['Cart']['qta'];
		
		$this->log .= "\r\n qta_prima_modifica $qta_prima_modifica";
		return $qta_prima_modifica;
	}
}