<?php
App::uses('AppModel', 'Model');

class BackupOrder extends AppModel {

	public $useTable = false;

	public function copyData($user, $order_id, $debug = false) {
		
		try {
			$sql = "INSERT INTO ".Configure::read('DB.prefix')."backup_orders_orders
					(SELECT * 
					 FROM ".Configure::read('DB.prefix')."orders as `Order` 
					 WHERE
						`Order`.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND `Order`.id = ".$order_id." )";		 	
			self::d($sql, $debug);
			$results = $this->query($sql);
		
			
			$sql = "INSERT INTO ".Configure::read('DB.prefix')."backup_orders_articles_orders
					(SELECT * 
					 FROM ".Configure::read('DB.prefix')."articles_orders as ArticlesOrder 
					 WHERE
						ArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND ArticlesOrder.order_id = ".$order_id." )";
			self::d($sql, $debug);
			$results = $this->query($sql);
			
		
			$sql = "INSERT INTO ".Configure::read('DB.prefix')."backup_orders_carts
					(SELECT * 
					 FROM ".Configure::read('DB.prefix')."carts as Cart 
					 WHERE
						Cart.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND Cart.order_id = ".$order_id." )";	 	
			self::d($sql, $debug);
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		return true;
	}
	
	public function deleteData($user, $order_id, $debug = false) {
		
		try {
			$sql = "DELETE from ".Configure::read('DB.prefix')."backup_orders_orders WHERE organization_id = ".(int)$user->organization['Organization']['id']." AND id = ".$order_id;		 	
			self::d($sql, $debug);
			$results = $this->query($sql);
		
			
			$sql = "DELETE from ".Configure::read('DB.prefix')."backup_orders_articles_orders WHERE organization_id = ".(int)$user->organization['Organization']['id']." AND order_id = ".$order_id;
			self::d($sql, $debug);
			$results = $this->query($sql);
			
		
			$sql = "DELETE from ".Configure::read('DB.prefix')."backup_orders_carts WHERE organization_id = ".(int)$user->organization['Organization']['id']." AND order_id = ".$order_id;	 	
			self::d($sql, $debug);
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		return true;
	}
	
	/*
	 * ripristina l'ordine salvato
	 */
	public function resumeData($user, $order_id, $debug = false) {
		$debug = true;
		try {
			/*
			 * se l'ordine non e' in state_code = CLOSE riapro la consegna
			 */
			App::import('Model', 'BackupOrdersOrder');
			$BackupOrdersOrder = new BackupOrdersOrder;
		
			$options = [];
			$options['conditions'] = ['id' => $order_id,
									  'organization_id' => $user->organization['Organization']['id']];
			$options['recursive'] = -1;
			$backupOrdersOrderResults = $BackupOrdersOrder->find('first', $options);
			if(empty($backupOrdersOrderResults)) 
				return false;
			
			$state_code = $backupOrdersOrderResults['BackupOrdersOrder']['state_code'];
			if($state_code!='CLOSE') {
				$sql = "UPDATE ".Configure::read('DB.prefix')."deliveries SET stato_elaborazione = 'OPEN' WHERE organization_id = ".(int)$user->organization['Organization']['id']." AND id = ".$backupOrdersOrderResults['BackupOrdersOrder']['delivery_id'];
				self::d($sql, $debug);
				$results = $this->query($sql);				
			}
			
			$sql = "INSERT INTO ".Configure::read('DB.prefix')."orders
					(SELECT * 
					 FROM ".Configure::read('DB.prefix')."backup_orders_orders as BackupOrdersOrder 
					 WHERE
						BackupOrdersOrder.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND BackupOrdersOrder.id = ".$order_id." )";		 	
			self::d($sql, $debug);
			$results = $this->query($sql);
		
			
			$sql = "INSERT INTO ".Configure::read('DB.prefix')."articles_orders
					(SELECT * 
					 FROM ".Configure::read('DB.prefix')."backup_orders_articles_orders as BackupOrdersArticlesOrder 
					 WHERE
						BackupOrdersArticlesOrder.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND BackupOrdersArticlesOrder.order_id = ".$order_id." )";
			self::d($sql, $debug);
			$results = $this->query($sql);
			
		
			$sql = "INSERT INTO ".Configure::read('DB.prefix')."carts
					(SELECT * 
					 FROM ".Configure::read('DB.prefix')."backup_orders_carts as BackupOrdersCart 
					 WHERE
						BackupOrdersCart.organization_id = ".(int)$user->organization['Organization']['id']." 
						AND BackupOrdersCart.order_id = ".$order_id." )";	 	
			self::d($sql, $debug);
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
		}

		return true;
	}
}