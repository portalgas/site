<?php
App::uses('AppModel', 'Model');


class ProdGasArticlesSyncronize extends AppModel {

    public $useTable = 'prod_gas_articles';
   
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

	/*
	 * articolo del produttore gia' presente nel GAS => UPDATE Article
	 * 	 	
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function syncronize_update($user, $organization_id, $prod_gas_article_id, $category_article_id=0, $debug=false) {

		// $debug = true;
		
		$msg_esito = "";

        if(empty($prod_gas_article_id) /* || empty($category_article_id) */) {
            return __('msg_error_params');
        }
		
		App::import('Model', 'Article');
		$Article = new Article;

		$Article->bindModel(array('belongsTo' => array('ProdGasArticle' => array(
														'className' => 'ProdGasArticle',
														'foreignKey' => 'prod_gas_article_id'))));
		$Article->unbindModel(['belongsTo' => ['SuppliersOrganization', 'CategoriesArticle']]);
		$Article->unbindModel(array('hasOne' => array('ArticlesArticlesType', 'ArticlesOrder')));
		$Article->unbindModel(array('hasMany' => array('ArticlesArticlesType', 'ArticlesOrder'))); 
		$Article->unbindModel(array('hasAndBelongsToMany' => array('ArticlesType', 'Order'))); 
		$options = [];
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									   'ProdGasArticle.id' => $prod_gas_article_id,
									   'ProdGasArticle.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
									   'Article.supplier_id' => $user->organization['Supplier']['Supplier']['id']);
		$options['recursive'] = 0;
		$results = $Article->find('first', $options);	

		if($debug) {
			echo "<pre>Article Originale \n";
			print_r($options['conditions']);
			print_r($results);
			echo "</pre>";			
		}
		
		if(empty($results)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		/*
		 * override Article
		 */
		$row = [];
		$row['Article'] = $results['Article']; 
		
		$row['Article']['category_article_id'] = $category_article_id;
		$row['Article']['name'] = $results['ProdGasArticle']['name'];
		$row['Article']['codice'] = $results['ProdGasArticle']['codice'];
		$row['Article']['nota'] = $results['ProdGasArticle']['nota'];
		$row['Article']['ingredienti'] = $results['ProdGasArticle']['ingredienti'];
		$row['Article']['prezzo'] = $results['ProdGasArticle']['prezzo'];
		$row['Article']['qta'] = $results['ProdGasArticle']['qta'];
		$row['Article']['um'] = $results['ProdGasArticle']['um'];
		$row['Article']['um_riferimento'] = $results['ProdGasArticle']['um_riferimento'];
		$row['Article']['pezzi_confezione'] = $results['ProdGasArticle']['pezzi_confezione'];
		$row['Article']['qta_minima'] = $results['ProdGasArticle']['qta_minima'];
		$row['Article']['qta_multipli'] = $results['ProdGasArticle']['qta_multipli'];
		$row['Article']['bio'] = $results['ProdGasArticle']['bio'];
		
		// $row['Article']['flag_presente_articlesorders'] = 'Y'; non + ho la funzione apposita syncronize_flag_presente_articlesorders
		$row['Article']['stato'] = 'Y';
		
		$Article->set($row);
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		/* 
		 * img1
		*/
		if(!empty($results['ProdGasArticle']['img1'])) {
			$newFile = $Article->copy_img_prod_gas_supplier($user, $results, $debug);
			if($newFile!=false) 
				$row['Article']['img1'] = $newFile;
			else {
				$msg_esito = "Errore nella copia dell'immagine dell'articolo con ID $prod_gas_article_id <br />";
				
				/* vado cmq avanti
				if(!$debug) 
					return $msg_esito;				
				else
					self::x($msg_esito);
				*/
				if($debug) 
					self::x($msg_esito);			
			}
		}
		
		if($debug) {
			echo "<pre>Article SAVE to UPDATE \n";
			print_r($row);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($row)) {
			$msg_esito .= "Articolo del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
				
		if($debug)
			exit;
		
		return true;
	} 

	/*
	 * nuovo articolo del produttore => INSERT Article
	 * 	 
	 * copia un articolo da ProdGasArticle a Article
	 * copia l'img da da ProdGasArticle a Article
	 */
	public function syncronize_insert($user, $organization_id, $prod_gas_article_id, $category_article_id=0, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($prod_gas_article_id)) {
             return __('msg_error_params');
        }

		App::import('Model', 'Article');
		$Article = new Article;
		
		App::import('Model', 'ProdGasSupplier');
		$ProdGasSupplier = new ProdGasSupplier;
		
		// non utilizzo il Model ProdGasArticlesSyncronize perche' in Article::copy_img_prod_gas_supplier() utilizzo ProdGasArticle
 		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;
		
		$options = [];
		$options['conditions'] = array('ProdGasArticle.id' => $prod_gas_article_id,
									   'ProdGasArticle.supplier_id' => $user->organization['Supplier']['Supplier']['id']);
		$options['recursive'] = -1;
		$results = $ProdGasArticle->find('first', $options);	

		if($debug) {
			echo "<pre>ProdGasArticle del produttore \n";
			print_r($options['conditions']);
			print_r($results);
			echo "</pre>";			
		}
		
		if(empty($results)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		/*
		 * ctrl che l'articolo del produttore non sia gia' associato
		 */	
		$options = [];
		$options['conditions'] = array('Article.prod_gas_article_id' => $prod_gas_article_id,
									   'Article.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
									   'Article.organization_id' => $organization_id);
		$options['recursive'] = -1;
		$articlesCtrlResults = $Article->find('first', $options);		
		if(!empty($articlesCtrlResults)) {			
			$msg_esito = "Articolo già presente!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);			
		} 
		
		/*
		 *
		 */
		$suppliersOrganizationsResults = $ProdGasSupplier->getSuppliersOrganization($user, $organization_id, $debug);
		if(empty($suppliersOrganizationsResults['SuppliersOrganization']['id'])) {
			$msg_esito = "Non trovato il produttore associato al GAS!";
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);			
		}
		
		/*
		 * popolo Article
		 */		
		$row = [];
		$row['Article']['id'] = $this->_getMaxIdOrganizationId($organization_id);
		$row['Article']['organization_id'] = $organization_id;
		$row['Article']['supplier_organization_id'] = $suppliersOrganizationsResults['SuppliersOrganization']['id'];
		$row['Article']['prod_gas_article_id'] = $results['ProdGasArticle']['id'];
		$row['Article']['supplier_id'] = $results['ProdGasArticle']['supplier_id'];
		
		$row['Article']['category_article_id'] = $category_article_id;
		$row['Article']['name'] = $results['ProdGasArticle']['name'];
		$row['Article']['codice'] = $results['ProdGasArticle']['codice'];
		$row['Article']['nota'] = $results['ProdGasArticle']['nota'];
		$row['Article']['ingredienti'] = $results['ProdGasArticle']['ingredienti'];
		$row['Article']['prezzo'] = $results['ProdGasArticle']['prezzo'];
		$row['Article']['qta'] = $results['ProdGasArticle']['qta'];
		$row['Article']['um'] = $results['ProdGasArticle']['um'];
		$row['Article']['um_riferimento'] = $results['ProdGasArticle']['um_riferimento'];
		$row['Article']['pezzi_confezione'] = $results['ProdGasArticle']['pezzi_confezione'];
		$row['Article']['qta_minima'] = $results['ProdGasArticle']['qta_minima'];
		$row['Article']['qta_multipli'] = $results['ProdGasArticle']['qta_multipli'];
		$row['Article']['bio'] = $results['ProdGasArticle']['bio'];
		
		// inalterati
		$row['Article']['qta_massima'] = 0;
		$row['Article']['qta_minima_order'] = 0;
		$row['Article']['qta_massima_order'] = 0;
		$row['Article']['alert_to_qta'] = 0;
		$row['Article']['stato'] = 'Y';

		$row['Article']['flag_presente_articlesorders'] = 'Y';
				
		$Article->set($row);
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
			
		/* 
		 * img1
		*/
		if(!empty($results['ProdGasArticle']['img1'])) {
			$newFile = $Article->copy_img_prod_gas_supplier($user, array_merge($results, $row), $debug);
			if($newFile!=false) 
				$row['Article']['img1'] = $newFile;
			else {
				$msg_esito = "Errore nella copia dell'immagine dell'articolo con ID $prod_gas_article_id <br />";
				
				/* vado cmq avanti
				if(!$debug) 
					return $msg_esito;				
				else
					self::x($msg_esito);
				*/
				if($debug) 
					self::x($msg_esito);
			}			
		}
		
		if($debug) {
			echo "<pre>Article SAVE to INSERT \n";
			print_r($row);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($row)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		//if($debug)
		//	exit;
		
		return true;
	} 	

	/*
	 * articolo del produttore non + presente nel suo archivio => DELETE Article
	 */
	public function syncronize_delete($user, $organization_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($article_id)) {
             return __('msg_error_params');
        }

        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);

		App::import('Model', 'Article');
		$Article = new Article;
		
		/*
 		 * ctrl gli eventuali acquisti gia' effettuati, se true non posso cancellarlo
		 */
		$isArticleInCart = $Article->isArticleInCart($tmp_user, $organization_id, $article_id);
		if($isArticleInCart)  {
			$msg_esito = "Articolo acquistato, non posso cancellarlo!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);			
		}
					
		/*
		 * Article prima del salvataggio
		*/
		$options = [];
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									  'Article.id' => $article_id);
		$options['recursive'] = -1;
		$options['fields'] = array('img1');
		$results = $Article->find('first', $options);

		/*
		 * il delete lo faccio dopo se no non lo trovo!
		 */
		if($Article->syncronizeArticlesOrder($tmp_user, $article_id, 'DELETE', $debug)) {
			/*
			 * potrei non trovarlo tra quelli ordinati
			$msg_esito = "Articolo non sincronizzato con gli articoli degli ordini associati!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
			*/			
		}
		
		if(!$Article->delete($organization_id, $article_id)) {
			$msg_esito = "Articolo non cancellato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);			
		}
					
						
		/*
		 * IMG1 delete
		*/
		if(!empty($results['Article']['img1'])) {
			$img_path = Configure::read('App.root').Configure::read('App.img.upload.article').DS.$organization_id.DS;
			$file1 = new File($img_path.$results['Article']['img1'], false, 0777);
		
			if($debug) {
				echo "<pre>Article.img1 \n ";
				print_r($file1);
				echo "</pre>";
			}
			
			if(!$file1->delete()) {
				$msg_esito = "Immagine dell'articolo non eliminata";

				if(!$debug) 
					return $msg_esito;				
				else
					self::x($msg_esito);			
			} 
		} // if(!empty($results['Article']['img1']))
		
		//if($debug)
		//	exit;
		
		return true;
	} 
	
	/*
	 * articolo del produttore non + presente nel suo archivio => e' acquistato e non posso DELETE Article => Article.flag_presente_articlesorder = 'N'
	 */
	public function syncronize_flag_presente_articlesorders($user, $organization_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($article_id)) {
            return __('msg_error_params');
        }

        $tmp_user = $this->utilsCommons->createObjUser(['organization_id' => $organization_id]);
        
		App::import('Model', 'Article');
		$Article = new Article;
					
		/*
		 * Article prima del salvataggio
		*/
		$options = [];
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									  'Article.id' => $article_id);
		$options['recursive'] = -1;
		$results = $Article->find('first', $options);
			
		if($results['Article']['flag_presente_articlesorders'] == 'N')
			$results['Article']['flag_presente_articlesorders']='Y';
		else
			$results['Article']['flag_presente_articlesorders']='N';
		
		$Article->set($results);
		if(!$Article->validates()) {
			$errors = $Article->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		if($debug) {
			echo "<pre>Article SAVE to UPDATE flag_presente_articlesorders \n";
			print_r($results);
			echo "</pre>";			
		}
		
		$Article->create();
		if(!$Article->save($results)) {
			$msg_esito = "Articolo del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
				
		//if($debug)
		//	exit;
		
		return true;
	} 
	
	public function import_article_gas($user, $organization_id, $prod_gas_article_id, $article_id, $debug=false) {

		$msg_esito = "";
		
        if(empty($organization_id) || empty($prod_gas_article_id) || empty($article_id)) {
            return false;
        }

		$sql = "UPDATE 
					".Configure::read('DB.prefix')."articles
				SET
					prod_gas_article_id = ".$prod_gas_article_id.",
					supplier_id = ".$user->organization['Supplier']['Supplier']['id']." 
				WHERE
				    organization_id = ".(int)$organization_id."
					and id = ".$article_id;
		self::d($sql, $debug);
		try {
			$results = $this->query($sql);
		}
		catch (Exception $e) {
			CakeLog::write('error',$sql);
			CakeLog::write('error',$e);
			if($debug) echo '<br />'.$e;
		}

		//if($debug)
		//	exit;
		
		return true;
	} 

	
	private function _getMaxIdOrganizationId($organization_id) {
    	
    	$maxId = 1;
    	
		App::import('Model', 'Article');
		$Article = new Article;
		
		$options = [];
     	$options['fields'] = array('MAX(Article.id)+1 AS maxId');
    	$options['conditions'] = array('Article.organization_id' => $organization_id);
    	$options['recursive'] = -1;
    	$results = $Article->find('first', $options);
    	if(!empty($results)) {
    		$results = current($results);
    		$maxId = $results['maxId'];
    		if(empty($maxId)) $maxId = 1;
    	}

    	return $maxId;
    }
	
	public function afterFind($results, $primary = false) {

		foreach ($results as $key => $val) {
			if(!empty($val)) {

				if(isset($val['ProdGasArticlesSyncronize']['prezzo'])) {
					$results[$key]['ProdGasArticlesSyncronize']['prezzo_'] = number_format($val['ProdGasArticlesSyncronize']['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['ProdGasArticlesSyncronize']['prezzo_e'] = $results[$key]['ProdGasArticlesSyncronize']['prezzo_'].' &euro;';
				}
				else
					/*
					 * se il find() arriva da $hasAndBelongsToMany
					*/
				 if(isset($val['prezzo'])) {
					$results[$key]['prezzo_'] = number_format($val['prezzo'],2,Configure::read('separatoreDecimali'),Configure::read('separatoreMigliaia'));
					$results[$key]['prezzo_e'] = $results[$key]['prezzo_'].' &euro;';
				}
				
				/*
				 * qta, da 1.00 a 1
				 * 		da 0.75 a 0,75  
				 * */
				if(isset($val['ProdGasArticlesSyncronize']['qta'])) {
					$qta = str_replace(".", ",", $val['ProdGasArticlesSyncronize']['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['ProdGasArticlesSyncronize']['qta_'] = $qta;
				}
				else
				/*
				 * se il find() arriva da $hasAndBelongsToMany
				*/	
				if(isset($val['qta'])) {
					$qta = str_replace(".", ",", $val['qta']);
					$arrCtrlTwoZero = explode(",",$qta);
					if($arrCtrlTwoZero[1]=='00') $qta = $arrCtrlTwoZero[0];
					$results[$key]['qta_'] = $qta;
				}
			}
		}
		
		return $results;
	}
	
	/*
	 * articleOrders
	 */	
	 public function syncronize_articles_orders_update($user, $organization_id, $order_id, $article_organization_id, $article_id, $prod_gas_article_id, $debug=false) {
		
		// $debug = true;
		
		$msg_esito = "";

        if(empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id) || empty($prod_gas_article_id)) {
            return __('msg_error_params');
        }
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'ProdGasArticle');
		$ProdGasArticle = new ProdGasArticle;

		$options = [];
		$options['conditions'] = array('ArticlesOrder.organization_id' => $organization_id,
									   'ArticlesOrder.order_id' => $order_id,
									   'ArticlesOrder.article_organization_id' => $article_organization_id,
									   'ArticlesOrder.article_id' => $article_id);
		$options['recursive'] = -1;
		$articlesOrderResults = $ArticlesOrder->find('first', $options);	

		if($debug) {
			echo "<pre>ArticlesOrder Originale \n";
			print_r($options['conditions']);
			print_r($articlesOrderResults);
			echo "</pre>";			
		}
		
		if(empty($articlesOrderResults)) {
			$msg_esito = "Articolo associato all'ordine $order_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}

		$options = [];
		$options['conditions'] = array('ProdGasArticle.supplier_id' => $user->organization['Supplier']['Supplier']['id'],
									   'ProdGasArticle.id' => $prod_gas_article_id);
		$options['recursive'] = -1;
		
		$prodGasArticleResults = $ProdGasArticle->find('first', $options);	

		if($debug) {
			echo "<pre>prodGasArticleResults master \n";
			print_r($options['conditions']);
			print_r($prodGasArticleResults);
			echo "</pre>";			
		}
		
		if(empty($prodGasArticleResults)) {
			$msg_esito = "Articolo del produttore $prod_gas_article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}

		
		
		
		/*
		 * override ArticleOrders
		 */
		$articlesOrderResults['ArticlesOrder']['organization_id'] = $organization_id;
		$articlesOrderResults['ArticlesOrder']['order_id'] = $order_id;
		$articlesOrderResults['ArticlesOrder']['name'] = $prodGasArticleResults['ProdGasArticle']['name'];
		$articlesOrderResults['ArticlesOrder']['prezzo'] = $prodGasArticleResults['ProdGasArticle']['prezzo'];
		$articlesOrderResults['ArticlesOrder']['pezzi_confezione'] = $prodGasArticleResults['ProdGasArticle']['pezzi_confezione'];
		$articlesOrderResults['ArticlesOrder']['qta_minima'] = $prodGasArticleResults['ProdGasArticle']['qta_minima'];
		$articlesOrderResults['ArticlesOrder']['qta_multipli'] = $prodGasArticleResults['ProdGasArticle']['qta_multipli'];
		$articlesOrderResults['ArticlesOrder']['stato'] = $prodGasArticleResults['ProdGasArticle']['stato'];
		if($debug) {
			echo "<pre>";
			print_r($articlesOrderResults);
			echo "</pre>";
		}		
		$ArticlesOrder->set($articlesOrderResults);
		if(!$ArticlesOrder->validates()) {
			$errors = $ArticlesOrder()->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		if($debug) {
			echo "<pre>ArticlesOrder SAVE to UPDATE \n";
			print_r($articlesOrderResults);
			echo "</pre>";			
		}
		
		$ArticlesOrder->create();
		if(!$ArticlesOrder->save($articlesOrderResults)) {
			$msg_esito .= "Articolo dell'ordine del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
				
		if($debug)
			exit;
		
		return true;	 
	 }

	 public function syncronize_articles_orders_insert($user, $organization_id, $order_id, $prod_gas_article_id, $debug=false) {
		$msg_esito = "";

        if(empty($organization_id) || empty($order_id) || empty($prod_gas_article_id)) {
            return __('msg_error_params');
        }
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		App::import('Model', 'Article');
		$Article = new Article;

		$options = [];
		$options['conditions'] = array('Article.organization_id' => $organization_id,
									   'Article.prod_gas_article_id' => $prod_gas_article_id);
		$options['recursive'] = -1;
		
		$articleResults = $Article->find('first', $options);	

		if($debug) {
			echo "<pre>articleResults master \n";
			print_r($options['conditions']);
			print_r($articleResults);
			echo "</pre>";			
		}
		
		if(empty($articleResults)) {
			$msg_esito = "Articolo del produttore $prod_gas_article_id non trovato!";

			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}

		
		
		
		/*
		 * insert ArticleOrders
		 */
		$articlesOrderResults = []; 
		$articlesOrderResults['ArticlesOrder']['organization_id'] = $organization_id;
		$articlesOrderResults['ArticlesOrder']['order_id'] = $order_id;
		$articlesOrderResults['ArticlesOrder']['article_organization_id'] = $articleResults['Article']['organization_id'];
		$articlesOrderResults['ArticlesOrder']['article_id'] = $articleResults['Article']['id'];
		$articlesOrderResults['ArticlesOrder']['name'] = $articleResults['Article']['name'];
		$articlesOrderResults['ArticlesOrder']['prezzo'] = $articleResults['Article']['prezzo'];
		$articlesOrderResults['ArticlesOrder']['pezzi_confezione'] = $articleResults['Article']['pezzi_confezione'];
		$articlesOrderResults['ArticlesOrder']['qta_minima'] = $articleResults['Article']['qta_minima'];
		$articlesOrderResults['ArticlesOrder']['qta_multipli'] = $articleResults['Article']['qta_multipli'];
		$articlesOrderResults['ArticlesOrder']['stato'] = $articleResults['Article']['stato'];
		
		$ArticlesOrder->set($articlesOrderResults);
		if(!$ArticlesOrder->validates()) {
			$errors = $ArticlesOrder()->validationErrors;
			
			foreach($errors as $key => $value) 
				foreach($value as $key2 => $msg) 
					$msg_esito .= $msg.'<br />';
				
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
		
		if($debug) {
			echo "<pre>ArticlesOrder SAVE to UPDATE \n";
			print_r($articlesOrderResults);
			echo "</pre>";			
		}
		
		$ArticlesOrder->create();
		if(!$ArticlesOrder->save($articlesOrderResults)) {
			$msg_esito .= "Articolo associato all'ordine del produttore con ID $prod_gas_article_id non salvato!";
			
			if(!$debug) 
				return $msg_esito;				
			else
				self::x($msg_esito);
		}
				
		//if($debug)
		//	exit;
		
		return true;	 
	 }
	 
	 public function syncronize_articles_orders_delete($userOrganization, $organization_id, $order_id, $article_organization_id, $article_id, $debug=false) {
		$msg_esito = "";

        if(empty($organization_id) || empty($order_id) || empty($article_organization_id) || empty($article_id)) {
            return __('msg_error_params');
        }
		
		App::import('Model', 'ArticlesOrder');
		$ArticlesOrder = new ArticlesOrder;
		
		$msg_esito = $ArticlesOrder->delete_and_carts($userOrganization, $order_id, $article_organization_id, $article_id, $des_order_id, $debug);
	
		//if($debug)
		//	exit;
		
		return true;	 
	 }	 
	 
	 /*
	  * nel contesto della gestione di un articolo posso gia' sincronizzare
	  * key action-organization_id-order_id
	  */
	 public function syncronize_to_article($user, $prod_gas_article_id, $data_prodGasArticlesSyncronize, $debug=false) {
	 
	 	// $debug = true;
 
 		$msg = "";
 		
 		if($debug) {
			echo "<pre>";
			print_r($data_prodGasArticlesSyncronize);
			echo "</pre>";
		}

		if(!empty($data_prodGasArticlesSyncronize)) {
			foreach($data_prodGasArticlesSyncronize as $key => $value) {
				if($value=='Y') {
					
					$category_article_id = 0;
					$order_id = 0;
					
					if(strpos($key, "-")!==false) {
						list($action, $organization_id, $order_id) = explode('-', $key);
					}
					
					if($debug) {
						echo '<br />action '.$action.' - organization_id '.$organization_id.' - order_id '.$order_id.' - value '.$value;
					}

					/*
					 * dati articolo solo se update, in insert non esiste ancora
					 */
					switch($action) {
						case "syncronize_update":
						case "syncronize_articles_orders_update":
							App::import('Model', 'Article');
							$Article = new Article;
					
							$options = [];
							$options['conditions'] = array('Article.organization_id' => $organization_id,
														   'Article.prod_gas_article_id' => $prod_gas_article_id);
							$options['recursive'] = -1;
							$articleResults = $Article->find('first', $options);
							if($debug) {
								echo "<pre>";
								print_r($options);
								print_r($articleResults);
								echo "</pre>";
							}						
							
							if(empty($articleResults))
								self::x("Articolo non trovato con prod_gas_article_id ".$prod_gas_article_id);
									
							$article_organization_id = $articleResults['Article']['organization_id'];
							$article_id = $articleResults['Article']['id'];
							$category_article_id = $articleResults['Article']['category_article_id'];
						break;
					}
			 		
						
					switch($action) {
						case "syncronize_update":
						 	$this->syncronize_update($user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
						break;
						case "syncronize_articles_orders_update":
							/*
							 * se non e' stato aggiornato l'articolo, prima lo aggiorno
							 */							
							if($data_prodGasArticlesSyncronize['syncronize_update-'.$organization_id.'-0']=='N')
								$this->syncronize_update($user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
							 
							$this->syncronize_articles_orders_update($user, $organization_id, $order_id, $article_organization_id, $article_id, $prod_gas_article_id, $debug);
						break;
						case "syncronize_insert":
							$this->syncronize_insert($user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
						break;
						case "syncronize_articles_orders_insert":
							/*
							 * se non e' stato inserito l'articolo, prima lo inserisco
							 */
							if($data_prodGasArticlesSyncronize['syncronize_insert-'.$organization_id.'-0']=='N')
								$this->syncronize_insert($user, $organization_id, $prod_gas_article_id, $category_article_id, $debug);
								
							$this->syncronize_articles_orders_insert($user, $organization_id, $order_id, $prod_gas_article_id, $debug);
						break;
					}
					
				} // end if($value=='Y') 
			} // loop
		}
		
		if($debug)
			exit;
			
		return true;
	}
}