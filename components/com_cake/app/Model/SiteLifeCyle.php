<?php
App::uses('AppModel', 'Model');


class SiteLifeCyle extends AppModel {

	public $useTable = false;

	/*
	 * articleResult puo' essere
	 *	array key [article_organization_id, article_id]
	 *	obj ['Article']
	 */
	public function changeArticle($user, $articleResult, $operation='', $options=[], $debug=false) {
		
		$esito = [];
		$esito['MSG'] = '';
	
		if(empty($articleResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	
				
		App::import('Model', 'Article');
		$Article = new Article;	
					
		if(!isset($articleResult['Article']))
			$articleResult = $this->_getArticleById($user, $articleResult, $debug);
	
		switch($operation) {
			case 'ADD_AFTER_SAVE':
				/*
				 * Articles Type
				*/
				if(!$Article->articlesTypesSave($articleResult))
					$esito['MSG'] .= '<br />'.__('The articlesType could not be saved. Please, try again.');
			
				$Article->syncronizeArticleTypeBio($user, $articleResult['Article']['id']);
			
				/*
				 * gestione della sincronizzazione dell'articolo associato all'ordine
				*/
				if($options['isUserPermissionArticlesOrder']) // se ho il modulo attivato devo modificarlo a mano
					if(!$Article->syncronizeArticlesOrder($user, $articleResult['Article']['organization_id'], $articleResult['Article']['id'],'INSERT'))
						$esito['MSG'] .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');			
			break;
			case 'EDIT_AFTER_SAVE':
				/*
				 * Articles Type
				*/
				if(!$Article->articlesTypesSave($articleResult))
					$esito['MSG'] .= '<br />'.__('The articlesType could not be saved. Please, try again.');
				
				$Article->syncronizeArticleTypeBio($user, $articleResult['Article']['id']);
				
				/*
				 * gestione della sincronizzazione dell'articolo associato all'ordine
				*/
				if($options['isUserPermissionArticlesOrder']) // se ho il modulo attivato devo modificarlo a mano
					if(!$Article->syncronizeArticlesOrder($user, $articleResult['Article']['organization_id'], $articleResult['Article']['id'], 'UPDATE'))
						$esito['MSG'] .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
			
				/*
				 * se lo stato = N
				 * 		- cancello l'associazione con gli ordini (trigger cancella gli acquisti effettuati)
				 */
				if($resultsOld['Article']['stato']=='Y' && $articleResult['Article']['stato']=='N') {
					//if(!$Article->syncronizeArticlesOrder($user, $articleResult['Article']['organization_id'], $articleResult['Article']['id'], 'DELETE'))
					//	$esito['MSG'] .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
				}
				else
				if($resultsOld['Article']['stato']=='N' && $articleResult['Article']['stato']=='Y') {
					if($options['isUserPermissionArticlesOrder']) // l'utente gestisce l'associazione degli articoli con l'ordine
						if(!$Article->syncronizeArticlesOrder($user, $articleResult['Article']['organization_id'], $articleResult['Article']['id'], 'INSERT'))
							$esito['MSG'] .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
				}
			break;
			case 'DELETE':
				if($options['isUserPermissionArticlesOrder']) {  // se ho il modulo attivato devo modificarlo a mano
				
					$tmp_user = null;
					
					if($articleResult['SuppliersOrganization']['owner_articles']!='REFERENT') {
						/*
						 * estraggo l'organization proprietario del listino
						 */
						App::import('Model', 'Organization');
						$Organization = new Organization;	
									
						$options = [];
						$options['conditions'] = ['Organization.id' => $user->organization['Organization']['id']];
						$options['recursive'] = -1;
						$organizationResults = $Organization->find('first', $options);
						if(!empty($organizationResult)) {							
							$tmp_user = $this->utilsCommons->createObjUser(['Organization' => $organizationResults['Organization']]);

							self::d($tmp_user);
						}
					}
					else 
						$tmp_user = $user;

					if(!empty($tmp_user)) {							
						if(!$Article->syncronizeArticlesOrder($tmp_user, $articleResult['Article']['organization_id'], $articleResult['Article']['id'], 'DELETE'))
							$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');
					}	
				}
			break;
			case 'EDIT_PRICE':
				if($options['isUserPermissionArticlesOrder'])  // se ho il modulo attivato devo modificarlo a mano
					if(!$Article->syncronizeArticlesOrder($user, $articleResult['Article']['organization_id'], $articleResult['Article']['id'], 'UPDATE'))
						$msg .= '<br />'.__('The articles order syncronize could not be saved. Please, try again.');			
			break;
		}
			
		$esito['CODE'] = "200";
		
		return $esito; 
	}
	
	/*
	 * articlesOrderResult puo' essere
	 *	array key [article_organization_id, article_id, order_id]
	 *	obj ['ArticlesOrder']
	 */	
	public function changeArticlesOrders($user, $articlesOrderResult, $operation='', $options=[], $debug=false) {
	
		$esito = [];

		if(empty($articlesOrderResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($articlesOrderResult))
			$articlesOrderResult = $this->_getArticlesOrderById($user, $articlesOrderResult, $debug);
	
		switch($operation) {
			case 'CLOSE':

			break;
		}
			
		return $esito; 
	}
	
	/*
	 * cartResult puo' essere
	 *	array key [article_organization_id, article_id, order_id, user_id]
	 *	obj ['ArticlesOrder']
	 */	
	public function changeCart($user, $cartResult, $operation='', $options=[], $debug=false) {
	
		$esito = [];

		if(empty($cartResult)) {
			$esito['CODE'] = "500";
			$esito['MSG'] = "Parametri errati";
			return $esito; 
		}	

		if(!is_array($cartResult))
			$cartResult = $this->_getCartById($user, $cartResult, $debug);
	
		switch($operation) {
			case 'CLOSE':

			break;
		}
			
		return $esito; 
	}

    private function _getCartById($user, $cartResult, $debug) {

		App::import('Model', 'Cart');
		$Cart = new Cart;
		
		$options = [];
		$options['conditions'] = ['Cart.organization_id' => (int)$user->organization['Organization']['id'],
								  'Cart.article_organization_id' => $cartResult['article_organization_id'],
								  'Cart.article_id' => $cartResult['article_id'],
								  'Cart.order_id' => $cartResult['order_id'],
								  'Cart.user_id' => $cartResult['user_id']];
		$options['recursive'] = 0;		
		$results = $this->find('first', $options);
		/*
		echo "<pre>SiteLifeCycle::_getCartById ";
		print_r($options);
		print_r($results);
		echo "</pre>";
		*/	

		return $results;
	}
	
    private function _getArticleById($user, $articleResult, $debug) {

		App::import('Model', 'Article');
		$Article = new Article;
		
		$options = [];
		$options['conditions'] = ['Article.organization_id' => (int)$user->organization['Organization']['id'],
								  'Article.article_organization_id' => $articleResult['article_organization_id'],
								  'Article.id' => $articleResult['article_id']];
		$options['recursive'] = 0;		
		$results = $this->find('first', $options);
		/*
		echo "<pre>SiteLifeCycle::_getArticleById ";
		print_r($options);
		print_r($results);
		echo "</pre>";
		*/	

		return $results;
	}
	
    private function _getArticlesOrderById($user, $articlesOrderResult, $debug) {

		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$options = [];
		$options['conditions'] = ['ArticlesOrder.organization_id' => (int)$user->organization['Organization']['id'],
								  'ArticlesOrder.order_id' => $articlesOrderResult['order_id'],
								  'ArticlesOrder.article_organization_id' => $articlesOrderResult['article_organization_id'],
								  'ArticlesOrder.article_id' => $articlesOrderResult['article_id']];
		$options['recursive'] = 0;		
		$results = $this->find('first', $options);
		/*
		echo "<pre>SiteLifeCycle::_getArticlesOrderById ";
		print_r($options);
		print_r($results);
		echo "</pre>";
		*/	

		return $results;
	}
	
}