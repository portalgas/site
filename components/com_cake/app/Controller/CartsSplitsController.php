<?php
App::uses('AppController', 'Controller');

class CartsSplitsController extends AppController {

   public $components = array('RequestHandler');
   
   public function beforeFilter() {
   		parent::beforeFilter();
   }
   
   /*
    * $id = CartsSplit.id = order_id-user_id-article_organization_id-article_id-num_split
    */
   public function admin_setImporto($row_id, $key, $importo_forzato=0) {

	   	if(empty($row_id) || (empty($key) && strpos($key,'_') !== false)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}
	   	list($order_id,$user_id,$article_organization_id,$article_id,$num_split) = explode('_',$key);
	   
	   	$esito = false;
	  
	   	if (!$this->CartsSplit->exists($this->user->organization['Organization']['id'], $order_id, $article_organization_id, $article_id, $user_id, $num_split)) {
	   		$this->Session->setFlash(__('msg_error_params'));
	   		$this->myRedirect(Configure::read('routes_msg_exclamation'));
	   	}

	   	$data['CartsSplit']['organization_id'] = $this->user->organization['Organization']['id'];
	   	$data['CartsSplit']['order_id'] = $order_id;
	   	$data['CartsSplit']['article_organization_id'] = $article_organization_id;
	   	$data['CartsSplit']['article_id'] = $article_id;
	   	$data['CartsSplit']['user_id'] = $user_id;
	   	$data['CartsSplit']['num_split'] = $num_split;
	   	$data['CartsSplit']['importo_forzato'] = $this->importoToDatabase($importo_forzato);
	   	if ($this->CartsSplit->save($data))
	   		$esito = true;
	   	else
	   		$esito = false;
	   
	   	if ($esito)
	   		$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'OKIMPORTO\',\''.$key.'\',null);</script>';
	   	else
	   		$content_for_layout = '<script type="text/javascript">managementCart(\''.$row_id.'\',\'NO\',\''.$key.'\',null);</script>';
	   		
	   	$this->set('content_for_layout',$content_for_layout);
	   
	   	$this->layout = 'ajax';
	   	$this->render('/Layouts/ajax');
   }   
}