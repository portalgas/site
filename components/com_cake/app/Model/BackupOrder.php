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
}