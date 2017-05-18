<?php
App::uses('AppModel', 'Model');

class BookmarksArticle extends Model {

	public function popolaCarts($user, $order_id, $supplier_organization_id, $debug=false) {
			
		/*
		 * ctrl se il produttore ha articoli tra ipreferiti degli utenti
		 */
		$options =  array();
		$options['conditions'] = array('BookmarksArticle.organization_id' => $user->organization['Organization']['id'],
									   'BookmarksArticle.supplier_organization_id' => $supplier_organization_id
		);
		$options['order'] = array('BookmarksArticle.article_organization_id','BookmarksArticle.article_id');
		$options['recursive'] = -1;
		$results = $this->find('count', $options);
		if($debug) {
			echo '<h2>BookmarksArticle del produttore '.$supplier_organization_id.'</h2>';
			echo "<pre>";
			print_r($results);
			echo "</pre>";	
		}
		if($results==0)
			return; 
		
		/*
		 * estraggo gli articoli associati all'ordine non ancora elaborati 	flag_bookmarks = 'N'
		 */
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'AjaxCart');
		$AjaxCart = new AjaxCart;
		
		$options = array();
		$options['conditions'] = array('ArticlesOrder.organization_id' => $user->organization['Organization']['id'],
										'ArticlesOrder.order_id' => $order_id,
										'ArticlesOrder.flag_bookmarks' => 'N');
		$options['order'] = array('ArticlesOrder.article_organization_id','ArticlesOrder.article_id');
		$options['recursive'] = -1;
		$results = $ArticlesOrder->find('all', $options);
		if($debug) {
			echo '<h2>ArticlesOrder dell\'ordine produttore '.$order_id.' ancora da elaborare flag_bookmarks = N</h2>';
			echo "<pre>";
			print_r($results);
			echo "</pre>";
		}	
		if(!empty($results)) {
			foreach ($results as $result) {
				
				$article_organization_id = $result['ArticlesOrder']['article_organization_id'];
				$article_id = $result['ArticlesOrder']['article_id'];
				
				$options =  array();
				$options['conditions'] = array('BookmarksArticle.organization_id' => $user->organization['Organization']['id'],
											   'BookmarksArticle.article_organization_id' => $article_organization_id,
											   'BookmarksArticle.article_id' => $article_id,
											   'User.organization_id' => $user->organization['Organization']['id'],
											    'User.block' => 0);
				$options['order'] = array('BookmarksArticle.user_id');
				$options['recursive'] = 1;
				$this->unbindModel(array('belongsTo' => array('SuppliersOrganization','Article')));
				$bookmarksArticleResults = $this->find('all', $options);
				if($debug) {
					echo '<h2>Preferenze degli utenti</h2>';
					echo "<pre>";
					print_r($bookmarksArticleResults);
					echo "</pre>";
				}	
				if(!empty($bookmarksArticleResults)) {
					
					foreach ($bookmarksArticleResults as $bookmarksArticleResult) {
	
						$user_id = $bookmarksArticleResult['BookmarksArticle']['user_id'];
						$qta = $bookmarksArticleResult['BookmarksArticle']['qta'];
						
						$resultsJS = $AjaxCart->managementCart($user, $order_id, $article_organization_id, $article_id, $user_id, $qta);
					}
				}		
			} // end foreach ArticlesOrder


			$sql = "UPDATE
						".Configure::read('DB.prefix')."articles_orders
					SET
						flag_bookmarks = 'Y'
					WHERE
						organization_id = ".(int)$user->organization['Organization']['id']."
				    	and order_id = ".(int)$order_id;
			if($debug)
				echo "<br />".$sql;
			
			try {
				if(!$debug) $results = $this->query($sql);
			
			}
			catch (Exception $e) {
				CakeLog::write('error',$e);
				return false;
			}
						
		} // end !empty($results)
	}
	
	public $validate = array(
		'organization_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),
		'supplier_organization_id' => array(
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
		'user_id' => array(
				'numeric' => array(
						'rule' => array('numeric'),
				),
		),			
		'qta' => array(
			'numeric' => array(
				'rule' => array('numeric'),
			),
		),
	);

	public $belongsTo = array(
		'User' => array(
			'className' => 'User',
			'foreignKey' => 'user_id',
			'conditions' => 'User.organization_id = BookmarksArticle.organization_id',
			'fields' => '',
			'order' => ''
		),
		'SuppliersOrganization' => array(
			'className' => 'SuppliersOrganization',
			'foreignKey' => 'supplier_organization_id',
			'conditions' => 'SuppliersOrganization.organization_id = BookmarksArticle.organization_id',
			'fields' => '',
			'order' => ''
		),
		'Article' => array(
				'className' => 'Article',
				'foreignKey' => 'article_id',
				'conditions' => 'Article.organization_id = BookmarksArticle.article_organization_id',
				'fields' => '',
				'order' => ''
		),		
	);	
}